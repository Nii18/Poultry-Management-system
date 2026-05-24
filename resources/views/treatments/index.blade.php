{{-- resources/views/treatments/index.blade.php --}}
@extends('layouts.master')

@section('title', 'Treatment Records')

@section('content')
<div class="container-fluid px-4">
    <!-- Modern Header -->
    <div class="report-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <div class="d-flex align-items-center gap-3">
                    <div class="header-icon">
                        <i class="fas fa-stethoscope"></i>
                    </div>
                    <div>
                        <h1 class="header-title mb-1">Treatment Records</h1>
                        <p class="header-subtitle text-muted mb-0">Track medical treatments and withdrawal periods</p>
                    </div>
                </div>
            </div>
            <div class="col-auto">
                <div class="action-buttons">
                    <button type="button" class="btn btn-primary" onclick="openCreateTreatmentModal()">
                        <i class="fas fa-plus me-2"></i>New Treatment
                    </button>
                    <a href="{{ route('treatments.withdrawal-alerts') }}" class="btn btn-outline-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>Withdrawal Alerts
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid mb-4">
        <div class="stat-card">
            <div class="stat-icon bg-primary">
                <i class="fas fa-clinic-medical"></i>
            </div>
            <div class="stat-details">
                <span class="stat-label">Total Treatments</span>
                <h2 class="stat-value">{{ $treatments->total() }}</h2>
                <span class="stat-trend">Total records</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon bg-warning">
                <i class="fas fa-hourglass-half"></i>
            </div>
            <div class="stat-details">
                <span class="stat-label">Active Withdrawals</span>
                <h2 class="stat-value">{{ $treatments->where('is_withdrawal_active', true)->count() }}</h2>
                <span class="stat-trend text-warning">Under withdrawal</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon bg-success">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-details">
                <span class="stat-label">Completed Treatments</span>
                <h2 class="stat-value">{{ $treatments->where('end_date', '<', now())->count() }}</h2>
                <span class="stat-trend text-success">Finished</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon bg-info">
                <i class="fas fa-paw"></i>
            </div>
            <div class="stat-details">
                <span class="stat-label">Flocks Treated</span>
                <h2 class="stat-value">{{ $treatments->pluck('flock_id')->unique()->count() }}</h2>
                <span class="stat-trend">Affected flocks</span>
            </div>
        </div>
    </div>

    <!-- Main Card -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-0 py-3">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="card-title mb-0 fw-semibold">
                        <i class="fas fa-list me-2 text-primary"></i>Treatment Records
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
        <div class="collapse {{ request('flock_id') || request('status') ? 'show' : '' }}" id="filterCollapse">
            <div class="card-body pt-0">
                <form method="GET" action="{{ route('treatments.index') }}" class="row g-3">
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
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Status</label>
                        <select name="status" class="form-select">
                            <option value="active" {{ request('status', 'active') == 'active' ? 'selected' : '' }}>Active Treatment</option>
                            <option value="withdrawal" {{ request('status') == 'withdrawal' ? 'selected' : '' }}>In Withdrawal Period</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <div class="d-flex gap-2 w-100">
                            <button type="submit" class="btn btn-primary flex-grow-1">
                                <i class="fas fa-search me-1"></i>Apply
                            </button>
                            <a href="{{ route('treatments.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card-body">
            @if($treatments->count() > 0)
                <div class="row g-4">
                    @foreach($treatments as $treatment)
                    <div class="col-xl-4 col-lg-6">
                        <div class="treatment-card {{ $treatment->is_withdrawal_active ? 'withdrawal-active' : '' }}">
                            <div class="treatment-card-header">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="treatment-date">
                                            <i class="fas fa-calendar-alt me-2 text-primary"></i>
                                            {{ $treatment->start_date->format('d M Y') }}
                                            @if($treatment->end_date)
                                                <span class="text-muted">to {{ $treatment->end_date->format('d M Y') }}</span>
                                            @endif
                                        </div>
                                        <h5 class="treatment-flock mt-2 mb-0">
                                            <a href="{{ route('flocks.show', $treatment->flock_id) }}" class="flock-link">
                                                {{ $treatment->flock->flock_number ?? 'N/A' }}
                                            </a>
                                        </h5>
                                        <div class="treatment-breed">{{ $treatment->flock->breed_variety ?? 'N/A' }}</div>
                                    </div>
                                    <div class="status-badge {{ 
                                        $treatment->is_withdrawal_active ? 'status-withdrawal' : 
                                        ($treatment->withdrawal_end_date && $treatment->withdrawal_end_date < now() ? 'status-safe' : 'status-active') 
                                    }}">
                                        @if($treatment->is_withdrawal_active)
                                            <i class="fas fa-hourglass-half me-1"></i>Withdrawal
                                        @elseif($treatment->withdrawal_end_date && $treatment->withdrawal_end_date < now())
                                            <i class="fas fa-check-circle me-1"></i>Safe
                                        @else
                                            <i class="fas fa-activity me-1"></i>Active
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="treatment-card-body">
                                <div class="diagnosis-info">
                                    <div class="diagnosis-name">
                                        <i class="fas fa-diagnoses me-2 text-danger"></i>
                                        <strong>{{ $treatment->diagnosis }}</strong>
                                    </div>
                                    <div class="product-name">
                                        <i class="fas fa-capsules me-2 text-primary"></i>
                                        {{ $treatment->product_name }}
                                    </div>
                                </div>
                                <div class="info-grid mt-3">
                                    <div class="info-item">
                                        <span class="info-label">Administration</span>
                                        <strong class="info-value">{{ ucfirst($treatment->administration_route) }}</strong>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Dosage</span>
                                        <strong class="info-value">{{ $treatment->dosage }}</strong>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Animals Treated</span>
                                        <strong class="info-value">{{ $treatment->animals_treated ?? 'N/A' }}</strong>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Batch Number</span>
                                        <strong class="info-value">{{ $treatment->batch_number ?? 'N/A' }}</strong>
                                    </div>
                                </div>
                                @if($treatment->withdrawal_end_date)
                                <div class="withdrawal-info">
                                    <div class="withdrawal-date">
                                        <i class="fas fa-calendar-times me-2"></i>
                                        <span>Withdrawal ends: <strong>{{ $treatment->withdrawal_end_date->format('d M Y') }}</strong></span>
                                        @if($treatment->is_withdrawal_active)
                                            <span class="days-badge">
                                                {{ $treatment->days_until_withdrawal_end }} days remaining
                                            </span>
                                        @endif
                                    </div>
                                    <div class="withdrawal-progress">
                                        <div class="progress">
                                            @php
                                                $totalDays = $treatment->withdrawal_days ?? 1;
                                                $elapsedDays = max(0, $totalDays - $treatment->days_until_withdrawal_end);
                                                $percentage = min(100, ($elapsedDays / $totalDays) * 100);
                                            @endphp
                                            <div class="progress-bar bg-warning" style="width: {{ $percentage }}%"></div>
                                        </div>
                                    </div>
                                </div>
                                @endif
                                @if($treatment->notes)
                                <div class="treatment-notes">
                                    <i class="fas fa-sticky-note me-1 text-muted"></i>
                                    {{ Str::limit($treatment->notes, 80) }}
                                </div>
                                @endif
                            </div>
                            <div class="treatment-card-footer">
                                <div class="btn-group w-100">
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="viewTreatment({{ $treatment->id }})">
                                        <i class="fas fa-eye me-1"></i>View
                                    </button>
                                    <button type="button" class="btn btn-outline-warning btn-sm" onclick="editTreatment({{ $treatment->id }})">
                                        <i class="fas fa-edit me-1"></i>Edit
                                    </button>
                                    @if(auth()->user()->role === 'admin')
                                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="deleteTreatment({{ $treatment->id }}, '{{ $treatment->flock->flock_number ?? 'N/A' }} - {{ $treatment->start_date->format('Y-m-d') }}')">
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
                @if($treatments->hasPages())
                <div class="d-flex justify-content-between align-items-center mt-4 pt-2">
                    <div class="text-muted small">
                        Showing {{ $treatments->firstItem() ?? 0 }} to {{ $treatments->lastItem() ?? 0 }} of {{ $treatments->total() }} records
                    </div>
                    <div>
                        {{ $treatments->withQueryString()->links() }}
                    </div>
                </div>
                @endif
            @else
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-stethoscope"></i>
                    </div>
                    <h4>No Treatment Records Found</h4>
                    <p>Get started by recording your first treatment.</p>
                    <button type="button" class="btn btn-primary" onclick="openCreateTreatmentModal()">
                        <i class="fas fa-plus me-2"></i>Record Treatment
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Create Treatment Modal -->
<div class="modal fade" id="createTreatmentModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title text-white"><i class="fas fa-plus-circle me-2"></i>New Treatment Record</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="createTreatmentContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-success" role="status"></div>
                    <p class="mt-2">Loading form...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="saveCreateTreatment">Save Treatment</button>
            </div>
        </div>
    </div>
