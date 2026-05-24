{{-- resources/views/breeding-records/index.blade.php --}}
@extends('layouts.master')

@section('content')
<div class="container-fluid px-4">
    <!-- Page Header -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <div class="d-flex align-items-center gap-3">
                    <div class="page-icon">
                        <i class="fas fa-baby-carriage fs-1 text-primary"></i>
                    </div>
                    <div>
                        <h1 class="page-title mb-1">Breeding Records</h1>
                        <p class="page-description text-muted mb-0">Track breeding activities, pregnancy progress, and offspring management</p>
                    </div>
                </div>
            </div>
            <div class="col-auto">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Breeding Records</li>
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
                        <i class="fas fa-heartbeat text-primary"></i>
                    </div>
                    <div class="stat-card-info">
                        <span class="stat-card-label">Total Breedings</span>
                        <h3 class="stat-card-value">{{ $records->total() }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-card-icon bg-warning-soft">
                        <i class="fas fa-clock text-warning"></i>
                    </div>
                    <div class="stat-card-info">
                        <span class="stat-card-label">Pending Deliveries</span>
                        <h3 class="stat-card-value">{{ $records->whereNull('actual_delivery_date')->where('expected_delivery_date', '>', now())->count() }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-card-icon bg-success-soft">
                        <i class="fas fa-check-circle text-success"></i>
                    </div>
                    <div class="stat-card-info">
                        <span class="stat-card-label">Successful</span>
                        <h3 class="stat-card-value">{{ $records->where('is_successful', true)->count() }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-card-icon bg-info-soft">
                        <i class="fas fa-baby text-info"></i>
                    </div>
                    <div class="stat-card-info">
                        <span class="stat-card-label">Total Offspring</span>
                        <h3 class="stat-card-value">{{ number_format($records->sum('offspring_count')) }}</h3>
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
                        <i class="fas fa-list me-2 text-primary"></i>Breeding Records
                    </h5>
                </div>
                <div class="col-auto">
                    <a href="{{ route('breeding-records.pending') }}" class="btn btn-info me-2">
                        <i class="fas fa-clock me-2"></i>Pending Deliveries
                    </a>
                    <button type="button" class="btn btn-primary" id="newBreedingRecordBtn">
                        <i class="fas fa-plus me-2"></i>New Breeding Record
                    </button>
                </div>
            </div>
        </div>
        
        <div class="card-body">
            <!-- Filters Section -->
            <div class="filter-section mb-4 p-3 bg-light rounded-3">
                <div class="row align-items-end g-3">
                    <div class="col-md-5">
                        <label class="form-label fw-semibold mb-2">
                            <i class="fas fa-paw me-1 text-muted"></i>Flock (Female)
                        </label>
                        <select name="flock_id" class="form-select" id="flockFilter">
                            <option value="">All Flocks</option>
                            @foreach($flocks as $flock)
                                <option value="{{ $flock->id }}" {{ request('flock_id') == $flock->id ? 'selected' : '' }}>
                                    {{ $flock->flock_number }} ({{ $flock->breed_variety }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label fw-semibold mb-2">
                            <i class="fas fa-flag-checkered me-1 text-muted"></i>Status
                        </label>
                        <select name="status" class="form-select" id="statusFilter">
                            <option value="all" {{ request('status', 'all') == 'all' ? 'selected' : '' }}>All Records</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending Delivery</option>
                            <option value="successful" {{ request('status') == 'successful' ? 'selected' : '' }}>Successful</option>
                            <option value="unsuccessful" {{ request('status') == 'unsuccessful' ? 'selected' : '' }}>Unsuccessful</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-primary w-100" id="applyFilters">
                                <i class="fas fa-filter me-2"></i>Filter
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Breeding Records Table -->
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="py-3">Female Flock</th>
                            <th class="py-3">Male Flock</th>
                            <th class="py-3">Breeding Date</th>
                            <th class="py-3">Expected Delivery</th>
                            <th class="py-3">Actual Delivery</th>
                            <th class="py-3">Offspring</th>
                            <th class="py-3">Success Rate</th>
                            <th class="py-3">Status</th>
                            <th class="py-3 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $record)
                        <tr>
                            <td>
                                <button type="button" class="btn btn-link p-0 text-primary fw-semibold text-decoration-none view-record-btn" 
                                        data-id="{{ $record->id }}" data-bs-toggle="modal" data-bs-target="#viewBreedingModal">
                                    {{ $record->female->flock_number ?? 'N/A' }}
                                </button>
                                <br>
                                <small class="text-muted">{{ $record->female->breed_variety ?? 'N/A' }}</small>
                            </td>
                            <td>
                                @if($record->male)
                                    <div>
                                        <strong>{{ $record->male->flock_number ?? 'N/A' }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $record->male->breed_variety ?? 'N/A' }}</small>
                                    </div>
                                @else
                                    <span class="badge bg-info-soft text-info">
                                        <i class="fas fa-syringe me-1"></i>External / AI
                                    </span>
                                @endif
                            </td>
                            <td>{{ $record->breeding_date->format('d M Y') }}</td>
                            <td>
                                {{ $record->expected_delivery_date->format('d M Y') }}
                                @if(!$record->actual_delivery_date && $record->expected_delivery_date > now())
                                    <br>
                                    <small class="text-warning">({{ now()->diffInDays($record->expected_delivery_date) }} days left)</small>
                                @elseif(!$record->actual_delivery_date && $record->expected_delivery_date <= now())
                                    <br>
                                    <small class="text-danger">(Overdue)</small>
                                @endif
                            </td>
                            <td>
                                @if($record->actual_delivery_date)
                                    {{ $record->actual_delivery_date->format('d M Y') }}
                                @else
                                    <span class="text-muted">Pending</span>
                                @endif
                            </td>
                            <td>
                                @if($record->offspring_count)
                                    <div class="d-flex flex-column">
                                        <span class="fw-semibold">{{ $record->offspring_count }} born</span>
                                        <small class="text-muted">Weaned: {{ $record->weaned_count ?? 0 }}</small>
                                    </div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $successRate = $record->conception_rate;
                                    $rateColor = $successRate >= 80 ? 'success' : ($successRate >= 50 ? 'warning' : 'danger');
                                @endphp
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height: 6px;">
                                        <div class="progress-bar bg-{{ $rateColor }}" 
                                             style="width: {{ $successRate }}%"></div>
                                    </div>
                                    <span class="small fw-semibold">{{ $successRate }}%</span>
                                </div>
                            </td>
                            <td>
                                @php
                                    if($record->is_successful) {
                                        $statusColor = 'success';
                                        $statusText = 'Successful';
                                        $statusIcon = 'fa-check-circle';
                                    } elseif($record->actual_delivery_date && !$record->is_successful) {
                                        $statusColor = 'danger';
                                        $statusText = 'Failed';
                                        $statusIcon = 'fa-times-circle';
                                    } else {
                                        $statusColor = 'warning';
                                        $statusText = 'Pending';
                                        $statusIcon = 'fa-hourglass-half';
                                    }
                                @endphp
                                <span class="badge bg-{{ $statusColor }}-soft text-{{ $statusColor }} px-3 py-2 rounded-pill">
                                    <i class="fas {{ $statusIcon }} me-1" style="font-size: 8px;"></i>
                                    {{ $statusText }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group gap-1">
                                    <button type="button" class="btn btn-sm btn-outline-primary view-record-btn" 
                                            data-id="{{ $record->id }}" data-bs-toggle="modal" data-bs-target="#viewBreedingModal" title="View Details">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                    @if(!$record->actual_delivery_date)
                                        <button type="button" class="btn btn-sm btn-outline-success record-delivery-btn" 
                                                data-id="{{ $record->id }}" data-flock="{{ $record->female->flock_number ?? 'N/A' }}" title="Record Delivery">
                                            <i class="fas fa-baby"></i> Delivery
                                        </button>
                                    @endif
                                    @if(auth()->user()->role === 'admin')
                                        <button type="button" class="btn btn-sm btn-outline-danger delete-record-btn" 
                                                data-id="{{ $record->id }}" data-info="{{ $record->female->flock_number ?? 'N/A' }} - {{ $record->breeding_date->format('Y-m-d') }}" title="Delete">
                                            <i class="fas fa-trash-alt"></i> Delete
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <div class="empty-state">
                                    <i class="fas fa-heart-broken fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">No Breeding Records Found</h5>
                                    <p class="text-muted mb-3">Get started by creating your first breeding record</p>
                                    <button type="button" class="btn btn-primary" id="emptyStateNewBtn">
                                        <i class="fas fa-plus me-2"></i>New Breeding Record
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($records->hasPages())
            <div class="d-flex justify-content-between align-items-center mt-4 pt-2">
                <div class="text-muted small">
                    Showing {{ $records->firstItem() ?? 0 }} to {{ $records->lastItem() ?? 0 }} of {{ $records->total() }} results
                </div>
                <div>
                    {{ $records->withQueryString()->links() }}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Create Breeding Record Modal -->
<div class="modal fade" id="createBreedingModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title text-white">
                    <i class="fas fa-plus-circle me-2"></i>New Breeding Record
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="createBreedingContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading form...</p>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveCreateBreeding">Create Record</button>
            </div>
        </div>
    </div>
</div>

<!-- View Breeding Record Modal -->
<div class="modal fade" id="viewBreedingModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title text-white">
                    <i class="fas fa-eye me-2"></i>Breeding Record Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewBreedingContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading breeding details...</p>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Record Delivery Modal -->
<div class="modal fade" id="recordDeliveryModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-success text-white border-0">
                <h5 class="modal-title text-white">
                    <i class="fas fa-baby me-2"></i>Record Delivery
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form id="recordDeliveryForm">
                    @csrf
                    <div class="alert alert-info mb-3">
                        <i class="fas fa-info-circle me-2"></i>
                        Recording delivery for flock: <strong id="deliveryFlockName"></strong>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Actual Delivery Date <span class="text-danger">*</span></label>
                        <input type="date" name="actual_delivery_date" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Offspring Count <span class="text-danger">*</span></label>
                            <input type="number" name="offspring_count" class="form-control" min="0" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Stillborn Count</label>
                            <input type="number" name="stillborn_count" class="form-control" min="0" value="0">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Weaned Count</label>
                            <input type="number" name="weaned_count" class="form-control" min="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Status</label>
                            <select name="is_successful" class="form-select" required>
                                <option value="1">Successful - Healthy delivery</option>
                                <option value="0">Unsuccessful - Complications/Stillbirth</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 pt-0 pb-4 px-4">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success px-4" id="submitDeliveryBtn">
                    <i class="fas fa-save me-1"></i>Save Delivery
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Hidden form for delete -->
<form id="deleteBreedingForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

@push('styles')
<style>
    /* Page Header Styles */
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
    
    .text-primary { color: #0d6e4f !important; }
    .text-success { color: #10b981 !important; }
    .text-info { color: #3b82f6 !important; }
    .text-warning { color: #f59e0b !important; }
    .text-danger { color: #dc2626 !important; }
    
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
    
    /* Filter Section */
    .filter-section {
        background: #f8fafc;
        border-radius: 12px;
    }
    
    /* Table Styles */
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
    
    /* Badge Styles */
    .bg-success-soft {
        background-color: #dcfce7;
        color: #166534;
    }
    
    .bg-secondary-soft {
        background-color: #f1f5f9;
        color: #475569;
    }
    
    .bg-danger-soft {
        background-color: #fee2e2;
        color: #991b1b;
    }
    
    .bg-warning-soft {
        background-color: #fef3c7;
        color: #92400e;
    }
    
    .badge {
        font-weight: 500;
        font-size: 0.75rem;
    }
    
    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 2rem;
    }
    
    /* Progress Bar */
    .progress {
        background-color: #e2e8f0;
        border-radius: 10px;
        overflow: hidden;
    }
    
    /* Button Group */
    .btn-group .btn {
        border-radius: 8px !important;
        margin: 0 2px;
        padding: 0.25rem 0.5rem;
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
    
    /* Stats Cards in Modal */
    .stats-card {
        background: #f8fafc;
        border-radius: 12px;
        padding: 1rem;
        border: 1px solid #e2e8f0;
        transition: all 0.3s ease;
    }
    
    .stats-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 0.5rem;
    }
    
    .stats-number {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1e293b;
    }
    
    .stats-label {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #64748b;
        font-weight: 600;
    }
    
    .offspring-list {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .offspring-card {
        background: #f8fafc;
        border-radius: 10px;
        padding: 0.75rem;
        border-left: 3px solid #0d6e4f;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // Helper function to properly cleanup and close modals
    function closeAllModals() {
        // Get all open modals
        const modals = document.querySelectorAll('.modal.show');
        modals.forEach(modal => {
            const modalInstance = bootstrap.Modal.getInstance(modal);
            if (modalInstance) {
                modalInstance.hide();
            }
            // Remove any lingering backdrop
            const backdrops = document.querySelectorAll('.modal-backdrop');
            backdrops.forEach(backdrop => backdrop.remove());
            // Remove body classes
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
        });
    }
    
    // Apply Filters
    document.getElementById('applyFilters')?.addEventListener('click', function() {
        const flockId = document.getElementById('flockFilter').value;
        const status = document.getElementById('statusFilter').value;
        let url = '{{ route("breeding-records.index") }}';
        const params = new URLSearchParams();
        
        if (flockId) params.append('flock_id', flockId);
        if (status && status !== 'all') params.append('status', status);
        
        if (params.toString()) {
            url += '?' + params.toString();
        }
        
        window.location.href = url;
    });
    
    // ==================== CREATE BREEDING RECORD MODAL ====================
    let createModal = null;
    
    function openCreateBreedingModal() {
        // Close any existing modal first
        closeAllModals();
        
        const modalElement = document.getElementById('createBreedingModal');
        createModal = new bootstrap.Modal(modalElement, {
            backdrop: 'static',
            keyboard: false
        });
        const modalBody = document.getElementById('createBreedingContent');
        
        modalBody.innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-2">Loading form...</p>
            </div>
        `;
        createModal.show();
        
        fetch('{{ route("breeding-records.create-form") }}', {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayBreedingCreateForm(data.female_flocks, data.male_flocks);
            } else {
                modalBody.innerHTML = `<div class="alert alert-danger m-3">Failed to load form: ${data.message}</div>`;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            modalBody.innerHTML = `<div class="alert alert-danger m-3">Error loading form: ${error.message}</div>`;
        });
    }
    
    // Clean up create modal when hidden
    document.getElementById('createBreedingModal')?.addEventListener('hidden.bs.modal', function() {
        if (createModal) {
            createModal.dispose();
            createModal = null;
        }
        // Ensure backdrop is removed
        const backdrops = document.querySelectorAll('.modal-backdrop');
        backdrops.forEach(backdrop => backdrop.remove());
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
    });
    
    function displayBreedingCreateForm(femaleFlocks, maleFlocks) {
        const femaleOptions = femaleFlocks.map(flock => 
            `<option value="${flock.id}" data-gestation="${flock.gestation_days || 0}">${escapeHtml(flock.flock_number)} - ${escapeHtml(flock.breed_variety)} (${escapeHtml(flock.species_name)})</option>`
        ).join('');
        
        const maleOptions = maleFlocks.map(flock => 
            `<option value="${flock.id}">${escapeHtml(flock.flock_number)} - ${escapeHtml(flock.breed_variety)}</option>`
        ).join('');
        
        document.getElementById('createBreedingContent').innerHTML = `
            <form id="createBreedingForm">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Female Flock (Dam) <span class="text-danger">*</span></label>
                        <select name="flock_id" class="form-select" required id="femaleFlockSelect">
                            <option value="">Select Female Flock</option>
                            ${femaleOptions}
                        </select>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Male Flock (Sire)</label>
                        <select name="mate_id" class="form-select" id="maleFlockSelect">
                            <option value="">Select Male Flock (or use AI)</option>
                            ${maleOptions}
                        </select>
                        <small class="text-muted">Leave empty for Artificial Insemination</small>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Breeding Date <span class="text-danger">*</span></label>
                        <input type="date" name="breeding_date" class="form-control" value="${new Date().toISOString().split('T')[0]}" required id="breedingDateInput">
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Expected Delivery Date <span class="text-danger">*</span></label>
                        <input type="date" name="expected_delivery_date" class="form-control" required id="expectedDeliveryInput">
                        <small class="text-muted">Will be auto-calculated based on species gestation period</small>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Breeding Method <span class="text-danger">*</span></label>
                        <select name="breeding_method" class="form-select" required>
                            <option value="natural">Natural</option>
                            <option value="artificial_insemination">Artificial Insemination</option>
                        </select>
                    </div>
                    
                    <div class="col-12 mb-3">
                        <label class="form-label fw-semibold">Notes</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Any additional notes about this breeding..."></textarea>
                    </div>
                </div>
            </form>
        `;
        
        // Auto-calculate expected delivery date
        const femaleSelect = document.getElementById('femaleFlockSelect');
        const breedingDate = document.getElementById('breedingDateInput');
        const expectedDelivery = document.getElementById('expectedDeliveryInput');
        
        function calculateExpectedDate() {
            const selectedOption = femaleSelect.options[femaleSelect.selectedIndex];
            const gestationDays = parseInt(selectedOption?.dataset.gestation || 0);
            const breedingDateValue = breedingDate.value;
            
            if (gestationDays > 0 && breedingDateValue) {
                const date = new Date(breedingDateValue);
                date.setDate(date.getDate() + gestationDays);
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                expectedDelivery.value = `${year}-${month}-${day}`;
            }
        }
        
        femaleSelect.addEventListener('change', calculateExpectedDate);
        breedingDate.addEventListener('change', calculateExpectedDate);
    }
    
    // Save Create Breeding Record
    document.getElementById('saveCreateBreeding')?.addEventListener('click', function() {
        const form = document.getElementById('createBreedingForm');
        if (!form) return;
        
        const formData = new FormData(form);
        const data = {};
        formData.forEach((value, key) => { data[key] = value; });
        
        // Validate required fields
        if (!data.flock_id || !data.breeding_date || !data.expected_delivery_date || !data.breeding_method) {
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Please fill in all required fields'
            });
            return;
        }
        
        const saveBtn = this;
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
        
        fetch('{{ route("breeding-records.store-ajax") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Close modal properly
                if (createModal) {
                    createModal.hide();
                }
                Swal.fire({
                    icon: 'success',
                    title: 'Created!',
                    text: 'Breeding record created successfully',
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
                saveBtn.disabled = false;
                saveBtn.innerHTML = 'Create Record';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred while saving'
            });
            saveBtn.disabled = false;
            saveBtn.innerHTML = 'Create Record';
        });
    });
    
    document.getElementById('newBreedingRecordBtn')?.addEventListener('click', openCreateBreedingModal);
    document.getElementById('emptyStateNewBtn')?.addEventListener('click', openCreateBreedingModal);
    
    // ==================== VIEW BREEDING RECORD MODAL ====================
    let viewModal = null;
    
    document.querySelectorAll('.view-record-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            // Close any existing modal first
            closeAllModals();
            
            const id = this.dataset.id;
            const modalElement = document.getElementById('viewBreedingModal');
            viewModal = new bootstrap.Modal(modalElement, {
                backdrop: 'static',
                keyboard: true
            });
            const modalBody = document.getElementById('viewBreedingContent');
            
            modalBody.innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2">Loading breeding details...</p>
                </div>
            `;
            viewModal.show();
            
            fetch(`/breeding-records/${id}/details-json`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayBreedingDetails(data.record);
                } else {
                    modalBody.innerHTML = `<div class="alert alert-danger m-3">Failed to load details: ${data.message}</div>`;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                modalBody.innerHTML = `<div class="alert alert-danger m-3">Error loading data: ${error.message}</div>`;
            });
        });
    });
    
    // Clean up view modal when hidden
    document.getElementById('viewBreedingModal')?.addEventListener('hidden.bs.modal', function() {
        if (viewModal) {
            viewModal.dispose();
            viewModal = null;
        }
        // Ensure backdrop is removed
        const backdrops = document.querySelectorAll('.modal-backdrop');
        backdrops.forEach(backdrop => backdrop.remove());
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
    });
    
    function displayBreedingDetails(record) {
        const statusClass = record.is_successful ? 'success' : (record.actual_delivery_date ? 'danger' : 'warning');
        const statusText = record.is_successful ? 'Successful' : (record.actual_delivery_date ? 'Failed' : 'Pending');
        
        const offspringHtml = record.offspring_records && record.offspring_records.length > 0 ? `
            <div class="detail-section">
                <h6><i class="fas fa-paw me-2"></i>Offspring Flocks</h6>
                <div class="offspring-list">
                    ${record.offspring_records.map(offspring => `
                        <div class="offspring-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>${escapeHtml(offspring.flock_number)}</strong>
                                    <span class="badge bg-info ms-2">${offspring.count} animals</span>
                                </div>
                                <small class="text-muted">${offspring.ear_tag_range !== 'N/A' ? 'Tags: ' + offspring.ear_tag_range : ''}</small>
                            </div>
                            ${offspring.avg_birth_weight ? `<small class="text-muted d-block mt-1">Avg Birth Weight: ${offspring.avg_birth_weight} kg</small>` : ''}
                        </div>
                    `).join('')}
                </div>
            </div>
        ` : '';
        
        document.getElementById('viewBreedingContent').innerHTML = `
            <div class="detail-section">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h5 class="mb-1">${escapeHtml(record.female_flock_number)} <i class="fas fa-times mx-2 text-muted"></i> ${escapeHtml(record.male_flock_number)}</h5>
                        <p class="text-muted mb-0">${escapeHtml(record.female_breed || '')} ${record.male_flock_number !== 'External/AI' && record.male_breed ? '× ' + escapeHtml(record.male_breed) : ''}</p>
                    </div>
                    <span class="badge bg-${statusClass}-soft text-${statusClass} px-3 py-2 rounded-pill">
                        <i class="fas fa-circle me-1" style="font-size: 8px;"></i>
                        ${statusText}
                    </span>
                </div>
            </div>
            
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="stats-card text-center">
                        <div class="stats-icon bg-primary-soft">
                            <i class="fas fa-chart-line text-primary"></i>
                        </div>
                        <div class="stats-number">${record.conception_rate || 0}%</div>
                        <div class="stats-label">Conception Rate</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stats-card text-center">
                        <div class="stats-icon bg-success-soft">
                            <i class="fas fa-heartbeat text-success"></i>
                        </div>
                        <div class="stats-number">${record.live_birth_rate || 0}%</div>
                        <div class="stats-label">Live Birth Rate</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stats-card text-center">
                        <div class="stats-icon bg-info-soft">
                            <i class="fas fa-baby text-info"></i>
                        </div>
                        <div class="stats-number">${record.weaning_rate || 0}%</div>
                        <div class="stats-label">Weaning Rate</div>
                    </div>
                </div>
            </div>
            
            <div class="detail-section">
                <h6><i class="fas fa-info-circle me-2"></i>Breeding Information</h6>
                <div class="detail-grid">
                    <div class="detail-item">
                        <span class="detail-label">Breeding Date</span>
                        <span class="detail-value">${record.breeding_date || 'N/A'}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Expected Delivery</span>
                        <span class="detail-value">${record.expected_delivery_date || 'N/A'}</span>
                    </div>
                    ${record.actual_delivery_date ? `
                    <div class="detail-item">
                        <span class="detail-label">Actual Delivery</span>
                        <span class="detail-value">${record.actual_delivery_date}</span>
                    </div>
                    ` : ''}
                    <div class="detail-item">
                        <span class="detail-label">Breeding Method</span>
                        <span class="detail-value">${record.breeding_method || 'N/A'}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Recorded By</span>
                        <span class="detail-value">${escapeHtml(record.recorded_by || 'N/A')}</span>
                    </div>
                </div>
            </div>
            
            ${record.actual_delivery_date && record.offspring_count !== undefined ? `
            <div class="detail-section">
                <h6><i class="fas fa-baby-carriage me-2"></i>Delivery Information</h6>
                <div class="detail-grid">
                    <div class="detail-item">
                        <span class="detail-label">Total Offspring</span>
                        <span class="detail-value">${record.offspring_count || 0}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Stillborn</span>
                        <span class="detail-value">${record.stillborn_count || 0}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Live Births</span>
                        <span class="detail-value">${(record.offspring_count || 0) - (record.stillborn_count || 0)}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Weaned Count</span>
                        <span class="detail-value">${record.weaned_count || 0}</span>
                    </div>
                </div>
            </div>
            ` : ''}
            
            ${offspringHtml}
            
            ${record.notes ? `
            <div class="detail-section">
                <h6><i class="fas fa-pencil-alt me-2"></i>Notes</h6>
                <p class="mb-0 p-3 bg-light rounded">${escapeHtml(record.notes)}</p>
            </div>
            ` : ''}
        `;
    }
    
    // ==================== RECORD DELIVERY MODAL ====================
    let deliveryModal = null;
    let currentDeliveryId = null;
    let currentDeliveryFlock = null;
    
    document.querySelectorAll('.record-delivery-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            // Close any existing modal first
            closeAllModals();
            
            currentDeliveryId = this.dataset.id;
            currentDeliveryFlock = this.dataset.flock;
            
            const modalElement = document.getElementById('recordDeliveryModal');
            deliveryModal = new bootstrap.Modal(modalElement, {
                backdrop: 'static',
                keyboard: true
            });
            const form = document.getElementById('recordDeliveryForm');
            
            form.reset();
            const today = new Date().toISOString().split('T')[0];
            form.querySelector('input[name="actual_delivery_date"]').value = today;
            document.getElementById('deliveryFlockName').innerText = escapeHtml(currentDeliveryFlock);
            
            deliveryModal.show();
        });
    });
    
    // Clean up delivery modal when hidden
    document.getElementById('recordDeliveryModal')?.addEventListener('hidden.bs.modal', function() {
        if (deliveryModal) {
            deliveryModal.dispose();
            deliveryModal = null;
        }
        currentDeliveryId = null;
        currentDeliveryFlock = null;
        // Ensure backdrop is removed
        const backdrops = document.querySelectorAll('.modal-backdrop');
        backdrops.forEach(backdrop => backdrop.remove());
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
    });
    
    document.getElementById('submitDeliveryBtn')?.addEventListener('click', function() {
        const form = document.getElementById('recordDeliveryForm');
        const formData = new FormData(form);
        const submitBtn = this;
        const originalText = submitBtn.innerHTML;
        
        const offspringCount = form.querySelector('input[name="offspring_count"]').value;
        if (!offspringCount || offspringCount < 0) {
            Swal.fire({ icon: 'error', title: 'Validation Error', text: 'Please enter a valid offspring count' });
            return;
        }
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
        
        fetch(`/breeding-records/${currentDeliveryId}/record-delivery-ajax`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (deliveryModal) {
                    deliveryModal.hide();
                }
                Swal.fire({ icon: 'success', title: 'Success!', text: 'Delivery recorded successfully', timer: 1500, showConfirmButton: false })
                    .then(() => window.location.reload());
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Failed to record delivery' });
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({ icon: 'error', title: 'Error', text: 'An error occurred while saving' });
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        });
    });
    
    // ==================== DELETE BREEDING RECORD ====================
    document.querySelectorAll('.delete-record-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const info = this.dataset.info;
            
            Swal.fire({
                title: 'Delete Breeding Record',
                text: `Are you sure you want to delete the breeding record for "${info}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.getElementById('deleteBreedingForm');
                    form.action = `/breeding-records/${id}`;
                    form.submit();
                }
            });
        });
    });
    
    // Global function to handle any lingering backdrops
    function cleanupBackdrops() {
        const backdrops = document.querySelectorAll('.modal-backdrop');
        backdrops.forEach(backdrop => backdrop.remove());
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
    }
    
    // Run cleanup on page load and on any navigation
    cleanupBackdrops();
    
    // Also cleanup when page is shown (for back/forward navigation)
    window.addEventListener('pageshow', cleanupBackdrops);
    
    function escapeHtml(str) {
        if (!str) return '';
        return String(str).replace(/[&<>]/g, function(m) {
            if (m === '&') return '&amp;';
            if (m === '<') return '&lt;';
            if (m === '>') return '&gt;';
            return m;
        });
    }
});
window.openCreateBreedingModal = openCreateBreedingModal;
</script>
@endpush
@endsection