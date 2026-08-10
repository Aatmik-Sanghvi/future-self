@extends('admin.layouts.admin')

@section('title', 'Daily Active Users')
@section('page-title', 'Daily Active Users')

@section('content')
<div class="fade-in">
    {{-- Page Header --}}
    <div class="page-header">
        <h1>Daily Active Users</h1>
        <p>Monitor user engagement with daily activity metrics and usage patterns.</p>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('admin.daily-active-users') }}" class="filters-bar">
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
            <a href="{{ route('admin.daily-active-users') }}" class="btn btn-secondary btn-sm">
                <i data-lucide="x" style="width:14px;height:14px;"></i>
                Clear
            </a>
        </div>
    </form>

    {{-- Summary Cards --}}
    @php
        $totalUniqueUsers = $dauSummary->sum('unique_users');
        $totalRequests = $dauSummary->sum('total_requests');
        $avgDau = $dauSummary->count() > 0 ? round($dauSummary->avg('unique_users'), 1) : 0;
        $peakDau = $dauSummary->max('unique_users') ?? 0;
    @endphp
    <div class="stats-grid">
        <div class="stat-card purple fade-in fade-in-delay-1">
            <div class="stat-icon">
                <i data-lucide="calendar-days" style="width:22px;height:22px;"></i>
            </div>
            <div class="stat-label">Days Tracked</div>
            <div class="stat-value">{{ $dauSummary->count() }}</div>
        </div>

        <div class="stat-card cyan fade-in fade-in-delay-2">
            <div class="stat-icon">
                <i data-lucide="trending-up" style="width:22px;height:22px;"></i>
            </div>
            <div class="stat-label">Avg. DAU</div>
            <div class="stat-value">{{ $avgDau }}</div>
        </div>

        <div class="stat-card green fade-in fade-in-delay-3">
            <div class="stat-icon">
                <i data-lucide="trophy" style="width:22px;height:22px;"></i>
            </div>
            <div class="stat-label">Peak DAU</div>
            <div class="stat-value">{{ $peakDau }}</div>
        </div>

        <div class="stat-card amber fade-in fade-in-delay-4">
            <div class="stat-icon">
                <i data-lucide="zap" style="width:22px;height:22px;"></i>
            </div>
            <div class="stat-label">Total Requests</div>
            <div class="stat-value">{{ number_format($totalRequests) }}</div>
        </div>
    </div>

    {{-- Charts --}}
    <div class="charts-grid">
        <div class="card">
            <div class="card-header">
                <h3>DAU Trend</h3>
                <span class="badge badge-cyan">USERS & REQUESTS</span>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="dauTrendChart"></canvas>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3>Peak Activity Hours</h3>
                <span class="badge badge-amber">DISTRIBUTION</span>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="peakHoursChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- DAU Summary by Date --}}
    <div class="card" style="margin-bottom:24px;">
        <div class="card-header">
            <h3>DAU Summary by Date</h3>
            <span class="badge badge-green">AGGREGATED</span>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Unique Users</th>
                        <th>Total Requests</th>
                        <th>Avg. Requests/User</th>
                        <th>Engagement</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dauSummary as $summary)
                        @php
                            $avgReq = $summary->unique_users > 0 ? round($summary->total_requests / $summary->unique_users, 1) : 0;
                            $engagementLevel = $avgReq >= 10 ? 'High' : ($avgReq >= 5 ? 'Medium' : 'Low');
                            $engagementColor = $avgReq >= 10 ? 'badge-green' : ($avgReq >= 5 ? 'badge-amber' : 'badge-red');
                        @endphp
                        <tr>
                            <td style="font-weight:600;color:var(--text-primary);">
                                {{ \Carbon\Carbon::parse($summary->active_date)->format('D, M d, Y') }}
                            </td>
                            <td>
                                <span style="font-weight:700;font-size:15px;color:var(--accent-primary-light);">{{ $summary->unique_users }}</span>
                            </td>
                            <td>{{ number_format($summary->total_requests) }}</td>
                            <td>{{ $avgReq }}</td>
                            <td><span class="badge {{ $engagementColor }}">{{ $engagementLevel }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <i data-lucide="calendar-x" style="width:40px;height:40px;display:block;margin:0 auto 12px;color:var(--text-muted);"></i>
                                    <h4>No data for this range</h4>
                                    <p>Try adjusting the date filters above.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Detailed DAU Records --}}
    <div class="card">
        <div class="card-header">
            <h3>Detailed User Records</h3>
            <span style="font-size:13px;color:var(--text-muted);">
                {{ $records->total() }} total records
            </span>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>User</th>
                        <th>First Seen</th>
                        <th>Last Seen</th>
                        <th>Requests</th>
                        <th>Session Duration</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $index => $record)
                        @php
                            $duration = $record->first_seen_at && $record->last_seen_at
                                ? $record->first_seen_at->diff($record->last_seen_at)
                                : null;
                        @endphp
                        <tr>
                            <td style="color:var(--text-muted);">{{ $records->firstItem() + $index }}</td>
                            <td style="font-weight:600;color:var(--text-primary);">
                                {{ $record->active_date->format('M d, Y') }}
                            </td>
                            <td>
                                <div style="display:flex;align-items:center;gap:10px;">
                                    <div style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#7c3aed,#06b6d4);display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;flex-shrink:0;">
                                        {{ strtoupper(substr($record->user->name ?? '?', 0, 1)) }}
                                    </div>
                                    <div>
                                        <div style="font-weight:600;color:var(--text-primary);font-size:13px;">{{ $record->user->name ?? 'N/A' }}</div>
                                        <div style="font-size:11px;color:var(--text-muted);">{{ $record->user->email ?? '' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td style="font-size:12px;">
                                {{ $record->first_seen_at ? $record->first_seen_at->format('h:i:s A') : '-' }}
                            </td>
                            <td style="font-size:12px;">
                                {{ $record->last_seen_at ? $record->last_seen_at->format('h:i:s A') : '-' }}
                            </td>
                            <td>
                                <span style="font-weight:700;color:var(--accent-secondary);">{{ number_format($record->request_count) }}</span>
                            </td>
                            <td style="font-size:12px;color:var(--text-muted);">
                                @if($duration)
                                    @if($duration->h > 0){{ $duration->h }}h @endif{{ $duration->i }}m {{ $duration->s }}s
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <i data-lucide="users" style="width:40px;height:40px;display:block;margin:0 auto 12px;color:var(--text-muted);"></i>
                                    <h4>No records found</h4>
                                    <p>DAU records will appear here once users start using the app.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($records->hasPages())
            <div class="pagination-wrapper">
                <div class="pagination-info">
                    Showing {{ $records->firstItem() }} to {{ $records->lastItem() }} of {{ $records->total() }} results
                </div>
                <div class="pagination">
                    @if ($records->onFirstPage())
                        <li class="disabled"><span>&laquo;</span></li>
                    @else
                        <li><a href="{{ $records->previousPageUrl() }}">&laquo;</a></li>
                    @endif

                    @foreach ($records->getUrlRange(max(1, $records->currentPage() - 2), min($records->lastPage(), $records->currentPage() + 2)) as $page => $url)
                        @if ($page == $records->currentPage())
                            <li class="active"><span>{{ $page }}</span></li>
                        @else
                            <li><a href="{{ $url }}">{{ $page }}</a></li>
                        @endif
                    @endforeach

                    @if ($records->hasMorePages())
                        <li><a href="{{ $records->nextPageUrl() }}">&raquo;</a></li>
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
    // ─── DAU TREND CHART ──────────────────────────
    const dauCtx = document.getElementById('dauTrendChart');
    new Chart(dauCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($trendLabels) !!},
            datasets: [
                {
                    label: 'Unique Users',
                    data: {!! json_encode($trendUsers) !!},
                    borderColor: '#06b6d4',
                    backgroundColor: (ctx) => {
                        const gradient = ctx.chart.ctx.createLinearGradient(0, 0, 0, 300);
                        gradient.addColorStop(0, 'rgba(6, 182, 212, 0.2)');
                        gradient.addColorStop(1, 'rgba(6, 182, 212, 0)');
                        return gradient;
                    },
                    fill: true,
                    tension: 0.4,
                    borderWidth: 2.5,
                    pointRadius: 2,
                    pointHoverRadius: 6,
                    pointBackgroundColor: '#06b6d4',
                    pointHoverBackgroundColor: '#06b6d4',
                    pointHoverBorderColor: '#fff',
                    pointHoverBorderWidth: 2,
                    yAxisID: 'y',
                },
                {
                    label: 'Total Requests',
                    data: {!! json_encode($trendRequests) !!},
                    borderColor: '#7c3aed',
                    backgroundColor: 'transparent',
                    borderWidth: 2,
                    borderDash: [5, 5],
                    pointRadius: 0,
                    pointHoverRadius: 5,
                    pointHoverBackgroundColor: '#7c3aed',
                    tension: 0.4,
                    yAxisID: 'y1',
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { position: 'top' },
            },
            scales: {
                y: {
                    type: 'linear',
                    position: 'left',
                    beginAtZero: true,
                    ticks: { precision: 0 },
                    title: { display: true, text: 'Users', color: '#06b6d4' },
                },
                y1: {
                    type: 'linear',
                    position: 'right',
                    beginAtZero: true,
                    ticks: { precision: 0 },
                    title: { display: true, text: 'Requests', color: '#7c3aed' },
                    grid: { drawOnChartArea: false },
                },
                x: { ticks: { maxTicksLimit: 12 } },
            }
        }
    });

    // ─── PEAK HOURS CHART ─────────────────────────
    new Chart(document.getElementById('peakHoursChart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($peakLabels) !!},
            datasets: [{
                label: 'Requests',
                data: {!! json_encode($peakValues) !!},
                backgroundColor: (ctx) => {
                    const value = ctx.raw || 0;
                    const max = Math.max(...{!! json_encode($peakValues) !!}, 1);
                    const intensity = value / max;
                    return `rgba(245, 158, 11, ${0.15 + intensity * 0.55})`;
                },
                borderColor: '#f59e0b',
                borderWidth: 1,
                borderRadius: 4,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0 } },
            }
        }
    });

    lucide.createIcons();
</script>
@endpush
