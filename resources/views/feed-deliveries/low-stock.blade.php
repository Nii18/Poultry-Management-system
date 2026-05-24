{{-- resources/views/feed-deliveries/low-stock.blade.php --}}
@extends('layouts.master')

@section('title', 'Feed Inventory Alerts')

@section('content')
<div class="container-fluid px-4">
    <!-- Modern Header -->
    <div class="report-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <div class="d-flex align-items-center gap-3">
                    <div class="header-icon bg-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div>
                        <h1 class="header-title mb-1">Feed Inventory Alerts</h1>
                        <p class="header-subtitle text-muted mb-0">Monitor low stock and expired feed inventory</p>
                    </div>
                </div>
            </div>
            <div class="col-auto">
                <div class="action-buttons">
                    <a href="{{ route('feed-deliveries.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Deliveries
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Summary Cards -->
    <div class="stats-grid mb-4">
        <div class="stat-card critical">
            <div class="stat-icon bg-critical">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <div class="stat-details">
                <span class="stat-label">Critical Stock</span>
                <h2 class="stat-value">{{ $lowStockDeliveries->where('remaining_quantity_kg', '<', 200)->count() }}</h2>
                <span class="stat-trend">Below 200kg - Urgent</span>
            </div>
        </div>
        <div class="stat-card warning">
            <div class="stat-icon bg-warning">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stat-details">
                <span class="stat-label">Low Stock Alert</span>
                <h2 class="stat-value">{{ $lowStockDeliveries->count() }}</h2>
                <span class="stat-trend">Below 500kg - Monitor</span>
            </div>
        </div>
        <div class="stat-card expired">
            <div class="stat-icon bg-expired">
                <i class="fas fa-calendar-times"></i>
            </div>
            <div class="stat-details">
                <span class="stat-label">Expired Stock</span>
                <h2 class="stat-value">{{ $expiredStock->count() }}</h2>
                <span class="stat-trend">Past expiry date</span>
            </div>
        </div>
        <div class="stat-card total">
            <div class="stat-icon bg-total">
                <i class="fas fa-warehouse"></i>
            </div>
            <div class="stat-details">
                <span class="stat-label">Total at Risk</span>
                <h2 class="stat-value">{{ $lowStockDeliveries->count() + $expiredStock->count() }}</h2>
                <span class="stat-trend">Items needing attention</span>
            </div>
        </div>
    </div>

    <!-- Low Stock Section -->
    <div class="alert-section mb-4">
        <div class="section-header">
            <div class="d-flex align-items-center gap-2">
                <div class="section-icon bg-warning-soft">
                    <i class="fas fa-chart-line text-warning"></i>
                </div>
                <h5 class="section-title mb-0">Low Stock Inventory <span class="badge bg-warning ms-2">{{ $lowStockDeliveries->count() }} items</span></h5>
            </div>
            <p class="section-description text-muted mb-0">Feed types below 500kg threshold that require reordering</p>
        </div>

        @if($lowStockDeliveries->count() > 0)
            <div class="row g-4 mt-2">
                @foreach($lowStockDeliveries as $delivery)
                <div class="col-xl-4 col-lg-6">
                    <div class="alert-card low-stock {{ $delivery->remaining_quantity_kg < 200 ? 'critical' : '' }}">
                        <div class="alert-card-header">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="feed-type">{{ $delivery->feedType->name ?? 'N/A' }}</h6>
                                    <span class="feed-category">{{ ucfirst($delivery->feedType->category ?? 'N/A') }}</span>
                                </div>
                                <div class="stock-level {{ $delivery->remaining_quantity_kg < 200 ? 'critical-level' : 'warning-level' }}">
                                    {{ $delivery->remaining_quantity_kg < 200 ? 'CRITICAL' : 'LOW' }}
                                </div>
                            </div>
                        </div>
                        <div class="alert-card-body">
                            <div class="stock-gauge">
                                <div class="gauge-label">Stock Level</div>
                                <div class="progress stock-progress">
                                    <div class="progress-bar {{ $delivery->remaining_quantity_kg < 200 ? 'bg-danger' : 'bg-warning' }}" 
                                         style="width: {{ ($delivery->remaining_quantity_kg / $delivery->quantity_kg) * 100 }}%">
                                    </div>
                                </div>
                                <div class="gauge-values">
                                    <span>{{ number_format($delivery->remaining_quantity_kg) }} kg</span>
                                    <span>of {{ number_format($delivery->quantity_kg) }} kg</span>
                                </div>
                            </div>
                            <div class="info-row">
                                <div class="info-item">
                                    <span class="info-label">Supplier</span>
                                    <strong class="info-value">{{ $delivery->supplier_name }}</strong>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Batch Number</span>
                                    <strong class="info-value">{{ $delivery->batch_number ?? 'N/A' }}</strong>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Expiry Date</span>
                                    <strong class="info-value {{ $delivery->expiry_date && $delivery->expiry_date <= now()->addDays(30) ? 'text-warning' : '' }}">
                                        {{ $delivery->expiry_date ? $delivery->expiry_date->format('d M Y') : 'N/A' }}
                                        @if($delivery->expiry_date && $delivery->expiry_date <= now()->addDays(30))
                                            <i class="fas fa-clock ms-1"></i>
                                        @endif
                                    </strong>
                                </div>
                            </div>
                        </div>
                        <div class="alert-card-footer">
                            <div class="btn-group w-100">
                                <button type="button" class="btn btn-outline-primary btn-sm view-delivery-btn" 
                                        data-id="{{ $delivery->id }}" data-bs-toggle="modal" data-bs-target="#viewDeliveryModal">
                                    <i class="fas fa-eye me-1"></i>View Details
                                </button>
                                <button type="button" class="btn btn-outline-warning btn-sm edit-delivery-btn" 
                                        data-id="{{ $delivery->id }}" data-bs-toggle="modal" data-bs-target="#editDeliveryModal">
                                    <i class="fas fa-edit me-1"></i>Edit
                                </button>
                                @if(auth()->user()->role === 'admin')
                                    <button type="button" class="btn btn-outline-danger btn-sm delete-delivery-btn" 
                                            data-id="{{ $delivery->id }}" data-delivery-info="{{ $delivery->feedType->name ?? 'N/A' }} - {{ $delivery->delivery_date->format('Y-m-d') }}">
                                        <i class="fas fa-trash-alt me-1"></i>Delete
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <div class="empty-state-small">
                <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                <p class="mb-0 text-success">All feed levels are adequate! No low stock alerts.</p>
            </div>
        @endif
    </div>

    <!-- Expired Stock Section -->
    <div class="alert-section expired-section">
        <div class="section-header">
            <div class="d-flex align-items-center gap-2">
                <div class="section-icon bg-danger-soft">
                    <i class="fas fa-calendar-times text-danger"></i>
                </div>
                <h5 class="section-title mb-0">Expired Stock <span class="badge bg-danger ms-2">{{ $expiredStock->count() }} items</span></h5>
            </div>
            <p class="section-description text-muted mb-0">Feed inventory that has passed its expiry date</p>
        </div>

        @if($expiredStock->count() > 0)
            <div class="row g-4 mt-2">
                @foreach($expiredStock as $delivery)
                <div class="col-xl-4 col-lg-6">
                    <div class="alert-card expired">
                        <div class="alert-card-header">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="feed-type">{{ $delivery->feedType->name ?? 'N/A' }}</h6>
                                    <span class="feed-category">{{ ucfirst($delivery->feedType->category ?? 'N/A') }}</span>
                                </div>
                                <div class="stock-level expired-level">EXPIRED</div>
                            </div>
                        </div>
                        <div class="alert-card-body">
                            <div class="info-row">
                                <div class="info-item">
                                    <span class="info-label">Supplier</span>
                                    <strong class="info-value">{{ $delivery->supplier_name }}</strong>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Batch Number</span>
                                    <strong class="info-value">{{ $delivery->batch_number ?? 'N/A' }}</strong>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Remaining Stock</span>
                                    <strong class="info-value text-danger">{{ number_format($delivery->remaining_quantity_kg) }} kg</strong>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Expired Since</span>
                                    <strong class="info-value text-danger">
                                        {{ $delivery->expiry_date->format('d M Y') }}
                                        @php $daysOverdue = now()->diffInDays($delivery->expiry_date, false); @endphp
                                        <span class="badge bg-danger ms-2">{{ abs($daysOverdue) }} days overdue</span>
                                    </strong>
                                </div>
                            </div>
                        </div>
                        <div class="alert-card-footer">
                            <button type="button" class="btn btn-outline-danger btn-sm w-100 view-delivery-btn" 
                                    data-id="{{ $delivery->id }}" data-bs-toggle="modal" data-bs-target="#viewDeliveryModal">
                                <i class="fas fa-eye me-1"></i>View Details
                            </button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <div class="empty-state-small">
                <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                <p class="mb-0 text-success">No expired stock found! All feed is within date.</p>
            </div>
        @endif
    </div>
