<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\SiteSelector;

class ValidateSiteSelection
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $site_selector = app(SiteSelector::class);

        if (!$site_selector->getSite() && !$site_selector) {
            return redirect()->route('site.picker');
        }

        return $next($request);
    }
}
