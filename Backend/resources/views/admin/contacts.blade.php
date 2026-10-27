@extends('admin.layouts.admin')

@section('title', 'Contact Queries')
@section('page-title', 'Contact Queries')

@section('content')
<div class="fade-in">
    {{-- Toast Notification --}}
    <div id="contactToast" class="admin-toast" style="display:none;">
        <div class="admin-toast-icon" id="toastIcon">
            <i data-lucide="check-circle-2" style="width:20px;height:20px;"></i>
        </div>
        <div class="admin-toast-content">
            <div class="admin-toast-title" id="toastTitle">Success</div>
            <div class="admin-toast-message" id="toastMessage">Action completed successfully.</div>
        </div>
        <button type="button" class="admin-toast-close" onclick="hideToast()">
            <i data-lucide="x" style="width:16px;height:16px;"></i>
        </button>
    </div>

    {{-- Page Header --}}
    <div class="page-header" style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:16px;">
        <div>
            <h1>Contact & Support Queries</h1>
            <p>Review, track, and send direct email replies to inquiries submitted through FutureSelf.</p>
        </div>
        <div style="display:flex;gap:10px;align-items:center;">
            <div style="font-size:12px;color:var(--text-muted);display:flex;align-items:center;gap:6px;background:var(--bg-glass);padding:8px 14px;border-radius:var(--radius-sm);border:1px solid var(--border-color);">
                <i data-lucide="send" style="width:14px;height:14px;color:var(--accent-primary-light);"></i>
                <span>Replies send from: <strong style="color:var(--text-primary);">{{ config('mail.support.address', env('SUPPORT_MAIL_FROM_ADDRESS', 'support@mail.futureself.in')) }}</strong></span>
            </div>
            <a href="mailto:{{ env('ADMIN_CONTACT_EMAIL', 'support@futureself.in') }}" class="btn btn-secondary" style="display:inline-flex;align-items:center;gap:8px;">
                <i data-lucide="mail" style="width:16px;height:16px;"></i>
                <span>Open Mailbox</span>
            </a>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="stats-grid">
        <div class="stat-card purple fade-in fade-in-delay-1">
            <div class="stat-icon">
                <i data-lucide="inbox" style="width:22px;height:22px;"></i>
            </div>
            <div class="stat-label">Total Queries</div>
            <div class="stat-value" id="statTotal">{{ number_format($totalContacts) }}</div>
        </div>

        <div class="stat-card amber fade-in fade-in-delay-2">
            <div class="stat-icon">
                <i data-lucide="clock" style="width:22px;height:22px;"></i>
            </div>
            <div class="stat-label">Pending Review</div>
            <div class="stat-value" id="statPending">{{ number_format($pendingCount) }}</div>
        </div>

        <div class="stat-card cyan fade-in fade-in-delay-3">
            <div class="stat-icon">
                <i data-lucide="loader" style="width:22px;height:22px;"></i>
            </div>
            <div class="stat-label">In Progress</div>
            <div class="stat-value" id="statInProgress">{{ number_format($inProgressCount) }}</div>
        </div>

        <div class="stat-card green fade-in fade-in-delay-4">
            <div class="stat-icon">
                <i data-lucide="check-circle-2" style="width:22px;height:22px;"></i>
            </div>
            <div class="stat-label">Resolved</div>
            <div class="stat-value" id="statResolved">{{ number_format($resolvedCount) }}</div>
        </div>
    </div>

    {{-- Category Pills --}}
    @if($categoriesBreakdown->isNotEmpty())
    <div class="card" style="margin-bottom:24px;padding:18px 22px;">
        <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.8px;color:var(--text-muted);margin-bottom:12px;">
            Filter by Category
        </div>
        <div class="category-pills">
            <a href="{{ route('admin.contacts', array_merge(request()->except(['category', 'page']))) }}" 
               class="category-pill {{ !request('category') ? 'active' : '' }}">
                <span>All Categories</span>
                <span class="count">{{ $totalContacts }}</span>
            </a>
            @foreach($categoriesBreakdown as $cat)
                @php
                    $isActive = request('category') === $cat->category;
                    $catParams = array_merge(request()->except(['page']), ['category' => $cat->category]);
                    $catLabel = ucwords(str_replace(['_', '-'], ' ', $cat->category));
                @endphp
                <a href="{{ route('admin.contacts', $catParams) }}"
                   class="category-pill {{ $isActive ? 'active' : '' }}">
                    <span>{{ $catLabel }}</span>
                    <span class="count">{{ $cat->count }}</span>
                </a>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Filters Bar --}}
    <form method="GET" action="{{ route('admin.contacts') }}" class="filters-bar" id="contactsFilterForm">
        @if(request('category'))
            <input type="hidden" name="category" value="{{ request('category') }}">
        @endif

        <div class="filter-group" style="flex:2;min-width:220px;">
            <label for="filterSearch">Search Query</label>
            <input type="text" id="filterSearch" name="search" value="{{ request('search') }}" 
                   placeholder="Search by name, email, subject, or message..." class="form-control">
        </div>

        <div class="filter-group" style="flex:1;min-width:150px;">
            <label for="filterStatus">Status</label>
            <select id="filterStatus" name="status" class="form-control">
                <option value="">All Statuses</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>⏳ Pending</option>
                <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>🔄 In Progress</option>
                <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>✅ Resolved</option>
                <option value="spam" {{ request('status') === 'spam' ? 'selected' : '' }}>🚫 Spam</option>
            </select>
        </div>

        <div class="filter-group" style="flex:1;min-width:145px;">
            <label for="filterDateFrom">From Date</label>
            <input type="date" id="filterDateFrom" name="date_from" value="{{ request('date_from') }}" class="form-control">
        </div>

        <div class="filter-group" style="flex:1;min-width:145px;">
            <label for="filterDateTo">To Date</label>
            <input type="date" id="filterDateTo" name="date_to" value="{{ request('date_to') }}" class="form-control">
        </div>

        <div class="filter-actions">
            <button type="submit" class="btn btn-primary" title="Apply Filters">
                <i data-lucide="filter" style="width:15px;height:15px;"></i>
                <span>Filter</span>
            </button>
            @if(request()->anyFilled(['search', 'category', 'status', 'date_from', 'date_to']))
                <a href="{{ route('admin.contacts') }}" class="btn btn-secondary" title="Clear all filters">
                    <i data-lucide="x" style="width:15px;height:15px;"></i>
                    <span>Reset</span>
                </a>
            @endif
        </div>
    </form>

    {{-- Contacts Table Card --}}
    <div class="card">
        <div class="card-header">
            <div style="display:flex;align-items:center;gap:10px;">
                <h3 style="margin:0;font-size:16px;">Inquiries & Messages</h3>
                <span class="badge badge-purple">{{ $contacts->total() }} Total</span>
            </div>
            @if(request('category'))
                <span class="badge badge-cyan">Filtered: {{ ucwords(str_replace(['_', '-'], ' ', request('category'))) }}</span>
            @endif
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th style="width:65px;">ID</th>
                        <th>Sender</th>
                        <th>Category</th>
                        <th>Subject & Message</th>
                        <th>Status</th>
                        <th>Received Date</th>
                        <th style="width:120px;text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($contacts as $contact)
                    @php
                        $rawCategory = strtolower($contact->category ?? 'general');
                        $catBadgeClass = 'cat-badge-general';
                        if (str_contains($rawCategory, 'support') || str_contains($rawCategory, 'tech')) {
                            $catBadgeClass = 'cat-badge-support';
                        } elseif (str_contains($rawCategory, 'feedback') || str_contains($rawCategory, 'feature')) {
                            $catBadgeClass = 'cat-badge-feedback';
                        } elseif (str_contains($rawCategory, 'bug') || str_contains($rawCategory, 'error') || str_contains($rawCategory, 'issue')) {
                            $catBadgeClass = 'cat-badge-bug';
                        } elseif (str_contains($rawCategory, 'bill') || str_contains($rawCategory, 'account') || str_contains($rawCategory, 'payment')) {
                            $catBadgeClass = 'cat-badge-billing';
                        }
                        $initial = strtoupper(substr($contact->name ?: 'U', 0, 1));
                    @endphp
                    <tr id="contact-row-{{ $contact->id }}">
                        {{-- ID --}}
                        <td style="color:var(--text-muted);font-weight:600;font-family:monospace;font-size:13px;">
                            #{{ $contact->id }}
                        </td>

                        {{-- Sender --}}
                        <td>
                            <div class="sender-cell">
                                <div class="sender-avatar">
                                    {{ $initial }}
                                </div>
                                <div class="sender-info">
                                    <div class="sender-name">{{ $contact->name }}</div>
                                    <div class="sender-meta">
                                        <a href="mailto:{{ $contact->email }}?subject=Re:%20{{ urlencode($contact->subject) }}" 
                                           class="sender-email" title="Click to email {{ $contact->email }}">
                                            {{ $contact->email }}
                                        </a>
                                        @if($contact->user_id)
                                            <span class="badge badge-green" style="font-size:10px;padding:1px 6px;">Registered</span>
                                        @else
                                            <span class="badge badge-amber" style="font-size:10px;padding:1px 6px;">Guest</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>

                        {{-- Category --}}
                        <td>
                            <span class="cat-badge {{ $catBadgeClass }}">
                                {{ ucwords(str_replace(['_', '-'], ' ', $contact->category ?? 'General')) }}
                            </span>
                        </td>

                        {{-- Subject & Preview --}}
                        <td>
                            <div class="message-preview-cell" onclick="openContactModal({{ $contact->id }})" title="Click to read full message">
                                <div class="message-subject">
                                    {{ $contact->subject }}
                                </div>
                                <div class="message-snippet">
                                    {{ Str::limit($contact->message, 85) }}
                                </div>
                            </div>
                        </td>

                        {{-- Status & Replied Badge --}}
                        <td>
                            <div style="display:flex;flex-direction:column;gap:5px;align-items:flex-start;">
                                <select onchange="updateStatus({{ $contact->id }}, this.value, this)" 
                                        class="status-pill-select status-{{ $contact->status }}"
                                        title="Change status">
                                    <option value="pending" {{ $contact->status === 'pending' ? 'selected' : '' }}>⏳ Pending</option>
                                    <option value="in_progress" {{ $contact->status === 'in_progress' ? 'selected' : '' }}>🔄 In Progress</option>
                                    <option value="resolved" {{ $contact->status === 'resolved' ? 'selected' : '' }}>✅ Resolved</option>
                                    <option value="spam" {{ $contact->status === 'spam' ? 'selected' : '' }}>🚫 Spam</option>
                                </select>
                                <div id="row-replied-badge-{{ $contact->id }}">
                                    @if($contact->replied_at)
                                        <span class="badge badge-green" style="font-size:10px;padding:1px 7px;" title="Replied on {{ $contact->replied_at->format('M d, Y h:i A') }}">
                                            ✓ Replied
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </td>

                        {{-- Date --}}
                        <td style="white-space:nowrap;">
                            <div style="font-size:13px;color:var(--text-primary);font-weight:500;">
                                {{ $contact->created_at ? $contact->created_at->format('M d, Y') : '—' }}
                            </div>
                            <div style="font-size:11px;color:var(--text-muted);margin-top:2px;">
                                {{ $contact->created_at ? $contact->created_at->format('h:i A') : '' }}
                            </div>
                        </td>

                        {{-- Actions --}}
                        <td style="text-align:right;">
                            <div class="action-buttons">
                                <button type="button" class="action-btn" onclick="openContactModal({{ $contact->id }})" title="View Details">
                                    <i data-lucide="eye" style="width:16px;height:16px;"></i>
                                </button>
                                <button type="button" class="action-btn action-btn-reply" onclick="openContactModal({{ $contact->id }}, true)" title="Compose Reply">
                                    <i data-lucide="reply" style="width:16px;height:16px;"></i>
                                </button>
                            </div>

                            {{-- Hidden JSON payload for modal --}}
                            <script type="application/json" id="contact-data-{{ $contact->id }}">
                                {!! json_encode([
                                    'id' => $contact->id,
                                    'name' => $contact->name,
                                    'email' => $contact->email,
                                    'subject' => $contact->subject,
                                    'category' => $contact->category,
                                    'message' => $contact->message,
                                    'status' => $contact->status,
                                    'user_id' => $contact->user_id,
                                    'admin_reply' => $contact->admin_reply,
                                    'replied_at' => $contact->replied_at ? $contact->replied_at->format('M d, Y — h:i A') : null,
                                    'date_formatted' => $contact->created_at ? $contact->created_at->format('M d, Y — h:i A') : '—',
                                ]) !!}
                            </script>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7">
                            <div style="text-align:center;padding:56px 24px;color:var(--text-muted);">
                                <i data-lucide="inbox" style="width:44px;height:44px;margin-bottom:14px;opacity:0.35;display:inline-block;"></i>
                                <div style="font-size:16px;font-weight:600;color:var(--text-secondary);margin-bottom:4px;">No contact queries found</div>
                                <p style="font-size:13px;max-width:380px;margin:0 auto;color:var(--text-muted);">
                                    When users submit questions or feedback through the website contact form, they will appear right here.
                                </p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($contacts->hasPages())
            <div class="pagination-wrapper">
                <div class="pagination-info">
                    Showing {{ $contacts->firstItem() }} to {{ $contacts->lastItem() }} of {{ $contacts->total() }} queries
                </div>
                <ul class="pagination">
                    {{-- Previous --}}
                    @if ($contacts->onFirstPage())
                        <li class="disabled"><span>&laquo;</span></li>
                    @else
                        <li><a href="{{ $contacts->previousPageUrl() }}">&laquo;</a></li>
                    @endif

                    {{-- Page Numbers --}}
                    @foreach ($contacts->getUrlRange(max(1, $contacts->currentPage() - 2), min($contacts->lastPage(), $contacts->currentPage() + 2)) as $page => $url)
                        @if ($page == $contacts->currentPage())
                            <li class="active"><span>{{ $page }}</span></li>
                        @else
                            <li><a href="{{ $url }}">{{ $page }}</a></li>
                        @endif
                    @endforeach

                    {{-- Next --}}
                    @if ($contacts->hasMorePages())
                        <li><a href="{{ $contacts->nextPageUrl() }}">&raquo;</a></li>
                    @else
                        <li class="disabled"><span>&raquo;</span></li>
                    @endif
                </ul>
            </div>
        @endif
    </div>
