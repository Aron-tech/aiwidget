<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionAnswer extends Model
{
    /** @use HasFactory<\Database\Factories\QuestionAnswerFactory> */
    use HasFactory;

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

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
