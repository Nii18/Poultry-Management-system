@extends('layouts.master')

@section('title', 'Audit Logs')

@section('content')
<div class="container-fluid px-4">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <div class="d-flex align-items-center gap-3">
                    <div class="page-icon bg-{{ auth()->user()->role === 'admin' ? 'danger' : 'info' }}-soft">
                        <i class="fas fa-history fs-1 text-{{ auth()->user()->role === 'admin' ? 'danger' : 'info' }}"></i>
                    </div>
                    <div>
                        <h1 class="page-title mb-1">Audit Logs</h1>
                        <p class="page-description text-muted mb-0">
                            @if(auth()->user()->role === 'admin')
                                Complete system activity history
                            @else
                                Worker activity and task records
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Action Type</label>
                    <select name="action" class="form-select">
                        <option value="">All Actions</option>
                        <option value="create" {{ request('action') == 'create' ? 'selected' : '' }}>Create</option>
                        <option value="update" {{ request('action') == 'update' ? 'selected' : '' }}>Update</option>
                        <option value="delete" {{ request('action') == 'delete' ? 'selected' : '' }}>Delete</option>
                        <option value="login" {{ request('action') == 'login' ? 'selected' : '' }}>Login</option>
                        <option value="logout" {{ request('action') == 'logout' ? 'selected' : '' }}>Logout</option>
                        <option value="clock_in" {{ request('action') == 'clock_in' ? 'selected' : '' }}>Clock In</option>
                        <option value="clock_out" {{ request('action') == 'clock_out' ? 'selected' : '' }}>Clock Out</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Entity Type</label>
                    <select name="entity_type" class="form-select">
                        <option value="">All Types</option>
                        <option value="flock" {{ request('entity_type') == 'flock' ? 'selected' : '' }}>Flock</option>
                        <option value="task" {{ request('entity_type') == 'task' ? 'selected' : '' }}>Task</option>
                        <option value="expense" {{ request('entity_type') == 'expense' ? 'selected' : '' }}>Expense</option>
                        <option value="treatment" {{ request('entity_type') == 'treatment' ? 'selected' : '' }}>Treatment</option>
                        <option value="worker_attendance" {{ request('entity_type') == 'worker_attendance' ? 'selected' : '' }}>Attendance</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Date Range</label>
                    <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}" placeholder="Start Date">
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter me-2"></i>Filter
                        </button>
                        <a href="{{ route('audit-logs.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>User</th>
                            <th>Role</th>
                            <th>Action</th>
                            <th>Entity</th>
                            <th>Description</th>
                            <th>IP Address</th>
                            @if(auth()->user()->role === 'admin')
                                <th>Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                        <tr>
                            <td>{{ $log->created_at->format('M d, Y H:i:s') }}</td>
                            <td>{{ $log->user->name ?? 'Unknown' }}</td>
                            <td>
                                <span class="badge bg-{{ $log->user_role === 'admin' ? 'danger' : ($log->user_role === 'manager' ? 'primary' : ($log->user_role === 'veterinarian' ? 'info' : ($log->user_role === 'accountant' ? 'warning' : 'secondary'))) }}-soft">
                                    {{ ucfirst($log->user_role) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-secondary-soft">
                                    {{ ucfirst(str_replace('_', ' ', $log->action)) }}
                                </span>
                            </td>
                            <td>
                                @if($log->entity_type)
                                    <span class="badge bg-info-soft">
                                        {{ ucfirst(str_replace('_', ' ', $log->entity_type)) }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <div>
                                    {{ $log->description }}
                                    @if($log->entity_id && auth()->user()->role === 'admin')
                                        <br>
                                        <small class="text-muted">ID: #{{ $log->entity_id }}</small>
                                    @endif
                                </div>
                            </td>
                            <td><code>{{ $log->ip_address ?? 'N/A' }}</code></td>
                            @if(auth()->user()->role === 'admin')
                                <td>
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-info view-details-btn" 
                                            data-log='@json($log)'
                                            title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            @endif
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ auth()->user()->role === 'admin' ? 8 : 7 }}" class="text-center py-5">
                                <div class="empty-state">
                                    <i class="fas fa-history fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">No Audit Logs Found</h5>
                                    <p class="text-muted mb-0">System activities will appear here</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $logs->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>

<!-- View Details Modal -->
@if(auth()->user()->role === 'admin')
<div class="modal fade" id="viewDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-info-circle me-2"></i>Audit Log Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detailsContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2">Loading details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endif

@push('styles')
<style>
    .empty-state {
        text-align: center;
        padding: 3rem;
    }
    
    .badge-soft {
        font-weight: 500;
        padding: 5px 10px;
    }
    
    .bg-danger-soft { background: #fee2e2; color: #991b1b; }
    .bg-primary-soft { background: #dbeafe; color: #1e40af; }
    .bg-info-soft { background: #d1fae5; color: #065f46; }
    .bg-warning-soft { background: #fef3c7; color: #92400e; }
    .bg-secondary-soft { background: #f1f5f9; color: #475569; }
    .bg-success-soft { background: #dcfce7; color: #166534; }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    @if(auth()->user()->role === 'admin')
    // View Details Modal
    $('.view-details-btn').click(function() {
        const log = $(this).data('log');
        const modal = new bootstrap.Modal(document.getElementById('viewDetailsModal'));
        const modalBody = document.getElementById('detailsContent');
        
        let oldValuesHtml = '';
        let newValuesHtml = '';
        
        if (log.old_values && Object.keys(log.old_values).length > 0) {
            oldValuesHtml = `
                <div class="mb-3">
                    <h6 class="fw-bold">Previous Values</h6>
                    <div class="bg-light p-3 rounded" style="max-height: 200px; overflow-y: auto;">
                        <pre class="mb-0 small">${JSON.stringify(log.old_values, null, 2)}</pre>
                    </div>
                </div>
            `;
        }
        
        if (log.new_values && Object.keys(log.new_values).length > 0) {
            newValuesHtml = `
                <div class="mb-3">
                    <h6 class="fw-bold">New Values</h6>
                    <div class="bg-light p-3 rounded" style="max-height: 200px; overflow-y: auto;">
                        <pre class="mb-0 small">${JSON.stringify(log.new_values, null, 2)}</pre>
                    </div>
                </div>
            `;
        }
        
        modalBody.innerHTML = `
            <div class="detail-section">
                <h6>Basic Information</h6>
                <div class="detail-grid">
                    <div class="detail-item">
                        <span class="detail-label">Time</span>
                        <span class="detail-value">${log.created_at}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">User</span>
                        <span class="detail-value">${escapeHtml(log.user?.name || 'Unknown')}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Role</span>
                        <span class="detail-value">${log.user_role}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Action</span>
                        <span class="detail-value">${log.action}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Entity Type</span>
                        <span class="detail-value">${log.entity_type || 'N/A'}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Entity ID</span>
                        <span class="detail-value">${log.entity_id || 'N/A'}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">IP Address</span>
                        <span class="detail-value"><code>${log.ip_address || 'N/A'}</code></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">User Agent</span>
                        <span class="detail-value small">${escapeHtml(log.user_agent || 'N/A')}</span>
                    </div>
                </div>
            </div>
            
            <div class="detail-section">
                <h6>Description</h6>
                <div class="bg-light p-3 rounded">
                    ${escapeHtml(log.description)}
                </div>
            </div>
            
            ${oldValuesHtml}
            ${newValuesHtml}
        `;
        
        modal.show();
    });
    @endif
    
    function escapeHtml(str) {
        if (!str) return '';
        const div = document.createElement('div');
        div.textContent = typeof str === 'object' ? JSON.stringify(str) : str;
        return div.innerHTML;
    }
});
</script>
@endpush