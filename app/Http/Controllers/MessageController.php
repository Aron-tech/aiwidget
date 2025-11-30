<?php

namespace App\Http\Controllers;

use App\Actions\GenerateEmbeddingAction;
use App\Actions\GenerateTextAction;
use App\Actions\SearchDocumentsAction;
use App\Http\Requests\MessageRequest;
use Illuminate\Http\JsonResponse;
use App\Models\Chat;
use App\Models\Site;
use App\Models\Message;
use Illuminate\Support\Facades\Cache;
use App\Enums\ChatStatusEnum;
use App\Enums\MessageSenderRolesEnum;
use Lorisleiva\Actions\Concerns\AsAction;

class MessageController extends Controller
{
    use AsAction;

    public function findBestAnswer(string $user_question, Site $site, int $chat_id): array
    {
        $site_id = $site->id;
        $kb_setting = getJsonValue($site, 'settings', 'knowledge-databases', []);
        $highest_score = 0;
        $embedding_token_count = 0;
        $optimized_result_question = null;
        $optimized_result_document = null;

        $language_cache = 'site_' . $site_id . '_chat_' . $chat_id . '_language';

        $language_result = Cache::remember($language_cache, now()->addMinutes(10), function () use ($user_question) {
            return GenerateTextAction::run(
                'You are a language detection service. Only respond with a short ISO 639-1 language code like "en", "hu", or "de". Do not explain.',
                $user_question,
                'gpt-3.5-turbo'
            );
        });

        $language = $language_result->text;
        $embedding_token_count += $language_result->usage->promptTokens;
        $embedding_token_count += $language_result->usage->completionTokens;

        if (in_array('question', $kb_setting)) {
            $exact_match = $site->questionAnswers()
                ->where('question', $user_question)
                ->first();

            if ($exact_match) {
                return [$exact_match->answer, $embedding_token_count];
            }

            $user_embedding_response = GenerateEmbeddingAction::run($user_question);
            $embedding_token_count += $user_embedding_response->usage->tokens;
            $user_embedding = $user_embedding_response->embeddings;

            $best_match = null;

            $questions = $site->questionAnswers()->get();

            foreach ($questions as $question) {
                $question_embedding = json_decode($question->embedding, true);

                if (!empty($question_embedding) && !empty($user_embedding)) {
                    $similarity_score = $this->cosineSimilarity($user_embedding, $question_embedding);
                } else {
                    $similarity_score = 0;
                }

                if ($similarity_score > $highest_score) {
                    $highest_score = $similarity_score;
                    $best_match = $question;
                }
            }

            if ($best_match && $highest_score > 0.5) {

                $optimized_result_question = GenerateTextAction::run(
                    'You are a translation and phrasing expert.',
                    "Translate only the following system answer into {$language}.
                                Use the user and system question only as context if needed.
                                Return only the translated system answer, nothing else.
                                User question: {$user_question}
                                System question: {$best_match->question}
                                System answer: {$best_match->answer}");

                $embedding_token_count += $optimized_result_question->usage->promptTokens;
                $embedding_token_count += $optimized_result_question->usage->completionTokens;
            }
            if(in_array('question', $kb_setting) && !in_array('document', $kb_setting)) {
                return [$optimized_result_question?->text, $embedding_token_count];
            }
        }

        if (in_array('document', $kb_setting)) {
            $optimized_result_document = (new SearchDocumentsAction())->execute($user_question, $site_id, 3, $language);
            $embedding_token_count += $optimized_result_document['token_count'];
            if(in_array('document', $kb_setting) && !in_array('question', $kb_setting)) {
                return [$optimized_result_document['answer'], $embedding_token_count];
            }
        }
        if (in_array('question', $kb_setting) && in_array('document', $kb_setting)) {
            $is_better_document = false;
            foreach ($optimized_result_document['search_results'] as $document_item) {
                if ($highest_score < $document_item['score']) {
                    $is_better_document = true;
                }
            }
            if ($is_better_document) {
                return [$optimized_result_document['answer'], $embedding_token_count];
            } elseif($highest_score>=0.1) {
                return [$optimized_result_question?->text, $embedding_token_count];
            }
        }

        return [null, $embedding_token_count];
    }

    private function cosineSimilarity(array $a, array $b): float
    {
        $sum = 0;
        $a_sum = 0;
        $b_sum = 0;

        for ($i = 0, $n = count($a); $i < $n; $i++) {
            $sum += $a[$i] * $b[$i];
            $a_sum += $a[$i] * $a[$i];
            $b_sum += $b[$i] * $b[$i];
        }

        return $sum / (sqrt($a_sum) * sqrt($b_sum) ?: 1);
    }

    public function store(Site $site, MessageRequest $request): JsonResponse
    {
        $validated = $request->validated();

        if (!isset($validated['chat_id'])) {
            $chat = Chat::create([
                'site_id' => $site->id,
                'visitor_name' => $validated['nickname'],
                'visitor_email' => $validated['email'],
            ]);
        } else {
            $chat = Chat::where('id', $validated['chat_id'])->where('site_id', $site->id)->first();
            if (!$chat) {
                return response()->json(['error' => 'Érvénytelen chat azonosító'], 404);
            }
        }

        Message::create([
            'chat_id' => $chat->id,
            'message' => $validated['message'],
        ]);

        $answer_response = $this->findBestAnswer($validated['message'], $site, $chat->id);
        $answer = $answer_response[0];
        $used_token_count = $answer_response[1];

        if (!$answer) {
            $answer = "A kérdésedre nem sikerült helyes választ találni, hamarosan megválaszolja egy munkatárs a kérdésedet.";
            $chat->update([
                'status' => ChatStatusEnum::WAITING,
            ]);
        }

        Message::create([
            'chat_id' => $chat->id,
            'message' => $answer,
            'sender_role' => MessageSenderRolesEnum::BOT,
            'token_count' => $used_token_count,
        ]);

        return response()->json([
            'message' => 'Az üzenet sikeresen elküldve!',
            'data' => [
                'chat_id' => $chat->id,
            ]
        ], 200);
    }
}