</div>

{{-- Contact Message Detail & Reply Modal --}}
<div id="contactModalOverlay" class="modal-overlay" onclick="closeContactModal(event)">
    <div id="contactModalCard" class="modal-card">
        {{-- Modal Topbar --}}
        <div style="padding:22px 28px;border-bottom:1px solid var(--border-color);display:flex;align-items:flex-start;justify-content:space-between;gap:16px;background:rgba(255,255,255,0.015);">
            <div>
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px;">
                    <span id="modalCategoryBadge" class="cat-badge cat-badge-general">Category</span>
                    <span id="modalAccountBadge" class="badge badge-amber" style="font-size:10px;padding:2px 7px;">Guest</span>
                    <span id="modalIdTag" style="font-size:11px;color:var(--text-muted);font-family:monospace;">#0</span>
                    <span id="modalRepliedTag" class="badge badge-green" style="display:none;font-size:10px;padding:2px 7px;">✓ Replied</span>
                </div>
                <h2 id="modalSubject" style="font-size:19px;font-weight:700;color:var(--text-primary);margin:0;line-height:1.35;">
                    Subject
                </h2>
            </div>
            <button type="button" onclick="closeContactModal()" class="action-btn" title="Close Modal (Esc)">
                <i data-lucide="x" style="width:18px;height:18px;"></i>
            </button>
        </div>

        {{-- Modal Body --}}
        <div style="padding:24px 28px;overflow-y:auto;flex:1;">
            {{-- Sender Meta Card --}}
            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(180px, 1fr));gap:16px;padding:16px 20px;background:rgba(255,255,255,0.025);border:1px solid var(--border-color);border-radius:var(--radius-md);margin-bottom:22px;">
                <div>
                    <div style="color:var(--text-muted);font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:4px;">Sender Name</div>
                    <div id="modalName" style="color:var(--text-primary);font-weight:600;font-size:14px;">Name</div>
                </div>
                <div>
                    <div style="color:var(--text-muted);font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:4px;">Email Address</div>
                    <div id="modalEmailWrapper">
                        <a id="modalEmail" href="#" style="color:var(--accent-primary-light);font-size:14px;font-weight:500;text-decoration:none;">
                            email@example.com
                        </a>
                    </div>
                </div>
                <div>
                    <div style="color:var(--text-muted);font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:4px;">Submitted On</div>
                    <div id="modalDate" style="color:var(--text-secondary);font-size:13px;">Date</div>
                </div>
            </div>

            {{-- User Message --}}
            <div style="margin-bottom:24px;">
                <div style="font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.8px;margin-bottom:10px;display:flex;align-items:center;gap:6px;">
                    <i data-lucide="message-square" style="width:14px;height:14px;"></i>
                    <span>Inquiry Message</span>
                </div>
                <div id="modalMessage" 
                     style="background:rgba(12, 12, 18, 0.7);border:1px solid rgba(255,255,255,0.07);border-radius:var(--radius-md);padding:18px 20px;font-size:14px;line-height:1.75;color:#f1f5f9;white-space:pre-wrap;word-break:break-word;">
                </div>
            </div>

            {{-- Previous Reply (if already sent) --}}
            <div id="modalPreviousReplyBox" style="display:none;margin-bottom:24px;">
                <div style="font-size:11px;font-weight:700;color:#10b981;text-transform:uppercase;letter-spacing:0.8px;margin-bottom:10px;display:flex;align-items:center;justify-content:space-between;">
                    <span style="display:flex;align-items:center;gap:6px;">
                        <i data-lucide="check-check" style="width:14px;height:14px;"></i>
                        <span>Previous Response Sent</span>
                    </span>
                    <span id="modalPreviousReplyDate" style="font-size:11px;color:var(--text-muted);font-weight:500;"></span>
                </div>
                <div id="modalPreviousReplyText" 
                     style="background:rgba(16, 185, 129, 0.05);border:1px solid rgba(16, 185, 129, 0.25);border-radius:var(--radius-md);padding:18px 20px;font-size:14px;line-height:1.75;color:#e2e8f0;white-space:pre-wrap;word-break:break-word;">
                </div>
            </div>

            {{-- Direct Reply Composer --}}
            <div id="replyComposerSection" class="reply-composer-card">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
                    <div style="font-size:12px;font-weight:700;color:var(--accent-primary-light);text-transform:uppercase;letter-spacing:0.8px;display:flex;align-items:center;gap:6px;">
                        <i data-lucide="send" style="width:14px;height:14px;"></i>
                        <span>Send Direct Email Reply</span>
                    </div>
                    <span style="font-size:11px;color:var(--text-muted);">From: <strong>{{ config('mail.support.address', env('SUPPORT_MAIL_FROM_ADDRESS', 'support@mail.futureself.in')) }}</strong></span>
                </div>

                <form id="contactReplyForm" onsubmit="submitReply(event)">
                    {{-- Quick Response Snippets --}}
                    <div style="margin-bottom:12px;display:flex;flex-wrap:wrap;gap:6px;align-items:center;">
                        <span style="font-size:11px;color:var(--text-muted);font-weight:600;">Templates:</span>
                        <button type="button" class="snippet-btn" onclick="insertSnippet('thankyou')">Thank You</button>
                        <button type="button" class="snippet-btn" onclick="insertSnippet('investigating')">Looking Into It</button>
                        <button type="button" class="snippet-btn" onclick="insertSnippet('resolved')">Issue Resolved</button>
                    </div>

                    {{-- Reply Subject --}}
                    <div style="margin-bottom:12px;">
                        <label for="replySubjectInput" style="display:block;font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:5px;">
                            Email Subject
                        </label>
                        <input type="text" id="replySubjectInput" name="subject" required class="form-control" style="width:100%;">
                    </div>

                    {{-- Reply Message --}}
                    <div style="margin-bottom:14px;">
                        <label for="replyMessageInput" style="display:block;font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:5px;">
                            Your Message
                        </label>
                        <textarea id="replyMessageInput" name="message" rows="5" required class="form-control" 
                                  placeholder="Type your message here. The user will receive this formatted nicely in their email inbox with a copy of their inquiry."
                                  style="width:100%;height:auto;resize:vertical;"></textarea>
                    </div>

                    {{-- Reply Controls & Submit --}}
                    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;padding-top:4px;">
                        <label style="display:flex;align-items:center;gap:8px;font-size:12.5px;color:var(--text-secondary);cursor:pointer;user-select:none;">
                            <input type="checkbox" id="replyMarkResolved" checked style="accent-color:var(--accent-primary);width:16px;height:16px;cursor:pointer;">
                            <span>Mark query as <strong>Resolved</strong> upon sending</span>
                        </label>

                        <button type="submit" id="replySubmitBtn" class="btn btn-primary" style="display:inline-flex;align-items:center;gap:8px;padding:9px 22px;">
                            <i data-lucide="send" style="width:15px;height:15px;"></i>
                            <span id="replyBtnText">Send Reply</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Modal Footer --}}
        <div style="padding:16px 28px;border-top:1px solid var(--border-color);background:rgba(18, 18, 26, 0.7);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px;">
            <div style="display:flex;align-items:center;gap:10px;">
                <label for="modalStatusSelect" style="font-size:11px;color:var(--text-muted);font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">
                    Change Status:
                </label>
                <select id="modalStatusSelect" onchange="updateModalStatus(this.value)" class="status-pill-select" style="min-width:140px;">
                    <option value="pending">⏳ Pending</option>
                    <option value="in_progress">🔄 In Progress</option>
                    <option value="resolved">✅ Resolved</option>
                    <option value="spam">🚫 Spam</option>
                </select>
            </div>

            <div style="display:flex;align-items:center;gap:12px;">
                <a id="modalMailtoLink" href="#" style="font-size:12px;color:var(--text-muted);text-decoration:none;display:inline-flex;align-items:center;gap:4px;" title="Open in external client">
                    <i data-lucide="external-link" style="width:13px;height:13px;"></i>
                    <span>External Client</span>
                </a>
                <button type="button" onclick="closeContactModal()" class="btn btn-secondary">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* ==========================================================
       CONTACTS DASHBOARD STYLES — FUTURE SELF ADMIN
       ========================================================== */

    /* Toast Notification */
    .admin-toast {
        position: fixed;
        top: 24px;
        right: 28px;
        z-index: 10000;
        background: rgba(18, 18, 28, 0.95);
        border: 1px solid rgba(124, 58, 237, 0.4);
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.6);
        border-radius: var(--radius-md);
        padding: 14px 18px;
        display: flex;
        align-items: center;
        gap: 12px;
        backdrop-filter: blur(16px);
        max-width: 420px;
        animation: toastSlideIn 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }
    @keyframes toastSlideIn {
        from { transform: translateX(40px); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    .admin-toast.toast-success { border-color: rgba(16, 185, 129, 0.4); }
    .admin-toast.toast-success .admin-toast-icon { color: #10b981; background: rgba(16, 185, 129, 0.15); }
    .admin-toast.toast-error { border-color: rgba(239, 68, 68, 0.4); }
    .admin-toast.toast-error .admin-toast-icon { color: #ef4444; background: rgba(239, 68, 68, 0.15); }
    .admin-toast-icon {
        width: 34px;
        height: 34px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .admin-toast-content { flex: 1; min-width: 0; }
    .admin-toast-title { font-size: 13px; font-weight: 700; color: #ffffff; }
    .admin-toast-message { font-size: 12px; color: var(--text-secondary); margin-top: 2px; }
    .admin-toast-close {
        background: none; border: none; color: var(--text-muted); cursor: pointer; padding: 4px; border-radius: 4px;
    }
    .admin-toast-close:hover { color: #ffffff; }

    /* Category Pill Filters */
    .category-pills {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: center;
    }
    .category-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 7px 16px;
        border-radius: 24px;
        font-size: 13px;
        font-weight: 500;
        text-decoration: none;
        transition: all var(--transition-fast);
        background: var(--bg-glass);
        border: 1px solid var(--border-color);
        color: var(--text-secondary);
    }
    .category-pill:hover {
        background: var(--bg-glass-hover);
        color: var(--text-primary);
        border-color: var(--border-color-hover);
        transform: translateY(-1px);
    }
    .category-pill.active {
        background: var(--gradient-primary);
        color: #ffffff;
        font-weight: 600;
        border-color: transparent;
        box-shadow: 0 4px 14px rgba(124, 58, 237, 0.35);
    }
    .category-pill .count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 2px 7px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 700;
        background: rgba(255, 255, 255, 0.12);
        color: inherit;
    }
    .category-pill.active .count {
        background: rgba(255, 255, 255, 0.25);
        color: #ffffff;
    }

    /* Enhanced Filters Bar */
    .filters-bar {
        display: flex;
        flex-wrap: wrap;
        gap: 14px;
        align-items: flex-end;
        margin-bottom: 24px;
        padding: 20px 24px;
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
    }
    .filters-bar .filter-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .filters-bar .filter-group label {
        font-size: 11px;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.6px;
    }
    .filters-bar .form-control {
        height: 40px;
        padding: 8px 14px;
        background: rgba(18, 18, 28, 0.85);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: var(--radius-sm);
        color: var(--text-primary);
        font-size: 13px;
        font-family: inherit;
        transition: all var(--transition-fast);
    }
    .filters-bar .form-control:focus {
        border-color: var(--accent-primary);
        box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.15);
        background: rgba(26, 26, 40, 0.95);
    }
    .filters-bar input[type="date"].form-control {
        color-scheme: dark;
        cursor: pointer;
    }
    .filters-bar .filter-actions {
        display: flex;
        gap: 8px;
        align-items: center;
    }
    .filters-bar .filter-actions .btn {
        height: 40px;
        padding: 0 18px;
    }

    /* Sender Cell */
    .sender-cell {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .sender-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: var(--gradient-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        font-weight: 700;
        color: #ffffff;
        flex-shrink: 0;
        box-shadow: 0 2px 8px rgba(124, 58, 237, 0.25);
    }
    .sender-info {
        display: flex;
        flex-direction: column;
        gap: 2px;
        min-width: 0;
    }
    .sender-name {
        font-weight: 600;
        color: var(--text-primary);
        font-size: 13.5px;
    }
    .sender-meta {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 12px;
    }
    .sender-email {
        color: var(--accent-primary-light);
        text-decoration: none;
        transition: color var(--transition-fast);
    }
    .sender-email:hover {
        color: #ffffff;
        text-decoration: underline;
    }

    /* Category Badges */
    .cat-badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 12px;
        border-radius: 16px;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.4px;
        text-transform: capitalize;
    }
    .cat-badge-general { background: rgba(124, 58, 237, 0.15); color: #c4b5fd; border: 1px solid rgba(124, 58, 237, 0.35); }
    .cat-badge-support { background: rgba(6, 182, 212, 0.15); color: #67e8f9; border: 1px solid rgba(6, 182, 212, 0.35); }
    .cat-badge-feedback { background: rgba(59, 130, 246, 0.15); color: #93c5fd; border: 1px solid rgba(59, 130, 246, 0.35); }
    .cat-badge-bug { background: rgba(239, 68, 68, 0.15); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.35); }
    .cat-badge-billing { background: rgba(245, 158, 11, 0.15); color: #fcd34d; border: 1px solid rgba(245, 158, 11, 0.35); }

    /* Status Pill Selector */
    .status-pill-select {
        appearance: none;
        -webkit-appearance: none;
        cursor: pointer;
        padding: 6px 30px 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        font-family: inherit;
        border: 1px solid transparent;
        transition: all var(--transition-fast);
        outline: none;
        background-repeat: no-repeat;
        background-position: right 10px center;
        background-size: 11px;
    }
    .status-pill-select.status-pending {
        background-color: rgba(245, 158, 11, 0.14);
        color: #f59e0b;
        border-color: rgba(245, 158, 11, 0.35);
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23f59e0b' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
    }
    .status-pill-select.status-in_progress {
        background-color: rgba(6, 182, 212, 0.14);
        color: #06b6d4;
        border-color: rgba(6, 182, 212, 0.35);
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2306b6d4' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
    }
    .status-pill-select.status-resolved {
        background-color: rgba(16, 185, 129, 0.14);
        color: #10b981;
        border-color: rgba(16, 185, 129, 0.35);
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2310b981' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
    }
    .status-pill-select.status-spam {
        background-color: rgba(239, 68, 68, 0.14);
        color: #ef4444;
        border-color: rgba(239, 68, 68, 0.35);
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23ef4444' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
    }
    .status-pill-select:hover {
        filter: brightness(1.15);
    }
    .status-pill-select:focus {
        box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.2);
    }
    .status-pill-select option {
        background: #141422;
        color: #f1f5f9;
        font-size: 13px;
        padding: 6px;
    }

    /* Action Buttons */
    .action-buttons {
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .action-btn {
        width: 34px;
        height: 34px;
        border-radius: var(--radius-sm);
        background: rgba(255, 255, 255, 0.04);
        border: 1px solid var(--border-color);
        color: var(--text-secondary);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all var(--transition-fast);
        padding: 0;
        text-decoration: none;
    }
    .action-btn:hover {
        background: rgba(124, 58, 237, 0.18);
        border-color: rgba(124, 58, 237, 0.4);
        color: var(--accent-primary-light);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(124, 58, 237, 0.2);
    }
    .action-btn.action-btn-reply:hover {
        background: rgba(6, 182, 212, 0.18);
        border-color: rgba(6, 182, 212, 0.4);
        color: var(--accent-secondary);
        box-shadow: 0 4px 12px rgba(6, 182, 212, 0.2);
    }

    /* Subject & Preview Cell */
    .message-preview-cell {
        max-width: 320px;
        cursor: pointer;
    }
    .message-subject {
        font-weight: 600;
        color: var(--text-primary);
        font-size: 13.5px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        transition: color var(--transition-fast);
    }
    .message-preview-cell:hover .message-subject {
        color: var(--accent-primary-light);
    }
    .message-snippet {
        font-size: 12.5px;
        color: var(--text-muted);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        margin-top: 3px;
        line-height: 1.4;
    }

    /* Modal Overlay & Card */
    .modal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(6, 4, 14, 0.85);
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
        z-index: 9999;
        align-items: center;
        justify-content: center;
        padding: 20px;
        animation: modalFadeIn 0.2s ease-out;
    }
    @keyframes modalFadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    .modal-card {
        background: var(--bg-secondary);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-xl);
        width: 100%;
        max-width: 680px;
        max-height: 90vh;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        box-shadow: var(--shadow-lg);
        animation: modalSlideUp 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    }
    @keyframes modalSlideUp {
        from { transform: translateY(16px) scale(0.98); opacity: 0; }
        to { transform: translateY(0) scale(1); opacity: 1; }
    }

    /* Reply Composer Card inside Modal */
    .reply-composer-card {
        background: rgba(124, 58, 237, 0.04);
        border: 1px solid rgba(124, 58, 237, 0.25);
        border-radius: var(--radius-md);
        padding: 20px;
        position: relative;
    }
    .snippet-btn {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: var(--text-secondary);
        font-size: 11px;
        padding: 3px 9px;
        border-radius: 12px;
        cursor: pointer;
        transition: all var(--transition-fast);
    }
    .snippet-btn:hover {
        background: rgba(124, 58, 237, 0.2);
        border-color: rgba(124, 58, 237, 0.4);
        color: var(--text-primary);
    }
    .reply-composer-card .form-control {
        background: rgba(14, 14, 22, 0.9);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }
    .reply-composer-card .form-control:focus {
        border-color: var(--accent-primary);
        box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.2);
    }
</style>
@endpush

@push('scripts')
<script>
let currentContactId = null;

// Snippet templates
const snippets = {
    thankyou: "Thank you for reaching out to FutureSelf support!\n\nWe appreciate you bringing this to our attention. Our team is actively reviewing your query and we will ensure everything is taken care of for you.",
    investigating: "Hello,\n\nWe are currently investigating your request and collaborating with our team to find the best solution. We'll update you as soon as we have progress to share.",
    resolved: "Hi there,\n\nWe are happy to let you know that your issue/request has been resolved! Please feel free to test it out on FutureSelf and let us know if everything looks good on your end."
};

function insertSnippet(key) {
    const textarea = document.getElementById('replyMessageInput');
    if (snippets[key]) {
        textarea.value = snippets[key];
        textarea.focus();
    }
}

function openContactModal(id, focusReply = false) {
    currentContactId = id;
    const dataEl = document.getElementById('contact-data-' + id);
    if (!dataEl) return;
    
    const contact = JSON.parse(dataEl.textContent);
    
    // Header tags
    document.getElementById('modalIdTag').textContent = '#' + contact.id;
    const catLabel = contact.category ? contact.category.replace(/[_-]/g, ' ') : 'General';
    const catBadge = document.getElementById('modalCategoryBadge');
    catBadge.textContent = catLabel.charAt(0).toUpperCase() + catLabel.slice(1);
    catBadge.className = 'cat-badge ' + getCategoryBadgeClass(contact.category);

    const accountBadge = document.getElementById('modalAccountBadge');
    if (contact.user_id) {
        accountBadge.textContent = 'Registered User';
        accountBadge.className = 'badge badge-green';
    } else {
        accountBadge.textContent = 'Guest User';
        accountBadge.className = 'badge badge-amber';
    }

    const repliedTag = document.getElementById('modalRepliedTag');
    if (contact.replied_at) {
        repliedTag.style.display = 'inline-flex';
    } else {
        repliedTag.style.display = 'none';
    }

    // Sender details
    document.getElementById('modalSubject').textContent = contact.subject || 'No Subject';
    document.getElementById('modalName').textContent = contact.name || 'Anonymous';
    
    const emailEl = document.getElementById('modalEmail');
    emailEl.textContent = contact.email || '—';
    emailEl.href = 'mailto:' + encodeURIComponent(contact.email) + '?subject=' + encodeURIComponent('Re: ' + (contact.subject || ''));
    
    document.getElementById('modalDate').textContent = contact.date_formatted || '—';
    document.getElementById('modalMessage').textContent = contact.message || '';
    
    // Status select
    const statusSelect = document.getElementById('modalStatusSelect');
    statusSelect.value = contact.status || 'pending';
    updatePillSelectClass(statusSelect, contact.status || 'pending');

    // Mailto fallback link
    const mailtoLink = document.getElementById('modalMailtoLink');
    mailtoLink.href = 'mailto:' + encodeURIComponent(contact.email) + '?subject=' + encodeURIComponent('Re: ' + (contact.subject || ''));

    // Previous reply display
    const prevReplyBox = document.getElementById('modalPreviousReplyBox');
    if (contact.admin_reply) {
        prevReplyBox.style.display = 'block';
        document.getElementById('modalPreviousReplyDate').textContent = contact.replied_at || '';
        document.getElementById('modalPreviousReplyText').textContent = contact.admin_reply;
    } else {
        prevReplyBox.style.display = 'none';
    }

    // Reply Form prefill
    const replySubjectInput = document.getElementById('replySubjectInput');
    const existingSubject = contact.subject || 'Your Inquiry';
    replySubjectInput.value = existingSubject.toLowerCase().startsWith('re:') ? existingSubject : 'Re: ' + existingSubject;
    document.getElementById('replyMessageInput').value = '';
    document.getElementById('replyMarkResolved').checked = true;

    // Open overlay
    const overlay = document.getElementById('contactModalOverlay');
    overlay.style.display = 'flex';
    if (window.lucide) lucide.createIcons();

    if (focusReply) {
        setTimeout(() => {
            const textarea = document.getElementById('replyMessageInput');
            textarea.scrollIntoView({ behavior: 'smooth', block: 'center' });
            textarea.focus();
        }, 150);
    }
}

function closeContactModal(event) {
    if (event && event.target !== document.getElementById('contactModalOverlay')) return;
    document.getElementById('contactModalOverlay').style.display = 'none';
}

function getCategoryBadgeClass(cat) {
    const raw = String(cat || '').toLowerCase();
    if (raw.includes('support') || raw.includes('tech')) return 'cat-badge-support';
    if (raw.includes('feedback') || raw.includes('feature')) return 'cat-badge-feedback';
    if (raw.includes('bug') || raw.includes('error') || raw.includes('issue')) return 'cat-badge-bug';
    if (raw.includes('bill') || raw.includes('account') || raw.includes('payment')) return 'cat-badge-billing';
    return 'cat-badge-general';
}

function updatePillSelectClass(selectEl, status) {
    selectEl.className = 'status-pill-select status-' + status;
}

function updateStatus(id, newStatus, selectElement) {
    if (selectElement) {
        updatePillSelectClass(selectElement, newStatus);
    }

    fetch('/admin/contacts/' + id + '/status', {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify({ status: newStatus })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            // Update cached JSON element
            const dataEl = document.getElementById('contact-data-' + id);
            if (dataEl) {
                const c = JSON.parse(dataEl.textContent);
                c.status = newStatus;
                dataEl.textContent = JSON.stringify(c);
            }
            showToast('success', 'Status Updated', 'Query status set to ' + newStatus.replace('_', ' '));
        }
    })
    .catch(err => {
        console.error('Failed to update status:', err);
        showToast('error', 'Error', 'Failed to update status');
    });
}

function updateModalStatus(newStatus) {
    if (!currentContactId) return;
    const modalSelect = document.getElementById('modalStatusSelect');
    updatePillSelectClass(modalSelect, newStatus);

    // Update table select
    const row = document.getElementById('contact-row-' + currentContactId);
    if (row) {
        const rowSelect = row.querySelector('.status-pill-select');
        if (rowSelect) {
            rowSelect.value = newStatus;
            updatePillSelectClass(rowSelect, newStatus);
        }
    }

    updateStatus(currentContactId, newStatus, null);
}

// Submit Direct Email Reply
function submitReply(e) {
    e.preventDefault();
    if (!currentContactId) return;

    const submitBtn = document.getElementById('replySubmitBtn');
    const btnText = document.getElementById('replyBtnText');
    const subject = document.getElementById('replySubjectInput').value.trim();
    const message = document.getElementById('replyMessageInput').value.trim();
    const markResolved = document.getElementById('replyMarkResolved').checked;

    if (!message) {
        showToast('error', 'Message Required', 'Please enter a reply message.');
        return;
    }

    submitBtn.disabled = true;
    btnText.textContent = 'Sending...';

    const payload = {
        subject: subject,
        message: message,
        status: markResolved ? 'resolved' : 'in_progress'
    };

    fetch('/admin/contacts/' + currentContactId + '/reply', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(data => {
        submitBtn.disabled = false;
        btnText.textContent = 'Send Reply';

        if (data.success) {
            // Update cached JSON
            const dataEl = document.getElementById('contact-data-' + currentContactId);
            if (dataEl) {
                const c = JSON.parse(dataEl.textContent);
                c.admin_reply = message;
                c.replied_at = data.contact.replied_at || 'Just now';
                c.status = payload.status;
                dataEl.textContent = JSON.stringify(c);
            }

            // Update UI elements in table
            const row = document.getElementById('contact-row-' + currentContactId);
            if (row) {
                const rowSelect = row.querySelector('.status-pill-select');
                if (rowSelect) {
                    rowSelect.value = payload.status;
                    updatePillSelectClass(rowSelect, payload.status);
                }
                const badgeContainer = document.getElementById('row-replied-badge-' + currentContactId);
                if (badgeContainer) {
                    badgeContainer.innerHTML = '<span class="badge badge-green" style="font-size:10px;padding:1px 7px;">✓ Replied</span>';
                }
            }

            // Update modal elements
            document.getElementById('modalRepliedTag').style.display = 'inline-flex';
            document.getElementById('modalStatusSelect').value = payload.status;
            updatePillSelectClass(document.getElementById('modalStatusSelect'), payload.status);
            
            const prevReplyBox = document.getElementById('modalPreviousReplyBox');
            prevReplyBox.style.display = 'block';
            document.getElementById('modalPreviousReplyDate').textContent = data.contact.replied_at || 'Just now';
            document.getElementById('modalPreviousReplyText').textContent = message;

            // Reset textarea
            document.getElementById('replyMessageInput').value = '';

            showToast('success', 'Reply Sent!', data.message || 'Your email reply has been queued and sent.');
            
            // Re-render lucide icons
            if (window.lucide) lucide.createIcons();
        } else {
            showToast('error', 'Failed to Send', data.message || 'An error occurred while sending reply.');
        }
    })
    .catch(err => {
        submitBtn.disabled = false;
        btnText.textContent = 'Send Reply';
        console.error('Error sending reply:', err);
        showToast('error', 'Network Error', 'Could not reach server to send reply.');
    });
}

// Toast notification helper
let toastTimer = null;
function showToast(type, title, message) {
    const toast = document.getElementById('contactToast');
    const toastTitle = document.getElementById('toastTitle');
    const toastMessage = document.getElementById('toastMessage');
    const toastIcon = document.getElementById('toastIcon');

    toast.className = 'admin-toast toast-' + type;
    toastTitle.textContent = title;
    toastMessage.textContent = message;

    if (type === 'success') {
        toastIcon.innerHTML = '<i data-lucide="check-circle-2" style="width:20px;height:20px;"></i>';
    } else {
        toastIcon.innerHTML = '<i data-lucide="alert-circle" style="width:20px;height:20px;"></i>';
    }
    if (window.lucide) lucide.createIcons();

    toast.style.display = 'flex';

    if (toastTimer) clearTimeout(toastTimer);
    toastTimer = setTimeout(() => {
        hideToast();
    }, 4500);
}

function hideToast() {
    const toast = document.getElementById('contactToast');
    toast.style.display = 'none';
    if (toastTimer) clearTimeout(toastTimer);
}

// Close modal on Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeContactModal();
});

// Initialize Lucide icons
if (window.lucide) {
    lucide.createIcons();
}
</script>
@endpush
