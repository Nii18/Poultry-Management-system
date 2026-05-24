@extends('layouts.master')

@section('content')
<div class="container-fluid px-4">
    
    @include('dashboard.partials.role-header')
    
    <!-- Health Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-card-icon bg-danger-soft">
                        <i class="fas fa-skull text-danger"></i>
                    </div>
                    <div class="stat-card-info">
                        <span class="stat-card-label">Today's Mortality</span>
                        <h3 class="stat-card-value">{{ $todayMortality ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-card-icon bg-warning-soft">
                        <i class="fas fa-pills text-warning"></i>
                    </div>
                    <div class="stat-card-info">
                        <span class="stat-card-label">Active Treatments</span>
                        <h3 class="stat-card-value">{{ $activeTreatments->count() ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-card-icon bg-info-soft">
                        <i class="fas fa-syringe text-info"></i>
                    </div>
                    <div class="stat-card-info">
                        <span class="stat-card-label">Upcoming Vaccinations</span>
                        <h3 class="stat-card-value">{{ $upcomingVaccinations->count() ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-card-icon bg-primary-soft">
                        <i class="fas fa-exclamation-triangle text-primary"></i>
                    </div>
                    <div class="stat-card-info">
                        <span class="stat-card-label">Critical Alerts</span>
                        <h3 class="stat-card-value">{{ $healthAlerts->count() ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="card-title mb-0 fw-semibold">
                        <i class="fas fa-bolt me-2 text-primary"></i>Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <button type="button" class="quick-action-btn w-100" id="newHealthRecordBtn">
                                <i class="fas fa-notes-medical text-primary"></i>
                                <span>Health Record</span>
                            </button>
                        </div>
                        <div class="col-md-4">
                            <button type="button" class="quick-action-btn w-100" onclick="openCreateModal()">
                                <i class="fas fa-syringe text-success"></i>
                                <span>Add Vaccination</span>
                            </button>
                        </div>
                        <div class="col-md-4">
                            <button type="button" class="quick-action-btn w-100" onclick="openCreateTreatmentModal()">
                                <i class="fas fa-pills text-warning"></i>
                                <span>New Treatment</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    

    <!-- Active Treatments Section -->
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-semibold">
                        <i class="fas fa-pills me-2 text-warning"></i>Active Treatments
                    </h5>
                    <a href="{{ route('treatments.index', ['status' => 'active']) }}" class="btn btn-sm btn-outline-warning">View All</a>
                </div>
                <div class="card-body">
                    @forelse($activeTreatments as $treatment)
                    <div class="treatment-item d-flex justify-content-between align-items-start p-3 mb-2 bg-light rounded-3">
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <h6 class="mb-0">{{ $treatment->diagnosis ?? 'Treatment' }}</h6>
                                <span class="badge bg-warning-soft text-warning">Active</span>
                            </div>
                            <small class="text-muted">Flock: {{ $treatment->flock->flock_number ?? 'N/A' }}</small>
                            <br><small class="text-muted">Product: {{ $treatment->product_name ?? 'N/A' }}</small>
                            @if($treatment->end_date)
                                <br><small class="text-muted">Ends: {{ Carbon\Carbon::parse($treatment->end_date)->format('d M Y') }}</small>
                            @endif
                            @if($treatment->withdrawal_end_date && Carbon\Carbon::parse($treatment->withdrawal_end_date)->isFuture())
                                <br><small class="text-danger">⚠️ Withdrawal until {{ Carbon\Carbon::parse($treatment->withdrawal_end_date)->format('d M Y') }}</small>
                            @endif
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary ms-2 view-treatment-btn" data-id="{{ $treatment->id }}" title="View Details">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    @empty
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-check-circle fa-2x mb-2"></i>
                        <p>No active treatments</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Upcoming Vaccinations -->
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-semibold">
                        <i class="fas fa-calendar-alt me-2 text-primary"></i>Upcoming Vaccinations
                    </h5>
                    <button type="button" class="btn btn-sm btn-outline-info" onclick="openScheduleModal()">
                        <i class="fas fa-calendar-alt me-1"></i> View Schedule
                    </button>
                </div>
                <div class="card-body">
                    @forelse($upcomingVaccinations as $vaccination)
                    <div class="vaccination-item d-flex justify-content-between align-items-start p-3 mb-2 bg-light rounded-3">
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <h6 class="mb-0">{{ $vaccination->vaccine_name ?? 'Vaccination' }}</h6>
                                <span class="badge bg-info-soft text-info">Upcoming</span>
                            </div>
                            <small class="text-muted">Flock: {{ $vaccination->flock->flock_number ?? 'N/A' }}</small>
                            <br><small class="text-muted">Target: {{ $vaccination->disease_target ?? 'N/A' }}</small>
                            <br><small class="text-muted">Date: {{ Carbon\Carbon::parse($vaccination->administration_date)->format('d M Y') }}</small>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary ms-2 view-vaccination-btn" data-id="{{ $vaccination->id }}" title="View Details">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    @empty
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-calendar-check fa-2x mb-2"></i>
                        <p>No upcoming vaccinations</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Health Records & Critical Alerts -->
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-semibold">
                        <i class="fas fa-notes-medical me-2 text-primary"></i>Recent Health Records
                    </h5>
                    <a href="{{ route('health-records.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    @forelse($recentHealthRecords as $record)
                    <div class="health-record-item d-flex justify-content-between align-items-start p-3 mb-2 bg-light rounded-3">
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <h6 class="mb-0">{{ $record->condition ?? 'Health Check' }}</h6>
                                <span class="badge bg-{{ $record->severity === 'critical' ? 'danger' : ($record->severity === 'warning' ? 'warning' : 'info') }}-soft">
                                    {{ ucfirst($record->severity) }}
                                </span>
                            </div>
                            <small class="text-muted">Flock: {{ $record->flock->flock_number ?? 'N/A' }}</small>
                            <br><small class="text-muted">Type: {{ ucfirst(str_replace('_', ' ', $record->record_type)) }}</small>
                            <br><small class="text-muted">Date: {{ $record->record_date->format('d M Y') }}</small>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary ms-2 view-health-record-btn" data-id="{{ $record->id }}" title="View Details">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    @empty
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-notes-medical fa-2x mb-2"></i>
                        <p>No recent health records</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-semibold">
                        <i class="fas fa-bell me-2 text-danger"></i>Critical Health Alerts
                    </h5>
                    <a href="{{ route('health-records.critical-alerts') }}" class="btn btn-sm btn-outline-danger">View All</a>
                </div>
                <div class="card-body">
                    @forelse($healthAlerts as $alert)
                    <div class="alert-item d-flex justify-content-between align-items-start p-3 mb-2 bg-light-danger rounded-3">
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <h6 class="mb-0">{{ $alert->title ?? 'Health Alert' }}</h6>
                                <span class="badge bg-danger">Critical</span>
                            </div>
                            <p class="mb-1 small">{{ $alert->message }}</p>
                            <small class="text-muted">{{ $alert->created_at->diffForHumans() }}</small>
                        </div>
                        <a href="{{ route('notifications.show', $alert->id) }}" class="btn btn-sm btn-outline-danger ms-2">
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                    @empty
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-check-circle fa-2x mb-2"></i>
                        <p>No critical alerts</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Withdrawal Alerts -->
    @if($withdrawalAlerts->count() > 0)
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0 fw-semibold">
                <i class="fas fa-clock me-2 text-warning"></i>Withdrawal Period Alerts
            </h5>
            <a href="{{ route('treatments.withdrawal-alerts') }}" class="btn btn-sm btn-outline-warning">View All</a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Flock</th>
                            <th>Product</th>
                            <th>Withdrawal End Date</th>
                            <th>Days Remaining</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($withdrawalAlerts as $withdrawal)
                        <tr>
                            <td>{{ $withdrawal->flock->flock_number ?? 'N/A' }}</td>
                            <td>{{ $withdrawal->product_name }}</td>
                            <td>{{ Carbon\Carbon::parse($withdrawal->withdrawal_end_date)->format('d M Y') }}</td>
                            <td>
                                @php
                                    $daysLeft = Carbon\Carbon::now()->diffInDays($withdrawal->withdrawal_end_date, false);
                                @endphp
                                <span class="badge bg-{{ $daysLeft <= 3 ? 'danger' : 'warning' }}-soft">
                                    {{ $daysLeft }} days left
                                </span>
                             </td
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-primary view-treatment-btn" data-id="{{ $withdrawal->id }}">
                                    <i class="fas fa-eye"></i>
                                </button>
                             </td
                         ?>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>


<!-- ==================== MODALS ==================== -->

<!-- Create Health Record Modal -->
<div class="modal fade" id="createHealthRecordModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title text-white"><i class="fas fa-plus-circle me-2"></i>New Health Record</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="createHealthContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-success" role="status"></div>
                    <p class="mt-2">Loading form...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="saveCreateHealth">Save Record</button>
            </div>
        </div>
    </div>
</div>

<!-- View Health Record Modal -->
<div class="modal fade" id="viewHealthRecordModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white"><i class="fas fa-eye me-2"></i>Health Record Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewHealthContent">
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
                    <p class="mt-2">Loading details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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

@push('styles')
<style>

.detail-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; }
    .detail-item { display: flex; flex-direction: column; }
    .detail-label { font-size: 0.7rem; text-transform: uppercase; color: #64748b; font-weight: 600; margin-bottom: 0.25rem; }
    .detail-value { font-size: 1rem; font-weight: 500; color: #1e293b; }
    .symptoms-list { display: flex; flex-wrap: wrap; gap: 0.5rem; }
    .symptom-tag { background: #f1f5f9; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 11px; }

    .quick-action-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.75rem;
        padding: 1rem;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        font-weight: 500;
        color: #1e293b;
        transition: all 0.3s ease;
        text-decoration: none;
    }
    .quick-action-btn:hover {
        background: white;
        border-color: #10b981;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .quick-action-btn i { font-size: 1.5rem; }
    
    .treatment-item, .vaccination-item, .health-record-item, .alert-item {
        transition: all 0.3s ease;
        cursor: pointer;
    }
    .treatment-item:hover, .vaccination-item:hover, .health-record-item:hover, .alert-item:hover {
        background: white !important;
        transform: translateX(5px);
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .bg-light-danger { background: #fee2e2; }
    .bg-warning-soft { background: #fef3c7; color: #92400e; }
    .bg-info-soft { background: #e0f2fe; color: #1e40af; }
    .bg-danger-soft { background: #fee2e2; color: #991b1b; }
    .bg-primary-soft { background: #dcfce7; color: #166534; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // ==================== HELPER FUNCTIONS ====================
    
    function closeAllModals() {
        const modals = document.querySelectorAll('.modal.show');
        modals.forEach(modal => {
            const modalInstance = bootstrap.Modal.getInstance(modal);
            if (modalInstance) {
                modalInstance.hide();
            }
        });
        const backdrops = document.querySelectorAll('.modal-backdrop');
        backdrops.forEach(backdrop => backdrop.remove());
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
    }
    
    function cleanupModalOnHide(modalElement) {
        modalElement.addEventListener('hidden.bs.modal', function() {
            const backdrops = document.querySelectorAll('.modal-backdrop');
            backdrops.forEach(backdrop => backdrop.remove());
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
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

    // ==================== HEALTH RECORD MODAL FUNCTIONS ====================
    
    function openCreateHealthModal() {
        closeAllModals();
        
        const modalElement = document.getElementById('createHealthRecordModal');
        const modal = new bootstrap.Modal(modalElement, { backdrop: 'static', keyboard: true });
        const modalBody = document.getElementById('createHealthContent');
        
        cleanupModalOnHide(modalElement);
        
        modalBody.innerHTML = `<div class="text-center py-4"><div class="spinner-border text-success" role="status"></div><p class="mt-2">Loading form...</p></div>`;
        modal.show();
        
        fetch('/health-records/create-form', {
            headers: { 'Accept': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayHealthCreateForm(data.flocks);
            } else {
                modalBody.innerHTML = `<div class="alert alert-danger">Failed to load form: ${data.message}</div>`;
            }
        })
        .catch(error => {
            modalBody.innerHTML = `<div class="alert alert-danger">Error loading form: ${error.message}</div>`;
        });
    }
    
    function displayHealthCreateForm(flocks) {
        const flockOptions = flocks.map(flock => `<option value="${flock.id}">${escapeHtml(flock.flock_number)} (${escapeHtml(flock.breed_variety)})</option>`).join('');
        
        document.getElementById('createHealthContent').innerHTML = `
            <form id="createHealthForm">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Flock <span class="text-danger">*</span></label>
                        <select name="flock_id" class="form-select" required>${flockOptions}</select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Record Date <span class="text-danger">*</span></label>
                        <input type="date" name="record_date" class="form-control" value="${new Date().toISOString().split('T')[0]}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Record Type <span class="text-danger">*</span></label>
                        <select name="record_type" class="form-select" required>
                            <option value="checkup">Routine Checkup</option>
                            <option value="symptom">Symptom Observation</option>
                            <option value="lab_result">Lab Result</option>
                            <option value="post_mortem">Post-Mortem</option>
                            <option value="consultation">Veterinary Consultation</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Severity <span class="text-danger">*</span></label>
                        <select name="severity" class="form-select" required>
                            <option value="info">Info - Routine</option>
                            <option value="warning">Warning - Monitor</option>
                            <option value="critical">Critical - Immediate Action</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Condition/Diagnosis</label>
                        <input type="text" name="condition" class="form-control" placeholder="e.g., Respiratory infection">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Affected Count</label>
                        <input type="number" name="affected_count" class="form-control" min="0">
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label fw-semibold">Symptoms (JSON format)</label>
                        <textarea name="symptoms" class="form-control" rows="2" placeholder='{"coughing": "yes", "fever": "39.5"}'></textarea>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label fw-semibold">Lab Results (JSON format)</label>
                        <textarea name="lab_results" class="form-control" rows="2" placeholder='{"blood_test": "normal"}'></textarea>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label fw-semibold">Veterinarian Notes</label>
                        <textarea name="veterinarian_notes" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label fw-semibold">Additional Notes</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
            </form>
        `;
        
        document.getElementById('saveCreateHealth').onclick = function() {
            const form = document.getElementById('createHealthForm');
            const formData = new FormData(form);
            const data = {};
            formData.forEach((value, key) => { data[key] = value; });
            
            fetch('/health-records/store-ajax', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({ icon: 'success', title: 'Saved!', text: 'Health record created successfully', timer: 1500, showConfirmButton: false })
                        .then(() => window.location.reload());
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Failed to create record' });
                }
            });
        };
    }
    
    function viewHealthRecord(id) {
        closeAllModals();
        
        const modalElement = document.getElementById('viewHealthRecordModal');
        const modal = new bootstrap.Modal(modalElement, { backdrop: 'static', keyboard: true });
        const modalBody = document.getElementById('viewHealthContent');
        
        cleanupModalOnHide(modalElement);
        
        modalBody.innerHTML = `<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Loading...</p></div>`;
        modal.show();
        
        fetch(`/health-records/${id}/details`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayHealthDetails(data.record);
                } else {
                    modalBody.innerHTML = `<div class="alert alert-danger">Failed to load details.</div>`;
                }
            })
            .catch(error => {
                modalBody.innerHTML = `<div class="alert alert-danger">Error loading data: ${error.message}</div>`;
            });
    }
    
    function displayHealthDetails(record) {
        let symptoms = record.symptoms;
        let labResults = record.lab_results;
        
        if (typeof symptoms === 'string') {
            try { symptoms = JSON.parse(symptoms); } catch(e) { symptoms = {}; }
        }
        if (typeof labResults === 'string') {
            try { labResults = JSON.parse(labResults); } catch(e) { labResults = {}; }
        }
        
        document.getElementById('viewHealthContent').innerHTML = `
            <div class="detail-section"><h6>Record Information</h6><div class="detail-grid">
                <div class="detail-item"><span class="detail-label">Flock</span><span class="detail-value">${escapeHtml(record.flock_number)}</span></div>
                <div class="detail-item"><span class="detail-label">Record Date</span><span class="detail-value">${record.record_date}</span></div>
                <div class="detail-item"><span class="detail-label">Record Type</span><span class="detail-value">${escapeHtml(record.record_type)}</span></div>
                <div class="detail-item"><span class="detail-label">Severity</span><span class="detail-value">${escapeHtml(record.severity)}</span></div>
                <div class="detail-item"><span class="detail-label">Condition</span><span class="detail-value">${escapeHtml(record.condition || 'Not specified')}</span></div>
                <div class="detail-item"><span class="detail-label">Affected Animals</span><span class="detail-value">${record.affected_count ? record.affected_count.toLocaleString() + ' (' + record.affected_percentage + '%)' : 'N/A'}</span></div>
            </div></div>
            ${symptoms && Object.keys(symptoms).length > 0 ? `<div class="detail-section"><h6>Symptoms</h6><div class="symptoms-list">${Object.entries(symptoms).map(([k,v]) => `<span class="symptom-tag">${k.replace(/_/g, ' ')}: ${v}</span>`).join('')}</div></div>` : ''}
            ${labResults && Object.keys(labResults).length > 0 ? `<div class="detail-section"><h6>Lab Results</h6><div class="detail-grid">${Object.entries(labResults).map(([k,v]) => `<div class="detail-item"><span class="detail-label">${k.replace(/_/g, ' ')}</span><span class="detail-value">${v}</span></div>`).join('')}</div></div>` : ''}
            ${record.veterinarian_notes ? `<div class="detail-section"><h6>Veterinarian Notes</h6><p>${escapeHtml(record.veterinarian_notes)}</p></div>` : ''}
            ${record.notes ? `<div class="detail-section"><h6>Additional Notes</h6><p>${escapeHtml(record.notes)}</p></div>` : ''}
        `;
    }
    
    // ==================== VACCINATION MODAL FUNCTIONS ====================
    
    function openCreateModal() {
        closeAllModals();
        
        const modalElement = document.getElementById('createVaccinationModal');
        const modal = new bootstrap.Modal(modalElement, { backdrop: 'static', keyboard: true });
        const modalBody = document.getElementById('createVaccinationContent');
        
        cleanupModalOnHide(modalElement);
        
        modalBody.innerHTML = `<div class="text-center py-4"><div class="spinner-border text-success" role="status"></div><p class="mt-2">Loading form...</p></div>`;
        modal.show();
        
        fetch('/vaccinations/create-form')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayVaccinationCreateForm(data.flocks);
                } else {
                    modalBody.innerHTML = `<div class="alert alert-danger">Failed to load form: ${data.message}</div>`;
                }
            })
            .catch(error => {
                modalBody.innerHTML = `<div class="alert alert-danger">Error: ${error.message}</div>`;
            });
    }
    
    function displayVaccinationCreateForm(flocks) {
        const flockOptions = flocks.map(flock => `<option value="${flock.id}">${escapeHtml(flock.flock_number)} (${escapeHtml(flock.breed_variety)})</option>`).join('');
        
        document.getElementById('createVaccinationContent').innerHTML = `
            <form id="createVaccinationForm">
                <div class="row">
                    <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Flock *</label><select name="flock_id" class="form-select" required>${flockOptions}</select></div>
                    <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Vaccine Name *</label><input type="text" name="vaccine_name" class="form-control" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Disease Target *</label><input type="text" name="disease_target" class="form-control" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Day Administered *</label><input type="number" name="day_administered" class="form-control" min="0" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Administration Date *</label><input type="date" name="administration_date" class="form-control" value="${new Date().toISOString().split('T')[0]}" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Route *</label><select name="route" class="form-select" required>
                        <option value="subcutaneous">Subcutaneous</option><option value="intramuscular">Intramuscular</option>
                        <option value="drinking_water">Drinking Water</option><option value="spray">Spray</option><option value="eye_drop">Eye Drop</option>
                    </select></div>
                    <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Batch Number *</label><input type="text" name="batch_number" class="form-control" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Expiry Date *</label><input type="date" name="expiry_date" class="form-control" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Dosage (ml)</label><input type="number" name="dosage_ml" class="form-control" step="0.01" min="0"></div>
                    <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Birds Vaccinated</label><input type="number" name="birds_vaccinated" class="form-control" min="0"></div>
                    <div class="col-12 mb-3"><label class="form-label fw-semibold">Notes</label><textarea name="notes" class="form-control" rows="3"></textarea></div>
                </div>
            </form>
        `;
        
        document.getElementById('saveCreateVaccination').onclick = function() {
            const form = document.getElementById('createVaccinationForm');
            const formData = new FormData(form);
            const data = {};
            formData.forEach((value, key) => { data[key] = value; });
            
            fetch('/vaccinations/store-ajax', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
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
            });
        };
    }
    
    function viewVaccination(id) {
        closeAllModals();
        
        const modalElement = document.getElementById('viewVaccinationModal');
        const modal = new bootstrap.Modal(modalElement, { backdrop: 'static', keyboard: true });
        const modalBody = document.getElementById('viewVaccinationContent');
        
        cleanupModalOnHide(modalElement);
        
        modalBody.innerHTML = `<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Loading...</p></div>`;
        modal.show();
        
        fetch(`/vaccinations/${id}/details`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayVaccinationDetails(data.vaccination);
                } else {
                    modalBody.innerHTML = `<div class="alert alert-danger">Failed to load details.</div>`;
                }
            })
            .catch(error => {
                modalBody.innerHTML = `<div class="alert alert-danger">Error loading data: ${error.message}</div>`;
            });
    }
    
    function displayVaccinationDetails(v) {
        document.getElementById('viewVaccinationContent').innerHTML = `
            <div class="detail-section"><h6>Vaccination Information</h6><div class="detail-grid">
                <div class="detail-item"><span class="detail-label">Flock</span><span class="detail-value">${escapeHtml(v.flock_number)}</span></div>
                <div class="detail-item"><span class="detail-label">Vaccine</span><span class="detail-value">${escapeHtml(v.vaccine_name)}</span></div>
                <div class="detail-item"><span class="detail-label">Disease Target</span><span class="detail-value">${escapeHtml(v.disease_target)}</span></div>
                <div class="detail-item"><span class="detail-label">Day</span><span class="detail-value">Day ${v.day_administered}</span></div>
                <div class="detail-item"><span class="detail-label">Administration Date</span><span class="detail-value">${v.administration_date}</span></div>
                <div class="detail-item"><span class="detail-label">Route</span><span class="detail-value">${escapeHtml(v.route)}</span></div>
                <div class="detail-item"><span class="detail-label">Batch Number</span><span class="detail-value">${escapeHtml(v.batch_number)}</span></div>
                <div class="detail-item"><span class="detail-label">Expiry Date</span><span class="detail-value">${v.expiry_date}</span></div>
                <div class="detail-item"><span class="detail-label">Coverage</span><span class="detail-value">${v.coverage_percentage}%</span></div>
            </div></div>
            ${v.notes ? `<div class="detail-section"><h6>Notes</h6><p>${escapeHtml(v.notes)}</p></div>` : ''}
        `;
    }
    
    // ==================== TREATMENT MODAL FUNCTIONS ====================
    
    function openCreateTreatmentModal() {
        closeAllModals();
        
        const modalElement = document.getElementById('createTreatmentModal');
        const modal = new bootstrap.Modal(modalElement, { backdrop: 'static', keyboard: true });
        const modalBody = document.getElementById('createTreatmentContent');
        
        cleanupModalOnHide(modalElement);
        
        modalBody.innerHTML = `<div class="text-center py-4"><div class="spinner-border text-success" role="status"></div><p class="mt-2">Loading form...</p></div>`;
        modal.show();
        
        fetch('/treatments/create-form')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayTreatmentCreateForm(data.flocks);
                } else {
                    modalBody.innerHTML = `<div class="alert alert-danger">Failed to load form: ${data.message}</div>`;
                }
            })
            .catch(error => {
                modalBody.innerHTML = `<div class="alert alert-danger">Error: ${error.message}</div>`;
            });
    }
    
    function displayTreatmentCreateForm(flocks) {
        const flockOptions = flocks.map(flock => `<option value="${flock.id}">${escapeHtml(flock.flock_number)} (${escapeHtml(flock.breed_variety)})</option>`).join('');
        
        document.getElementById('createTreatmentContent').innerHTML = `
            <form id="createTreatmentForm">
                <div class="row">
                    <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Flock *</label><select name="flock_id" class="form-select" required>${flockOptions}</select></div>
                    <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Diagnosis *</label><input type="text" name="diagnosis" class="form-control" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Product Name *</label><input type="text" name="product_name" class="form-control" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Active Ingredient</label><input type="text" name="active_ingredient" class="form-control"></div>
                    <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Dosage *</label><input type="text" name="dosage" class="form-control" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Administration Route *</label><select name="administration_route" class="form-select" required>
                        <option value="water">Water</option><option value="feed">Feed</option><option value="injection">Injection</option><option value="topical">Topical</option>
                    </select></div>
                    <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Start Date *</label><input type="date" name="start_date" class="form-control" value="${new Date().toISOString().split('T')[0]}" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label fw-semibold">End Date *</label><input type="date" name="end_date" class="form-control" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Withdrawal Days</label><input type="number" name="withdrawal_days" class="form-control" min="0"></div>
                    <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Batch Number</label><input type="text" name="batch_number" class="form-control"></div>
                    <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Animals Treated</label><input type="number" name="animals_treated" class="form-control" min="0"></div>
                    <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Cost</label><input type="number" name="cost" class="form-control" step="0.01" min="0"></div>
                    <div class="col-12 mb-3"><label class="form-label fw-semibold">Notes</label><textarea name="notes" class="form-control" rows="3"></textarea></div>
                </div>
            </form>
        `;
        
        document.getElementById('saveCreateTreatment').onclick = function() {
            const form = document.getElementById('createTreatmentForm');
            const formData = new FormData(form);
            const data = {};
            formData.forEach((value, key) => { data[key] = value; });
            
            fetch('/treatments/store-ajax', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
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
            });
        };
    }
    
    function viewTreatment(id) {
        closeAllModals();
        
        const modalElement = document.getElementById('viewTreatmentModal');
        const modal = new bootstrap.Modal(modalElement, { backdrop: 'static', keyboard: true });
        const modalBody = document.getElementById('viewTreatmentContent');
        
        cleanupModalOnHide(modalElement);
        
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
            <div class="detail-section"><h6>Treatment Information</h6><div class="detail-grid">
                <div class="detail-item"><span class="detail-label">Flock</span><span class="detail-value">${escapeHtml(t.flock_number)}</span></div>
                <div class="detail-item"><span class="detail-label">Diagnosis</span><span class="detail-value">${escapeHtml(t.diagnosis)}</span></div>
                <div class="detail-item"><span class="detail-label">Product</span><span class="detail-value">${escapeHtml(t.product_name)}</span></div>
                <div class="detail-item"><span class="detail-label">Treatment Period</span><span class="detail-value">${t.start_date} to ${t.end_date}</span></div>
                <div class="detail-item"><span class="detail-label">Dosage</span><span class="detail-value">${escapeHtml(t.dosage)}</span></div>
                <div class="detail-item"><span class="detail-label">Route</span><span class="detail-value">${escapeHtml(t.administration_route)}</span></div>
                <div class="detail-item"><span class="detail-label">Withdrawal Ends</span><span class="detail-value">${t.withdrawal_end_date || 'N/A'}</span></div>
            </div></div>
            ${t.notes ? `<div class="detail-section"><h6>Notes</h6><p>${escapeHtml(t.notes)}</p></div>` : ''}
        `;
    }
    
    // ==================== SCHEDULE MODAL FUNCTION ====================
    
    function openScheduleModal() {
        closeAllModals();
        
        const modalElement = document.getElementById('scheduleModal');
        const modal = new bootstrap.Modal(modalElement, { backdrop: 'static', keyboard: true });
        const modalBody = document.getElementById('scheduleContent');
        
        cleanupModalOnHide(modalElement);
        
        modalBody.innerHTML = `<div class="text-center py-4"><div class="spinner-border text-info" role="status"></div><p class="mt-2">Loading schedule...</p></div>`;
        modal.show();
        
        fetch('/vaccinations/schedule-data')
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
        let upcomingHtml = '<h6>Upcoming Vaccinations</h6><div class="table-responsive"><table class="table table-bordered"><thead class="table-light"><tr><th>Date</th><th>Flock</th><th>Vaccine</th><th>Disease</th></tr></thead><tbody>';
        upcoming.forEach(v => {
            upcomingHtml += `<td><td class="text-nowrap">${v.administration_date}</td><td>${escapeHtml(v.flock_number)}</td><td>${escapeHtml(v.vaccine_name)}</td><td>${escapeHtml(v.disease_target)}</td></tr>`;
        });
        upcomingHtml += '</tbody>\\</div>';
        
        let pastHtml = '<h6 class="mt-3">Past Vaccinations</h6><div class="table-responsive"><table class="table table-bordered"><thead class="table-light"><tr><th>Date</th><th>Flock</th><th>Vaccine</th><th>Disease</th></tr></thead><tbody>';
        past.forEach(v => {
            pastHtml += `<td><td class="text-nowrap text-muted">${v.administration_date}</td><td class="text-muted">${escapeHtml(v.flock_number)}</td><td class="text-muted">${escapeHtml(v.vaccine_name)}</td><td class="text-muted">${escapeHtml(v.disease_target)}</td></tr>`;
        });
        pastHtml += '</tbody>\\</div>';
        
        document.getElementById('scheduleContent').innerHTML = upcomingHtml + pastHtml;
    }
    
    // ==================== EVENT LISTENERS ====================
    
    document.getElementById('newHealthRecordBtn')?.addEventListener('click', openCreateHealthModal);
    
    document.querySelectorAll('.view-treatment-btn').forEach(btn => {
        btn.addEventListener('click', function() { 
            viewTreatment(this.dataset.id); 
        });
    });
    
    document.querySelectorAll('.view-vaccination-btn').forEach(btn => {
        btn.addEventListener('click', function() { 
            viewVaccination(this.dataset.id); 
        });
    });
    
    document.querySelectorAll('.view-health-record-btn').forEach(btn => {
        btn.addEventListener('click', function() { 
            viewHealthRecord(this.dataset.id); 
        });
    });
</script>
@endpush

@endsection