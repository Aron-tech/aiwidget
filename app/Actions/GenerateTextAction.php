<?php

namespace App\Actions;

use EchoLabs\Prism\Enums\Provider;
use EchoLabs\Prism\Prism;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\Concerns\AsAction;

class GenerateTextAction
{
    use AsAction;

    /**
     * @throws \Exception
     */
    public function handle(string $system_prompt, string $prompt, string $model = 'gpt-4o-mini', int $timeout = 60, int $client_retry = 3, int $sleep_milliseconds = 1000)
    {
        try {
            return Prism::text()
                ->using(Provider::OpenAI, $model)
                ->withSystemPrompt($system_prompt)
                ->withPrompt($prompt)
                ->withClientOptions(['timeout' => $timeout])
                ->withClientRetry($client_retry, $sleep_milliseconds)
                ->generate();
        }catch (\Exception $e) {
            Log::error('Text generation failed', [
                'error' => $e->getMessage()
            ]);
            throw new \Exception('Failed to generate text: ' . $e->getMessage());
        }
    }
}
