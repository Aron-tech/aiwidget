<?php

namespace App\Actions;

use EchoLabs\Prism\Embeddings\Response;
use EchoLabs\Prism\Enums\Provider;
use EchoLabs\Prism\Exceptions\PrismException;
use EchoLabs\Prism\Prism;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\Concerns\AsAction;

class GenerateEmbeddingAction
{
    use AsAction;

    public function handle(string $text, int $timeout = 60, int $client_retry = 3, int $sleep_milliseconds = 1000): ?Response
    {
        try {
            return Prism::embeddings()
                ->using(Provider::OpenAI, 'text-embedding-3-large')
                ->fromInput($text)
                ->withClientOptions(['timeout' => $timeout])
                ->withClientRetry($client_retry, 1000)
                ->generate();
        } catch (PrismException $e) {
            Log::error('Embeddings generation failed:', [
                'error' => $e->getMessage()
            ]);
        }
        return null;
    }
}
