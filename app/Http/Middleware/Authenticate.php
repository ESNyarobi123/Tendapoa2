<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    protected function redirectTo($request): ?string
    {
        // For API routes, always return JSON response instead of redirecting
        if ($request->is('api/*')) {
            return null;
        }
        
        if (!$request->expectsJson()) {
            return route('login');
        }
        return null;
    }

    /**
     * Handle an unauthenticated user.
     */
    protected function unauthenticated($request, array $guards)
    {
        // For API routes, return JSON response
        if ($request->is('api/*')) {
            return response()->json([
                'success' => false,
                'message' => 'Huna ruhusa. Tafadhali ingia kwanza.',
                'error' => 'Unauthenticated'
            ], 401);
        }

        // For web routes, use default behavior
        return parent::unauthenticated($request, $guards);
    }
}
