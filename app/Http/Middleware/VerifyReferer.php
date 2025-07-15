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

        //Log::info($referer);

        $uuid = $request->route('site')['uuid'];

        $site = Site::where('uuid', $uuid)->firstOrFail();

        if ($referer !== $site->domain)
            abort(403, 'Érvénytelen azonosító!');

        return $next($request);
    }
}
