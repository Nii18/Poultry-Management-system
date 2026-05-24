@extends('layouts.master')

@section('content')
<div class="container-fluid px-4">

    {{-- ── Page Header ── --}}
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <div class="d-flex align-items-center gap-3">
                    <div class="page-icon bg-primary-soft">
                        <i class="fas fa-file-invoice-dollar fs-1 text-primary"></i>
                    </div>
                    <div>
                        <h1 class="page-title mb-1">Financial Reports</h1>
                        <p class="page-description text-muted mb-0">Comprehensive financial overview and analysis</p>
                    </div>
                </div>
            </div>
            <div class="col-auto">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Financial Reports</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

   {{-- ── Filters ── --}}
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('reports.financial') }}" id="financialFilterForm">

            {{-- Mode Switcher --}}
            <div class="d-flex gap-2 mb-3 flex-wrap">
                <span class="fw-semibold text-muted me-2 align-self-center" style="font-size:.8rem;text-transform:uppercase;letter-spacing:.05em;">Filter by:</span>
                <button type="button" class="btn btn-sm filter-mode-btn {{ ($filterMode ?? 'year') === 'year' ? 'btn-primary' : 'btn-outline-secondary' }}" data-mode="year">
                    <i class="fas fa-calendar me-1"></i>Year / Quarter
                </button>
                <button type="button" class="btn btn-sm filter-mode-btn {{ ($filterMode ?? '') === 'month' ? 'btn-primary' : 'btn-outline-secondary' }}" data-mode="month">
                    <i class="fas fa-calendar-alt me-1"></i>Month Range
                </button>
                <button type="button" class="btn btn-sm filter-mode-btn {{ ($filterMode ?? '') === 'custom' ? 'btn-primary' : 'btn-outline-secondary' }}" data-mode="custom">
                    <i class="fas fa-calendar-range me-1"></i>Custom Dates
                </button>
            </div>

            {{-- Hidden mode field --}}
            <input type="hidden" name="filter_mode" id="filterModeInput" value="{{ $filterMode ?? 'year' }}">

            <div class="row g-3 align-items-end">

                {{-- ── PANEL 1: Year / Quarter ── --}}
                <div id="panel-year" class="col-12 filter-panel">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Year</label>
                            <select name="year" class="form-select">
                                @foreach(range(date('Y'), date('Y') - 5) as $y)
                                    <option value="{{ $y }}" {{ ($year ?? date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">
                                Quarter <span class="text-muted fw-normal">(optional)</span>
                            </label>
                            <select name="quarter" class="form-select">
                                <option value="">Full Year</option>
                                <option value="1" {{ ($quarter ?? '') == '1' ? 'selected' : '' }}>Q1 — Jan to Mar</option>
                                <option value="2" {{ ($quarter ?? '') == '2' ? 'selected' : '' }}>Q2 — Apr to Jun</option>
                                <option value="3" {{ ($quarter ?? '') == '3' ? 'selected' : '' }}>Q3 — Jul to Sep</option>
                                <option value="4" {{ ($quarter ?? '') == '4' ? 'selected' : '' }}>Q4 — Oct to Dec</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- ── PANEL 2: Month Range ── --}}
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
                    </div>
                </div>

                {{-- ── PANEL 3: Custom Date Range ── --}}
                <div id="panel-custom" class="col-12 filter-panel" style="display:none;">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Start Date</label>
                            <input type="date" name="start_date" class="form-control"
                                   value="{{ $customStart ?? date('Y-01-01') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">End Date</label>
                            <input type="date" name="end_date" class="form-control"
                                   value="{{ $customEnd ?? date('Y-m-d') }}">
                        </div>
                    </div>
                </div>

                {{-- ── Actions (always visible) ── --}}
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter me-2"></i>Apply
                    </button>
                    <a href="{{ route('reports.financial') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-redo-alt me-2"></i>Reset
                    </a>
                </div>

            </div>

            {{-- Active period display --}}
            <div class="mt-3 pt-2 border-top d-flex align-items-center gap-2">
                <i class="fas fa-calendar-check text-primary" style="font-size:.85rem;"></i>
                <span style="font-size:.8rem;color:#64748b;">
                    Showing data for:
                    <strong class="text-dark">
                        {{ $startDate->format('d M Y') }} — {{ $endDate->format('d M Y') }}
                    </strong>
                </span>
                @if(($filterMode ?? 'year') === 'year')
                    <span class="badge bg-primary-soft text-primary ms-1">
                        {{ $year }}{{ isset($quarter) && $quarter ? ' · Q'.$quarter : ' · Full Year' }}
                    </span>
                @elseif(($filterMode ?? '') === 'month')
                    <span class="badge bg-info-soft text-info ms-1">Month Range</span>
                @else
                    <span class="badge bg-warning-soft text-warning ms-1">Custom Range</span>
                @endif
            </div>

        </form>
    </div>
</div>

    {{-- ── Summary Stat Cards ── --}}
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="stat-card bg-success-soft">
                <div class="stat-card-body">
                    <div class="stat-card-icon bg-white">
                        <i class="fas fa-arrow-trend-up text-success fs-4"></i>
                    </div>
                    <div class="stat-card-info">
                        <span class="stat-card-label">Total Revenue</span>
                        <h3 class="stat-card-value text-success">₵{{ number_format($totalRevenue, 2) }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card bg-danger-soft">
                <div class="stat-card-body">
                    <div class="stat-card-icon bg-white">
                        <i class="fas fa-arrow-trend-down text-danger fs-4"></i>
                    </div>
                    <div class="stat-card-info">
                        <span class="stat-card-label">Total Expenses</span>
                        <h3 class="stat-card-value text-danger">₵{{ number_format($totalExpenses, 2) }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card {{ $netProfit >= 0 ? 'bg-info-soft' : 'bg-warning-soft' }}">
                <div class="stat-card-body">
                    <div class="stat-card-icon bg-white">
                        <i class="fas fa-scale-balanced {{ $netProfit >= 0 ? 'text-info' : 'text-warning' }} fs-4"></i>
                    </div>
                    <div class="stat-card-info">
                        <span class="stat-card-label">Net {{ $netProfit >= 0 ? 'Profit' : 'Loss' }}</span>
                        <h3 class="stat-card-value {{ $netProfit >= 0 ? 'text-info' : 'text-warning' }}">
                            ₵{{ number_format(abs($netProfit), 2) }}
                        </h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card bg-primary-soft">
                <div class="stat-card-body">
                    <div class="stat-card-icon bg-white">
                        <i class="fas fa-percent text-primary fs-4"></i>
                    </div>
                    <div class="stat-card-info">
                        <span class="stat-card-label">Profit Margin</span>
                        <h3 class="stat-card-value text-primary">{{ $profitMargin }}%</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── 2×2 Charts Grid ── --}}
    <div class="row g-4">

        {{-- CHART 1: Monthly Revenue vs Expenses (Line) --}}
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0 py-3 d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="card-title mb-0 fw-semibold">
                            <i class="fas fa-chart-line text-primary me-2"></i>Revenue vs Expenses
                        </h5>
                        <p class="text-muted mb-0" style="font-size:.78rem;">Month-by-month trend for {{ $year }}</p>
                    </div>
                    <span class="badge bg-primary-soft text-primary px-3 py-2">{{ $year }}</span>
                </div>
                <div class="card-body">
                    <canvas id="trendChart" height="260"></canvas>
                </div>
                <div class="card-footer bg-white border-0 d-flex gap-4 pb-3 pt-0 px-4">
                    <div class="d-flex align-items-center gap-2">
                        <span class="legend-swatch" style="background:#10b981;"></span>
                        <span class="legend-label">Revenue</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="legend-swatch" style="background:#ef4444;"></span>
                        <span class="legend-label">Expenses</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="legend-swatch" style="background:#6366f1;border-style:dashed;"></span>
                        <span class="legend-label">Net Profit</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- CHART 2: Expense Breakdown Doughnut --}}
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0 py-3 d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="card-title mb-0 fw-semibold">
                            <i class="fas fa-chart-pie text-danger me-2"></i>Expense Breakdown
                        </h5>
                        <p class="text-muted mb-0" style="font-size:.78rem;">Spend distribution by category</p>
                    </div>
                    <span class="badge bg-danger-soft text-danger px-3 py-2">₵{{ number_format($totalExpenses, 2) }}</span>
                </div>
                <div class="card-body">
                    <div class="row align-items-center g-0">
                        <div class="col-6">
                            <canvas id="expensePieChart" height="240"></canvas>
                        </div>
                        <div class="col-6 ps-3">
                            <div id="expenseLegend" class="d-flex flex-column gap-2"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- CHART 3: Monthly Net Profit/Loss Bar --}}
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0 py-3 d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="card-title mb-0 fw-semibold">
                            <i class="fas fa-chart-bar me-2" style="color:#6366f1;"></i>Monthly Net Profit / Loss
                        </h5>
                        <p class="text-muted mb-0" style="font-size:.78rem;">Green = profit &middot; Red = loss per month</p>
                    </div>
                    <span class="badge px-3 py-2 {{ $netProfit >= 0 ? 'bg-success-soft text-success' : 'bg-danger-soft text-danger' }}">
                        {{ $netProfit >= 0 ? 'Profitable' : 'Loss-making' }}
                    </span>
                </div>
                <div class="card-body">
                    <canvas id="profitBarChart" height="260"></canvas>
                </div>
            </div>
        </div>

        {{-- CHART 4: Revenue by Flock/Source Doughnut --}}
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0 py-3 d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="card-title mb-0 fw-semibold">
                            <i class="fas fa-layer-group text-success me-2"></i>Revenue by Source
                        </h5>
                        <p class="text-muted mb-0" style="font-size:.78rem;">Income split by flock</p>
                    </div>
                    <span class="badge bg-success-soft text-success px-3 py-2">₵{{ number_format($totalRevenue, 2) }}</span>
                </div>
                <div class="card-body">
                    <div class="row align-items-center g-0">
                        <div class="col-6">
                            <canvas id="revenueSourceChart" height="240"></canvas>
                        </div>
                        <div class="col-6 ps-3">
                            <div id="revenueLegend" class="d-flex flex-column gap-2"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>{{-- end charts row --}}
