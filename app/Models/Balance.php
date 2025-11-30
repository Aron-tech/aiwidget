<?php

namespace App\Models;

use App\Enums\BalanceTransactionTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Balance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'key_id',
        'amount',
        'type',
        'description',
    ];

    protected $casts = [
        'type' => BalanceTransactionTypeEnum::class,
    ];

    public function key(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function site(): HasOneThrough
    {
        return $this->hasOneThrough(Site::class, Key::class, 'id', 'id', 'key_id', 'site_id');
    }
}
