<?php

namespace App\Jobs;

use App\Models\Document;
use App\Actions\{ExtractTextAction, ChunkTextAction, StoreEmbeddingsAction};
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};
use Illuminate\Foundation\Bus\Dispatchable;

class ProcessDocumentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $site_id,
        public Document $document,
    ) {}

    public function handle(
        ExtractTextAction $extractText,
        ChunkTextAction $chunkText,
        StoreEmbeddingsAction $storeEmbeddings
    ): void
    {
        if (!$text = $extractText->execute($this->document->path)) {
            \Log::error("Extraction failed: {$this->document->path}");
            return;
        }

        $chunks = $chunkText->execute($text);
        $storeEmbeddings->execute($chunks, $this->site_id, $this->document->id);
    }
}
