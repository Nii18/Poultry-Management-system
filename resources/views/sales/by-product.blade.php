{{-- resources/views/sales/by-product.blade.php --}}
@extends('layouts.master')

@section('content')
<div class="container-fluid px-4 py-2">

    {{-- ── Page Header ── --}}
    <div class="bp-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <div class="d-flex align-items-center gap-3">
                    <div class="bp-icon-wrap">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                    <div>
                        <h1 class="bp-title mb-0">Sales by Product</h1>
                        <p class="bp-subtitle mb-0">Revenue breakdown &amp; profit analysis &mdash; {{ $year }}</p>
                    </div>
                </div>
            </div>
            <div class="col-auto d-flex align-items-center gap-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 bp-breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('sales.index') }}">Sales</a></li>
                        <li class="breadcrumb-item active">By Product</li>
                    </ol>
                </nav>
                <a href="{{ route('sales.index') }}" class="bp-back-btn">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>
    </div>

    {{-- ── Year Filter ── --}}
    <div class="bp-filter-bar mb-4">
        <form method="GET" class="d-flex align-items-center gap-3">
            <label class="bp-filter-label"><i class="fas fa-calendar-alt me-2"></i>Viewing Year</label>
            <select name="year" class="bp-year-select" onchange="this.form.submit()">
                @for($y = 2020; $y <= date('Y'); $y++)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
            <div class="bp-filter-divider"></div>
            <span class="bp-filter-note">
                <i class="fas fa-info-circle me-1"></i>Click any stat card to view details
            </span>
        </form>
    </div>

    {{-- ── KPI Stat Cards (clickable) ── --}}
    <div class="row g-4 mb-5">
        {{-- Revenue --}}
        <div class="col-md-4">
            <div class="bp-kpi-card bp-kpi-revenue"
                 data-type="revenue"
                 data-value="{{ $totalRevenue }}"
                 data-year="{{ $year }}"
                 onclick="openKpiModal('revenue')"
                 role="button" tabindex="0"
                 aria-label="View revenue details">
                <div class="bp-kpi-glow bp-glow-green"></div>
                <div class="bp-kpi-inner">
                    <div class="bp-kpi-icon-wrap">
                        <div class="bp-kpi-icon bp-icon-green">
                            <i class="fas fa-arrow-trend-up"></i>
                        </div>
                        <span class="bp-kpi-badge">Click for details</span>
                    </div>
                    <div class="bp-kpi-body">
                        <span class="bp-kpi-label">Total Revenue</span>
                        <h2 class="bp-kpi-value text-success">₵{{ number_format($totalRevenue, 2) }}</h2>
                        <div class="bp-kpi-meta">
                            <span class="bp-kpi-sub">
                                <i class="fas fa-boxes-stacking me-1"></i>
                                {{ $salesByProduct->count() }} product{{ $salesByProduct->count() != 1 ? 's' : '' }} sold
                            </span>
                        </div>
                    </div>
                </div>
                <div class="bp-kpi-ripple"></div>
            </div>
        </div>

        {{-- Expenses --}}
        <div class="col-md-4">
            <div class="bp-kpi-card bp-kpi-expense"
                 data-type="expenses"
                 data-value="{{ $totalExpenses }}"
                 data-year="{{ $year }}"
                 onclick="openKpiModal('expenses')"
                 role="button" tabindex="0"
                 aria-label="View expenses details">
                <div class="bp-kpi-glow bp-glow-red"></div>
                <div class="bp-kpi-inner">
                    <div class="bp-kpi-icon-wrap">
                        <div class="bp-kpi-icon bp-icon-red">
                            <i class="fas fa-receipt"></i>
                        </div>
                        <span class="bp-kpi-badge">Click for details</span>
                    </div>
                    <div class="bp-kpi-body">
                        <span class="bp-kpi-label">Total Expenses</span>
                        <h2 class="bp-kpi-value text-danger">₵{{ number_format($totalExpenses, 2) }}</h2>
                        <div class="bp-kpi-meta">
                            <span class="bp-kpi-sub">
                                <i class="fas fa-percent me-1"></i>
                                @php $expRatio = $totalRevenue > 0 ? ($totalExpenses/$totalRevenue)*100 : 0; @endphp
                                {{ number_format($expRatio, 1) }}% of revenue
                            </span>
                        </div>
                    </div>
                </div>
                <div class="bp-kpi-ripple"></div>
            </div>
        </div>

        {{-- Net Profit --}}
        <div class="col-md-4">
            <div class="bp-kpi-card {{ $netProfit >= 0 ? 'bp-kpi-profit' : 'bp-kpi-loss' }}"
                 data-type="profit"
                 data-value="{{ $netProfit }}"
                 data-year="{{ $year }}"
                 onclick="openKpiModal('profit')"
                 role="button" tabindex="0"
                 aria-label="View profit details">
                <div class="bp-kpi-glow {{ $netProfit >= 0 ? 'bp-glow-blue' : 'bp-glow-amber' }}"></div>
                <div class="bp-kpi-inner">
                    <div class="bp-kpi-icon-wrap">
                        <div class="bp-kpi-icon {{ $netProfit >= 0 ? 'bp-icon-blue' : 'bp-icon-amber' }}">
                            <i class="fas {{ $netProfit >= 0 ? 'fa-chart-simple' : 'fa-triangle-exclamation' }}"></i>
                        </div>
                        <span class="bp-kpi-badge">Click for details</span>
                    </div>
                    <div class="bp-kpi-body">
                        <span class="bp-kpi-label">Net Profit</span>
                        <h2 class="bp-kpi-value {{ $netProfit >= 0 ? 'text-primary' : 'text-warning' }}">
                            ₵{{ number_format(abs($netProfit), 2) }}
                        </h2>
                        <div class="bp-kpi-meta">
                            <span class="bp-kpi-pill {{ $netProfit >= 0 ? 'bp-pill-profit' : 'bp-pill-loss' }}">
                                <i class="fas {{ $netProfit >= 0 ? 'fa-check-circle' : 'fa-exclamation-circle' }} me-1"></i>
                                {{ $netProfit >= 0 ? 'Profitable' : 'Net Loss' }}
                            </span>
                            @php $margin = $totalRevenue > 0 ? ($netProfit/$totalRevenue)*100 : 0; @endphp
                            <span class="bp-kpi-sub ms-2">{{ number_format($margin, 1) }}% margin</span>
                        </div>
                    </div>
                </div>
                <div class="bp-kpi-ripple"></div>
            </div>
        </div>
    </div>

    {{-- ── Main Content Grid ── --}}
    <div class="row g-4 mb-4">

        {{-- Product Breakdown Table --}}
        <div class="col-xl-6">
            <div class="bp-panel h-100">
                <div class="bp-panel-header">
                    <div class="bp-panel-title-group">
                        <div class="bp-panel-icon bp-panel-icon-green">
                            <i class="fas fa-chart-pie"></i>
                        </div>
                        <div>
                            <h5 class="bp-panel-title">Sales by Product Type</h5>
                            <p class="bp-panel-subtitle">Revenue per category in {{ $year }}</p>
                        </div>
                    </div>
                    <span class="bp-panel-count">{{ $salesByProduct->count() }} products</span>
                </div>
                <div class="bp-panel-body">
                    @php
                        $productMeta = [
                            'eggs_tray'      => ['icon' => '🥚', 'label' => 'Eggs (Tray)',      'color' => '#f59e0b'],
                            'eggs_crate'     => ['icon' => '📦', 'label' => 'Eggs (Crate)',     'color' => '#f97316'],
                            'eggs_box'       => ['icon' => '📦', 'label' => 'Eggs (Box)',        'color' => '#fb923c'],
                            'live_bird'      => ['icon' => '🐓', 'label' => 'Live Bird',         'color' => '#10b981'],
                            'meat_kg'        => ['icon' => '🍗', 'label' => 'Meat (kg)',         'color' => '#ef4444'],
                            'breeding_stock' => ['icon' => '🧬', 'label' => 'Breeding Stock',   'color' => '#8b5cf6'],
                            'manure'         => ['icon' => '💩', 'label' => 'Manure',           'color' => '#78716c'],
                            'other'          => ['icon' => '📦', 'label' => 'Other',            'color' => '#64748b'],
                        ];
                        $colorPalette = ['#10b981','#3b82f6','#f59e0b','#ef4444','#8b5cf6','#f97316','#06b6d4','#78716c'];
                        $ci = 0;
                    @endphp

                    @forelse($salesByProduct as $sale)
                        @php
                            $pct = $totalRevenue > 0 ? ($sale->total_revenue / $totalRevenue) * 100 : 0;
                            $meta = $productMeta[$sale->product_type] ?? ['icon' => '📦', 'label' => ucfirst(str_replace('_',' ',$sale->product_type)), 'color' => '#64748b'];
                            $barColor = $meta['color'];
                        @endphp
                        <div class="bp-product-row">
                            <div class="bp-product-left">
                                <span class="bp-product-emoji">{{ $meta['icon'] }}</span>
                                <div>
                                    <div class="bp-product-name">{{ $meta['label'] }}</div>
                                    <div class="bp-product-qty">{{ number_format($sale->total_quantity, 2) }} units</div>
                                </div>
                            </div>
                            <div class="bp-product-right">
                                <div class="bp-product-bar-wrap">
                                    <div class="bp-product-bar" style="width:{{ $pct }}%; background:{{ $barColor }}"></div>
                                </div>
                                <div class="bp-product-stats">
                                    <span class="bp-product-revenue">₵{{ number_format($sale->total_revenue, 2) }}</span>
                                    <span class="bp-product-pct">{{ number_format($pct, 1) }}%</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="bp-empty">
                            <i class="fas fa-box-open"></i>
                            <p>No sales data for {{ $year }}</p>
                        </div>
                    @endforelse

                    @if($salesByProduct->count())
                    <div class="bp-product-total-row">
                        <span>Total</span>
                        <span class="bp-product-total-qty">{{ number_format($salesByProduct->sum('total_quantity'), 2) }} units</span>
                        <span class="bp-product-total-rev">₵{{ number_format($totalRevenue, 2) }}</span>
                        <span class="bp-product-total-pct">100%</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Monthly Bar Chart --}}
        <div class="col-xl-6">
            <div class="bp-panel h-100">
                <div class="bp-panel-header">
                    <div class="bp-panel-title-group">
                        <div class="bp-panel-icon bp-panel-icon-blue">
                            <i class="fas fa-chart-column"></i>
                        </div>
                        <div>
                            <h5 class="bp-panel-title">Monthly Revenue</h5>
                            <p class="bp-panel-subtitle">Month-by-month breakdown for {{ $year }}</p>
                        </div>
                    </div>
                </div>
                <div class="bp-panel-body">
                    <canvas id="monthlyChart" style="max-height:320px"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Revenue vs Expenses Panel ── --}}
    <div class="row g-4">
        <div class="col-xl-8">
            <div class="bp-panel">
                <div class="bp-panel-header">
                    <div class="bp-panel-title-group">
                        <div class="bp-panel-icon bp-panel-icon-purple">
                            <i class="fas fa-scale-balanced"></i>
                        </div>
                        <div>
                            <h5 class="bp-panel-title">Revenue vs Expenses</h5>
                            <p class="bp-panel-subtitle">Full-year comparison for {{ $year }}</p>
                        </div>
                    </div>
                </div>
                <div class="bp-panel-body">
                    <canvas id="comparisonChart" style="max-height:280px"></canvas>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="bp-panel h-100">
                <div class="bp-panel-header">
                    <div class="bp-panel-title-group">
                        <div class="bp-panel-icon bp-panel-icon-amber">
                            <i class="fas fa-calculator"></i>
                        </div>
                        <div>
                            <h5 class="bp-panel-title">P&amp;L Summary</h5>
                            <p class="bp-panel-subtitle">{{ $year }} financial result</p>
                        </div>
                    </div>
                </div>
                <div class="bp-panel-body d-flex flex-column gap-3 justify-content-center h-100 py-2">
                    <div class="bp-summary-block bp-sb-green">
                        <div class="bp-sb-label">Revenue</div>
                        <div class="bp-sb-value">₵{{ number_format($totalRevenue, 2) }}</div>
                        <div class="bp-sb-bar"><div style="width:100%"></div></div>
                    </div>
                    <div class="bp-summary-block bp-sb-red">
                        <div class="bp-sb-label">Expenses</div>
                        <div class="bp-sb-value">₵{{ number_format($totalExpenses, 2) }}</div>
                        @php $expWidth = $totalRevenue > 0 ? min(($totalExpenses/$totalRevenue)*100, 100) : 0; @endphp
                        <div class="bp-sb-bar"><div style="width:{{ $expWidth }}%"></div></div>
                    </div>
                    <div class="bp-summary-divider"></div>
                    <div class="bp-summary-block {{ $netProfit >= 0 ? 'bp-sb-blue' : 'bp-sb-amber' }}">
                        <div class="bp-sb-label">Net {{ $netProfit >= 0 ? 'Profit' : 'Loss' }}</div>
                        <div class="bp-sb-value">₵{{ number_format(abs($netProfit), 2) }}</div>
                        @php $profWidth = $totalRevenue > 0 ? min((abs($netProfit)/$totalRevenue)*100, 100) : 0; @endphp
                        <div class="bp-sb-bar"><div style="width:{{ $profWidth }}%"></div></div>
                    </div>
                    <div class="bp-profit-badge {{ $netProfit >= 0 ? 'bp-badge-profit' : 'bp-badge-loss' }}">
                        <i class="fas {{ $netProfit >= 0 ? 'fa-check-circle' : 'fa-exclamation-triangle' }} me-2"></i>
                        {{ $netProfit >= 0 ? 'Farm is profitable this year' : 'Farm is at a loss this year' }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


{{-- ══════════════════════════════════════════════════════
     KPI Detail Modals
═══════════════════════════════════════════════════════ --}}

{{-- Revenue Modal --}}
<div class="modal fade bp-modal" id="revenueModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content bp-mc">
            <div class="bp-mc-header bp-mh-green">
                <div class="d-flex align-items-center gap-3">
                    <div class="bp-mh-icon"><i class="fas fa-arrow-trend-up"></i></div>
                    <div>
                        <h5 class="bp-mh-title mb-0">Revenue Details</h5>
                        <p class="bp-mh-sub mb-0">Sales income breakdown for {{ $year }}</p>
                    </div>
                </div>
                <button type="button" class="bp-close-btn" data-bs-dismiss="modal"><i class="fas fa-xmark"></i></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3 mb-4">
                    <div class="col-4">
                        <div class="bp-modal-stat bp-ms-green">
                            <div class="bp-ms-label">Total Revenue</div>
                            <div class="bp-ms-value">₵{{ number_format($totalRevenue, 2) }}</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="bp-modal-stat bp-ms-blue">
                            <div class="bp-ms-label">Products Sold</div>
                            <div class="bp-ms-value">{{ $salesByProduct->count() }}</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="bp-modal-stat bp-ms-purple">
                            <div class="bp-ms-label">Avg per Product</div>
                            <div class="bp-ms-value">₵{{ $salesByProduct->count() > 0 ? number_format($totalRevenue / $salesByProduct->count(), 2) : '0.00' }}</div>
                        </div>
                    </div>
                </div>
                <h6 class="bp-modal-section-title">Revenue by Product</h6>
                <div class="bp-modal-table-wrap">
                    <table class="bp-modal-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Qty Sold</th>
                                <th>Revenue</th>
                                <th>Share</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($salesByProduct as $sale)
                                @php
                                    $pct = $totalRevenue > 0 ? ($sale->total_revenue / $totalRevenue) * 100 : 0;
                                    $meta = $productMeta[$sale->product_type] ?? ['icon' => '📦', 'label' => ucfirst(str_replace('_',' ',$sale->product_type)), 'color' => '#64748b'];
                                @endphp
                                <tr>
                                    <td><span class="me-2">{{ $meta['icon'] }}</span>{{ $meta['label'] }}</td>
                                    <td>{{ number_format($sale->total_quantity, 2) }}</td>
                                    <td class="fw-semibold text-success">₵{{ number_format($sale->total_revenue, 2) }}</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="bp-mini-bar-wrap">
                                                <div class="bp-mini-bar" style="width:{{ $pct }}%; background:{{ $meta['color'] }}"></div>
                                            </div>
                                            <span class="small fw-medium">{{ number_format($pct, 1) }}%</span>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-3">No sales data</td></tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>Total</th>
                                <th>{{ number_format($salesByProduct->sum('total_quantity'), 2) }}</th>
                                <th class="text-success">₵{{ number_format($totalRevenue, 2) }}</th>
                                <th>100%</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="bp-modal-chart-wrap mt-4">
                    <canvas id="revenueModalChart" height="180"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Expenses Modal --}}
