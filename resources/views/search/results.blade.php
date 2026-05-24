@extends('layouts.master')

@section('title', 'Search Results')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">
                    <i class="fas fa-search me-2"></i>
                    Search Results for "{{ $query }}"
                </h4>
                <p class="text-muted">{{ $total }} result(s) found</p>
            </div>
        </div>
    </div>

    @if($total === 0)
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-search fa-3x text-muted mb-3"></i>
                        <h3>No results found</h3>
                        <p class="text-muted">Try searching with different keywords.</p>
                        <a href="{{ url()->previous() }}" class="btn btn-primary mt-2">
                            <i class="fas fa-arrow-left me-2"></i>Go Back
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @else
        <!-- Flocks Section -->
        @if($flocks->count() > 0)
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">
                                <i class="fas fa-users me-2 text-primary"></i>
                                Flocks & Herds ({{ $flocks->count() }})
                            </h4>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Flock #</th>
                                            <th>Species</th>
                                            <th>Bird Count</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($flocks as $flock)
                                            <tr>
                                                <td>{{ $flock['name'] }}</td>
                                                <td>{{ $flock['species'] ?? 'N/A' }}</td>
                                                <td>{{ $flock['bird_count'] ?? 0 }}</td>
                                                <td>
                                                    <button type="button" 
                                                            class="btn btn-sm btn-primary view-flock-btn" 
                                                            data-id="{{ $flock['id'] ?? $flock->id ?? 0 }}"
                                                            onclick="showFlockDetails(this)">
                                                        <i class="fas fa-eye"></i> View
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Houses Section -->
        @if($houses->count() > 0)
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">
                                <i class="fas fa-building me-2 text-success"></i>
                                Houses ({{ $houses->count() }})
                            </h4>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Capacity</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($houses as $house)
                                            <tr>
                                                <td>{{ $house['name'] }}</td>
                                                <td>{{ $house['capacity'] ?? 'N/A' }}</td>
                                                <td>
                                                    <button type="button" 
                                                            class="btn btn-sm btn-success view-house-btn" 
                                                            data-id="{{ $house['id'] ?? $house->id ?? 0 }}"
                                                            onclick="showHouseDetails(this)">
                                                        <i class="fas fa-eye"></i> View
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Treatments Section -->
        @if($treatments->count() > 0)
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">
                                <i class="fas fa-stethoscope me-2 text-danger"></i>
                                Treatments ({{ $treatments->count() }})
                            </h4>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Medication</th>
                                            <th>Flock</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($treatments as $treatment)
                                            <tr>
                                                <td>{{ $treatment['name'] }}</td>
                                                <td>{{ $treatment['flock_number'] ?? 'N/A' }}</td>
                                                <td>
                                                    <button type="button" 
                                                            class="btn btn-sm btn-danger view-treatment-btn" 
                                                            data-id="{{ $treatment['id'] ?? $treatment->id ?? 0 }}"
                                                            onclick="showTreatmentDetails(this)">
                                                        <i class="fas fa-eye"></i> View
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Daily Logs Section -->
        @if($daily_logs->count() > 0)
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">
                                <i class="fas fa-clipboard-list me-2 text-info"></i>
                                Daily Logs ({{ $daily_logs->count() }})
                            </h4>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Log ID</th>
                                            <th>Flock</th>
                                            <th>Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($daily_logs as $log)
                                            <tr>
                                                <td>{{ $log['name'] }}</td>
                                                <td>{{ $log['flock_number'] ?? 'N/A' }}</td>
                                                <td>{{ $log['date'] ?? 'N/A' }}</td>
                                                <td>
                                                    <button type="button" 
                                                            class="btn btn-sm btn-info view-dailylog-btn" 
                                                            data-id="{{ $log['id'] ?? $log->id ?? 0 }}"
                                                            onclick="showDailyLogDetails(this)">
                                                        <i class="fas fa-eye"></i> View
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Expenses Section -->
        @if($expenses->count() > 0)
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">
                                <i class="fas fa-dollar-sign me-2 text-warning"></i>
                                Expenses ({{ $expenses->count() }})
                            </h4>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Description</th>
                                            <th>Category</th>
                                            <th>Amount</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($expenses as $expense)
                                            <tr>
                                                <td>{{ $expense['name'] }}</td>
                                                <td>{{ ucfirst($expense['category'] ?? 'N/A') }}</td>
                                                <td>${{ number_format($expense['amount'] ?? 0, 2) }}</td>
                                                <td>
                                                    <button type="button" 
                                                            class="btn btn-sm btn-warning view-expense-btn" 
                                                            data-id="{{ $expense['id'] ?? $expense->id ?? 0 }}"
                                                            onclick="showExpenseDetails(this)">
                                                        <i class="fas fa-eye"></i> View
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>

