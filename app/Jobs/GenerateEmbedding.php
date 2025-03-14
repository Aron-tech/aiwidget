<?php

namespace App\Jobs;

use App\Models\QuestionAnswer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use EchoLabs\Prism\Prism;
use EchoLabs\Prism\Enums\Provider;

class GenerateEmbedding implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $questionAnswer;

    public function __construct(QuestionAnswer $questionAnswer)
    {
        $this->questionAnswer = $questionAnswer;
    }

    public function handle()
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
    }
}
