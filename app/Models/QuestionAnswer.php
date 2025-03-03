<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function site()
    {
        return $this->belongsTo(Site::class);
    }
}