</div>

<!-- View Treatment Modal -->
<div class="modal fade" id="viewTreatmentModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white"><i class="fas fa-eye me-2"></i>Treatment Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewTreatmentContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2">Loading treatment details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Treatment Modal -->
<div class="modal fade" id="editTreatmentModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title text-white"><i class="fas fa-edit me-2"></i>Edit Treatment</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="editTreatmentContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-warning" role="status"></div>
                    <p class="mt-2">Loading treatment details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="saveEditTreatment">Update Treatment</button>
            </div>
        </div>
    </div>
</div>

<!-- Form for Delete -->
<form id="deleteTreatmentForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Open Create Modal
    function openCreateTreatmentModal() {
        const modal = new bootstrap.Modal(document.getElementById('createTreatmentModal'));
        const modalBody = document.getElementById('createTreatmentContent');
        
        modalBody.innerHTML = `<div class="text-center py-4"><div class="spinner-border text-success" role="status"></div><p class="mt-2">Loading form...</p></div>`;
        modal.show();
        
        fetch('{{ route("treatments.create-form") }}')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayCreateForm(data.flocks);
                } else {
                    modalBody.innerHTML = `<div class="alert alert-danger">Failed to load form: ${data.message}</div>`;
                }
            })
            .catch(error => {
                modalBody.innerHTML = `<div class="alert alert-danger">Error: ${error.message}</div>`;
            });
    }
    
    function displayCreateForm(flocks) {
        const flockOptions = flocks.map(flock => `<option value="${flock.id}">${escapeHtml(flock.flock_number)} (${escapeHtml(flock.breed_variety)})</option>`).join('');
        
        document.getElementById('createTreatmentContent').innerHTML = `
            <form id="createTreatmentForm">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Flock <span class="text-danger">*</span></label>
                        <select name="flock_id" class="form-select" required>${flockOptions}</select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Diagnosis <span class="text-danger">*</span></label>
                        <input type="text" name="diagnosis" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Product Name <span class="text-danger">*</span></label>
                        <input type="text" name="product_name" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Active Ingredient</label>
                        <input type="text" name="active_ingredient" class="form-control">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Dosage <span class="text-danger">*</span></label>
                        <input type="text" name="dosage" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Administration Route <span class="text-danger">*</span></label>
                        <select name="administration_route" class="form-select" required>
                            <option value="water">Water</option><option value="feed">Feed</option>
                            <option value="injection">Injection</option><option value="topical">Topical</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Start Date <span class="text-danger">*</span></label>
                        <input type="date" name="start_date" class="form-control" value="${new Date().toISOString().split('T')[0]}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">End Date <span class="text-danger">*</span></label>
                        <input type="date" name="end_date" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Withdrawal Days</label>
                        <input type="number" name="withdrawal_days" class="form-control" min="0">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Batch Number</label>
                        <input type="text" name="batch_number" class="form-control">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Animals Treated</label>
                        <input type="number" name="animals_treated" class="form-control" min="0">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Cost</label>
                        <input type="number" name="cost" class="form-control" step="0.01" min="0">
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label fw-semibold">Notes</label>
                        <textarea name="notes" class="form-control" rows="3"></textarea>
                    </div>
                </div>
            </form>
        `;
        
        document.getElementById('saveCreateTreatment').onclick = function() {
            const form = document.getElementById('createTreatmentForm');
            const formData = new FormData(form);
            const data = {};
            formData.forEach((value, key) => { data[key] = value; });
            
            fetch('{{ route("treatments.store-ajax") }}', {
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
                    Swal.fire({ icon: 'success', title: 'Saved!', text: 'Treatment recorded successfully', timer: 1500, showConfirmButton: false })
                        .then(() => window.location.reload());
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Failed to record treatment' });
                }
            })
            .catch(error => {
                Swal.fire({ icon: 'error', title: 'Error', text: 'An error occurred' });
            });
        };
    }
    
    // View Treatment
    function viewTreatment(id) {
        const modal = new bootstrap.Modal(document.getElementById('viewTreatmentModal'));
        const modalBody = document.getElementById('viewTreatmentContent');
        
        modalBody.innerHTML = `<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Loading...</p></div>`;
        modal.show();
        
        fetch(`/treatments/${id}/details`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayTreatmentDetails(data.treatment);
                } else {
                    modalBody.innerHTML = `<div class="alert alert-danger">Failed to load treatment details.</div>`;
                }
            })
            .catch(error => {
                modalBody.innerHTML = `<div class="alert alert-danger">Error loading data: ${error.message}</div>`;
            });
    }
    
    function displayTreatmentDetails(t) {
        document.getElementById('viewTreatmentContent').innerHTML = `
            <div class="detail-section">
                <h6>Treatment Information</h6>
                <div class="detail-grid">
                    <div class="detail-item"><span class="detail-label">Flock</span><span class="detail-value">${escapeHtml(t.flock_number)}</span></div>
                    <div class="detail-item"><span class="detail-label">Diagnosis</span><span class="detail-value">${escapeHtml(t.diagnosis)}</span></div>
                    <div class="detail-item"><span class="detail-label">Product</span><span class="detail-value">${escapeHtml(t.product_name)}</span></div>
                    <div class="detail-item"><span class="detail-label">Active Ingredient</span><span class="detail-value">${escapeHtml(t.active_ingredient || 'N/A')}</span></div>
                    <div class="detail-item"><span class="detail-label">Treatment Period</span><span class="detail-value">${t.start_date} to ${t.end_date}</span></div>
                    <div class="detail-item"><span class="detail-label">Dosage</span><span class="detail-value">${escapeHtml(t.dosage)}</span></div>
                    <div class="detail-item"><span class="detail-label">Route</span><span class="detail-value">${escapeHtml(t.administration_route)}</span></div>
                    <div class="detail-item"><span class="detail-label">Animals Treated</span><span class="detail-value">${t.animals_treated || 'N/A'}</span></div>
                    <div class="detail-item"><span class="detail-label">Batch Number</span><span class="detail-value">${escapeHtml(t.batch_number || 'N/A')}</span></div>
                    <div class="detail-item"><span class="detail-label">Cost</span><span class="detail-value">$${t.cost || '0.00'}</span></div>
                    ${t.withdrawal_end_date ? `<div class="detail-item"><span class="detail-label">Withdrawal Ends</span><span class="detail-value">${t.withdrawal_end_date}</span></div>` : ''}
                </div>
            </div>
            ${t.notes ? `<div class="detail-section"><h6>Notes</h6><p class="mb-0">${escapeHtml(t.notes)}</p></div>` : ''}
        `;
    }
    
    // Edit Treatment
    function editTreatment(id) {
        const modal = new bootstrap.Modal(document.getElementById('editTreatmentModal'));
        const modalBody = document.getElementById('editTreatmentContent');
        
        modalBody.innerHTML = `<div class="text-center py-4"><div class="spinner-border text-warning" role="status"></div><p class="mt-2">Loading...</p></div>`;
        modal.show();
        
        fetch(`/treatments/${id}/edit-data`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayTreatmentEditForm(data.treatment, data.flocks);
                    window.currentEditId = id;
                } else {
                    modalBody.innerHTML = `<div class="alert alert-danger">Failed to load treatment details.</div>`;
                }
            })
            .catch(error => {
                modalBody.innerHTML = `<div class="alert alert-danger">Error loading data: ${error.message}</div>`;
            });
    }
    
    function displayTreatmentEditForm(t, flocks) {
        const flockOptions = flocks.map(f => `<option value="${f.id}" ${t.flock_id == f.id ? 'selected' : ''}>${escapeHtml(f.flock_number)} (${escapeHtml(f.breed_variety)})</option>`).join('');
        
        document.getElementById('editTreatmentContent').innerHTML = `
            <form id="editTreatmentForm">
                <input type="hidden" name="id" value="${t.id}">
                <div class="row">
                    <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Flock *</label><select name="flock_id" class="form-select" required>${flockOptions}</select></div>
                    <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Diagnosis *</label><input type="text" name="diagnosis" class="form-control" value="${escapeHtml(t.diagnosis)}" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Product Name *</label><input type="text" name="product_name" class="form-control" value="${escapeHtml(t.product_name)}" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Active Ingredient</label><input type="text" name="active_ingredient" class="form-control" value="${escapeHtml(t.active_ingredient || '')}"></div>
                    <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Dosage *</label><input type="text" name="dosage" class="form-control" value="${escapeHtml(t.dosage)}" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Administration Route *</label><select name="administration_route" class="form-select" required>
                        <option value="water" ${t.administration_route === 'water' ? 'selected' : ''}>Water</option>
                        <option value="feed" ${t.administration_route === 'feed' ? 'selected' : ''}>Feed</option>
                        <option value="injection" ${t.administration_route === 'injection' ? 'selected' : ''}>Injection</option>
                        <option value="topical" ${t.administration_route === 'topical' ? 'selected' : ''}>Topical</option>
                    </select></div>
                    <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Start Date *</label><input type="date" name="start_date" class="form-control" value="${t.start_date}" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label fw-semibold">End Date *</label><input type="date" name="end_date" class="form-control" value="${t.end_date}" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Withdrawal Days</label><input type="number" name="withdrawal_days" class="form-control" value="${t.withdrawal_days || ''}" min="0"></div>
                    <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Batch Number</label><input type="text" name="batch_number" class="form-control" value="${escapeHtml(t.batch_number || '')}"></div>
                    <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Animals Treated</label><input type="number" name="animals_treated" class="form-control" value="${t.animals_treated || ''}" min="0"></div>
                    <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Cost</label><input type="number" name="cost" class="form-control" step="0.01" min="0" value="${t.cost || ''}"></div>
                    <div class="col-12 mb-3"><label class="form-label fw-semibold">Notes</label><textarea name="notes" class="form-control" rows="3">${escapeHtml(t.notes || '')}</textarea></div>
                </div>
            </form>
        `;
        
        document.getElementById('saveEditTreatment').onclick = function() {
            const form = document.getElementById('editTreatmentForm');
            const formData = new FormData(form);
            const data = {};
            formData.forEach((value, key) => { data[key] = value; });
            
            fetch(`/treatments/${window.currentEditId}/update-ajax`, {
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
                    Swal.fire({ icon: 'success', title: 'Updated!', text: 'Treatment updated successfully', timer: 1500, showConfirmButton: false })
                        .then(() => window.location.reload());
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Failed to update treatment' });
                }
            })
            .catch(error => {
                Swal.fire({ icon: 'error', title: 'Error', text: 'An error occurred' });
            });
        };
    }
    
    // Delete Treatment
    function deleteTreatment(id, info) {
        Swal.fire({
            title: 'Delete Treatment',
            text: `Are you sure you want to delete the treatment record for "${info}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.getElementById('deleteTreatmentForm');
                form.action = `/treatments/${id}`;
                form.submit();
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

@push('styles')
<style>
    .report-header { margin-bottom: 1.5rem; }
    .header-icon { width: 55px; height: 55px; background: linear-gradient(135deg, #0d6e4f 0%, #0a5a40 100%); border-radius: 14px; display: flex; align-items: center; justify-content: center; }
    .header-icon i { font-size: 26px; color: white; }
    .header-title { font-size: 26px; font-weight: 700; color: #1e293b; margin: 0; }
    .header-subtitle { font-size: 14px; margin: 0; }
    .action-buttons { display: flex; gap: 0.75rem; }
    
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem; margin-bottom: 1.5rem; }
    .stat-card { background: white; border-radius: 16px; padding: 1rem; display: flex; align-items: center; gap: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; transition: all 0.3s ease; }
    .stat-card:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0,0,0,0.08); }
    .stat-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; }
    .stat-icon i { font-size: 22px; color: white; }
    .stat-details { flex: 1; }
    .stat-label { font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; font-weight: 600; }
    .stat-value { font-size: 28px; font-weight: 700; color: #1e293b; margin: 0.15rem 0; line-height: 1.2; }
    .stat-trend { font-size: 11px; color: #64748b; }
    .stat-trend.text-warning { color: #f59e0b; }
    .stat-trend.text-success { color: #10b981; }
    
    .treatment-card { background: white; border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden; transition: all 0.3s ease; height: 100%; }
    .treatment-card:hover { transform: translateY(-3px); box-shadow: 0 12px 30px rgba(0,0,0,0.1); }
    .treatment-card.withdrawal-active { border-left: 4px solid #f59e0b; }
    .treatment-card-header { padding: 1rem 1.25rem; background: linear-gradient(135deg, #f8afc 0%, #ffffff 100%); border-bottom: 1px solid #e2e8f0; }
    .treatment-date { font-size: 13px; color: #64748b; }
    .treatment-flock { margin: 0.5rem 0 0.25rem 0; }
    .flock-link { color: #1e293b; text-decoration: none; font-weight: 700; font-size: 18px; transition: color 0.2s; }
    .flock-link:hover { color: #0d6e4f; }
    .treatment-breed { font-size: 12px; color: #64748b; }
    
    .status-badge { padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 11px; font-weight: 600; display: inline-flex; align-items: center; }
    .status-active { background: #dbeafe; color: #1e40af; }
    .status-withdrawal { background: #fef3c7; color: #d97706; }
    .status-safe { background: #d1fae5; color: #065f46; }
    
    .treatment-card-body { padding: 1rem 1.25rem; }
    .diagnosis-info { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem; padding-bottom: 0.75rem; border-bottom: 1px solid #e2e8f0; }
    .diagnosis-name { font-size: 14px; }
    .product-name { font-size: 13px; color: #0d6e4f; }
    
    .info-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.75rem; }
    .info-item { display: flex; flex-direction: column; gap: 0.25rem; }
    .info-label { font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; }
    .info-value { font-size: 13px; color: #1e293b; }
    
    .withdrawal-info { margin-top: 0.75rem; padding: 0.75rem; background: #fefce8; border-radius: 10px; }
    .withdrawal-date { font-size: 12px; color: #854d0e; margin-bottom: 0.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem; }
    .days-badge { background: #f59e0b; color: white; padding: 0.2rem 0.5rem; border-radius: 12px; font-size: 10px; font-weight: 600; }
    .withdrawal-progress .progress { height: 4px; border-radius: 10px; background: #e2e8f0; }
    .withdrawal-progress .progress-bar { border-radius: 10px; }
    
    .treatment-notes { margin-top: 0.75rem; padding-top: 0.75rem; border-top: 1px solid #e2e8f0; font-size: 12px; color: #64748b; }
    .treatment-card-footer { padding: 0.75rem 1.25rem; background: #f8fafc; border-top: 1px solid #e2e8f0; }
    .btn-group { display: flex; gap: 0.5rem; }
    .btn-group .btn { flex: 1; border-radius: 8px; font-size: 12px; padding: 0.4rem 0.5rem; }
    
    .empty-state { text-align: center; padding: 3rem; }
    .empty-icon { width: 70px; height: 70px; background: #f1f5f9; border-radius: 35px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; }
    .empty-icon i { font-size: 32px; color: #94a3b8; }
    .empty-state h4 { font-size: 18px; font-weight: 600; color: #1e293b; margin-bottom: 0.5rem; }
    .empty-state p { color: #64748b; margin-bottom: 1.5rem; }
    
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
        .info-grid { grid-template-columns: 1fr; gap: 0.5rem; }
        .diagnosis-info { flex-direction: column; align-items: flex-start; }
        .withdrawal-date { flex-direction: column; align-items: flex-start; }
        .btn-group { flex-direction: column; }
        .detail-grid { grid-template-columns: 1fr; }
    }
</style>
@endpush
@endsection