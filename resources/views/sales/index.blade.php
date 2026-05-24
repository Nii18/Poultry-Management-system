{{-- resources/views/sales/index.blade.php --}}
@extends('layouts.master')

@section('content')
<div class="container-fluid px-4">
    <!-- Page Header -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <div class="d-flex align-items-center gap-3">
                    <div class="page-icon">
                        <i class="fas fa-chart-line fs-1 text-success"></i>
                    </div>
                    <div>
                        <h1 class="page-title mb-1">Sales & Revenue</h1>
                        <p class="page-description text-muted mb-0">Track all farm income and revenue</p>
                    </div>
                </div>
            </div>
            <div class="col-auto">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Sales</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <!-- Stats Cards — each card is clickable and opens a detail modal -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="stat-card stat-card-clickable" onclick="openStatModal('revenue')" role="button" tabindex="0" title="Click to view revenue details">
                <div class="stat-card-body">
                    <div class="stat-card-icon bg-success-soft">
                        <i class="fas fa-chart-line text-success"></i>
                    </div>
                    <div class="stat-card-info">
                        <span class="stat-card-label">Total Revenue</span>
                        <h3 class="stat-card-value text-success">₵{{ number_format($totalRevenue, 2) }}</h3>
                    </div>
                    <div class="stat-card-chevron"><i class="fas fa-chevron-right text-muted"></i></div>
                </div>
                <div class="stat-card-hint">Click for itemised details</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card stat-card-clickable" onclick="openStatModal('quantity')" role="button" tabindex="0" title="Click to view quantity details">
                <div class="stat-card-body">
                    <div class="stat-card-icon bg-primary-soft">
                        <i class="fas fa-boxes text-primary"></i>
                    </div>
                    <div class="stat-card-info">
                        <span class="stat-card-label">Total Quantity Sold</span>
                        <h3 class="stat-card-value">{{ number_format($totalQuantity, 2) }}</h3>
                    </div>
                    <div class="stat-card-chevron"><i class="fas fa-chevron-right text-muted"></i></div>
                </div>
                <div class="stat-card-hint">Click for stock details</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card stat-card-clickable" onclick="openStatModal('transactions')" role="button" tabindex="0" title="Click to view transaction details">
                <div class="stat-card-body">
                    <div class="stat-card-icon bg-info-soft">
                        <i class="fas fa-receipt text-info"></i>
                    </div>
                    <div class="stat-card-info">
                        <span class="stat-card-label">Total Transactions</span>
                        <h3 class="stat-card-value">{{ $sales->total() }}</h3>
                    </div>
                    <div class="stat-card-chevron"><i class="fas fa-chevron-right text-muted"></i></div>
                </div>
                <div class="stat-card-hint">Click for transaction log</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card stat-card-clickable" onclick="openStatModal('products')" role="button" tabindex="0" title="Click to view product types">
                <div class="stat-card-body">
                    <div class="stat-card-icon bg-warning-soft">
                        <i class="fas fa-tag text-warning"></i>
                    </div>
                    <div class="stat-card-info">
                        <span class="stat-card-label">Product Types</span>
                        <h3 class="stat-card-value">{{ $productTypes->count() }}</h3>
                    </div>
                    <div class="stat-card-chevron"><i class="fas fa-chevron-right text-muted"></i></div>
                </div>
                <div class="stat-card-hint">Click for product breakdown</div>
            </div>
        </div>
    </div>

    <!-- Main Card -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-0 py-3">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="card-title mb-0 fw-semibold">
                        <i class="fas fa-list me-2 text-success"></i>Sales Records
                    </h5>
                </div>
                <div class="col-auto">
                    <div class="d-flex gap-2">
                        <a href="{{ route('sales.by-product') }}" class="btn btn-info">
                            <i class="fas fa-chart-pie me-2"></i>View by Product
                        </a>
                        <button type="button" class="btn btn-success" id="newSaleBtn">
                            <i class="fas fa-plus me-2"></i>Record Sale
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body">
            <!-- Filters Section -->
            <div class="filter-section mb-4 p-3 bg-light rounded-3">
                <div class="row align-items-end g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold mb-2">
                            <i class="fas fa-paw me-1 text-muted"></i>Flock
                        </label>
                        <select name="flock_id" class="form-select" id="flockFilter">
                            <option value="">All Flocks</option>
                            @foreach($flocks as $flock)
                                <option value="{{ $flock->id }}" {{ request('flock_id') == $flock->id ? 'selected' : '' }}>
                                    {{ $flock->flock_number }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold mb-2">
                            <i class="fas fa-tag me-1 text-muted"></i>Product Type
                        </label>
                        <select name="product_type" class="form-select" id="productTypeFilter">
                            <option value="">All Products</option>
                            @foreach($productTypes as $type)
                                <option value="{{ $type }}" {{ request('product_type') == $type ? 'selected' : '' }}>
                                    @php
                                        $labels = [
                                            'eggs_tray' => 'Eggs (Tray - 30 eggs)',
                                            'eggs_crate' => 'Eggs (Crate - 12 trays)',
                                            'eggs_box' => 'Eggs (Box - 360 eggs)',
                                            'live_bird' => 'Live Bird',
                                            'meat_kg' => 'Meat (kg)',
                                            'breeding_stock' => 'Breeding Stock',
                                            'manure' => 'Manure',
                                            'other' => 'Other',
                                        ];
                                    @endphp
                                    {{ $labels[$type] ?? ucfirst(str_replace('_', ' ', $type)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold mb-2">
                            <i class="fas fa-calendar me-1 text-muted"></i>Start Date
                        </label>
                        <input type="date" name="start_date" class="form-control" id="startDateFilter"
                               value="{{ $startDate instanceof \Carbon\Carbon ? $startDate->format('Y-m-d') : $startDate }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold mb-2">
                            <i class="fas fa-calendar me-1 text-muted"></i>End Date
                        </label>
                        <input type="date" name="end_date" class="form-control" id="endDateFilter"
                               value="{{ $endDate instanceof \Carbon\Carbon ? $endDate->format('Y-m-d') : $endDate }}">
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-success w-100" id="applyFilters">
                            <i class="fas fa-filter me-1"></i>Filter
                        </button>
                    </div>
                </div>
            </div>

            <!-- Sales Table -->
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="py-3">Date</th>
                            <th class="py-3">Product</th>
                            <th class="py-3">Quantity</th>
                            <th class="py-3">Unit Price (₵)</th>
                            <th class="py-3">Total (₵)</th>
                            <th class="py-3">Customer</th>
                            <th class="py-3">Flock</th>
                            <th class="py-3 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sales as $sale)
                        <tr>
                            <td>{{ $sale->sale_date->format('d M Y') }}</td>
                            <td>
                                @php
                                    $productLabels = [
                                        'eggs_tray' => ['label' => '🥚 Eggs (Tray)', 'color' => 'warning'],
                                        'eggs_crate' => ['label' => '📦 Eggs (Crate)', 'color' => 'warning'],
                                        'eggs_box' => ['label' => '📦 Eggs (Box)', 'color' => 'warning'],
                                        'live_bird' => ['label' => '🐓 Live Bird', 'color' => 'primary'],
                                        'meat_kg' => ['label' => '🍗 Meat (kg)', 'color' => 'danger'],
                                        'breeding_stock' => ['label' => '🧬 Breeding Stock', 'color' => 'info'],
                                        'manure' => ['label' => '💩 Manure', 'color' => 'secondary'],
                                        'other' => ['label' => '📦 Other', 'color' => 'secondary'],
                                    ];
                                    $product = $productLabels[$sale->product_type] ?? ['label' => ucfirst(str_replace('_', ' ', $sale->product_type)), 'color' => 'secondary'];
                                @endphp
                                <span class="badge bg-{{ $product['color'] }}-soft text-{{ $product['color'] }} px-3 py-2 rounded-pill">
                                    {{ $product['label'] }}
                                </span>
                            </td>
                            <td>{{ number_format($sale->quantity, 2) }}</td>
                            <td>₵{{ number_format($sale->unit_price, 2) }}</td>
                            <td><strong class="text-success">₵{{ number_format($sale->total_amount, 2) }}</strong></td>
                            <td>{{ $sale->customer_name ?? 'Walk-in' }}</td>
                            <td>
                                @if($sale->flock)
                                    <span class="badge bg-primary-soft text-primary">
                                        <i class="fas fa-paw me-1"></i>{{ $sale->flock->flock_number }}
                                    </span>
                                @else
                                    <span class="badge bg-secondary-soft text-secondary">
                                        <i class="fas fa-globe me-1"></i>General
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group gap-1">
                                    <button type="button" class="btn btn-sm btn-outline-primary view-sale-btn" data-id="{{ $sale->id }}" title="View Details">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-warning edit-sale-btn" data-id="{{ $sale->id }}" title="Edit">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    @if(auth()->user()->role === 'admin')
                                        <button type="button" class="btn btn-sm btn-outline-danger delete-sale-btn" data-id="{{ $sale->id }}" data-info="{{ $sale->description ?? $sale->product_type }}" title="Delete">
                                            <i class="fas fa-trash-alt"></i> Delete
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="empty-state">
                                    <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">No Sales Records Found</h5>
                                    <p class="text-muted mb-3">Get started by recording your first sale</p>
                                    <button type="button" class="btn btn-success" id="emptyStateNewBtn">
                                        <i class="fas fa-plus me-2"></i>Record Sale
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="4"><strong class="fs-5">Total Revenue</strong></td>
                            <td><strong class="text-success fs-5">₵{{ number_format($totalRevenue, 2) }}</strong></td>
                            <td colspan="3"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Pagination -->
            @if($sales->hasPages())
            <div class="d-flex justify-content-between align-items-center mt-4 pt-2">
                <div class="text-muted small">
                    Showing {{ $sales->firstItem() ?? 0 }} to {{ $sales->lastItem() ?? 0 }} of {{ $sales->total() }} results
                </div>
                <div>
                    {{ $sales->withQueryString()->links() }}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════
     STAT CARD DETAIL MODALS
═══════════════════════════════════════════════════════════ --}}

{{-- ── REVENUE DETAIL MODAL ── --}}
<div class="modal fade" id="statRevenueModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0" style="background:linear-gradient(135deg,#10b981,#059669);">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-modal-icon"><i class="fas fa-chart-line"></i></div>
                    <div>
                        <h5 class="modal-title text-white fw-bold mb-0">Total Revenue — Itemised Breakdown</h5>
                        <p class="text-white-50 small mb-0">All items sold with quantity &amp; revenue contribution</p>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                {{-- Summary row --}}
                <div class="p-4 border-bottom" style="background:#f0fdf4;">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="stat-mini-card stat-mini-green">
                                <div class="stat-mini-label">Total Revenue</div>
                                <div class="stat-mini-value">₵{{ number_format($totalRevenue, 2) }}</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-mini-card stat-mini-blue">
                                <div class="stat-mini-label">Total Transactions</div>
                                <div class="stat-mini-value">{{ $sales->total() }}</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-mini-card stat-mini-purple">
                                <div class="stat-mini-label">Avg per Transaction</div>
                                <div class="stat-mini-value">₵{{ $sales->total() > 0 ? number_format($totalRevenue / $sales->total(), 2) : '0.00' }}</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-mini-card stat-mini-amber">
                                <div class="stat-mini-label">Total Qty Sold</div>
                                <div class="stat-mini-value">{{ number_format($totalQuantity, 2) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- Itemised table --}}
                <div class="p-4">
                    <h6 class="stat-section-title mb-3"><i class="fas fa-list me-2 text-success"></i>Items Sold — Revenue per Product</h6>
                    @php
                        $revenueByProduct = $sales->getCollection()->groupBy('product_type');
                        $productMeta = [
                            'eggs_tray'      => ['icon'=>'🥚','label'=>'Eggs (Tray)'],
                            'eggs_crate'     => ['icon'=>'📦','label'=>'Eggs (Crate)'],
                            'eggs_box'       => ['icon'=>'📦','label'=>'Eggs (Box)'],
                            'live_bird'      => ['icon'=>'🐓','label'=>'Live Bird'],
                            'meat_kg'        => ['icon'=>'🍗','label'=>'Meat (kg)'],
                            'breeding_stock' => ['icon'=>'🧬','label'=>'Breeding Stock'],
                            'manure'         => ['icon'=>'💩','label'=>'Manure'],
                            'other'          => ['icon'=>'📦','label'=>'Other'],
                        ];
                    @endphp
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th class="text-center">Transactions</th>
                                    <th class="text-end">Qty Sold</th>
                                    <th class="text-end">Avg Unit Price</th>
                                    <th class="text-end">Total Revenue</th>
                                    <th>Revenue Share</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($revenueByProduct as $type => $group)
                                    @php
                                        $pm = $productMeta[$type] ?? ['icon'=>'📦','label'=>ucfirst(str_replace('_',' ',$type))];
                                        $grpRevenue = $group->sum('total_amount');
                                        $grpQty     = $group->sum('quantity');
                                        $grpAvgPrice= $group->avg('unit_price');
                                        $grpPct     = $totalRevenue > 0 ? ($grpRevenue / $totalRevenue) * 100 : 0;
                                    @endphp
                                    <tr>
                                        <td>
                                            <span class="me-2 fs-5">{{ $pm['icon'] }}</span>
                                            <strong>{{ $pm['label'] }}</strong>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-light text-dark border">{{ $group->count() }}</span>
                                        </td>
                                        <td class="text-end fw-semibold">{{ number_format($grpQty, 2) }}</td>
                                        <td class="text-end">₵{{ number_format($grpAvgPrice, 2) }}</td>
                                        <td class="text-end">
                                            <strong class="text-success">₵{{ number_format($grpRevenue, 2) }}</strong>
                                        </td>
                                        <td style="min-width:160px">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="progress flex-grow-1" style="height:7px;">
                                                    <div class="progress-bar bg-success" style="width:{{ $grpPct }}%"></div>
                                                </div>
                                                <span class="small text-muted fw-semibold">{{ number_format($grpPct,1) }}%</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th>TOTAL</th>
                                    <th class="text-center">{{ $sales->count() }}</th>
                                    <th class="text-end">{{ number_format($totalQuantity,2) }}</th>
                                    <th></th>
                                    <th class="text-end text-success">₵{{ number_format($totalRevenue,2) }}</th>
                                    <th>100%</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── QUANTITY DETAIL MODAL ── --}}
