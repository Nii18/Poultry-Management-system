{{-- resources/views/feed-issuances/index.blade.php --}}
@extends('layouts.master')

@section('title', 'Feed Issuances')

@section('content')
<div class="container-fluid px-4">
    <!-- Modern Header -->
    <div class="report-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <div class="d-flex align-items-center gap-3">
                    <div class="header-icon">
                        <i class="fas fa-truck-ramp-box"></i>
                    </div>
                    <div>
                        <h1 class="header-title mb-1">Feed Issuances</h1>
                        <p class="header-subtitle text-muted mb-0">Track feed consumption and distribution by flock</p>
                    </div>
                </div>
            </div>
            <div class="col-auto">
                <div class="action-buttons">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createIssuanceModal">
                        <i class="fas fa-plus me-2"></i>Record Issuance
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid mb-4">
        <div class="stat-card">
            <div class="stat-icon bg-primary">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stat-details">
                <span class="stat-label">Total Issuances</span>
                <h2 class="stat-value">{{ $issuances->total() }}</h2>
                <span class="stat-trend">Total records</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon bg-success">
                <i class="fas fa-weight-hanging"></i>
            </div>
            <div class="stat-details">
                <span class="stat-label">Total Feed Issued</span>
                <h2 class="stat-value">{{ number_format($issuances->sum('quantity_kg')) }} kg</h2>
                <span class="stat-trend text-success">Consumption total</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon bg-info">
                <i class="fas fa-paw"></i>
            </div>
            <div class="stat-details">
                <span class="stat-label">Flocks Served</span>
                <h2 class="stat-value">{{ $issuances->pluck('flock_id')->unique()->count() }}</h2>
                <span class="stat-trend">Active recipients</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon bg-warning">
                <i class="fas fa-calendar-week"></i>
            </div>
            <div class="stat-details">
                <span class="stat-label">Avg Daily Issuance</span>
                <h2 class="stat-value">{{ number_format($issuances->avg('quantity_kg'), 0) }} kg</h2>
                <span class="stat-trend">Per record</span>
            </div>
        </div>
    </div>

    <!-- Main Card -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-0 py-3">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="card-title mb-0 fw-semibold">
                        <i class="fas fa-list me-2 text-primary"></i>Issuance Records
                    </h5>
                </div>
                <div class="col-auto">
                    <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse">
                        <i class="fas fa-filter me-1"></i>Filters
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Collapsible Filters -->
        <div class="collapse {{ request('flock_id') || request('start_date') ? 'show' : '' }}" id="filterCollapse">
            <div class="card-body pt-0">
                <form method="GET" action="{{ route('feed-issuances.index') }}" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Flock</label>
                        <select name="flock_id" class="form-select">
                            <option value="">All Flocks</option>
                            @foreach($flocks as $flock)
                                <option value="{{ $flock->id }}" {{ request('flock_id') == $flock->id ? 'selected' : '' }}>
                                    {{ $flock->flock_number }} ({{ $flock->breed_variety }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">From Date</label>
                        <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">To Date</label>
                        <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <div class="d-flex gap-2 w-100">
                            <button type="submit" class="btn btn-primary flex-grow-1">
                                <i class="fas fa-search me-1"></i>Apply
                            </button>
                            <a href="{{ route('feed-issuances.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card-body">
            @if($issuances->count() > 0)
                <div class="row g-4">
                    @foreach($issuances as $issuance)
                    <div class="col-xl-4 col-lg-6">
                        <div class="issuance-card">
                            <div class="issuance-card-header">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="issuance-date">
                                            <i class="fas fa-calendar-alt me-2 text-primary"></i>
                                            {{ $issuance->issuance_date->format('d M Y') }}
                                            @if($issuance->issuance_time)
                                                <span class="issuance-time">at {{ $issuance->issuance_time }}</span>
                                            @endif
                                        </div>
                                        <h5 class="issuance-flock mt-2 mb-0">
                                            <a href="{{ route('flocks.show', $issuance->flock_id) }}" class="flock-link">
                                                {{ $issuance->flock->flock_number ?? 'N/A' }}
                                            </a>
                                        </h5>
                                        <div class="issuance-breed">{{ $issuance->flock->breed_variety ?? 'N/A' }}</div>
                                    </div>
                                    <div class="quantity-badge">
                                        <span class="quantity-value">{{ number_format($issuance->quantity_kg, 0) }}</span>
                                        <span class="quantity-unit">kg</span>
                                    </div>
                                </div>
                            </div>
                            <div class="issuance-card-body">
                                <div class="info-grid">
                                    <div class="info-item">
                                        <span class="info-label">Feed Type</span>
                                        <strong class="info-value">{{ $issuance->feedDelivery->feedType->name ?? 'N/A' }}</strong>
                                        <span class="info-category">{{ ucfirst($issuance->feedDelivery->feedType->category ?? 'N/A') }}</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Batch Number</span>
                                        <strong class="info-value">{{ $issuance->feedDelivery->batch_number ?? 'N/A' }}</strong>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Supplier</span>
                                        <strong class="info-value">{{ $issuance->feedDelivery->supplier_name ?? 'N/A' }}</strong>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Issued By</span>
                                        <strong class="info-value">{{ $issuance->issuer->name ?? 'N/A' }}</strong>
                                    </div>
                                </div>
                                @if($issuance->notes)
                                <div class="issuance-notes">
                                    <i class="fas fa-sticky-note me-1 text-muted"></i>
                                    {{ Str::limit($issuance->notes, 80) }}
                                </div>
                                @endif
                            </div>
                            <div class="issuance-card-footer">
                                <div class="btn-group w-100">
                                    <button type="button" class="btn btn-outline-primary btn-sm view-issuance-btn" 
                                            data-id="{{ $issuance->id }}" data-bs-toggle="modal" data-bs-target="#viewIssuanceModal">
                                        <i class="fas fa-eye me-1"></i>View
                                    </button>
                                    <button type="button" class="btn btn-outline-warning btn-sm edit-issuance-btn" 
                                            data-id="{{ $issuance->id }}" data-bs-toggle="modal" data-bs-target="#editIssuanceModal">
                                        <i class="fas fa-edit me-1"></i>Edit
                                    </button>
                                    @if(auth()->user()->role === 'admin')
                                        <button type="button" class="btn btn-outline-danger btn-sm delete-issuance-btn" 
                                                data-id="{{ $issuance->id }}" data-issuance-info="{{ $issuance->flock->flock_number ?? 'N/A' }} - {{ $issuance->issuance_date->format('Y-m-d') }}">
                                            <i class="fas fa-trash-alt me-1"></i>Delete
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                @if($issuances->hasPages())
                <div class="d-flex justify-content-between align-items-center mt-4 pt-2">
                    <div class="text-muted small">
                        Showing {{ $issuances->firstItem() ?? 0 }} to {{ $issuances->lastItem() ?? 0 }} of {{ $issuances->total() }} records
                    </div>
                    <div>
                        {{ $issuances->withQueryString()->links() }}
                    </div>
                </div>
                @endif
            @else
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-truck-ramp-box"></i>
                    </div>
                    <h4>No Feed Issuances Found</h4>
                    <p>Get started by recording your first feed issuance.</p>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createIssuanceModal">
                        <i class="fas fa-plus me-2"></i>Record Issuance
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Create Issuance Modal -->
<div class="modal fade" id="createIssuanceModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title text-white">
                    <i class="fas fa-plus-circle me-2"></i>Record Feed Issuance
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="createIssuanceContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-success" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading form...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="saveCreateIssuance">Record Issuance</button>
            </div>
        </div>
    </div>
</div>

<!-- View Issuance Modal -->
<div class="modal fade" id="viewIssuanceModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white">
                    <i class="fas fa-eye me-2"></i>Issuance Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewIssuanceContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading issuance details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Issuance Modal -->
<div class="modal fade" id="editIssuanceModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title text-white">
                    <i class="fas fa-edit me-2"></i>Edit Issuance
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="editIssuanceContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-warning" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading issuance details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="saveEditIssuance">Update Issuance</button>
            </div>
        </div>
    </div>
</div>

<!-- Hidden forms for AJAX actions -->
<form id="deleteIssuanceForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

@push('styles')
<style>
    .report-header { margin-bottom: 1.5rem; }
    .header-icon { width: 55px; height: 55px; background: linear-gradient(135deg, #0d6e4f 0%, #0a5a40 100%); border-radius: 14px; display: flex; align-items: center; justify-content: center; }
    .header-icon i { font-size: 26px; color: white; }
    .header-title { font-size: 26px; font-weight: 700; color: #1e293b; letter-spacing: -0.02em; margin: 0; }
    .header-subtitle { font-size: 14px; margin: 0; }
    .action-buttons { display: flex; gap: 0.75rem; }
    
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem; margin-bottom: 1.5rem; }
    .stat-card { background: white; border-radius: 16px; padding: 1rem; display: flex; align-items: center; gap: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; transition: all 0.3s ease; }
    .stat-card:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0,0,0,0.08); }
    .stat-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; }
    .stat-icon i { font-size: 22px; color: white; }
    .stat-icon.bg-primary { background: linear-gradient(135deg, #0d6e4f, #0a5a40); }
    .stat-icon.bg-success { background: linear-gradient(135deg, #10b981, #059669); }
    .stat-icon.bg-info { background: linear-gradient(135deg, #3b82f6, #2563eb); }
    .stat-icon.bg-warning { background: linear-gradient(135deg, #f59e0b, #d97706); }
    .stat-details { flex: 1; }
    .stat-label { font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; font-weight: 600; }
    .stat-value { font-size: 28px; font-weight: 700; color: #1e293b; margin: 0.15rem 0; line-height: 1.2; }
    .stat-trend { font-size: 11px; color: #64748b; }
    .stat-trend.text-success { color: #10b981; }
    
    .issuance-card { background: white; border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden; transition: all 0.3s ease; height: 100%; }
    .issuance-card:hover { transform: translateY(-3px); box-shadow: 0 12px 30px rgba(0,0,0,0.1); }
    .issuance-card-header { padding: 1rem 1.25rem; background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%); border-bottom: 1px solid #e2e8f0; }
    .issuance-date { font-size: 13px; color: #64748b; }
    .issuance-time { font-size: 11px; color: #94a3b8; }
    .issuance-flock { margin: 0.5rem 0 0.25rem 0; }
    .flock-link { color: #1e293b; text-decoration: none; font-weight: 700; font-size: 18px; transition: color 0.2s; }
    .flock-link:hover { color: #0d6e4f; }
    .issuance-breed { font-size: 12px; color: #64748b; }
    
    .quantity-badge { background: linear-gradient(135deg, #0d6e4f, #0a5a40); border-radius: 12px; padding: 0.5rem 1rem; text-align: center; min-width: 80px; }
    .quantity-value { display: block; font-size: 24px; font-weight: 700; color: white; line-height: 1.1; }
    .quantity-unit { font-size: 10px; color: rgba(255,255,255,0.8); text-transform: uppercase; }
    
    .issuance-card-body { padding: 1rem 1.25rem; }
    .info-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.75rem; }
    .info-item { display: flex; flex-direction: column; gap: 0.25rem; }
    .info-label { font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; }
    .info-value { font-size: 13px; color: #1e293b; }
    .info-category { font-size: 10px; background: #e0f2fe; color: #0369a1; padding: 0.15rem 0.4rem; border-radius: 10px; display: inline-block; width: fit-content; margin-top: 0.25rem; }
    
    .issuance-notes { margin-top: 0.75rem; padding-top: 0.75rem; border-top: 1px solid #e2e8f0; font-size: 12px; color: #64748b; }
    
    .issuance-card-footer { padding: 0.75rem 1.25rem; background: #f8fafc; border-top: 1px solid #e2e8f0; }
    .btn-group { display: flex; gap: 0.5rem; }
    .btn-group .btn { flex: 1; border-radius: 8px; font-size: 12px; padding: 0.4rem 0.5rem; }
    
    .empty-state { text-align: center; padding: 3rem; }
    .empty-icon { width: 70px; height: 70px; background: #f1f5f9; border-radius: 35px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; }
    .empty-icon i { font-size: 32px; color: #94a3b8; }
    .empty-state h4 { font-size: 18px; font-weight: 600; color: #1e293b; margin-bottom: 0.5rem; }
    .empty-state p { color: #64748b; margin-bottom: 1.5rem; }
    
    .pagination { margin-bottom: 0; }
    .page-link { border-radius: 8px; margin: 0 2px; border: none; color: #475569; padding: 0.4rem 0.75rem; font-size: 13px; }
    .page-item.active .page-link { background-color: #0d6e4f; color: white; }
    .page-link:hover { background-color: #e2e8f0; color: #0d6e4f; }
    
    /* Modal Styles */
    .modal-header { padding: 1rem 1.5rem; }
    .modal-body { padding: 1.5rem; max-height: 70vh; overflow-y: auto; }
    .detail-section { margin-bottom: 1.5rem; }
    .detail-section h6 { font-weight: 600; color: #1e293b; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 2px solid #e2e8f0; }
    .detail-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; }
    .detail-item { display: flex; flex-direction: column; }
    .detail-label { font-size: 0.7rem; text-transform: uppercase; color: #64748b; font-weight: 600; margin-bottom: 0.25rem; }
    .detail-value { font-size: 1rem; font-weight: 500; color: #1e293b; }
    
    @media (max-width: 768px) {
        .stats-grid { grid-template-columns: repeat(2, 1fr); gap: 1rem; }
        .stat-value { font-size: 22px; }
        .header-title { font-size: 22px; }
        .quantity-badge { padding: 0.35rem 0.75rem; min-width: 70px; }
        .quantity-value { font-size: 20px; }
        .info-grid { grid-template-columns: 1fr; gap: 0.5rem; }
        .issuance-date { font-size: 11px; }
        .btn-group { flex-direction: column; }
        .detail-grid { grid-template-columns: 1fr; }
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Create Issuance Modal - Load form via AJAX
    document.getElementById('createIssuanceModal')?.addEventListener('show.bs.modal', function() {
        const modalBody = document.getElementById('createIssuanceContent');
        modalBody.innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border text-success" role="status"></div>
                <p class="mt-2">Loading form...</p>
            </div>
        `;
        
        // Use the existing create route to get the form data
        fetch('{{ route("feed-issuances.create") }}')
            .then(response => response.text())
            .then(html => {
                // Parse the HTML to extract form data
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const selectFields = doc.querySelectorAll('select[name="flock_id"], select[name="feed_delivery_id"]');
                
                // Get data from the parsed HTML
                const flockOptions = [];
                const feedDeliveryOptions = [];
                
                doc.querySelectorAll('select[name="flock_id"] option').forEach(opt => {
                    if (opt.value) {
                        flockOptions.push({ id: opt.value, text: opt.textContent });
                    }
                });
                
                doc.querySelectorAll('select[name="feed_delivery_id"] option').forEach(opt => {
                    if (opt.value) {
                        feedDeliveryOptions.push({ id: opt.value, text: opt.textContent });
                    }
                });
                
                displayCreateForm(flockOptions, feedDeliveryOptions);
            })
            .catch(error => {
                modalBody.innerHTML = `<div class="alert alert-danger">Error loading form: ${error.message}</div>`;
            });
    });
    
    function displayCreateForm(flocks, feedDeliveries) {
        const flockOptions = flocks.map(flock => 
            `<option value="${flock.id}">${escapeHtml(flock.text)}</option>`
        ).join('');
        
        const deliveryOptions = feedDeliveries.map(delivery => 
            `<option value="${delivery.id}">${escapeHtml(delivery.text)}</option>`
        ).join('');
        
        document.getElementById('createIssuanceContent').innerHTML = `
            <form id="createIssuanceForm">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Flock <span class="text-danger">*</span></label>
                        <select name="flock_id" class="form-select" required>
                            <option value="">Select Flock</option>
                            ${flockOptions}
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Feed Stock <span class="text-danger">*</span></label>
                        <select name="feed_delivery_id" class="form-select" required>
                            <option value="">Select Feed Batch</option>
                            ${deliveryOptions}
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Quantity (kg) <span class="text-danger">*</span></label>
                        <input type="number" name="quantity_kg" class="form-control" step="0.01" min="0.01" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Issuance Date <span class="text-danger">*</span></label>
                        <input type="date" name="issuance_date" class="form-control" value="${new Date().toISOString().split('T')[0]}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Issuance Time</label>
                        <input type="time" name="issuance_time" class="form-control" value="${new Date().toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' })}">
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label fw-semibold">Notes</label>
                        <textarea name="notes" class="form-control" rows="3"></textarea>
                    </div>
                </div>
            </form>
        `;
    }
    
    // Save Create Issuance
    document.getElementById('saveCreateIssuance')?.addEventListener('click', function() {
        const form = document.getElementById('createIssuanceForm');
        const formData = new FormData(form);
        const data = {};
        formData.forEach((value, key) => { data[key] = value; });
        
        fetch('{{ route("feed-issuances.store") }}', {
            method: 'POST',
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
                    title: 'Recorded!',
                    text: 'Feed issuance recorded successfully',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    window.location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message || 'Failed to record issuance'
                });
            }
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred while recording the issuance'
            });
        });
    });
    
    // View Issuance Modal - Load content via AJAX
    document.querySelectorAll('.view-issuance-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const issuanceId = this.dataset.id;
            const modalBody = document.getElementById('viewIssuanceContent');
            
            modalBody.innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2">Loading issuance details...</p>
                </div>
            `;
            
            fetch(`/feed-issuances/${issuanceId}/details`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayIssuanceDetails(data.issuance);
                    } else {
                        modalBody.innerHTML = `<div class="alert alert-danger">Failed to load issuance details.</div>`;
                    }
                })
                .catch(error => {
                    modalBody.innerHTML = `<div class="alert alert-danger">Error loading data: ${error.message}</div>`;
                });
        });
    });
    
    function displayIssuanceDetails(issuance) {
        document.getElementById('viewIssuanceContent').innerHTML = `
            <div class="detail-section">
                <h6>Issuance Information</h6>
                <div class="detail-grid">
                    <div class="detail-item">
                        <span class="detail-label">Issuance Date</span>
                        <span class="detail-value">${issuance.issuance_date} ${issuance.issuance_time ? 'at ' + issuance.issuance_time : ''}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Flock</span>
                        <span class="detail-value">${escapeHtml(issuance.flock_number)} (${escapeHtml(issuance.breed_variety)})</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Species</span>
                        <span class="detail-value">${escapeHtml(issuance.species_name)}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Issued By</span>
                        <span class="detail-value">${escapeHtml(issuance.issued_by_name)}</span>
                    </div>
                </div>
            </div>
            
            <div class="detail-section">
                <h6>Feed Information</h6>
                <div class="detail-grid">
                    <div class="detail-item">
                        <span class="detail-label">Feed Type</span>
                        <span class="detail-value">${escapeHtml(issuance.feed_type_name)} (${escapeHtml(issuance.category)})</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Batch Number</span>
                        <span class="detail-value">${escapeHtml(issuance.batch_number || 'N/A')}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Supplier</span>
                        <span class="detail-value">${escapeHtml(issuance.supplier_name)}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Quantity Issued</span>
                        <span class="detail-value">${issuance.quantity_kg.toLocaleString()} kg</span>
                    </div>
                </div>
            </div>
            
            ${issuance.notes ? `
            <div class="detail-section">
                <h6>Notes</h6>
                <p class="mb-0">${escapeHtml(issuance.notes)}</p>
            </div>
            ` : ''}
        `;
    }
    
    // Edit Issuance Modal - Load content via AJAX
    document.querySelectorAll('.edit-issuance-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const issuanceId = this.dataset.id;
            const modalBody = document.getElementById('editIssuanceContent');
            
            modalBody.innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border text-warning" role="status"></div>
                    <p class="mt-2">Loading issuance details...</p>
                </div>
            `;
            
            fetch(`/feed-issuances/${issuanceId}/edit-data`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayEditForm(data.issuance, data.flocks);
                        window.currentEditIssuanceId = issuanceId;
                    } else {
                        modalBody.innerHTML = `<div class="alert alert-danger">Failed to load issuance details.</div>`;
                    }
                })
                .catch(error => {
                    modalBody.innerHTML = `<div class="alert alert-danger">Error loading data: ${error.message}</div>`;
                });
        });
    });
    
    function displayEditForm(issuance, flocks) {
        const flockOptions = flocks.map(flock => 
            `<option value="${flock.id}" ${issuance.flock_id == flock.id ? 'selected' : ''}>${escapeHtml(flock.flock_number)} (${escapeHtml(flock.breed_variety)})</option>`
        ).join('');
        
        document.getElementById('editIssuanceContent').innerHTML = `
            <form id="editIssuanceForm">
                <input type="hidden" name="id" value="${issuance.id}">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Flock <span class="text-danger">*</span></label>
                        <select name="flock_id" class="form-select" required>
                            <option value="">Select Flock</option>
                            ${flockOptions}
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Quantity (kg) <span class="text-danger">*</span></label>
                        <input type="number" name="quantity_kg" class="form-control" step="0.01" min="0.01" value="${issuance.quantity_kg}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Issuance Date <span class="text-danger">*</span></label>
                        <input type="date" name="issuance_date" class="form-control" value="${issuance.issuance_date}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Issuance Time</label>
                        <input type="time" name="issuance_time" class="form-control" value="${issuance.issuance_time || ''}">
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label fw-semibold">Notes</label>
                        <textarea name="notes" class="form-control" rows="3">${escapeHtml(issuance.notes || '')}</textarea>
                    </div>
                </div>
            </form>
        `;
    }
    
    // Save Edit Issuance
    document.getElementById('saveEditIssuance')?.addEventListener('click', function() {
        const form = document.getElementById('editIssuanceForm');
        const formData = new FormData(form);
        const data = {};
        formData.forEach((value, key) => { data[key] = value; });
        
        fetch(`/feed-issuances/${window.currentEditIssuanceId}`, {
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
                    text: 'Feed issuance updated successfully',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    window.location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message || 'Failed to update issuance'
                });
            }
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred while updating the issuance'
            });
        });
    });
    
    // Delete Issuance - SweetAlert Confirmation
    document.querySelectorAll('.delete-issuance-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const issuanceId = this.dataset.id;
            const issuanceInfo = this.dataset.issuanceInfo;
            
            Swal.fire({
                title: 'Delete Issuance',
                text: `Are you sure you want to delete the issuance record for "${issuanceInfo}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.getElementById('deleteIssuanceForm');
                    form.action = `/feed-issuances/${issuanceId}`;
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