<div class="modal fade bp-modal" id="expensesModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bp-mc">
            <div class="bp-mc-header bp-mh-red">
                <div class="d-flex align-items-center gap-3">
                    <div class="bp-mh-icon"><i class="fas fa-receipt"></i></div>
                    <div>
                        <h5 class="bp-mh-title mb-0">Expense Details</h5>
                        <p class="bp-mh-sub mb-0">Cost breakdown for {{ $year }}</p>
                    </div>
                </div>
                <button type="button" class="bp-close-btn" data-bs-dismiss="modal"><i class="fas fa-xmark"></i></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3 mb-4">
                    <div class="col-6">
                        <div class="bp-modal-stat bp-ms-red">
                            <div class="bp-ms-label">Total Expenses</div>
                            <div class="bp-ms-value">₵{{ number_format($totalExpenses, 2) }}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="bp-modal-stat bp-ms-amber">
                            <div class="bp-ms-label">Expense Ratio</div>
                            <div class="bp-ms-value">{{ $totalRevenue > 0 ? number_format(($totalExpenses/$totalRevenue)*100, 1) : '0.0' }}%</div>
                        </div>
                    </div>
                </div>
                <div class="bp-info-box">
                    <i class="fas fa-info-circle me-2 text-primary"></i>
                    Expenses represent all costs incurred against revenue of <strong>₵{{ number_format($totalRevenue, 2) }}</strong> in {{ $year }}.
                    @if($netProfit >= 0)
                        The farm retained <strong class="text-success">₵{{ number_format($netProfit, 2) }}</strong> after expenses.
                    @else
                        Expenses exceeded revenue by <strong class="text-danger">₵{{ number_format(abs($netProfit), 2) }}</strong>.
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Profit Modal --}}
<div class="modal fade bp-modal" id="profitModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bp-mc">
            <div class="bp-mc-header {{ $netProfit >= 0 ? 'bp-mh-blue' : 'bp-mh-amber' }}">
                <div class="d-flex align-items-center gap-3">
                    <div class="bp-mh-icon">
                        <i class="fas {{ $netProfit >= 0 ? 'fa-chart-simple' : 'fa-triangle-exclamation' }}"></i>
                    </div>
                    <div>
                        <h5 class="bp-mh-title mb-0">Net {{ $netProfit >= 0 ? 'Profit' : 'Loss' }} Details</h5>
                        <p class="bp-mh-sub mb-0">Profitability analysis for {{ $year }}</p>
                    </div>
                </div>
                <button type="button" class="bp-close-btn" data-bs-dismiss="modal"><i class="fas fa-xmark"></i></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3 mb-4">
                    <div class="col-4">
                        <div class="bp-modal-stat bp-ms-green">
                            <div class="bp-ms-label">Revenue</div>
                            <div class="bp-ms-value">₵{{ number_format($totalRevenue, 2) }}</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="bp-modal-stat bp-ms-red">
                            <div class="bp-ms-label">Expenses</div>
                            <div class="bp-ms-value">₵{{ number_format($totalExpenses, 2) }}</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="bp-modal-stat {{ $netProfit >= 0 ? 'bp-ms-blue' : 'bp-ms-amber' }}">
                            <div class="bp-ms-label">Net {{ $netProfit >= 0 ? 'Profit' : 'Loss' }}</div>
                            <div class="bp-ms-value">₵{{ number_format(abs($netProfit), 2) }}</div>
                        </div>
                    </div>
                </div>
                <div class="bp-profit-breakdown">
                    <div class="bp-pb-row">
                        <span class="bp-pb-label">Profit Margin</span>
                        <span class="bp-pb-value {{ $netProfit >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ $totalRevenue > 0 ? number_format(($netProfit/$totalRevenue)*100, 2) : '0.00' }}%
                        </span>
                    </div>
                    <div class="bp-pb-row">
                        <span class="bp-pb-label">ROI (on Expenses)</span>
                        <span class="bp-pb-value {{ $netProfit >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ $totalExpenses > 0 ? number_format(($netProfit/$totalExpenses)*100, 2) : '0.00' }}%
                        </span>
                    </div>
                    <div class="bp-pb-row">
                        <span class="bp-pb-label">Revenue Coverage</span>
                        <span class="bp-pb-value">
                            {{ $totalExpenses > 0 ? number_format(($totalRevenue/$totalExpenses)*100, 1) : '0.0' }}% of expenses covered
                        </span>
                    </div>
                    <div class="bp-pb-row">
                        <span class="bp-pb-label">Status</span>
                        <span class="bp-pb-value">
                            <span class="bp-profit-badge {{ $netProfit >= 0 ? 'bp-badge-profit' : 'bp-badge-loss' }} d-inline-flex">
                                <i class="fas {{ $netProfit >= 0 ? 'fa-check-circle' : 'fa-exclamation-circle' }} me-1"></i>
                                {{ $netProfit >= 0 ? 'Profitable' : 'Net Loss' }}
                            </span>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


