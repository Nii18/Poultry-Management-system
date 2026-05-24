{{-- resources/views/health-records/critical-alerts.blade.php --}}
@extends('layouts.master')

@section('title', 'Critical Health Alerts')

@section('content')
<div class="container-fluid px-4">
    <!-- Modern Header -->
    <div class="report-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <div class="d-flex align-items-center gap-3">
                    <div class="header-icon bg-critical">
                        <i class="fas fa-heartbeat"></i>
                    </div>
                    <div>
                        <h1 class="header-title mb-1">Critical Health Alerts</h1>
                        <p class="header-subtitle text-muted mb-0">Immediate attention required for these health issues</p>
                    </div>
                </div>
            </div>
            <div class="col-auto">
                <div class="action-buttons">
                    <a href="{{ route('health-records.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Health Records
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
                <h2 class="stat-value text-danger">{{ $criticalRecords->count() }}</h2>
                <span class="stat-trend">Last 7 days</span>
            </div>
        </div>
        <div class="stat-card flocks">
            <div class="stat-icon bg-flocks">
                <i class="fas fa-paw"></i>
            </div>
            <div class="stat-details">
                <span class="stat-label">Affected Flocks</span>
                <h2 class="stat-value">{{ $criticalRecords->pluck('flock_id')->unique()->count() }}</h2>
                <span class="stat-trend">Flocks with issues</span>
            </div>
        </div>
        <div class="stat-card conditions">
            <div class="stat-icon bg-conditions">
                <i class="fas fa-diagnoses"></i>
            </div>
            <div class="stat-details">
                <span class="stat-label">Unique Conditions</span>
                <h2 class="stat-value">{{ $criticalRecords->pluck('condition')->unique()->filter()->count() }}</h2>
                <span class="stat-trend">Different diagnoses</span>
            </div>
        </div>
        <div class="stat-card affected">
            <div class="stat-icon bg-affected">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stat-details">
                <span class="stat-label">Total Affected</span>
                <h2 class="stat-value">{{ number_format($criticalRecords->sum('affected_count')) }}</h2>
                <span class="stat-trend">Animals impacted</span>
            </div>
        </div>
    </div>

    <!-- Critical Alerts Section -->
    @if($criticalRecords->count() > 0)
        <div class="alert-section">
            <div class="section-header">
                <div class="d-flex align-items-center gap-2">
                    <div class="section-icon bg-critical-soft">
                        <i class="fas fa-exclamation-triangle text-danger"></i>
                    </div>
                    <h5 class="section-title mb-0">Critical Alerts</h5>
                    <span class="badge bg-danger ms-2">{{ $criticalRecords->count() }} Alerts</span>
                </div>
                <p class="section-description text-muted mb-0">Health records requiring immediate attention from the last 7 days</p>
            </div>

            <div class="alert alert-danger mb-3">
                <i class="fas fa-exclamation-circle me-2"></i>
                <strong>Urgent:</strong> These health issues require immediate veterinary attention. 
                Delaying treatment could result in serious health consequences or mortality.
            </div>

            <div class="row g-4">
                @foreach($criticalRecords as $record)
                <div class="col-xl-6 col-lg-6">
                    <div class="alert-card critical-alert">
                        <div class="alert-card-header">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="date-badge">
                                        <i class="fas fa-calendar-alt me-1"></i>
                                        {{ $record->record_date->format('d M Y') }}
                                        <span class="days-ago">{{ $record->record_date->diffForHumans() }}</span>
                                    </div>
                                    <div class="flock-info mt-2">
                                        <a href="{{ route('flocks.show', $record->flock_id) }}" class="flock-link">
                                            <i class="fas fa-tractor me-1"></i>{{ $record->flock->flock_number ?? 'N/A' }}
                                        </a>
                                        <span class="species-badge">{{ $record->flock->species->name ?? 'N/A' }}</span>
                                    </div>
                                    <div class="record-type mt-2">
                                        <span class="type-badge {{ $record->record_type }}">
                                            <i class="fas {{ 
                                                $record->record_type === 'checkup' ? 'fa-stethoscope' : 
                                                ($record->record_type === 'symptom' ? 'fa-head-side-medical' : 
                                                ($record->record_type === 'lab_result' ? 'fa-flask' : 
                                                ($record->record_type === 'post_mortem' ? 'fa-microscope' : 'fa-comments'))) 
                                            }} me-1"></i>
                                            {{ ucfirst(str_replace('_', ' ', $record->record_type)) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="severity-badge critical">
                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                    {{ ucfirst($record->severity) }}
                                </div>
                            </div>
                        </div>
                        <div class="alert-card-body">
                            <div class="condition-box">
                                <div class="condition-title">
                                    <i class="fas fa-diagnoses me-2 text-danger"></i>
                                    <strong>Condition / Diagnosis</strong>
                                </div>
                                <div class="condition-value">{{ $record->condition ?? 'Not specified' }}</div>
                            </div>
                            <div class="info-grid mt-3">
                                <div class="info-item">
                                    <span class="info-label">Affected Animals</span>
                                    <div class="info-value">
                                        @if($record->affected_count)
                                            <span class="affected-number">{{ number_format($record->affected_count) }}</span>
                                            <span class="affected-percentage">({{ $record->affected_percentage }}% of flock)</span>
                                        @else
                                            N/A
                                        @endif
                                    </div>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Recorded By</span>
                                    <strong class="info-value">{{ $record->recorder->name ?? 'N/A' }}</strong>
                                </div>
                            </div>
                            @if($record->symptoms && count($record->symptoms) > 0)
                            <div class="symptoms-box mt-3">
                                <div class="symptoms-title">
                                    <i class="fas fa-head-side-medical me-2 text-warning"></i>
                                    <strong>Observed Symptoms</strong>
                                </div>
                                <div class="symptoms-list">
                                    @foreach($record->symptoms as $symptom => $value)
                                        <span class="symptom-tag">
                                            {{ ucwords(str_replace('_', ' ', $symptom)) }}: 
                                            <strong>{{ is_bool($value) ? ($value ? 'Yes' : 'No') : $value }}</strong>
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                            @if($record->veterinarian_notes)
                            <div class="vet-notes mt-3">
                                <i class="fas fa-sticky-note me-1 text-info"></i>
                                <strong>Vet Notes:</strong> {{ Str::limit($record->veterinarian_notes, 100) }}
                            </div>
                            @endif
                        </div>
                        <div class="alert-card-footer">
                            <div class="btn-group w-100">
                                <a href="{{ route('health-records.show', $record->id) }}" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-eye me-1"></i>View Details
                                </a>
                                <a href="{{ route('treatments.create', ['flock_id' => $record->flock_id]) }}" class="btn btn-outline-danger btn-sm">
                                    <i class="fas fa-stethoscope me-1"></i>Create Treatment
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Pagination if needed -->
            @if(isset($criticalRecords) && method_exists($criticalRecords, 'hasPages') && $criticalRecords->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $criticalRecords->withQueryString()->links() }}
            </div>
            @endif
        </div>
    @else
        <div class="empty-state">
            <div class="empty-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h4>No Critical Alerts</h4>
            <p>No critical health alerts have been recorded in the last 7 days.</p>
            <p class="text-muted">All flocks are currently in good health.</p>
            <a href="{{ route('health-records.index') }}" class="btn btn-primary">
                <i class="fas fa-arrow-left me-2"></i>View All Health Records
            </a>
        </div>
    @endif

    <!-- Recommendation Card -->
    <div class="recommendation-card mt-4">
        <div class="recommendation-icon">
            <i class="fas fa-clinic-medical"></i>
        </div>
        <div class="recommendation-content">
            <h6 class="mb-1">Recommended Actions</h6>
            <ul class="mb-0 ps-3">
                <li>Immediately isolate affected animals to prevent spread</li>
                <li>Consult with veterinarian for appropriate treatment plan</li>
                <li>Document all symptoms and observations for accurate diagnosis</li>
                <li>Monitor the entire flock for similar symptoms</li>
                <li>Review biosecurity protocols to prevent recurrence</li>
            </ul>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* Header Styles */
    .report-header { margin-bottom: 1.5rem; }
    .header-icon { width: 55px; height: 55px; border-radius: 14px; display: flex; align-items: center; justify-content: center; }
    .header-icon.bg-critical { background: linear-gradient(135deg, #dc2626, #b91c1c); }
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
    .bg-critical { background: linear-gradient(135deg, #dc2626, #b91c1c); }
    .bg-flocks { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }
    .bg-conditions { background: linear-gradient(135deg, #f59e0b, #d97706); }
    .bg-affected { background: linear-gradient(135deg, #3b82f6, #2563eb); }
    .stat-details { flex: 1; }
    .stat-label { font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; font-weight: 600; }
    .stat-value { font-size: 28px; font-weight: 700; margin: 0.15rem 0; line-height: 1.2; }
    .stat-trend { font-size: 11px; color: #64748b; }
    
    /* Section Header */
    .section-header { margin-bottom: 1rem; }
    .section-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; }
    .bg-critical-soft { background: #fee2e2; }
    .section-title { font-size: 18px; font-weight: 600; color: #1e293b; margin: 0; }
    .section-description { font-size: 13px; margin-top: 0.25rem; }
    
    /* Alert Cards */
    .alert-card { background: white; border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden; transition: all 0.3s ease; height: 100%; }
    .alert-card:hover { transform: translateY(-3px); box-shadow: 0 12px 30px rgba(0,0,0,0.1); }
    .critical-alert { border-left: 4px solid #dc2626; }
    .alert-card-header { padding: 1rem 1.25rem; background: #fef2f2; border-bottom: 1px solid #fecaca; }
    .date-badge { font-size: 12px; color: #64748b; }
    .days-ago { font-size: 11px; color: #94a3b8; margin-left: 0.5rem; }
    .flock-info { display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap; }
    .flock-link { color: #1e293b; text-decoration: none; font-weight: 700; font-size: 16px; transition: color 0.2s; }
    .flock-link:hover { color: #dc2626; }
    .species-badge { font-size: 11px; background: #e2e8f0; padding: 0.2rem 0.6rem; border-radius: 12px; color: #64748b; }
    .type-badge { font-size: 12px; padding: 0.25rem 0.75rem; border-radius: 20px; background: #e2e8f0; color: #475569; }
    .type-badge.checkup { background: #dbeafe; color: #1e40af; }
    .type-badge.symptom { background: #fef3c7; color: #92400e; }
    .type-badge.lab_result { background: #d1fae5; color: #065f46; }
    .type-badge.post_mortem { background: #f1f5f9; color: #475569; }
    .severity-badge { padding: 0.3rem 0.75rem; border-radius: 20px; font-size: 11px; font-weight: 600; }
    .severity-badge.critical { background: #dc2626; color: white; }
    
    .alert-card-body { padding: 1rem 1.25rem; }
    .condition-box { background: #f8fafc; padding: 0.75rem; border-radius: 10px; margin-bottom: 0.5rem; border-left: 3px solid #dc2626; }
    .condition-title { font-size: 11px; color: #64748b; margin-bottom: 0.25rem; }
    .condition-value { font-size: 14px; font-weight: 500; color: #1e293b; }
    
    .info-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.75rem; }
    .info-item { display: flex; flex-direction: column; gap: 0.25rem; }
    .info-label { font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; font-weight: 600; }
    .info-value { font-size: 13px; color: #1e293b; }
    .affected-number { font-size: 18px; font-weight: 700; color: #dc2626; }
    .affected-percentage { font-size: 12px; color: #64748b; margin-left: 0.25rem; }
    
    .symptoms-box { background: #fefce8; padding: 0.75rem; border-radius: 10px; border-left: 3px solid #f59e0b; }
    .symptoms-title { font-size: 11px; color: #854d0e; margin-bottom: 0.5rem; }
    .symptoms-list { display: flex; flex-wrap: wrap; gap: 0.5rem; }
    .symptom-tag { font-size: 11px; background: white; padding: 0.2rem 0.6rem; border-radius: 12px; border: 1px solid #fde68a; color: #854d0e; }
    
    .vet-notes { background: #e0f2fe; padding: 0.6rem 0.75rem; border-radius: 10px; font-size: 12px; color: #0369a1; }
    
    .alert-card-footer { padding: 0.75rem 1.25rem; background: #f8fafc; border-top: 1px solid #e2e8f0; }
    .btn-group { display: flex; gap: 0.5rem; }
    .btn-group .btn { flex: 1; border-radius: 8px; font-size: 12px; padding: 0.4rem 0.5rem; }
    
    /* Empty State */
    .empty-state { text-align: center; padding: 3rem; background: white; border-radius: 16px; border: 1px solid #e2e8f0; }
    .empty-icon { width: 70px; height: 70px; background: #d1fae5; border-radius: 35px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; }
    .empty-icon i { font-size: 32px; color: #10b981; }
    .empty-state h4 { font-size: 18px; font-weight: 600; color: #1e293b; margin-bottom: 0.5rem; }
    .empty-state p { color: #64748b; margin-bottom: 1.5rem; }
    
    /* Recommendation Card */
    .recommendation-card { background: #e8f4f8; border-radius: 12px; padding: 1rem; display: flex; align-items: flex-start; gap: 1rem; border: 1px solid #bee0e8; }
    .recommendation-icon { width: 40px; height: 40px; background: #0d6e4f; border-radius: 10px; display: flex; align-items: center; justify-content: center; }
    .recommendation-icon i { font-size: 20px; color: white; }
    .recommendation-content h6 { color: #0d6e4f; }
    .recommendation-content ul { font-size: 13px; color: #475569; }
    .recommendation-content li { margin-bottom: 0.25rem; }
    
    /* Responsive */
    @media (max-width: 768px) {
        .stats-grid { grid-template-columns: repeat(2, 1fr); gap: 1rem; }
        .stat-value { font-size: 22px; }
        .header-title { font-size: 22px; }
        .info-grid { grid-template-columns: 1fr; gap: 0.5rem; }
        .flock-info { flex-direction: column; align-items: flex-start; }
        .recommendation-card { flex-direction: column; }
    }
</style>
@endpush
@endsection