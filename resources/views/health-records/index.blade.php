{{-- resources/views/health-records/index.blade.php --}}
@extends('layouts.master')

@section('title', 'Health Records')

@section('content')
<div class="container-fluid px-4">
    <!-- Modern Header -->
    <div class="report-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <div class="d-flex align-items-center gap-3">
                    <div class="header-icon">
                        <i class="fas fa-notes-medical"></i>
                    </div>
                    <div>
                        <h1 class="header-title mb-1">Health Records</h1>
                        <p class="header-subtitle text-muted mb-0">Track flock health, symptoms, and veterinary visits</p>
                    </div>
                </div>
            </div>
            <div class="col-auto">
                <div class="action-buttons">
                    <button type="button" class="btn btn-primary" id="newHealthRecordBtn">
                        <i class="fas fa-plus me-2"></i>New Health Record
                    </button>
                    <a href="{{ route('health-records.critical-alerts') }}" class="btn btn-outline-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>Critical Alerts
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid mb-4">
        <div class="stat-card"><div class="stat-icon bg-primary"><i class="fas fa-chart-line"></i></div><div class="stat-details"><span class="stat-label">Total Records</span><h2 class="stat-value">{{ $records->total() }}</h2><span class="stat-trend">Health records</span></div></div>
        <div class="stat-card"><div class="stat-icon bg-danger"><i class="fas fa-exclamation-triangle"></i></div><div class="stat-details"><span class="stat-label">Critical</span><h2 class="stat-value">{{ $records->where('severity', 'critical')->count() }}</h2><span class="stat-trend text-danger">Urgent attention</span></div></div>
        <div class="stat-card"><div class="stat-icon bg-info"><i class="fas fa-stethoscope"></i></div><div class="stat-details"><span class="stat-label">Affected Animals</span><h2 class="stat-value">{{ number_format($records->sum('affected_count')) }}</h2><span class="stat-trend">Total impacted</span></div></div>
        <div class="stat-card"><div class="stat-icon bg-success"><i class="fas fa-paw"></i></div><div class="stat-details"><span class="stat-label">Flocks Affected</span><h2 class="stat-value">{{ $records->pluck('flock_id')->unique()->count() }}</h2><span class="stat-trend">Unique flocks</span></div></div>
    </div>

    <!-- Main Card -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-0 py-3">
            <div class="row align-items-center"><div class="col"><h5 class="card-title mb-0 fw-semibold"><i class="fas fa-list me-2 text-primary"></i>Health Records</h5></div>
            <div class="col-auto"><button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse"><i class="fas fa-filter me-1"></i>Filters</button></div></div>
        </div>
        
        <div class="collapse {{ request('flock_id') || request('record_type') || request('severity') ? 'show' : '' }}" id="filterCollapse">
            <div class="card-body pt-0">
                <form method="GET" action="{{ route('health-records.index') }}" class="row g-3">
                    <div class="col-md-3"><label class="form-label fw-semibold">Flock</label><select name="flock_id" class="form-select"><option value="">All Flocks</option>@foreach($flocks as $flock)<option value="{{ $flock->id }}" {{ request('flock_id') == $flock->id ? 'selected' : '' }}>{{ $flock->flock_number }}</option>@endforeach</select></div>
                    <div class="col-md-3"><label class="form-label fw-semibold">Record Type</label><select name="record_type" class="form-select"><option value="">All Types</option>@foreach($recordTypes as $type)<option value="{{ $type }}" {{ request('record_type') == $type ? 'selected' : '' }}>{{ ucfirst($type) }}</option>@endforeach</select></div>
                    <div class="col-md-3"><label class="form-label fw-semibold">Severity</label><select name="severity" class="form-select"><option value="">All</option>@foreach($severities as $sev)<option value="{{ $sev }}" {{ request('severity') == $sev ? 'selected' : '' }}>{{ ucfirst($sev) }}</option>@endforeach</select></div>
                    <div class="col-md-3 d-flex align-items-end"><div class="d-flex gap-2 w-100"><button type="submit" class="btn btn-primary flex-grow-1"><i class="fas fa-search me-1"></i>Apply</button><a href="{{ route('health-records.index') }}" class="btn btn-outline-secondary"><i class="fas fa-times"></i></a></div></div>
                </form>
            </div>
        </div>

        <div class="card-body">
            @if($records->count() > 0)
                <div class="row g-4">
                    @foreach($records as $record)
                    @php
                        // Decode symptoms if it's a JSON string
                        $symptomsData = $record->symptoms;
                        if (is_string($symptomsData)) {
                            $symptomsData = json_decode($symptomsData, true);
                        }
                        $symptomsCount = is_array($symptomsData) ? count($symptomsData) : 0;
                    @endphp
                    <div class="col-xl-4 col-lg-6">
                        <div class="health-card severity-{{ $record->severity }}">
                            <div class="health-card-header">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="record-date"><i class="fas fa-calendar-alt me-2 text-primary"></i>{{ $record->record_date->format('d M Y') }}</div>
                                        <h5 class="health-flock mt-2 mb-0"><a href="{{ route('flocks.show', $record->flock_id) }}" class="flock-link">{{ $record->flock->flock_number ?? 'N/A' }}</a></h5>
                                        <div class="health-breed">{{ $record->flock->breed_variety ?? 'N/A' }}</div>
                                    </div>
                                    <div class="severity-badge severity-{{ $record->severity }}">{{ ucfirst($record->severity) }}</div>
                                </div>
                            </div>
                            <div class="health-card-body">
                                <div class="record-info"><span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $record->record_type)) }}</span></div>
                                <div class="condition-info mt-2"><strong>Condition:</strong> {{ $record->condition ?? 'Not specified' }}</div>
                                <div class="affected-info"><strong>Affected:</strong> {{ $record->affected_count ? number_format($record->affected_count) . ' animals (' . $record->affected_percentage . '%)' : 'N/A' }}</div>
                                @if($symptomsCount > 0)<div class="symptoms-preview mt-2"><i class="fas fa-head-side-medical me-1"></i>{{ $symptomsCount }} symptoms recorded</div>@endif
                                @if($record->veterinarian_notes)<div class="vet-notes-preview mt-1"><i class="fas fa-sticky-note me-1"></i>{{ Str::limit($record->veterinarian_notes, 60) }}</div>@endif
                            </div>
                            <div class="health-card-footer">
                                <div class="btn-group w-100">
                                    <button type="button" class="btn btn-outline-primary btn-sm view-record-btn" data-id="{{ $record->id }}"><i class="fas fa-eye me-1"></i>View</button>
                                    <button type="button" class="btn btn-outline-warning btn-sm edit-record-btn" data-id="{{ $record->id }}"><i class="fas fa-edit me-1"></i>Edit</button>
                                    @if(auth()->user()->role === 'admin')<button type="button" class="btn btn-outline-danger btn-sm delete-record-btn" data-id="{{ $record->id }}" data-record-info="{{ $record->flock->flock_number ?? 'N/A' }} - {{ $record->record_date->format('Y-m-d') }}"><i class="fas fa-trash-alt me-1"></i>Delete</button>@endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @if($records->hasPages())<div class="d-flex justify-content-between align-items-center mt-4 pt-2"><div class="text-muted small">Showing {{ $records->firstItem() ?? 0 }} to {{ $records->lastItem() ?? 0 }} of {{ $records->total() }} records</div><div>{{ $records->withQueryString()->links() }}</div></div>@endif
            @else
                <div class="empty-state"><div class="empty-icon"><i class="fas fa-notes-medical"></i></div><h4>No Health Records Found</h4><p>Get started by recording your first health observation.</p><button type="button" class="btn btn-primary" id="emptyStateNewBtn"><i class="fas fa-plus me-2"></i>Record Health</button></div>
            @endif
        </div>
    </div>
