<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Enums\KeyTypesEnum;

class VerifyAdminRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(!auth()->user()->keys()->where('type', KeyTypesEnum::DEVELOPER)->exists()) {
            return redirect()->route('site.picker');
        }

        return $next($request);
    }
}
