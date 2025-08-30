<?php

namespace App\Actions;

use App\Models\DocumentChunk;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\Concerns\AsAction;

class SearchDocumentsAction
{
    use AsAction;
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
     * Dokumentumokban való keresés és válasz generálás
     */
    public function execute(string $query, int $site_id, int $top_k = 5): array
    {
        try {

            $token_count = 0;
            // 1. Query embedding generálása

            $embedding_response = GenerateEmbeddingAction::run($query);
            $queryEmbedding = $embedding_response->embeddings;
            $token_count += $embedding_response->usage->tokens;

            // 2. Releváns chunk-ok keresése Qdrant-ban
            $searchResults = $this->searchInQdrant($queryEmbedding, $site_id, $top_k);

            // 3. Chunk-ok részleteinek lekérése az adatbázisból
            $enrichedResults = $this->enrichWithDatabaseData($searchResults);

            // 4. Válasz generálása a releváns kontextus alapján
            $answer = $this->generateAnswer($query, $enrichedResults);

            return [
                'token_count' => $token_count,
                'query' => $query,
                'answer' => $answer,
                'sources' => $this->formatSources($enrichedResults),
                'search_results' => $searchResults,
                'context_used' => count($enrichedResults)
            ];

        } catch (\Exception $e) {
            Log::error('Search failed', [
                'query' => $query,
                'site_id' => $site_id,
                'error' => $e->getMessage()
            ]);

            return [
                'query' => $query,
                'answer' => 'Sajnálom, nem tudtam megválaszolni a kérdést a rendelkezésre álló dokumentumok alapján. Kérlek próbáld meg másképpen megfogalmazni a kérdést.',
                'sources' => [],
                'search_results' => [],
                'context_used' => 0,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Keresés Qdrant vektortárban
     */
    private function searchInQdrant(array $queryEmbedding, int $site_id, int $top_k): array
    {
        try {
            $response = $this->http_client->post("{$this->qdrant_url}/collections/{$this->collection_name}/points/search", [
                'json' => [
                    'vector' => $queryEmbedding,
                    'limit' => $top_k * 2, // Több eredményt kérünk, hogy legyen választék
                    'with_payload' => true,
                    'score_threshold' => 0.3, // Minimum hasonlósági küszöb
                    'filter' => [
                        'must' => [
                            [
                                'key' => 'site_id',
                                'match' => [
                                    'value' => $site_id
                                ]
                            ]
                        ]
                    ]
                ]
            ]);

            $result = json_decode($response->getBody(), true);
            $results = $result['result'] ?? [];

            Log::info('Qdrant search completed', [
                'query_embedding_size' => count($queryEmbedding),
                'results_count' => count($results),
                'site_id' => $site_id
            ]);

            return $results;

        } catch (GuzzleException $e) {
            Log::error("Qdrant search failed: " . $e->getMessage());

            // Fallback: keresés filter nélkül, majd manuális szűrés
            return $this->fallbackSearch($queryEmbedding, $site_id, $top_k);
        }
    }

    /**
     * Fallback keresés ha a szűrő nem működik
     */
    private function fallbackSearch(array $queryEmbedding, int $site_id, int $top_k): array
    {
        try {
            Log::info("Using fallback search without filters");

            $response = $this->http_client->post("{$this->qdrant_url}/collections/{$this->collection_name}/points/search", [
                'json' => [
                    'vector' => $queryEmbedding,
                    'limit' => $top_k * 3,
                    'with_payload' => true,
                    'score_threshold' => 0.3
                ]
            ]);

            $result = json_decode($response->getBody(), true);
            $points = $result['result'] ?? [];

            // Manuális szűrés site_id alapján
            $filtered = array_filter($points, function($point) use ($site_id) {
                return isset($point['payload']['site_id']) && $point['payload']['site_id'] == $site_id;
            });

            return array_slice(array_values($filtered), 0, $top_k);

        } catch (GuzzleException $e) {
            Log::error("Fallback search also failed: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Keresési eredmények kiegészítése adatbázis adatokkal
     */
    private function enrichWithDatabaseData(array $searchResults): array
    {
        if (empty($searchResults)) {
            return [];
        }

        // Chunk ID-k gyűjtése (a Qdrant-ban tárolt ID-k DocumentChunk ID-k)
        $chunkIds = array_map(function($result) {
            return $result['id'];
        }, $searchResults);

        // Chunk-ok lekérése az adatbázisból kapcsolt adatokkal
        $chunks = DocumentChunk::with(['document'])
            ->whereIn('id', $chunkIds)
            ->get()
            ->keyBy('id');

        $enrichedResults = [];
        foreach ($searchResults as $result) {
            $chunk = $chunks->get($result['id']);
            if ($chunk) {
                $enrichedResults[] = [
                    'score' => $result['score'],
                    'chunk' => $chunk,
                    'payload' => $result['payload'],
                    'text' => $chunk->text,
                    'document_title' => $chunk->document->title ?? 'Unknown Document',
                    'document_id' => $chunk->document_id,
                ];
            }
        }

        // Pontszám szerint rendezés (magas -> alacsony)
        usort($enrichedResults, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        return $enrichedResults;
    }

    /**
     * Válasz generálása AI-val a releváns kontextus alapján
     */
    private function generateAnswer(string $query, array $enrichedResults): ?string
    {
        if (empty($enrichedResults)) {
            return 'Nem találtam releváns információt a kérdésedre a feltöltött dokumentumokban.';
        }

        // Kontextus összeállítása
        $contexts = [];
        foreach (array_slice($enrichedResults, 0, 5) as $result) { // Max 5 legjobb eredmény
            $contexts[] = [
                'document' => $result['document_title'],
                'text' => $result['text'],
                'relevance_score' => round($result['score'], 3)
            ];
        }

        $contextText = implode("\n\n---\n\n", array_map(function($ctx) {
            return "Dokumentum: {$ctx['document']}\nTartalom: {$ctx['text']}";
        }, $contexts));

        try {
            $response = GenerateTextAction::run($this->getSystemPrompt(), "Kontextus:\n{$contextText}\n\nKérdés: {$query}\n\nKérlek válaszolj a kérdésre a fenti kontextus alapján a kérdés nyelvén nyelven.");

            return $response->text ?? __('interface.could_not_process_request');

        } catch (\Exception $e) {
            Log::error('Answer generation failed', [
                'query' => $query,
                'contexts_count' => count($contexts),
                'error' => $e->getMessage()
            ]);

            return __('interface.could_not_process_request');
        }
    }

    /**
     * System prompt a válaszgeneráláshoz
     */
    private function getSystemPrompt(): string
    {
        return "Te egy hasznos asszisztens vagy, aki dokumentumok alapján válaszol kérdésekre.

        Feladatod:
        1. Alaposan olvasd el a megadott kontextust
        2. Válaszolj a kérdésre CSAK a kontextus alapján
        3. Ha a kontextusban nincs elegendő információ, akkor ezt közöld
        4. Kérdés nyelvén(pl: magyar, angol) válaszolj, világosan és érthetően

        Szabályok:
        - NE találj ki információkat
        - NE válaszolj általános tudásod alapján
        - Csak a megadott kontextust használd
        - Ha bizonytalan vagy, inkább mondd el, hogy nincs elegendő információ
        - Légy precíz és tömör, de teljes körű";
    }

    /**
     * Források formázása a válaszhoz
     */
    private function formatSources(array $enrichedResults): array
    {
        $sources = [];
        $seenDocuments = [];

        foreach ($enrichedResults as $result) {
            $docId = $result['document_id'];

            if (!isset($seenDocuments[$docId])) {
                $sources[] = [
                    'document_id' => $docId,
                    'document_title' => $result['document_title'],
                    'relevance_score' => round($result['score'], 3),
                    'chunk_count' => 1
                ];
                $seenDocuments[$docId] = count($sources) - 1;
            } else {
                $sources[$seenDocuments[$docId]]['chunk_count']++;
                // Legjobb pontszámot tartjuk meg
                $sources[$seenDocuments[$docId]]['relevance_score'] = max(
                    $sources[$seenDocuments[$docId]]['relevance_score'],
                    round($result['score'], 3)
                );
            }
        }

        // Relevancia szerint rendezés
        usort($sources, function($a, $b) {
            return $b['relevance_score'] <=> $a['relevance_score'];
        });

        return $sources;
    }

    /**
     * Csak keresés válaszgenerálás nélkül
     */
    public function searchOnly(string $query, int $site_id, int $top_k = 10): array
    {
        try {
            $query_embedding_response = GenerateEmbeddingAction::run($query);
            $query_embedding = $query_embedding_response->embeddings;
            $search_results = $this->searchInQdrant($query_embedding, $site_id, $top_k);
            $enrichedResults = $this->enrichWithDatabaseData($search_results);

            return [
                'token_count' => $query_embedding_response->usage->tokens ?? 0,
                'query' => $query,
                'results' => $enrichedResults,
                'total_found' => count($enrichedResults)
            ];

        } catch (\Exception $e) {
            Log::error('Search-only failed', [
                'query' => $query,
                'site_id' => $site_id,
                'error' => $e->getMessage()
            ]);

            return [
                'query' => $query,
                'results' => [],
                'total_found' => 0,
                'error' => $e->getMessage()
            ];
        }
    }
}
