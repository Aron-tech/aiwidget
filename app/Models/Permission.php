<?php

namespace App\Models;

use App\Enums\PermissionTypesEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    /** @use HasFactory<\Database\Factories\PermissionFactory> */
    use HasFactory;

    protected $guarded = ['id'];

    protected $fillable = [
        'value',
    ];

    public function keys()
    {
        return $this->belongsToMany(Key::class, 'keys_permissions');
    }

    public function getTypeAttribute($value): PermissionTypesEnum
    {
        return PermissionTypesEnum::from($value);
    }

    public function setTypeAttribute(PermissionTypesEnum $value): void
    {
        $this->attributes['type'] = $value->value;
    }

    public static function fromEnum(): array
    {
        return PermissionTypesEnum::cases();
    }
}
