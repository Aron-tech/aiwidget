<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Hidehalo\Nanoid\Client;

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

        public function getRouteKeyName(): string
        {
            return 'uuid';
        }

        protected static function boot()
        {
            parent::boot();
            static::creating(function ($model) {
                if (empty($model->uuid)) {
                    $nano = new Client();
                    $model->uuid = $nano->generateId(24); // 24 karakter hosszú Nano ID
                }
            });
        }

    protected $casts = [
        'settings' => 'json',
    ];

    public function users()
    {
        return $this->hasManyThrough(User::class, Key::class, 'site_id', 'id', 'id', 'user_id');
    }

    public function chats()
    {
        return $this->hasMany(Chat::class);
    }

    public function keys()
    {
        return $this->hasMany(Key::class);
    }

    public function questionAnswers()
    {
        return $this->hasMany(QuestionAnswer::class);
    }
}
