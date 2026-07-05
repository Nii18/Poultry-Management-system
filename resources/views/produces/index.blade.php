@extends('layouts.master')

@section('title', 'Farm Produce Records')

@push('styles')
<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #059669 0%, #10b981 100%);
        --card-shadow: 0 20px 35px -12px rgba(0, 0, 0, 0.08);
        --hover-shadow: 0 25px 40px -12px rgba(0, 0, 0, 0.15);
        --transition-smooth: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* ── Stat cards ─────────────────────────────────────────── */
    .stat-card-modern {
        background: white; border-radius: 24px; padding: 1.5rem;
        position: relative; overflow: hidden; transition: var(--transition-smooth);
        border: 1px solid rgba(5,150,105,0.1); box-shadow: var(--card-shadow);
    }
    .stat-card-modern:hover { transform: translateY(-4px); box-shadow: var(--hover-shadow); }
    .stat-card-modern::before {
        content: ''; position: absolute; top: 0; left: 0; right: 0;
        height: 4px; background: var(--primary-gradient);
    }
    .stat-icon-modern {
        width: 52px; height: 52px; border-radius: 18px;
        display: flex; align-items: center; justify-content: center; font-size: 24px;
        background: linear-gradient(135deg, #f0fdf4, #dcfce7); color: #059669;
    }
    .stat-value-modern { font-size: 2rem; font-weight: 800; color: #1f2937; line-height: 1.2; margin-bottom: .25rem; }

    /* ── Filter bar ─────────────────────────────────────────── */
    .filter-bar-modern {
        background: white; border-radius: 20px; padding: 1.25rem 1.5rem; margin-bottom: 2rem;
        box-shadow: var(--card-shadow); border: 1px solid rgba(0,0,0,.05);
    }

    /* ── Table ──────────────────────────────────────────────── */
    .table-modern { border-radius: 20px; overflow: hidden; box-shadow: var(--card-shadow); background: white; }
    .table-modern thead th {
        background: linear-gradient(135deg,#f8fafc,#f1f5f9);
        font-weight: 600; font-size: .75rem; text-transform: uppercase;
        letter-spacing: .05em; color: #475569; padding: 1rem; border-bottom: 2px solid #e2e8f0;
    }
    .table-modern tbody tr { transition: var(--transition-smooth); border-bottom: 1px solid #f1f5f9; }
    .table-modern tbody tr:hover {
        background: linear-gradient(90deg,#fefce8,#fef9c3);
        transform: scale(1.01); box-shadow: 0 2px 8px rgba(0,0,0,.05);
    }

    /* ── Product badges ─────────────────────────────────────── */
    .badge-product-modern {
        font-size: .7rem; padding: .35rem .85rem; border-radius: 30px;
        font-weight: 600; letter-spacing: .02em; transition: var(--transition-smooth);
        display: inline-flex; align-items: center; gap: 6px;
    }
    .badge-product-modern:hover { transform: translateY(-1px); filter: brightness(.98); }
    .badge-eggs           { background: linear-gradient(135deg,#fef3c7,#fde68a); color: #92400e; }
    .badge-live_bird      { background: linear-gradient(135deg,#d1fae5,#a7f3d0); color: #065f46; }
    .badge-meat           { background: linear-gradient(135deg,#fee2e2,#fecaca); color: #991b1b; }
    .badge-breeding_stock { background: linear-gradient(135deg,#ede9fe,#ddd6fe); color: #4c1d95; }
    .badge-manure         { background: linear-gradient(135deg,#fef9c3,#fef08a); color: #713f12; }

    /* ── Action buttons ─────────────────────────────────────── */
    .action-btn {
        width: 34px; height: 34px; padding: 0; border-radius: 10px;
        display: inline-flex; align-items: center; justify-content: center;
        transition: var(--transition-smooth);
        background: #f8fafc; border: 1px solid #e2e8f0; color: #64748b; cursor: pointer;
    }
    .action-btn:hover { transform: translateY(-2px); }
    .action-btn.view:hover   { background: #0ea5e9; border-color: #0ea5e9; color: white; }
    .action-btn.edit:hover   { background: #f59e0b; border-color: #f59e0b; color: white; }
    .action-btn.delete:hover { background: #ef4444; border-color: #ef4444; color: white; }

    /* ── Quick banner ───────────────────────────────────────── */
    .quick-stat {
        background: linear-gradient(135deg,#059669,#10b981);
        border-radius: 16px; padding: .75rem 1.5rem; color: white; margin-bottom: 1.5rem;
    }

    /* ── Form controls ──────────────────────────────────────── */
    .form-control-modern, .form-select-modern {
        border-radius: 12px; border: 1.5px solid #e2e8f0;
        padding: .625rem 1rem; transition: var(--transition-smooth);
    }
    .form-control-modern:focus, .form-select-modern:focus {
        border-color: #059669; box-shadow: 0 0 0 3px rgba(5,150,105,.1);
    }

    /* ── Empty state ────────────────────────────────────────── */
    .empty-state { text-align: center; padding: 4rem 2rem; background: linear-gradient(135deg,#f8fafc,#f1f5f9); border-radius: 20px; }
    .empty-state i { font-size: 4rem; color: #cbd5e1; margin-bottom: 1rem; }

    /* ── Stock chips in table ─────────────────────────────────── */
    .stock-chip-mini {
        display: flex;
        flex-direction: column;
        padding: 0.25rem 0;
        border-bottom: 1px solid #f1f5f9;
    }
    .stock-chip-mini:last-child { border-bottom: none; }
    .stock-label { font-size: 0.65rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.03em; }
    .stock-value { font-size: 0.85rem; font-weight: 700; }
    .stock-value.total { color: #0369a1; }
    .stock-value.breeder { color: #5b21b6; }
    .stock-value.sellable { color: #065f46; }

    .progress-mini { height: 3px; background: #e2e8f0; border-radius: 99px; overflow: hidden; margin-top: 6px; }
    .progress-mini-fill { height: 100%; border-radius: 99px; background: linear-gradient(90deg, #059669, #10b981); transition: width 0.3s ease; }

    /* ── Sellable panel styles ───────────────────────────────── */
    .sellable-stock-panel {
        border-radius: 14px; padding: 1rem 1.1rem;
        border: 1.5px solid #a7f3d0;
        background: linear-gradient(135deg,#f0fdf4,#ecfdf5);
        transition: var(--transition-smooth);
    }
    .sellable-stock-panel.warn {
        border-color: #fcd34d;
        background: linear-gradient(135deg,#fffbeb,#fef3c7);
    }
    .sellable-stock-panel.danger {
        border-color: #fca5a5;
        background: linear-gradient(135deg,#fff5f5,#fee2e2);
    }
    .sellable-chip {
        display: inline-flex; flex-direction: column; align-items: center;
        padding: .5rem .9rem; border-radius: 12px;
        min-width: 72px; text-align: center;
    }
    .sellable-chip .chip-val { font-size: 1.25rem; font-weight: 800; line-height: 1; }
    .sellable-chip .chip-lbl { font-size: .6rem; text-transform: uppercase; letter-spacing: .05em; color: #64748b; margin-top: 3px; }
    .sellable-chip.total    { background: #e0f2fe; }
    .sellable-chip.breeder  { background: #ede9fe; }
    .sellable-chip.sellable { background: #dcfce7; }

    @media (max-width: 768px) {
        .stat-value-modern { font-size: 1.5rem; }
        .stat-icon-modern  { width: 44px; height: 44px; font-size: 20px; }
        .quick-stat        { flex-direction: column; text-align: center; }
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-3">

{{-- ── Page Header ─────────────────────────────────────────── --}}
<div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
    <div>
        <h1 class="display-6 fw-bold mb-2" style="background:linear-gradient(135deg,#065f46,#059669);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">
            <i class="fas fa-tractor me-2"></i>Farm Produce Records
        </h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active text-muted">Produce Management</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2 mt-3 mt-sm-0">
        @if(in_array(auth()->user()->role ?? '', ['admin','manager','accountant']))
        <a href="{{ route('produces.inventory') }}" class="btn btn-outline-success" style="border-radius:8px;padding:0.4rem 1rem;font-size:0.85rem;border-color:#d1d5db;color:#4b5563;">
            <i class="fas fa-chart-line me-1"></i>Analytics
        </a>
        @endif
        @if(in_array(auth()->user()->role ?? '', ['admin','manager','worker']))
        <button class="btn btn-success" id="newProduceBtn" style="background:var(--primary-gradient);border:none;border-radius:8px;padding:0.4rem 1rem;font-size:0.85rem;">
            <i class="fas fa-plus-circle me-1"></i>New Record
        </button>
        @endif
    </div>
</div>

    {{-- ── Monthly Produce Stat Cards ───────────────────────────── --}}
    @php
        $icons = ['eggs'=>'fa-egg','live_bird'=>'fa-dove','meat'=>'fa-drumstick-bite','manure'=>'fa-seedling'];
    @endphp
    <div class="row g-4 mb-5">
        @foreach($monthlyStats as $stat)
        <div class="col-md-6 col-xl-3">
            <div class="stat-card-modern" style="cursor:pointer;" onclick="openStatDetail('{{ $stat->product_type }}')">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="stat-icon-modern">{{ \App\Models\FarmProduce::productIcon($stat->product_type) }}</div>
                    <div class="text-end">
                        <small class="text-muted text-uppercase fw-semibold">This Month</small>
                        <div class="stat-value-modern">{{ number_format($stat->total_produced, 0) }}</div>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between">
                    <span class="fw-semibold">{{ ucwords(str_replace('_', ' ', $stat->product_type)) }}</span>
                    <span class="badge-product-modern badge-{{ $stat->product_type }}">
                        {{ \App\Models\FarmProduce::productIcon($stat->product_type) }}
                        {{ ucfirst(str_replace('_', ' ', $stat->product_type)) }}
                    </span>
                </div>
                <div class="mt-3">
                    <div class="progress" style="height:6px;border-radius:10px;background:#e2e8f0;">
                        <div class="progress-bar" style="width:100%;background:linear-gradient(90deg,#059669,#10b981);border-radius:10px;"></div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- ── Pre-calculate flock stock data for quick lookup in table ── --}}
    @php
        $flockStockMap = [];
        $activeFlocksForStock = \App\Models\Flock::where('status', 'active')
            ->with(['species', 'breederLogs' => fn($q) => $q->latest()->limit(1)])
            ->get()
            ->map(function ($f) use (&$flockStockMap) {
                $current  = (int) $f->current_count;
                $breeder  = (int) ($f->breederLogs->first()->breeder_count ?? 0);
                $sellable = max(0, $current - $breeder);
                $flockStockMap[$f->id] = (object) compact('current', 'breeder', 'sellable');
                return (object) compact('f', 'current', 'breeder', 'sellable');
            });
    @endphp

    {{-- ── Quick Insight Banner ─────────────────────────────────── --}}
    <div class="quick-stat d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="d-flex align-items-center gap-3">
            <i class="fas fa-chart-simple fa-2x"></i>
            <div>
                <small class="opacity-75">Total Production Value</small>
                <h5 class="mb-0 fw-bold">{{ number_format($totalQty, 2) }} units</h5>
            </div>
        </div>
        <div class="d-flex gap-4">
            <div>
                <small class="opacity-75">Records Found</small>
                <h6 class="mb-0"><i class="fas fa-database me-1"></i> {{ $produces->total() }} entries</h6>
            </div>
            <div>
                <small class="opacity-75">Date Range</small>
                <h6 class="mb-0"><i class="fas fa-calendar-alt me-1"></i>
                    {{ \Carbon\Carbon::parse($startDate)->format('d M') }} –
                    {{ \Carbon\Carbon::parse($endDate)->format('d M, Y') }}
                </h6>
            </div>
        </div>
    </div>

    {{-- ── Filters ──────────────────────────────────────────────── --}}
    <div class="filter-bar-modern">
        <form method="GET" action="{{ route('produces.index') }}" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label fw-semibold small text-uppercase text-muted mb-1">
                    <i class="fas fa-people-arrows me-1"></i>Select Flock
                </label>
                <select name="flock_id" class="form-select form-select-modern">
                    <option value="">All Flocks</option>
                    @foreach($flocks as $flock)
                        <option value="{{ $flock->id }}" {{ $flockId == $flock->id ? 'selected' : '' }}>
                            {{ $flock->flock_number }} - {{ $flock->breed_variety }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold small text-uppercase text-muted mb-1">
                    <i class="fas fa-tag me-1"></i>Product Type
                </label>
                <select name="product_type" class="form-select form-select-modern">
                    <option value="">All Types</option>
                    @foreach($activeProductTypes as $type)
                        <option value="{{ $type }}" {{ $productType === $type ? 'selected' : '' }}>
                            {{ ucwords(str_replace('_', ' ', $type)) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold small text-uppercase text-muted mb-1">
                    <i class="fas fa-calendar-alt me-1"></i>From Date
                </label>
                <input type="date" name="start_date" class="form-control form-control-modern" value="{{ $startDate }}">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold small text-uppercase text-muted mb-1">
                    <i class="fas fa-calendar-check me-1"></i>To Date
                </label>
                <input type="date" name="end_date" class="form-control form-control-modern" value="{{ $endDate }}">
            </div>
            <div class="col-md-3">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success px-4 py-2"><i class="fas fa-filter me-2"></i>Apply Filters</button>
                    <a href="{{ route('produces.index') }}" class="btn btn-outline-secondary px-4 py-2"><i class="fas fa-undo-alt me-2"></i>Reset</a>
                </div>
            </div>
        </form>
    </div>

    {{-- ── Produce Records Table (Enhanced with Stock Info) ─────── --}}
    <div class="table-modern">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4"><i class="fas fa-calendar-day me-2"></i>Date</th>
                        <th><i class="fas fa-boxes me-2"></i>Product</th>
                        <th><i class="fas fa-weight-hanging me-2"></i>Quantity</th>
                        <th><i class="fas fa-people-arrows me-2"></i>Flock</th>
                        <th><i class="fas fa-chart-line me-2"></i>Current Stock</th>
                        <th><i class="fas fa-user-check me-2"></i>Recorded By</th>
                        <th><i class="fas fa-sticky-note me-2"></i>Notes</th>
                        <th class="text-end pe-4"><i class="fas fa-cog me-2"></i>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($produces as $produce)
                    @php
                        $stockInfo = $produce->flock_id && isset($flockStockMap[$produce->flock_id]) ? $flockStockMap[$produce->flock_id] : null;
                    @endphp
                    <tr>
                        <td class="ps-4">
                            <div class="fw-semibold">{{ $produce->produce_date->format('d M Y') }}</div>
                            <small class="text-muted">{{ $produce->produce_date->diffForHumans() }}</small>
                        </td>
                        <td>
                            <span class="badge-product-modern badge-{{ $produce->product_type }}">
                                <i class="fas {{ $icons[$produce->product_type] ?? 'fa-box' }}"></i>
                                {{ $produce->product_type_label }}
                            </span>
                        </td>
                        <td>
                            <span class="fw-bold fs-5">{{ number_format($produce->net_quantity, 0) }}</span>
                            <span class="text-muted small ms-1">{{ $produce->unit }}</span>
                            @if($produce->quantity_damaged > 0)
                            <div style="font-size:.7rem;margin-top:2px;">
                                <span class="text-muted">{{ number_format($produce->quantity, 0) }} collected</span>
                                <span class="text-danger ms-1">· {{ number_format($produce->quantity_damaged, 0) }} dmg</span>
                            </div>
                            @endif
                        </td>
                        <td>
                            @if($produce->flock)
                                <div>
                                    <a href="{{ route('flocks.show', $produce->flock_id) }}" class="text-decoration-none fw-semibold">
                                        <i class="fas fa-people-arrows me-1"></i>{{ $produce->flock->flock_number }}
                                    </a>
                                    <div class="small text-muted">{{ $produce->flock->breed_variety ?? '' }}</div>
                                </div>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if($stockInfo)
                                <div style="min-width: 110px;">
                                    <div class="stock-chip-mini">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <span class="stock-label">Total</span>
                                            <span class="stock-value total">{{ number_format($stockInfo->current) }}</span>
                                        </div>
                                    </div>
                                    @if($stockInfo->breeder > 0)
                                    <div class="stock-chip-mini">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <span class="stock-label">Breeders</span>
                                            <span class="stock-value breeder">{{ number_format($stockInfo->breeder) }}</span>
                                        </div>
                                    </div>
                                    @endif
                                    <div class="stock-chip-mini">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <span class="stock-label">Sellable</span>
                                            <span class="stock-value sellable">{{ number_format($stockInfo->sellable) }}</span>
                                        </div>
                                    </div>
                                    @if($stockInfo->breeder > 0 && $stockInfo->current > 0)
                                    <div class="progress-mini">
                                        <div class="progress-mini-fill" style="width: {{ ($stockInfo->sellable / $stockInfo->current) * 100 }}%;"></div>
                                    </div>
                                    <div class="text-end mt-1">
                                        <small class="text-muted" style="font-size: 0.6rem;">{{ round(($stockInfo->sellable / $stockInfo->current) * 100) }}% sellable</small>
                                    </div>
                                    @elseif($stockInfo->breeder == 0 && $stockInfo->current > 0)
                                    <div class="progress-mini">
                                        <div class="progress-mini-fill" style="width: 100%;"></div>
                                    </div>
                                    <div class="text-end mt-1">
                                        <small class="text-success" style="font-size: 0.6rem;">100% sellable</small>
                                    </div>
                                    @endif
                                </div>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="bg-light rounded-circle p-1" style="width:32px;height:32px;display:flex;align-items:center;justify-content:center;">
                                    <i class="fas fa-user-circle text-muted"></i>
                                </div>
                                <span class="small">{{ $produce->creator->name ?? '—' }}</span>
                            </div>
                        </td>
                        <td>
                            @if($produce->notes)
                                <span class="text-muted small" data-bs-toggle="tooltip" title="{{ $produce->notes }}">
                                    <i class="fas fa-comment-dots me-1"></i>{{ Str::limit($produce->notes, 30) }}
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            <div class="d-flex gap-1 justify-content-end">
                                <button class="action-btn view" onclick="viewProduce({{ $produce->id }})" title="View Details"><i class="fas fa-eye"></i></button>
                                @if(in_array(auth()->user()->role ?? '', ['admin','manager']))
                                <button class="action-btn edit" onclick="editProduce({{ $produce->id }})" title="Edit Record"><i class="fas fa-pencil-alt"></i></button>
                                @endif
                                @if(in_array(auth()->user()->role ?? '', ['admin','manager']) || $produce->created_by === auth()->id())
                                <button class="action-btn delete" onclick="deleteProduce({{ $produce->id }})" title="Delete Record"><i class="fas fa-trash-alt"></i></button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8">
                            <div class="empty-state">
                                <i class="fas fa-box-open"></i>
                                <h5 class="mt-3">No Produce Records Found</h5>
                                <p class="text-muted">Start recording your farm produce to track production.</p>
                                @if(in_array(auth()->user()->role ?? '', ['admin','manager','worker']))
                                <button class="btn btn-success mt-2" id="newProduceBtnEmpty">
                                    <i class="fas fa-plus-circle me-2"></i>Record First Produce
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($produces->hasPages())
        <div class="p-3 bg-light border-top">{{ $produces->links() }}</div>
        @endif
    </div>
</div>

{{-- ── MODALS ─────────────────────────────────────────────── --}}

<div class="modal fade" id="createProduceModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:28px;border:none;overflow:hidden;">
            <div class="modal-header" style="background:linear-gradient(135deg,#059669,#10b981);color:white;padding:1.25rem 1.5rem;border:none;">
                <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Record New Produce</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="createProduceBody" style="padding:1.5rem;">
                <div class="text-center py-4"><div class="spinner-border text-success" role="status"></div><p class="mt-2">Loading form...</p></div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="viewProduceModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:28px;border:none;overflow:hidden;">
            <div class="modal-header" style="background:linear-gradient(135deg,#0ea5e9,#3b82f6);color:white;padding:1.25rem 1.5rem;border:none;">
                <h5 class="modal-title"><i class="fas fa-eye me-2"></i>Produce Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewProduceBody" style="padding:1.5rem;">
                <div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Loading details...</p></div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editProduceModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:28px;border:none;overflow:hidden;">
            <div class="modal-header" style="background:linear-gradient(135deg,#f59e0b,#d97706);color:white;padding:1.25rem 1.5rem;border:none;">
                <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Produce Record</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="editProduceBody" style="padding:1.5rem;">
                <div class="text-center py-4"><div class="spinner-border text-warning" role="status"></div><p class="mt-2">Loading form...</p></div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="statDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius:24px;overflow:hidden;">
            <div class="modal-header border-0" id="statDetailHeader">
                <h5 class="modal-title text-white fw-bold" id="statDetailTitle">Product Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="statDetailBody">
                <div class="text-center py-4"><div class="spinner-border text-success" role="status"></div><p class="mt-2">Loading...</p></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// ── Constants & globals ───────────────────────────────────────────────────────
const LIVE_BIRD_TYPES = ['live_bird', 'live_sale', 'breeding_stock'];
const CSRF = '{{ csrf_token() }}';
let _flockMap = {};

// ── Helpers ───────────────────────────────────────────────────────────────────
function ucfirst(str) { return str.replace(/_/g,' ').replace(/\b\w/g, c => c.toUpperCase()); }

function autoFillUnit(type, unitSelectId) {
    const map = { eggs:'pieces', milk:'litres', meat:'kg', live_bird:'birds', live_sale:'birds', manure:'bags', wool:'kg', honey:'kg', breeding_stock:'birds' };
    const sel = document.getElementById(unitSelectId);
    if (sel) sel.value = map[(type||'').toLowerCase().replace(/\s+/g,'_')] || 'units';
}

function fetchDefaultUnit(selectEl, unitInputId) {
    const type = selectEl.value; if (!type) return;
    fetch(`{{ url('/produces/unit') }}/${type}`).then(r=>r.json()).then(d=>{ const u=document.getElementById(unitInputId); if(u&&d.unit) u.value=d.unit; });
}

function refreshNetDisplay(qtyId, damagedId, displayId) {
    const qty=parseFloat(document.getElementById(qtyId)?.value)||0;
    const dmg=parseFloat(document.getElementById(damagedId)?.value)||0;
    const el=document.getElementById(displayId); if(!el) return;
    const net=Math.max(0,qty-dmg);
    if(dmg>qty){ el.textContent='⚠ Damaged cannot exceed total quantity'; el.style.color='#ef4444'; }
    else { el.textContent=`✅ Net available for sale: ${net.toFixed(2)}`; el.style.color='#059669'; }
}
function validateDamaged()     { refreshNetDisplay('cp_qty','cp_damaged','cp_net_display'); }
function editValidateDamaged() { refreshNetDisplay('ep_qty','ep_damaged','ep_net_display'); }

function isLiveBirdType(type) { return LIVE_BIRD_TYPES.includes((type||'').toLowerCase().replace(/\s+/g,'_')); }

// ── Sellable panel (create / edit modals) ─────────────────────────────────────
function updateSellablePanel(productType, flockId, panelId, qtyInputId) {
    const panel = document.getElementById(panelId);
    if (!panel) return;
    
    if (!isLiveBirdType(productType)) {
        panel.style.display = 'none';
        return;
    }
    
    panel.style.display = '';
    
    if (!flockId) {
        panel.className = 'sellable-stock-panel';
        panel.innerHTML = `<div class="d-flex align-items-center gap-2 text-muted" style="font-size:.85rem;">
            <i class="fas fa-info-circle"></i>Select a flock to see available sellable birds.
        </div>`;
        return;
    }
    
    const flock = _flockMap[flockId];
    if (!flock) {
        panel.style.display = 'none';
        return;
    }
    
    const qty = parseFloat(document.getElementById(qtyInputId)?.value) || 0;
    const { sellable_count: sellable, breeder_count: breeder, current_count: current, flock_number } = flock;
    
    let cls = 'sellable-stock-panel';
    let alert = '';
    
    if (qty > 0 && qty > sellable) {
        cls += ' danger';
        alert = `<div class="mt-2 d-flex align-items-center gap-2 p-2 rounded-3" style="background:#fee2e2;font-size:.8rem;color:#991b1b;">
            <i class="fas fa-triangle-exclamation"></i>
            <span><strong>${qty}</strong> exceeds the ${sellable.toLocaleString()} sellable birds. Reduce the quantity.</span>
        </div>`;
    } else if (qty > 0 && qty > sellable * 0.8) {
        cls += ' warn';
        alert = `<div class="mt-2 d-flex align-items-center gap-2 p-2 rounded-3" style="background:#fef3c7;font-size:.8rem;color:#92400e;">
            <i class="fas fa-circle-exclamation"></i>
            <span>Recording <strong>${qty}</strong> birds — only <strong>${(sellable - qty).toLocaleString()}</strong> remain after this.</span>
        </div>`;
    }
    
    const sellablePercent = current > 0 ? Math.round((sellable / current) * 100) : 0;
    const breederPercent = current > 0 ? Math.round((breeder / current) * 100) : 0;
    
    panel.className = cls;
    panel.innerHTML = `
        <div class="d-flex align-items-center gap-2 mb-2" style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#047857;">
            <i class="fas fa-dove"></i>Flock ${flock_number} — Current Stock Status
        </div>
        
        <div class="d-flex gap-2 flex-wrap mb-2">
            <div class="sellable-chip total">
                <span class="chip-val" style="color:#0369a1;">${current.toLocaleString()}</span>
                <span class="chip-lbl">Total</span>
            </div>
            <div class="d-flex align-items-center text-muted">−</div>
            <div class="sellable-chip breeder">
                <span class="chip-val" style="color:#5b21b6;">${breeder.toLocaleString()}</span>
                <span class="chip-lbl">Breeders</span>
            </div>
            <div class="d-flex align-items-center text-muted">=</div>
            <div class="sellable-chip sellable">
                <span class="chip-val" style="color:#065f46;">${sellable.toLocaleString()}</span>
                <span class="chip-lbl">Sellable</span>
            </div>
        </div>
        
        ${breeder > 0 && current > 0 ? `
        <div class="mb-2">
            <div class="progress" style="height: 6px; border-radius: 99px;">
                <div class="progress-bar bg-success" style="width: ${sellablePercent}%; border-radius: 99px;"></div>
                <div class="progress-bar bg-primary" style="width: ${breederPercent}%; border-radius: 99px;"></div>
            </div>
            <div class="d-flex justify-content-between mt-1" style="font-size:.65rem;">
                <span><span style="color:#059669;">●</span> Sellable ${sellablePercent}%</span>
                <span><span style="color:#3b82f6;">●</span> Breeders ${breederPercent}%</span>
            </div>
        </div>
        ` : breeder === 0 && current > 0 ? `
        <div class="mb-2">
            <div class="progress" style="height: 6px; border-radius: 99px;">
                <div class="progress-bar" style="width: 100%; background: linear-gradient(90deg, #059669, #10b981); border-radius: 99px;"></div>
            </div>
            <div class="text-center mt-1" style="font-size:.65rem; color:#065f46;">
                <i class="fas fa-check-circle me-1"></i>100% sellable — no breeders retained
            </div>
        </div>
        ` : ''}
        
        ${alert}
        
        ${qty > 0 && qty <= sellable ? `
        <div class="mt-2 d-flex align-items-center gap-2 p-2 rounded-3" style="background:#dcfce7;font-size:.75rem;color:#065f46;">
            <i class="fas fa-check-circle"></i>
            <span>Recording ${qty} birds. After this, ${(sellable - qty).toLocaleString()} sellable will remain.</span>
        </div>
        ` : ''}
        
        ${sellable === 0 && current > 0 ? `
        <div class="mt-2 d-flex align-items-center gap-2 p-2 rounded-3" style="background:#fee2e2;font-size:.75rem;color:#991b1b;">
            <i class="fas fa-ban"></i>
            <span>No sellable birds available in this flock. Consider adding more birds or reducing breeders.</span>
        </div>
        ` : ''}
    `;
}

// ── Tooltips ──────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));
});

// ── Reload helper ─────────────────────────────────────────────────────────────
function reloadAfterModal(modalId) {
    const el=document.getElementById(modalId), inst=bootstrap.Modal.getInstance(el);
    if(!inst){ window.location.reload(); return; }
    el.addEventListener('hidden.bs.modal',function h(){ el.removeEventListener('hidden.bs.modal',h); window.location.reload(); });
    inst.hide();
}

// ── CREATE modal ──────────────────────────────────────────────────────────────
function openCreateModal() {
    const body=document.getElementById('createProduceBody');
    body.innerHTML=`<div class="text-center py-4"><div class="spinner-border text-success" role="status"></div><p class="mt-2">Loading form...</p></div>`;
    new bootstrap.Modal(document.getElementById('createProduceModal')).show();
    fetch('{{ route("produces.create-form") }}').then(r=>r.json()).then(data=>{
        if(!data.success){ body.innerHTML=`<div class="alert alert-danger">${data.message}</div>`; return; }
        _flockMap={};
        data.flocks.forEach(f=>{ _flockMap[f.id]=f; });
        body.innerHTML=buildCreateForm(data);
    }).catch(()=>{ body.innerHTML=`<div class="alert alert-danger">Failed to load form.</div>`; });
}
document.getElementById('newProduceBtn')?.addEventListener('click', openCreateModal);
document.addEventListener('click', e=>{ if(e.target.closest('#newProduceBtnEmpty')) openCreateModal(); });

function buildCreateForm(data) {
    const flockOptions=data.flocks.map(f=>`<option value="${f.id}">${f.flock_number} – ${f.breed_variety} (${f.sellable_count} sellable)</option>`).join('');
    const allSugg=[...new Set([...data.existingTypes,...data.suggestions])];
    const datalistOpts=allSugg.map(t=>`<option value="${t}">${t.charAt(0).toUpperCase()+t.slice(1).replace(/_/g,' ')}</option>`).join('');
    const unitOpts=data.units.map(u=>`<option value="${u}">${u}</option>`).join('');
    const today=new Date().toISOString().split('T')[0];
    return `
    <form id="createProduceForm"><div class="row g-3">
        <div class="col-12">
            <label class="form-label fw-semibold">Product Type <span class="text-danger">*</span> <span class="badge bg-info-soft text-info ms-2 small">Free text</span></label>
            <input type="text" class="form-control" name="product_type" id="cp_product_type" list="productTypeSuggestions" placeholder="e.g. milk, eggs, live_bird…" required autocomplete="off" style="border-radius:12px;padding:.625rem 1rem;"
                oninput="autoFillUnit(this.value,'cp_unit'); updateSellablePanel(this.value,document.getElementById('cp_flock_id').value,'cp_sellable_panel','cp_qty')">
            <datalist id="productTypeSuggestions">${datalistOpts}</datalist>
            <small class="text-muted">Previously recorded types appear as suggestions.</small>
        </div>
        <div class="col-8">
            <label class="form-label fw-semibold">Total Quantity <span class="text-danger">*</span></label>
            <input type="number" class="form-control" name="quantity" id="cp_qty" step="0.01" min="0.01" required placeholder="e.g. 120" style="border-radius:12px;padding:.625rem 1rem;"
                oninput="validateDamaged(); updateSellablePanel(document.getElementById('cp_product_type').value,document.getElementById('cp_flock_id').value,'cp_sellable_panel','cp_qty')">
        </div>
        <div class="col-4">
            <label class="form-label fw-semibold">Unit <span class="text-danger">*</span></label>
            <select class="form-control" name="unit" id="cp_unit" required style="border-radius:12px;padding:.625rem 1rem;">${unitOpts}</select>
        </div>
        <div class="col-12">
            <label class="form-label fw-semibold">Damaged / Unusable <span class="text-muted small fw-normal">(e.g. broken eggs)</span></label>
            <input type="number" class="form-control" name="quantity_damaged" id="cp_damaged" step="0.01" min="0" value="0" style="border-radius:12px;padding:.625rem 1rem;" oninput="validateDamaged()">
            <small class="text-muted d-block mt-1" id="cp_net_display"></small>
        </div>
        <div class="col-12">
            <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
            <input type="date" class="form-control" name="produce_date" required value="${today}" max="${today}" style="border-radius:12px;padding:.625rem 1rem;">
        </div>
        <div class="col-12">
            <label class="form-label fw-semibold">Flock <span class="text-muted small fw-normal">(optional)</span></label>
            <select class="form-control" name="flock_id" id="cp_flock_id" style="border-radius:12px;padding:.625rem 1rem;"
                onchange="updateSellablePanel(document.getElementById('cp_product_type').value,this.value,'cp_sellable_panel','cp_qty')">
                <option value="">— No specific flock —</option>${flockOptions}
            </select>
        </div>
        <div class="col-12"><div id="cp_sellable_panel" class="sellable-stock-panel" style="display:none;"></div></div>
        <div class="col-12">
            <label class="form-label fw-semibold">Notes <span class="text-muted small fw-normal">(optional)</span></label>
            <textarea class="form-control" name="notes" rows="2" placeholder="e.g. Morning collection, House A" style="border-radius:12px;padding:.625rem 1rem;"></textarea>
        </div>
    </div>
    <div class="d-flex gap-2 mt-4">
        <button type="button" class="btn btn-secondary flex-grow-1" data-bs-dismiss="modal" style="border-radius:12px;">Cancel</button>
        <button type="submit" class="btn btn-success flex-grow-1" id="createProduceSubmit" style="border-radius:12px;background:linear-gradient(135deg,#059669,#10b981);border:none;">
            <span class="submit-text"><i class="fas fa-save me-1"></i>Save Record</span>
            <span class="spinner-border spinner-border-sm d-none" role="status"></span>
        </button>
    </div></form>`;
}

// ── CREATE submit ─────────────────────────────────────────────────────────────
document.addEventListener('submit', function(e) {
    if (!e.target.matches('#createProduceForm')) return;
    e.preventDefault();
    const dmg=parseFloat(document.getElementById('cp_damaged')?.value)||0;
    const qty=parseFloat(document.getElementById('cp_qty')?.value)||0;
    if(dmg>qty){ Swal.fire({icon:'warning',title:'Invalid Entry',text:'Damaged quantity cannot exceed total collected.',confirmButtonColor:'#059669'}); return; }
    const pt=document.getElementById('cp_product_type')?.value||'';
    const fid=document.getElementById('cp_flock_id')?.value;
    if(isLiveBirdType(pt)&&fid&&_flockMap[fid]){
        if(qty>_flockMap[fid].sellable_count){ Swal.fire({icon:'error',title:'Exceeds Sellable Stock',html:`You entered <strong>${qty}</strong> birds but only <strong>${_flockMap[fid].sellable_count}</strong> are sellable in flock <strong>${_flockMap[fid].flock_number}</strong>.`,confirmButtonColor:'#059669'}); return; }
    }
    const btn=document.getElementById('createProduceSubmit');
    btn.querySelector('.submit-text').classList.add('d-none');
    btn.querySelector('.spinner-border').classList.remove('d-none');
    btn.disabled=true;
    fetch('{{ route("produces.store-ajax") }}',{method:'POST',headers:{'X-CSRF-TOKEN':CSRF,'Content-Type':'application/json','Accept':'application/json'},body:JSON.stringify(Object.fromEntries(new FormData(e.target).entries()))})
    .then(r=>r.json()).then(data=>{
        if(data.success){ Swal.fire({icon:'success',title:'Saved!',text:data.message||'Produce record created.',timer:2000,showConfirmButton:false,timerProgressBar:true}).then(()=>reloadAfterModal('createProduceModal')); }
        else { Swal.fire({icon:'error',title:'Error!',text:data.message,confirmButtonColor:'#d33'}); btn.querySelector('.submit-text').classList.remove('d-none'); btn.querySelector('.spinner-border').classList.add('d-none'); btn.disabled=false; }
    }).catch(()=>{ Swal.fire({icon:'error',title:'Error!',text:'Something went wrong.',confirmButtonColor:'#d33'}); btn.querySelector('.submit-text').classList.remove('d-none'); btn.querySelector('.spinner-border').classList.add('d-none'); btn.disabled=false; });
});

// ── VIEW with Stock Information ──────────────────────────────────────────────
function viewProduce(id) {
    const body = document.getElementById('viewProduceBody');
    body.innerHTML = `<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Loading details...</p></div>`;
    new bootstrap.Modal(document.getElementById('viewProduceModal')).show();
    
    fetch(`/produces/${id}/details-json`).then(r => r.json()).then(data => {
        if (!data.success) {
            body.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
            return;
        }
        
        const p = data.produce;
        const hd = parseFloat(p.quantity_damaged) > 0;
        const hasStock = p.stock_info && p.stock_info.current > 0;
        
        let stockHtml = '';
        if (hasStock) {
            const sellablePercent = p.stock_info.current > 0 ? Math.round((p.stock_info.sellable / p.stock_info.current) * 100) : 0;
            const breederPercent = p.stock_info.current > 0 ? Math.round((p.stock_info.breeder / p.stock_info.current) * 100) : 0;
            
            stockHtml = `
            <div class="row mb-3">
                <div class="col-4 text-muted">Current Stock</div>
                <div class="col-8">
                    <div class="bg-light p-3 rounded-3" style="background: linear-gradient(135deg, #f8fafc, #f1f5f9);">
                        <div class="d-flex justify-content-around mb-2">
                            <div class="text-center">
                                <div class="small text-muted">Total</div>
                                <div class="fw-bold fs-5" style="color: #0369a1;">${p.stock_info.current.toLocaleString()}</div>
                            </div>
                            ${p.stock_info.breeder > 0 ? `
                            <div class="text-center">
                                <div class="small text-muted">Breeders</div>
                                <div class="fw-bold fs-5" style="color: #5b21b6;">${p.stock_info.breeder.toLocaleString()}</div>
                            </div>
                            ` : ''}
                            <div class="text-center">
                                <div class="small text-muted">Sellable</div>
                                <div class="fw-bold fs-5" style="color: #065f46;">${p.stock_info.sellable.toLocaleString()}</div>
                            </div>
                        </div>
                        ${p.stock_info.breeder > 0 ? `
                        <div class="progress mb-2" style="height: 8px; border-radius: 99px;">
                            <div class="progress-bar bg-success" style="width: ${sellablePercent}%; border-radius: 99px;"></div>
                            <div class="progress-bar bg-primary" style="width: ${breederPercent}%; border-radius: 99px;"></div>
                        </div>
                        <div class="d-flex justify-content-between small text-muted">
                            <span><span style="color: #059669;">●</span> Sellable ${sellablePercent}%</span>
                            <span><span style="color: #3b82f6;">●</span> Breeders ${breederPercent}%</span>
                        </div>
                        ` : p.stock_info.current > 0 ? `
                        <div class="progress mb-2" style="height: 8px; border-radius: 99px;">
                            <div class="progress-bar" style="width: 100%; background: linear-gradient(90deg, #059669, #10b981); border-radius: 99px;"></div>
                        </div>
                        <div class="text-center small text-success">
                            <i class="fas fa-check-circle me-1"></i>100% sellable - no breeders retained
                        </div>
                        ` : ''}
                    </div>
                </div>
            </div>`;
        }
        
        body.innerHTML = `
        <div class="mb-3 pb-2 border-bottom">
            <div class="d-flex justify-content-between align-items-center">
                <span class="badge-product-modern badge-${p.product_type.toLowerCase()}">${p.product_type_label}</span>
                <small class="text-muted">${p.created_at}</small>
            </div>
        </div>
        
        <div class="row mb-3">
            <div class="col-4 text-muted">Date</div>
            <div class="col-8 fw-semibold">${p.produce_date}</div>
        </div>
        
        <div class="row mb-3">
            <div class="col-4 text-muted">Collected</div>
            <div class="col-8">
                <span class="fw-bold fs-5">${p.quantity}</span> 
                <span class="text-muted ms-1">${p.unit}</span>
            </div>
        </div>
        
        ${hd ? `
        <div class="row mb-3">
            <div class="col-4 text-muted">Damaged</div>
            <div class="col-8">
                <span class="fw-bold fs-5 text-danger">${p.quantity_damaged}</span> 
                <span class="text-muted ms-1">${p.unit}</span>
            </div>
        </div>
        ` : ''}
        
        <div class="row mb-3">
            <div class="col-4 text-muted">Net Available</div>
            <div class="col-8">
                <span class="fw-bold fs-5 text-success">${p.net_quantity}</span>
                <span class="text-muted ms-1">${p.unit}</span>
                ${hd ? `<small class="text-muted ms-2">(${p.quantity} − ${p.quantity_damaged})</small>` : ''}
            </div>
        </div>
        
        <div class="row mb-3">
            <div class="col-4 text-muted">Flock</div>
            <div class="col-8">
                ${p.flock_number !== 'N/A' ? `
                    <div class="fw-semibold">${p.flock_number}</div>
                    ${p.flock_breed ? `<small class="text-muted">${p.flock_breed}</small>` : ''}
                ` : '<span class="text-muted">—</span>'}
            </div>
        </div>
        
        ${stockHtml}
        
        <div class="row mb-3">
            <div class="col-4 text-muted">Notes</div>
            <div class="col-8 text-muted">${p.notes || '—'}</div>
        </div>
        
        <div class="row mb-3">
            <div class="col-4 text-muted">Recorded by</div>
            <div class="col-8">
                <i class="fas fa-user-circle me-1"></i>${p.recorded_by}
            </div>
        </div>`;
    }).catch(() => {
        body.innerHTML = `<div class="alert alert-danger">Failed to load details.</div>`;
    });
}

// ── EDIT with Stock Information Panel ─────────────────────────────────────────
function editProduce(id) {
    const body = document.getElementById('editProduceBody');
    body.innerHTML = `<div class="text-center py-4"><div class="spinner-border text-warning" role="status"></div><p class="mt-2">Loading form...</p></div>`;
    new bootstrap.Modal(document.getElementById('editProduceModal')).show();
    
    fetch(`/produces/${id}/edit-data`).then(r => r.json()).then(data => {
        if (!data.success) {
            body.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
            return;
        }
        
        _flockMap = {};
        data.flocks.forEach(f => { _flockMap[f.id] = f; });
        
        const p = data.produce;
        const flockOpts = data.flocks.map(f => `<option value="${f.id}" ${f.id == p.flock_id ? 'selected' : ''}>${f.flock_number} – ${f.breed_variety} (${f.sellable_count} sellable)</option>`).join('');
        const typeOpts = data.existingTypes.map(t => `<option value="${t}" ${t === p.product_type ? 'selected' : ''}>${ucfirst(t)}</option>`).join('');
        const units = ['pieces', 'birds', 'kg', 'bags', 'litres', 'trays', 'crates', 'units'];
        const unitOpts = units.map(u => `<option value="${u}" ${u === p.unit ? 'selected' : ''}>${u}</option>`).join('');
        
        body.innerHTML = `
        <form id="editProduceForm" data-id="${p.id}">
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label fw-semibold">Product Type <span class="text-danger">*</span></label>
                    <select class="form-control" name="product_type" id="ep_product_type" required style="border-radius:12px;padding:.625rem 1rem;"
                        onchange="fetchDefaultUnit(this,'ep_unit'); updateSellablePanel(this.value, document.getElementById('ep_flock_id').value, 'ep_sellable_panel', 'ep_qty')">
                        ${typeOpts}
                    </select>
                </div>
                
                <div class="col-8">
                    <label class="form-label fw-semibold">Total Collected <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" name="quantity" id="ep_qty" step="0.01" min="0.01" value="${p.quantity}" required style="border-radius:12px;padding:.625rem 1rem;"
                        oninput="editValidateDamaged(); updateSellablePanel(document.getElementById('ep_product_type').value, document.getElementById('ep_flock_id').value, 'ep_sellable_panel', 'ep_qty')">
                </div>
                
                <div class="col-4">
                    <label class="form-label fw-semibold">Unit</label>
                    <select class="form-control" name="unit" id="ep_unit" style="border-radius:12px;padding:.625rem 1rem;">
                        ${unitOpts}
                    </select>
                </div>
                
                <div class="col-12">
                    <label class="form-label fw-semibold">Damaged / Unusable</label>
                    <input type="number" class="form-control" name="quantity_damaged" id="ep_damaged" step="0.01" min="0" value="${p.quantity_damaged ?? 0}" style="border-radius:12px;padding:.625rem 1rem;" oninput="editValidateDamaged()">
                    <small id="ep_net_display" class="mt-1 d-block"></small>
                </div>
                
                <div class="col-12">
                    <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" name="produce_date" value="${p.produce_date}" required style="border-radius:12px;padding:.625rem 1rem;">
                </div>
                
                <div class="col-12">
                    <label class="form-label fw-semibold">Flock</label>
                    <select class="form-control" name="flock_id" id="ep_flock_id" style="border-radius:12px;padding:.625rem 1rem;"
                        onchange="updateSellablePanel(document.getElementById('ep_product_type').value, this.value, 'ep_sellable_panel', 'ep_qty')">
                        <option value="">— No specific flock —</option>
                        ${flockOpts}
                    </select>
                </div>
                
                <div class="col-12">
                    <div id="ep_sellable_panel" class="sellable-stock-panel" style="display:none;"></div>
                </div>
                
                <div class="col-12">
                    <label class="form-label fw-semibold">Notes</label>
                    <textarea class="form-control" name="notes" rows="3" style="border-radius:12px;padding:.625rem 1rem;">${p.notes || ''}</textarea>
                </div>
            </div>
            
            <div class="d-flex gap-2 mt-4">
                <button type="button" class="btn btn-secondary flex-grow-1" data-bs-dismiss="modal" style="border-radius:12px;">Cancel</button>
                <button type="submit" class="btn btn-warning flex-grow-1" id="editProduceSubmit" style="border-radius:12px;">
                    <span class="submit-text"><i class="fas fa-save me-1"></i>Save Changes</span>
                    <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                </button>
            </div>
        </form>`;
        
        editValidateDamaged();
        updateSellablePanel(p.product_type, p.flock_id ? String(p.flock_id) : '', 'ep_sellable_panel', 'ep_qty');
        
    }).catch(() => {
        body.innerHTML = `<div class="alert alert-danger">Failed to load form.</div>`;
    });
}

// ── EDIT submit ───────────────────────────────────────────────────────────────
document.addEventListener('submit', function(e) {
    if (!e.target.matches('#editProduceForm')) return;
    e.preventDefault();
    const dmg=parseFloat(document.getElementById('ep_damaged')?.value)||0;
    const qty=parseFloat(document.getElementById('ep_qty')?.value)||0;
    if(dmg>qty){ Swal.fire({icon:'warning',title:'Invalid Entry',text:'Damaged cannot exceed total.',confirmButtonColor:'#f59e0b'}); return; }
    const pt=document.getElementById('ep_product_type')?.value||'';
    const fid=document.getElementById('ep_flock_id')?.value;
    if(isLiveBirdType(pt)&&fid&&_flockMap[fid]&&qty>_flockMap[fid].sellable_count){
        Swal.fire({icon:'error',title:'Exceeds Sellable Stock',html:`You entered <strong>${qty}</strong> birds but only <strong>${_flockMap[fid].sellable_count}</strong> are sellable.`,confirmButtonColor:'#f59e0b'}); return;
    }
    const id=e.target.dataset.id, btn=document.getElementById('editProduceSubmit');
    btn.querySelector('.submit-text').classList.add('d-none'); btn.querySelector('.spinner-border').classList.remove('d-none'); btn.disabled=true;
    fetch(`/produces/${id}/update-ajax`,{method:'PUT',headers:{'X-CSRF-TOKEN':CSRF,'Content-Type':'application/json','Accept':'application/json'},body:JSON.stringify(Object.fromEntries(new FormData(e.target).entries()))})
    .then(r=>{ if(!r.ok) return r.json().then(d=>Promise.reject(d)); return r.json(); })
    .then(data=>{
        if(data.success){ Swal.fire({icon:'success',title:'Updated!',text:data.message||'Record updated.',timer:2000,showConfirmButton:false,timerProgressBar:true}).then(()=>reloadAfterModal('editProduceModal')); }
        else { Swal.fire({icon:'error',title:'Error!',text:data.message,confirmButtonColor:'#d33'}); btn.querySelector('.submit-text').classList.remove('d-none'); btn.querySelector('.spinner-border').classList.add('d-none'); btn.disabled=false; }
    }).catch(err=>{ Swal.fire({icon:'error',title:'Error!',text:err?.message||'Something went wrong.',confirmButtonColor:'#d33'}); btn.querySelector('.submit-text').classList.remove('d-none'); btn.querySelector('.spinner-border').classList.add('d-none'); btn.disabled=false; });
});

// ── DELETE ────────────────────────────────────────────────────────────────────
function deleteProduce(id) {
    Swal.fire({title:'Delete this record?',text:'This cannot be undone.',icon:'warning',showCancelButton:true,confirmButtonColor:'#ef4444',cancelButtonColor:'#6c757d',confirmButtonText:'Yes, delete it!'})
    .then(result=>{ if(!result.isConfirmed) return;
        Swal.fire({title:'Deleting…',text:'Please wait',allowOutsideClick:false,didOpen:()=>Swal.showLoading()});
        fetch(`/produces/${id}`,{method:'POST',headers:{'X-CSRF-TOKEN':CSRF,'Content-Type':'application/json','Accept':'application/json','X-HTTP-Method-Override':'DELETE'},body:JSON.stringify({_method:'DELETE'}),credentials:'same-origin'})
        .then(r=>{ if(!r.ok) return r.json().then(d=>Promise.reject(d)); return r.json(); })
        .then(data=>{
            if(data.success){
                document.querySelectorAll('table tbody tr').forEach(row=>{ if(row.querySelector(`[onclick="deleteProduce(${id})"]`)){ row.style.transition='opacity .3s'; row.style.opacity='0'; setTimeout(()=>row.remove(),300); } });
                Swal.fire({icon:'success',title:'Deleted!',text:data.message||'Record deleted.',timer:1500,showConfirmButton:false,timerProgressBar:true}).then(()=>window.location.reload());
            } else { Swal.fire({icon:'error',title:'Not Allowed',text:data.message||'Failed to delete.',confirmButtonColor:'#d33'}); }
        }).catch(err=>{ Swal.fire({icon:'error',title:'Error!',text:err?.message||'Network error.',confirmButtonColor:'#d33'}); });
    });
}

// ── STAT CARD DETAIL ──────────────────────────────────────────────────────────
let _statTabState='month';
function setStatTab(tab) {
    _statTabState=tab;
    document.querySelectorAll('.stat-tab-btn').forEach(b=>b.classList.toggle('active',b.dataset.tab===tab));
    document.querySelectorAll('.stat-tab-pane').forEach(p=>{ p.style.display=p.dataset.pane===tab?'':'none'; });
}

function buildSpeciesBreakdown(sb, total) {
    if(!sb||!sb.length) return '';
    const cards=sb.map(s=>{
        const pct=total>0?Math.round((parseFloat(s.all_time.produced.replace(/,/g,''))/total)*100):0;
        return `<div class="species-card mb-3">
            <div class="species-card-header">
                <div class="d-flex align-items-center gap-2"><span class="species-badge">${s.species_code}</span><span class="fw-semibold">${s.species_name}</span><span class="text-muted small">${s.flock_count} flock${s.flock_count!==1?'s':''} · ${s.record_count} record${s.record_count!==1?'s':''}</span></div>
                <div class="text-end"><small class="text-muted">${pct}% of total</small><div class="species-share-bar" style="width:80px;"><div class="species-share-bar-fill" style="width:${pct}%;"></div></div></div>
            </div>
            <div class="p-3">
                <div class="mb-2"><div class="species-section-title"><i class="fas fa-calendar-day" style="color:#059669;"></i>This Month${s.this_month.damage_pct>0?`<span class="badge" style="background:#fee2e2;color:#991b1b;font-size:.65rem;">${s.this_month.damage_pct}% dmg</span>`:''}</div>
                    <div class="d-flex flex-wrap gap-2">
                        <div class="species-stat-pill" style="background:#f0fdf4;border:1px solid #bbf7d0;"><span class="val text-success">${s.this_month.produced}</span><span class="lbl">Collected</span></div>
                        <div class="species-stat-pill" style="background:#fee2e2;border:1px solid #fecaca;"><span class="val text-danger">${s.this_month.damaged}</span><span class="lbl">Damaged</span></div>
                        <div class="species-stat-pill" style="background:#eff6ff;border:1px solid #bfdbfe;"><span class="val text-primary">${s.this_month.available}</span><span class="lbl">Net Avail.</span></div>
                    </div>
                </div>
                <div><div class="species-section-title"><i class="fas fa-history" style="color:#94a3b8;"></i>All Time${s.all_time.damage_pct>0?`<span class="badge" style="background:#fef3c7;color:#92400e;font-size:.65rem;">${s.all_time.damage_pct}% dmg</span>`:''}</div>
                    <div class="d-flex flex-wrap gap-2">
                        <div class="species-stat-pill" style="background:#f8fafc;border:1px solid #e2e8f0;"><span class="val" style="color:#059669;">${s.all_time.produced}</span><span class="lbl">Produced</span></div>
                        <div class="species-stat-pill" style="background:#f8fafc;border:1px solid #e2e8f0;"><span class="val text-danger">${s.all_time.damaged}</span><span class="lbl">Damaged</span></div>
                        <div class="species-stat-pill" style="background:#f8fafc;border:1px solid #e2e8f0;"><span class="val text-primary">${s.all_time.available}</span><span class="lbl">Net Avail.</span></div>
                    </div>
                </div>
            </div>
        </div>`;
    }).join('');
    return `<div class="species-breakdown-section"><div class="species-section-title mb-3"><i class="fas fa-layer-group" style="color:#7c3aed;"></i>Breakdown by Species<span class="badge" style="background:#ede9fe;color:#4c1d95;font-size:.7rem;">${sb.length} species</span></div>${cards}</div>`;
}

function buildFlockBreakdown(fb, total) {
    if(!fb||!fb.length) return '';
    const rows=fb.map(f=>{
        const share=total>0?Math.round((f.raw_produced/total)*100):0;
        return `<tr>
            <td class="fw-semibold ps-3"><i class="fas fa-layer-group me-1 text-muted" style="font-size:.75rem;"></i>${f.flock_number}</td>
            <td><span class="species-badge" style="font-size:.65rem;">${f.species_code}</span><span class="ms-1 small text-muted">${f.breed_variety}</span></td>
            <td class="fw-bold text-success">${f.produced}</td><td class="text-danger">${f.damaged}</td><td class="text-primary fw-semibold">${f.available}</td>
            <td><div class="d-flex align-items-center gap-2"><div style="flex:1;height:5px;background:#e2e8f0;border-radius:99px;min-width:50px;"><div style="width:${share}%;height:100%;background:linear-gradient(90deg,#059669,#10b981);border-radius:99px;"></div></div><span class="small text-muted">${share}%</span></div></td>
            <td class="text-end pe-3">${f.damage_pct>0?`<span class="badge" style="background:#fee2e2;color:#991b1b;font-size:.65rem;">${f.damage_pct}%</span>`:`<span class="text-muted small">—</span>`}</td>
        </table>`;
    }).join('');
    return `<div class="mb-4"><div class="species-section-title mb-2"><i class="fas fa-trophy" style="color:#d97706;"></i>Top Contributing Flocks<span class="badge" style="background:#fef3c7;color:#92400e;font-size:.7rem;">all time</span></div>
    <div class="table-responsive"><table class="table table-sm align-middle mb-0" style="font-size:.82rem;">
        <thead><tr style="background:#f8fafc;"><th class="ps-3 fw-semibold text-muted" style="font-size:.7rem;">Flock</th><th class="fw-semibold text-muted" style="font-size:.7rem;">Species / Breed</th><th class="fw-semibold text-muted" style="font-size:.7rem;">Produced</th><th class="fw-semibold text-muted" style="font-size:.7rem;">Damaged</th><th class="fw-semibold text-muted" style="font-size:.7rem;">Net Avail.</th><th class="fw-semibold text-muted" style="font-size:.7rem;">Share</th><th class="text-end pe-3 fw-semibold text-muted" style="font-size:.7rem;">Dmg %</th></tr></thead>
        <tbody>${rows}</tbody>
    </table></div></div>`;
}

function openStatDetail(productType) {
    const modal=new bootstrap.Modal(document.getElementById('statDetailModal'));
    const header=document.getElementById('statDetailHeader'), title=document.getElementById('statDetailTitle'), body=document.getElementById('statDetailBody');
    _statTabState='month';
    body.innerHTML=`<div class="text-center py-4"><div class="spinner-border text-success" role="status"></div><p class="mt-2">Loading...</p></div>`;
    modal.show();
    fetch(`/produces/stat/${productType}`).then(r=>r.json()).then(data=>{
        if(!data.success){ body.innerHTML=`<div class="alert alert-danger">${data.message}</div>`; return; }
        const m=data.this_month, a=data.all_time, mon=new Date().toLocaleString('default',{month:'long',year:'numeric'});
        header.style.background='linear-gradient(135deg,#059669,#10b981)';
        title.innerHTML=`${data.icon} ${data.label} — Detailed Breakdown`;
        const rawTotal=parseFloat(a.produced.replace(/,/g,''))||0;
        const speciesHtml=data.has_species_breakdown?buildSpeciesBreakdown(data.species_breakdown,rawTotal):'';
        const flockHtml=buildFlockBreakdown(data.flock_breakdown,rawTotal);
        const recentHtml=data.recent_records.length>0?`<div class="mb-2"><div class="species-section-title mb-2"><i class="fas fa-clock" style="color:#64748b;"></i>5 Most Recent Records</div>
        <div class="table-responsive"><table class="table table-sm table-hover align-middle" style="font-size:.82rem;"><thead class="table-light"><tr><th>Date</th><th>Flock</th><th>Species</th><th class="text-end">Collected</th><th class="text-end text-danger">Damaged</th><th class="text-end text-success">Net</th><th>Unit</th></tr></thead>
        <tbody>${data.recent_records.map(r=>`<tr><td class="fw-semibold">${r.date}</td><td>${r.flock}</td><td>${r.species_code!=='—'?`<span class="species-badge" style="font-size:.6rem;">${r.species_code}</span>`:'<span class="text-muted">—</span>'}</td><td class="text-end">${r.quantity}</td><td class="text-end text-danger">${r.damaged}</td><td class="text-end text-success fw-bold">${r.net}</td><td class="text-muted small">${r.unit}</td></tr>`).join('')}</tbody>
        </table></div></div>`:'';
        body.innerHTML=`
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div class="tab-toggle">
                <button class="stat-tab-btn active" data-tab="month" onclick="setStatTab('month')"><i class="fas fa-calendar-alt me-1"></i>This Month</button>
                <button class="stat-tab-btn" data-tab="alltime" onclick="setStatTab('alltime')"><i class="fas fa-history me-1"></i>All Time</button>
            </div>
            <small class="text-muted">${mon}</small>
        </div>
        <div class="stat-tab-pane" data-pane="month">
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-4"><div class="p-3 rounded-3 text-center" style="background:#f0fdf4;border:1px solid #bbf7d0;"><div class="fw-bold fs-4 text-success">${m.produced}</div><div class="small text-muted">Total Collected</div></div></div>
                <div class="col-6 col-md-4"><div class="p-3 rounded-3 text-center" style="background:#fee2e2;border:1px solid #fecaca;"><div class="fw-bold fs-4 text-danger">${m.damaged}</div><div class="small text-muted">Damaged</div>${m.damage_pct>0?`<div class="small text-danger">${m.damage_pct}% rate</div>`:''}</div></div>
                <div class="col-6 col-md-4"><div class="p-3 rounded-3 text-center" style="background:#eff6ff;border:1px solid #bfdbfe;"><div class="fw-bold fs-4 text-primary">${m.available}</div><div class="small text-muted">Net Available</div></div></div>
                <div class="col-6 col-md-4"><div class="p-3 rounded-3 text-center" style="background:#fef3c7;border:1px solid #fde68a;"><div class="fw-bold fs-4" style="color:#d97706;">${m.sold}</div><div class="small text-muted">Sold This Month</div></div></div>
                <div class="col-6 col-md-4"><div class="p-3 rounded-3 text-center" style="background:#f0fdf4;border:1px solid #bbf7d0;"><div class="fw-bold fs-4 text-success">₵${m.revenue}</div><div class="small text-muted">Revenue This Month</div></div></div>
                <div class="col-6 col-md-4"><div class="p-3 rounded-3 text-center" style="background:#fdf4ff;border:1px solid #e9d5ff;"><div class="fw-bold fs-4" style="color:#7c3aed;">${m.remaining}</div><div class="small text-muted">Ready for Sale</div></div></div>
            </div>
            ${speciesHtml}${recentHtml}
        </div>
        <div class="stat-tab-pane" data-pane="alltime" style="display:none;">
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3"><div class="p-3 rounded-3 text-center bg-light"><div class="fw-bold fs-4 text-success">${a.produced}</div><div class="small text-muted">Ever Produced</div></div></div>
                <div class="col-6 col-md-3"><div class="p-3 rounded-3 text-center bg-light"><div class="fw-bold fs-4 text-danger">${a.damaged}</div><div class="small text-muted">Total Damaged</div>${a.damage_pct>0?`<div class="small text-danger">${a.damage_pct}%</div>`:''}</div></div>
                <div class="col-6 col-md-3"><div class="p-3 rounded-3 text-center bg-light"><div class="fw-bold fs-4 text-warning">${a.sold}</div><div class="small text-muted">Total Sold</div></div></div>
                <div class="col-6 col-md-3"><div class="p-3 rounded-3 text-center bg-light"><div class="fw-bold fs-4 text-success">₵${a.revenue}</div><div class="small text-muted">Total Revenue</div></div></div>
            </div>
            ${speciesHtml}${flockHtml}
        </div>`;
    }).catch(err=>{ body.innerHTML=`<div class="alert alert-danger">Error: ${err.message}</div>`; });
}
</script>
@endpush