<div class="modal fade" id="statQuantityModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0" style="background:linear-gradient(135deg,#3b82f6,#2563eb);">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-modal-icon"><i class="fas fa-boxes"></i></div>
                    <div>
                        <h5 class="modal-title text-white fw-bold mb-0">Quantity Sold — Stock Breakdown</h5>
                        <p class="text-white-50 small mb-0">Units sold per product with remaining stock estimates</p>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="p-4 border-bottom" style="background:#eff6ff;">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="stat-mini-card stat-mini-blue">
                                <div class="stat-mini-label">Total Units Sold</div>
                                <div class="stat-mini-value">{{ number_format($totalQuantity, 2) }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-mini-card stat-mini-green">
                                <div class="stat-mini-label">Product Categories</div>
                                <div class="stat-mini-value">{{ $productTypes->count() }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-mini-card stat-mini-purple">
                                <div class="stat-mini-label">Avg Units / Transaction</div>
                                <div class="stat-mini-value">{{ $sales->total() > 0 ? number_format($totalQuantity / $sales->total(), 2) : '0.00' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="p-4">
                    <h6 class="stat-section-title mb-3"><i class="fas fa-layer-group me-2 text-primary"></i>Qty Sold per Product &amp; Stock Status</h6>
                    @php
                        $qtyByProduct = $sales->getCollection()->groupBy('product_type');
                        // Stock reference capacities (from flock data where available)
                        $stockRef = [
                            'eggs_tray'      => ['unit'=>'trays',    'capacity'=> null],
                            'eggs_crate'     => ['unit'=>'crates',   'capacity'=> null],
                            'eggs_box'       => ['unit'=>'boxes',    'capacity'=> null],
                            'live_bird'      => ['unit'=>'birds',    'capacity'=> $flocks->sum('current_count')],
                            'meat_kg'        => ['unit'=>'kg',       'capacity'=> null],
                            'breeding_stock' => ['unit'=>'animals',  'capacity'=> null],
                            'manure'         => ['unit'=>'bags',     'capacity'=> null],
                            'other'          => ['unit'=>'units',    'capacity'=> null],
                        ];
                    @endphp
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th class="text-center">Transactions</th>
                                    <th class="text-end">Qty Sold</th>
                                    <th class="text-end">Avg per Sale</th>
                                    <th class="text-end">Highest Sale</th>
                                    <th>Stock Available</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($qtyByProduct as $type => $group)
                                    @php
                                        $pm   = $productMeta[$type] ?? ['icon'=>'📦','label'=>ucfirst(str_replace('_',' ',$type))];
                                        $ref  = $stockRef[$type]    ?? ['unit'=>'units','capacity'=>null];
                                        $sold = $group->sum('quantity');
                                        $avg  = $group->avg('quantity');
                                        $max  = $group->max('quantity');
                                        $cap  = $ref['capacity'];
                                        $rem  = $cap !== null ? max(0, $cap - $sold) : null;
                                        $pctUsed = ($cap && $cap > 0) ? min(($sold / $cap) * 100, 100) : null;
                                    @endphp
                                    <tr>
                                        <td>
                                            <span class="me-2 fs-5">{{ $pm['icon'] }}</span>
                                            <strong>{{ $pm['label'] }}</strong>
                                            <div class="small text-muted">{{ $ref['unit'] }}</div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-light text-dark border">{{ $group->count() }}</span>
                                        </td>
                                        <td class="text-end fw-bold text-primary">{{ number_format($sold, 2) }}</td>
                                        <td class="text-end text-muted">{{ number_format($avg, 2) }}</td>
                                        <td class="text-end">
                                            <span class="badge bg-info-soft text-info">{{ number_format($max, 2) }}</span>
                                        </td>
                                        <td style="min-width:200px">
                                            @if($rem !== null)
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="progress flex-grow-1" style="height:7px;">
                                                        <div class="progress-bar {{ $pctUsed > 80 ? 'bg-danger' : ($pctUsed > 50 ? 'bg-warning' : 'bg-success') }}"
                                                             style="width:{{ $pctUsed }}%"></div>
                                                    </div>
                                                    <span class="small fw-semibold text-muted">{{ number_format($rem,0) }} left</span>
                                                </div>
                                                <div class="small text-muted mt-1">{{ number_format($pctUsed,1) }}% of {{ number_format($cap,0) }} capacity sold</div>
                                            @else
                                                <span class="badge bg-secondary-soft text-secondary">
                                                    <i class="fas fa-infinity me-1"></i>Made to order / variable
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th>TOTAL</th>
                                    <th class="text-center">{{ $sales->count() }}</th>
                                    <th class="text-end text-primary">{{ number_format($totalQuantity,2) }}</th>
                                    <th colspan="3"></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div class="alert alert-info border-0 mt-3 small">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Stock note:</strong> Live Bird remaining stock is derived from active flock headcount.
                        Other product types (eggs, manure, meat) are produced continuously and shown as variable.
                        For precise stock levels, refer to the Inventory or Daily Logs section.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── TRANSACTIONS DETAIL MODAL ── --}}
<div class="modal fade" id="statTransactionsModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0" style="background:linear-gradient(135deg,#14b8a6,#0d9488);">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-modal-icon"><i class="fas fa-receipt"></i></div>
                    <div>
                        <h5 class="modal-title text-white fw-bold mb-0">Transaction Log</h5>
                        <p class="text-white-50 small mb-0">Timestamped record of every sale in the selected period</p>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="p-4 border-bottom" style="background:#f0fdfa;">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="stat-mini-card stat-mini-teal">
                                <div class="stat-mini-label">Total Transactions</div>
                                <div class="stat-mini-value">{{ $sales->total() }}</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-mini-card stat-mini-green">
                                <div class="stat-mini-label">Total Revenue</div>
                                <div class="stat-mini-value">₵{{ number_format($totalRevenue,2) }}</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-mini-card stat-mini-blue">
                                <div class="stat-mini-label">Avg Sale Value</div>
                                <div class="stat-mini-value">₵{{ $sales->total() > 0 ? number_format($totalRevenue/$sales->total(),2) : '0.00' }}</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-mini-card stat-mini-purple">
                                <div class="stat-mini-label">Period Shown</div>
                                <div class="stat-mini-value" style="font-size:.9rem;">
                                    {{ ($startDate instanceof \Carbon\Carbon ? $startDate : \Carbon\Carbon::parse($startDate))->format('d M') }}
                                    –
                                    {{ ($endDate instanceof \Carbon\Carbon ? $endDate : \Carbon\Carbon::parse($endDate))->format('d M Y') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="p-4">
                    <h6 class="stat-section-title mb-3"><i class="fas fa-clock me-2 text-info"></i>Transaction Timeline — Date &amp; Time of Each Sale</h6>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Date &amp; Time Recorded</th>
                                    <th>Sale Date</th>
                                    <th>Product</th>
                                    <th class="text-end">Qty</th>
                                    <th class="text-end">Unit Price</th>
                                    <th class="text-end">Total</th>
                                    <th>Customer</th>
                                    <th>Payment</th>
                                    <th>Recorded By</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $txCount = ($sales->currentPage()-1) * $sales->perPage(); @endphp
                                @forelse($sales as $sale)
                                    @php
                                        $txCount++;
                                        $pm = $productMeta[$sale->product_type] ?? ['icon'=>'📦','label'=>ucfirst(str_replace('_',' ',$sale->product_type))];
                                        $payIcons = [
                                            'cash'=>'💵','bank_transfer'=>'🏦',
                                            'mobile_money'=>'📱','check'=>'📝',
                                        ];
                                        $payIcon = $payIcons[$sale->payment_method] ?? '💳';
                                    @endphp
                                    <tr>
                                        <td class="text-muted small">{{ $txCount }}</td>
                                        <td>
                                            <div class="fw-semibold small">{{ $sale->created_at->format('d M Y') }}</div>
                                            <div class="text-muted" style="font-size:.7rem;">
                                                <i class="fas fa-clock me-1"></i>{{ $sale->created_at->format('H:i:s') }}
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border small">
                                                {{ $sale->sale_date->format('d M Y') }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="me-1">{{ $pm['icon'] }}</span>
                                            <span class="small fw-semibold">{{ $pm['label'] }}</span>
                                        </td>
                                        <td class="text-end small">{{ number_format($sale->quantity,2) }}</td>
                                        <td class="text-end small">₵{{ number_format($sale->unit_price,2) }}</td>
                                        <td class="text-end">
                                            <strong class="text-success small">₵{{ number_format($sale->total_amount,2) }}</strong>
                                        </td>
                                        <td class="small text-muted">{{ $sale->customer_name ?? 'Walk-in' }}</td>
                                        <td class="small">{{ $payIcon }} {{ $sale->payment_method ? ucfirst(str_replace('_',' ',$sale->payment_method)) : '—' }}</td>
                                        <td class="small text-muted">{{ $sale->creator->name ?? '—' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="10" class="text-center py-4 text-muted">No transactions in this period</td></tr>
                                @endforelse
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="6" class="text-end">Period Total:</th>
                                    <th class="text-end text-success">₵{{ number_format($totalRevenue,2) }}</th>
                                    <th colspan="3"></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    @if($sales->hasPages())
                    <p class="text-muted small mt-2">
                        <i class="fas fa-info-circle me-1"></i>
                        Showing {{ $sales->firstItem() }}–{{ $sales->lastItem() }} of {{ $sales->total() }} transactions.
                        Use the main table pagination to view more.
                    </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── PRODUCT TYPES DETAIL MODAL ── --}}
<div class="modal fade" id="statProductsModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0" style="background:linear-gradient(135deg,#f59e0b,#d97706);">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-modal-icon"><i class="fas fa-tag"></i></div>
                    <div>
                        <h5 class="modal-title text-white fw-bold mb-0">Product Types — Active &amp; Remaining</h5>
                        <p class="text-white-50 small mb-0">All product categories with stock &amp; revenue summary</p>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="p-4 border-bottom" style="background:#fffbeb;">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="stat-mini-card stat-mini-amber">
                                <div class="stat-mini-label">Active Product Types</div>
                                <div class="stat-mini-value">{{ $productTypes->count() }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-mini-card stat-mini-green">
                                <div class="stat-mini-label">Total Revenue</div>
                                <div class="stat-mini-value">₵{{ number_format($totalRevenue,2) }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-mini-card stat-mini-blue">
                                <div class="stat-mini-label">Unused Types</div>
                                @php
                                    $allTypes = ['eggs_tray','eggs_crate','eggs_box','live_bird','meat_kg','breeding_stock','manure','other'];
                                    $unusedTypes = array_diff($allTypes, $productTypes->toArray());
                                @endphp
                                <div class="stat-mini-value">{{ count($unusedTypes) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="p-4">
                    {{-- Active Types --}}
                    <h6 class="stat-section-title mb-3"><i class="fas fa-check-circle me-2 text-success"></i>Active Product Types in This Period</h6>
                    @php
                        $allProductLabels = [
                            'eggs_tray'      => ['icon'=>'🥚','label'=>'Eggs (Tray)',     'desc'=>'30 eggs per tray','unit'=>'trays'],
                            'eggs_crate'     => ['icon'=>'📦','label'=>'Eggs (Crate)',    'desc'=>'12 trays / 360 eggs','unit'=>'crates'],
                            'eggs_box'       => ['icon'=>'📦','label'=>'Eggs (Box)',       'desc'=>'360 eggs per box','unit'=>'boxes'],
                            'live_bird'      => ['icon'=>'🐓','label'=>'Live Bird',        'desc'=>'Per bird, live sale','unit'=>'birds'],
                            'meat_kg'        => ['icon'=>'🍗','label'=>'Meat (kg)',        'desc'=>'Dressed/processed meat','unit'=>'kg'],
                            'breeding_stock' => ['icon'=>'🧬','label'=>'Breeding Stock',  'desc'=>'Breeder animals sold','unit'=>'animals'],
                            'manure'         => ['icon'=>'💩','label'=>'Manure',          'desc'=>'Farm organic waste','unit'=>'bags/loads'],
                            'other'          => ['icon'=>'📦','label'=>'Other',           'desc'=>'Miscellaneous products','unit'=>'units'],
                        ];
                        $grpByType = $sales->getCollection()->groupBy('product_type');
                    @endphp
                    <div class="table-responsive mb-4">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th>Unit</th>
                                    <th class="text-center">Transactions</th>
                                    <th class="text-end">Qty Sold</th>
                                    <th class="text-end">Revenue</th>
                                    <th>Remaining / Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($productTypes as $type)
                                    @php
                                        $pm   = $allProductLabels[$type] ?? ['icon'=>'📦','label'=>ucfirst(str_replace('_',' ',$type)),'desc'=>'','unit'=>'units'];
                                        $grp  = $grpByType[$type] ?? collect();
                                        $rev  = $grp->sum('total_amount');
                                        $qty  = $grp->sum('quantity');
                                        $txs  = $grp->count();
                                        // Live bird remaining from flock headcount
                                        $remaining = null;
                                        if ($type === 'live_bird') {
                                            $remaining = $flocks->sum('current_count');
                                        }
                                    @endphp
                                    <tr>
                                        <td>
                                            <span class="me-2 fs-5">{{ $pm['icon'] }}</span>
                                            <div class="d-inline-block">
                                                <div class="fw-semibold">{{ $pm['label'] }}</div>
                                                <div class="small text-muted">{{ $pm['desc'] }}</div>
                                            </div>
                                        </td>
                                        <td><span class="badge bg-secondary-soft text-secondary">{{ $pm['unit'] }}</span></td>
                                        <td class="text-center"><span class="badge bg-light text-dark border">{{ $txs }}</span></td>
                                        <td class="text-end fw-semibold text-primary">{{ number_format($qty,2) }}</td>
                                        <td class="text-end fw-semibold text-success">₵{{ number_format($rev,2) }}</td>
                                        <td>
                                            @if($remaining !== null)
                                                <span class="badge {{ $remaining > 0 ? 'bg-success-soft text-success' : 'bg-danger-soft text-danger' }}">
                                                    <i class="fas fa-{{ $remaining > 0 ? 'check' : 'times' }} me-1"></i>
                                                    {{ number_format($remaining,0) }} birds in flock
                                                </span>
                                            @else
                                                <span class="badge bg-info-soft text-info">
                                                    <i class="fas fa-recycle me-1"></i>Continuous / variable
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Unused / Dormant Types --}}
                    @if(count($unusedTypes))
                    <h6 class="stat-section-title mb-3"><i class="fas fa-pause-circle me-2 text-muted"></i>Unused Product Types — Not sold in this period</h6>
                    <div class="row g-2">
                        @foreach($unusedTypes as $type)
                            @php $pm = $allProductLabels[$type] ?? ['icon'=>'📦','label'=>ucfirst(str_replace('_',' ',$type)),'desc'=>'']; @endphp
                            <div class="col-md-6">
                                <div class="d-flex align-items-center gap-2 p-3 rounded-3 border bg-light">
                                    <span class="fs-4">{{ $pm['icon'] }}</span>
                                    <div>
                                        <div class="fw-semibold text-muted">{{ $pm['label'] }}</div>
                                        <div class="small text-muted">{{ $pm['desc'] }} — <em>no sales this period</em></div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>


{{-- ═══════════════════════════════════════════════════════════
     EXISTING CRUD MODALS (unchanged)
═══════════════════════════════════════════════════════════ --}}

<!-- Create Sale Modal -->
<div class="modal fade" id="createSaleModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-success text-white border-0">
                <h5 class="modal-title text-white">
                    <i class="fas fa-plus-circle me-2"></i>Record Sale
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="createSaleContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-success" role="status"></div>
                    <p class="mt-2">Loading form...</p>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="saveCreateSale">Record Sale</button>
            </div>
        </div>
    </div>
</div>

<!-- View Sale Modal -->
<div class="modal fade" id="viewSaleModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title text-white">
                    <i class="fas fa-eye me-2"></i>Sale Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewSaleContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2">Loading sale details...</p>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Sale Modal -->
<div class="modal fade" id="editSaleModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-warning text-white border-0">
                <h5 class="modal-title text-white">
                    <i class="fas fa-edit me-2"></i>Edit Sale
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="editSaleContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-warning" role="status"></div>
                    <p class="mt-2">Loading sale details...</p>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="saveEditSale">Update Sale</button>
            </div>
        </div>
    </div>
</div>

<!-- Hidden form for delete -->
<form id="deleteSaleForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

@push('styles')
<style>
    .page-header { margin-bottom: 1.5rem; }

    .page-icon {
        width: 50px; height: 50px;
        display: flex; align-items: center; justify-content: center;
        background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
        border-radius: 12px;
    }
    .page-title { font-size: 1.75rem; font-weight: 600; color: #1e293b; }
    .page-description { font-size: 0.875rem; }

    /* ── Stat Cards ── */
    .stat-card {
        background: white; border-radius: 16px; padding: 1rem;
        transition: all 0.3s ease; border: 1px solid #e2e8f0;
    }
    .stat-card-clickable {
        cursor: pointer;
    }
    .stat-card-clickable:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0,0,0,.09);
        border-color: #10b981;
    }
    .stat-card-clickable:active { transform: translateY(-1px); }
    .stat-card-clickable:focus-visible { outline: 3px solid #10b981; outline-offset: 3px; }

    .stat-card-body { display: flex; align-items: center; gap: 1rem; }
    .stat-card-icon {
        width: 48px; height: 48px; display: flex; align-items: center;
        justify-content: center; border-radius: 12px; font-size: 1.5rem;
    }
    .stat-card-info { flex: 1; }
    .stat-card-label {
        font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;
        color: #64748b; font-weight: 600;
    }
    .stat-card-value { font-size: 1.75rem; font-weight: 700; margin: 0; line-height: 1.2; }
    .stat-card-chevron { color: #cbd5e1; transition: transform .2s, color .2s; }
    .stat-card-clickable:hover .stat-card-chevron { color: #10b981; transform: translateX(3px); }
    .stat-card-hint {
        font-size: .68rem; color: #94a3b8; text-align: right;
        margin-top: .35rem; opacity: 0; transition: opacity .2s;
    }
    .stat-card-clickable:hover .stat-card-hint { opacity: 1; }

    .bg-success-soft  { background: #dcfce7; }
    .bg-primary-soft  { background: #e0f2fe; }
    .bg-info-soft     { background: #ccfbf1; }
    .bg-warning-soft  { background: #fef3c7; }
    .bg-danger-soft   { background: #fee2e2; }
    .bg-secondary-soft{ background: #f1f5f9; }

    .filter-section { background: #f8fafc; border-radius: 12px; }

    .table th { font-weight: 600; font-size: 0.875rem; color: #475569; border-bottom-width: 1px; }
    .table td { font-size: 0.875rem; color: #334155; vertical-align: middle; }

    .btn-group .btn { border-radius: 8px !important; margin: 0 2px; padding: .25rem .5rem; }

    .pagination { margin-bottom: 0; }
    .page-link { border-radius: 8px; margin: 0 2px; border: none; color: #475569; padding: .5rem .875rem; }
    .page-item.active .page-link { background-color: #10b981; color: white; }
    .page-link:hover { background-color: #e2e8f0; color: #10b981; }

    /* Detail / view modal styles */
    .detail-section { margin-bottom: 1.5rem; }
    .detail-section h6 { font-weight: 600; color: #1e293b; margin-bottom: 1rem; padding-bottom: .5rem; border-bottom: 2px solid #e2e8f0; }
    .detail-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; }
    .detail-item { display: flex; flex-direction: column; }
    .detail-label { font-size: .7rem; text-transform: uppercase; color: #64748b; font-weight: 600; margin-bottom: .25rem; }
    .detail-value { font-size: 1rem; font-weight: 500; color: #1e293b; }

    /* ── Stat modal shared styles ── */
    .stat-modal-icon {
        width: 42px; height: 42px; background: rgba(255,255,255,.2); border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.1rem; color: #fff;
    }
    .stat-mini-card {
        border-radius: 12px; padding: .875rem 1rem; text-align: center;
    }
    .stat-mini-label { font-size: .7rem; text-transform: uppercase; letter-spacing: .6px; font-weight: 700; color: #64748b; }
    .stat-mini-value { font-size: 1.3rem; font-weight: 800; color: #0f172a; margin-top: .2rem; }
    .stat-mini-green  { background: #f0fdf4; border: 1px solid #bbf7d0; }
    .stat-mini-blue   { background: #eff6ff; border: 1px solid #bfdbfe; }
    .stat-mini-teal   { background: #f0fdfa; border: 1px solid #99f6e4; }
    .stat-mini-purple { background: #f5f3ff; border: 1px solid #ddd6fe; }
    .stat-mini-amber  { background: #fffbeb; border: 1px solid #fde68a; }
    .stat-section-title { font-size: .82rem; text-transform: uppercase; letter-spacing: .6px; font-weight: 700; color: #64748b; }
    .progress { background-color: #e2e8f0; border-radius: 10px; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    /* ── Stat card modal opener ── */
    window.openStatModal = function (type) {
        const map = {
            revenue:      'statRevenueModal',
            quantity:     'statQuantityModal',
            transactions: 'statTransactionsModal',
            products:     'statProductsModal',
        };
        const el = document.getElementById(map[type]);
        if (el) new bootstrap.Modal(el).show();
    };

    document.querySelectorAll('.stat-card-clickable').forEach(card => {
        card.addEventListener('keydown', e => {
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); card.click(); }
        });
    });

    /* ── Filters ── */
    document.getElementById('applyFilters')?.addEventListener('click', function () {
        const params = new URLSearchParams();
        const flock   = document.getElementById('flockFilter').value;
        const product = document.getElementById('productTypeFilter').value;
        const sd      = document.getElementById('startDateFilter').value;
        const ed      = document.getElementById('endDateFilter').value;
        if (flock)   params.append('flock_id', flock);
        if (product) params.append('product_type', product);
        if (sd)      params.append('start_date', sd);
        if (ed)      params.append('end_date', ed);
        window.location.href = '{{ route("sales.index") }}' + (params.toString() ? '?' + params : '');
    });

    /* ── Modal helpers ── */
    function closeAllModals() {
        document.querySelectorAll('.modal.show').forEach(m => bootstrap.Modal.getInstance(m)?.hide());
        const cleanup = () => {
            document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
        };
        setTimeout(cleanup, 310);
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str).replace(/[&<>]/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;'}[m] || m));
    }

    /* ── Create Sale ── */
    let createModal = null;

    function openCreateSaleModal() {
        closeAllModals();
        setTimeout(() => {
            const el = document.getElementById('createSaleModal');
            createModal = new bootstrap.Modal(el, { backdrop: 'static', keyboard: false });
            document.getElementById('createSaleContent').innerHTML = `<div class="text-center py-4"><div class="spinner-border text-success" role="status"></div><p class="mt-2">Loading form...</p></div>`;
            createModal.show();

            fetch('{{ route("sales.create-form") }}', {
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content, 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) displaySaleCreateForm(data.flocks);
                else document.getElementById('createSaleContent').innerHTML = `<div class="alert alert-danger m-3">Failed: ${data.message}</div>`;
            })
            .catch(e => { document.getElementById('createSaleContent').innerHTML = `<div class="alert alert-danger m-3">Error: ${e.message}</div>`; });
        }, 350);
    }

    document.getElementById('createSaleModal')?.addEventListener('hidden.bs.modal', () => {
        createModal?.dispose(); createModal = null;
        document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
        document.body.classList.remove('modal-open');
        document.body.style.overflow = ''; document.body.style.paddingRight = '';
    });

    function displaySaleCreateForm(flocks) {
        const fo = flocks.map(f => `<option value="${f.id}">${escapeHtml(f.flock_number)} - ${escapeHtml(f.breed_variety)}</option>`).join('');
        document.getElementById('createSaleContent').innerHTML = `
            <form id="createSaleForm"><div class="row">
                <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Product Type <span class="text-danger">*</span></label>
                    <select name="product_type" class="form-select" required>
                        <option value="">Select Product</option>
                        <option value="eggs_tray">🥚 Eggs (Tray - 30 eggs)</option>
                        <option value="eggs_crate">📦 Eggs (Crate - 12 trays)</option>
                        <option value="eggs_box">📦 Eggs (Box - 360 eggs)</option>
                        <option value="live_bird">🐓 Live Bird</option>
                        <option value="meat_kg">🍗 Meat (per kg)</option>
                        <option value="breeding_stock">🧬 Breeding Stock</option>
                        <option value="manure">💩 Manure</option>
                        <option value="other">📦 Other</option>
                    </select></div>
                <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Sale Date <span class="text-danger">*</span></label>
                    <input type="date" name="sale_date" class="form-control" value="${new Date().toISOString().split('T')[0]}" required></div>
                <div class="col-md-4 mb-3"><label class="form-label fw-semibold">Quantity <span class="text-danger">*</span></label>
                    <input type="number" name="quantity" id="quantityInput" class="form-control" step="0.01" min="0.01" placeholder="Quantity" required></div>
                <div class="col-md-4 mb-3"><label class="form-label fw-semibold">Unit Price (₵) <span class="text-danger">*</span></label>
                    <div class="input-group"><span class="input-group-text">₵</span>
                    <input type="number" name="unit_price" id="unitPriceInput" class="form-control" step="0.01" min="0.01" placeholder="0.00" required></div></div>
                <div class="col-md-4 mb-3"><label class="form-label fw-semibold">Total Amount (₵)</label>
                    <div class="input-group"><span class="input-group-text">₵</span>
                    <input type="number" name="total_amount" id="totalAmountInput" class="form-control" step="0.01" readonly style="background:#f8fafc;"></div>
                    <small class="text-muted">Auto-calculated</small></div>
                <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Customer Name</label>
                    <input type="text" name="customer_name" class="form-control" placeholder="Customer name"></div>
                <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Payment Method</label>
                    <select name="payment_method" class="form-select">
                        <option value="">Select Payment Method</option>
                        <option value="cash">💵 Cash</option>
                        <option value="bank_transfer">🏦 Bank Transfer</option>
                        <option value="mobile_money">📱 Mobile Money (MoMo)</option>
                        <option value="check">📝 Cheque</option>
                    </select></div>
                <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Receipt Number</label>
                    <input type="text" name="receipt_number" class="form-control" placeholder="Receipt/invoice number"></div>
                <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Associated Flock</label>
                    <select name="flock_id" class="form-select"><option value="">None - General Sale</option>${fo}</select></div>
                <div class="col-12 mb-3"><label class="form-label fw-semibold">Description</label>
                    <input type="text" name="description" class="form-control" placeholder="Brief description"></div>
                <div class="col-12 mb-3"><label class="form-label fw-semibold">Notes</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="Additional notes..."></textarea></div>
            </div></form>`;

        const qi = document.getElementById('quantityInput');
        const upi = document.getElementById('unitPriceInput');
        const tai = document.getElementById('totalAmountInput');
        const calc = () => { tai.value = ((parseFloat(qi.value)||0) * (parseFloat(upi.value)||0)).toFixed(2); };
        qi.addEventListener('input', calc); upi.addEventListener('input', calc);
    }

    document.getElementById('saveCreateSale')?.addEventListener('click', function () {
        const form = document.getElementById('createSaleForm');
        if (!form) return;
        const data = {};
        new FormData(form).forEach((v,k) => { data[k] = v; });
        if (!data.total_amount || data.total_amount == 0)
            data.total_amount = ((parseFloat(data.quantity)||0)*(parseFloat(data.unit_price)||0)).toFixed(2);
        if (!data.product_type || !data.sale_date || !data.quantity || !data.unit_price) {
            Swal.fire({ icon: 'error', title: 'Validation Error', text: 'Please fill in all required fields' }); return;
        }
        const btn = this; btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
        fetch('{{ route("sales.store-ajax") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content, 'Accept': 'application/json', 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) { createModal?.hide(); Swal.fire({ icon:'success', title:'Recorded!', text:'Sale recorded successfully', timer:1500, showConfirmButton:false }).then(()=>window.location.reload()); }
            else { Swal.fire({ icon:'error', title:'Error', text:data.message||'Failed to record sale' }); btn.disabled=false; btn.innerHTML='Record Sale'; }
        })
        .catch(e => { Swal.fire({ icon:'error', title:'Error', text:'An error occurred' }); btn.disabled=false; btn.innerHTML='Record Sale'; });
    });

    document.getElementById('newSaleBtn')?.addEventListener('click', openCreateSaleModal);
    document.getElementById('emptyStateNewBtn')?.addEventListener('click', openCreateSaleModal);

    /* ── View Sale ── */
    let viewModal = null;
    document.querySelectorAll('.view-sale-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            closeAllModals();
            const id = this.dataset.id;
            setTimeout(() => {
                const el = document.getElementById('viewSaleModal');
                viewModal = new bootstrap.Modal(el, { backdrop:'static', keyboard:true });
                document.getElementById('viewSaleContent').innerHTML = `<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Loading...</p></div>`;
                viewModal.show();
                fetch(`/sales/${id}/details-json`, { headers:{ 'Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]')?.content } })
                .then(r=>r.json())
                .then(data=>{ if(data.success) displaySaleDetails(data.sale); else document.getElementById('viewSaleContent').innerHTML=`<div class="alert alert-danger m-3">Failed: ${data.message}</div>`; })
                .catch(e=>{ document.getElementById('viewSaleContent').innerHTML=`<div class="alert alert-danger m-3">Error: ${e.message}</div>`; });
            }, 350);
        });
    });

    document.getElementById('viewSaleModal')?.addEventListener('hidden.bs.modal', () => {
        viewModal?.dispose(); viewModal = null;
        document.querySelectorAll('.modal-backdrop').forEach(b=>b.remove());
        document.body.classList.remove('modal-open'); document.body.style.overflow=''; document.body.style.paddingRight='';
    });

    function displaySaleDetails(sale) {
        document.getElementById('viewSaleContent').innerHTML = `
            <div class="detail-section p-4">
                <h6><i class="fas fa-info-circle me-2"></i>Sale Information</h6>
                <div class="detail-grid">
                    <div class="detail-item"><span class="detail-label">Date</span><span class="detail-value">${sale.sale_date}</span></div>
                    <div class="detail-item"><span class="detail-label">Product Type</span><span class="detail-value"><span class="badge bg-success">${escapeHtml(sale.product_type)}</span></span></div>
                    <div class="detail-item"><span class="detail-label">Quantity</span><span class="detail-value">${sale.quantity}</span></div>
                    <div class="detail-item"><span class="detail-label">Unit Price</span><span class="detail-value">₵${sale.unit_price}</span></div>
                    <div class="detail-item"><span class="detail-label">Total Amount</span><span class="detail-value text-success fs-5 fw-bold">₵${sale.total_amount}</span></div>
                    <div class="detail-item"><span class="detail-label">Customer</span><span class="detail-value">${escapeHtml(sale.customer_name)}</span></div>
                    <div class="detail-item"><span class="detail-label">Payment Method</span><span class="detail-value">${escapeHtml(sale.payment_method)}</span></div>
                    <div class="detail-item"><span class="detail-label">Receipt Number</span><span class="detail-value">${escapeHtml(sale.receipt_number)}</span></div>
                    <div class="detail-item"><span class="detail-label">Description</span><span class="detail-value">${escapeHtml(sale.description)}</span></div>
                    <div class="detail-item"><span class="detail-label">Recorded By</span><span class="detail-value">${escapeHtml(sale.recorded_by)}</span></div>
                    <div class="detail-item"><span class="detail-label">Recorded At</span><span class="detail-value">${sale.created_at}</span></div>
                </div>
            </div>
            <div class="detail-section px-4 pb-4">
                <h6><i class="fas fa-link me-2"></i>Associated Record</h6>
                <div class="detail-item">
                    <span class="detail-label">Flock</span>
                    <span class="detail-value">${sale.flock_number ? '<span class="badge bg-primary">'+escapeHtml(sale.flock_number)+(sale.flock_breed?' ('+escapeHtml(sale.flock_breed)+')':'')+'</span>' : 'None (General Sale)'}</span>
                </div>
            </div>
            ${sale.notes ? `<div class="detail-section px-4 pb-4"><h6><i class="fas fa-pencil-alt me-2"></i>Notes</h6><p class="mb-0 p-3 bg-light rounded">${escapeHtml(sale.notes)}</p></div>` : ''}
        `;
    }

    /* ── Edit Sale ── */
    let editModal = null;
    let currentEditId = null;

    document.querySelectorAll('.edit-sale-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            closeAllModals();
            currentEditId = this.dataset.id;
            setTimeout(() => {
                const el = document.getElementById('editSaleModal');
                editModal = new bootstrap.Modal(el, { backdrop:'static', keyboard:true });
                document.getElementById('editSaleContent').innerHTML = `<div class="text-center py-4"><div class="spinner-border text-warning" role="status"></div><p class="mt-2">Loading...</p></div>`;
                editModal.show();
                fetch(`/sales/${currentEditId}/edit-data`, { headers:{ 'Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]')?.content } })
                .then(r=>r.json())
                .then(data=>{ if(data.success) displaySaleEditForm(data.sale, data.flocks); else document.getElementById('editSaleContent').innerHTML=`<div class="alert alert-danger m-3">Failed: ${data.message}</div>`; })
                .catch(e=>{ document.getElementById('editSaleContent').innerHTML=`<div class="alert alert-danger m-3">Error: ${e.message}</div>`; });
            }, 350);
        });
    });

    document.getElementById('editSaleModal')?.addEventListener('hidden.bs.modal', () => {
        editModal?.dispose(); editModal = null; currentEditId = null;
        document.querySelectorAll('.modal-backdrop').forEach(b=>b.remove());
        document.body.classList.remove('modal-open'); document.body.style.overflow=''; document.body.style.paddingRight='';
    });

    function displaySaleEditForm(sale, flocks) {
        const fo = flocks.map(f => `<option value="${f.id}" ${sale.flock_id==f.id?'selected':''}>${escapeHtml(f.flock_number)} - ${escapeHtml(f.breed_variety)}</option>`).join('');
        const ptOpts = [['eggs_tray','🥚 Eggs (Tray - 30 eggs)'],['eggs_crate','📦 Eggs (Crate - 12 trays)'],['eggs_box','📦 Eggs (Box - 360 eggs)'],['live_bird','🐓 Live Bird'],['meat_kg','🍗 Meat (per kg)'],['breeding_stock','🧬 Breeding Stock'],['manure','💩 Manure'],['other','📦 Other']]
            .map(([v,l])=>`<option value="${v}" ${sale.product_type==v?'selected':''}>${l}</option>`).join('');
        document.getElementById('editSaleContent').innerHTML = `
            <form id="editSaleForm"><div class="row">
                <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Product Type <span class="text-danger">*</span></label>
                    <select name="product_type" class="form-select" required>${ptOpts}</select></div>
                <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Sale Date <span class="text-danger">*</span></label>
                    <input type="date" name="sale_date" class="form-control" value="${sale.sale_date}" required></div>
                <div class="col-md-4 mb-3"><label class="form-label fw-semibold">Quantity <span class="text-danger">*</span></label>
                    <input type="number" name="quantity" id="editQty" class="form-control" step="0.01" min="0.01" value="${sale.quantity}" required></div>
                <div class="col-md-4 mb-3"><label class="form-label fw-semibold">Unit Price (₵) <span class="text-danger">*</span></label>
                    <div class="input-group"><span class="input-group-text">₵</span>
                    <input type="number" name="unit_price" id="editUP" class="form-control" step="0.01" min="0.01" value="${sale.unit_price}" required></div></div>
                <div class="col-md-4 mb-3"><label class="form-label fw-semibold">Total (₵)</label>
                    <div class="input-group"><span class="input-group-text">₵</span>
                    <input type="number" name="total_amount" id="editTA" class="form-control" step="0.01" readonly style="background:#f8fafc;" value="${sale.total_amount}"></div></div>
                <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Customer Name</label>
                    <input type="text" name="customer_name" class="form-control" value="${escapeHtml(sale.customer_name||'')}"></div>
                <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Payment Method</label>
                    <select name="payment_method" class="form-select">
                        <option value="">Select Payment Method</option>
                        ${[['cash','💵 Cash'],['bank_transfer','🏦 Bank Transfer'],['mobile_money','📱 Mobile Money (MoMo)'],['check','📝 Cheque']].map(([v,l])=>`<option value="${v}" ${sale.payment_method==v?'selected':''}>${l}</option>`).join('')}
                    </select></div>
                <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Receipt Number</label>
                    <input type="text" name="receipt_number" class="form-control" value="${escapeHtml(sale.receipt_number||'')}"></div>
                <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Associated Flock</label>
                    <select name="flock_id" class="form-select"><option value="">None - General Sale</option>${fo}</select></div>
                <div class="col-12 mb-3"><label class="form-label fw-semibold">Description</label>
                    <input type="text" name="description" class="form-control" value="${escapeHtml(sale.description||'')}"></div>
                <div class="col-12 mb-3"><label class="form-label fw-semibold">Notes</label>
                    <textarea name="notes" class="form-control" rows="2">${escapeHtml(sale.notes||'')}</textarea></div>
            </div></form>`;
        const qi=document.getElementById('editQty'), upi=document.getElementById('editUP'), tai=document.getElementById('editTA');
        const calc=()=>{tai.value=((parseFloat(qi.value)||0)*(parseFloat(upi.value)||0)).toFixed(2);};
        qi.addEventListener('input',calc); upi.addEventListener('input',calc);
    }

    document.getElementById('saveEditSale')?.addEventListener('click', function () {
        const form = document.getElementById('editSaleForm');
        if (!form) return;
        const data = {};
        new FormData(form).forEach((v,k)=>{data[k]=v;});
        if (!data.total_amount||data.total_amount==0)
            data.total_amount=((parseFloat(data.quantity)||0)*(parseFloat(data.unit_price)||0)).toFixed(2);
        const btn=this; btn.disabled=true; btn.innerHTML='<i class="fas fa-spinner fa-spin me-2"></i>Updating...';
        fetch(`/sales/${currentEditId}/update-ajax`,{
            method:'PUT',
            headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]')?.content,'Accept':'application/json','Content-Type':'application/json'},
            body:JSON.stringify(data)
        })
        .then(r=>r.json())
        .then(data=>{
            if(data.success){editModal?.hide();Swal.fire({icon:'success',title:'Updated!',text:'Sale updated successfully',timer:1500,showConfirmButton:false}).then(()=>window.location.reload());}
            else{Swal.fire({icon:'error',title:'Error',text:data.message||'Failed'});btn.disabled=false;btn.innerHTML='Update Sale';}
        })
        .catch(()=>{Swal.fire({icon:'error',title:'Error',text:'An error occurred'});btn.disabled=false;btn.innerHTML='Update Sale';});
    });

    /* ── Delete ── */
    document.querySelectorAll('.delete-sale-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.dataset.id;
            Swal.fire({
                title: 'Delete Sale', text: 'Are you sure you want to delete this sale record?',
                icon: 'warning', showCancelButton: true,
                confirmButtonColor: '#dc2626', cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!', cancelButtonText: 'Cancel'
            }).then(result => {
                if (result.isConfirmed) {
                    const form = document.getElementById('deleteSaleForm');
                    form.action = `/sales/${id}`; form.submit();
                }
            });
        });
    });
});
</script>
@endpush

@endsection