<!-- View Flock Modal -->
<div class="modal fade" id="viewFlockModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-eye me-2"></i>Flock Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewFlockContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2">Loading flock details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- View House Modal -->
<div class="modal fade" id="viewHouseModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-eye me-2"></i>House Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewHouseContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-success" role="status"></div>
                    <p class="mt-2">Loading house details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- View Treatment Modal -->
<div class="modal fade" id="viewTreatmentModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-eye me-2"></i>Treatment Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewTreatmentContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-danger" role="status"></div>
                    <p class="mt-2">Loading treatment details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- View Daily Log Modal -->
<div class="modal fade" id="viewDailyLogModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="fas fa-eye me-2"></i>Daily Log Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewDailyLogContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-info" role="status"></div>
                    <p class="mt-2">Loading log details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- View Expense Modal -->
<div class="modal fade" id="viewExpenseModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i class="fas fa-eye me-2"></i>Expense Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewExpenseContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-warning" role="status"></div>
                    <p class="mt-2">Loading expense details...</p>
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
    .page-title-box {
        margin-bottom: 1.5rem;
    }
    
    .page-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: #1e293b;
    }
    
    .header-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #1e293b;
    }
    
    .table th {
        font-weight: 600;
        font-size: 0.85rem;
        color: #475569;
        background-color: #f8fafc;
    }
    
    .table td {
        font-size: 0.85rem;
        color: #334155;
        vertical-align: middle;
    }
    
    .btn-sm {
        padding: 0.25rem 0.75rem;
        font-size: 0.75rem;
    }
    
    /* Modal Styles */
    .modal-header {
        padding: 1rem 1.5rem;
    }
    
    .modal-body {
        padding: 1.5rem;
        max-height: 70vh;
        overflow-y: auto;
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
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
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
        font-size: 0.95rem;
        font-weight: 500;
        color: #1e293b;
    }
    
    /* Status badges */
    .badge-active { background: #dcfce7; color: #166534; }
    .badge-inactive { background: #fee2e2; color: #991b1b; }
    .badge-pending { background: #fef3c7; color: #92400e; }
    
    /* Progress bar */
    .progress {
        background-color: #e2e8f0;
        border-radius: 10px;
        overflow: hidden;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Helper function to escape HTML
function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}

// ==================== FLOCK MODAL ====================
window.showFlockDetails = function(btn) {
    const flockId = btn.getAttribute('data-id');
    const modal = new bootstrap.Modal(document.getElementById('viewFlockModal'));
    const modalBody = document.getElementById('viewFlockContent');
    
    modalBody.innerHTML = `<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Loading flock details...</p></div>`;
    modal.show();
    
    fetch(`/flocks/${flockId}/details`, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const flock = data.flock;
            const summary = data.summary;
            const statusClass = flock.status === 'active' ? 'badge-active' : 'badge-inactive';
            
            modalBody.innerHTML = `
                <div class="detail-section">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h5 class="mb-1">${escapeHtml(flock.flock_number)}</h5>
                            <p class="text-muted mb-0">${escapeHtml(flock.breed_variety)}</p>
                        </div>
                        <span class="badge ${statusClass} px-3 py-2">${escapeHtml(flock.status || 'N/A')}</span>
                    </div>
                </div>
                
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="text-center p-3 bg-light rounded">
                            <div class="fs-4 fw-bold text-primary">${summary.age_days}</div>
                            <small class="text-muted">Age (days)</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center p-3 bg-light rounded">
                            <div class="fs-4 fw-bold text-success">${summary.current_count.toLocaleString()}</div>
                            <small class="text-muted">Current Count</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center p-3 bg-light rounded">
                            <div class="fs-4 fw-bold text-danger">${summary.mortality_rate}%</div>
                            <small class="text-muted">Mortality Rate</small>
                        </div>
                    </div>
                </div>
                
                <div class="detail-section">
                    <h6>Basic Information</h6>
                    <div class="detail-grid">
                        <div class="detail-item"><span class="detail-label">Species</span><span class="detail-value">${escapeHtml(flock.species_name)}</span></div>
                        <div class="detail-item"><span class="detail-label">House</span><span class="detail-value">${escapeHtml(flock.house_name)}</span></div>
                        <div class="detail-item"><span class="detail-label">Breed</span><span class="detail-value">${escapeHtml(flock.breed_variety)}</span></div>
                        <div class="detail-item"><span class="detail-label">Start Date</span><span class="detail-value">${flock.start_date}</span></div>
                        <div class="detail-item"><span class="detail-label">Initial Count</span><span class="detail-value">${flock.initial_count.toLocaleString()}</span></div>
                        <div class="detail-item"><span class="detail-label">Production Type</span><span class="detail-value">${escapeHtml(flock.production_type)}</span></div>
                    </div>
                </div>
                
                <div class="detail-section">
                    <h6>Performance Metrics</h6>
                    <div class="detail-grid">
                        <div class="detail-item"><span class="detail-label">Survival Rate</span><span class="detail-value">${summary.survival_rate}%</span></div>
                        <div class="detail-item"><span class="detail-label">Feed Conversion Ratio</span><span class="detail-value">${summary.fcr}</span></div>
                        <div class="detail-item"><span class="detail-label">Total Feed</span><span class="detail-value">${summary.total_feed.toLocaleString()} kg</span></div>
                        <div class="detail-item"><span class="detail-label">Avg Daily Gain</span><span class="detail-value">${summary.avg_daily_gain} kg</span></div>
                    </div>
                </div>
                
                ${flock.notes ? `<div class="detail-section"><h6>Notes</h6><p class="mb-0 p-3 bg-light rounded">${escapeHtml(flock.notes)}</p></div>` : ''}
            `;
        } else {
            modalBody.innerHTML = `<div class="alert alert-danger">Failed to load flock details: ${data.message}</div>`;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        modalBody.innerHTML = `<div class="alert alert-danger">Error loading data</div>`;
    });
};

// ==================== HOUSE MODAL ====================
window.showHouseDetails = function(btn) {
    const houseId = btn.getAttribute('data-id');
    const modal = new bootstrap.Modal(document.getElementById('viewHouseModal'));
    const modalBody = document.getElementById('viewHouseContent');
    
    modalBody.innerHTML = `<div class="text-center py-4"><div class="spinner-border text-success" role="status"></div><p class="mt-2">Loading house details...</p></div>`;
    modal.show();
    
    fetch(`/houses/${houseId}/details`, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const house = data.house;
            const stats = data.stats;
            
            modalBody.innerHTML = `
                <div class="detail-section">
                    <h5 class="mb-2">${escapeHtml(house.name)}</h5>
                    <p class="text-muted">Code: ${escapeHtml(house.house_code)}</p>
                </div>
                
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="text-center p-3 bg-light rounded">
                            <div class="fs-4 fw-bold text-primary">${stats.total_flocks}</div>
                            <small class="text-muted">Total Flocks</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center p-3 bg-light rounded">
                            <div class="fs-4 fw-bold text-success">${stats.total_animals.toLocaleString()}</div>
                            <small class="text-muted">Total Animals</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center p-3 bg-light rounded">
                            <div class="fs-4 fw-bold text-info">${stats.occupancy_rate}%</div>
                            <small class="text-muted">Occupancy Rate</small>
                        </div>
                    </div>
                </div>
                
                <div class="detail-section">
                    <h6>Basic Information</h6>
                    <div class="detail-grid">
                        <div class="detail-item"><span class="detail-label">House Code</span><span class="detail-value">${escapeHtml(house.house_code)}</span></div>
                        <div class="detail-item"><span class="detail-label">Species</span><span class="detail-value">${escapeHtml(house.species_name || 'Not Assigned')}</span></div>
                        <div class="detail-item"><span class="detail-label">Status</span><span class="detail-value">${escapeHtml(house.status)}</span></div>
                        <div class="detail-item"><span class="detail-label">Capacity</span><span class="detail-value">${house.capacity.toLocaleString()}</span></div>
                    </div>
                </div>
                
                <div class="detail-section">
                    <h6>Dimensions & Equipment</h6>
                    <div class="detail-grid">
                        <div class="detail-item"><span class="detail-label">Length</span><span class="detail-value">${house.length_m || 0} m</span></div>
                        <div class="detail-item"><span class="detail-label">Width</span><span class="detail-value">${house.width_m || 0} m</span></div>
                        <div class="detail-item"><span class="detail-label">Area</span><span class="detail-value">${((house.length_m || 0) * (house.width_m || 0)).toFixed(2)} m²</span></div>
                        <div class="detail-item"><span class="detail-label">Feeders</span><span class="detail-value">${house.feeders_count || 0}</span></div>
                        <div class="detail-item"><span class="detail-label">Drinkers</span><span class="detail-value">${house.drinkers_count || 0}</span></div>
                        <div class="detail-item"><span class="detail-label">Fans/Heaters</span><span class="detail-value">${house.fans_count || 0} / ${house.heaters_count || 0}</span></div>
                    </div>
                </div>
                
                ${house.notes ? `<div class="detail-section"><h6>Notes</h6><p class="mb-0 p-3 bg-light rounded">${escapeHtml(house.notes)}</p></div>` : ''}
            `;
        } else {
            modalBody.innerHTML = `<div class="alert alert-danger">Failed to load house details: ${data.message}</div>`;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        modalBody.innerHTML = `<div class="alert alert-danger">Error loading data</div>`;
    });
};

// ==================== TREATMENT MODAL ====================
window.showTreatmentDetails = function(btn) {
    const treatmentId = btn.getAttribute('data-id');
    const modal = new bootstrap.Modal(document.getElementById('viewTreatmentModal'));
    const modalBody = document.getElementById('viewTreatmentContent');
    
    modalBody.innerHTML = `<div class="text-center py-4"><div class="spinner-border text-danger" role="status"></div><p class="mt-2">Loading treatment details...</p></div>`;
    modal.show();
    
    fetch(`/treatments/${treatmentId}/details`, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const t = data.treatment;
            
            modalBody.innerHTML = `
                <div class="detail-section">
                    <h5 class="mb-1">${escapeHtml(t.medication_name)}</h5>
                    <p class="text-muted">${escapeHtml(t.diagnosis)}</p>
                </div>
                
                <div class="detail-section">
                    <h6>Treatment Information</h6>
                    <div class="detail-grid">
                        <div class="detail-item"><span class="detail-label">Flock</span><span class="detail-value">${escapeHtml(t.flock_number)}</span></div>
                        <div class="detail-item"><span class="detail-label">Start Date</span><span class="detail-value">${t.start_date}</span></div>
                        <div class="detail-item"><span class="detail-label">End Date</span><span class="detail-value">${t.end_date}</span></div>
                        <div class="detail-item"><span class="detail-label">Dosage</span><span class="detail-value">${escapeHtml(t.dosage)}</span></div>
                        <div class="detail-item"><span class="detail-label">Route</span><span class="detail-value">${escapeHtml(t.administration_route)}</span></div>
                        <div class="detail-item"><span class="detail-label">Animals Treated</span><span class="detail-value">${t.animals_treated || 'N/A'}</span></div>
                        ${t.withdrawal_end_date ? `<div class="detail-item"><span class="detail-label">Withdrawal Ends</span><span class="detail-value">${t.withdrawal_end_date}</span></div>` : ''}
                    </div>
                </div>
                
                ${t.notes ? `<div class="detail-section"><h6>Notes</h6><p class="mb-0 p-3 bg-light rounded">${escapeHtml(t.notes)}</p></div>` : ''}
            `;
        } else {
            modalBody.innerHTML = `<div class="alert alert-danger">Failed to load treatment details: ${data.message}</div>`;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        modalBody.innerHTML = `<div class="alert alert-danger">Error loading data</div>`;
    });
};

