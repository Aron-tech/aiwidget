<?php

namespace App\Models;

use App\Enums\KeyTypesEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Traits\Searchable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Enums\PermissionTypesEnum;

class Key extends Model
{
    use HasFactory, Searchable;

    protected array $searchable = ['token'];

    protected $guarded = ['id'];

    protected $fillable = [
        'token',
        'site_id',
        'user_id',
        'type',
        'expiration_time',
    ];

    protected $casts = [
        'type' => KeyTypesEnum::class,
        'expiration_time' => 'datetime',
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

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'keys_permissions');
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignPermission(PermissionTypesEnum $permission): void
    {
        $permission_id = Permission::where('value', $permission->value)->pluck('id')->first();
        $this->permissions()->syncWithoutDetaching([$permission_id]);
    }

    public function assignMultiplePermissions(array $permissions): void
    {
        $permission_ids = Permission::whereIn('value',
            array_map(fn($type) => $type->value, $permissions)
        )->pluck('id');

        $this->permissions()->syncWithoutDetaching($permission_ids);
    }
}