</div>

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

<!-- Edit Health Record Modal -->
<div class="modal fade" id="editHealthRecordModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title text-white"><i class="fas fa-edit me-2"></i>Edit Health Record</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="editHealthContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-warning" role="status"></div>
                    <p class="mt-2">Loading details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="saveEditHealth">Update Record</button>
            </div>
        </div>
    </div>
</div>

<!-- Hidden forms -->
<form id="deleteHealthForm" method="POST" style="display: none;">@csrf @method('DELETE')</form>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Helper function to open create modal
    function openCreateHealthModal() {
        const modal = new bootstrap.Modal(document.getElementById('createHealthRecordModal'));
        const modalBody = document.getElementById('createHealthContent');
        
        modalBody.innerHTML = `<div class="text-center py-4"><div class="spinner-border text-success" role="status"></div><p class="mt-2">Loading form...</p></div>`;
        modal.show();
        
        fetch("{{ route('health-records.create-form') }}", {
    headers: {
        'Accept': 'application/json'
    }
})
.then(async response => {
    const text = await response.text();

    try {
        const data = JSON.parse(text);

        if (!response.ok || !data.success) {
            throw new Error(data.message || 'Failed to load form');
        }

        return data;
    } catch (e) {
        console.error('RAW RESPONSE:', text);
        throw new Error('Server did not return valid JSON');
    }
})
.then(data => {
            if (data.success) {
                displayHealthCreateForm(data.flocks);
            } else {
                modalBody.innerHTML = `<div class="alert alert-danger">Failed to load form: ${data.message}</div>`;
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            modalBody.innerHTML = `<div class="alert alert-danger">Error loading form: ${error.message}</div>`;
        });
    }
    
    // Attach event listeners to buttons
    document.getElementById('newHealthRecordBtn')?.addEventListener('click', openCreateHealthModal);
    document.getElementById('emptyStateNewBtn')?.addEventListener('click', openCreateHealthModal);
    
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
                        <label class="form-label fw-semibold">Symptoms</label>
                        <input type="text" name="symptoms" class="form-control" placeholder="e.g. coughing, fever, weakness">
                        <small class="text-muted">Separate symptoms with commas</small>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label fw-semibold">Lab Results</label>
                        <input type="text" name="lab_results" class="form-control" placeholder="e.g. blood test normal, infection detected">
                        <small class="text-muted">Separate results with commas</small>
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
    }
    
    // Save Create Health Record
    document.getElementById('saveCreateHealth')?.addEventListener('click', function() {
        const form = document.getElementById('createHealthForm');
        const formData = new FormData(form);
        const data = {};
        formData.forEach((value, key) => { data[key] = value; });
        
        // Parse JSON fields
        if (data.symptoms && data.symptoms.trim()) {
            try { data.symptoms = JSON.parse(data.symptoms); } catch(e) { data.symptoms = null; }
        } else { data.symptoms = null; }
        if (data.lab_results && data.lab_results.trim()) {
            try { data.lab_results = JSON.parse(data.lab_results); } catch(e) { data.lab_results = null; }
        } else { data.lab_results = null; }
        
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
                Swal.fire({
                    icon: 'success',
                    title: 'Saved!',
                    text: 'Health record created successfully',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    window.location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message || 'Failed to create record'
                });
            }
        })
        .catch(error => {
            console.error('Save error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred while saving'
            });
        });
    });
    
    // View Health Record Modal
    document.querySelectorAll('.view-record-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const modal = new bootstrap.Modal(document.getElementById('viewHealthRecordModal'));
            const modalBody = document.getElementById('viewHealthContent');
            
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
        });
    });
    
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
    
    // Edit Health Record Modal
    document.querySelectorAll('.edit-record-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const modal = new bootstrap.Modal(document.getElementById('editHealthRecordModal'));
            const modalBody = document.getElementById('editHealthContent');
            
            modalBody.innerHTML = `<div class="text-center py-4"><div class="spinner-border text-warning" role="status"></div><p class="mt-2">Loading...</p></div>`;
            modal.show();
            
            fetch(`/health-records/${id}/edit-data`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayEditForm(data.record, data.flocks);
                        window.currentEditId = id;
                    } else {
                        modalBody.innerHTML = `<div class="alert alert-danger">Failed to load details.</div>`;
                    }
                })
                .catch(error => {
                    modalBody.innerHTML = `<div class="alert alert-danger">Error loading data: ${error.message}</div>`;
                });
        });
    });
    
    function displayEditForm(record, flocks) {
        const flockOptions = flocks.map(flock => 
            `<option value="${flock.id}" ${record.flock_id == flock.id ? 'selected' : ''}>${escapeHtml(flock.flock_number)} (${escapeHtml(flock.breed_variety)})</option>`
        ).join('');
        
        let symptomsStr = '';
        let labResultsStr = '';
        
        if (record.symptoms) {
            symptomsStr = typeof record.symptoms === 'string' ? record.symptoms : JSON.stringify(record.symptoms, null, 2);
        }
        if (record.lab_results) {
            labResultsStr = typeof record.lab_results === 'string' ? record.lab_results : JSON.stringify(record.lab_results, null, 2);
        }
        
        document.getElementById('editHealthContent').innerHTML = `
            <form id="editHealthForm">
                <div class="row">
                    <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Flock *</label><select name="flock_id" class="form-select" required>${flockOptions}</select></div>
                    <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Record Date *</label><input type="date" name="record_date" class="form-control" value="${record.record_date}" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Record Type *</label><select name="record_type" class="form-select" required>
                        <option value="checkup" ${record.record_type === 'checkup' ? 'selected' : ''}>Routine Checkup</option>
                        <option value="symptom" ${record.record_type === 'symptom' ? 'selected' : ''}>Symptom Observation</option>
                        <option value="lab_result" ${record.record_type === 'lab_result' ? 'selected' : ''}>Lab Result</option>
                        <option value="post_mortem" ${record.record_type === 'post_mortem' ? 'selected' : ''}>Post-Mortem</option>
                        <option value="consultation" ${record.record_type === 'consultation' ? 'selected' : ''}>Veterinary Consultation</option>
                    </select></div>
                    <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Severity *</label><select name="severity" class="form-select" required>
                        <option value="info" ${record.severity === 'info' ? 'selected' : ''}>Info - Routine</option>
                        <option value="warning" ${record.severity === 'warning' ? 'selected' : ''}>Warning - Monitor</option>
                        <option value="critical" ${record.severity === 'critical' ? 'selected' : ''}>Critical - Immediate Action</option>
                    </select></div>
                    <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Condition/Diagnosis</label><input type="text" name="condition" class="form-control" value="${escapeHtml(record.condition || '')}"></div>
                    <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Affected Count</label><input type="number" name="affected_count" class="form-control" min="0" value="${record.affected_count || ''}"></div>
                    <div class="col-12 mb-3"><label class="form-label fw-semibold">Symptoms (JSON)</label><textarea name="symptoms" class="form-control" rows="2">${escapeHtml(symptomsStr)}</textarea><small class="text-muted">Enter as JSON key-value pairs</small></div>
                    <div class="col-12 mb-3"><label class="form-label fw-semibold">Lab Results (JSON)</label><textarea name="lab_results" class="form-control" rows="2">${escapeHtml(labResultsStr)}</textarea></div>
                    <div class="col-12 mb-3"><label class="form-label fw-semibold">Veterinarian Notes</label><textarea name="veterinarian_notes" class="form-control" rows="2">${escapeHtml(record.veterinarian_notes || '')}</textarea></div>
                    <div class="col-12 mb-3"><label class="form-label fw-semibold">Additional Notes</label><textarea name="notes" class="form-control" rows="2">${escapeHtml(record.notes || '')}</textarea></div>
                </div>
            </form>
        `;
        
        // Re-attach save event
        const saveBtn = document.getElementById('saveEditHealth');
        const newSaveBtn = saveBtn.cloneNode(true);
        saveBtn.parentNode.replaceChild(newSaveBtn, saveBtn);
        
        newSaveBtn.addEventListener('click', function() {
            const form = document.getElementById('editHealthForm');
            const formData = new FormData(form);
            const data = {};
            formData.forEach((value, key) => { data[key] = value; });
            
            if (data.symptoms && data.symptoms.trim()) {
                try { data.symptoms = JSON.parse(data.symptoms); } catch(e) { data.symptoms = null; }
            } else { data.symptoms = null; }
            if (data.lab_results && data.lab_results.trim()) {
                try { data.lab_results = JSON.parse(data.lab_results); } catch(e) { data.lab_results = null; }
            } else { data.lab_results = null; }
            
            fetch(`/health-records/${window.currentEditId}/update-ajax`, {
                method: 'PUT',
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
                    Swal.fire({ icon: 'success', title: 'Updated!', text: 'Health record updated successfully', timer: 1500, showConfirmButton: false })
                        .then(() => window.location.reload());
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Failed to update record' });
                }
            })
            .catch(error => {
                Swal.fire({ icon: 'error', title: 'Error', text: 'An error occurred' });
            });
        });
    }
    
    // Delete Health Record
    document.querySelectorAll('.delete-record-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const info = this.dataset.recordInfo;
            Swal.fire({
                title: 'Delete Health Record',
                text: `Are you sure you want to delete the health record for "${info}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.getElementById('deleteHealthForm');
                    form.action = `/health-records/${id}`;
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

@push('styles')
<style>
    .report-header { margin-bottom: 1.5rem; }
    .header-icon { width: 55px; height: 55px; background: linear-gradient(135deg, #0d6e4f 0%, #0a5a40 100%); border-radius: 14px; display: flex; align-items: center; justify-content: center; }
    .header-icon i { font-size: 26px; color: white; }
    .header-title { font-size: 26px; font-weight: 700; color: #1e293b; margin: 0; }
    .header-subtitle { font-size: 14px; margin: 0; }
    .action-buttons { display: flex; gap: 0.75rem; }
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem; margin-bottom: 1.5rem; }
    .stat-card { background: white; border-radius: 16px; padding: 1rem; display: flex; align-items: center; gap: 1rem; border: 1px solid #e2e8f0; transition: all 0.3s ease; }
    .stat-card:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0,0,0,0.08); }
    .stat-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; }
    .stat-icon i { font-size: 22px; color: white; }
    .stat-details { flex: 1; }
    .stat-label { font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; font-weight: 600; }
    .stat-value { font-size: 28px; font-weight: 700; color: #1e293b; margin: 0.15rem 0; line-height: 1.2; }
    .stat-trend { font-size: 11px; color: #64748b; }
    .health-card { background: white; border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden; transition: all 0.3s ease; height: 100%; }
    .health-card.severity-critical { border-left: 4px solid #dc2626; }
    .health-card.severity-warning { border-left: 4px solid #f59e0b; }
    .health-card.severity-info { border-left: 4px solid #0d6e4f; }
    .health-card-header { padding: 1rem 1.25rem; background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%); border-bottom: 1px solid #e2e8f0; }
    .record-date { font-size: 13px; color: #64748b; }
    .health-flock { margin: 0.5rem 0 0.25rem 0; }
    .flock-link { color: #1e293b; text-decoration: none; font-weight: 700; font-size: 18px; transition: color 0.2s; }
    .flock-link:hover { color: #0d6e4f; }
    .health-breed { font-size: 12px; color: #64748b; }
    .severity-badge { padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 11px; font-weight: 600; }
    .severity-badge.severity-critical { background: #fee2e2; color: #dc2626; }
    .severity-badge.severity-warning { background: #fef3c7; color: #f59e0b; }
    .severity-badge.severity-info { background: #dbeafe; color: #0d6e4f; }
    .health-card-body { padding: 1rem 1.25rem; }
    .condition-info, .affected-info { font-size: 13px; margin-bottom: 0.25rem; }
    .symptoms-preview, .vet-notes-preview { font-size: 11px; color: #64748b; }
    .health-card-footer { padding: 0.75rem 1.25rem; background: #f8fafc; border-top: 1px solid #e2e8f0; }
    .btn-group { display: flex; gap: 0.5rem; }
    .btn-group .btn { flex: 1; border-radius: 8px; font-size: 12px; padding: 0.4rem 0.5rem; }
    .empty-state { text-align: center; padding: 3rem; }
    .empty-icon { width: 70px; height: 70px; background: #f1f5f9; border-radius: 35px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; }
    .detail-section { margin-bottom: 1.5rem; }
    .detail-section h6 { font-weight: 600; color: #1e293b; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 2px solid #e2e8f0; }
    .detail-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; }
    .detail-item { display: flex; flex-direction: column; }
    .detail-label { font-size: 0.7rem; text-transform: uppercase; color: #64748b; font-weight: 600; margin-bottom: 0.25rem; }
    .detail-value { font-size: 1rem; font-weight: 500; color: #1e293b; }
    .symptoms-list { display: flex; flex-wrap: wrap; gap: 0.5rem; }
    .symptom-tag { background: #f1f5f9; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 11px; }
    @media (max-width: 768px) {
        .stats-grid { grid-template-columns: repeat(2, 1fr); gap: 1rem; }
        .stat-value { font-size: 22px; }
        .header-title { font-size: 22px; }
        .btn-group { flex-direction: column; }
        .detail-grid { grid-template-columns: 1fr; }
    }
</style>
@endpush
@endsection