@extends('layouts.master')

@section('content')
<div class="container-fluid px-4">

    {{-- Page Header --}}
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <div class="d-flex align-items-center gap-3">
                    <div class="page-icon bg-danger-soft">
                        <i class="fas fa-heartbeat fs-1 text-danger"></i>
                    </div>
                    <div>
                        <h1 class="page-title mb-1">Health Reports</h1>
                        <p class="page-description text-muted mb-0">Animal health monitoring and mortality tracking</p>
                    </div>
                </div>
            </div>
            <div class="col-auto">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Health Reports</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    {{-- ── Flexible Date Filter ── --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('reports.health') }}" id="healthFilterForm">

                <div class="d-flex gap-2 mb-3 flex-wrap">
                    <span class="fw-semibold text-muted me-2 align-self-center" style="font-size:.8rem;text-transform:uppercase;letter-spacing:.05em;">Filter by:</span>
                    <button type="button" class="btn btn-sm filter-mode-btn {{ ($filterMode ?? 'range') === 'range' ? 'btn-danger' : 'btn-outline-secondary' }}" data-mode="range">
                        <i class="fas fa-calendar-range me-1"></i>Date Range
                    </button>
                    <button type="button" class="btn btn-sm filter-mode-btn {{ ($filterMode ?? '') === 'month' ? 'btn-danger' : 'btn-outline-secondary' }}" data-mode="month">
                        <i class="fas fa-calendar-alt me-1"></i>Month Range
                    </button>
                    <button type="button" class="btn btn-sm filter-mode-btn {{ ($filterMode ?? '') === 'year' ? 'btn-danger' : 'btn-outline-secondary' }}" data-mode="year">
                        <i class="fas fa-calendar me-1"></i>Year / Quarter
                    </button>
                </div>

                <input type="hidden" name="filter_mode" id="filterModeInput" value="{{ $filterMode ?? 'range' }}">

                <div class="row g-3 align-items-end">

                    {{-- PANEL: Date Range --}}
                    <div id="panel-range" class="col-12 filter-panel">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Start Date</label>
                                <input type="date" name="start_date" class="form-control"
                                       value="{{ isset($startDate) ? $startDate->format('Y-m-d') : date('Y-m-01') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">End Date</label>
                                <input type="date" name="end_date" class="form-control"
                                       value="{{ isset($endDate) ? $endDate->format('Y-m-d') : date('Y-m-d') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Species</label>
                                <select name="species_id" class="form-select">
                                    <option value="">All Species</option>
                                    @foreach($species as $s)
                                        <option value="{{ $s->id }}" {{ ($speciesId ?? '') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- PANEL: Month Range --}}
                    <div id="panel-month" class="col-12 filter-panel" style="display:none;">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">From Month</label>
                                <input type="month" name="from_month" class="form-control"
                                       value="{{ $fromMonth ?? date('Y-m', strtotime('first day of january')) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">To Month</label>
                                <input type="month" name="to_month" class="form-control"
                                       value="{{ $toMonth ?? date('Y-m') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Species</label>
                                <select name="species_id" class="form-select">
                                    <option value="">All Species</option>
                                    @foreach($species as $s)
                                        <option value="{{ $s->id }}" {{ ($speciesId ?? '') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- PANEL: Year / Quarter --}}
                    <div id="panel-year" class="col-12 filter-panel" style="display:none;">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-2">
                                <label class="form-label fw-semibold">Year</label>
                                <select name="year" class="form-select">
                                    @foreach(range(date('Y'), date('Y') - 5) as $y)
                                        <option value="{{ $y }}" {{ ($filterYear ?? date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Quarter <span class="text-muted fw-normal">(optional)</span></label>
                                <select name="quarter" class="form-select">
                                    <option value="">Full Year</option>
                                    <option value="1" {{ ($filterQuarter ?? '') == '1' ? 'selected' : '' }}>Q1 — Jan to Mar</option>
                                    <option value="2" {{ ($filterQuarter ?? '') == '2' ? 'selected' : '' }}>Q2 — Apr to Jun</option>
                                    <option value="3" {{ ($filterQuarter ?? '') == '3' ? 'selected' : '' }}>Q3 — Jul to Sep</option>
                                    <option value="4" {{ ($filterQuarter ?? '') == '4' ? 'selected' : '' }}>Q4 — Oct to Dec</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Species</label>
                                <select name="species_id" class="form-select">
                                    <option value="">All Species</option>
                                    @foreach($species as $s)
                                        <option value="{{ $s->id }}" {{ ($speciesId ?? '') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-filter me-2"></i>Apply
                        </button>
                        <a href="{{ route('reports.health') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-redo-alt me-2"></i>Reset
                        </a>
                    </div>
                </div>

                <div class="mt-3 pt-2 border-top d-flex align-items-center gap-2 flex-wrap">
                    <i class="fas fa-calendar-check text-danger" style="font-size:.85rem;"></i>
                    <span style="font-size:.8rem;color:#64748b;">
                        Showing data for:
                        <strong class="text-dark">
                            {{ $startDate->format('d M Y') }} — {{ $endDate->format('d M Y') }}
                        </strong>
                    </span>
                </div>

            </form>
        </div>
    </div>

    {{-- ── Summary KPI Cards ── --}}
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-card-icon bg-danger-soft">
                        <i class="fas fa-skull-crossbones text-danger fs-4"></i>
                    </div>
                    <div class="stat-card-info">
                        <span class="stat-card-label">Total Mortality</span>
                        <h3 class="stat-card-value text-danger">{{ number_format($summary['total_mortality']) }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-card-icon bg-warning-soft">
                        <i class="fas fa-scissors text-warning fs-4"></i>
                    </div>
                    <div class="stat-card-info">
                        <span class="stat-card-label">Total Culling</span>
                        <h3 class="stat-card-value text-warning">{{ number_format($summary['total_culling']) }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-card-icon bg-info-soft">
                        <i class="fas fa-triangle-exclamation text-info fs-4"></i>
                    </div>
                    <div class="stat-card-info">
                        <span class="stat-card-label">Total Losses</span>
                        <h3 class="stat-card-value text-info">{{ number_format($summary['total_losses']) }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-card-icon bg-primary-soft">
                        <i class="fas fa-chart-simple text-primary fs-4"></i>
                    </div>
                    <div class="stat-card-info">
                        <span class="stat-card-label">Avg Daily Mortality</span>
                        <h3 class="stat-card-value text-primary">{{ number_format($summary['avg_daily_mortality'] ?? 0, 1) }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Health Records Summary Cards (add after existing 4 KPI cards) ── --}}
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-card-body">
                <div class="stat-card-icon bg-danger-soft">
                    <i class="fas fa-file-medical text-danger fs-4"></i>
                </div>
                <div class="stat-card-info">
                    <span class="stat-card-label">Health Records Filed</span>
                    <h3 class="stat-card-value text-danger">{{ number_format($summary['total_health_records']) }}</h3>
                    <small class="text-muted">{{ $summary['critical_count'] }} critical · {{ $summary['warning_count'] }} warning · {{ $summary['info_count'] }} info</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-card-body">
                <div class="stat-card-icon bg-warning-soft">
                    <i class="fas fa-exclamation-circle text-warning fs-4"></i>
                </div>
                <div class="stat-card-info">
                    <span class="stat-card-label">Critical Alerts</span>
                    <h3 class="stat-card-value text-warning">{{ number_format($summary['critical_count']) }}</h3>
                    <small class="text-muted">Require immediate attention</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-card-body">
                <div class="stat-card-icon bg-info-soft">
                    <i class="fas fa-users text-info fs-4"></i>
                </div>
                <div class="stat-card-info">
                    <span class="stat-card-label">Animals Affected</span>
                    <h3 class="stat-card-value text-info">{{ number_format($summary['total_affected_birds']) }}</h3>
                    <small class="text-muted">Across all health records</small>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Critical Alerts Table ── --}}
@if($criticalAlerts->count() > 0)
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white border-0 py-3 d-flex align-items-center justify-content-between">
        <h5 class="card-title mb-0 fw-semibold">
            <i class="fas fa-exclamation-triangle text-danger me-2"></i>Critical Health Alerts
        </h5>
        <span class="badge bg-danger px-3 py-2">{{ $criticalAlerts->count() }} records</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="py-3 ps-4">Date</th>
                        <th class="py-3">Flock</th>
                        <th class="py-3">Species</th>
                        <th class="py-3">Condition</th>
                        <th class="py-3">Type</th>
                        <th class="py-3 text-end">Affected</th>
                        <th class="py-3">Severity</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($criticalAlerts as $alert)
                    <tr>
                        <td class="ps-4 fw-semibold">{{ $alert->record_date->format('d M Y') }}</td>
                        <td>{{ $alert->flock->flock_number ?? 'N/A' }}</td>
                        <td>{{ $alert->flock->species->name ?? 'N/A' }}</td>
                        <td>{{ $alert->condition ?? 'Not specified' }}</td>
                        <td><span class="badge bg-secondary-soft text-secondary">{{ ucfirst(str_replace('_',' ',$alert->record_type)) }}</span></td>
                        <td class="text-end text-danger fw-bold">{{ number_format($alert->affected_count ?? 0) }}</td>
                        <td><span class="badge bg-danger-soft text-danger"><i class="fas fa-circle me-1" style="font-size:8px;"></i>Critical</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

{{-- ── Health Records by Type ── --}}
@if($byRecordType->count() > 0)
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white border-0 py-3">
        <h5 class="card-title mb-0 fw-semibold">
            <i class="fas fa-stethoscope text-primary me-2"></i>Health Records by Type
        </h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="py-3">Record Type</th>
                        <th class="py-3 text-center">Count</th>
                        <th class="py-3 text-end">Total Animals Affected</th>
                        <th class="py-3">Share</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalRecords = $byRecordType->sum('count'); @endphp
                    @foreach($byRecordType as $type)
                    @php $pct = $totalRecords > 0 ? ($type->count / $totalRecords) * 100 : 0; @endphp
                    <tr>
                        <td class="fw-semibold">{{ ucfirst(str_replace('_',' ',$type->record_type)) }}</td>
                        <td class="text-center"><span class="badge bg-primary-soft text-primary">{{ $type->count }}</span></td>
                        <td class="text-end text-danger fw-semibold">{{ number_format($type->total_affected ?? 0) }}</td>
                        <td style="min-width:200px;">
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress flex-grow-1" style="height:6px;">
                                    <div class="progress-bar bg-danger" style="width:{{ $pct }}%"></div>
                                </div>
                                <span class="small text-muted">{{ number_format($pct,1) }}%</span>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

    {{-- ── Mortality Trend Chart ── --}}
    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0 py-3 d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="card-title mb-0 fw-semibold">
                            <i class="fas fa-chart-area text-danger me-2"></i>Mortality & Culling Trend
                        </h5>
                        <small class="text-muted">Daily losses over selected period</small>
                    </div>
                    <span class="badge bg-danger-soft text-danger px-3 py-2">
                        {{ $startDate->format('d M') }} — {{ $endDate->format('d M Y') }}
                    </span>
                </div>
                <div class="card-body">
                    <canvas id="mortalityChart" height="260"></canvas>
                </div>
            </div>
        </div>

        {{-- Losses Breakdown --}}
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="card-title mb-0 fw-semibold">
                        <i class="fas fa-chart-pie text-danger me-2"></i>Losses Breakdown
                    </h5>
                </div>
                <div class="card-body d-flex flex-column align-items-center justify-content-center">
                    <canvas id="lossesDonut" height="220"></canvas>
                    <div class="d-flex gap-4 mt-3">
                        <div class="text-center">
                            <div class="d-flex align-items-center gap-1 justify-content-center">
                                <span style="width:10px;height:10px;border-radius:50%;background:#ef4444;display:inline-block;"></span>
                                <small class="text-muted fw-semibold">Mortality</small>
                            </div>
                            <strong class="text-danger">{{ number_format($summary['total_mortality']) }}</strong>
                        </div>
                        <div class="text-center">
                            <div class="d-flex align-items-center gap-1 justify-content-center">
                                <span style="width:10px;height:10px;border-radius:50%;background:#f59e0b;display:inline-block;"></span>
                                <small class="text-muted fw-semibold">Culling</small>
                            </div>
                            <strong class="text-warning">{{ number_format($summary['total_culling']) }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Daily Log Table ── --}}
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-0 py-3 d-flex align-items-center justify-content-between">
            <h5 class="card-title mb-0 fw-semibold">
                <i class="fas fa-table text-danger me-2"></i>Daily Health Log
            </h5>
            <span class="badge bg-danger-soft text-danger px-3 py-2">{{ $mortalityTrends->count() }} days</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="py-3 ps-4">Date</th>
                            <th class="py-3 text-end">Mortality</th>
                            <th class="py-3 text-end">Culling</th>
                            <th class="py-3 text-end">Total Loss</th>
                            <th class="py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($mortalityTrends as $day)
                        <tr>
                            <td class="ps-4 fw-semibold">{{ \Carbon\Carbon::parse($day->date)->format('d M Y') }}</td>
                            <td class="text-end text-danger fw-semibold">{{ number_format($day->total_mortality) }}</td>
                            <td class="text-end text-warning fw-semibold">{{ number_format($day->total_culling) }}</td>
                            <td class="text-end fw-bold">{{ number_format($day->total_mortality + $day->total_culling) }}</td>
                            <td>
                                @php $loss = $day->total_mortality + $day->total_culling; @endphp
                                <span class="badge {{ $loss == 0 ? 'bg-success-soft text-success' : ($loss <= 5 ? 'bg-warning-soft text-warning' : 'bg-danger-soft text-danger') }}">
                                    {{ $loss == 0 ? 'Normal' : ($loss <= 5 ? 'Low Alert' : 'High Alert') }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="fas fa-heartbeat fa-3x d-block mb-3 opacity-25"></i>
                                No health records for this period
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection

@push('styles')
<style>
    .page-header { margin-bottom:1.5rem; }
    .page-icon { width:50px;height:50px;display:flex;align-items:center;justify-content:center;border-radius:12px; }
    .page-title { font-size:1.75rem;font-weight:600;color:#1e293b; }
    .page-description { font-size:.875rem; }
    .bg-primary-soft  { background:#eff6ff; }
    .bg-success-soft  { background:#f0fdf4; }
    .bg-danger-soft   { background:#fef2f2; }
    .bg-warning-soft  { background:#fffbeb; }
    .bg-info-soft     { background:#f0fdfa; }
    .stat-card { background:#fff;border-radius:16px;padding:1.1rem;border:1px solid #e2e8f0; }
    .stat-card-body { display:flex;align-items:center;gap:1rem; }
    .stat-card-icon { width:52px;height:52px;display:flex;align-items:center;justify-content:center;border-radius:14px;flex-shrink:0; }
    .stat-card-info { flex:1; }
    .stat-card-label { font-size:.72rem;text-transform:uppercase;letter-spacing:.6px;color:#64748b;font-weight:700;display:block;margin-bottom:.2rem; }
    .stat-card-value { font-size:1.45rem;font-weight:800;margin:0;line-height:1.15; }
    .card { border-radius:16px; }
    .card-header { border-radius:16px 16px 0 0 !important; }
    .table th { font-weight:600;font-size:.875rem;color:#475569; }
    .table td { font-size:.875rem;color:#334155;vertical-align:middle; }
    .progress { background:#e2e8f0;border-radius:10px; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    const rawTrends  = @json($mortalityTrends);
    const labels     = rawTrends.map(r => r.date);
    const mortality  = rawTrends.map(r => parseFloat(r.total_mortality) || 0);
    const culling    = rawTrends.map(r => parseFloat(r.total_culling)   || 0);

    /* ── Trend Chart ── */
    new Chart(document.getElementById('mortalityChart'), {
        type: 'line',
        data: {
            labels,
            datasets: [
                {
                    label: 'Mortality',
                    data: mortality,
                    borderColor: '#ef4444',
                    backgroundColor: 'rgba(239,68,68,.09)',
                    borderWidth: 2.5,
                    pointRadius: 3,
                    tension: .4,
                    fill: true
                },
                {
                    label: 'Culling',
                    data: culling,
                    borderColor: '#f59e0b',
                    backgroundColor: 'rgba(245,158,11,.07)',
                    borderWidth: 2.5,
                    pointRadius: 3,
                    tension: .4,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            interaction: { mode: 'index', intersect: false },
            plugins: { legend: { position: 'top', labels: { font: { size: 12 } } } },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 10 }, maxTicksLimit: 15 } },
                y: { grid: { color: '#f1f5f9' }, ticks: { precision: 0 } }
            }
        }
    });

    /* ── Losses Donut ── */
    const totalMort = {{ (int) $summary['total_mortality'] }};
    const totalCull = {{ (int) $summary['total_culling'] }};

    if (totalMort + totalCull > 0) {
        new Chart(document.getElementById('lossesDonut'), {
            type: 'doughnut',
            data: {
                labels: ['Mortality', 'Culling'],
                datasets: [{
                    data: [totalMort, totalCull],
                    backgroundColor: ['#ef4444', '#f59e0b'],
                    borderWidth: 2,
                    borderColor: '#fff',
                    hoverOffset: 5
                }]
            },
            options: {
                responsive: true,
                cutout: '70%',
                plugins: { legend: { display: false } }
            }
        });
    } else {
        document.getElementById('lossesDonut').closest('.card-body').innerHTML =
            '<div class="text-center text-muted py-4"><i class="fas fa-heart fa-3x text-success opacity-50 mb-3 d-block"></i><p>No losses recorded<br>in this period.</p></div>';
    }

    /* ── Filter mode switcher ── */
    const modeInput = document.getElementById('filterModeInput');
    const modeBtns  = document.querySelectorAll('.filter-mode-btn');
    const allPanels = document.querySelectorAll('.filter-panel');

    function activateMode(mode) {
        modeInput.value = mode;
        modeBtns.forEach(btn => {
            const active = btn.dataset.mode === mode;
            btn.classList.toggle('btn-danger',            active);
            btn.classList.toggle('btn-outline-secondary', !active);
        });
        allPanels.forEach(p => { p.style.display = 'none'; });
        const target = document.getElementById('panel-' + mode);
        if (target) target.style.display = '';
    }

    activateMode('{{ $filterMode ?? "range" }}');
    modeBtns.forEach(btn => btn.addEventListener('click', () => activateMode(btn.dataset.mode)));

});
</script>
@endpush