// ==================== DAILY LOG MODAL ====================
window.showDailyLogDetails = function(btn) {
    const logId = btn.getAttribute('data-id');
    const modal = new bootstrap.Modal(document.getElementById('viewDailyLogModal'));
    const modalBody = document.getElementById('viewDailyLogContent');
    
    modalBody.innerHTML = `<div class="text-center py-4"><div class="spinner-border text-info" role="status"></div><p class="mt-2">Loading log details...</p></div>`;
    modal.show();
    
    fetch(`/daily-logs/${logId}/details`, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const log = data.log;
            
            modalBody.innerHTML = `
                <div class="detail-section">
                    <h6>Log Information</h6>
                    <div class="detail-grid">
                        <div class="detail-item"><span class="detail-label">Date</span><span class="detail-value">${log.log_date}</span></div>
                        <div class="detail-item"><span class="detail-label">Flock</span><span class="detail-value">${escapeHtml(log.flock_number)}</span></div>
                        <div class="detail-item"><span class="detail-label">Mortality</span><span class="detail-value">${log.mortality_count}</span></div>
                        <div class="detail-item"><span class="detail-label">Culling</span><span class="detail-value">${log.culling_count}</span></div>
                        <div class="detail-item"><span class="detail-label">Feed Intake</span><span class="detail-value">${log.feed_intake_kg} kg</span></div>
                        <div class="detail-item"><span class="detail-label">Water Consumption</span><span class="detail-value">${log.water_consumption_liters} L</span></div>
                        <div class="detail-item"><span class="detail-label">Avg Weight</span><span class="detail-value">${log.average_weight_kg} kg</span></div>
                        <div class="detail-item"><span class="detail-label">Temperature</span><span class="detail-value">${log.min_temperature_c}°C - ${log.max_temperature_c}°C</span></div>
                        <div class="detail-item"><span class="detail-label">Humidity</span><span class="detail-value">${log.min_humidity}% - ${log.max_humidity}%</span></div>
                        <div class="detail-item"><span class="detail-label">Ammonia</span><span class="detail-value">${log.ammonia_ppm} ppm</span></div>
                    </div>
                </div>
                
                ${log.notes ? `<div class="detail-section"><h6>Notes</h6><p class="mb-0 p-3 bg-light rounded">${escapeHtml(log.notes)}</p></div>` : ''}
            `;
        } else {
            modalBody.innerHTML = `<div class="alert alert-danger">Failed to load log details: ${data.message}</div>`;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        modalBody.innerHTML = `<div class="alert alert-danger">Error loading data</div>`;
    });
};

