<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     * Ensures the authenticated user is an admin and has completed 2FA verification.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('admin.login')
                ->with('error', 'Please log in to access the admin panel.');
        }

        if (!auth()->user()->is_admin) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('admin.login')
                ->with('error', 'You do not have admin privileges.');
        }

        $routeName = $request->route() ? $request->route()->getName() : '';
        $allowedRoutes = [
            'admin.2fa.setup',
            'admin.2fa.confirm',
            'admin.2fa.challenge',
            'admin.2fa.verify',
            'admin.logout',
        ];

        if (in_array($routeName, $allowedRoutes)) {
            return $next($request);
        }

        if (!session('admin_2fa_passed')) {
            if (!auth()->user()->google2fa_enabled) {
                return redirect()->route('admin.2fa.setup');
            }
            return redirect()->route('admin.2fa.challenge');
        }

        return $next($request);
    }
}
