<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            abort(403, 'Huna ruhusa. Admin pekee.');
        }

        // Allow returning from impersonation while logged in as another user
        if ($request->routeIs('admin.stop-impersonate') && Session::has('admin_id')) {
            return $next($request);
        }

        if (Auth::user()->role !== 'admin') {
            abort(403, 'Huna ruhusa. Admin pekee.');
        }

        return $next($request);
    }
}

