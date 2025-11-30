<?php

namespace App\Actions;

use EchoLabs\Prism\Embeddings\Response;
use EchoLabs\Prism\Enums\Provider;
use EchoLabs\Prism\Prism;
use Exception;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\Concerns\AsAction;

class GenerateEmbeddingAction
{
    use AsAction;

    public function handle(string $text, bool $is_preprocess = true, int $timeout = 60, int $client_retry = 3, int $sleep_milliseconds = 1000): Response
    {
        try {
            if($is_preprocess){
                $this->preprocessText($text);
            }

            return Prism::embeddings()
                ->using(Provider::OpenAI, 'text-embedding-3-large')
                ->fromInput($text)
                ->withClientOptions(['timeout' => $timeout])
                ->withClientRetry($client_retry, $sleep_milliseconds)
                ->generate();
        } catch (Exception $e) {
            Log::error('Embedding generation failed', [
                'text' => substr($text, 0, 100),
                'error' => $e->getMessage()
            ]);
            throw new \Exception('Failed to generate embedding: ' . $e->getMessage());
        }
    }
    private function preprocessText($text): ?string
    {
        $text = strtolower($text); // Kisbetűssé alakítás
        $text = trim($text); // Felesleges szóközök eltávolítása
        return preg_replace('/[^\p{L}\p{N}\s]/u', '', $text); // Speciális karakterek eltávolítása
    }
}
