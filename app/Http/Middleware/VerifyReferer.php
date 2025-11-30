<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Site;
use Illuminate\Support\Facades\Log;

class VerifyReferer
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $referer = $request->server('HTTP_REFERER');

        $uuid = $request->route('site')['uuid'];

        $site = Site::where('uuid', $uuid)->firstOrFail();

        $referer_host = parse_url($referer, PHP_URL_HOST);
        $site_host = parse_url($site->domain, PHP_URL_HOST) ?: $site->domain;

        if ($referer_host !== $site_host)
            abort(403, 'Érvénytelen azonosító!');

        return $next($request);
    }
}
