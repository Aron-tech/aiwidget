<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Key extends Model
{
    /** @use HasFactory<\Database\Factories\KeyFactory> */
    use HasFactory;

    protected $guarded = ['id'];

    protected $fillable = [
        'token',
        'site_id',
        'user_id',
        'type',
        'expiration_time',
    ];

    public function setTokenAttribute($value)
    {
        $this->attributes['token'] = hash('sha256', $value);
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->expiration_time)) {
                $model->expiration_time = now()->addDays(360);
            }
        });
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