</div>

<!-- View Delivery Modal -->
<div class="modal fade" id="viewDeliveryModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white">
                    <i class="fas fa-eye me-2"></i>Delivery Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewDeliveryContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading delivery details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Delivery Modal -->
<div class="modal fade" id="editDeliveryModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title text-white">
                    <i class="fas fa-edit me-2"></i>Edit Delivery
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="editDeliveryContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-warning" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading delivery details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="saveEditDelivery">Update Delivery</button>
            </div>
        </div>
    </div>
</div>

<!-- Hidden forms for AJAX actions -->
<form id="deleteDeliveryForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

@push('styles')
<style>
    /* Header Styles */
    .report-header { margin-bottom: 1.5rem; }
    .header-icon { width: 55px; height: 55px; border-radius: 14px; display: flex; align-items: center; justify-content: center; }
    .header-icon i { font-size: 26px; color: white; }
    .header-icon.bg-warning { background: linear-gradient(135deg, #f59e0b, #d97706); }
    .header-title { font-size: 26px; font-weight: 700; color: #1e293b; letter-spacing: -0.02em; margin: 0; }
    .header-subtitle { font-size: 14px; margin: 0; }
    .action-buttons { display: flex; gap: 0.75rem; }
    
    /* Stats Grid */
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem; margin-bottom: 1.5rem; }
    .stat-card { background: white; border-radius: 16px; padding: 1rem; display: flex; align-items: center; gap: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; transition: all 0.3s ease; }
    .stat-card:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0,0,0,0.08); }
    .stat-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; }
    .stat-icon i { font-size: 22px; color: white; }
    .bg-critical { background: linear-gradient(135deg, #dc2626, #b91c1c); }
    .bg-warning { background: linear-gradient(135deg, #f59e0b, #d97706); }
    .bg-expired { background: linear-gradient(135deg, #6b7280, #4b5563); }
    .bg-total { background: linear-gradient(135deg, #0d6e4f, #0a5a40); }
    .stat-details { flex: 1; }
    .stat-label { font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; font-weight: 600; }
    .stat-value { font-size: 28px; font-weight: 700; color: #1e293b; margin: 0.15rem 0; line-height: 1.2; }
    .stat-trend { font-size: 11px; color: #64748b; }
    
    /* Section Header */
    .section-header { margin-bottom: 1rem; }
    .section-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; }
    .bg-warning-soft { background: #fef3c7; }
    .bg-danger-soft { background: #fee2e2; }
    .section-title { font-size: 18px; font-weight: 600; color: #1e293b; }
    .section-description { font-size: 13px; margin-top: 0.25rem; }
    
    /* Alert Cards */
    .alert-card { background: white; border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden; transition: all 0.3s ease; height: 100%; }
    .alert-card:hover { transform: translateY(-3px); box-shadow: 0 12px 30px rgba(0,0,0,0.1); }
    .alert-card.low-stock { border-left: 4px solid #f59e0b; }
    .alert-card.low-stock.critical { border-left: 4px solid #dc2626; }
    .alert-card.expired { border-left: 4px solid #6b7280; opacity: 0.85; }
    .alert-card-header { padding: 1rem 1.25rem; background: #f8fafc; border-bottom: 1px solid #e2e8f0; }
    .feed-type { font-size: 16px; font-weight: 700; color: #1e293b; margin: 0 0 0.25rem 0; }
    .feed-category { font-size: 11px; background: #e2e8f0; padding: 0.2rem 0.5rem; border-radius: 10px; color: #64748b; }
    
    .stock-level { padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 11px; font-weight: 700; letter-spacing: 0.5px; }
    .warning-level { background: #fef3c7; color: #d97706; }
    .critical-level { background: #fee2e2; color: #dc2626; }
    .expired-level { background: #f1f5f9; color: #475569; }
    
    .alert-card-body { padding: 1rem 1.25rem; }
    .stock-gauge { margin-bottom: 1rem; }
    .gauge-label { font-size: 11px; color: #64748b; margin-bottom: 0.5rem; }
    .stock-progress { height: 6px; border-radius: 10px; background: #e2e8f0; }
    .stock-progress .progress-bar { border-radius: 10px; }
    .gauge-values { display: flex; justify-content: space-between; margin-top: 0.5rem; font-size: 12px; }
    .gauge-values span:first-child { font-weight: 700; color: #1e293b; }
    .gauge-values span:last-child { color: #64748b; }
    
    .info-row { display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.75rem; }
    .info-item { display: flex; flex-direction: column; gap: 0.25rem; }
    .info-label { font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; }
    .info-value { font-size: 13px; color: #1e293b; }
    
    .alert-card-footer { padding: 0.75rem 1.25rem; background: #f8fafc; border-top: 1px solid #e2e8f0; }
    .btn-group { display: flex; gap: 0.5rem; }
    .btn-group .btn { flex: 1; border-radius: 8px; font-size: 12px; padding: 0.4rem 0.5rem; }
    
    /* Empty State */
    .empty-state-small { text-align: center; padding: 2rem; background: #f8fafc; border-radius: 12px; margin-top: 1rem; }
    
    /* Modal Styles */
    .modal-header { padding: 1rem 1.5rem; }
    .modal-body { padding: 1.5rem; max-height: 70vh; overflow-y: auto; }
    .detail-section { margin-bottom: 1.5rem; }
    .detail-section h6 { font-weight: 600; color: #1e293b; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 2px solid #e2e8f0; }
    .detail-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; }
    .detail-item { display: flex; flex-direction: column; }
    .detail-label { font-size: 0.7rem; text-transform: uppercase; color: #64748b; font-weight: 600; margin-bottom: 0.25rem; }
    .detail-value { font-size: 1rem; font-weight: 500; color: #1e293b; }
    
    /* Responsive */
    @media (max-width: 768px) {
        .stats-grid { grid-template-columns: repeat(2, 1fr); gap: 1rem; }
        .stat-value { font-size: 22px; }
        .header-title { font-size: 22px; }
        .info-row { grid-template-columns: 1fr; gap: 0.5rem; }
        .btn-group { flex-direction: column; }
        .detail-grid { grid-template-columns: 1fr; }
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // View Delivery Modal - Load content via AJAX
    document.querySelectorAll('.view-delivery-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const deliveryId = this.dataset.id;
            const modalBody = document.getElementById('viewDeliveryContent');
            
            modalBody.innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2">Loading delivery details...</p>
                </div>
            `;
            
            fetch(`/feed-deliveries/${deliveryId}/details`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayDeliveryDetails(data.delivery);
                    } else {
                        modalBody.innerHTML = `<div class="alert alert-danger">Failed to load delivery details.</div>`;
                    }
                })
                .catch(error => {
                    modalBody.innerHTML = `<div class="alert alert-danger">Error loading data: ${error.message}</div>`;
                });
        });
    });
    
    function displayDeliveryDetails(delivery) {
        document.getElementById('viewDeliveryContent').innerHTML = `
            <div class="detail-section">
                <h6>Delivery Information</h6>
                <div class="detail-grid">
                    <div class="detail-item">
                        <span class="detail-label">Delivery Date</span>
                        <span class="detail-value">${delivery.delivery_date}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Feed Type</span>
                        <span class="detail-value">${escapeHtml(delivery.feed_type_name)}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Supplier</span>
                        <span class="detail-value">${escapeHtml(delivery.supplier_name)}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Invoice Number</span>
                        <span class="detail-value">${escapeHtml(delivery.invoice_number || 'N/A')}</span>
                    </div>
                </div>
            </div>
            
            <div class="detail-section">
                <h6>Quantity & Cost</h6>
                <div class="detail-grid">
                    <div class="detail-item">
                        <span class="detail-label">Quantity</span>
                        <span class="detail-value">${delivery.quantity_kg.toLocaleString()} kg</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Cost per kg</span>
                        <span class="detail-value">$${delivery.cost_per_kg.toFixed(2)}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Total Cost</span>
                        <span class="detail-value">$${delivery.total_cost.toLocaleString()}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Remaining Stock</span>
                        <span class="detail-value">${delivery.remaining_quantity_kg.toLocaleString()} kg</span>
                    </div>
                </div>
            </div>
            
            <div class="detail-section">
                <h6>Tracking Information</h6>
                <div class="detail-grid">
                    <div class="detail-item">
                        <span class="detail-label">Batch Number</span>
                        <span class="detail-value">${escapeHtml(delivery.batch_number || 'N/A')}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Expiry Date</span>
                        <span class="detail-value">${delivery.expiry_date || 'N/A'}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Usage Rate</span>
                        <span class="detail-value">${delivery.usage_percentage.toFixed(1)}%</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Received By</span>
                        <span class="detail-value">${escapeHtml(delivery.received_by_name)}</span>
                    </div>
                </div>
            </div>
            
            ${delivery.notes ? `
            <div class="detail-section">
                <h6>Notes</h6>
                <p class="mb-0">${escapeHtml(delivery.notes)}</p>
            </div>
            ` : ''}
        `;
    }
    
    // Edit Delivery Modal - Load content via AJAX
    document.querySelectorAll('.edit-delivery-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const deliveryId = this.dataset.id;
            const modalBody = document.getElementById('editDeliveryContent');
            
            modalBody.innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border text-warning" role="status"></div>
                    <p class="mt-2">Loading delivery details...</p>
                </div>
            `;
            
            fetch(`/feed-deliveries/${deliveryId}/edit-data`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayEditForm(data.delivery, data.feedTypes);
                        window.currentEditDeliveryId = deliveryId;
                    } else {
                        modalBody.innerHTML = `<div class="alert alert-danger">Failed to load delivery details.</div>`;
                    }
                })
                .catch(error => {
                    modalBody.innerHTML = `<div class="alert alert-danger">Error loading data: ${error.message}</div>`;
                });
        });
    });
    
    function displayEditForm(delivery, feedTypes) {
        const feedTypeOptions = feedTypes.map(type => 
            `<option value="${type.id}" ${delivery.feed_type_id == type.id ? 'selected' : ''}>${escapeHtml(type.name)} (${type.category}) - ${type.species_name || 'N/A'}</option>`
        ).join('');
        
        document.getElementById('editDeliveryContent').innerHTML = `
            <form id="editDeliveryForm">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Feed Type <span class="text-danger">*</span></label>
                        <select name="feed_type_id" class="form-select" required>
                            ${feedTypeOptions}
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Delivery Date <span class="text-danger">*</span></label>
                        <input type="date" name="delivery_date" class="form-control" value="${delivery.delivery_date}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Supplier Name <span class="text-danger">*</span></label>
                        <input type="text" name="supplier_name" class="form-control" value="${escapeHtml(delivery.supplier_name)}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Invoice Number</label>
                        <input type="text" name="invoice_number" class="form-control" value="${escapeHtml(delivery.invoice_number || '')}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Quantity (kg) <span class="text-danger">*</span></label>
                        <input type="number" name="quantity_kg" class="form-control" step="0.01" min="0.01" value="${delivery.quantity_kg}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Cost per kg ($) <span class="text-danger">*</span></label>
                        <input type="number" name="cost_per_kg" class="form-control" step="0.01" min="0" value="${delivery.cost_per_kg}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Remaining Stock (kg)</label>
                        <input type="number" name="remaining_quantity_kg" class="form-control" step="0.01" min="0" value="${delivery.remaining_quantity_kg}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Expiry Date</label>
                        <input type="date" name="expiry_date" class="form-control" value="${delivery.expiry_date || ''}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Batch Number</label>
                        <input type="text" name="batch_number" class="form-control" value="${escapeHtml(delivery.batch_number || '')}">
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label fw-semibold">Notes</label>
                        <textarea name="notes" class="form-control" rows="3">${escapeHtml(delivery.notes || '')}</textarea>
                    </div>
                </div>
            </form>
        `;
    }
    
    // Save Edit Delivery
    document.getElementById('saveEditDelivery')?.addEventListener('click', function() {
        const form = document.getElementById('editDeliveryForm');
        const formData = new FormData(form);
        const data = {};
        formData.forEach((value, key) => { data[key] = value; });
        
        fetch(`/feed-deliveries/${window.currentEditDeliveryId}`, {
            method: 'PUT',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Updated!',
                    text: 'Feed delivery updated successfully',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    window.location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message || 'Failed to update delivery'
                });
            }
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred while updating the delivery'
            });
        });
    });
    
    // Delete Delivery - SweetAlert Confirmation (Admin only)
    document.querySelectorAll('.delete-delivery-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const deliveryId = this.dataset.id;
            const deliveryInfo = this.dataset.deliveryInfo;
            
            Swal.fire({
                title: 'Delete Delivery',
                html: `Are you sure you want to delete the delivery record for:<br><strong>${escapeHtml(deliveryInfo)}</strong>?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.getElementById('deleteDeliveryForm');
                    form.action = `/feed-deliveries/${deliveryId}`;
                    form.submit();
                }
            });
        });
    });
    
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