</div>{{-- end container-fluid --}}
@endsection

@push('styles')
<style>
    .page-header { margin-bottom: 1.5rem; }
    .page-icon {
        width: 50px; height: 50px;
        display: flex; align-items: center; justify-content: center;
        border-radius: 12px;
    }
    .bg-primary-soft  { background: #eff6ff !important; }
    .bg-success-soft  { background: #f0fdf4 !important; }
    .bg-danger-soft   { background: #fef2f2 !important; }
    .bg-warning-soft  { background: #fffbeb !important; }
    .bg-info-soft     { background: #f0fdfa !important; }

    .stat-card { border-radius: 16px; padding: 1.1rem; border: 1px solid #e2e8f0; background: #fff; }
    .stat-card-body { display: flex; align-items: center; gap: 1rem; }
    .stat-card-icon {
        width: 52px; height: 52px; display: flex; align-items: center;
        justify-content: center; border-radius: 14px; flex-shrink: 0;
    }
    .stat-card-info { flex: 1; }
    .stat-card-label {
        font-size: .72rem; text-transform: uppercase; letter-spacing: .6px;
        color: #64748b; font-weight: 700; display: block; margin-bottom: .2rem;
    }
    .stat-card-value { font-size: 1.45rem; font-weight: 800; margin: 0; line-height: 1.15; }

    .card { border-radius: 16px; }
    .card-header { border-radius: 16px 16px 0 0 !important; }

    /* legend helpers */
    .legend-swatch {
        width: 12px; height: 12px; border-radius: 3px;
        display: inline-block; flex-shrink: 0;
    }
    .legend-label { font-size: .78rem; color: #64748b; font-weight: 600; }
    .legend-dot   { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; display: inline-block; }
    .legend-name  { font-size: .75rem; color: #374151; font-weight: 600; }
    .legend-amt   { font-size: .72rem; font-weight: 700; color: #111827; }
    .legend-pct   { font-size: .72rem; color: #94a3b8; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    /* ── PHP → JS data ── */
    const MONTHS = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

    const rawMonthlyExpenses = @json($monthlyExpenses);   // [{month, total}]
    const rawMonthlySales    = @json($monthlySales);       // [{month, total}]
    const rawCategories      = @json($expensesByCategory); // [{category, total}]
    const rawRevSources      = @json($revenueBySpecies);   // [{flock_id, total_revenue, flock?}]

    const totalRevenue  = {{ (float) $totalRevenue }};
    const totalExpenses = {{ (float) $totalExpenses }};

    /* ── helpers ── */
    function monthArr(raw, key = 'total') {
        const a = new Array(12).fill(0);
        raw.forEach(r => { a[parseInt(r.month) - 1] = parseFloat(r[key]) || 0; });
        return a;
    }

    function cedis(n) {
        return '₵' + n.toLocaleString('en-GH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    const expByMonth  = monthArr(rawMonthlyExpenses);
    const revByMonth  = monthArr(rawMonthlySales);
    const profByMonth = revByMonth.map((r, i) => +(r - expByMonth[i]).toFixed(2));

    const RING = ['#ef4444','#f97316','#eab308','#22c55e','#14b8a6','#3b82f6','#8b5cf6','#ec4899','#64748b'];
    const SRC  = ['#10b981','#3b82f6','#f59e0b','#8b5cf6','#ef4444','#14b8a6','#f97316','#ec4899'];

    /* ══════════════════════════════════════════════
       CHART 1 — Revenue vs Expenses (Line)
    ══════════════════════════════════════════════ */
    new Chart(document.getElementById('trendChart'), {
        type: 'line',
        data: {
            labels: MONTHS,
            datasets: [
                {
                    label: 'Revenue',
                    data: revByMonth,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16,185,129,.09)',
                    borderWidth: 2.5,
                    pointBackgroundColor: '#10b981',
                    pointRadius: 4, pointHoverRadius: 6,
                    fill: true, tension: .4
                },
                {
                    label: 'Expenses',
                    data: expByMonth,
                    borderColor: '#ef4444',
                    backgroundColor: 'rgba(239,68,68,.07)',
                    borderWidth: 2.5,
                    pointBackgroundColor: '#ef4444',
                    pointRadius: 4, pointHoverRadius: 6,
                    fill: true, tension: .4
                },
                {
                    label: 'Net Profit',
                    data: profByMonth,
                    borderColor: '#6366f1',
                    backgroundColor: 'transparent',
                    borderWidth: 2,
                    borderDash: [5, 4],
                    pointBackgroundColor: '#6366f1',
                    pointRadius: 3, pointHoverRadius: 5,
                    fill: false, tension: .4
                }
            ]
        },
        options: {
            responsive: true,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ` ${ctx.dataset.label}: ${cedis(ctx.parsed.y)}`
                    }
                }
            },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 11 } } },
                y: {
                    grid: { color: '#f1f5f9' },
                    ticks: {
                        font: { size: 11 },
                        callback: v => v >= 1000 ? '₵' + (v/1000).toFixed(1) + 'k' : '₵' + v
                    }
                }
            }
        }
    });

    /* ══════════════════════════════════════════════
       CHART 2 — Expense Doughnut
    ══════════════════════════════════════════════ */
    const catLabels = rawCategories.map(c => c.category.charAt(0).toUpperCase() + c.category.slice(1));
    const catAmts   = rawCategories.map(c => parseFloat(c.total) || 0);
    const catColors = RING.slice(0, catLabels.length);

    if (catLabels.length) {
        new Chart(document.getElementById('expensePieChart'), {
            type: 'doughnut',
            data: {
                labels: catLabels,
                datasets: [{ data: catAmts, backgroundColor: catColors, borderWidth: 2, borderColor: '#fff', hoverOffset: 6 }]
            },
            options: {
                responsive: true,
                cutout: '68%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => {
                                const pct = totalExpenses > 0 ? ((ctx.raw / totalExpenses) * 100).toFixed(1) : 0;
                                return ` ${cedis(ctx.raw)} (${pct}%)`;
                            }
                        }
                    }
                }
            }
        });

        const expLegend = document.getElementById('expenseLegend');
        catLabels.forEach((lbl, i) => {
            const pct = totalExpenses > 0 ? ((catAmts[i] / totalExpenses) * 100).toFixed(1) : 0;
            expLegend.innerHTML += `
                <div class="d-flex align-items-start gap-2">
                    <span class="legend-dot mt-1" style="background:${catColors[i]};"></span>
                    <div>
                        <div class="legend-name">${lbl}</div>
                        <div class="d-flex gap-1 flex-wrap">
                            <span class="legend-amt">${cedis(catAmts[i])}</span>
                            <span class="legend-pct">${pct}%</span>
                        </div>
                    </div>
                </div>`;
        });
    } else {
        document.getElementById('expenseLegend').innerHTML = '<p class="text-muted small">No expense data for this period.</p>';
        document.getElementById('expensePieChart').closest('.col-6').innerHTML = '<div class="d-flex align-items-center justify-content-center h-100 text-muted"><i class="fas fa-chart-pie fa-3x opacity-25"></i></div>';
    }

    /* ══════════════════════════════════════════════
       CHART 3 — Monthly Profit/Loss Bar
    ══════════════════════════════════════════════ */
    new Chart(document.getElementById('profitBarChart'), {
        type: 'bar',
        data: {
            labels: MONTHS,
            datasets: [{
                label: 'Net Profit / Loss',
                data: profByMonth,
                backgroundColor: profByMonth.map(v => v >= 0 ? 'rgba(16,185,129,.75)' : 'rgba(239,68,68,.75)'),
                borderColor:     profByMonth.map(v => v >= 0 ? '#10b981' : '#ef4444'),
                borderWidth: 1.5,
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => {
                            const sign = ctx.parsed.y >= 0 ? '▲ Profit' : '▼ Loss';
                            return ` ${sign}: ${cedis(Math.abs(ctx.parsed.y))}`;
                        }
                    }
                }
            },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 11 } } },
                y: {
                    grid: { color: '#f1f5f9' },
                    ticks: {
                        font: { size: 11 },
                        callback: v => {
                            const abs = Math.abs(v);
                            return (v < 0 ? '-' : '') + '₵' + (abs >= 1000 ? (abs/1000).toFixed(1) + 'k' : abs);
                        }
                    }
                }
            }
        }
    });

    /* ══════════════════════════════════════════════
       CHART 4 — Revenue by Source Doughnut
    ══════════════════════════════════════════════ */
    const srcLabels = rawRevSources.map((r, i) => {
        if (r.flock && r.flock.flock_number) return 'Flock ' + r.flock.flock_number;
        if (r.flock_id)                       return 'Flock #' + r.flock_id;
        return 'General Sales';
    });
    const srcAmts   = rawRevSources.map(r => parseFloat(r.total_revenue) || 0);
    const srcColors = SRC.slice(0, srcLabels.length);

    // Fallback if no breakdown
    const finalLabels = srcLabels.length ? srcLabels : ['All Sales'];
    const finalAmts   = srcAmts.length   ? srcAmts   : [totalRevenue];
    const finalColors = srcColors.length ? srcColors : ['#10b981'];

    if (totalRevenue > 0) {
        new Chart(document.getElementById('revenueSourceChart'), {
            type: 'doughnut',
            data: {
                labels: finalLabels,
                datasets: [{
                    data: finalAmts,
                    backgroundColor: finalColors,
                    borderWidth: 2, borderColor: '#fff', hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                cutout: '68%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => {
                                const pct = totalRevenue > 0 ? ((ctx.raw / totalRevenue) * 100).toFixed(1) : 0;
                                return ` ${cedis(ctx.raw)} (${pct}%)`;
                            }
                        }
                    }
                }
            }
        });

        const revLegend = document.getElementById('revenueLegend');
        finalLabels.forEach((lbl, i) => {
            const pct = totalRevenue > 0 ? ((finalAmts[i] / totalRevenue) * 100).toFixed(1) : 0;
            revLegend.innerHTML += `
                <div class="d-flex align-items-start gap-2">
                    <span class="legend-dot mt-1" style="background:${finalColors[i]};"></span>
                    <div>
                        <div class="legend-name">${lbl}</div>
                        <div class="d-flex gap-1 flex-wrap">
                            <span class="legend-amt">${cedis(finalAmts[i])}</span>
                            <span class="legend-pct">${pct}%</span>
                        </div>
                    </div>
                </div>`;
        });
    } else {
        document.getElementById('revenueLegend').innerHTML = '<p class="text-muted small">No revenue data for this period.</p>';
        document.getElementById('revenueSourceChart').closest('.col-6').innerHTML = '<div class="d-flex align-items-center justify-content-center h-100 text-muted"><i class="fas fa-chart-pie fa-3x opacity-25"></i></div>';
    }

});

/* ── Filter mode switcher ── */
const modeInput  = document.getElementById('filterModeInput');
const modeBtns   = document.querySelectorAll('.filter-mode-btn');
const allPanels  = document.querySelectorAll('.filter-panel');

function activateMode(mode) {
    modeInput.value = mode;

    // Update button styles
    modeBtns.forEach(btn => {
        const active = btn.dataset.mode === mode;
        btn.classList.toggle('btn-primary',          active);
        btn.classList.toggle('btn-outline-secondary', !active);
    });

    // Show/hide panels
    allPanels.forEach(p => { p.style.display = 'none'; });
    const target = document.getElementById('panel-' + mode);
    if (target) target.style.display = '';
}

// Init on load using server-side mode
activateMode('{{ $filterMode ?? 'year' }}');

// Wire buttons
modeBtns.forEach(btn => {
    btn.addEventListener('click', () => activateMode(btn.dataset.mode));
});
</script>
@endpush