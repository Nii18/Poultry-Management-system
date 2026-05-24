{{-- resources/views/treatments/withdrawal-alerts.blade.php --}}
@extends('layouts.master')

@section('title', 'Withdrawal Alerts')

@section('content')
<div class="container-fluid px-4">
    <!-- Modern Header -->
    <div class="report-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <div class="d-flex align-items-center gap-3">
                    <div class="header-icon bg-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div>
                        <h1 class="header-title mb-1">Withdrawal Alerts</h1>
                        <p class="header-subtitle text-muted mb-0">Monitor medication withdrawal periods for market-ready animals</p>
                    </div>
                </div>
            </div>
            <div class="col-auto">
                <div class="action-buttons">
                    <a href="{{ route('treatments.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Treatments
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
                <span class="stat-label">Critical Alerts</span>
                <h2 class="stat-value text-danger">{{ $expiringWithdrawals->count() }}</h2>
                <span class="stat-trend">Expiring in 0-3 days</span>
            </div>
        </div>
        <div class="stat-card warning">
            <div class="stat-icon bg-warning">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-details">
                <span class="stat-label">Active Withdrawals</span>
                <h2 class="stat-value text-warning">{{ $activeWithdrawals->count() }}</h2>
                <span class="stat-trend">Expiring in 4+ days</span>
            </div>
        </div>
        <div class="stat-card total">
            <div class="stat-icon bg-total">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stat-details">
                <span class="stat-label">Total Active</span>
                <h2 class="stat-value">{{ $expiringWithdrawals->count() + $activeWithdrawals->count() }}</h2>
                <span class="stat-trend">Active withdrawal records</span>
            </div>
        </div>
        <div class="stat-card info">
            <div class="stat-icon bg-info">
                <i class="fas fa-calendar-week"></i>
            </div>
            <div class="stat-details">
                <span class="stat-label">Affected Flocks</span>
                <h2 class="stat-value">
                    {{ $expiringWithdrawals->pluck('flock_id')->merge($activeWithdrawals->pluck('flock_id'))->unique()->count() }}
                </h2>
                <span class="stat-trend">Flocks under withdrawal</span>
            </div>
        </div>
    </div>

    <!-- Critical Alerts Section -->
    <div class="alert-section critical-section mb-4">
        <div class="section-header">
            <div class="d-flex align-items-center gap-2">
                <div class="section-icon bg-danger-soft">
                    <i class="fas fa-exclamation-triangle text-danger"></i>
                </div>
                <h5 class="section-title mb-0">Critical Alerts</h5>
                <span class="badge bg-danger ms-2">{{ $expiringWithdrawals->count() }} Expiring Soon</span>
            </div>
            <p class="section-description text-muted mb-0">Withdrawal periods ending within the next 3 days</p>
        </div>

        @if($expiringWithdrawals->count() > 0)
            <div class="alert alert-warning mb-3">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Important:</strong> These medications are nearing their withdrawal period end. 
                Animals should NOT be sold or slaughtered until the withdrawal period is complete.
            </div>
            
            <div class="row g-4">
                @foreach($expiringWithdrawals as $treatment)
                <div class="col-xl-6 col-lg-6">
                    <div class="alert-card critical-alert">
                        <div class="alert-card-header">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="flock-info">
                                        <button type="button" class="flock-link-btn" onclick="viewFlock({{ $treatment->flock_id }})" style="background: none; border: none; padding: 0;">
                                            <i class="fas fa-tractor me-1"></i>
                                            <span class="flock-link">{{ $treatment->flock->flock_number ?? 'N/A' }}</span>
                                        </button>
                                        <span class="species-badge">{{ $treatment->flock->species->name ?? 'N/A' }}</span>
                                    </div>
                                    <div class="treatment-details mt-2">
                                        <div class="product-name">
                                            <i class="fas fa-capsules me-1 text-primary"></i>
                                            <strong>{{ $treatment->product_name }}</strong>
                                        </div>
                                        <div class="diagnosis">
                                            <i class="fas fa-stethoscope me-1 text-danger"></i>
                                            {{ $treatment->diagnosis }}
                                        </div>
                                    </div>
                                </div>
                                <div class="days-badge critical">
                                    @php
                                        $daysLeft = now()->diffInDays($treatment->withdrawal_end_date, false);
                                    @endphp
                                    @if($daysLeft <= 1)
                                        <span class="badge bg-danger">LAST DAY!</span>
                                    @else
                                        <span class="badge bg-warning">{{ $daysLeft }} days left</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="alert-card-body">
                            <div class="info-grid">
                                <div class="info-item">
                                    <span class="info-label">Treatment Date</span>
                                    <strong class="info-value">{{ $treatment->start_date->format('d M Y') }}</strong>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Withdrawal Ends</span>
                                    <strong class="info-value text-danger">{{ $treatment->withdrawal_end_date->format('d M Y') }}</strong>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Administration Route</span>
                                    <strong class="info-value">{{ ucfirst($treatment->administration_route) }}</strong>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Dosage</span>
                                    <strong class="info-value">{{ $treatment->dosage }}</strong>
                                </div>
                            </div>
                            @if($treatment->notes)
                            <div class="alert-notes mt-2">
                                <i class="fas fa-sticky-note me-1 text-muted"></i>
                                {{ Str::limit($treatment->notes, 80) }}
                            </div>
                            @endif
                        </div>
                        <div class="alert-card-footer">
                            <div class="btn-group w-100">
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="viewTreatment({{ $treatment->id }})">
                                    <i class="fas fa-eye me-1"></i>View Treatment
                                </button>
                                <button type="button" class="btn btn-outline-info btn-sm" onclick="viewFlock({{ $treatment->flock_id }})">
                                    <i class="fas fa-paw me-1"></i>View Flock
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <div class="empty-state-small">
                <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                <p class="mb-0 text-success">No critical alerts! No withdrawal periods expiring in the next 3 days.</p>
            </div>
        @endif
    </div>

    <!-- Active Withdrawals Section -->
    <div class="alert-section active-section">
        <div class="section-header">
            <div class="d-flex align-items-center gap-2">
                <div class="section-icon bg-warning-soft">
                    <i class="fas fa-hourglass-half text-warning"></i>
                </div>
                <h5 class="section-title mb-0">Active Withdrawals</h5>
                <span class="badge bg-warning ms-2">{{ $activeWithdrawals->count() }} Active</span>
            </div>
            <p class="section-description text-muted mb-0">Withdrawal periods ending in 4+ days</p>
        </div>

        @if($activeWithdrawals->count() > 0)
            <div class="row g-4">
                @foreach($activeWithdrawals as $treatment)
                <div class="col-xl-6 col-lg-6">
                    <div class="alert-card active-alert">
                        <div class="alert-card-header">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="flock-info">
                                        <button type="button" class="flock-link-btn" onclick="viewFlock({{ $treatment->flock_id }})" style="background: none; border: none; padding: 0;">
                                            <i class="fas fa-tractor me-1"></i>
                                            <span class="flock-link">{{ $treatment->flock->flock_number ?? 'N/A' }}</span>
                                        </button>
                                        <span class="species-badge">{{ $treatment->flock->species->name ?? 'N/A' }}</span>
                                    </div>
                                    <div class="treatment-details mt-2">
                                        <div class="product-name">
                                            <i class="fas fa-capsules me-1 text-primary"></i>
                                            <strong>{{ $treatment->product_name }}</strong>
                                        </div>
                                        <div class="diagnosis">
                                            <i class="fas fa-stethoscope me-1 text-danger"></i>
                                            {{ $treatment->diagnosis }}
                                        </div>
                                    </div>
                                </div>
                                <div class="days-badge active">
                                    <span class="badge bg-secondary">
                                        {{ now()->diffInDays($treatment->withdrawal_end_date, false) }} days left
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="alert-card-body">
                            <div class="info-grid">
                                <div class="info-item">
                                    <span class="info-label">Treatment Date</span>
                                    <strong class="info-value">{{ $treatment->start_date->format('d M Y') }}</strong>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Withdrawal Ends</span>
                                    <strong class="info-value">{{ $treatment->withdrawal_end_date->format('d M Y') }}</strong>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Administration Route</span>
                                    <strong class="info-value">{{ ucfirst($treatment->administration_route) }}</strong>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Dosage</span>
                                    <strong class="info-value">{{ $treatment->dosage }}</strong>
                                </div>
                            </div>
                            @if($treatment->notes)
                            <div class="alert-notes mt-2">
                                <i class="fas fa-sticky-note me-1 text-muted"></i>
                                {{ Str::limit($treatment->notes, 80) }}
                            </div>
                            @endif
                        </div>
                        <div class="alert-card-footer">
                            <div class="btn-group w-100">
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="viewTreatment({{ $treatment->id }})">
                                    <i class="fas fa-eye me-1"></i>View Treatment
                                </button>
                                <button type="button" class="btn btn-outline-info btn-sm" onclick="viewFlock({{ $treatment->flock_id }})">
                                    <i class="fas fa-paw me-1"></i>View Flock
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <div class="empty-state-small">
                <i class="fas fa-info-circle fa-2x text-info mb-2"></i>
                <p class="mb-0 text-info">No active withdrawal periods at this time.</p>
            </div>
        @endif
    </div>

    <!-- Educational Info -->
    <div class="info-card mt-4">
        <div class="info-icon">
            <i class="fas fa-graduation-cap"></i>
        </div>
        <div class="info-content">
            <h6 class="mb-1">What is a withdrawal period?</h6>
            <p class="mb-0">The withdrawal period is the time required after medication administration before animals can be safely sold for meat/milk/eggs. Marketing animals before the withdrawal period ends is illegal and poses health risks to consumers.</p>
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

