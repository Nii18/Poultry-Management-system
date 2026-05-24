@extends('layouts.master')

@section('content')
<div class="container-fluid px-4">

    {{-- Page Header --}}
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <div class="d-flex align-items-center gap-3">
                    <div class="page-icon bg-warning-soft">
                        <i class="fas fa-chart-line fs-1 text-warning"></i>
                    </div>
                    <div>
                        <h1 class="page-title mb-1">Performance Reports</h1>
                        <p class="page-description text-muted mb-0">Farm performance metrics and KPIs</p>
                    </div>
                </div>
            </div>
            <div class="col-auto">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Performance Reports</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    {{-- ── Flexible Date Filter ── --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('reports.performance') }}" id="perfFilterForm">

                {{-- Mode Switcher --}}
                <div class="d-flex gap-2 mb-3 flex-wrap">
                    <span class="fw-semibold text-muted me-2 align-self-center" style="font-size:.8rem;text-transform:uppercase;letter-spacing:.05em;">Filter by:</span>
                    <button type="button" class="btn btn-sm filter-mode-btn {{ ($filterMode ?? 'range') === 'range' ? 'btn-warning' : 'btn-outline-secondary' }}" data-mode="range">
                        <i class="fas fa-calendar-range me-1"></i>Date Range
                    </button>
                    <button type="button" class="btn btn-sm filter-mode-btn {{ ($filterMode ?? '') === 'month' ? 'btn-warning' : 'btn-outline-secondary' }}" data-mode="month">
                        <i class="fas fa-calendar-alt me-1"></i>Month Range
                    </button>
                    <button type="button" class="btn btn-sm filter-mode-btn {{ ($filterMode ?? '') === 'year' ? 'btn-warning' : 'btn-outline-secondary' }}" data-mode="year">
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
                                        <option value="{{ $s->id }}" {{ ($speciesId ?? '') == $s->id ? 'selected' : '' }}>
                                            {{ $s->name }}
                                        </option>
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
                                        <option value="{{ $s->id }}" {{ ($speciesId ?? '') == $s->id ? 'selected' : '' }}>
                                            {{ $s->name }}
                                        </option>
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
                                        <option value="{{ $s->id }}" {{ ($speciesId ?? '') == $s->id ? 'selected' : '' }}>
                                            {{ $s->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-warning text-white">
                            <i class="fas fa-filter me-2"></i>Apply
                        </button>
                        <a href="{{ route('reports.performance') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-redo-alt me-2"></i>Reset
                        </a>
                    </div>
                </div>

                {{-- Active period display --}}
                <div class="mt-3 pt-2 border-top d-flex align-items-center gap-2 flex-wrap">
                    <i class="fas fa-calendar-check text-warning" style="font-size:.85rem;"></i>
                    <span style="font-size:.8rem;color:#64748b;">
                        Showing data for:
                        <strong class="text-dark">
                            {{ $startDate->format('d M Y') }} — {{ $endDate->format('d M Y') }}
                        </strong>
                    </span>
                    <span class="badge bg-warning-soft text-warning ms-1">
                        {{ $flocks->total() }} flock(s) found
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
                    <div class="stat-card-icon bg-primary-soft">
                        <i class="fas fa-layer-group text-primary fs-4"></i>
                    </div>
                    <div class="stat-card-info">
                        <span class="stat-card-label">Total Flocks</span>
                        <h3 class="stat-card-value text-primary">{{ $summary['total_flocks'] }}</h3>
                    </div>
                </div>
            </div>
        </div>
       {{-- Find the Total Animals stat card and replace it with two clearer ones: --}}
<div class="col-md-3">
    <div class="stat-card">
        <div class="stat-card-body">
            <div class="stat-card-icon bg-success-soft">
                <i class="fas fa-paw text-success"></i>
            </div>
            <div class="stat-card-info">
                <span class="stat-card-label">Animals in Report Period</span>
                <h3 class="stat-card-value">{{ number_format($summary['total_animals']) }}</h3>
                <small class="text-muted">Initial count of flocks started in this period</small>
            </div>
        </div>
    </div>
</div>
<div class="col-md-3">
    <div class="stat-card">
        <div class="stat-card-body">
            <div class="stat-card-icon bg-primary-soft">
                <i class="fas fa-layer-group text-primary"></i>
            </div>
            <div class="stat-card-info">
                <span class="stat-card-label">Total Live Animals (System)</span>
                <h3 class="stat-card-value">{{ number_format($summary['current_animals_in_system']) }}</h3>
                <small class="text-muted">All active flocks right now</small>
            </div>
        </div>
    </div>
</div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-card-icon bg-danger-soft">
                        <i class="fas fa-heart-crack text-danger fs-4"></i>
                    </div>
                    <div class="stat-card-info">
                        <span class="stat-card-label">Avg Mortality Rate</span>
                        <h3 class="stat-card-value text-danger">{{ number_format($summary['avg_mortality_rate'] ?? 0, 1) }}%</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-card-icon bg-success-soft">
                        <i class="fas fa-sack-dollar text-success fs-4"></i>
                    </div>
                    <div class="stat-card-info">
                        <span class="stat-card-label">Net Profit</span>
                        <h3 class="stat-card-value text-success">
                            ₵{{ number_format(($summary['total_revenue'] ?? 0) - ($summary['total_expenses'] ?? 0), 2) }}
                        </h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Charts Row ── --}}
    <div class="row g-4 mb-4">

        {{-- Mortality Trend --}}
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0 py-3 d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="card-title mb-0 fw-semibold">
                            <i class="fas fa-chart-area text-danger me-2"></i>Mortality & Feed Trend
                        </h5>
                        <small class="text-muted">Daily mortality count and feed intake</small>
                    </div>
                    <span class="badge bg-danger-soft text-danger px-3 py-2">
                        {{ $startDate->format('d M') }} — {{ $endDate->format('d M Y') }}
                    </span>
                </div>
                <div class="card-body">
                    <canvas id="mortalityTrendChart" height="260"></canvas>
                </div>
            </div>
        </div>

        {{-- FCR & ADG Gauges --}}
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="card-title mb-0 fw-semibold">
                        <i class="fas fa-gauge-high text-warning me-2"></i>Key Ratios
                    </h5>
                    <small class="text-muted">Feed efficiency & growth</small>
                </div>
                <div class="card-body d-flex flex-column gap-4 justify-content-center">

                    {{-- FCR --}}
                    <div>
                        <div class="d-flex justify-content-between mb-1">
                            <span style="font-size:.8rem;font-weight:700;color:#374151;">Avg Feed Conversion Ratio (FCR)</span>
                            <span style="font-size:.9rem;font-weight:800;color:#f59e0b;">{{ number_format($summary['avg_fcr'] ?? 0, 2) }}</span>
                        </div>
                        <div class="progress" style="height:10px;">
                            @php $fcrPct = min((($summary['avg_fcr'] ?? 0) / 3) * 100, 100); @endphp
                            <div class="progress-bar {{ ($summary['avg_fcr'] ?? 0) <= 1.8 ? 'bg-success' : 'bg-warning' }}"
                                 style="width:{{ $fcrPct }}%;"></div>
                        </div>
                        <small class="text-muted">Industry avg: 1.80 · Lower is better</small>
                    </div>

                    {{-- ADG --}}
                    <div>
                        <div class="d-flex justify-content-between mb-1">
                            <span style="font-size:.8rem;font-weight:700;color:#374151;">Avg Daily Gain (ADG) kg</span>
                            <span style="font-size:.9rem;font-weight:800;color:#3b82f6;">{{ number_format($summary['avg_adg'] ?? 0, 3) }}</span>
                        </div>
                        <div class="progress" style="height:10px;">
                            @php $adgPct = min((($summary['avg_adg'] ?? 0) / 0.1) * 100, 100); @endphp
                            <div class="progress-bar bg-info" style="width:{{ $adgPct }}%;"></div>
                        </div>
                        <small class="text-muted">Target: 0.06–0.08 kg/day</small>
                    </div>

                    {{-- Mortality --}}
                    <div>
                        <div class="d-flex justify-content-between mb-1">
                            <span style="font-size:.8rem;font-weight:700;color:#374151;">Avg Mortality Rate</span>
                            <span style="font-size:.9rem;font-weight:800;color:{{ ($summary['avg_mortality_rate'] ?? 0) <= 5 ? '#10b981' : '#ef4444' }};">
                                {{ number_format($summary['avg_mortality_rate'] ?? 0, 1) }}%
                            </span>
                        </div>
                        <div class="progress" style="height:10px;">
                            <div class="progress-bar {{ ($summary['avg_mortality_rate'] ?? 0) <= 5 ? 'bg-success' : 'bg-danger' }}"
                                 style="width:{{ min($summary['avg_mortality_rate'] ?? 0, 100) }}%;"></div>
                        </div>
                        <small class="text-muted">Acceptable threshold: ≤ 5%</small>
                    </div>

                    {{-- ROI --}}
                    <div>
                        <div class="d-flex justify-content-between mb-1">
                            <span style="font-size:.8rem;font-weight:700;color:#374151;">Return on Investment (ROI)</span>
                            <span style="font-size:.9rem;font-weight:800;color:{{ ($summary['avg_roi'] ?? 0) >= 0 ? '#10b981' : '#ef4444' }};">
                                {{ number_format($summary['avg_roi'] ?? 0, 1) }}%
                            </span>
                        </div>
                        <div class="progress" style="height:10px;">
                            <div class="progress-bar {{ ($summary['avg_roi'] ?? 0) >= 0 ? 'bg-success' : 'bg-danger' }}"
                                 style="width:{{ min(abs($summary['avg_roi'] ?? 0), 100) }}%;"></div>
                        </div>
                        <small class="text-muted">Based on revenue vs expenses</small>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- ── Weight Trend Chart ── --}}
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0 py-3 d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="card-title mb-0 fw-semibold">
                            <i class="fas fa-weight-hanging text-info me-2"></i>Average Weight Trend
                        </h5>
                        <small class="text-muted">kg per day across all active flocks</small>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="weightTrendChart" height="120"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Flock Table ── --}}
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-0 py-3 d-flex align-items-center justify-content-between">
            <h5 class="card-title mb-0 fw-semibold">
                <i class="fas fa-list text-warning me-2"></i>Flock Performance Detail
            </h5>
            <span class="badge bg-warning-soft text-warning px-3 py-2">{{ $flocks->total() }} flocks</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="py-3 ps-4">Flock #</th>
                            <th class="py-3">Species</th>
                            <th class="py-3">Start Date</th>
                            <th class="py-3 text-end">Initial Count</th>
                            <th class="py-3 text-end">Mortality %</th>
                            <th class="py-3 text-end">FCR</th>
                            <th class="py-3 text-end">ADG (kg)</th>
                            <th class="py-3 text-end">Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($flocks as $flock)
                        <tr>
                            <td class="ps-4">
                                <span class="fw-semibold">{{ $flock->flock_number }}</span>
                            </td>
                            <td>{{ $flock->species->name ?? 'N/A' }}</td>
                            <td>{{ $flock->start_date->format('d M Y') }}</td>
                            <td class="text-end">{{ number_format($flock->initial_count) }}</td>
                            <td class="text-end">
                                <span class="badge {{ ($flock->mortality_rate ?? 0) <= 5 ? 'bg-success-soft text-success' : 'bg-danger-soft text-danger' }}">
                                    {{ number_format($flock->mortality_rate ?? 0, 1) }}%
                                </span>
                            </td>
                            <td class="text-end">{{ number_format($flock->feed_conversion_ratio ?? 0, 2) }}</td>
                            <td class="text-end">{{ number_format($flock->average_daily_gain ?? 0, 3) }}</td>
                            <td class="text-end fw-semibold text-success">
                                ₵{{ number_format($flock->total_revenue ?? 0, 2) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="fas fa-layer-group fa-3x text-muted mb-3 d-block"></i>
                                <span class="text-muted">No flocks found for this period</span>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($flocks->hasPages())
            <div class="d-flex justify-content-between align-items-center px-4 py-3 border-top">
                <small class="text-muted">Showing {{ $flocks->firstItem() }} to {{ $flocks->lastItem() }} of {{ $flocks->total() }}</small>
                {{ $flocks->withQueryString()->links() }}
            </div>
            @endif
        </div>
    </div>

</div>
@endsection

@push('styles')
<style>
    .page-header { margin-bottom: 1.5rem; }
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

    const rawTrends = @json($dailyTrends);

    const trendLabels   = rawTrends.map(r => r.date);
    const mortalityData = rawTrends.map(r => parseFloat(r.total_mortality) || 0);
    const feedData      = rawTrends.map(r => parseFloat(r.total_feed)      || 0);
    const weightData    = rawTrends.map(r => parseFloat(r.avg_weight)      || 0);

    /* ── Mortality & Feed Chart ── */
    new Chart(document.getElementById('mortalityTrendChart'), {
        type: 'line',
        data: {
            labels: trendLabels,
            datasets: [
                {
                    label: 'Mortality Count',
                    data: mortalityData,
                    borderColor: '#ef4444',
                    backgroundColor: 'rgba(239,68,68,.08)',
                    borderWidth: 2,
                    pointRadius: 3,
                    tension: .4,
                    fill: true,
                    yAxisID: 'y'
                },
                {
                    label: 'Feed Intake (kg)',
                    data: feedData,
                    borderColor: '#f59e0b',
                    backgroundColor: 'rgba(245,158,11,.06)',
                    borderWidth: 2,
                    pointRadius: 3,
                    tension: .4,
                    fill: true,
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { position: 'top', labels: { font: { size: 12 } } }
            },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 10 }, maxTicksLimit: 15 } },
                y:  { position: 'left',  grid: { color: '#f1f5f9' }, title: { display: true, text: 'Mortality', color: '#ef4444' } },
                y1: { position: 'right', grid: { drawOnChartArea: false }, title: { display: true, text: 'Feed (kg)', color: '#f59e0b' } }
            }
        }
    });

    /* ── Weight Trend Chart ── */
    new Chart(document.getElementById('weightTrendChart'), {
        type: 'line',
        data: {
            labels: trendLabels,
            datasets: [{
                label: 'Avg Weight (kg)',
                data: weightData,
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59,130,246,.08)',
                borderWidth: 2.5,
                pointRadius: 3,
                tension: .4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 10 }, maxTicksLimit: 15 } },
                y: { grid: { color: '#f1f5f9' }, ticks: { callback: v => v + ' kg' } }
            }
        }
    });

    /* ── Filter mode switcher ── */
    const modeInput = document.getElementById('filterModeInput');
    const modeBtns  = document.querySelectorAll('.filter-mode-btn');
    const allPanels = document.querySelectorAll('.filter-panel');

    function activateMode(mode) {
        modeInput.value = mode;
        modeBtns.forEach(btn => {
            const active = btn.dataset.mode === mode;
            btn.classList.toggle('btn-warning',           active);
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