@extends('layouts.master')

@section('title', 'Health Calendar')

@section('content')
<div class="container-fluid px-4">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <div class="d-flex align-items-center gap-3">
                    <div class="page-icon bg-success-soft">
                        <i class="fas fa-calendar-alt fs-1 text-success"></i>
                    </div>
                    <div>
                        <h1 class="page-title mb-1">Health Calendar</h1>
                        <p class="page-description text-muted mb-0">Vaccination, treatment, and health event schedule</p>
                    </div>
                </div>
            </div>
            <div class="col-auto">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('health-records.index') }}">Health Records</a></li>
                        <li class="breadcrumb-item active">Health Calendar</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <!-- Calendar Navigation -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <a href="{{ route('health-records.health-calendar', ['year' => $prevMonth->year, 'month' => $prevMonth->month]) }}" class="btn btn-outline-primary">
                    <i class="fas fa-chevron-left me-1"></i> {{ $prevMonth->format('M Y') }}
                </a>
                <h3 class="mb-0">{{ Carbon\Carbon::create($year, $month, 1)->format('F Y') }}</h3>
                <a href="{{ route('health-records.health-calendar', ['year' => $nextMonth->year, 'month' => $nextMonth->month]) }}" class="btn btn-outline-primary">
                    {{ $nextMonth->format('M Y') }} <i class="fas fa-chevron-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Calendar Grid -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="calendar-container">
                <!-- Weekday Headers -->
                <div class="calendar-weekdays">
                    <div class="calendar-weekday">Sun</div>
                    <div class="calendar-weekday">Mon</div>
                    <div class="calendar-weekday">Tue</div>
                    <div class="calendar-weekday">Wed</div>
                    <div class="calendar-weekday">Thu</div>
                    <div class="calendar-weekday">Fri</div>
                    <div class="calendar-weekday">Sat</div>
                </div>

                <!-- Calendar Days -->
                @foreach($calendarData as $week)
                <div class="calendar-week">
                    @foreach($week as $day)
                    <div class="calendar-day {{ !$day['isCurrentMonth'] ? 'other-month' : '' }} {{ $day['isToday'] ? 'today' : '' }}">
                        <div class="calendar-day-header">
                            <span class="calendar-day-number">{{ $day['day'] }}</span>
                        </div>
                        <div class="calendar-events">
                            @foreach($day['events'] as $event)
                            <div class="calendar-event event-{{ $event['color'] }}" onclick="showEventDetails('{{ $event['type'] }}', {{ $event['id'] }})">
                                <div class="event-title">{{ $event['title'] }}</div>
                                <div class="event-flock">
                                    <i class="fas fa-chicken"></i> {{ $event['flock'] }}
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Legend -->
    <div class="card shadow-sm border-0 mt-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <h6 class="mb-3"><i class="fas fa-info-circle me-2"></i>Legend</h6>
                    <div class="d-flex gap-4 flex-wrap">
                        <div class="d-flex align-items-center gap-2">
                            <div class="event-legend event-success"></div>
                            <span>Vaccination</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <div class="event-legend event-warning"></div>
                            <span>Treatment</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <div class="event-legend event-danger"></div>
                            <span>Critical Health Record</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <div class="event-legend event-info"></div>
                            <span>Health Check / Record</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <div class="today-legend"></div>
                            <span>Today</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Event Details Modal -->
