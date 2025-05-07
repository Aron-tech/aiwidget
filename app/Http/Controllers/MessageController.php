<?php

namespace App\Http\Controllers;

use App\Http\Requests\MessageRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Chat;
use App\Models\Site;
use App\Models\Message;
use App\Models\QuestionAnswer;
use EchoLabs\Prism\Prism;
use EchoLabs\Prism\Enums\Provider;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Enums\ChatStatusEnum;
use App\Enums\MessageSenderRolesEnum;

class MessageController extends Controller
{
    public function findBestAnswer($user_question, $site_id, $chat_id): ?string
    {
        $exact_match = QuestionAnswer::where('site_id', $site_id)
            ->where('question', $user_question)
            ->first();

        if ($exact_match) {
            return $exact_match->answer;
        }

        $user_embedding = $this->getEmbedding($user_question);

        $best_match = null;
        $highest_score = 0;

        $questions = QuestionAnswer::where('site_id', $site_id)->get();

        foreach ($questions as $question) {
            $question_embedding = json_decode($question->embedding, true);

            if(!empty($question_embedding) && !empty($user_embedding)) {
                $similarity_score = $this->cosineSimilarity($user_embedding, $question_embedding);
            }else{
                $similarity_score = 0;
            }

            if ($similarity_score > $highest_score) {
                $highest_score = $similarity_score;
                $best_match = $question;
            }
        }
        //Log::info($highest_score);

        if ($best_match && $highest_score > 0.5){

            $language_cache = 'site_' . $site_id . '_chat_' . $chat_id . '_language';

            //Log::info('Felhasználó kérdés: ' . $user_question . ' Válasz: ' . $best_match->answer);
            $language = Cache::remember($language_cache, now()->addMinutes(10), function () use ($user_question) {
                $language_result = Prism::text()
                    ->using(Provider::OpenAI, 'gpt-3.5-turbo')
                    ->withSystemPrompt('You are a language detection service. Only respond with a short ISO 639-1 language code like "en", "hu", or "de". Do not explain.')
                    ->withPrompt($user_question)
                    ->generate();
                return $language_result->text;
            });

            //Log::info('Nyelv: ' . $language->text);

            $optimized_result = Prism::text()
                ->using(Provider::OpenAI, 'gpt-4o-mini')
                ->withSystemPrompt('You are a translation and phrasing expert.')
                ->withClientOptions(['timeout' => 15])
                ->withPrompt(
                    "You are given a user question, a system question, and a system answer.\n\n" .
                    "Your task is to translate ONLY the system answer into " . $language . ".\n" .
                    "Do NOT return or rephrase the user question or system question.\n" .
                    "Do NOT add any explanation or extra text — return only the translated answer.\n\n" .
                    "User question: " . $user_question . "\n" .
                    "System question: " . $best_match->question . "\n" .
                    "System answer: " . $best_match->answer
                )
                ->generate();
            //Log::info('Optimalizált válasz: ' . $optimized_result->text);

            if (isset($optimized_result->text))
                return $optimized_result->text;
        }

        return null;
    }

    private function getEmbedding($text): ?array
    {
        $cache_key = 'embedding_' . md5($text);

        return Cache::remember($cache_key, now()->addHours(12), function () use ($text) {

            $response = Prism::embeddings()
                ->using(Provider::OpenAI, 'text-embedding-3-large')
                ->fromInput($text)
                ->withClientOptions(['timeout' => 15])
                ->withClientRetry(2, 100)
                ->generate();

            if (empty($response->embeddings)) {
                throw new \Exception("Hiba: A beágyazás generálása sikertelen.");
            }

            return $response->embeddings;
        });
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

        if (!$validated['chat_id']) {
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

        $answer = $this->findBestAnswer($validated['message'], $site->id, $chat->id);

        if(!$answer){
            $answer = "A kérdésedre nem sikerült helyes választ találni, hamarosan megválaszolja egy munkatárs a kérdésedet.";
            $chat->update([
                'status' => ChatStatusEnum::WAITING,
            ]);
        }

        //Log::info("Bot válasz lekérdezés után: $answer");

        Message::create([
            'chat_id' => $chat->id,
            'message' => $answer,
            'sender_role' => MessageSenderRolesEnum::BOT,
        ]);

        //Log::info("Bot üzenet mentése adatbázisba: $bot_message->id");

        return response()->json([
            'message' => 'Az üzenet sikeresen elküldve!',
            'data' => [
                'chat_id' => $chat->id,
            ]
        ], 200);
    }
}