@push('styles')
<style>
/* ════════════════════════════════════════════
   SALES BY PRODUCT — Custom Design System
════════════════════════════════════════════ */
:root {
    --bp-font: 'DM Sans', 'Segoe UI', system-ui, sans-serif;
    --bp-green:  #10b981;
    --bp-green2: #059669;
    --bp-red:    #ef4444;
    --bp-blue:   #3b82f6;
    --bp-purple: #8b5cf6;
    --bp-amber:  #f59e0b;
    --bp-slate:  #64748b;
    --bp-card-bg: #ffffff;
    --bp-border: #e2e8f0;
    --bp-radius: 18px;
    --bp-shadow: 0 4px 24px rgba(0,0,0,.07);
}

/* Header */
.bp-header { margin-bottom: 1.5rem; padding: 1.5rem 0 .5rem; }
.bp-icon-wrap {
    width: 52px; height: 52px; border-radius: 14px;
    background: linear-gradient(135deg,#dcfce7,#a7f3d0);
    display:flex; align-items:center; justify-content:center;
    font-size: 1.5rem; color: var(--bp-green);
}
.bp-title { font-size:1.8rem; font-weight:700; color:#0f172a; letter-spacing:-.5px; }
.bp-subtitle { font-size:.875rem; color:var(--bp-slate); margin-top:2px; }
.bp-breadcrumb { font-size:.8rem; }
.bp-back-btn {
    display:inline-flex; align-items:center; gap:6px;
    padding:.45rem .9rem; border-radius:10px; font-size:.82rem; font-weight:600;
    background:#f1f5f9; color:#334155; text-decoration:none;
    transition:all .2s;
}
.bp-back-btn:hover { background:#e2e8f0; color:#0f172a; }

/* Filter Bar */
.bp-filter-bar {
    background:#f8fafc; border:1px solid var(--bp-border);
    border-radius:12px; padding:.75rem 1.25rem;
    display:flex; align-items:center;
}
.bp-filter-label { font-size:.82rem; font-weight:600; color:#475569; white-space:nowrap; }
.bp-year-select {
    padding:.35rem .75rem; border:1.5px solid var(--bp-border);
    border-radius:9px; font-size:.85rem; font-weight:600; color:#1e293b;
    background:#fff; cursor:pointer; min-width:110px;
}
.bp-year-select:focus { outline:none; border-color:var(--bp-green); }
.bp-filter-divider { width:1px; height:24px; background:var(--bp-border); }
.bp-filter-note { font-size:.78rem; color:var(--bp-slate); }

/* ── KPI Cards ── */
.bp-kpi-card {
    position:relative; border-radius:var(--bp-radius);
    background:var(--bp-card-bg); border:1px solid var(--bp-border);
    padding:1.5rem; cursor:pointer; overflow:hidden;
    transition: transform .25s, box-shadow .25s, border-color .25s;
    user-select:none;
}
.bp-kpi-card:hover { transform:translateY(-4px); box-shadow:0 12px 40px rgba(0,0,0,.1); }
.bp-kpi-card:active { transform:translateY(-1px); }
.bp-kpi-card:focus-visible { outline:3px solid var(--bp-green); outline-offset:3px; }
.bp-kpi-revenue:hover { border-color:var(--bp-green); }
.bp-kpi-expense:hover { border-color:var(--bp-red); }
.bp-kpi-profit:hover  { border-color:var(--bp-blue); }
.bp-kpi-loss:hover    { border-color:var(--bp-amber); }

.bp-kpi-glow {
    position:absolute; top:-40px; right:-40px;
    width:140px; height:140px; border-radius:50%; opacity:.12; pointer-events:none;
}
.bp-glow-green  { background:var(--bp-green); }
.bp-glow-red    { background:var(--bp-red); }
.bp-glow-blue   { background:var(--bp-blue); }
.bp-glow-amber  { background:var(--bp-amber); }

.bp-kpi-inner { position:relative; z-index:1; display:flex; align-items:flex-start; gap:1rem; }
.bp-kpi-icon-wrap { display:flex; flex-direction:column; align-items:center; gap:.5rem; }
.bp-kpi-icon {
    width:52px; height:52px; border-radius:14px; display:flex;
    align-items:center; justify-content:center; font-size:1.3rem;
}
.bp-icon-green  { background:#dcfce7; color:var(--bp-green); }
.bp-icon-red    { background:#fee2e2; color:var(--bp-red); }
.bp-icon-blue   { background:#dbeafe; color:var(--bp-blue); }
.bp-icon-amber  { background:#fef3c7; color:var(--bp-amber); }
.bp-icon-purple { background:#ede9fe; color:var(--bp-purple); }

.bp-kpi-badge {
    font-size:.62rem; font-weight:700; text-transform:uppercase; letter-spacing:.5px;
    background:#f1f5f9; color:var(--bp-slate); padding:.2rem .5rem; border-radius:6px;
    white-space:nowrap; opacity:0; transition:opacity .2s;
}
.bp-kpi-card:hover .bp-kpi-badge { opacity:1; }

.bp-kpi-body { flex:1; }
.bp-kpi-label { font-size:.72rem; text-transform:uppercase; letter-spacing:.8px; color:var(--bp-slate); font-weight:700; }
.bp-kpi-value { font-size:1.9rem; font-weight:800; margin:.25rem 0; line-height:1.1; letter-spacing:-.5px; }
.bp-kpi-meta { display:flex; align-items:center; flex-wrap:wrap; gap:.5rem; margin-top:.25rem; }
.bp-kpi-sub { font-size:.75rem; color:var(--bp-slate); }
.bp-kpi-pill {
    font-size:.72rem; font-weight:700; padding:.2rem .6rem; border-radius:20px;
    display:inline-flex; align-items:center;
}
.bp-pill-profit { background:#dcfce7; color:#065f46; }
.bp-pill-loss   { background:#fef3c7; color:#92400e; }

.bp-kpi-ripple {
    position:absolute; inset:0; background:radial-gradient(circle at center, rgba(255,255,255,.3) 0%, transparent 70%);
    opacity:0; transition:opacity .3s;
}
.bp-kpi-card:active .bp-kpi-ripple { opacity:1; }

/* ── Panels ── */
.bp-panel {
    background:var(--bp-card-bg); border-radius:var(--bp-radius);
    border:1px solid var(--bp-border); box-shadow:var(--bp-shadow); overflow:hidden;
}
.bp-panel-header {
    padding:1.25rem 1.5rem; border-bottom:1px solid var(--bp-border);
    display:flex; align-items:center; justify-content:space-between;
    background:#fafbfc;
}
.bp-panel-title-group { display:flex; align-items:center; gap:.875rem; }
.bp-panel-icon {
    width:40px; height:40px; border-radius:11px; display:flex;
    align-items:center; justify-content:center; font-size:1rem;
}
.bp-panel-icon-green  { background:#dcfce7; color:var(--bp-green); }
.bp-panel-icon-blue   { background:#dbeafe; color:var(--bp-blue); }
.bp-panel-icon-purple { background:#ede9fe; color:var(--bp-purple); }
.bp-panel-icon-amber  { background:#fef3c7; color:var(--bp-amber); }

.bp-panel-title { font-size:1rem; font-weight:700; color:#0f172a; margin:0; }
.bp-panel-subtitle { font-size:.75rem; color:var(--bp-slate); margin:0; }
.bp-panel-count {
    font-size:.75rem; font-weight:700; padding:.3rem .7rem; border-radius:8px;
    background:#f1f5f9; color:var(--bp-slate);
}
.bp-panel-body { padding:1.25rem 1.5rem; }

/* Product Rows */
.bp-product-row {
    display:flex; align-items:center; gap:1rem; padding:.75rem 0;
    border-bottom:1px solid #f1f5f9;
}
.bp-product-row:last-child { border-bottom:none; }
.bp-product-left { display:flex; align-items:center; gap:.75rem; min-width:0; flex:0 0 180px; }
.bp-product-emoji { font-size:1.4rem; flex-shrink:0; }
.bp-product-name { font-size:.875rem; font-weight:600; color:#1e293b; white-space:nowrap; }
.bp-product-qty  { font-size:.72rem; color:var(--bp-slate); }
.bp-product-right { flex:1; display:flex; flex-direction:column; gap:.35rem; }
.bp-product-bar-wrap { height:7px; background:#f1f5f9; border-radius:10px; overflow:hidden; }
.bp-product-bar { height:100%; border-radius:10px; transition:width .6s cubic-bezier(.4,0,.2,1); }
.bp-product-stats { display:flex; justify-content:space-between; align-items:center; }
.bp-product-revenue { font-size:.8rem; font-weight:700; color:var(--bp-green); }
.bp-product-pct     { font-size:.75rem; color:var(--bp-slate); }
.bp-product-total-row {
    display:flex; justify-content:space-between; align-items:center;
    padding:.75rem 0 0; margin-top:.5rem; border-top:2px solid var(--bp-border);
    font-size:.85rem; font-weight:700; color:#1e293b;
}
.bp-product-total-rev { color:var(--bp-green); }
.bp-product-total-pct { color:var(--bp-slate); }
.bp-empty { text-align:center; padding:3rem 1rem; color:var(--bp-slate); }
.bp-empty i { font-size:2.5rem; margin-bottom:.75rem; display:block; }

/* P&L Summary */
.bp-summary-block { border-radius:12px; padding:1rem 1.25rem; }
.bp-sb-green { background:#f0fdf4; border:1px solid #bbf7d0; }
.bp-sb-red   { background:#fff1f2; border:1px solid #fecdd3; }
.bp-sb-blue  { background:#eff6ff; border:1px solid #bfdbfe; }
.bp-sb-amber { background:#fffbeb; border:1px solid #fde68a; }
.bp-sb-label { font-size:.72rem; text-transform:uppercase; letter-spacing:.6px; font-weight:700; color:var(--bp-slate); }
.bp-sb-value { font-size:1.4rem; font-weight:800; color:#0f172a; margin:.2rem 0; }
.bp-sb-bar { height:5px; background:rgba(0,0,0,.06); border-radius:10px; overflow:hidden; margin-top:.4rem; }
.bp-sb-green .bp-sb-bar > div { height:100%; background:var(--bp-green); border-radius:10px; }
.bp-sb-red   .bp-sb-bar > div { height:100%; background:var(--bp-red);   border-radius:10px; }
.bp-sb-blue  .bp-sb-bar > div { height:100%; background:var(--bp-blue);  border-radius:10px; }
.bp-sb-amber .bp-sb-bar > div { height:100%; background:var(--bp-amber); border-radius:10px; }
.bp-summary-divider { height:1px; background:var(--bp-border); margin:.25rem 0; }
.bp-profit-badge {
    display:flex; align-items:center; justify-content:center;
    padding:.6rem 1rem; border-radius:10px; font-size:.8rem; font-weight:700;
    text-align:center;
}
.bp-badge-profit { background:#dcfce7; color:#065f46; }
.bp-badge-loss   { background:#fef3c7; color:#92400e; }

/* ── Modals ── */
.bp-modal .modal-content.bp-mc {
    border:none; border-radius:20px; overflow:hidden;
    box-shadow:0 25px 80px rgba(0,0,0,.18);
}
.bp-mc-header {
    display:flex; align-items:center; justify-content:space-between;
    padding:1.25rem 1.5rem;
}
.bp-mh-green  { background:linear-gradient(135deg,#10b981,#059669); color:#fff; }
.bp-mh-red    { background:linear-gradient(135deg,#ef4444,#dc2626); color:#fff; }
.bp-mh-blue   { background:linear-gradient(135deg,#3b82f6,#2563eb); color:#fff; }
.bp-mh-amber  { background:linear-gradient(135deg,#f59e0b,#d97706); color:#fff; }
.bp-mh-icon {
    width:40px; height:40px; background:rgba(255,255,255,.2); border-radius:10px;
    display:flex; align-items:center; justify-content:center; font-size:1.1rem;
}
.bp-mh-title { font-size:1rem; font-weight:700; color:#fff; }
.bp-mh-sub   { font-size:.78rem; color:rgba(255,255,255,.8); }
.bp-close-btn {
    background:rgba(255,255,255,.2); border:none; color:#fff;
    width:32px; height:32px; border-radius:8px; cursor:pointer;
    display:flex; align-items:center; justify-content:center; font-size:.9rem;
    transition:background .2s;
}
.bp-close-btn:hover { background:rgba(255,255,255,.35); }

.bp-modal-stat {
    border-radius:12px; padding:1rem; text-align:center;
}
.bp-ms-green  { background:#f0fdf4; border:1px solid #bbf7d0; }
.bp-ms-red    { background:#fff1f2; border:1px solid #fecdd3; }
.bp-ms-blue   { background:#eff6ff; border:1px solid #bfdbfe; }
.bp-ms-purple { background:#f5f3ff; border:1px solid #ddd6fe; }
.bp-ms-amber  { background:#fffbeb; border:1px solid #fde68a; }
.bp-ms-label  { font-size:.7rem; text-transform:uppercase; letter-spacing:.6px; font-weight:700; color:var(--bp-slate); }
.bp-ms-value  { font-size:1.3rem; font-weight:800; color:#0f172a; margin-top:.25rem; }

.bp-modal-section-title { font-size:.82rem; text-transform:uppercase; letter-spacing:.6px; font-weight:700; color:var(--bp-slate); margin-bottom:.75rem; }

.bp-modal-table-wrap { overflow-x:auto; border-radius:10px; border:1px solid var(--bp-border); }
.bp-modal-table { width:100%; border-collapse:collapse; font-size:.85rem; }
.bp-modal-table thead th { padding:.6rem .875rem; background:#f8fafc; color:#475569; font-size:.75rem; font-weight:700; text-transform:uppercase; letter-spacing:.4px; }
.bp-modal-table tbody td { padding:.6rem .875rem; border-top:1px solid #f1f5f9; color:#334155; }
.bp-modal-table tfoot th { padding:.6rem .875rem; border-top:2px solid var(--bp-border); background:#fafbfc; }

.bp-mini-bar-wrap { width:80px; height:5px; background:#f1f5f9; border-radius:10px; overflow:hidden; }
.bp-mini-bar { height:100%; border-radius:10px; }

.bp-modal-chart-wrap { border:1px solid var(--bp-border); border-radius:12px; padding:1rem; background:#fafbfc; }

.bp-info-box { background:#f0f9ff; border:1px solid #bae6fd; border-radius:10px; padding:1rem 1.25rem; font-size:.875rem; color:#0c4a6e; }

.bp-profit-breakdown { border:1px solid var(--bp-border); border-radius:12px; overflow:hidden; }
.bp-pb-row { display:flex; justify-content:space-between; align-items:center; padding:.75rem 1rem; border-bottom:1px solid #f1f5f9; }
.bp-pb-row:last-child { border-bottom:none; }
.bp-pb-label { font-size:.82rem; color:var(--bp-slate); font-weight:600; }
.bp-pb-value { font-size:.9rem; font-weight:700; color:#1e293b; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    /* ── Monthly Chart ── */
    const monthlyData = @json($monthlyBreakdown);
    const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    const values = Array(12).fill(0);
    monthlyData.forEach(item => { values[item.month - 1] = parseFloat(item.total); });

    const monthlyGrad = document.getElementById('monthlyChart').getContext('2d');
    const mgradient = monthlyGrad.createLinearGradient(0, 0, 0, 300);
    mgradient.addColorStop(0, 'rgba(16,185,129,.7)');
    mgradient.addColorStop(1, 'rgba(16,185,129,.05)');

    new Chart(document.getElementById('monthlyChart'), {
        type: 'bar',
        data: {
            labels: months,
            datasets: [{
                label: 'Monthly Revenue (₵)',
                data: values,
                backgroundColor: mgradient,
                borderColor: '#10b981',
                borderWidth: 2,
                borderRadius: 8,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ' ₵' + ctx.raw.toLocaleString('en-GH', {minimumFractionDigits:2})
                    }
                }
            },
            scales: {
                x: { grid: { display: false }, ticks: { color: '#64748b', font: { size:11 } } },
                y: {
                    beginAtZero: true,
                    grid: { color: '#f1f5f9', drawBorder: false },
                    ticks: {
                        color: '#94a3b8',
                        font: { size:10 },
                        callback: v => '₵' + (v >= 1000 ? (v/1000).toFixed(0)+'k' : v)
                    }
                }
            }
        }
    });

    /* ── Comparison Chart ── */
    const totalRevenue  = {{ $totalRevenue }};
    const totalExpenses = {{ $totalExpenses }};
    const netProfit     = {{ $netProfit }};

    new Chart(document.getElementById('comparisonChart'), {
        type: 'bar',
        data: {
            labels: ['Revenue', 'Expenses', 'Net Profit'],
            datasets: [{
                data: [totalRevenue, totalExpenses, Math.abs(netProfit)],
                backgroundColor: ['#10b981', '#ef4444', netProfit >= 0 ? '#3b82f6' : '#f59e0b'],
                borderRadius: 10,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ' ₵' + ctx.raw.toLocaleString('en-GH', {minimumFractionDigits:2})
                    }
                }
            },
            scales: {
                x: { grid: { display: false }, ticks: { color: '#64748b', font: { size:12, weight:'600' } } },
                y: {
                    beginAtZero: true,
                    grid: { color: '#f1f5f9' },
                    ticks: {
                        color: '#94a3b8', font: { size:10 },
                        callback: v => '₵' + (v >= 1000 ? (v/1000).toFixed(0)+'k' : v)
                    }
                }
            }
        }
    });

    /* ── Revenue Modal Chart ── */
    const salesByProduct = @json($salesByProduct);
    const productMeta = {
        'eggs_tray':      { label:'Eggs (Tray)',      color:'#f59e0b' },
        'eggs_crate':     { label:'Eggs (Crate)',     color:'#f97316' },
        'eggs_box':       { label:'Eggs (Box)',        color:'#fb923c' },
        'live_bird':      { label:'Live Bird',         color:'#10b981' },
        'meat_kg':        { label:'Meat (kg)',         color:'#ef4444' },
        'breeding_stock': { label:'Breeding Stock',   color:'#8b5cf6' },
        'manure':         { label:'Manure',           color:'#78716c' },
        'other':          { label:'Other',            color:'#64748b' },
    };

    let modalChartInstance = null;

    function renderRevenueModalChart() {
        const ctx = document.getElementById('revenueModalChart');
        if (!ctx) return;
        if (modalChartInstance) modalChartInstance.destroy();

        const labels  = salesByProduct.map(s => (productMeta[s.product_type]?.label || s.product_type));
        const data    = salesByProduct.map(s => parseFloat(s.total_revenue));
        const colors  = salesByProduct.map(s => productMeta[s.product_type]?.color || '#64748b');

        modalChartInstance = new Chart(ctx, {
            type: 'doughnut',
            data: { labels, datasets: [{ data, backgroundColor: colors, borderWidth: 0, hoverOffset: 8 }] },
            options: {
                responsive: true,
                plugins: {
                    legend: { position:'bottom', labels: { padding:12, font:{size:11} } },
                    tooltip: {
                        callbacks: {
                            label: ctx => ' ₵' + ctx.raw.toLocaleString('en-GH', {minimumFractionDigits:2})
                        }
                    }
                }
            }
        });
    }

    document.getElementById('revenueModal')?.addEventListener('shown.bs.modal', renderRevenueModalChart);

    /* ── KPI Modal Opener ── */
    window.openKpiModal = function(type) {
        let modalId;
        if (type === 'revenue')  modalId = 'revenueModal';
        if (type === 'expenses') modalId = 'expensesModal';
        if (type === 'profit')   modalId = 'profitModal';
        const el = document.getElementById(modalId);
        if (el) new bootstrap.Modal(el).show();
    };

    /* keyboard enter for accessibility */
    document.querySelectorAll('.bp-kpi-card').forEach(card => {
        card.addEventListener('keydown', e => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                card.click();
            }
        });
    });
});
</script>
@endpush

@endsection