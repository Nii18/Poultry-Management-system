@extends('layouts.master')

@section('content')
<div class="container-fluid px-4">
    <!-- Page Header -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <div class="d-flex align-items-center gap-3">
                    <div class="page-icon bg-primary-soft">
                        <i class="fas fa-chart-line fs-1 text-primary"></i>
                    </div>
                    <div>
                        <h1 class="page-title mb-1">Farm Analytics</h1>
                        <p class="page-description text-muted mb-0">Comprehensive farm performance insights and trends</p>
                    </div>
                </div>
            </div>
            <div class="col-auto">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Analytics</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
{{-- ── Flexible Date Filter ── --}}
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('analytics.index') }}" id="analyticsFilterForm">

            <div class="d-flex gap-2 mb-3 flex-wrap">
                <span class="fw-semibold text-muted me-2 align-self-center" style="font-size:.8rem;text-transform:uppercase;letter-spacing:.05em;">Filter by:</span>
                <button type="button" class="btn btn-sm filter-mode-btn {{ ($filterMode ?? 'range') === 'range' ? 'btn-primary' : 'btn-outline-secondary' }}" data-mode="range">
                    <i class="fas fa-calendar-range me-1"></i>Date Range
                </button>
                <button type="button" class="btn btn-sm filter-mode-btn {{ ($filterMode ?? '') === 'month' ? 'btn-primary' : 'btn-outline-secondary' }}" data-mode="month">
                    <i class="fas fa-calendar-alt me-1"></i>Month Range
                </button>
                <button type="button" class="btn btn-sm filter-mode-btn {{ ($filterMode ?? '') === 'year' ? 'btn-primary' : 'btn-outline-secondary' }}" data-mode="year">
                    <i class="fas fa-calendar me-1"></i>Year / Quarter
                </button>
            </div>

            <input type="hidden" name="filter_mode" id="filterModeInput" value="{{ $filterMode ?? 'range' }}">

            <div class="row g-3 align-items-end">

                {{-- PANEL: Date Range --}}
                <div id="panel-range" class="col-12 filter-panel">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Start Date</label>
                            <input type="date" name="from_date" class="form-control"
                                   value="{{ $fromDate ?? date('Y-m-01') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">End Date</label>
                            <input type="date" name="to_date" class="form-control"
                                   value="{{ $toDate ?? date('Y-m-d') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Period</label>
                            <select name="period" class="form-select">
                                <option value="daily"     {{ ($period ?? 'daily') === 'daily'     ? 'selected' : '' }}>Daily</option>
                                <option value="weekly"    {{ ($period ?? 'daily') === 'weekly'    ? 'selected' : '' }}>Weekly</option>
                                <option value="monthly"   {{ ($period ?? 'daily') === 'monthly'   ? 'selected' : '' }}>Monthly</option>
                                <option value="quarterly" {{ ($period ?? 'daily') === 'quarterly' ? 'selected' : '' }}>Quarterly</option>
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
                    </div>
                </div>

                {{-- Actions --}}
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-chart-line me-2"></i>Generate
                    </button>
                    <a href="{{ route('analytics.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-redo-alt me-2"></i>Reset
                    </a>
                    <button type="button" class="btn btn-outline-success" onclick="exportAnalytics()">
                        <i class="fas fa-file-excel me-2"></i>Export
                    </button>
                    <button type="button" class="btn btn-outline-info ms-2" onclick="printAnalytics()">
                        <i class="fas fa-print me-2"></i>Print
                    </button>
                </div>

                

            </div>

            {{-- Active period display --}}
            @if(isset($fromDate) || isset($fromMonth) || isset($filterYear))
            <div class="mt-3 pt-2 border-top d-flex align-items-center gap-2 flex-wrap">
                <i class="fas fa-calendar-check text-primary" style="font-size:.85rem;"></i>
                <span style="font-size:.8rem;color:#64748b;">
                    Showing data for:
                    <strong class="text-dark">{{ $fromDate ?? ($fromMonth ?? $filterYear) }} — {{ $toDate ?? ($toMonth ?? $filterYear) }}</strong>
                </span>
            </div>
            @endif

        </form>
    </div>
