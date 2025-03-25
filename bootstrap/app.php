<?php

use App\Http\Middleware\VerifyReferer;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\HandleCors;
use App\Http\Middleware\CheckSiteInUrl;
use \Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\AppLang;
use App\Http\Middleware\ValidateSiteSelection;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        RedirectIfAuthenticated::redirectUsing(fn() => route('site.picker'));
        $middleware->web(append: [
            AppLang::class
        ]);
        $middleware->append(HandleCors::class);
        $middleware->prepend(HandleCors::class, [
            'paths' => ['api/*'],
            'allowed_methods' => ['*'],
            'allowed_origins' => ['*'],
            'allowed_headers' => ['*'],
            'exposed_headers' => [],
            'max_age' => 0,
            'supports_credentials' => false,
        ]);
        $middleware->alias([
            'verify.referer' => VerifyReferer::class,
            'check_site_in_url' => CheckSiteInUrl::class,
            'validate_site_selection' => ValidateSiteSelection::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
