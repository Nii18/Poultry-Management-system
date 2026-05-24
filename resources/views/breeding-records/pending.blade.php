{{-- resources/views/breeding-records/pending.blade.php --}}
@extends('layouts.master')

@section('title', 'Pending Deliveries')

@section('content')
<div class="container-fluid px-4">
    <!-- Modern Header -->
    <div class="report-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <div class="d-flex align-items-center gap-3">
                    <div class="header-icon bg-primary">
                        <i class="fas fa-baby-carriage"></i>
                    </div>
                    <div>
                        <h1 class="header-title mb-1">Pending Deliveries</h1>
                        <p class="header-subtitle text-muted mb-0">Upcoming births and deliveries requiring attention</p>
                    </div>
                </div>
            </div>
            <div class="col-auto">
                <div class="action-buttons">
                    <button type="button" class="btn btn-primary" id="newBreedingRecordBtn">
                        <i class="fas fa-plus me-2"></i>New Breeding Record
                    </button>
                    <a href="{{ route('breeding-records.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Records
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="stats-grid mb-4">
        <div class="stat-card">
            <div class="stat-icon bg-primary">
                <i class="fas fa-calendar-week"></i>
            </div>
            <div class="stat-details">
                <span class="stat-label">Pending Deliveries</span>
                <h2 class="stat-value">{{ $pendingBreedings->count() }}</h2>
                <span class="stat-trend">Upcoming births</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon bg-warning">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-details">
                <span class="stat-label">Next 7 Days</span>
                <h2 class="stat-value">{{ $pendingBreedings->filter(function($record) { return now()->diffInDays($record->expected_delivery_date, false) <= 7; })->count() }}</h2>
                <span class="stat-trend">Critical window</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon bg-info">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div class="stat-details">
                <span class="stat-label">Next 14 Days</span>
                <h2 class="stat-value">{{ $pendingBreedings->filter(function($record) { $days = now()->diffInDays($record->expected_delivery_date, false); return $days > 7 && $days <= 14; })->count() }}</h2>
                <span class="stat-trend">Upcoming</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon bg-success">
                <i class="fas fa-heartbeat"></i>
            </div>
            <div class="stat-details">
                <span class="stat-label">Average Gestation</span>
                <h2 class="stat-value">{{ number_format($pendingBreedings->avg(function($record) { return $record->breeding_date->diffInDays($record->expected_delivery_date); }), 0) }}</h2>
                <span class="stat-trend">Days per pregnancy</span>
            </div>
        </div>
    </div>

    <!-- Pending Deliveries Section -->
    @if($pendingBreedings->count() > 0)
        <div class="alert-section">
            <div class="section-header">
                <div class="d-flex align-items-center gap-2">
                    <div class="section-icon bg-primary-soft">
                        <i class="fas fa-list text-primary"></i>
                    </div>
                    <h5 class="section-title mb-0">Upcoming Deliveries</h5>
                    <span class="badge bg-primary ms-2">{{ $pendingBreedings->count() }} Pending</span>
                </div>
                <p class="section-description text-muted mb-0">Breeding records requiring delivery recording</p>
            </div>

            <div class="alert alert-info mb-3">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Reminder:</strong> Record deliveries as soon as they occur to maintain accurate breeding records.
                The system will track offspring counts and success rates automatically.
            </div>

            <div class="row g-4">
                @foreach($pendingBreedings as $record)
                    @php
                        $daysLeft = now()->diffInDays($record->expected_delivery_date, false);
                        $isUrgent = $daysLeft <= 7;
                        $isImminent = $daysLeft <= 3;
                        $progressPercentage = max(0, min(100, (($record->breeding_date->diffInDays(now()) / max(1, $record->breeding_date->diffInDays($record->expected_delivery_date))) * 100)));
                    @endphp
                    <div class="col-xl-6 col-lg-6">
                        <div class="delivery-card {{ $isUrgent ? 'urgent' : ($isImminent ? 'imminent' : '') }}">
                            <div class="delivery-card-header">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="expected-date-badge">
                                            <i class="fas fa-calendar-check me-1"></i>
                                            Expected: {{ $record->expected_delivery_date->format('d M Y') }}
                                        </div>
                                        <h5 class="female-flock mt-2 mb-0">
                                            <button type="button" class="btn btn-link p-0 text-primary fw-semibold text-decoration-none view-record-btn" 
                                                    data-id="{{ $record->id }}" style="font-size: 1.1rem;">
                                                <i class="fas fa-female me-1"></i>{{ $record->female->flock_number ?? 'N/A' }}
                                            </button>
                                        </h5>
                                        <div class="breeding-details">
                                            <span class="breed-variety">{{ $record->female->breed_variety ?? 'N/A' }}</span>
                                            @if($record->male)
                                                <span class="male-info">
                                                    <i class="fas fa-male me-1"></i> × {{ $record->male->flock_number }}
                                                </span>
                                            @else
                                                <span class="male-info">
                                                    <i class="fas fa-syringe me-1"></i> Artificial Insemination
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="days-badge {{ $isUrgent ? 'urgent' : ($isImminent ? 'imminent' : 'normal') }}">
                                        @if($daysLeft <= 0)
                                            <span class="badge bg-danger">Overdue!</span>
                                        @elseif($daysLeft <= 3)
                                            <span class="badge bg-danger">{{ $daysLeft }} days left</span>
                                        @elseif($daysLeft <= 7)
                                            <span class="badge bg-warning">{{ $daysLeft }} days left</span>
                                        @else
                                            <span class="badge bg-info">{{ $daysLeft }} days left</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="delivery-card-body">
                                <div class="progress-section">
                                    <div class="progress-label">
                                        <span>Gestation Progress</span>
                                        <strong>{{ number_format($progressPercentage, 1) }}%</strong>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar {{ $isUrgent ? 'bg-danger' : ($isImminent ? 'bg-warning' : 'bg-success') }}" 
                                             style="width: {{ $progressPercentage }}%">
                                        </div>
                                    </div>
                                </div>
                                <div class="info-grid mt-3">
                                    <div class="info-item">
                                        <span class="info-label">Breeding Date</span>
                                        <strong class="info-value">{{ $record->breeding_date->format('d M Y') }}</strong>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Breeding Method</span>
                                        <strong class="info-value">{{ ucfirst(str_replace('_', ' ', $record->breeding_method)) }}</strong>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Gestation Period</span>
                                        <strong class="info-value">{{ $record->breeding_date->diffInDays($record->expected_delivery_date) }} days</strong>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Recorded By</span>
                                        <strong class="info-value">{{ $record->recorder->name ?? 'N/A' }}</strong>
                                    </div>
                                </div>
                                @if($record->notes)
                                <div class="delivery-notes mt-3">
                                    <i class="fas fa-sticky-note me-1 text-muted"></i>
                                    {{ Str::limit($record->notes, 80) }}
                                </div>
                                @endif
                            </div>
                            <div class="delivery-card-footer">
                                <div class="btn-group w-100">
                                    <button type="button" class="btn btn-success btn-sm record-delivery-btn" 
                                            data-id="{{ $record->id }}" data-flock="{{ $record->female->flock_number ?? 'N/A' }}">
                                        <i class="fas fa-baby me-1"></i>Record Delivery
                                    </button>
                                    <button type="button" class="btn btn-outline-primary btn-sm view-record-btn" 
                                            data-id="{{ $record->id }}">
                                        <i class="fas fa-eye me-1"></i>View Details
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <div class="empty-state">
            <div class="empty-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h4>No Pending Deliveries</h4>
            <p>There are no pending deliveries at this time.</p>
            <p class="text-muted">All breeding records have been completed or are up to date.</p>
            <button type="button" class="btn btn-primary" id="emptyStateNewBtn">
                <i class="fas fa-plus me-2"></i>New Breeding Record
            </button>
        </div>
    @endif

    <!-- Preparation Tips Card -->
    @if($pendingBreedings->count() > 0)
    <div class="tips-card mt-4">
        <div class="tips-icon">
            <i class="fas fa-lightbulb"></i>
        </div>
        <div class="tips-content">
            <h6 class="mb-1">Preparation Tips</h6>
            <ul class="mb-0 ps-3">
                <li>Prepare a clean, comfortable birthing area at least one week before expected delivery</li>
                <li>Monitor the female closely for signs of labor (restlessness, nesting behavior, loss of appetite)</li>
                <li>Have necessary supplies ready: clean towels, gloves, disinfectant, and emergency contact numbers</li>
                <li>Record all relevant details immediately after delivery for accurate record keeping</li>
                <li>Ensure proper nutrition and care for the mother post-delivery</li>
            </ul>
        </div>
    </div>
    @endif
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

