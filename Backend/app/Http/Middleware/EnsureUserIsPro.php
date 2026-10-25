<?php

namespace App\Http\Middleware;

use App\Helpers\ResponseHelper;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsPro
{
    /**
     * Handle an incoming request.
     * Restricts access unless user has an active Pro subscription or 7-day trial.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return ResponseHelper::send(401, 'Unauthenticated.');
        }

        if (! $user->hasActivePro()) {
            return ResponseHelper::send(403, 'Pro subscription required. Upgrade your plan or start your 7-day free trial to access Daily Missions and Goal Tracking.', [
                'requires_pro' => true,
                'is_pro' => false,
                'subscription_status' => $user->subscription_status ?? 'free',
            ]);
        }

        return $next($request);
    }
}
