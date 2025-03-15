<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckSiteInUrl
{
    public function handle(Request $request, Closure $next)
    {
        auth()->user()->sites->contains('uuid', $request->route('site')) ?:
            abort(403, 'interface.dont_have_access_to_site');

        return $next($request);
    }
}
