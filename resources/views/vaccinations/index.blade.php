{{-- resources/views/vaccinations/index.blade.php --}}
@extends('layouts.master')

@section('title', 'Vaccination Records')

@section('content')
<div class="container-fluid px-4">
    <!-- Modern Header -->
    <div class="report-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <div class="d-flex align-items-center gap-3">
                    <div class="header-icon">
                        <i class="fas fa-syringe"></i>
                    </div>
                    <div>
                        <h1 class="header-title mb-1">Vaccination Records</h1>
                        <p class="header-subtitle text-muted mb-0">Track immunization schedules and coverage</p>
                    </div>
                </div>
            </div>
            <div class="col-auto">
                <div class="action-buttons">
                    <button type="button" class="btn btn-primary" onclick="openCreateModal()">
                        <i class="fas fa-plus me-2"></i>New Vaccination
                    </button>
                    <button type="button" class="btn btn-outline-info" onclick="openScheduleModal()">
                        <i class="fas fa-calendar-alt me-2"></i>View Schedule
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid mb-4">
        <div class="stat-card">
            <div class="stat-icon bg-primary">
                <i class="fas fa-syringe"></i>
            </div>
            <div class="stat-details">
                <span class="stat-label">Total Vaccinations</span>
                <h2 class="stat-value">{{ $vaccinations->total() }}</h2>
                <span class="stat-trend">Total records</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon bg-success">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-details">
                <span class="stat-label">Avg Coverage Rate</span>
                <h2 class="stat-value">{{ number_format($vaccinations->avg('coverage_percentage'), 1) }}%</h2>
                <span class="stat-trend text-success">Immunization rate</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon bg-info">
                <i class="fas fa-paw"></i>
            </div>
            <div class="stat-details">
                <span class="stat-label">Flocks Vaccinated</span>
                <h2 class="stat-value">{{ $vaccinations->pluck('flock_id')->unique()->count() }}</h2>
                <span class="stat-trend">Unique flocks</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon bg-warning">
                <i class="fas fa-calendar-week"></i>
            </div>
            <div class="stat-details">
                <span class="stat-label">This Month</span>
                <h2 class="stat-value">{{ $vaccinations->whereBetween('administration_date', [now()->startOfMonth(), now()->endOfMonth()])->count() }}</h2>
                <span class="stat-trend">Current period</span>
            </div>
        </div>
    </div>

    <!-- Main Card -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-0 py-3">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="card-title mb-0 fw-semibold">
                        <i class="fas fa-list me-2 text-primary"></i>Vaccination Records
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
                <form method="GET" action="{{ route('vaccinations.index') }}" class="row g-3">
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
                        <input type="date" name="start_date" class="form-control" value="{{ request('start_date', $startDate->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">To Date</label>
                        <input type="date" name="end_date" class="form-control" value="{{ request('end_date', $endDate->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <div class="d-flex gap-2 w-100">
                            <button type="submit" class="btn btn-primary flex-grow-1">
                                <i class="fas fa-search me-1"></i>Apply
                            </button>
                            <a href="{{ route('vaccinations.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card-body">
            @if($vaccinations->count() > 0)
                <div class="row g-4">
                    @foreach($vaccinations as $vaccination)
                    <div class="col-xl-4 col-lg-6">
                        <div class="vaccination-card">
                            <div class="vaccination-card-header">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="vaccination-date">
                                            <i class="fas fa-calendar-alt me-2 text-primary"></i>
                                            {{ $vaccination->administration_date->format('d M Y') }}
                                            <span class="vaccination-day">(Day {{ $vaccination->day_administered }})</span>
                                        </div>
                                        <h5 class="vaccination-flock mt-2 mb-0">
                                            <a href="{{ route('flocks.show', $vaccination->flock_id) }}" class="flock-link">
                                                {{ $vaccination->flock->flock_number ?? 'N/A' }}
                                            </a>
                                        </h5>
                                        <div class="vaccination-breed">{{ $vaccination->flock->breed_variety ?? 'N/A' }}</div>
                                    </div>
                                    <div class="coverage-badge">
                                        <span class="coverage-value">{{ number_format($vaccination->coverage_percentage ?? 0, 0) }}%</span>
                                        <span class="coverage-label">Coverage</span>
                                    </div>
                                </div>
                            </div>
                            <div class="vaccination-card-body">
                                <div class="vaccine-info">
                                    <div class="vaccine-name">
                                        <i class="fas fa-vial me-2 text-primary"></i>
                                        <strong>{{ $vaccination->vaccine_name }}</strong>
                                    </div>
                                    <div class="vaccine-disease">
                                        <i class="fas fa-biohazard me-2 text-danger"></i>
                                        {{ $vaccination->disease_target }}
                                    </div>
                                </div>
                                <div class="info-grid mt-3">
                                    <div class="info-item">
                                        <span class="info-label">Administration Route</span>
                                        <strong class="info-value">{{ ucfirst(str_replace('_', ' ', $vaccination->route)) }}</strong>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Batch Number</span>
                                        <strong class="info-value">{{ $vaccination->batch_number }}</strong>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Dosage</span>
                                        <strong class="info-value">{{ $vaccination->dosage_ml ? $vaccination->dosage_ml . ' ml' : 'N/A' }}</strong>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Expiry Date</span>
                                        <strong class="info-value {{ $vaccination->expiry_date < now() ? 'text-danger' : '' }}">
                                            {{ $vaccination->expiry_date->format('d M Y') }}
                                            @if($vaccination->expiry_date < now())
                                                <span class="badge bg-danger ms-1">Expired</span>
                                            @endif
                                        </strong>
                                    </div>
                                </div>
                                @if($vaccination->notes)
                                <div class="vaccination-notes">
                                    <i class="fas fa-sticky-note me-1 text-muted"></i>
                                    {{ Str::limit($vaccination->notes, 80) }}
                                </div>
                                @endif
                            </div>
                            <div class="vaccination-card-footer">
                                <div class="btn-group w-100">
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="viewVaccination({{ $vaccination->id }})">
                                        <i class="fas fa-eye me-1"></i>View
                                    </button>
                                    <button type="button" class="btn btn-outline-warning btn-sm" onclick="editVaccination({{ $vaccination->id }})">
                                        <i class="fas fa-edit me-1"></i>Edit
                                    </button>
                                    @if(auth()->user()->role === 'admin')
                                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="deleteVaccination({{ $vaccination->id }}, '{{ $vaccination->flock->flock_number ?? 'N/A' }} - {{ $vaccination->vaccine_name }}')">
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
                @if($vaccinations->hasPages())
                <div class="d-flex justify-content-between align-items-center mt-4 pt-2">
                    <div class="text-muted small">
                        Showing {{ $vaccinations->firstItem() ?? 0 }} to {{ $vaccinations->lastItem() ?? 0 }} of {{ $vaccinations->total() }} records
                    </div>
                    <div>
                        {{ $vaccinations->withQueryString()->links() }}
                    </div>
                </div>
                @endif
            @else
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-syringe"></i>
                    </div>
                    <h4>No Vaccination Records Found</h4>
                    <p>Get started by recording your first vaccination.</p>
                    <button type="button" class="btn btn-primary" onclick="openCreateModal()">
                        <i class="fas fa-plus me-2"></i>Record Vaccination
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Create Vaccination Modal -->
<div class="modal fade" id="createVaccinationModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title text-white"><i class="fas fa-plus-circle me-2"></i>New Vaccination Record</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="createVaccinationContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-success" role="status"></div>
                    <p class="mt-2">Loading form...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="saveCreateVaccination">Save Vaccination</button>
            </div>
        </div>
    </div>
