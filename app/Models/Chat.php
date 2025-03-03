<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    /** @use HasFactory<\Database\Factories\ChatFactory> */
    use HasFactory;

    protected $guarded = ['id'];

    protected $fillable = [
        'visitor_name',
        'visitor_email',
        'status',
        'site_id',
    ];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}