<!-- View Flock Modal -->
<div class="modal fade" id="viewFlockModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title text-white"><i class="fas fa-paw me-2"></i>Flock Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewFlockContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-success" role="status"></div>
                    <p class="mt-2">Loading flock details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // View Treatment Modal
    function viewTreatment(id) {
        const modal = new bootstrap.Modal(document.getElementById('viewTreatmentModal'));
        const modalBody = document.getElementById('viewTreatmentContent');
        
        modalBody.innerHTML = `<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Loading treatment details...</p></div>`;
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
    
    // View Flock Modal
    function viewFlock(id) {
        const modal = new bootstrap.Modal(document.getElementById('viewFlockModal'));
        const modalBody = document.getElementById('viewFlockContent');
        
        modalBody.innerHTML = `<div class="text-center py-4"><div class="spinner-border text-success" role="status"></div><p class="mt-2">Loading flock details...</p></div>`;
        modal.show();
        
        fetch(`/flocks/${id}/details`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayFlockDetails(data.flock, data.summary);
                } else {
                    modalBody.innerHTML = `<div class="alert alert-danger">Failed to load flock details.</div>`;
                }
            })
            .catch(error => {
                modalBody.innerHTML = `<div class="alert alert-danger">Error loading data: ${error.message}</div>`;
            });
    }
    
    function displayFlockDetails(flock, summary) {
        const statusClass = {
            'active': 'badge bg-success',
            'closed': 'badge bg-secondary',
            'quarantined': 'badge bg-danger',
            'breeding': 'badge bg-info'
        };
        
        document.getElementById('viewFlockContent').innerHTML = `
            <div class="detail-section">
                <h6>Basic Information</h6>
                <div class="detail-grid">
                    <div class="detail-item"><span class="detail-label">Flock Number</span><span class="detail-value">${escapeHtml(flock.flock_number)}</span></div>
                    <div class="detail-item"><span class="detail-label">Species</span><span class="detail-value">${escapeHtml(flock.species_name)} (${escapeHtml(flock.species_code)})</span></div>
                    <div class="detail-item"><span class="detail-label">House</span><span class="detail-value">${escapeHtml(flock.house_name)} (${escapeHtml(flock.house_code)})</span></div>
                    <div class="detail-item"><span class="detail-label">Breed/Variety</span><span class="detail-value">${escapeHtml(flock.breed_variety)}</span></div>
                    <div class="detail-item"><span class="detail-label">Start Date</span><span class="detail-value">${flock.start_date}</span></div>
                    <div class="detail-item"><span class="detail-label">Source</span><span class="detail-value">${escapeHtml(flock.source || 'N/A')}</span></div>
                </div>
            </div>
            <div class="detail-section">
                <h6>Performance Metrics</h6>
                <div class="detail-grid">
                    <div class="detail-item"><span class="detail-label">Age</span><span class="detail-value">${summary.age_days} days (${summary.age_weeks} weeks)</span></div>
                    <div class="detail-item"><span class="detail-label">Current Count</span><span class="detail-value">${summary.current_count.toLocaleString()} / ${flock.initial_count.toLocaleString()}</span></div>
                    <div class="detail-item"><span class="detail-label">Mortality Rate</span><span class="detail-value ${summary.mortality_rate > 5 ? 'text-danger' : 'text-success'}">${summary.mortality_rate}% (Survival: ${summary.survival_rate}%)</span></div>
                    <div class="detail-item"><span class="detail-label">Feed Conversion Ratio</span><span class="detail-value">${summary.fcr}</span></div>
                    <div class="detail-item"><span class="detail-label">Total Feed Consumed</span><span class="detail-value">${summary.total_feed.toLocaleString()} kg</span></div>
                    <div class="detail-item"><span class="detail-label">Average Daily Gain</span><span class="detail-value">${summary.avg_daily_gain} kg</span></div>
                </div>
            </div>
            <div class="detail-section">
                <h6>Production Information</h6>
                <div class="detail-grid">
                    <div class="detail-item"><span class="detail-label">Production Type</span><span class="detail-value">${flock.production_type.charAt(0).toUpperCase() + flock.production_type.slice(1)}</span></div>
                    <div class="detail-item"><span class="detail-label">Breeding Stock</span><span class="detail-value">${flock.is_breeding_stock ? 'Yes' : 'No'}</span></div>
                    <div class="detail-item"><span class="detail-label">Parity Number</span><span class="detail-value">${flock.parity_number || 'N/A'}</span></div>
                    <div class="detail-item"><span class="detail-label">Status</span><span class="${statusClass[flock.status]}">${flock.status.charAt(0).toUpperCase() + flock.status.slice(1)}</span></div>
                </div>
            </div>
            ${flock.notes ? `<div class="detail-section"><h6>Notes</h6><p class="mb-0">${escapeHtml(flock.notes)}</p></div>` : ''}
        `;
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
    /* Your existing styles remain the same */
    .report-header { margin-bottom: 1.5rem; }
    .header-icon { width: 55px; height: 55px; border-radius: 14px; display: flex; align-items: center; justify-content: center; }
    .header-icon.bg-danger { background: linear-gradient(135deg, #dc2626, #b91c1c); }
    .header-icon i { font-size: 26px; color: white; }
    .header-title { font-size: 26px; font-weight: 700; color: #1e293b; margin: 0; }
    .header-subtitle { font-size: 14px; margin: 0; }
    .action-buttons { display: flex; gap: 0.75rem; }
    
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem; margin-bottom: 1.5rem; }
    .stat-card { background: white; border-radius: 16px; padding: 1rem; display: flex; align-items: center; gap: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; transition: all 0.3s ease; }
    .stat-card:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0,0,0,0.08); }
    .stat-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; }
    .stat-icon i { font-size: 22px; color: white; }
    .bg-critical { background: linear-gradient(135deg, #dc2626, #b91c1c); }
    .bg-warning { background: linear-gradient(135deg, #f59e0b, #d97706); }
    .bg-total { background: linear-gradient(135deg, #0d6e4f, #0a5a40); }
    .bg-info { background: linear-gradient(135deg, #3b82f6, #2563eb); }
    .stat-details { flex: 1; }
    .stat-label { font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; font-weight: 600; }
    .stat-value { font-size: 28px; font-weight: 700; margin: 0.15rem 0; line-height: 1.2; }
    .stat-trend { font-size: 11px; color: #64748b; }
    
    .section-header { margin-bottom: 1rem; }
    .section-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; }
    .bg-danger-soft { background: #fee2e2; }
    .bg-warning-soft { background: #fef3c7; }
    .section-title { font-size: 18px; font-weight: 600; color: #1e293b; margin: 0; }
    .section-description { font-size: 13px; margin-top: 0.25rem; }
    
    .alert-card { background: white; border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden; transition: all 0.3s ease; height: 100%; }
    .alert-card:hover { transform: translateY(-3px); box-shadow: 0 12px 30px rgba(0,0,0,0.1); }
    .critical-alert { border-left: 4px solid #dc2626; }
    .active-alert { border-left: 4px solid #f59e0b; }
    .alert-card-header { padding: 1rem 1.25rem; background: #f8fafc; border-bottom: 1px solid #e2e8f0; }
    .flock-info { display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap; }
    .flock-link-btn { background: none; border: none; cursor: pointer; }
    .flock-link { color: #1e293b; text-decoration: none; font-weight: 700; font-size: 16px; transition: color 0.2s; }
    .flock-link-btn:hover .flock-link { color: #0d6e4f; text-decoration: underline; }
    .species-badge { font-size: 11px; background: #e2e8f0; padding: 0.2rem 0.6rem; border-radius: 12px; color: #64748b; }
    .treatment-details { display: flex; flex-direction: column; gap: 0.25rem; }
    .product-name { font-size: 14px; }
    .diagnosis { font-size: 13px; color: #64748b; }
    
    .days-badge.critical { align-self: flex-start; }
    .days-badge.active { align-self: flex-start; }
    
    .alert-card-body { padding: 1rem 1.25rem; }
    .info-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.75rem; }
    .info-item { display: flex; flex-direction: column; gap: 0.25rem; }
    .info-label { font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; font-weight: 600; }
    .info-value { font-size: 13px; color: #1e293b; }
    
    .alert-notes { margin-top: 0.75rem; padding-top: 0.75rem; border-top: 1px solid #e2e8f0; font-size: 12px; color: #64748b; }
    .alert-card-footer { padding: 0.75rem 1.25rem; background: #f8fafc; border-top: 1px solid #e2e8f0; }
    .btn-group { display: flex; gap: 0.5rem; }
    .btn-group .btn { flex: 1; border-radius: 8px; font-size: 12px; padding: 0.4rem 0.5rem; }
    
    .empty-state-small { text-align: center; padding: 2rem; background: #f8fafc; border-radius: 12px; margin-top: 1rem; }
    
    .info-card { background: #f0f9ff; border-radius: 12px; padding: 1rem; display: flex; align-items: flex-start; gap: 1rem; border: 1px solid #bae6fd; }
    .info-icon { width: 40px; height: 40px; background: #e0f2fe; border-radius: 10px; display: flex; align-items: center; justify-content: center; }
    .info-icon i { font-size: 20px; color: #0284c7; }
    .info-content h6 { color: #0369a1; }
    .info-content p { font-size: 13px; color: #475569; }
    
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
        .flock-info { flex-direction: column; align-items: flex-start; }
        .info-card { flex-direction: column; }
        .detail-grid { grid-template-columns: 1fr; }
    }
</style>
@endpush
@endsection