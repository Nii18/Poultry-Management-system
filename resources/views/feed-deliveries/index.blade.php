{{-- resources/views/feed-deliveries/index.blade.php --}}
@extends('layouts.master')

@section('content')
<div class="container-fluid px-4">
    <!-- Page Header -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <div class="d-flex align-items-center gap-3">
                    <div class="page-icon">
                        <i class="fas fa-truck fs-1 text-primary"></i>
                    </div>
                    <div>
                        <h1 class="page-title mb-1">Feed Deliveries</h1>
                        <p class="page-description text-muted mb-0">Track all feed purchases and inventory</p>
                    </div>
                </div>
            </div>
            <div class="col-auto">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item">Feed</li>
                        <li class="breadcrumb-item active">Deliveries</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-card-icon bg-primary-soft">
                        <i class="fas fa-truck text-primary"></i>
                    </div>
                    <div class="stat-card-info">
                        <span class="stat-card-label">Total Deliveries</span>
                        <h3 class="stat-card-value">{{ $deliveries->total() }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-card-icon bg-success-soft">
                        <i class="fas fa-weight-hanging text-success"></i>
                    </div>
                    <div class="stat-card-info">
                        <span class="stat-card-label">Total Quantity</span>
                        <h3 class="stat-card-value">{{ number_format($deliveries->sum('quantity_kg')) }} kg</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-card-icon bg-warning-soft">
                        <i class="fas fa-dollar-sign text-warning"></i>
                    </div>
                    <div class="stat-card-info">
                        <span class="stat-card-label">Total Cost</span>
                        <h3 class="stat-card-value">${{ number_format($deliveries->sum('total_cost'), 2) }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-card-icon bg-info-soft">
                        <i class="fas fa-chart-line text-info"></i>
                    </div>
                    <div class="stat-card-info">
                        <span class="stat-card-label">Avg Cost/kg</span>
                        <h3 class="stat-card-value">${{ number_format($deliveries->avg('cost_per_kg'), 2) }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Card -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-0 py-3">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="card-title mb-0 fw-semibold">
                        <i class="fas fa-list me-2 text-primary"></i>Feed Delivery Records
                    </h5>
                </div>
                <div class="col-auto">
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createDeliveryModal">
                            <i class="fas fa-plus me-2"></i>New Delivery
                        </button>
                        <a href="{{ route('feed-deliveries.low-stock') }}" class="btn btn-outline-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>Low Stock Alerts
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card-body">
            <!-- Filters Section -->
            <div class="filter-section mb-4 p-3 bg-light rounded-3">
                <div class="row align-items-end g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold mb-2">
                            <i class="fas fa-seedling me-1 text-dark"></i>Feed Type
                        </label>
                        <select name="feed_type_id" class="form-select" id="feedTypeFilter">
                            <option value="">All Types</option>
                            @foreach($feedTypes as $type)
                                <option value="{{ $type->id }}" {{ request('feed_type_id') == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold mb-2">
                            <i class="fas fa-calendar me-1" style="color:#64748b;"></i>Start Date
                        </label>
                        <input type="date" name="start_date" class="form-control" id="startDateFilter" value="{{ $startDate->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold mb-2">
                            <i class="fas fa-calendar-end me-1" style="color:#64748b;"></i>End Date
                        </label>
                        <input type="date" name="end_date" class="form-control" id="endDateFilter" value="{{ $endDate->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-primary px-4" id="applyFilters">
                                <i class="fas fa-filter me-2"></i>Apply Filters
                            </button>
                            <a href="{{ route('feed-deliveries.index') }}" class="btn btn-outline-secondary px-4">
                                <i class="fas fa-redo-alt me-2"></i>Reset
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Deliveries Table - Borderless like Flocks -->
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="py-3">Date</th>
                            <th class="py-3">Feed Type</th>
                            <th class="py-3">Supplier</th>
                            <th class="py-3">Quantity (kg)</th>
                            <th class="py-3">Cost/kg</th>
                            <th class="py-3">Total Cost</th>
                            <th class="py-3">Remaining</th>
                            <th class="py-3">Batch Number</th>
                            <th class="py-3 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($deliveries as $delivery)
                        <tr>
                            <td>{{ $delivery->delivery_date->format('d M Y') }}</td>
                            <td>
                                <button type="button" class="btn btn-link p-0 text-primary fw-semibold text-decoration-none view-delivery-btn" 
                                        data-id="{{ $delivery->id }}" data-bs-toggle="modal" data-bs-target="#viewDeliveryModal">
                                    {{ $delivery->feedType->name ?? 'N/A' }}
                                </button>
                            </td>
                            <td>{{ $delivery->supplier_name }}</td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-semibold">{{ number_format($delivery->quantity_kg) }}</span>
                                    <small class="text-muted">kg</small>
                                </div>
                            </td>
                            <td>${{ number_format($delivery->cost_per_kg, 2) }}</td>
                            <td>${{ number_format($delivery->total_cost, 2) }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height: 6px;">
                                        <div class="progress-bar bg-{{ $delivery->remaining_quantity_kg < 500 ? 'warning' : 'success' }}" 
                                             style="width: {{ ($delivery->quantity_kg - $delivery->remaining_quantity_kg) / $delivery->quantity_kg * 100 }}%"></div>
                                    </div>
                                    <span class="small fw-semibold {{ $delivery->remaining_quantity_kg < 500 ? 'text-warning' : 'text-success' }}">
                                        {{ number_format($delivery->remaining_quantity_kg) }} kg
                                    </span>
                                </div>
                            </td>
                            <td>
                                <code>{{ $delivery->batch_number ?? 'N/A' }}</code>
                            </td>
                            <td>
                                <div class="btn-group gap-1">
                                    <button type="button" class="btn btn-sm btn-outline-primary view-delivery-btn" 
                                            data-id="{{ $delivery->id }}" data-bs-toggle="modal" data-bs-target="#viewDeliveryModal" title="View Details">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-warning edit-delivery-btn" 
                                            data-id="{{ $delivery->id }}" data-bs-toggle="modal" data-bs-target="#editDeliveryModal" title="Edit">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    @if(auth()->user()->role === 'admin')
                                        <button type="button" class="btn btn-sm btn-outline-danger delete-delivery-btn" 
                                                data-id="{{ $delivery->id }}"
                                                data-delivery-info="{{ $delivery->feedType->name ?? 'N/A' }} - {{ $delivery->delivery_date->format('Y-m-d') }}" 
                                                title="Delete">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <div class="empty-state">
                                    <i class="fas fa-truck fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">No Feed Deliveries Found</h5>
                                    <p class="text-muted mb-3">Get started by recording your first delivery</p>
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createDeliveryModal">
                                        <i class="fas fa-plus me-2"></i>Record Delivery
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Enhanced Pagination -->
            @if($deliveries->hasPages())
            <div class="d-flex justify-content-between align-items-center mt-4">
                <div class="text-muted">
                    Showing {{ $deliveries->firstItem() }} to {{ $deliveries->lastItem() }} of {{ $deliveries->total() }} deliveries
                </div>
                <nav>
                    <ul class="pagination mb-0">
                        @if($deliveries->onFirstPage())
                            <li class="page-item disabled">
                                <span class="page-link">‹ Previous</span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link" href="{{ $deliveries->previousPageUrl() }}" rel="prev">‹ Previous</a>
                            </li>
                        @endif

                        @php
                            $current = $deliveries->currentPage();
                            $last = $deliveries->lastPage();
                            $start = max(1, $current - 2);
                            $end = min($last, $current + 2);
                        @endphp

                        @if($start > 1)
                            <li class="page-item">
                                <a class="page-link" href="{{ $deliveries->url(1) }}">1</a>
                            </li>
                            @if($start > 2)
                                <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                            @endif
                        @endif

                        @for($page = $start; $page <= $end; $page++)
                            @if($page == $current)
                                <li class="page-item active">
                                    <span class="page-link">{{ $page }}</span>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link" href="{{ $deliveries->url($page) }}">{{ $page }}</a>
                                </li>
                            @endif
                        @endfor

                        @if($end < $last)
                            @if($end < $last - 1)
                                <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                            @endif
                            <li class="page-item">
                                <a class="page-link" href="{{ $deliveries->url($last) }}">{{ $last }}</a>
                            </li>
                        @endif

                        @if($deliveries->hasMorePages())
                            <li class="page-item">
                                <a class="page-link" href="{{ $deliveries->nextPageUrl() }}" rel="next">Next ›</a>
                            </li>
                        @else
                            <li class="page-item disabled">
                                <span class="page-link">Next ›</span>
                            </li>
                        @endif
                    </ul>
                </nav>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Create Feed Delivery Modal -->
<div class="modal fade" id="createDeliveryModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title text-white">
                    <i class="fas fa-plus-circle me-2"></i>Record Feed Delivery
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('feed-deliveries.store') }}">
                @csrf
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Feed Type <span class="text-danger">*</span></label>
                            <select name="feed_type_id" class="form-select" required>
                                <option value="">Select Feed Type</option>
                                @foreach($feedTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }} ({{ ucfirst($type->category) }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Delivery Date <span class="text-danger">*</span></label>
                            <input type="date" name="delivery_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Supplier <span class="text-danger">*</span></label>
                            <input type="text" name="supplier_name" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Invoice Number</label>
                            <input type="text" name="invoice_number" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Quantity (kg) <span class="text-danger">*</span></label>
                            <input type="number" name="quantity_kg" class="form-control" step="0.01" min="0.01" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Cost per kg ($) <span class="text-danger">*</span></label>
                            <input type="number" name="cost_per_kg" class="form-control" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Expiry Date</label>
                            <input type="date" name="expiry_date" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Batch Number</label>
                            <input type="text" name="batch_number" id="batchNumberField" class="form-control" readonly style="background:#f8fafc; color:#64748b;">
                            <small class="text-muted">Auto-generated</small>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label fw-semibold">Notes</label>
                            <textarea name="notes" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Record Delivery
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Delivery Modal -->
<div class="modal fade" id="viewDeliveryModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title text-white">
                    <i class="fas fa-eye me-2"></i>Delivery Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewDeliveryContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2">Loading delivery details...</p>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Delivery Modal -->
<div class="modal fade" id="editDeliveryModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-warning text-white border-0">
                <h5 class="modal-title text-white">
                    <i class="fas fa-edit me-2"></i>Edit Delivery
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="editDeliveryContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-warning" role="status"></div>
                    <p class="mt-2">Loading delivery details...</p>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="saveEditDelivery">Update Delivery</button>
            </div>
        </div>
    </div>
</div>

<!-- Hidden delete form -->
<form id="deleteDeliveryForm" method="POST" style="display:none;">
    @csrf
    @method('DELETE')
</form>

@push('styles')
<style>
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
    
    .filter-section {
        background: #f8fafc;
        border-radius: 12px;
    }
    
    .table th {
        font-weight: 600;
        font-size: 0.875rem;
        color: #475569;
        border-bottom-width: 1px;
    }
    
    .table td {
        font-size: 0.875rem;
        color: #334155;
        vertical-align: middle;
    }
    
    .progress {
        background-color: #e2e8f0;
        border-radius: 10px;
        overflow: hidden;
    }
    
    .btn-group .btn {
        border-radius: 8px !important;
        margin: 0 2px;
        padding: 0.25rem 0.5rem;
    }
    
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
    
    .detail-section {
        margin-bottom: 1.5rem;
    }
    
    .detail-section h6 {
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #e2e8f0;
    }
    
    .detail-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1rem;
    }
    
    .detail-item {
        display: flex;
        flex-direction: column;
    }
    
    .detail-label {
        font-size: 0.7rem;
        text-transform: uppercase;
        color: #64748b;
        font-weight: 600;
        margin-bottom: 0.25rem;
    }
    
    .detail-value {
        font-size: 1rem;
        font-weight: 500;
        
        color: #1e293b;
    }

    /* Quick fix for all white text issues */
label, .form-label, .modal-body, .modal-body p, 
.detail-section p, .detail-value, .bg-light p,
.card-body, .card-body p {
    color: #1e293b !important;
}

.modal-body .bg-light {
    background-color: #f1f5f9 !important;
}

.form-control, .form-select {
    background-color: #ffffff !important;
    color: #1e293b !important;
}

.form-control::placeholder {
    color: #94a3b8 !important;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.getElementById('applyFilters')?.addEventListener('click', function() {
        const feedTypeId = document.getElementById('feedTypeFilter').value;
        const startDate = document.getElementById('startDateFilter').value;
        const endDate = document.getElementById('endDateFilter').value;
        let url = '{{ route("feed-deliveries.index") }}';
        const params = new URLSearchParams();
        
        if (feedTypeId) params.append('feed_type_id', feedTypeId);
        if (startDate) params.append('start_date', startDate);
        if (endDate) params.append('end_date', endDate);
        
        if (params.toString()) {
            url += '?' + params.toString();
        }
        
        window.location.href = url;
    });

    // Auto-generate batch number
    (function() {
        const field = document.getElementById('batchNumberField');
        if (field) {
            const now = new Date();
            const pad = n => String(n).padStart(2, '0');
            const batch = `FD-${now.getFullYear()}${pad(now.getMonth()+1)}${pad(now.getDate())}-${Math.random().toString(36).substr(2,5).toUpperCase()}`;
            field.value = batch;
        }
    })();

    function escapeHtml(str) {
        if (!str) return '';
        return String(str).replace(/[&<>]/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;'}[m]));
    }

    // View Delivery
    document.querySelectorAll('.view-delivery-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const modalBody = document.getElementById('viewDeliveryContent');
            modalBody.innerHTML = `<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Loading...</p></div>`;
            
            fetch(`/feed-deliveries/${id}/details`)
                .then(r => r.json())
                .then(data => {
                    if (!data.success) { 
                        modalBody.innerHTML = `<div class="alert alert-danger">${data.message}</div>`; 
                        return; 
                    }
                    const d = data.delivery;
                    modalBody.innerHTML = `
                        <div class="detail-section">
                            <h6><i class="fas fa-info-circle me-2"></i>Delivery Information</h6>
                            <div class="detail-grid">
                                <div class="detail-item"><span class="detail-label">Date</span><span class="detail-value">${d.delivery_date}</span></div>
                                <div class="detail-item"><span class="detail-label">Feed Type</span><span class="detail-value">${escapeHtml(d.feed_type_name)}</span></div>
                                <div class="detail-item"><span class="detail-label">Supplier</span><span class="detail-value">${escapeHtml(d.supplier_name)}</span></div>
                                <div class="detail-item"><span class="detail-label">Invoice</span><span class="detail-value">${escapeHtml(d.invoice_number || 'N/A')}</span></div>
                                <div class="detail-item"><span class="detail-label">Batch Number</span><span class="detail-value"><code>${escapeHtml(d.batch_number || 'N/A')}</code></span></div>
                                <div class="detail-item"><span class="detail-label">Expiry Date</span><span class="detail-value">${d.expiry_date || 'N/A'}</span></div>
                            </div>
                        </div>
                        <div class="detail-section">
                            <h6><i class="fas fa-chart-line me-2"></i>Quantity & Cost</h6>
                            <div class="detail-grid">
                                <div class="detail-item"><span class="detail-label">Quantity</span><span class="detail-value">${d.quantity_kg.toLocaleString()} kg</span></div>
                                <div class="detail-item"><span class="detail-label">Cost/kg</span><span class="detail-value">$${d.cost_per_kg.toFixed(2)}</span></div>
                                <div class="detail-item"><span class="detail-label">Total Cost</span><span class="detail-value">$${d.total_cost.toLocaleString()}</span></div>
                                <div class="detail-item"><span class="detail-label">Remaining</span><span class="detail-value">${d.remaining_quantity_kg.toLocaleString()} kg</span></div>
                                <div class="detail-item"><span class="detail-label">Usage</span><span class="detail-value">${d.usage_percentage.toFixed(1)}%</span></div>
                                <div class="detail-item"><span class="detail-label">Received By</span><span class="detail-value">${escapeHtml(d.received_by_name)}</span></div>
                            </div>
                        </div>
                        ${d.notes ? `<div class="detail-section"><h6><i class="fas fa-sticky-note me-2"></i>Notes</h6><p class="mb-0 p-3 bg-light rounded">${escapeHtml(d.notes)}</p></div>` : ''}
                    `;
                })
                .catch(() => { modalBody.innerHTML = `<div class="alert alert-danger">Error loading data.</div>`; });
        });
    });

    // Edit Delivery
    document.querySelectorAll('.edit-delivery-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            window.currentEditDeliveryId = id;
            const modalBody = document.getElementById('editDeliveryContent');
            modalBody.innerHTML = `<div class="text-center py-4"><div class="spinner-border text-warning" role="status"></div><p class="mt-2">Loading...</p></div>`;
            
            fetch(`/feed-deliveries/${id}/edit-data`)
                .then(r => r.json())
                .then(data => {
                    if (!data.success) { 
                        modalBody.innerHTML = `<div class="alert alert-danger">${data.message}</div>`; 
                        return; 
                    }
                    const d = data.delivery;
                    const opts = data.feedTypes.map(t => `<option value="${t.id}" ${d.feed_type_id == t.id ? 'selected' : ''}>${escapeHtml(t.name)} (${t.category})</option>`).join('');
                    modalBody.innerHTML = `
                        <form id="editDeliveryForm">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Feed Type <span class="text-danger">*</span></label>
                                    <select name="feed_type_id" class="form-select" required>${opts}</select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Delivery Date <span class="text-danger">*</span></label>
                                    <input type="date" name="delivery_date" class="form-control" value="${d.delivery_date}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Supplier <span class="text-danger">*</span></label>
                                    <input type="text" name="supplier_name" class="form-control" value="${escapeHtml(d.supplier_name)}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Invoice Number</label>
                                    <input type="text" name="invoice_number" class="form-control" value="${escapeHtml(d.invoice_number || '')}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Quantity (kg) <span class="text-danger">*</span></label>
                                    <input type="number" name="quantity_kg" class="form-control" step="0.01" min="0.01" value="${d.quantity_kg}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Cost per kg ($) <span class="text-danger">*</span></label>
                                    <input type="number" name="cost_per_kg" class="form-control" step="0.01" min="0" value="${d.cost_per_kg}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Remaining Stock (kg)</label>
                                    <input type="number" name="remaining_quantity_kg" class="form-control" step="0.01" min="0" value="${d.remaining_quantity_kg}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Expiry Date</label>
                                    <input type="date" name="expiry_date" class="form-control" value="${d.expiry_date || ''}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Batch Number</label>
                                    <input type="text" name="batch_number" class="form-control" value="${escapeHtml(d.batch_number || '')}" readonly style="background:#f8fafc; color:#64748b;">
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label fw-semibold">Notes</label>
                                    <textarea name="notes" class="form-control" rows="3">${escapeHtml(d.notes || '')}</textarea>
                                </div>
                            </div>
                        </form>`;
                })
                .catch(() => { modalBody.innerHTML = `<div class="alert alert-danger">Error loading data.</div>`; });
        });
    });

    document.getElementById('saveEditDelivery')?.addEventListener('click', function() {
        const form = document.getElementById('editDeliveryForm');
        const data = {};
        new FormData(form).forEach((v, k) => { data[k] = v; });
        
        fetch(`/feed-deliveries/${window.currentEditDeliveryId}`, {
            method: 'PUT',
            headers: { 
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 
                'Accept': 'application/json', 
                'Content-Type': 'application/json' 
            },
            body: JSON.stringify(data)
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                Swal.fire({ icon: 'success', title: 'Updated!', timer: 1500, showConfirmButton: false })
                    .then(() => location.reload());
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: data.message });
            }
        });
    });

    // Delete Delivery
    document.querySelectorAll('.delete-delivery-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const info = this.dataset.deliveryInfo;
            Swal.fire({ 
                title: 'Delete Delivery?', 
                text: `Delete: "${info}"? This action cannot be undone.`, 
                icon: 'warning', 
                showCancelButton: true, 
                confirmButtonColor: '#dc2626' 
            })
            .then(result => {
                if (result.isConfirmed) {
                    const form = document.getElementById('deleteDeliveryForm');
                    form.action = `/feed-deliveries/${id}`;
                    form.submit();
                }
            });
        });
    });
</script>
@endpush
@endsection