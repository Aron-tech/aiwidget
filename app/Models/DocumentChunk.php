<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentChunk extends Model
{
    protected $fillable = [
        'document_id',
        'chunk_index',
        'text',
        'token_count',
        'hash',
        'embedding',
        'meta_data',
    ];

    protected $casts = [
        'embedding' => 'json',
        'meta_data' => 'json',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }
}