// ==================== EXPENSE MODAL ====================
window.showExpenseDetails = function(btn) {
    const expenseId = btn.getAttribute('data-id');
    const modal = new bootstrap.Modal(document.getElementById('viewExpenseModal'));
    const modalBody = document.getElementById('viewExpenseContent');
    
    modalBody.innerHTML = `<div class="text-center py-4"><div class="spinner-border text-warning" role="status"></div><p class="mt-2">Loading expense details...</p></div>`;
    modal.show();
    
    fetch(`/expenses/${expenseId}/details-json`, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const e = data.expense;
            
            modalBody.innerHTML = `
                <div class="detail-section">
                    <h6>Expense Information</h6>
                    <div class="detail-grid">
                        <div class="detail-item"><span class="detail-label">Date</span><span class="detail-value">${e.expense_date}</span></div>
                        <div class="detail-item"><span class="detail-label">Category</span><span class="detail-value">${escapeHtml(e.category)}</span></div>
                        <div class="detail-item"><span class="detail-label">Description</span><span class="detail-value">${escapeHtml(e.description)}</span></div>
                        <div class="detail-item"><span class="detail-label">Amount</span><span class="detail-value text-danger fw-bold">₵${parseFloat(e.amount).toLocaleString()}</span></div>
                        <div class="detail-item"><span class="detail-label">Vendor</span><span class="detail-value">${escapeHtml(e.vendor_name || 'N/A')}</span></div>
                        <div class="detail-item"><span class="detail-label">Payment Method</span><span class="detail-value">${escapeHtml(e.payment_method || 'N/A')}</span></div>
                        <div class="detail-item"><span class="detail-label">Receipt Number</span><span class="detail-value">${escapeHtml(e.receipt_number || 'N/A')}</span></div>
                        <div class="detail-item"><span class="detail-label">Associated Flock</span><span class="detail-value">${escapeHtml(e.flock_number || 'None')}</span></div>
                        <div class="detail-item"><span class="detail-label">Associated House</span><span class="detail-value">${escapeHtml(e.house_name || 'None')}</span></div>
                    </div>
                </div>
                
                ${e.notes ? `<div class="detail-section"><h6>Notes</h6><p class="mb-0 p-3 bg-light rounded">${escapeHtml(e.notes)}</p></div>` : ''}
            `;
        } else {
            modalBody.innerHTML = `<div class="alert alert-danger">Failed to load expense details: ${data.message}</div>`;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        modalBody.innerHTML = `<div class="alert alert-danger">Error loading data</div>`;
    });
};
</script>
@endpush
@endsection