<?php

namespace App\Livewire\Traits;

use App\Enums\PermissionTypesEnum;
use App\Enums\KeyTypesEnum;
use App\Models\SiteSelector;

trait RequiresPermission
{
    private function showPermissionError()
    {
        $this->dispatch('notify', 'danger', __('interface.no_permission'));
    }

    protected function hasPermission(PermissionTypesEnum $permission): bool
    {
        $site_selector = new SiteSelector();
        $site_id = $site_selector->getSite()->id;

        $key = auth()->user()->keys()
            ->whereHas('site', fn($query) => $query->where('id', $site_id))
            ->with('permissions')
            ->first();

        if (empty($key)) {
            $this->showPermissionError();
            return false;
        }

        if ($key->type === KeyTypesEnum::OWNER || $key->type === KeyTypesEnum::DEVELOPER) {
            return true;
        }

        return $key->permissions->contains('value', $permission) ? true : $this->showPermissionError() && false;
    }
}