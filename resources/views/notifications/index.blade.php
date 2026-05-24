{{-- resources/views/notifications/index.blade.php --}}
@extends('layouts.master')

@section('content')
<div class="container-fluid px-4">
    <!-- Page Header -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <div class="d-flex align-items-center gap-3">
                    <div class="page-icon bg-info-soft">
                        <i class="fas fa-bell fs-1 text-info"></i>
                    </div>
                    <div>
                        <h1 class="page-title mb-1">Notifications</h1>
                        <p class="page-description text-muted mb-0">View all system alerts and messages</p>
                    </div>
                </div>
            </div>
            <div class="col-auto">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Notifications</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-card-icon bg-primary-soft">
                        <i class="fas fa-bell text-primary"></i>
                    </div>
                    <div class="stat-card-info">
                        <span class="stat-card-label">Total Notifications</span>
                        <h3 class="stat-card-value">{{ $notifications->total() }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-card-icon bg-warning-soft">
                        <i class="fas fa-envelope-open text-warning"></i>
                    </div>
                    <div class="stat-card-info">
                        <span class="stat-card-label">Unread</span>
                        <h3 class="stat-card-value">{{ $notifications->whereNull('read_at')->count() }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-card-icon bg-success-soft">
                        <i class="fas fa-check-circle text-success"></i>
                    </div>
                    <div class="stat-card-info">
                        <span class="stat-card-label">Read</span>
                        <h3 class="stat-card-value">{{ $notifications->whereNotNull('read_at')->count() }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body py-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div class="btn-group" role="group">
                    <a href="{{ route('notifications.index') }}" class="btn btn-sm {{ !request('unread_only') ? 'btn-primary' : 'btn-outline-primary' }}">
                        <i class="fas fa-list me-1"></i> All
                    </a>
                    <a href="{{ route('notifications.index', ['unread_only' => 1]) }}" class="btn btn-sm {{ request('unread_only') ? 'btn-primary' : 'btn-outline-primary' }}">
                        <i class="fas fa-envelope me-1"></i> Unread Only
                    </a>
                </div>
                <div class="d-flex gap-2">
                    <form method="POST" action="{{ route('notifications.mark-all-read') }}" id="markAllReadForm">
                        @csrf
                        <button type="button" class="btn btn-sm btn-primary" onclick="confirmMarkAllRead()">
                            <i class="fas fa-check-double me-1"></i> Mark All as Read
                        </button>
                    </form>
                    <form method="POST" action="{{ route('notifications.clear-all') }}" id="clearAllForm">
                        @csrf
                        <button type="button" class="btn btn-sm btn-danger" onclick="confirmClearAll()">
                            <i class="fas fa-trash-alt me-1"></i> Clear All
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Notifications List -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-0 py-3">
            <div class="d-flex align-items-center">
                <div class="rounded-circle bg-info-soft p-2 me-3">
                    <i class="fas fa-history text-info"></i>
                </div>
                <div>
                    <h5 class="card-title mb-0 fw-semibold">Notification History</h5>
                    <small class="text-muted">Recent alerts and system messages</small>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            @forelse($notifications as $notification)
            <div class="notification-item {{ !$notification->read_at ? 'unread' : '' }}">
                <div class="notification-icon">
                    @if($notification->severity === 'critical')
                        <i class="fas fa-exclamation-circle text-danger"></i>
                    @elseif($notification->severity === 'warning')
                        <i class="fas fa-exclamation-triangle text-warning"></i>
                    @else
                        <i class="fas fa-info-circle text-info"></i>
                    @endif
                </div>
                <div class="notification-content">
                    <div class="notification-header">
                        <h6 class="notification-title">{{ $notification->title }}</h6>
                        <span class="notification-date">{{ $notification->created_at->diffForHumans() }}</span>
                    </div>
                    <p class="notification-message">{{ Str::limit($notification->message, 100) }}</p>
                    <div class="notification-footer">
                        @if($notification->flock)
                            <span class="badge bg-secondary-soft text-secondary">
                                <i class="fas fa-tractor me-1"></i> Flock: {{ $notification->flock->flock_number }}
                            </span>
                        @endif
                        @if(!$notification->read_at)
                            <span class="badge bg-warning-soft text-warning">
                                <i class="fas fa-circle me-1" style="font-size: 8px;"></i> Unread
                            </span>
                        @else
                            <span class="badge bg-success-soft text-success">
                                <i class="fas fa-check-circle me-1"></i> Read
                            </span>
                        @endif
                    </div>
                </div>
                <div class="notification-actions">
                    <div class="btn-group">
                        <button type="button" class="btn btn-sm btn-outline-primary view-notification-btn" 
                                data-id="{{ $notification->id }}" title="View Details">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger delete-notification-btn" 
                                data-id="{{ $notification->id }}" data-title="{{ $notification->title }}" title="Delete">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </div>
            </div>
            @empty
            <div class="empty-state text-center py-5">
                <i class="fas fa-bell-slash fa-4x text-muted mb-3"></i>
                <h5 class="text-muted">No Notifications</h5>
                <p class="text-muted">You're all caught up! No new notifications at this time.</p>
            </div>
            @endforelse
        </div>
        @if($notifications->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            <div class="d-flex justify-content-center">
                {{ $notifications->withQueryString()->links() }}
            </div>
        </div>
        @endif
    </div>
</div>

<!-- View Notification Modal -->
<div class="modal fade" id="viewNotificationModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header" id="notificationModalHeader">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-white bg-opacity-25 p-2 me-3" id="modalIcon">
                        <i class="fas fa-bell fs-5 text-white"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0 text-white" id="notificationModalTitle">Notification Details</h5>
                        <small class="text-white-50" id="notificationModalDate"></small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4" id="notificationModalBody">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading notification details...</p>
                </div>
            </div>
            <div class="modal-footer border-0 px-4 pb-4">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Hidden form for delete -->
<form id="deleteNotificationForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

@push('styles')
<style>
    /* Page Header */
    .page-header {
        margin-bottom: 1.5rem;
    }
    
    .page-icon {
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #e8f4f8 0%, #d1e9f0 100%);
        border-radius: 12px;
    }
    
    .page-title {
        font-size: 1.75rem;
        font-weight: 600;
        color: #1e293b;
    }
    
    .page-description {
        font-size: 0.875rem;
    }
    
    /* Stat Cards */
    .stat-card {
        background: white;
        border-radius: 16px;
        padding: 1rem;
        transition: all 0.3s ease;
        border: 1px solid #e2e8f0;
    }
    
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.05);
    }
    
    .stat-card-body {
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    
    .stat-card-icon {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        font-size: 1.5rem;
    }
    
    .bg-primary-soft { background: #e0f2fe; }
    .bg-success-soft { background: #dcfce7; }
    .bg-warning-soft { background: #fef3c7; }
    .bg-info-soft { background: #d1fae5; }
    .bg-danger-soft { background: #fee2e2; }
    .bg-secondary-soft { background: #f1f5f9; }
    
    .stat-card-info {
        flex: 1;
    }
    
    .stat-card-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #64748b;
        font-weight: 600;
    }
    
    .stat-card-value {
        font-size: 1.75rem;
        font-weight: 700;
        margin: 0;
        line-height: 1.2;
        color: #1e293b;
    }
    
    /* Notification Items */
    .notification-item {
        display: flex;
        align-items: flex-start;
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #e2e8f0;
        transition: all 0.3s ease;
        background: white;
    }
    
    .notification-item:hover {
        background-color: #f8fafc;
    }
    
    .notification-item.unread {
        background: linear-gradient(90deg, #f0f9ff 0%, white 100%);
        border-left: 3px solid #0d6e4f;
    }
    
    .notification-icon {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 40px;
        background: #f8fafc;
        margin-right: 1rem;
        flex-shrink: 0;
    }
    
    .notification-icon i {
        font-size: 1.25rem;
    }
    
    .notification-content {
        flex: 1;
        min-width: 0;
    }
    
    .notification-header {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-bottom: 0.5rem;
    }
    
    .notification-title {
        font-size: 1rem;
        font-weight: 600;
        color: #1e293b;
        margin: 0;
    }
    
    .notification-date {
        font-size: 0.75rem;
        color: #94a3b8;
        white-space: nowrap;
    }
    
    .notification-message {
        font-size: 0.875rem;
        color: #475569;
        margin-bottom: 0.5rem;
        line-height: 1.4;
    }
    
    .notification-footer {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }
    
    .notification-actions {
        margin-left: 1rem;
        flex-shrink: 0;
    }
    
    /* Badge Styles */
    .badge {
        font-weight: 500;
        font-size: 0.7rem;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
    }
    
    .bg-success-soft {
        background-color: #dcfce7;
        color: #166534;
    }
    
    .bg-warning-soft {
        background-color: #fef3c7;
        color: #92400e;
    }
    
    .bg-secondary-soft {
        background-color: #f1f5f9;
        color: #475569;
    }
    
    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 3rem;
    }
    
    /* Button Group */
    .btn-group .btn {
        border-radius: 8px;
        margin: 0 2px;
    }
    
    /* Pagination */
    .pagination {
        margin-bottom: 0;
    }
    
    .page-link {
        border-radius: 8px;
        margin: 0 2px;
        border: none;
        color: #475569;
        padding: 0.5rem 0.875rem;
    }
    
    .page-item.active .page-link {
        background-color: #0d6e4f;
        color: white;
    }
    
    .page-link:hover {
        background-color: #e2e8f0;
        color: #0d6e4f;
    }
    
    /* Modal Header Colors */
    .modal-header.bg-critical {
        background: linear-gradient(135deg, #dc3545 0%, #b02a37 100%);
    }
    
    .modal-header.bg-warning {
        background: linear-gradient(135deg, #ffc107 0%, #d39e00 100%);
    }
    
    .modal-header.bg-info {
        background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .notification-item {
            flex-direction: column;
        }
        
        .notification-icon {
            margin-bottom: 0.75rem;
        }
        
        .notification-actions {
            margin-left: 0;
            margin-top: 0.75rem;
        }
        
        .notification-header {
            flex-direction: column;
        }
        
        .notification-date {
            white-space: normal;
        }
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Store the current notification being viewed
    let currentNotificationRow = null;

    // View Notification Modal
    document.querySelectorAll('.view-notification-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const notificationId = this.dataset.id;
            currentNotificationRow = this.closest('.notification-item');
            
            const modalBody = document.getElementById('notificationModalBody');
            
            // Reset modal content
            modalBody.innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading notification details...</p>
                </div>
            `;
            
            // Fetch JSON data
            fetch(`/notifications/${notificationId}/json`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayNotificationDetails(data.notification);
                        const modalEl = document.getElementById('viewNotificationModal');
                        const modal = new bootstrap.Modal(modalEl);
                        modal.show();
                        
                        // Mark as read when modal is closed
                        modalEl.addEventListener('hidden.bs.modal', function onModalHidden() {
                            markAsReadAndUpdateUI(notificationId);
                            modalEl.removeEventListener('hidden.bs.modal', onModalHidden);
                        });
                    } else {
                        modalBody.innerHTML = `<div class="alert alert-danger">Failed to load: ${data.message}</div>`;
                    }
                })
                .catch(error => {
                    modalBody.innerHTML = `<div class="alert alert-danger">Error: ${error.message}</div>`;
                });
        });
    });
    
    function displayNotificationDetails(notification) {
        const modalHeader = document.getElementById('notificationModalHeader');
        const modalIcon = document.getElementById('modalIcon');
        const modalTitle = document.getElementById('notificationModalTitle');
        const modalDate = document.getElementById('notificationModalDate');
        const modalBody = document.getElementById('notificationModalBody');
        
        // Set header color based on severity
        let headerClass = 'bg-info';
        let iconClass = 'fas fa-info-circle';
        if (notification.severity === 'critical') {
            headerClass = 'bg-danger';
            iconClass = 'fas fa-exclamation-circle';
        } else if (notification.severity === 'warning') {
            headerClass = 'bg-warning';
            iconClass = 'fas fa-exclamation-triangle';
        }
        
        modalHeader.className = `modal-header ${headerClass} text-white border-0`;
        modalIcon.innerHTML = `<i class="${iconClass} fs-5 text-white"></i>`;
        modalTitle.textContent = notification.title;
        modalDate.textContent = notification.created_at;
        
        modalBody.innerHTML = `
            <div class="notification-detail">
                <div class="mb-4">
                    <h6 class="fw-semibold text-muted mb-2">Message</h6>
                    <p class="mb-0">${escapeHtml(notification.message)}</p>
                </div>
                
                ${notification.flock ? `
                <div class="mb-3">
                    <h6 class="fw-semibold text-muted mb-2">Related Flock</h6>
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-tractor me-2"></i>
                        <strong>Flock Number:</strong> ${escapeHtml(notification.flock.flock_number)}
                    </div>
                </div>
                ` : ''}
                
                <div class="mt-3 pt-2 border-top">
                    <div class="row">
                        <div class="col-6">
                            <small class="text-muted">Status</small>
                            <div>
                                <span class="badge bg-success">✓ Marked as Read</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Received</small>
                            <div class="fw-semibold">${notification.time_ago}</div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
    
    function markAsReadAndUpdateUI(notificationId) {
        fetch(`/notifications/${notificationId}/mark-read-ajax`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update the UI for this notification row
                if (currentNotificationRow) {
                    // Remove unread class
                    currentNotificationRow.classList.remove('unread');
                    
                    // Update the status badge
                    const statusBadge = currentNotificationRow.querySelector('.notification-footer .badge:first-child');
                    if (statusBadge && statusBadge.textContent.includes('Unread')) {
                        statusBadge.className = 'badge bg-success-soft text-success';
                        statusBadge.innerHTML = '<i class="fas fa-check-circle me-1"></i> Read';
                    }
                }
                
                // Update the stats counters
                updateStatsCounters();
                
                // Update header and sidebar badges
                updateBadges();
            }
        })
        .catch(error => console.error('Error marking as read:', error));
    }
    
    function updateStatsCounters() {
        // Get current unread count from the stat card
        const unreadCountEl = document.querySelector('.stat-card-value');
        if (unreadCountEl) {
            // Find the unread card (second stat card)
            const statCards = document.querySelectorAll('.stat-card-value');
            if (statCards.length >= 3) {
                let currentUnread = parseInt(statCards[1].textContent) || 0;
                let currentRead = parseInt(statCards[2].textContent) || 0;
                
                if (currentUnread > 0) {
                    statCards[1].textContent = currentUnread - 1;
                    statCards[2].textContent = currentRead + 1;
                }
            }
        }
    }
    
    function updateBadges() {
        fetch('/notifications/unread-count')
            .then(response => response.json())
            .then(data => {
                const headerBadge = document.getElementById('notificationBadge');
                const sidebarBadge = document.getElementById('sidebarNotificationBadge');
                const count = data.count || 0;
                
                if (headerBadge) {
                    if (count > 0) {
                        headerBadge.textContent = count > 99 ? '99+' : count;
                        headerBadge.style.display = 'inline-flex';
                    } else {
                        headerBadge.style.display = 'none';
                    }
                }
                
                if (sidebarBadge) {
                    if (count > 0) {
                        sidebarBadge.textContent = count > 99 ? '99+' : count;
                        sidebarBadge.style.display = 'inline-block';
                    } else {
                        sidebarBadge.style.display = 'none';
                    }
                }
            })
            .catch(error => console.error('Error updating badges:', error));
    }
    
    // Delete Notification
    document.querySelectorAll('.delete-notification-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const notificationId = this.dataset.id;
            const notificationTitle = this.dataset.title;
            
            Swal.fire({
                title: 'Delete Notification',
                text: `Are you sure you want to delete "${notificationTitle}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.getElementById('deleteNotificationForm');
                    form.action = `/notifications/${notificationId}`;
                    form.submit();
                }
            });
        });
    });
    
    function confirmMarkAllRead() {
        Swal.fire({
            title: 'Mark All as Read',
            text: 'Are you sure you want to mark all notifications as read?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0d6e4f',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, mark all read',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('/notifications/read-all', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    }
                })
                .then(() => {
                    window.location.reload();
                });
            }
        });
    }
    
    function confirmClearAll() {
        Swal.fire({
            title: 'Clear All Notifications',
            text: 'This will permanently delete all notifications. This action cannot be undone!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, clear all',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('clearAllForm').submit();
            }
        });
    }
    
    function escapeHtml(str) {
        if (!str) return '';
        return String(str).replace(/[&<>]/g, function(m) {
            if (m === '&') return '&amp;';
            if (m === '<') return '&lt;';
            if (m === '>') return '&gt;';
            return m;
        });
    }
</script>
@endpush
@endsection