<?php

namespace App\Jobs;

use App\Actions\GenerateEmbeddingAction;
use App\Models\QuestionAnswer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateEmbedding implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected QuestionAnswer $question_answer;

    public function __construct(QuestionAnswer $question_answer)
    {
        $this->question_answer = $question_answer;
    }

    public function handle(): void
    {
        $embedding = $this->getEmbedding($this->questionAnswer->question);
        $this->questionAnswer->embedding = json_encode($embedding);
        $this->questionAnswer->save();
    }

    private function preprocessText($text)
    {
        $text = strtolower($text); // Kisbetűssé alakítás
        $text = trim($text); // Felesleges szóközök eltávolítása
        $text = preg_replace('/[^\p{L}\p{N}\s]/u', '', $text); // Speciális karakterek eltávolítása
        return $text;
    }


    private function getEmbedding($text)
    {
        $text = $this->preprocessText($text);

        $response = Prism::embeddings()
            ->using(Provider::OpenAI, 'text-embedding-3-large')
            ->fromInput($text)
            ->withClientOptions(['timeout' => 30])
            ->withClientRetry(3, 100)
            ->generate();

        return $response->embeddings;
        $embedding_response = GenerateEmbeddingAction::run($this->question_answer->question);
        $this->question_answer->embedding = json_encode($embedding_response->embenddings);
        $this->question_answer->save();
    }
}
