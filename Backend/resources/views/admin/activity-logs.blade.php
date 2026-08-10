@extends('admin.layouts.admin')

@section('title', 'Activity Logs')
@section('page-title', 'Activity Logs')

@section('content')
<div class="fade-in">
    {{-- Page Header --}}
    <div class="page-header">
        <h1>User Activity Logs</h1>
        <p>Track and analyze every action performed by users across the platform.</p>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('admin.activity-logs') }}" class="filters-bar" id="filtersForm">
        <div class="filter-group">
            <label>Search User</label>
            <input type="text" name="search" class="form-control" placeholder="Name or email..." value="{{ request('search') }}">
        </div>

        <div class="filter-group">
            <label>Action Type</label>
            <select name="action" class="form-control">
                <option value="">All Actions</option>
                @foreach($actionTypes as $type)
                    <option value="{{ $type }}" {{ request('action') == $type ? 'selected' : '' }}>{{ $type }}</option>
                @endforeach
            </select>
        </div>

        <div class="filter-group">
            <label>Date From</label>
            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
        </div>

        <div class="filter-group">
            <label>Date To</label>
            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
        </div>

        <div class="filter-group">
            <label>Per Page</label>
            <select name="per_page" class="form-control" style="min-width:80px;">
                @foreach([10, 25, 50, 100] as $pp)
                    <option value="{{ $pp }}" {{ request('per_page', 25) == $pp ? 'selected' : '' }}>{{ $pp }}</option>
                @endforeach
            </select>
        </div>

        <div class="filter-group" style="display:flex;flex-direction:row;gap:8px;align-self:flex-end;">
            <button type="submit" class="btn btn-primary btn-sm">
                <i data-lucide="search" style="width:14px;height:14px;"></i>
                Filter
            </button>
            <a href="{{ route('admin.activity-logs') }}" class="btn btn-secondary btn-sm">
                <i data-lucide="x" style="width:14px;height:14px;"></i>
                Clear
            </a>
        </div>
    </form>

    {{-- Charts --}}
    <div class="charts-grid">
        <div class="card">
            <div class="card-header">
                <h3>Activity Over Time</h3>
                <span class="badge badge-purple">TREND</span>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="activityTimeChart"></canvas>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3>Most Active Users</h3>
                <span class="badge badge-cyan">TOP 10</span>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="topUsersChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Activity Logs Table --}}
    <div class="card">
        <div class="card-header">
            <h3>Activity Logs</h3>
            <span style="font-size:13px;color:var(--text-muted);">
                {{ $logs->total() }} total records
            </span>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Description</th>
                        <th>Route</th>
                        <th>Method</th>
                        <th>IP Address</th>
                        <th>Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $index => $log)
                        <tr>
                            <td style="color:var(--text-muted);">{{ $logs->firstItem() + $index }}</td>
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
                            <td class="wrap" style="color:var(--text-secondary);font-size:12px;">{{ Str::limit($log->description, 50) }}</td>
                            <td style="font-family:monospace;font-size:12px;color:var(--text-muted);">{{ $log->route }}</td>
                            <td>
                                @php
                                    $methodColors = [
                                        'GET' => 'badge-green',
                                        'POST' => 'badge-blue',
                                        'PUT' => 'badge-amber',
                                        'PATCH' => 'badge-amber',
                                        'DELETE' => 'badge-red',
                                    ];
                                @endphp
                                <span class="badge {{ $methodColors[$log->method] ?? 'badge-purple' }}">{{ $log->method }}</span>
                            </td>
                            <td style="font-family:monospace;font-size:12px;">{{ $log->ip_address }}</td>
                            <td style="white-space:nowrap;">
                                <div style="font-size:12px;color:var(--text-secondary);">{{ $log->logged_at->format('M d, Y') }}</div>
                                <div style="font-size:11px;color:var(--text-muted);">{{ $log->logged_at->format('h:i:s A') }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <i data-lucide="search-x" style="width:40px;height:40px;display:block;margin:0 auto 12px;color:var(--text-muted);"></i>
                                    <h4>No activity logs found</h4>
                                    <p>Try adjusting your filters or wait for users to interact with the app.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="pagination-wrapper">
                <div class="pagination-info">
                    Showing {{ $logs->firstItem() }} to {{ $logs->lastItem() }} of {{ $logs->total() }} results
                </div>
                <div class="pagination">
                    {{-- Previous --}}
                    @if ($logs->onFirstPage())
                        <li class="disabled"><span>&laquo;</span></li>
                    @else
                        <li><a href="{{ $logs->previousPageUrl() }}">&laquo;</a></li>
                    @endif

                    {{-- Pages --}}
                    @foreach ($logs->getUrlRange(max(1, $logs->currentPage() - 2), min($logs->lastPage(), $logs->currentPage() + 2)) as $page => $url)
                        @if ($page == $logs->currentPage())
                            <li class="active"><span>{{ $page }}</span></li>
                        @else
                            <li><a href="{{ $url }}">{{ $page }}</a></li>
                        @endif
                    @endforeach

                    {{-- Next --}}
                    @if ($logs->hasMorePages())
                        <li><a href="{{ $logs->nextPageUrl() }}">&raquo;</a></li>
                    @else
                        <li class="disabled"><span>&raquo;</span></li>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    // ─── ACTIVITY OVER TIME CHART ────────────────
    new Chart(document.getElementById('activityTimeChart'), {
        type: 'line',
        data: {
            labels: {!! json_encode($chartLabels) !!},
            datasets: [{
                label: 'Actions',
                data: {!! json_encode($chartValues) !!},
                borderColor: '#7c3aed',
                backgroundColor: (ctx) => {
                    const gradient = ctx.chart.ctx.createLinearGradient(0, 0, 0, 300);
                    gradient.addColorStop(0, 'rgba(124, 58, 237, 0.2)');
                    gradient.addColorStop(1, 'rgba(124, 58, 237, 0)');
                    return gradient;
                },
                fill: true,
                tension: 0.4,
                borderWidth: 2.5,
                pointRadius: 3,
                pointBackgroundColor: '#7c3aed',
                pointBorderColor: '#0a0a0f',
                pointBorderWidth: 2,
                pointHoverRadius: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0 } },
                x: { ticks: { maxTicksLimit: 12 } },
            }
        }
    });

    // ─── TOP USERS CHART ──────────────────────────
    const topUsersData = {!! json_encode($topUsers) !!};
    const topUserColors = ['#7c3aed', '#06b6d4', '#10b981', '#f59e0b', '#ef4444', '#3b82f6', '#8b5cf6', '#14b8a6', '#f97316', '#ec4899'];

    new Chart(document.getElementById('topUsersChart'), {
        type: 'bar',
        data: {
            labels: topUsersData.map(u => u.user ? u.user.name : 'Unknown'),
            datasets: [{
                label: 'Actions',
                data: topUsersData.map(u => u.count),
                backgroundColor: topUserColors.slice(0, topUsersData.length).map(c => c + '40'),
                borderColor: topUserColors.slice(0, topUsersData.length),
                borderWidth: 1.5,
                borderRadius: 6,
                hoverBackgroundColor: topUserColors.slice(0, topUsersData.length).map(c => c + '80'),
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            plugins: { legend: { display: false } },
            scales: {
                x: { beginAtZero: true, ticks: { precision: 0 } },
            }
        }
    });

    lucide.createIcons();
</script>
@endpush