</div>

<!-- View Vaccination Modal -->
<div class="modal fade" id="viewVaccinationModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white"><i class="fas fa-eye me-2"></i>Vaccination Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewVaccinationContent">
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

<!-- Edit Vaccination Modal -->
<div class="modal fade" id="editVaccinationModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title text-white"><i class="fas fa-edit me-2"></i>Edit Vaccination</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="editVaccinationContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-warning" role="status"></div>
                    <p class="mt-2">Loading details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="saveEditVaccination">Update Vaccination</button>
            </div>
        </div>
    </div>
</div>

<!-- Schedule Modal -->
<div class="modal fade" id="scheduleModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title text-white"><i class="fas fa-calendar-alt me-2"></i>Vaccination Schedule</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="scheduleContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-info" role="status"></div>
                    <p class="mt-2">Loading schedule...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<form id="deleteVaccinationForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Open Create Modal
    function openCreateModal() {
        const modal = new bootstrap.Modal(document.getElementById('createVaccinationModal'));
        const modalBody = document.getElementById('createVaccinationContent');
        
        modalBody.innerHTML = `<div class="text-center py-4"><div class="spinner-border text-success" role="status"></div><p class="mt-2">Loading form...</p></div>`;
        modal.show();
        
        fetch('{{ route("vaccinations.create-form") }}')
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
                console.error('Error:', error);
            });
    }
    
    function displayCreateForm(flocks) {
        const flockOptions = flocks.map(flock => `<option value="${flock.id}">${escapeHtml(flock.flock_number)} (${escapeHtml(flock.breed_variety)})</option>`).join('');
        
        document.getElementById('createVaccinationContent').innerHTML = `
            <form id="createVaccinationForm">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Flock *</label>
                        <select name="flock_id" class="form-select" required>${flockOptions}</select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Vaccine Name *</label>
                        <input type="text" name="vaccine_name" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Disease Target *</label>
                        <input type="text" name="disease_target" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Day Administered *</label>
                        <input type="number" name="day_administered" class="form-control" min="0" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Administration Date *</label>
                        <input type="date" name="administration_date" class="form-control" value="${new Date().toISOString().split('T')[0]}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Route *</label>
                        <select name="route" class="form-select" required>
                            <option value="subcutaneous">Subcutaneous</option>
                            <option value="intramuscular">Intramuscular</option>
                            <option value="drinking_water">Drinking Water</option>
                            <option value="spray">Spray</option>
                            <option value="eye_drop">Eye Drop</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Batch Number *</label>
                        <input type="text" name="batch_number" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Expiry Date *</label>
                        <input type="date" name="expiry_date" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Dosage (ml)</label>
                        <input type="number" name="dosage_ml" class="form-control" step="0.01" min="0">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Birds Vaccinated</label>
                        <input type="number" name="birds_vaccinated" class="form-control" min="0">
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label fw-semibold">Notes</label>
                        <textarea name="notes" class="form-control" rows="3"></textarea>
                    </div>
                </div>
            </form>
        `;
        
        document.getElementById('saveCreateVaccination').onclick = function() {
            const form = document.getElementById('createVaccinationForm');
            const formData = new FormData(form);
            const data = {};
            formData.forEach((value, key) => { data[key] = value; });
            
            fetch('{{ route("vaccinations.store-ajax") }}', {
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
                    Swal.fire({ icon: 'success', title: 'Saved!', text: 'Vaccination recorded successfully', timer: 1500, showConfirmButton: false })
                        .then(() => window.location.reload());
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Failed to record vaccination' });
                }
            })
            .catch(error => {
                Swal.fire({ icon: 'error', title: 'Error', text: 'An error occurred' });
            });
        };
    }
    
    // View Vaccination
    function viewVaccination(id) {
        const modal = new bootstrap.Modal(document.getElementById('viewVaccinationModal'));
        const modalBody = document.getElementById('viewVaccinationContent');
        
        modalBody.innerHTML = `<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Loading...</p></div>`;
        modal.show();
        
        fetch(`/vaccinations/${id}/details`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayViewDetails(data.vaccination);
                } else {
                    modalBody.innerHTML = `<div class="alert alert-danger">Failed to load details.</div>`;
                }
            })
            .catch(error => {
                modalBody.innerHTML = `<div class="alert alert-danger">Error loading data: ${error.message}</div>`;
            });
    }
    
    function displayViewDetails(v) {
        document.getElementById('viewVaccinationContent').innerHTML = `
            <div class="detail-section">
                <h6>Vaccination Information</h6>
                <div class="detail-grid">
                    <div class="detail-item"><span class="detail-label">Flock</span><span class="detail-value">${escapeHtml(v.flock_number)}</span></div>
                    <div class="detail-item"><span class="detail-label">Vaccine</span><span class="detail-value">${escapeHtml(v.vaccine_name)}</span></div>
                    <div class="detail-item"><span class="detail-label">Disease Target</span><span class="detail-value">${escapeHtml(v.disease_target)}</span></div>
                    <div class="detail-item"><span class="detail-label">Day</span><span class="detail-value">Day ${v.day_administered}</span></div>
                    <div class="detail-item"><span class="detail-label">Administration Date</span><span class="detail-value">${v.administration_date}</span></div>
                    <div class="detail-item"><span class="detail-label">Route</span><span class="detail-value">${escapeHtml(v.route)}</span></div>
                    <div class="detail-item"><span class="detail-label">Batch Number</span><span class="detail-value">${escapeHtml(v.batch_number)}</span></div>
                    <div class="detail-item"><span class="detail-label">Expiry Date</span><span class="detail-value">${v.expiry_date}</span></div>
                    <div class="detail-item"><span class="detail-label">Dosage</span><span class="detail-value">${v.dosage_ml ? v.dosage_ml + ' ml' : 'N/A'}</span></div>
                    <div class="detail-item"><span class="detail-label">Coverage</span><span class="detail-value">${v.coverage_percentage}%</span></div>
                </div>
            </div>
            ${v.notes ? `<div class="detail-section"><h6>Notes</h6><p class="mb-0">${escapeHtml(v.notes)}</p></div>` : ''}
        `;
    }
    
    // Edit Vaccination
    function editVaccination(id) {
        const modal = new bootstrap.Modal(document.getElementById('editVaccinationModal'));
        const modalBody = document.getElementById('editVaccinationContent');
        
        modalBody.innerHTML = `<div class="text-center py-4"><div class="spinner-border text-warning" role="status"></div><p class="mt-2">Loading...</p></div>`;
        modal.show();
        
        fetch(`/vaccinations/${id}/edit-data`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayEditForm(data.vaccination, data.flocks);
                    window.currentEditId = id;
                } else {
                    modalBody.innerHTML = `<div class="alert alert-danger">Failed to load details.</div>`;
                }
            })
            .catch(error => {
                modalBody.innerHTML = `<div class="alert alert-danger">Error loading data: ${error.message}</div>`;
            });
    }
    
    function displayEditForm(vaccination, flocks) {
        const flockOptions = flocks.map(flock => 
            `<option value="${flock.id}" ${vaccination.flock_id == flock.id ? 'selected' : ''}>${escapeHtml(flock.flock_number)} (${escapeHtml(flock.breed_variety)})</option>`
        ).join('');
        
        document.getElementById('editVaccinationContent').innerHTML = `
            <form id="editVaccinationForm">
                <div class="row">
                    <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Flock *</label><select name="flock_id" class="form-select" required>${flockOptions}</select></div>
                    <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Vaccine Name *</label><input type="text" name="vaccine_name" class="form-control" value="${escapeHtml(vaccination.vaccine_name)}" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Disease Target *</label><input type="text" name="disease_target" class="form-control" value="${escapeHtml(vaccination.disease_target)}" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Day Administered *</label><input type="number" name="day_administered" class="form-control" value="${vaccination.day_administered}" min="0" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Administration Date *</label><input type="date" name="administration_date" class="form-control" value="${vaccination.administration_date}" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Route *</label><select name="route" class="form-select" required>
                        <option value="subcutaneous" ${vaccination.route === 'subcutaneous' ? 'selected' : ''}>Subcutaneous</option>
                        <option value="intramuscular" ${vaccination.route === 'intramuscular' ? 'selected' : ''}>Intramuscular</option>
                        <option value="drinking_water" ${vaccination.route === 'drinking_water' ? 'selected' : ''}>Drinking Water</option>
                        <option value="spray" ${vaccination.route === 'spray' ? 'selected' : ''}>Spray</option>
                        <option value="eye_drop" ${vaccination.route === 'eye_drop' ? 'selected' : ''}>Eye Drop</option>
                    </select></div>
                    <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Batch Number *</label><input type="text" name="batch_number" class="form-control" value="${escapeHtml(vaccination.batch_number)}" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Expiry Date *</label><input type="date" name="expiry_date" class="form-control" value="${vaccination.expiry_date}" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Dosage (ml)</label><input type="number" name="dosage_ml" class="form-control" step="0.01" min="0" value="${vaccination.dosage_ml || ''}"></div>
                    <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Birds Vaccinated</label><input type="number" name="birds_vaccinated" class="form-control" min="0" value="${vaccination.birds_vaccinated || ''}"></div>
                    <div class="col-12 mb-3"><label class="form-label fw-semibold">Notes</label><textarea name="notes" class="form-control" rows="3">${escapeHtml(vaccination.notes || '')}</textarea></div>
                </div>
            </form>
        `;
        
        document.getElementById('saveEditVaccination').onclick = function() {
            const form = document.getElementById('editVaccinationForm');
            const formData = new FormData(form);
            const data = {};
            formData.forEach((value, key) => { data[key] = value; });
            
            fetch(`/vaccinations/${window.currentEditId}/update-ajax`, {
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
                    Swal.fire({ icon: 'success', title: 'Updated!', text: 'Vaccination updated successfully', timer: 1500, showConfirmButton: false })
                        .then(() => window.location.reload());
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Failed to update vaccination' });
                }
            })
            .catch(error => {
                Swal.fire({ icon: 'error', title: 'Error', text: 'An error occurred' });
            });
        };
    }
    
    // Open Schedule Modal
    function openScheduleModal() {
        const modal = new bootstrap.Modal(document.getElementById('scheduleModal'));
        const modalBody = document.getElementById('scheduleContent');
        
        modalBody.innerHTML = `<div class="text-center py-4"><div class="spinner-border text-info" role="status"></div><p class="mt-2">Loading schedule...</p></div>`;
        modal.show();
        
        fetch('{{ route("vaccinations.schedule-data") }}')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displaySchedule(data.upcoming, data.past);
                } else {
                    modalBody.innerHTML = `<div class="alert alert-danger">Failed to load schedule: ${data.message}</div>`;
                }
            })
            .catch(error => {
                modalBody.innerHTML = `<div class="alert alert-danger">Error: ${error.message}</div>`;
            });
    }
    
    function displaySchedule(upcoming, past) {
        let upcomingHtml = '<h6>Upcoming Vaccinations</h6>';
        if (upcoming.length > 0) {
            upcomingHtml += '<div class="table-responsive"><table class="table table-sm table-bordered"><thead class="table-light"><tr><th>Date</th><th>Flock</th><th>Vaccine</th><th>Disease</th></tr></thead><tbody>';
            upcoming.forEach(v => {
                upcomingHtml += `<tr><td>${v.administration_date}</td><td>${escapeHtml(v.flock_number)}</td><td>${escapeHtml(v.vaccine_name)}</td><td>${escapeHtml(v.disease_target)}</td></tr>`;
            });
            upcomingHtml += '</tbody></table></div>';
        } else {
            upcomingHtml += '<p class="text-muted">No upcoming vaccinations scheduled.</p>';
        }
        
        let pastHtml = '<h6 class="mt-3">Past Vaccinations (Last 20)</h6>';
        if (past.length > 0) {
            pastHtml += '<div class="table-responsive"><table class="table table-sm table-bordered"><thead class="table-light"><tr><th>Date</th><th>Flock</th><th>Vaccine</th><th>Disease</th></tr></thead><tbody>';
            past.forEach(v => {
                pastHtml += `<tr><td>${v.administration_date}</td><td>${escapeHtml(v.flock_number)}</td><td>${escapeHtml(v.vaccine_name)}</td><td>${escapeHtml(v.disease_target)}</td></tr>`;
            });
            pastHtml += '</tbody></table></div>';
        } else {
            pastHtml += '<p class="text-muted">No past vaccination records.</p>';
        }
        
        document.getElementById('scheduleContent').innerHTML = upcomingHtml + pastHtml;
    }
    
    // Delete Vaccination
    function deleteVaccination(id, info) {
        Swal.fire({
            title: 'Delete Vaccination',
            text: `Are you sure you want to delete the vaccination record for "${info}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.getElementById('deleteVaccinationForm');
                form.action = `/vaccinations/${id}`;
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
    /* Your existing styles remain exactly the same */
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
    .bg-primary { background: linear-gradient(135deg, #0d6e4f, #0a5a40); }
    .bg-success { background: linear-gradient(135deg, #10b981, #059669); }
    .bg-info { background: linear-gradient(135deg, #3b82f6, #2563eb); }
    .bg-warning { background: linear-gradient(135deg, #f59e0b, #d97706); }
    .stat-details { flex: 1; }
    .stat-label { font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; font-weight: 600; }
    .stat-value { font-size: 28px; font-weight: 700; color: #1e293b; margin: 0.15rem 0; line-height: 1.2; }
    .stat-trend { font-size: 11px; color: #64748b; }
    .stat-trend.text-success { color: #10b981; }
    
    .vaccination-card { background: white; border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden; transition: all 0.3s ease; height: 100%; }
    .vaccination-card:hover { transform: translateY(-3px); box-shadow: 0 12px 30px rgba(0,0,0,0.1); }
    .vaccination-card-header { padding: 1rem 1.25rem; background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%); border-bottom: 1px solid #e2e8f0; }
    .vaccination-date { font-size: 13px; color: #64748b; }
    .vaccination-day { font-size: 11px; color: #94a3b8; margin-left: 0.5rem; }
    .vaccination-flock { margin: 0.5rem 0 0.25rem 0; }
    .flock-link { color: #1e293b; text-decoration: none; font-weight: 700; font-size: 18px; transition: color 0.2s; }
    .flock-link:hover { color: #0d6e4f; }
    .vaccination-breed { font-size: 12px; color: #64748b; }
    
    .coverage-badge { background: linear-gradient(135deg, #10b981, #059669); border-radius: 12px; padding: 0.5rem 1rem; text-align: center; min-width: 80px; }
    .coverage-value { display: block; font-size: 24px; font-weight: 700; color: white; line-height: 1.1; }
    .coverage-label { font-size: 10px; color: rgba(255,255,255,0.8); text-transform: uppercase; }
    
    .vaccination-card-body { padding: 1rem 1.25rem; }
    .vaccine-info { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem; padding-bottom: 0.75rem; border-bottom: 1px solid #e2e8f0; }
    .vaccine-name { font-size: 14px; }
    .vaccine-disease { font-size: 13px; }
    
    .info-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.75rem; }
    .info-item { display: flex; flex-direction: column; gap: 0.25rem; }
    .info-label { font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; }
    .info-value { font-size: 13px; color: #1e293b; }
    
    .vaccination-notes { margin-top: 0.75rem; padding-top: 0.75rem; border-top: 1px solid #e2e8f0; font-size: 12px; color: #64748b; }
    .vaccination-card-footer { padding: 0.75rem 1.25rem; background: #f8fafc; border-top: 1px solid #e2e8f0; }
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
        .vaccine-info { flex-direction: column; align-items: flex-start; }
        .btn-group { flex-direction: column; }
        .detail-grid { grid-template-columns: 1fr; }
    }
</style>
@endpush
@endsection