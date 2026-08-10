<?php

namespace App\Http\Middleware;

use App\Models\DailyActiveUser;
use App\Models\UserActivityLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogUserActivity
{
    /**
     * Handle an incoming request.
     * Logs user activity and updates daily active user records.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only log for authenticated users
        if ($request->user()) {
            $this->logActivity($request);
            $this->updateDailyActiveUser($request);
        }

        return $response;
    }

    /**
     * Log the user's activity to the user_activity_logs table.
     */
    protected function logActivity(Request $request): void
    {
        $routeName = $request->route()?->getName() ?? $request->path();
        $action = $this->resolveAction($request);

        UserActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => $action,
            'description' => $this->resolveDescription($action, $request),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'route' => $request->path(),
            'method' => $request->method(),
            'logged_at' => now(),
        ]);
    }

    /**
     * Update or create a daily active user record for today.
     */
    protected function updateDailyActiveUser(Request $request): void
    {
        $today = now()->toDateString();
        $userId = $request->user()->id;

        $record = DailyActiveUser::where('user_id', $userId)
            ->where('active_date', $today)
            ->first();

        if ($record) {
            $record->update([
                'last_seen_at' => now(),
                'request_count' => $record->request_count + 1,
            ]);
        } else {
            DailyActiveUser::create([
                'user_id' => $userId,
                'active_date' => $today,
                'first_seen_at' => now(),
                'last_seen_at' => now(),
                'request_count' => 1,
            ]);
        }
    }

    /**
     * Resolve a human-readable action name from the request.
     */
    protected function resolveAction(Request $request): string
    {
        $routeName = $request->route()?->getName();
        if ($routeName) {
            return $routeName;
        }

        // Fallback: derive from URI path
        $path = $request->path();
        $segments = explode('/', $path);
        $lastSegment = end($segments);

        return $lastSegment ?: 'unknown';
    }

    /**
     * Generate a human-readable description based on the action.
     */
    protected function resolveDescription(string $action, Request $request): string
    {
        $descriptions = [
            'login' => 'User logged in',
            'register' => 'User registered',
            'logout' => 'User logged out',
            'profile' => 'Viewed profile',
            'update-profile' => 'Updated profile',
            'update-password' => 'Changed password',
            'chat' => 'Sent a chat message',
            'conversations' => 'Viewed conversations',
            'messages' => 'Viewed messages',
            'feedback' => 'Submitted feedback',
            'delete-account' => 'Deleted account',
        ];

        return $descriptions[$action] ?? 'Performed action: ' . $action;
    }
}
