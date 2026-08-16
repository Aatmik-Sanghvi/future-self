@extends('admin.layouts.admin')

@section('title', 'Feedbacks')
@section('page-title', 'Feedbacks')

@section('content')
<div class="fade-in">
    {{-- Page Header --}}
    <div class="page-header">
        <h1>User Feedbacks</h1>
        <p>Review and analyze feedback submitted by users to improve the Future Self experience.</p>
    </div>

    {{-- Stats Cards --}}
    <div class="stats-grid">
        <div class="stat-card purple fade-in fade-in-delay-1">
            <div class="stat-icon">
                <i data-lucide="message-square" style="width:22px;height:22px;"></i>
            </div>
            <div class="stat-label">Total Feedbacks</div>
            <div class="stat-value">{{ number_format($totalFeedbacks) }}</div>
        </div>

        <div class="stat-card green fade-in fade-in-delay-2">
            <div class="stat-icon">
                <i data-lucide="check-circle" style="width:22px;height:22px;"></i>
            </div>
            <div class="stat-label">Submitted</div>
            <div class="stat-value">{{ number_format($submittedCount) }}</div>
        </div>

        <div class="stat-card amber fade-in fade-in-delay-3">
            <div class="stat-icon">
                <i data-lucide="skip-forward" style="width:22px;height:22px;"></i>
            </div>
            <div class="stat-label">Skipped</div>
            <div class="stat-value">{{ number_format($skippedCount) }}</div>
        </div>

        <div class="stat-card cyan fade-in fade-in-delay-4">
            <div class="stat-icon">
                <i data-lucide="star" style="width:22px;height:22px;"></i>
            </div>
            <div class="stat-label">Avg Rating</div>
            <div class="stat-value">{{ $avgRating ? number_format($avgRating, 1) : '—' }} <span style="font-size:14px;color:var(--text-muted);">/ 5</span></div>
        </div>

        <div class="stat-card purple fade-in fade-in-delay-4">
            <div class="stat-icon">
                <i data-lucide="gauge" style="width:22px;height:22px;"></i>
            </div>
            <div class="stat-label">Avg NPS</div>
            <div class="stat-value">{{ $avgNps !== null ? number_format($avgNps, 1) : '—' }} <span style="font-size:14px;color:var(--text-muted);">/ 10</span></div>
        </div>
    </div>

    {{-- Charts --}}
    <div class="charts-grid">
        <div class="card">
            <div class="card-header">
                <h3>Feedback Over Time</h3>
                <span class="badge badge-purple">TREND</span>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="feedbackTimeChart"></canvas>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3>NPS Score Distribution</h3>
                <span class="badge badge-cyan">0–10</span>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="npsChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="charts-grid">
        <div class="card">
            <div class="card-header">
                <h3>Helpful Rating Distribution</h3>
                <span class="badge badge-amber">1–5 STARS</span>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="ratingChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('admin.feedbacks') }}" class="filters-bar" id="filtersForm">
        <div class="filter-group">
            <label>Search User</label>
            <input type="text" name="search" class="form-control" placeholder="Name or email..." value="{{ request('search') }}">
        </div>

        <div class="filter-group">
            <label>Status</label>
            <select name="status" class="form-control">
                <option value="">All</option>
                <option value="submitted" {{ request('status') == 'submitted' ? 'selected' : '' }}>Submitted</option>
                <option value="skipped" {{ request('status') == 'skipped' ? 'selected' : '' }}>Skipped</option>
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
            <a href="{{ route('admin.feedbacks') }}" class="btn btn-secondary btn-sm">
                <i data-lucide="x" style="width:14px;height:14px;"></i>
                Clear
            </a>
        </div>
    </form>

    {{-- Feedbacks Table --}}
    <div class="card">
        <div class="card-header">
            <h3>All Feedbacks</h3>
            <span style="font-size:13px;color:var(--text-muted);">
                {{ $feedbacks->total() }} total records
            </span>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Status</th>
                        <th>Rating</th>
                        <th>NPS</th>
                        <th>Would Use Again</th>
                        <th>Price Willingness</th>
                        <th>Date</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($feedbacks as $index => $fb)
                        <tr>
                            <td style="color:var(--text-muted);">{{ $feedbacks->firstItem() + $index }}</td>
                            <td>
                                <div style="display:flex;align-items:center;gap:10px;">
                                    <div style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#7c3aed,#06b6d4);display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;flex-shrink:0;">
                                        {{ strtoupper(substr($fb->user->name ?? '?', 0, 1)) }}
                                    </div>
                                    <div>
                                        <div style="font-weight:600;color:var(--text-primary);font-size:13px;">{{ $fb->user->name ?? 'N/A' }}</div>
                                        <div style="font-size:11px;color:var(--text-muted);">{{ $fb->user->email ?? '' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($fb->is_skipped)
                                    <span class="badge badge-amber">Skipped</span>
                                @else
                                    <span class="badge badge-green">Submitted</span>
                                @endif
                            </td>
                            <td>
                                @if(!$fb->is_skipped && $fb->helpful_rating)
                                    <div style="display:flex;gap:2px;">
                                        @for($s = 1; $s <= 5; $s++)
                                            <span style="color:{{ $s <= $fb->helpful_rating ? '#f59e0b' : 'var(--text-muted)' }};font-size:14px;">★</span>
                                        @endfor
                                    </div>
                                @else
                                    <span style="color:var(--text-muted);">—</span>
                                @endif
                            </td>
                            <td>
                                @if(!$fb->is_skipped && $fb->nps_score !== null)
                                    @php
                                        $npsColor = $fb->nps_score >= 9 ? 'badge-green' : ($fb->nps_score >= 7 ? 'badge-blue' : ($fb->nps_score >= 5 ? 'badge-amber' : 'badge-red'));
                                    @endphp
                                    <span class="badge {{ $npsColor }}">{{ $fb->nps_score }}</span>
                                @else
                                    <span style="color:var(--text-muted);">—</span>
                                @endif
                            </td>
                            <td>
                                @if(!$fb->is_skipped && $fb->would_use_again)
                                    <span style="font-size:12px;color:var(--text-secondary);">{{ $fb->would_use_again }}</span>
                                @else
                                    <span style="color:var(--text-muted);">—</span>
                                @endif
                            </td>
                            <td>
                                @if(!$fb->is_skipped && $fb->monthly_price_willingness)
                                    <span style="font-size:12px;color:var(--text-secondary);">{{ $fb->monthly_price_willingness }}</span>
                                @else
                                    <span style="color:var(--text-muted);">—</span>
                                @endif
                            </td>
                            <td style="white-space:nowrap;">
                                <div style="font-size:12px;color:var(--text-secondary);">{{ $fb->created_at->format('M d, Y') }}</div>
                                <div style="font-size:11px;color:var(--text-muted);">{{ $fb->created_at->format('h:i A') }}</div>
                            </td>
                            <td>
                                @if(!$fb->is_skipped)
                                    <button type="button" class="btn btn-primary btn-sm" onclick="openDetailModal({{ $fb->id }})" style="padding:5px 10px;font-size:11px;">
                                        <i data-lucide="eye" style="width:13px;height:13px;"></i>
                                        View
                                    </button>
                                @else
                                    <span style="color:var(--text-muted);font-size:11px;">—</span>
                                @endif
                            </td>
                        </tr>

                        {{-- Hidden detail data for modal --}}
                        @if(!$fb->is_skipped)
                        <script type="application/json" id="fb-data-{{ $fb->id }}">
                            {!! json_encode([
                                'user' => $fb->user->name ?? 'N/A',
                                'email' => $fb->user->email ?? '',
                                'date' => $fb->created_at->format('M d, Y h:i A'),
                                'helpful_rating' => $fb->helpful_rating,
                                'is_personal' => $fb->is_personal,
                                'understood_level' => $fb->understood_level,
                                'would_use_again' => $fb->would_use_again,
                                'use_frequency' => $fb->use_frequency,
                                'most_valuable' => $fb->most_valuable,
                                'confused_or_disappointed' => $fb->confused_or_disappointed,
                                'feature_to_come_back' => $fb->feature_to_come_back,
                                'monthly_price_willingness' => $fb->monthly_price_willingness,
                                'subscription_convincer' => $fb->subscription_convincer,
                                'nps_score' => $fb->nps_score,
                                'one_thing_to_change' => $fb->one_thing_to_change,
                            ]) !!}
                        </script>
                        @endif
                    @empty
                        <tr>
                            <td colspan="9">
                                <div class="empty-state">
                                    <i data-lucide="message-square-off" style="width:40px;height:40px;display:block;margin:0 auto 12px;color:var(--text-muted);"></i>
                                    <h4>No feedbacks found</h4>
                                    <p>Try adjusting your filters or wait for users to submit feedback.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($feedbacks->hasPages())
            <div class="pagination-wrapper">
                <div class="pagination-info">
                    Showing {{ $feedbacks->firstItem() }} to {{ $feedbacks->lastItem() }} of {{ $feedbacks->total() }} results
                </div>
                <div class="pagination">
                    {{-- Previous --}}
                    @if ($feedbacks->onFirstPage())
                        <li class="disabled"><span>&laquo;</span></li>
                    @else
                        <li><a href="{{ $feedbacks->previousPageUrl() }}">&laquo;</a></li>
                    @endif

                    {{-- Pages --}}
                    @foreach ($feedbacks->getUrlRange(max(1, $feedbacks->currentPage() - 2), min($feedbacks->lastPage(), $feedbacks->currentPage() + 2)) as $page => $url)
                        @if ($page == $feedbacks->currentPage())
                            <li class="active"><span>{{ $page }}</span></li>
                        @else
                            <li><a href="{{ $url }}">{{ $page }}</a></li>
                        @endif
                    @endforeach

                    {{-- Next --}}
                    @if ($feedbacks->hasMorePages())
                        <li><a href="{{ $feedbacks->nextPageUrl() }}">&raquo;</a></li>
                    @else
                        <li class="disabled"><span>&raquo;</span></li>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>

{{-- Detail Modal --}}
<div id="fbDetailOverlay" style="display:none;position:fixed;inset:0;background:rgba(6,4,14,0.85);backdrop-filter:blur(14px);z-index:9999;align-items:center;justify-content:center;" onclick="closeDetailModal(event)">
    <div id="fbDetailModal" style="background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:var(--radius-xl);padding:28px;max-width:640px;width:90%;max-height:85vh;overflow-y:auto;position:relative;box-shadow:var(--shadow-lg);">
        <button type="button" onclick="closeDetailModal()" style="position:absolute;top:16px;right:16px;width:32px;height:32px;border-radius:8px;background:var(--bg-glass);border:1px solid var(--border-color);color:var(--text-muted);cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all 0.15s ease;">
            <i data-lucide="x" style="width:18px;height:18px;"></i>
        </button>

        <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
            <div style="width:42px;height:42px;border-radius:50%;background:var(--gradient-primary);display:flex;align-items:center;justify-content:center;font-size:16px;font-weight:700;flex-shrink:0;" id="modalAvatar"></div>
            <div>
                <div style="font-weight:700;font-size:16px;color:var(--text-primary);" id="modalUser"></div>
                <div style="font-size:12px;color:var(--text-muted);" id="modalEmail"></div>
            </div>
            <div style="margin-left:auto;font-size:12px;color:var(--text-muted);" id="modalDate"></div>
        </div>

        <div id="modalContent" style="display:flex;flex-direction:column;gap:16px;"></div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* 5-column stats grid for this page */
    .stats-grid {
        grid-template-columns: repeat(5, 1fr) !important;
    }
    @media (max-width: 1200px) {
        .stats-grid {
            grid-template-columns: repeat(3, 1fr) !important;
        }
    }
    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr) !important;
        }
    }

    /* Detail modal field styling */
    .fb-detail-field {
        background: var(--bg-glass);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        padding: 14px 16px;
    }
    .fb-detail-label {
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--text-muted);
        margin-bottom: 6px;
    }
    .fb-detail-value {
        font-size: 13px;
        color: var(--text-primary);
        line-height: 1.55;
    }
    .fb-detail-empty {
        color: var(--text-muted);
        font-style: italic;
    }

    /* Star display in modal */
    .fb-stars {
        display: flex;
        gap: 3px;
        font-size: 18px;
    }
    .fb-star-on { color: #f59e0b; }
    .fb-star-off { color: var(--text-muted); opacity: 0.3; }

    /* NPS badge in modal */
    .fb-nps-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border-radius: 10px;
        font-size: 16px;
        font-weight: 800;
    }
    .fb-nps-promoter { background: rgba(16,185,129,0.15); color: #10b981; border: 1px solid rgba(16,185,129,0.3); }
    .fb-nps-passive { background: rgba(59,130,246,0.15); color: #3b82f6; border: 1px solid rgba(59,130,246,0.3); }
    .fb-nps-detractor { background: rgba(239,68,68,0.15); color: #ef4444; border: 1px solid rgba(239,68,68,0.3); }

    /* Two-column grid for compact fields */
    .fb-detail-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }
    @media (max-width: 500px) {
        .fb-detail-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    // ─── FEEDBACK OVER TIME CHART ─────────────
    new Chart(document.getElementById('feedbackTimeChart'), {
        type: 'line',
        data: {
            labels: {!! json_encode($timeLabels) !!},
            datasets: [
                {
                    label: 'Submitted',
                    data: {!! json_encode($timeSubmitted) !!},
                    borderColor: '#10b981',
                    backgroundColor: (ctx) => {
                        const g = ctx.chart.ctx.createLinearGradient(0, 0, 0, 300);
                        g.addColorStop(0, 'rgba(16, 185, 129, 0.2)');
                        g.addColorStop(1, 'rgba(16, 185, 129, 0)');
                        return g;
                    },
                    fill: true,
                    tension: 0.4,
                    borderWidth: 2.5,
                    pointRadius: 3,
                    pointBackgroundColor: '#10b981',
                    pointBorderColor: '#0a0a0f',
                    pointBorderWidth: 2,
                    pointHoverRadius: 6,
                },
                {
                    label: 'Skipped',
                    data: {!! json_encode($timeSkipped) !!},
                    borderColor: '#f59e0b',
                    backgroundColor: 'rgba(245, 158, 11, 0.05)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 2,
                    pointRadius: 3,
                    pointBackgroundColor: '#f59e0b',
                    pointBorderColor: '#0a0a0f',
                    pointBorderWidth: 2,
                    pointHoverRadius: 6,
                    borderDash: [5, 5],
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: true } },
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0 } },
                x: { ticks: { maxTicksLimit: 12 } },
            }
        }
    });

    // ─── NPS DISTRIBUTION CHART ───────────────
    const npsColors = {!! json_encode($npsValues) !!}.map((_, i) => {
        if (i >= 9) return '#10b981';
        if (i >= 7) return '#3b82f6';
        if (i >= 5) return '#f59e0b';
        return '#ef4444';
    });

    new Chart(document.getElementById('npsChart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($npsLabels) !!},
            datasets: [{
                label: 'Responses',
                data: {!! json_encode($npsValues) !!},
                backgroundColor: npsColors.map(c => c + '50'),
                borderColor: npsColors,
                borderWidth: 1.5,
                borderRadius: 6,
                hoverBackgroundColor: npsColors.map(c => c + '90'),
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

    // ─── RATING DISTRIBUTION CHART ────────────
    new Chart(document.getElementById('ratingChart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($ratingLabels) !!},
            datasets: [{
                label: 'Responses',
                data: {!! json_encode($ratingValues) !!},
                backgroundColor: ['#ef444450', '#f59e0b50', '#3b82f650', '#06b6d450', '#10b98150'],
                borderColor: ['#ef4444', '#f59e0b', '#3b82f6', '#06b6d4', '#10b981'],
                borderWidth: 1.5,
                borderRadius: 6,
                hoverBackgroundColor: ['#ef444490', '#f59e0b90', '#3b82f690', '#06b6d490', '#10b98190'],
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

    // ─── DETAIL MODAL ─────────────────────────
    function openDetailModal(id) {
        const el = document.getElementById('fb-data-' + id);
        if (!el) return;
        const data = JSON.parse(el.textContent);

        document.getElementById('modalAvatar').textContent = (data.user || '?')[0].toUpperCase();
        document.getElementById('modalUser').textContent = data.user;
        document.getElementById('modalEmail').textContent = data.email;
        document.getElementById('modalDate').textContent = data.date;

        let html = '';

        // Stars
        let starsHtml = '<div class="fb-stars">';
        for (let i = 1; i <= 5; i++) {
            starsHtml += `<span class="${i <= (data.helpful_rating || 0) ? 'fb-star-on' : 'fb-star-off'}">★</span>`;
        }
        starsHtml += '</div>';

        // NPS
        let npsHtml = '—';
        if (data.nps_score !== null && data.nps_score !== undefined) {
            const cls = data.nps_score >= 9 ? 'fb-nps-promoter' : (data.nps_score >= 7 ? 'fb-nps-passive' : 'fb-nps-detractor');
            npsHtml = `<span class="fb-nps-badge ${cls}">${data.nps_score}</span>`;
        }

        // Grid fields
        html += '<div class="fb-detail-grid">';
        html += fieldCard('Helpful Rating', starsHtml);
        html += fieldCard('NPS Score', npsHtml);
        html += fieldCard('Felt Personal?', data.is_personal);
        html += fieldCard('Understood Level', data.understood_level);
        html += fieldCard('Would Use Again', data.would_use_again);
        html += fieldCard('Usage Frequency', data.use_frequency);
        html += fieldCard('Price Willingness', data.monthly_price_willingness);
        html += '</div>';

        // Long text fields
        html += fieldCard('Most Valuable Aspect', data.most_valuable);
        html += fieldCard('What Confused or Disappointed?', data.confused_or_disappointed);
        html += fieldCard('Feature to Come Back For', data.feature_to_come_back);
        html += fieldCard('What Would Convince to Subscribe?', data.subscription_convincer);
        html += fieldCard('One Thing to Change', data.one_thing_to_change);

        document.getElementById('modalContent').innerHTML = html;

        const overlay = document.getElementById('fbDetailOverlay');
        overlay.style.display = 'flex';
        lucide.createIcons();
    }

    function fieldCard(label, value) {
        const display = value
            ? `<div class="fb-detail-value">${escapeHtml(String(value))}</div>`
            : `<div class="fb-detail-value fb-detail-empty">Not provided</div>`;

        // If value contains HTML (stars/badge), use it raw
        const isHtml = typeof value === 'string' && value.startsWith('<');
        const safeDisplay = isHtml
            ? `<div class="fb-detail-value">${value}</div>`
            : display;

        return `<div class="fb-detail-field"><div class="fb-detail-label">${escapeHtml(label)}</div>${safeDisplay}</div>`;
    }

    function escapeHtml(str) {
        if (!str) return '';
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    function closeDetailModal(event) {
        if (event && event.target !== document.getElementById('fbDetailOverlay')) return;
        document.getElementById('fbDetailOverlay').style.display = 'none';
    }
    // Also close if called without event (X button)
    document.querySelector('#fbDetailModal button')?.addEventListener('click', () => {
        document.getElementById('fbDetailOverlay').style.display = 'none';
    });

    lucide.createIcons();
</script>
@endpush