<div class="modal fade" id="eventDetailsModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header" id="eventModalHeader">
                <h5 class="modal-title" id="eventModalTitle">
                    <i class="fas fa-info-circle me-2"></i>Event Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="eventDetailsContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2">Loading details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a href="#" id="viewFullDetailsBtn" class="btn btn-primary">View Full Details</a>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .calendar-container {
        background: white;
        border-radius: 12px;
        overflow: hidden;
    }
    
    .calendar-weekdays {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
    }
    
    .calendar-weekday {
        padding: 12px;
        text-align: center;
        font-weight: 600;
        color: #475569;
        font-size: 14px;
    }
    
    .calendar-week {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        min-height: 120px;
    }
    
    .calendar-day {
        border-right: 1px solid #e2e8f0;
        border-bottom: 1px solid #e2e8f0;
        padding: 8px;
        background: white;
        transition: all 0.2s ease;
        display: flex;
        flex-direction: column;
    }
    
    .calendar-day:nth-child(7n) {
        border-right: none;
    }
    
    .calendar-day.other-month {
        background: #f8fafc;
    }
    
    .calendar-day.today {
        background: #f0fdf4;
    }
    
    .calendar-day-header {
        text-align: right;
        margin-bottom: 8px;
    }
    
    .calendar-day-number {
        display: inline-block;
        width: 28px;
        height: 28px;
        line-height: 28px;
        text-align: center;
        border-radius: 50%;
        font-size: 13px;
        font-weight: 500;
        color: #1e293b;
    }
    
    .calendar-day.today .calendar-day-number {
        background: #22c55e;
        color: white;
    }
    
    .calendar-events {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 4px;
        overflow-y: auto;
        max-height: 120px;
    }
    
    .calendar-event {
        padding: 4px 8px;
        border-radius: 6px;
        font-size: 11px;
        cursor: pointer;
        transition: all 0.2s ease;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .calendar-event:hover {
        transform: translateX(2px);
        filter: brightness(0.95);
    }
    
    .event-success {
        background: #dcfce7;
        border-left: 3px solid #22c55e;
        color: #166534;
    }
    
    .event-warning {
        background: #fef3c7;
        border-left: 3px solid #f59e0b;
        color: #92400e;
    }
    
    .event-danger {
        background: #fee2e2;
        border-left: 3px solid #ef4444;
        color: #991b1b;
    }
    
    .event-info {
        background: #dbeafe;
        border-left: 3px solid #3b82f6;
        color: #1e40af;
    }
    
    .event-title {
        font-weight: 600;
        margin-bottom: 2px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .event-flock {
        font-size: 9px;
        opacity: 0.8;
        display: flex;
        align-items: center;
        gap: 2px;
    }
    
    .event-flock i {
        font-size: 8px;
    }
    
    .event-legend {
        width: 30px;
        height: 12px;
        border-radius: 4px;
    }
    
    .event-legend.event-success {
        background: #dcfce7;
        border-left: 3px solid #22c55e;
    }
    
    .event-legend.event-warning {
        background: #fef3c7;
        border-left: 3px solid #f59e0b;
    }
    
    .event-legend.event-danger {
        background: #fee2e2;
        border-left: 3px solid #ef4444;
    }
    
    .event-legend.event-info {
        background: #dbeafe;
        border-left: 3px solid #3b82f6;
    }
    
    .today-legend {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: #22c55e;
    }
    
    @media (max-width: 768px) {
        .calendar-week {
            min-height: 100px;
        }
        
        .calendar-day-number {
            width: 24px;
            height: 24px;
            line-height: 24px;
            font-size: 11px;
        }
        
        .calendar-event {
            padding: 2px 4px;
            font-size: 9px;
        }
        
        .event-title {
            font-size: 8px;
        }
        
        .calendar-events {
            max-height: 80px;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    function showEventDetails(type, id) {
        const modal = new bootstrap.Modal(document.getElementById('eventDetailsModal'));
        const modalBody = document.getElementById('eventDetailsContent');
        const modalTitle = document.getElementById('eventModalTitle');
        const modalHeader = document.getElementById('eventModalHeader');
        const viewFullBtn = document.getElementById('viewFullDetailsBtn');
        
        let url = '';
        let title = '';
        let headerClass = '';
        
        switch(type) {
            case 'vaccination':
                url = `/vaccinations/${id}/details`;
                title = '💉 Vaccination Details';
                headerClass = 'bg-success text-white';
                viewFullBtn.href = `/vaccinations/${id}`;
                break;
            case 'treatment':
                url = `/treatments/${id}/details`;
                title = '💊 Treatment Details';
                headerClass = 'bg-warning text-white';
                viewFullBtn.href = `/treatments/${id}`;
                break;
            case 'health_record':
                url = `/health-records/${id}/details`;
                title = '🏥 Health Record Details';
                headerClass = 'bg-info text-white';
                viewFullBtn.href = `/health-records/${id}`;
                break;
            default:
                url = '#';
                title = 'Event Details';
                headerClass = 'bg-primary text-white';
        }
        
        modalTitle.innerHTML = `<i class="fas fa-info-circle me-2"></i>${title}`;
        modalHeader.className = `modal-header ${headerClass}`;
        
        modalBody.innerHTML = `<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Loading details...</p></div>`;
        modal.show();
        
        fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    let detailsHtml = '';
                    if (type === 'vaccination') {
                        const v = data.vaccination;
                        detailsHtml = `
                            <div class="detail-section">
                                <div class="detail-item mb-3">
                                    <span class="detail-label">Flock</span>
                                    <span class="detail-value">${escapeHtml(v.flock_number)}</span>
                                </div>
                                <div class="detail-item mb-3">
                                    <span class="detail-label">Vaccine</span>
                                    <span class="detail-value">${escapeHtml(v.vaccine_name)}</span>
                                </div>
                                <div class="detail-item mb-3">
                                    <span class="detail-label">Disease Target</span>
                                    <span class="detail-value">${escapeHtml(v.disease_target)}</span>
                                </div>
                                <div class="detail-item mb-3">
                                    <span class="detail-label">Date</span>
                                    <span class="detail-value">${v.administration_date}</span>
                                </div>
                                <div class="detail-item mb-3">
                                    <span class="detail-label">Route</span>
                                    <span class="detail-value">${escapeHtml(v.route)}</span>
                                </div>
                                <div class="detail-item mb-3">
                                    <span class="detail-label">Batch Number</span>
                                    <span class="detail-value">${escapeHtml(v.batch_number)}</span>
                                </div>
                                <div class="detail-item mb-3">
                                    <span class="detail-label">Coverage</span>
                                    <span class="detail-value">${v.coverage_percentage}%</span>
                                </div>
                            </div>
                        `;
                    } else if (type === 'treatment') {
                        const t = data.treatment;
                        detailsHtml = `
                            <div class="detail-section">
                                <div class="detail-item mb-3">
                                    <span class="detail-label">Flock</span>
                                    <span class="detail-value">${escapeHtml(t.flock_number)}</span>
                                </div>
                                <div class="detail-item mb-3">
                                    <span class="detail-label">Diagnosis</span>
                                    <span class="detail-value">${escapeHtml(t.diagnosis)}</span>
                                </div>
                                <div class="detail-item mb-3">
                                    <span class="detail-label">Product</span>
                                    <span class="detail-value">${escapeHtml(t.product_name)}</span>
                                </div>
                                <div class="detail-item mb-3">
                                    <span class="detail-label">Treatment Period</span>
                                    <span class="detail-value">${t.start_date} to ${t.end_date}</span>
                                </div>
                                <div class="detail-item mb-3">
                                    <span class="detail-label">Dosage</span>
                                    <span class="detail-value">${escapeHtml(t.dosage)}</span>
                                </div>
                                <div class="detail-item mb-3">
                                    <span class="detail-label">Route</span>
                                    <span class="detail-value">${escapeHtml(t.administration_route)}</span>
                                </div>
                                ${t.withdrawal_end_date ? `
                                <div class="detail-item mb-3">
                                    <span class="detail-label">Withdrawal Ends</span>
                                    <span class="detail-value">${t.withdrawal_end_date}</span>
                                </div>
                                ` : ''}
                            </div>
                        `;
                    } else if (type === 'health_record') {
                        const hr = data.record;
                        detailsHtml = `
                            <div class="detail-section">
                                <div class="detail-item mb-3">
                                    <span class="detail-label">Flock</span>
                                    <span class="detail-value">${escapeHtml(hr.flock_number)}</span>
                                </div>
                                <div class="detail-item mb-3">
                                    <span class="detail-label">Date</span>
                                    <span class="detail-value">${hr.record_date}</span>
                                </div>
                                <div class="detail-item mb-3">
                                    <span class="detail-label">Record Type</span>
                                    <span class="detail-value">${escapeHtml(hr.record_type)}</span>
                                </div>
                                <div class="detail-item mb-3">
                                    <span class="detail-label">Condition</span>
                                    <span class="detail-value">${escapeHtml(hr.condition || 'N/A')}</span>
                                </div>
                                <div class="detail-item mb-3">
                                    <span class="detail-label">Severity</span>
                                    <span class="detail-value">
                                        <span class="badge bg-${hr.severity === 'critical' ? 'danger' : (hr.severity === 'warning' ? 'warning' : 'info')}">
                                            ${escapeHtml(hr.severity)}
                                        </span>
                                    </span>
                                </div>
                                <div class="detail-item mb-3">
                                    <span class="detail-label">Affected Birds</span>
                                    <span class="detail-value">${hr.affected_count || 'N/A'}</span>
                                </div>
                            </div>
                        `;
                    }
                    
                    modalBody.innerHTML = detailsHtml;
                } else {
                    modalBody.innerHTML = `<div class="alert alert-danger">Failed to load details: ${data.message}</div>`;
                }
            })
            .catch(error => {
                modalBody.innerHTML = `<div class="alert alert-danger">Error loading details: ${error.message}</div>`;
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
@endsection