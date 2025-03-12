<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Str;

class Site extends Model
{
    /** @use HasFactory<\Database\Factories\SiteFactory> */
    use HasFactory;

    protected $guarded = ['id'];

    protected $fillable = [
        'name',
        'uuid',
        'domain',
        'user_id',
        'key_id',
        'settings',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    protected $casts = [
        'settings' => 'json',
    ];

    public function users(): HasManyThrough
    {
        return $this->hasManyThrough(User::class, Key::class, 'site_id', 'id', 'id', 'user_id');
    }

    public function chats(): HasMany
    {
        return $this->hasMany(Chat::class);
    }

    public function keys(): HasMany
    {
        return $this->hasMany(Key::class);
    }

    public function questionAnswers(): HasMany
    {
        return $this->hasMany(QuestionAnswer::class);
    }
}
