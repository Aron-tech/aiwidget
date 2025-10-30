<?php

namespace App\Gates;

use App\Enums\PermissionTypesEnum;
use App\Enums\KeyTypesEnum;
use App\Models\SiteSelector;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class PermissionGate
{
    public static function register()
    {
        Gate::define('hasPermission', function (User $user, PermissionTypesEnum $permission) {
            $site = SiteSelector::getSite()
                ->load(['keys' => function ($query) use ($user) {
                    $query->where('user_id', $user->id)
                        ->with('permissions');
                }]);

            if(empty($site))
                redirect()->route('site.picker')->wiht('error', __('interface.missing_site'));

            $key = $site->keys[0];

            if (empty($key))
                return false;

            if(empty($key->permissions))
                return false;

            if ($key->type === KeyTypesEnum::CUSTOMER || $key->type === KeyTypesEnum::DEVELOPER)
                return true;

            return $key->permissions->contains('value', $permission);
        });
    }
}
