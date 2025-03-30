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
        $type = auth()->user()->keys()->first()->type ?? null;
        
        if(empty($type) || KeyTypesEnum::DEVELOPER !== $type) {
            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}
