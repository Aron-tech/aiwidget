<?php

namespace App\Models;

use App\Enums\FileStatusEnum;
use App\Enums\FileTypeEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'site_id',
        'title',
        'path',
        'type',
        'status',
    ];

    protected $casts = [
        'type' => FileTypeEnum::class,
        'status' => FileStatusEnum::class,
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function chunks(): HasMany
    {
        return $this->hasMany(DocumentChunk::class);
    }
}
