<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\MessageSenderRolesEnum;

class Message extends Model
{
    /** @use HasFactory<\Database\Factories\MessageFactory> */
    use HasFactory;

    protected $guarded = ['id'];

    protected $fillable = [
        'chat_id',
        'message',
        'sender_role',
    ];

    protected $casts = [
        'sender_role' => MessageSenderRolesEnum::class,
    ];

    public function chat(): BelongsTo
    {
        return $this->belongsTo(Chat::class);
    }
}
