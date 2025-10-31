<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsApproved
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!$request->user()) {
            return redirect()->route('login');
        }

        // Check if user is approved
        if (!$request->user()->isApproved()) {
            // Logout the user
            auth()->logout();

            // Redirect to login with error message
            return redirect()->route('login')
                ->with('error', 'Tu cuenta está pendiente de aprobación. Un administrador debe aprobar tu cuenta antes de que puedas acceder.');
        }

        return $next($request);
    }
}