</div>

    <!-- Key Performance Indicators -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-card-icon bg-success-soft">
                        <i class="fas fa-dollar-sign text-success fs-4"></i>
                    </div>
                    <div class="stat-card-info">
                        <span class="stat-card-label">Total Revenue</span>
                        <h3 class="stat-card-value text-success">${{ number_format($financialSummary['total_revenue'] ?? 0, 2) }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-card-icon bg-danger-soft">
                        <i class="fas fa-credit-card text-danger fs-4"></i>
                    </div>
                    <div class="stat-card-info">
                        <span class="stat-card-label">Total Expenses</span>
                        <h3 class="stat-card-value text-danger">${{ number_format($financialSummary['total_expenses'] ?? 0, 2) }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-card-icon bg-primary-soft">
                        <i class="fas fa-chart-line text-primary fs-4"></i>
                    </div>
                    <div class="stat-card-info">
                        <span class="stat-card-label">Net Profit</span>
                        <h3 class="stat-card-value text-primary">${{ number_format($financialSummary['net_profit'] ?? 0, 2) }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-card-icon bg-info-soft">
                        <i class="fas fa-percent text-info fs-4"></i>
                    </div>
                    <div class="stat-card-info">
                        <span class="stat-card-label">Profit Margin</span>
                        <h3 class="stat-card-value text-info">
                            @php
                                $totalRev = $financialSummary['total_revenue'] ?? 0;
                                $netProf = $financialSummary['net_profit'] ?? 0;
                                $margin = $totalRev > 0 ? ($netProf / $totalRev) * 100 : 0;
                            @endphp
                            {{ number_format($margin, 1) }}%
                        </h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Revenue vs Expenses Chart -->
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0 py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-primary-soft p-2 me-3">
                                <i class="fas fa-chart-line text-primary"></i>
                            </div>
                            <div>
                                <h5 class="card-title mb-0 fw-semibold">Revenue vs Expenses</h5>
                                <small class="text-muted">Financial performance trend</small>
                            </div>
                        </div>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-primary active" onclick="toggleChartType('line')">Line</button>
                            <button type="button" class="btn btn-outline-primary" onclick="toggleChartType('bar')">Bar</button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="revenueExpenseChart" height="300"></canvas>
                </div>
            </div>
        </div>

{{-- ── KPI Cards (₵ fixed) ── --}}
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-card-body">
                <div class="stat-card-icon bg-success-soft">
                    <i class="fas fa-arrow-trend-up text-success fs-4"></i>
                </div>
                <div class="stat-card-info">
                    <span class="stat-card-label">Total Revenue</span>
                    <h3 class="stat-card-value text-success">₵{{ number_format($financialSummary['total_revenue'] ?? 0, 2) }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-card-body">
                <div class="stat-card-icon bg-danger-soft">
                    <i class="fas fa-arrow-trend-down text-danger fs-4"></i>
                </div>
                <div class="stat-card-info">
                    <span class="stat-card-label">Total Expenses</span>
                    <h3 class="stat-card-value text-danger">₵{{ number_format($financialSummary['total_expenses'] ?? 0, 2) }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-card-body">
                <div class="stat-card-icon bg-primary-soft">
                    <i class="fas fa-scale-balanced text-primary fs-4"></i>
                </div>
                <div class="stat-card-info">
                    <span class="stat-card-label">Net Profit</span>
                    <h3 class="stat-card-value text-primary">₵{{ number_format($financialSummary['net_profit'] ?? 0, 2) }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-card-body">
                <div class="stat-card-icon bg-info-soft">
                    <i class="fas fa-percent text-info fs-4"></i>
                </div>
                <div class="stat-card-info">
                    <span class="stat-card-label">Profit Margin</span>
                    <h3 class="stat-card-value text-info">
                        @php
                            $totalRev = $financialSummary['total_revenue'] ?? 0;
                            $netProf  = $financialSummary['net_profit'] ?? 0;
                            $margin   = $totalRev > 0 ? ($netProf / $totalRev) * 100 : 0;
                        @endphp
                        {{ number_format($margin, 1) }}%
                    </h3>
                </div>
            </div>
        </div>
    </div>
</div>

    <!-- REMOVED: Top Expense Categories and Top Revenue Sources sections -->

    <div class="row g-4 mt-2">
        <!-- Production Performance -->
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0 py-3">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-info-soft p-2 me-3">
                            <i class="fas fa-egg text-info"></i>
                        </div>
                        <div>
                            <h5 class="card-title mb-0 fw-semibold">Egg Production</h5>
                            <small class="text-muted">Last 30 days</small>
                        </div>
                    </div>
                </div>
                <div class="card-body text-center">
                    <h2 class="display-4 fw-bold text-info">{{ number_format($eggProduction ?? 0) }}</h2>
                    <p class="text-muted">Total eggs produced</p>
                    <div class="row mt-3">
                        <div class="col-6">
                            <small class="text-muted">Daily Average</small>
                            <h5 class="mb-0">{{ number_format(($eggProduction ?? 0) / 30, 0) }}</h5>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Production Rate</small>
                            <h5 class="mb-0 text-success">{{ number_format($eggProductionRate ?? 0, 1) }}%</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mortality Rate -->
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0 py-3">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-danger-soft p-2 me-3">
                            <i class="fas fa-heartbeat text-danger"></i>
                        </div>
                        <div>
                            <h5 class="card-title mb-0 fw-semibold">Mortality Rate</h5>
                            <small class="text-muted">Animal health indicator</small>
                        </div>
                    </div>
                </div>
                <div class="card-body text-center">
                    <h2 class="display-4 fw-bold text-danger">{{ number_format($mortalityRate ?? 0, 1) }}%</h2>
                    <p class="text-muted">Overall mortality rate</p>
                    <div class="progress mt-3" style="height: 10px;">
                        <div class="progress-bar bg-danger" style="width: {{ min($mortalityRate ?? 0, 100) }}%"></div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-6">
                            <small class="text-muted">Target Rate</small>
                            <h5 class="mb-0 text-success">{{ number_format($targetMortalityRate ?? 5, 1) }}%</h5>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Status</small>
                            <h5 class="mb-0 {{ ($mortalityRate ?? 0) <= ($targetMortalityRate ?? 5) ? 'text-success' : 'text-warning' }}">
                                {{ ($mortalityRate ?? 0) <= ($targetMortalityRate ?? 5) ? 'Good' : 'Alert' }}
                            </h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Feed Conversion Ratio -->
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0 py-3">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-warning-soft p-2 me-3">
                            <i class="fas fa-weight-hanging text-warning"></i>
                        </div>
                        <div>
                            <h5 class="card-title mb-0 fw-semibold">Feed Conversion Ratio</h5>
                            <small class="text-muted">Feed efficiency</small>
                        </div>
                    </div>
                </div>
                <div class="card-body text-center">
                    <h2 class="display-4 fw-bold text-warning">{{ number_format($fcr ?? 0, 2) }}</h2>
                    <p class="text-muted">kg feed / kg gain</p>
                    <div class="row mt-3">
                        <div class="col-6">
                            <small class="text-muted">Industry Avg</small>
                            <h5 class="mb-0">{{ number_format($industryAvgFCR ?? 1.8, 2) }}</h5>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Efficiency</small>
                            <h5 class="mb-0 {{ ($fcr ?? 0) <= ($industryAvgFCR ?? 1.8) ? 'text-success' : 'text-warning' }}">
                                {{ ($fcr ?? 0) <= ($industryAvgFCR ?? 1.8) ? 'Good' : 'Needs Improvement' }}
                            </h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Transactions Table -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0 py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-secondary-soft p-2 me-3">
                                <i class="fas fa-history text-secondary"></i>
                            </div>
                            <div>
                                <h5 class="card-title mb-0 fw-semibold">Recent Transactions</h5>
                                <small class="text-muted">Latest financial activities</small>
                            </div>
                        </div>
                        <a href="{{ route('expenses.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Category</th>
                                    <th>Description</th>
                                    <th class="text-end">Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentTransactions ?? [] as $transaction)
                                <tr>
                                    <td>
                                        @if(isset($transaction->expense_date))
                                            {{ \Carbon\Carbon::parse($transaction->expense_date)->format('d M Y') }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-danger-soft text-danger">
                                            <i class="fas fa-arrow-down me-1"></i> Expense
                                        </span>
                                    </td>
                                    <td>{{ ucfirst($transaction->category ?? 'N/A') }}</td>
                                    <td>{{ $transaction->description ?? 'N/A' }}</td>
                                    <td class="text-end text-danger fw-bold">
                                        -${{ number_format($transaction->amount ?? 0, 2) }}
                                    </td>
                                    <td>
                                        <span class="badge bg-success-soft text-success">
                                            <i class="fas fa-check-circle me-1"></i> Completed
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="empty-state">
                                            <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                                            <h5 class="text-muted">No transactions found</h5>
                                            <p class="text-muted mb-3">Start recording expenses to see data here</p>
                                            <a href="{{ route('expenses.create') }}" class="btn btn-primary">
                                                <i class="fas fa-plus me-2"></i>Add Expense
                                            </a>
                                        </div>
                                    </td>
                                </table>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
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
    }
    
    .bg-primary-soft { background: #e0f2fe; }
    .bg-success-soft { background: #dcfce7; }
    .bg-danger-soft { background: #fee2e2; }
    .bg-warning-soft { background: #fef3c7; }
    .bg-info-soft { background: #d1fae5; }
    .bg-secondary-soft { background: #f1f5f9; }
    
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
        font-size: 1.5rem;
        font-weight: 700;
        margin: 0;
        line-height: 1.2;
        color: #1e293b;
    }
    
    .metrics-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    
    .metric-item {
        padding: 0.5rem 0;
        border-bottom: 1px solid #e2e8f0;
    }
    
    .metric-info {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.25rem;
    }
    
    .metric-label {
        font-size: 0.875rem;
        color: #64748b;
    }
    
    .metric-value {
        font-size: 1rem;
        font-weight: 600;
        color: #1e293b;
    }
    
    .progress {
        background-color: #e2e8f0;
        border-radius: 10px;
        overflow: hidden;
    }
    
    .empty-state {
        text-align: center;
        padding: 2rem;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let revenueExpenseChart = null;
    let currentChartType = 'line';
    
    // Revenue vs Expenses Chart
    const chartLabels = @json($chartLabels ?? []);
    const revenueData = @json($revenueData ?? []);
    const expenseData = @json($expenseData ?? []);
    
    function initChart(type) {
        const ctx = document.getElementById('revenueExpenseChart').getContext('2d');
        
        if (revenueExpenseChart) {
            revenueExpenseChart.destroy();
        }
        
        revenueExpenseChart = new Chart(ctx, {
            type: type,
            data: {
                labels: chartLabels,
                datasets: [
                    {
                        label: 'Revenue',
                        data: revenueData,
                        backgroundColor: 'rgba(25, 135, 84, 0.3)',
                        borderColor: '#198754',
                        borderWidth: 2,
                        borderRadius: 8,
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Expenses',
                        data: expenseData,
                        backgroundColor: 'rgba(220, 53, 69, 0.3)',
                        borderColor: '#dc3545',
                        borderWidth: 2,
                        borderRadius: 8,
                        tension: 0.4,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `${context.dataset.label}: $${context.parsed.y.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
                            }
                        }
                    },
                    legend: {
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Amount ($)',
                            color: '#64748b'
                        },
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            }
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Date',
                            color: '#64748b'
                        }
                    }
                }
            }
        });
    }
    
    function toggleChartType(type) {
        currentChartType = type;
        initChart(type);
        
        document.querySelectorAll('.btn-group .btn').forEach(btn => {
            btn.classList.remove('active');
            if (btn.innerText.toLowerCase() === type) {
                btn.classList.add('active');
            }
        });
    }
    
    // Initialize chart
    if (document.getElementById('revenueExpenseChart')) {
        initChart('line');
    }
    
    function exportAnalytics() {
        window.location.href = '{{ route("analytics.export") }}' + window.location.search;
    }
    
    function printAnalytics() {
        window.print();
    }

    /* ── Filter mode switcher ── */
const modeInput = document.getElementById('filterModeInput');
const modeBtns  = document.querySelectorAll('.filter-mode-btn');
const allPanels = document.querySelectorAll('.filter-panel');

function activateMode(mode) {
    modeInput.value = mode;
    modeBtns.forEach(btn => {
        const active = btn.dataset.mode === mode;
        btn.classList.toggle('btn-primary',           active);
        btn.classList.toggle('btn-outline-secondary', !active);
    });
    allPanels.forEach(p => { p.style.display = 'none'; });
    const target = document.getElementById('panel-' + mode);
    if (target) target.style.display = '';
}

activateMode('{{ $filterMode ?? "range" }}');
modeBtns.forEach(btn => btn.addEventListener('click', () => activateMode(btn.dataset.mode)));
</script>
@endpush
@endsection