<?php

namespace App\Actions;

use App\Models\DocumentChunk;
use EchoLabs\Prism\Prism;
use EchoLabs\Prism\Enums\Provider;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class StoreEmbeddingsAction
{
    private Client $http_client;
    private string $qdrant_url;
    private string $collection_name = 'document_chunks';

    public function __construct()
    {
        $this->http_client = new Client([
            'timeout' => config('qdrant.timeout', 30),
            'headers' => [
                'Content-Type' => 'application/json'
            ]
        ]);
        $this->qdrant_url = rtrim(config('qdrant.url', 'http://localhost:6333'), '/');
    }

    /**
     * @throws Exception
     */
    public function execute(array $chunks, int $site_id, int $document_id): void
    {
        $points = [];

        foreach ($chunks as $i => $text) {
            try {
                $response = Prism::embeddings()
                    ->using(Provider::OpenAI, 'text-embedding-3-large')
                    ->fromInput($text)
                    ->withClientOptions(['timeout' => 60])
                    ->withClientRetry(3, 1000)
                    ->generate();

                $vector = $response->embeddings;

                if(DocumentChunk::where('hash', hash('sha256', $text))->exists()) {
                    Log::info("Chunk already exists in database, skipping", [
                        'chunk_index' => $i,
                        'text_preview' => substr($text, 0, 100)
                    ]);
                    continue;
                }else{
                    $document_chunk = DocumentChunk::create([
                        'document_id' => $document_id,
                        'chunk_index' => $i,
                        'text' => $text,
                        'token_count' => count(explode(' ', $text)),
                        'hash' => hash('sha256', $text),
                        'embedding' => json_encode($vector),
                    ]);
                }

                $points[] = [
                    'id' => $document_chunk->id,
                    'vector' => $vector,
                    'payload' => [
                        'site_id' => $site_id,
                        'chunk_index' => $i,
                        'text_preview' => substr($text, 0, 100),
                        'created_at' => now()->toISOString(),
                    ]
                ];

                Log::info("Embedding created for chunk {$i}", [
                    'vector_length' => count($vector),
                    'text_length' => strlen($text)
                ]);

            } catch (Exception $e) {
                Log::error("Failed to create embedding for chunk {$i}: " . $e->getMessage());
                throw new Exception("Failed to create embedding for chunk {$i}: " . $e->getMessage());
            }
        }

        // Batch upsert minden point egyszerre
        $this->upsertPoints($points);
    }

    /**
     * Pointok batch feltöltése Qdrant-ba
     * @throws Exception
     */
    private function upsertPoints(array $points): void
    {
        if (empty($points)) {
            Log::warning('No points to upsert');
            return;
        }

        try {
            $response = $this->http_client->put("{$this->qdrant_url}/collections/{$this->collection_name}/points", [
                'json' => [
                    'points' => $points
                ]
            ]);

            $statusCode = $response->getStatusCode();
            if ($statusCode < 200 || $statusCode >= 300) {
                throw new Exception("Qdrant returned status code: {$statusCode}");
            }

            $result = json_decode($response->getBody(), true);
            if (isset($result['status']) && $result['status'] === 'error') {
                throw new Exception("Qdrant error: " . ($result['error'] ?? 'Unknown error'));
            }

            Log::info("Successfully upserted " . count($points) . " points to Qdrant");

        } catch (GuzzleException $e) {
            Log::error("Qdrant batch upsert failed: " . $e->getMessage());

            // Fallback: egyenként próbáljuk meg
            $this->upsertPointsIndividually($points);
        }
    }

    /**
     * Fallback: pointok egyenkénti feltöltése
     * @throws Exception
     */
    private function upsertPointsIndividually(array $points): void
    {
        Log::info("Attempting individual upserts as fallback");
        $successful = 0;
        $failed = 0;

        foreach ($points as $point) {
            try {
                $response = $this->http_client->put("{$this->qdrant_url}/collections/{$this->collection_name}/points", [
                    'json' => [
                        'points' => [$point]
                    ]
                ]);

                if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
                    $successful++;
                } else {
                    $failed++;
                    Log::error("Failed to upsert individual point", [
                        'point_id' => $point['id'],
                        'status_code' => $response->getStatusCode()
                    ]);
                }

            } catch (GuzzleException $e) {
                $failed++;
                Log::error("Individual point upsert failed", [
                    'point_id' => $point['id'],
                    'error' => $e->getMessage()
                ]);
            }
        }

        Log::info("Individual upsert results", [
            'successful' => $successful,
            'failed' => $failed,
            'total' => count($points)
        ]);

        if ($successful === 0) {
            throw new Exception("All individual upserts failed");
        }
    }

    /**
     * Kollekció létrehozása
     * @throws Exception
     */
    public function createCollection(): array
    {
        $config = config('qdrant.collections.document_chunks', [
            'vector_size' => 3072, // text-embedding-3-large
            'distance' => 'Cosine'
        ]);

        try {
            $response = $this->http_client->put("{$this->qdrant_url}/collections/{$this->collection_name}", [
                'json' => [
                    'vectors' => [
                        'size' => $config['vector_size'],
                        'distance' => $config['distance']
                    ]
                ]
            ]);

            $result = json_decode($response->getBody(), true);
            Log::info("Qdrant collection created successfully", $result);
            return $result;

        } catch (GuzzleException $e) {
            Log::error('Qdrant collection creation failed: ' . $e->getMessage());
            throw new Exception('Failed to create Qdrant collection: ' . $e->getMessage());
        }
    }

    /**
     * Kollekció létezésének ellenőrzése
     */
    public function collectionExists(): bool
    {
        try {
            $response = $this->http_client->get("{$this->qdrant_url}/collections/{$this->collection_name}");
            return $response->getStatusCode() === 200;
        } catch (GuzzleException $e) {
            return false;
        }
    }

    /**
     * Kollekció létrehozása ha nem létezik
     * @throws Exception
     */
    public function ensureCollectionExists(): void
    {
        if (!$this->collectionExists()) {
            Log::info("Collection doesn't exist, creating it");
            $this->createCollection();
        } else {
            Log::info("Collection already exists");
        }
    }
}
