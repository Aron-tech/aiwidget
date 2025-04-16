<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Jobs\GenerateEmbedding;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Traits\Searchable;

class QuestionAnswer extends Model
{
    use HasFactory, Searchable;

    protected array $searchable = ['question', 'answer'];

    protected $guarded = ['id'];

    protected $fillable = [
        'site_id',
        'question',
        'answer',
        'embedding',
    ];

    protected $casts = [
        'embedding' => 'json',
    ];

    protected static function boot()
    {
        parent::boot();

        static::created(function ($questionAnswer) {
            GenerateEmbedding::dispatch($questionAnswer);
        });

        static::updated(function ($questionAnswer) {
            if ($questionAnswer->isDirty('question')) {
                GenerateEmbedding::dispatch($questionAnswer);
            }
        });
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
