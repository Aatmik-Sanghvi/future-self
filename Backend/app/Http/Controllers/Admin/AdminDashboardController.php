<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DailyActiveUser;
use App\Models\Feedback;
use App\Models\User;
use App\Models\UserActivityLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    /**
     * Dashboard with summary stats and charts data.
     */
    public function index(Request $request)
    {
        $today = Carbon::today();
        $startOfWeek = Carbon::now()->startOfWeek();

        // Summary stats
        $totalUsers = User::where('is_admin', false)->count();
        $dauToday = DailyActiveUser::where('active_date', $today)->count();
        $activityLogsToday = UserActivityLog::whereDate('logged_at', $today)->count();
        $newUsersThisWeek = User::where('is_admin', false)
            ->where('created_at', '>=', $startOfWeek)
            ->count();

        // --- Chart Data ---

        // 1. DAU chart (last 7 days)
        $dauChartData = DailyActiveUser::select(
                DB::raw('DATE(active_date) as active_date'),
                DB::raw('COUNT(DISTINCT user_id) as unique_users'),
                DB::raw('SUM(request_count) as total_requests')
            )
            ->where('active_date', '>=', Carbon::now()->subDays(7))
            ->groupBy(DB::raw('DATE(active_date)'))
            ->orderBy('active_date')
            ->get()
            ->keyBy(fn ($item) => is_object($item->active_date) ? $item->active_date->format('Y-m-d') : (string)$item->active_date);

        // Fill missing dates with 0
        $dauLabels = [];
        $dauValues = [];
        $dauRequests = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->toDateString();
            $dauLabels[] = Carbon::parse($date)->format('M d');
            $record = $dauChartData->get($date);
            $dauValues[] = $record ? (int)$record->unique_users : 0;
            $dauRequests[] = $record ? (int)$record->total_requests : 0;
        }

        // 2. Activity by action type (top 10 actions, last 7 days)
        $activityByAction = UserActivityLog::select(
                'action',
                DB::raw('COUNT(*) as count')
            )
            ->where('logged_at', '>=', Carbon::now()->subDays(7))
            ->groupBy('action')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        // 3. User registrations chart (last 7 days)
        $registrationsChart = User::where('is_admin', false)
            ->select(DB::raw('DATE(created_at) as reg_date'), DB::raw('COUNT(*) as count'))
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('reg_date')
            ->get()
            ->keyBy(fn ($item) => Carbon::parse($item->reg_date)->format('Y-m-d'));

        $regLabels = [];
        $regValues = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->toDateString();
            $regLabels[] = Carbon::parse($date)->format('M d');
            $record = $registrationsChart->get($date);
            $regValues[] = $record ? (int)$record->count : 0;
        }

        // 4. Hourly activity distribution (today)
        $hourlyActivity = UserActivityLog::select(
                DB::raw('HOUR(logged_at) as hour'),
                DB::raw('COUNT(*) as count')
            )
            ->whereDate('logged_at', $today)
            ->groupBy(DB::raw('HOUR(logged_at)'))
            ->orderBy('hour')
            ->get()
            ->keyBy(fn ($item) => (int)$item->hour);

        $hourlyLabels = [];
        $hourlyValues = [];
        for ($h = 0; $h < 24; $h++) {
            $hourlyLabels[] = sprintf('%02d:00', $h);
            $record = $hourlyActivity->get($h);
            $hourlyValues[] = $record ? (int)$record->count : 0;
        }

        // Ensure numeric array indexes for Chart.js JSON arrays
        $dauValues = array_values($dauValues);
        $dauRequests = array_values($dauRequests);
        $regValues = array_values($regValues);
        $hourlyValues = array_values($hourlyValues);

        // Recent activity (latest 10)
        $recentActivity = UserActivityLog::with('user:id,name,email')
            ->latest('logged_at')
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact(
            'totalUsers',
            'dauToday',
            'activityLogsToday',
            'newUsersThisWeek',
            'dauLabels',
            'dauValues',
            'dauRequests',
            'activityByAction',
            'regLabels',
            'regValues',
            'hourlyLabels',
            'hourlyValues',
            'recentActivity'
        ));
    }

    /**
     * User activity logs with filtering and pagination.
     */
    public function activityLogs(Request $request)
    {
        $request->validate([
            'search' => 'nullable|string|max:100',
            'action' => 'nullable|string|max:100',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'per_page' => 'nullable|integer|min:10|max:100',
        ]);

        $query = UserActivityLog::with('user:id,name,email');

        // Search by user name or email
        if ($search = $request->input('search')) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by action type
        if ($action = $request->input('action')) {
            $query->where('action', $action);
        }

        // Filter by date range
        $query->dateRange($request->input('date_from'), $request->input('date_to'));

        $logs = $query->latest('logged_at')
            ->paginate($request->input('per_page', 25))
            ->withQueryString();

        // Get distinct action types for filter dropdown
        $actionTypes = UserActivityLog::select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        // --- Chart Data for Activity Logs page ---

        // Activity over time (for the filtered period or last 7 days)
        $chartFrom = $request->input('date_from', Carbon::now()->subDays(7)->toDateString());
        $chartTo = $request->input('date_to', Carbon::now()->toDateString());

        $activityOverTime = UserActivityLog::select(
                DB::raw('DATE(logged_at) as log_date'),
                DB::raw('COUNT(*) as count')
            )
            ->dateRange($chartFrom, $chartTo)
            ->groupBy(DB::raw('DATE(logged_at)'))
            ->orderBy('log_date')
            ->get()
            ->keyBy(fn ($item) => (string)$item->log_date);

        $chartLabels = [];
        $chartValues = [];
        $start = Carbon::parse($chartFrom);
        $end = Carbon::parse($chartTo);
        while ($start->lte($end)) {
            $dateStr = $start->toDateString();
            $chartLabels[] = $start->format('M d');
            $record = $activityOverTime->get($dateStr);
            $chartValues[] = $record ? (int)$record->count : 0;
            $start->addDay();
        }

        // Top users by activity count (filtered period)
        $topUsers = UserActivityLog::select(
                'user_id',
                DB::raw('COUNT(*) as count')
            )
            ->dateRange($request->input('date_from'), $request->input('date_to'))
            ->groupBy('user_id')
            ->orderByDesc('count')
            ->limit(10)
            ->with('user:id,name')
            ->get();

        return view('admin.activity-logs', compact(
            'logs',
            'actionTypes',
            'chartLabels',
            'chartValues',
            'topUsers'
        ));
    }

    /**
     * Daily active users with filtering and pagination.
     */
    public function dailyActiveUsers(Request $request)
    {
        $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'per_page' => 'nullable|integer|min:10|max:100',
        ]);

        $query = DailyActiveUser::with('user:id,name,email');

        // Filter by date range
        $query->dateRange($request->input('date_from'), $request->input('date_to'));

        $records = $query->latest('active_date')
            ->paginate($request->input('per_page', 25))
            ->withQueryString();

        // DAU summary by date (for the table header aggregation)
        $dauSummary = DailyActiveUser::select(
                'active_date',
                DB::raw('COUNT(DISTINCT user_id) as unique_users'),
                DB::raw('SUM(request_count) as total_requests')
            )
            ->dateRange($request->input('date_from'), $request->input('date_to'))
            ->groupBy('active_date')
            ->orderByDesc('active_date')
            ->get();

        // --- Chart Data ---

        // DAU trend (last 30 days or filtered)
        $chartFrom = $request->input('date_from', Carbon::now()->subDays(30)->toDateString());
        $chartTo = $request->input('date_to', Carbon::now()->toDateString());

        $dauTrend = DailyActiveUser::select(
                DB::raw('DATE(active_date) as active_date'),
                DB::raw('COUNT(DISTINCT user_id) as unique_users'),
                DB::raw('SUM(request_count) as total_requests')
            )
            ->dateRange($chartFrom, $chartTo)
            ->groupBy(DB::raw('DATE(active_date)'))
            ->orderBy('active_date')
            ->get()
            ->keyBy(fn ($item) => is_object($item->active_date) ? $item->active_date->format('Y-m-d') : (string)$item->active_date);

        $trendLabels = [];
        $trendUsers = [];
        $trendRequests = [];
        $start = Carbon::parse($chartFrom);
        $end = Carbon::parse($chartTo);
        while ($start->lte($end)) {
            $dateStr = $start->toDateString();
            $trendLabels[] = $start->format('M d');
            $record = $dauTrend->get($dateStr);
            $trendUsers[] = $record ? (int)$record->unique_users : 0;
            $trendRequests[] = $record ? (int)$record->total_requests : 0;
            $start->addDay();
        }

        // Peak activity hours (across filtered range)
        $peakHours = UserActivityLog::select(
                DB::raw('HOUR(logged_at) as hour'),
                DB::raw('COUNT(*) as count')
            )
            ->dateRange($request->input('date_from'), $request->input('date_to'))
            ->groupBy(DB::raw('HOUR(logged_at)'))
            ->orderBy('hour')
            ->get()
            ->keyBy(fn ($item) => (int)$item->hour);

        $peakLabels = [];
        $peakValues = [];
        for ($h = 0; $h < 24; $h++) {
            $peakLabels[] = sprintf('%02d:00', $h);
            $record = $peakHours->get($h);
            $peakValues[] = $record ? (int)$record->count : 0;
        }

        return view('admin.daily-active-users', compact(
            'records',
            'dauSummary',
            'trendLabels',
            'trendUsers',
            'trendRequests',
            'peakLabels',
            'peakValues'
        ));
    }

    /**
     * User feedbacks with filtering, stats, and charts.
     */
    public function feedbacks(Request $request)
    {
        $request->validate([
            'search'   => 'nullable|string|max:100',
            'status'   => 'nullable|string|in:submitted,skipped',
            'date_from' => 'nullable|date',
            'date_to'   => 'nullable|date|after_or_equal:date_from',
            'per_page'  => 'nullable|integer|min:10|max:100',
        ]);

        // --- Summary Stats ---
        $totalFeedbacks   = Feedback::count();
        $submittedCount   = Feedback::where('is_skipped', false)->count();
        $skippedCount     = Feedback::where('is_skipped', true)->count();
        $avgNps           = Feedback::where('is_skipped', false)->whereNotNull('nps_score')->avg('nps_score');
        $avgRating        = Feedback::where('is_skipped', false)->whereNotNull('helpful_rating')->avg('helpful_rating');

        // --- Charts ---

        // 1. NPS Score Distribution (0-10)
        $npsDistribution = Feedback::where('is_skipped', false)
            ->whereNotNull('nps_score')
            ->select('nps_score', DB::raw('COUNT(*) as count'))
            ->groupBy('nps_score')
            ->orderBy('nps_score')
            ->get()
            ->keyBy('nps_score');

        $npsLabels = [];
        $npsValues = [];
        for ($i = 0; $i <= 10; $i++) {
            $npsLabels[] = (string) $i;
            $npsValues[] = $npsDistribution->has($i) ? (int) $npsDistribution[$i]->count : 0;
        }

        // 2. Feedback over time (last 30 days)
        $chartFrom = $request->input('date_from', Carbon::now()->subDays(30)->toDateString());
        $chartTo   = $request->input('date_to', Carbon::now()->toDateString());

        $feedbackOverTime = Feedback::select(
                DB::raw('DATE(created_at) as fb_date'),
                DB::raw('SUM(CASE WHEN is_skipped = 0 THEN 1 ELSE 0 END) as submitted'),
                DB::raw('SUM(CASE WHEN is_skipped = 1 THEN 1 ELSE 0 END) as skipped')
            )
            ->whereBetween(DB::raw('DATE(created_at)'), [$chartFrom, $chartTo])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('fb_date')
            ->get()
            ->keyBy('fb_date');

        $timeLabels    = [];
        $timeSubmitted = [];
        $timeSkipped   = [];
        $start = Carbon::parse($chartFrom);
        $end   = Carbon::parse($chartTo);
        while ($start->lte($end)) {
            $dateStr         = $start->toDateString();
            $timeLabels[]    = $start->format('M d');
            $record          = $feedbackOverTime->get($dateStr);
            $timeSubmitted[] = $record ? (int) $record->submitted : 0;
            $timeSkipped[]   = $record ? (int) $record->skipped : 0;
            $start->addDay();
        }

        // 3. Helpful Rating Distribution (1-5 stars)
        $ratingDistribution = Feedback::where('is_skipped', false)
            ->whereNotNull('helpful_rating')
            ->select('helpful_rating', DB::raw('COUNT(*) as count'))
            ->groupBy('helpful_rating')
            ->orderBy('helpful_rating')
            ->get()
            ->keyBy('helpful_rating');

        $ratingLabels = [];
        $ratingValues = [];
        for ($i = 1; $i <= 5; $i++) {
            $ratingLabels[] = $i . ' Star' . ($i > 1 ? 's' : '');
            $ratingValues[] = $ratingDistribution->has($i) ? (int) $ratingDistribution[$i]->count : 0;
        }

        // --- Filtered Table Query ---
        $query = Feedback::with('user:id,name,email');

        // Search by user name or email
        if ($search = $request->input('search')) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($status = $request->input('status')) {
            if ($status === 'submitted') {
                $query->where('is_skipped', false);
            } elseif ($status === 'skipped') {
                $query->where('is_skipped', true);
            }
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $feedbacks = $query->latest()
            ->paginate($request->input('per_page', 25))
            ->withQueryString();

        return view('admin.feedbacks', compact(
            'totalFeedbacks',
            'submittedCount',
            'skippedCount',
            'avgNps',
            'avgRating',
            'npsLabels',
            'npsValues',
            'timeLabels',
            'timeSubmitted',
            'timeSkipped',
            'ratingLabels',
            'ratingValues',
            'feedbacks'
        ));
    }
}
