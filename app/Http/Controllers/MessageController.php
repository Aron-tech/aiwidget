<?php

namespace App\Http\Controllers;

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
use LanguageDetector\LanguageDetector;

class MessageController extends Controller
{
    public function findBestAnswer($user_question, $site_id): ?string
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

            //Log::info('Felhasználó kérdés: ' . $user_question . ' Válasz: ' . $best_match->answer);

            $detector = new LanguageDetector(null, ['en', 'hu', 'de']);
            $language = $detector->evaluate($user_question);

            //Log::info('Felhasználó kérdés nyelve: ' . $language);

            $optimized_result = Prism::text()
                ->using(Provider::OpenAI, 'gpt-4o-mini')
                ->withSystemPrompt('You are a translation and phrasing expert.')
                ->withPrompt(
                    "User question: " . $user_question . " " .
                    "System question: " . $best_match->question . " " .
                    "System answer: " . $best_match->answer . ". " .
                    "The answer must be translated to the language: " . $language . ". ".
                    "Use only the information contained in the System question and answer, and nothing else. " .
                    "Do not add any extra information, and do not change the content of the System question and answer."
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

        return Cache::remember($cache_key, now()->addMinutes(5), function () use ($text) {

            $response = Prism::embeddings()
                ->using(Provider::OpenAI, 'text-embedding-3-large')
                ->fromInput($text)
                ->withClientOptions(['timeout' => 30])
                ->withClientRetry(3, 100)
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

    public function store(Site $site, Request $request): JsonResponse
    {
        $site_id = $site->id;

        $validated = $request->validate([
            'nickname' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'message' => 'required|min:6|string',
            'chat_id' => 'nullable|max:255',
        ]);

        $chat_id = $validated['chat_id'] ?? null;

        if (!$chat_id) {

            $chat = Chat::create([
                'site_id' => $site_id,
                'visitor_name' => $validated['nickname'],
                'visitor_email' => $validated['email'],
            ]);

            $chat_id = $chat->id;

        } else {
            $chat = Chat::where('id', $chat_id)->where('site_id', $site_id)->first();
            if (!$chat) {
                return response()->json(['error' => 'Érvénytelen chat azonosító'], 400);
            }
        }

        Message::create([
            'chat_id' => $chat_id,
            'message' => $validated['message'],
        ]);

        $answer = $this->findBestAnswer($validated['message'], $site_id);

        if(!$answer){
            $answer = "A kérdésedre nem sikerült helyes választ találni, hamarosan megválaszolja egy munkatárs a kérdésedet.";
            $chat->status = ChatStatusEnum::WAITING;
            $chat->save();
        }

        Log::info("Bot válasz lekérdezés után: $answer");

        Message::create([
            'chat_id' => $chat_id,
            'message' => $answer,
            'sender_role' => MessageSenderRolesEnum::BOT,
        ]);

        //Log::info("Bot üzenet mentése adatbázisba: $bot_message->id");

        return response()->json([
            'message' => 'Az üzenet sikeresen elküldve!',
            'data' => [
                'chat_id' => $chat_id,
            ]
        ], 200);
    }
}
