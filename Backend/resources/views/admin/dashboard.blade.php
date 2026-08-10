@extends('admin.layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="fade-in">
    {{-- Page Header --}}
    <div class="page-header">
        <h1>Dashboard Overview</h1>
        <p>Real-time insights into your application's user activity and engagement.</p>
    </div>

    {{-- Stats Cards --}}
    <div class="stats-grid">
        <div class="stat-card purple fade-in fade-in-delay-1">
            <div class="stat-icon">
                <i data-lucide="users" style="width:22px;height:22px;"></i>
            </div>
            <div class="stat-label">Total Users</div>
            <div class="stat-value">{{ number_format($totalUsers) }}</div>
        </div>

        <div class="stat-card cyan fade-in fade-in-delay-2">
            <div class="stat-icon">
                <i data-lucide="user-check" style="width:22px;height:22px;"></i>
            </div>
            <div class="stat-label">Active Today</div>
            <div class="stat-value">{{ number_format($dauToday) }}</div>
        </div>

        <div class="stat-card green fade-in fade-in-delay-3">
            <div class="stat-icon">
                <i data-lucide="activity" style="width:22px;height:22px;"></i>
            </div>
            <div class="stat-label">Actions Today</div>
            <div class="stat-value">{{ number_format($activityLogsToday) }}</div>
        </div>

        <div class="stat-card amber fade-in fade-in-delay-4">
            <div class="stat-icon">
                <i data-lucide="user-plus" style="width:22px;height:22px;"></i>
            </div>
            <div class="stat-label">New This Week</div>
            <div class="stat-value">{{ number_format($newUsersThisWeek) }}</div>
        </div>
    </div>

    {{-- Charts Row 1: DAU Trend + Activity by Action --}}
    <div class="charts-grid">
        <div class="card">
            <div class="card-header">
                <h3>Daily Active Users (Last 7 Days)</h3>
                <span class="badge badge-cyan">TREND</span>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="dauChart"></canvas>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3>Top Actions (Last 7 Days)</h3>
                <span class="badge badge-purple">BREAKDOWN</span>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="actionsChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Row 2: User Registrations + Hourly Activity --}}
    <div class="charts-grid">
        <div class="card">
            <div class="card-header">
                <h3>User Registrations (Last 7 Days)</h3>
                <span class="badge badge-green">GROWTH</span>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="registrationsChart"></canvas>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3>Hourly Activity Distribution (Today)</h3>
                <span class="badge badge-amber">PATTERN</span>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="hourlyChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Activity --}}
    <div class="card">
        <div class="card-header">
            <h3>Recent Activity</h3>
            <a href="{{ route('admin.activity-logs') }}" class="btn btn-secondary btn-sm">
                View All
                <i data-lucide="arrow-right" style="width:14px;height:14px;"></i>
            </a>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Action</th>
                        <th>Route</th>
                        <th>Method</th>
                        <th>IP Address</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentActivity as $log)
                        <tr>
                            <td>
                                <div style="display:flex;align-items:center;gap:10px;">
                                    <div style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#7c3aed,#06b6d4);display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;flex-shrink:0;">
                                        {{ strtoupper(substr($log->user->name ?? '?', 0, 1)) }}
                                    </div>
                                    <div>
                                        <div style="font-weight:600;color:var(--text-primary);font-size:13px;">{{ $log->user->name ?? 'N/A' }}</div>
                                        <div style="font-size:11px;color:var(--text-muted);">{{ $log->user->email ?? '' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge badge-purple">{{ $log->action }}</span></td>
                            <td style="color:var(--text-muted);font-size:12px;font-family:monospace;">{{ $log->route }}</td>
                            <td><span class="badge badge-blue">{{ $log->method }}</span></td>
                            <td style="font-family:monospace;font-size:12px;">{{ $log->ip_address }}</td>
                            <td style="color:var(--text-muted);">{{ $log->logged_at->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <i data-lucide="inbox" style="width:40px;height:40px;display:block;margin:0 auto 12px;color:var(--text-muted);"></i>
                                    <h4>No activity yet</h4>
                                    <p>Activity logs will appear here once users interact with the app.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ─── DAU TREND CHART ─────────────────────────
    const dauEl = document.getElementById('dauChart');
    if (dauEl) {
        new Chart(dauEl, {
            type: 'line',
            data: {
                labels: {!! json_encode($dauLabels) !!},
                datasets: [
                    {
                        label: 'Unique Users',
                        data: {!! json_encode($dauValues) !!},
                        borderColor: '#7c3aed',
                        backgroundColor: 'rgba(124, 58, 237, 0.18)',
                        fill: true,
                        tension: 0.4,
                        borderWidth: 3,
                        pointRadius: 4,
                        pointBackgroundColor: '#a78bfa',
                        pointHoverRadius: 7,
                        pointHoverBackgroundColor: '#7c3aed',
                        pointHoverBorderColor: '#fff',
                        pointHoverBorderWidth: 2,
                    },
                    {
                        label: 'Total Requests',
                        data: {!! json_encode($dauRequests) !!},
                        borderColor: '#06b6d4',
                        backgroundColor: 'rgba(6, 182, 212, 0.12)',
                        fill: true,
                        tension: 0.4,
                        borderWidth: 2,
                        pointRadius: 3,
                        pointBackgroundColor: '#06b6d4',
                        pointHoverRadius: 6,
                        pointHoverBackgroundColor: '#06b6d4',
                        borderDash: [4, 4],
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { position: 'top', labels: { boxWidth: 12, padding: 16 } },
                },
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 } },
                    x: { ticks: { maxTicksLimit: 10 } },
                }
            }
        });
    }

    // ─── ACTIONS DOUGHNUT CHART ─────────────────
    const actionData = {!! json_encode($activityByAction) !!};
    const actionsEl = document.getElementById('actionsChart');

    if (actionsEl) {
        if (!actionData || actionData.length === 0) {
            actionsEl.parentNode.innerHTML = `
                <div class="empty-state" style="padding: 60px 0;">
                    <i data-lucide="pie-chart" style="width:36px;height:36px;display:block;margin:0 auto 12px;color:var(--text-muted);"></i>
                    <h4 style="color:var(--text-secondary);">No Action Breakdown</h4>
                    <p style="font-size:12px;color:var(--text-muted);">Activity by action will appear here as users interact.</p>
                </div>
            `;
            if (window.lucide) lucide.createIcons();
        } else {
            const actionColors = ['#7c3aed', '#06b6d4', '#10b981', '#f59e0b', '#ef4444', '#3b82f6', '#8b5cf6', '#14b8a6', '#f97316', '#ec4899'];

            new Chart(actionsEl, {
                type: 'doughnut',
                data: {
                    labels: actionData.map(a => a.action),
                    datasets: [{
                        data: actionData.map(a => a.count),
                        backgroundColor: actionColors.slice(0, actionData.length),
                        borderColor: '#12121a',
                        borderWidth: 3,
                        hoverOffset: 8,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: { padding: 14, font: { size: 12, weight: '500' } }
                        },
                    }
                }
            });
        }
    }

    // ─── REGISTRATIONS CHART ─────────────────────
    const regEl = document.getElementById('registrationsChart');
    if (regEl) {
        new Chart(regEl.getContext('2d'), {
            type: 'bar',
            data: {
                labels: {!! json_encode($regLabels) !!},
                datasets: [{
                    label: 'New Users',
                    data: {!! json_encode($regValues) !!},
                    backgroundColor: '#10b981',
                    borderColor: '#10b981',
                    borderWidth: 1,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        suggestedMax: 5,
                        ticks: { precision: 0, stepSize: 1 }
                    },
                    x: { ticks: { maxTicksLimit: 10 } },
                }
            }
        });
    }

    // ─── HOURLY ACTIVITY CHART ───────────────────
    const hourlyEl = document.getElementById('hourlyChart');
    if (hourlyEl) {
        new Chart(hourlyEl.getContext('2d'), {
            type: 'bar',
            data: {
                labels: {!! json_encode($hourlyLabels) !!},
                datasets: [{
                    label: 'Requests',
                    data: {!! json_encode($hourlyValues) !!},
                    backgroundColor: '#f59e0b',
                    borderColor: '#f59e0b',
                    borderWidth: 1,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        suggestedMax: 5,
                        ticks: { precision: 0, stepSize: 1 }
                    },
                }
            }
        });
    }

    if (window.lucide) lucide.createIcons();
});
</script>
@endpush
