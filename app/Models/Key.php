<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'activated',
    ];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
