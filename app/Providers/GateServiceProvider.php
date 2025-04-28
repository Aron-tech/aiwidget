<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Gates\PermissionGate;

class GateServiceProvider extends ServiceProvider
{
    /**
     * A szolgáltatás indítása.
     *
     * @return void
     */
    public function boot()
    {
        // Regisztráljuk a PermissionGate-et
        PermissionGate::register();
    }

    /**
     * A szolgáltatás regisztrálása.
     *
     * @return void
     */
    public function register()
    {
        // Ide nem szükséges semmit tenni
    }
}