@push('styles')
<style>
    /* Header Styles */
    .report-header { margin-bottom: 1.5rem; }
    .header-icon { width: 55px; height: 55px; background: linear-gradient(135deg, #0d6e4f 0%, #0a5a40 100%); border-radius: 14px; display: flex; align-items: center; justify-content: center; }
    .header-icon i { font-size: 26px; color: white; }
    .header-title { font-size: 26px; font-weight: 700; color: #1e293b; margin: 0; }
    .header-subtitle { font-size: 14px; margin: 0; }
    .action-buttons { display: flex; gap: 0.75rem; }
    
    /* Stats Grid */
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.25rem; margin-bottom: 1.5rem; }
    .stat-card { background: white; border-radius: 16px; padding: 1rem; display: flex; align-items: center; gap: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; transition: all 0.3s ease; }
    .stat-card:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0,0,0,0.08); }
    .stat-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; }
    .stat-icon i { font-size: 22px; color: white; }
    .bg-primary { background: linear-gradient(135deg, #0d6e4f, #0a5a40); }
    .bg-warning { background: linear-gradient(135deg, #f59e0b, #d97706); }
    .bg-info { background: linear-gradient(135deg, #3b82f6, #2563eb); }
    .bg-success { background: linear-gradient(135deg, #10b981, #059669); }
    .stat-details { flex: 1; }
    .stat-label { font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; font-weight: 600; }
    .stat-value { font-size: 28px; font-weight: 700; margin: 0.15rem 0; line-height: 1.2; }
    .stat-trend { font-size: 11px; color: #64748b; }
    
    /* Section Header */
    .section-header { margin-bottom: 1rem; }
    .section-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; }
    .bg-primary-soft { background: #e0f2fe; }
    .section-title { font-size: 18px; font-weight: 600; color: #1e293b; margin: 0; }
    .section-description { font-size: 13px; margin-top: 0.25rem; }
    
    /* Delivery Cards */
    .delivery-card { background: white; border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden; transition: all 0.3s ease; height: 100%; }
    .delivery-card:hover { transform: translateY(-3px); box-shadow: 0 12px 30px rgba(0,0,0,0.1); }
    .delivery-card.urgent { border-left: 4px solid #f59e0b; }
    .delivery-card.imminent { border-left: 4px solid #dc2626; }
    .delivery-card-header { padding: 1rem 1.25rem; background: #f8fafc; border-bottom: 1px solid #e2e8f0; }
    .expected-date-badge { font-size: 12px; color: #64748b; }
    .female-flock { margin: 0.5rem 0 0.25rem 0; }
    .flock-link { color: #1e293b; text-decoration: none; font-weight: 700; font-size: 18px; transition: color 0.2s; }
    .flock-link:hover { color: #0d6e4f; }
    .breeding-details { display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap; margin-top: 0.25rem; }
    .breed-variety { font-size: 12px; color: #64748b; }
    .male-info { font-size: 12px; color: #0d6e4f; background: #e0f2fe; padding: 0.2rem 0.6rem; border-radius: 12px; }
    
    .days-badge { align-self: flex-start; }
    .days-badge.urgent .badge { background-color: #f59e0b !important; }
    .days-badge.imminent .badge { background-color: #dc2626 !important; }
    .days-badge.normal .badge { background-color: #0d6e4f !important; }
    
    .delivery-card-body { padding: 1rem 1.25rem; }
    .progress-section { margin-bottom: 1rem; }
    .progress-label { display: flex; justify-content: space-between; font-size: 11px; margin-bottom: 0.5rem; color: #64748b; }
    .progress { height: 8px; background: #e2e8f0; border-radius: 10px; overflow: hidden; }
    .progress-bar { border-radius: 10px; transition: width 0.5s ease; }
    
    .info-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.75rem; }
    .info-item { display: flex; flex-direction: column; gap: 0.25rem; }
    .info-label { font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; font-weight: 600; }
    .info-value { font-size: 13px; color: #1e293b; }
    
    .delivery-notes { margin-top: 0.75rem; padding-top: 0.75rem; border-top: 1px solid #e2e8f0; font-size: 12px; color: #64748b; }
    .delivery-card-footer { padding: 0.75rem 1.25rem; background: #f8fafc; border-top: 1px solid #e2e8f0; }
    .btn-group { display: flex; gap: 0.5rem; }
    .btn-group .btn { flex: 1; border-radius: 8px; font-size: 12px; padding: 0.4rem 0.5rem; }
    
    /* Empty State */
    .empty-state { text-align: center; padding: 3rem; background: white; border-radius: 16px; border: 1px solid #e2e8f0; }
    .empty-icon { width: 70px; height: 70px; background: #d1fae5; border-radius: 35px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; }
    .empty-icon i { font-size: 32px; color: #10b981; }
    .empty-state h4 { font-size: 18px; font-weight: 600; color: #1e293b; margin-bottom: 0.5rem; }
    .empty-state p { color: #64748b; margin-bottom: 1rem; }
    
    /* Tips Card */
    .tips-card { background: #fefce8; border-radius: 12px; padding: 1rem; display: flex; align-items: flex-start; gap: 1rem; border: 1px solid #fde68a; }
    .tips-icon { width: 40px; height: 40px; background: #f59e0b; border-radius: 10px; display: flex; align-items: center; justify-content: center; }
    .tips-icon i { font-size: 20px; color: white; }
    .tips-content h6 { color: #92400e; }
    .tips-content ul { font-size: 13px; color: #78350f; }
    .tips-content li { margin-bottom: 0.25rem; }
    
    /* Modal Styles */
    .modal-header.bg-success { background: linear-gradient(135deg, #0d6e4f, #0a5a40); }
    
    /* Detail Styles for Modal */
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
    
    .stats-card {
        background: #f8fafc;
        border-radius: 12px;
        padding: 1rem;
        border: 1px solid #e2e8f0;
        text-align: center;
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
    
    /* Responsive */
    @media (max-width: 768px) {
        .stats-grid { grid-template-columns: repeat(2, 1fr); gap: 1rem; }
        .stat-value { font-size: 22px; }
        .header-title { font-size: 22px; }
        .info-grid { grid-template-columns: 1fr; gap: 0.5rem; }
        .breeding-details { flex-direction: column; align-items: flex-start; }
        .tips-card { flex-direction: column; }
        .btn-group { flex-direction: column; }
        .detail-grid { grid-template-columns: 1fr; }
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // Helper function to properly cleanup and close modals
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
    
    // ==================== CREATE BREEDING RECORD MODAL ====================
    let createModal = null;
    
    function openCreateBreedingModal() {
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
        
        fetch('/breeding-records/create-form', {
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
    
    document.getElementById('createBreedingModal')?.addEventListener('hidden.bs.modal', function() {
        if (createModal) {
            createModal.dispose();
            createModal = null;
        }
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
    
    document.getElementById('saveCreateBreeding')?.addEventListener('click', function() {
        const form = document.getElementById('createBreedingForm');
        if (!form) return;
        
        const formData = new FormData(form);
        const data = {};
        formData.forEach((value, key) => { data[key] = value; });
        
        if (!data.flock_id || !data.breeding_date || !data.expected_delivery_date || !data.breeding_method) {
            Swal.fire({ icon: 'error', title: 'Validation Error', text: 'Please fill in all required fields' });
            return;
        }
        
        const saveBtn = this;
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
        
        fetch('/breeding-records/store-ajax', {
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
                if (createModal) createModal.hide();
                Swal.fire({ icon: 'success', title: 'Created!', text: 'Breeding record created successfully', timer: 1500, showConfirmButton: false })
                    .then(() => window.location.reload());
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Failed to create record' });
                saveBtn.disabled = false;
                saveBtn.innerHTML = 'Create Record';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({ icon: 'error', title: 'Error', text: 'An error occurred while saving' });
            saveBtn.disabled = false;
            saveBtn.innerHTML = 'Create Record';
        });
    });
    
    document.getElementById('newBreedingRecordBtn')?.addEventListener('click', openCreateBreedingModal);
    document.getElementById('emptyStateNewBtn')?.addEventListener('click', openCreateBreedingModal);
    
    // ==================== VIEW BREEDING RECORD MODAL ====================
let viewModal = null;

document.querySelectorAll('.view-record-btn').forEach(btn => {
    btn.addEventListener('click', function () {
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

        // ✅ FIXED ROUTE (matches Laravel: /breeding-records/{id}/details-json)
        const url = `/breeding-records/${id}/details-json`;

        fetch(url, {
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayBreedingDetails(data.record);
            } else {
                modalBody.innerHTML = `
                    <div class="alert alert-danger m-3">
                        Failed to load details: ${data.message}
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            modalBody.innerHTML = `
                <div class="alert alert-danger m-3">
                    Error loading data: ${error.message}
                </div>
            `;
        });
    });
});

document.getElementById('viewBreedingModal')?.addEventListener('hidden.bs.modal', function () {
    if (viewModal) {
        viewModal.dispose();
        viewModal = null;
    }

    document.querySelectorAll('.modal-backdrop').forEach(backdrop => backdrop.remove());
    document.body.classList.remove('modal-open');
    document.body.style.overflow = '';
    document.body.style.paddingRight = '';
});
    
    function displayBreedingDetails(record) {
        const statusClass = record.is_successful ? 'success' : (record.actual_delivery_date ? 'danger' : 'warning');
        const statusText = record.is_successful ? 'Successful' : (record.actual_delivery_date ? 'Failed' : 'Pending');
        
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
                    <div class="stats-card">
                        <div class="stats-icon bg-primary-soft">
                            <i class="fas fa-chart-line text-primary"></i>
                        </div>
                        <div class="stats-number">${record.conception_rate || 0}%</div>
                        <div class="stats-label">Conception Rate</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stats-card">
                        <div class="stats-icon bg-success-soft">
                            <i class="fas fa-heartbeat text-success"></i>
                        </div>
                        <div class="stats-number">${record.live_birth_rate || 0}%</div>
                        <div class="stats-label">Live Birth Rate</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stats-card">
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
    
    document.getElementById('recordDeliveryModal')?.addEventListener('hidden.bs.modal', function() {
        if (deliveryModal) {
            deliveryModal.dispose();
            deliveryModal = null;
        }
        currentDeliveryId = null;
        currentDeliveryFlock = null;
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
        
        fetch(`/breeding-records/ajax/${currentDeliveryId}/record-delivery`, {
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
                if (deliveryModal) deliveryModal.hide();
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
</script>
@endpush

@endsection