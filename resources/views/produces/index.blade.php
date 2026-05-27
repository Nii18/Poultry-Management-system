@extends('layouts.master')

@section('title', 'Farm Produce Records')

@push('styles')
<style>
    /* Modern Variables */
    :root {
        --primary-gradient: linear-gradient(135deg, #059669 0%, #10b981 100%);
        --card-shadow: 0 20px 35px -12px rgba(0, 0, 0, 0.08);
        --hover-shadow: 0 25px 40px -12px rgba(0, 0, 0, 0.15);
        --transition-smooth: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .stat-card-modern {
        background: white;
        border-radius: 24px;
        padding: 1.5rem;
        position: relative;
        overflow: hidden;
        transition: var(--transition-smooth);
        border: 1px solid rgba(5, 150, 105, 0.1);
        box-shadow: var(--card-shadow);
    }
    .stat-card-modern:hover { transform: translateY(-4px); box-shadow: var(--hover-shadow); }
    .stat-card-modern::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 4px;
        background: var(--primary-gradient);
    }
    .stat-icon-modern {
        width: 52px; height: 52px;
        border-radius: 18px;
        display: flex; align-items: center; justify-content: center;
        font-size: 24px;
        background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
        color: #059669;
    }
    .stat-value-modern { font-size: 2rem; font-weight: 800; color: #1f2937; line-height: 1.2; margin-bottom: 0.25rem; }
    .filter-bar-modern {
        background: white; border-radius: 20px;
        padding: 1.25rem 1.5rem; margin-bottom: 2rem;
        box-shadow: var(--card-shadow); border: 1px solid rgba(0,0,0,0.05);
    }
    .table-modern { border-radius: 20px; overflow: hidden; box-shadow: var(--card-shadow); background: white; }
    .table-modern thead th {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        font-weight: 600; font-size: 0.75rem;
        text-transform: uppercase; letter-spacing: 0.05em;
        color: #475569; padding: 1rem; border-bottom: 2px solid #e2e8f0;
    }
    .table-modern tbody tr { transition: var(--transition-smooth); border-bottom: 1px solid #f1f5f9; }
    .table-modern tbody tr:hover {
        background: linear-gradient(90deg, #fefce8 0%, #fef9c3 100%);
        transform: scale(1.01);
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    .badge-product-modern {
        font-size: 0.7rem; padding: 0.35rem 0.85rem; border-radius: 30px;
        font-weight: 600; letter-spacing: 0.02em;
        transition: var(--transition-smooth);
        display: inline-flex; align-items: center; gap: 6px;
    }
    .badge-product-modern:hover { transform: translateY(-1px); filter: brightness(0.98); }
    .badge-eggs           { background: linear-gradient(135deg, #fef3c7, #fde68a); color: #92400e; }
    .badge-live_bird      { background: linear-gradient(135deg, #d1fae5, #a7f3d0); color: #065f46; }
    .badge-meat           { background: linear-gradient(135deg, #fee2e2, #fecaca); color: #991b1b; }
    .badge-breeding_stock { background: linear-gradient(135deg, #ede9fe, #ddd6fe); color: #4c1d95; }
    .badge-manure         { background: linear-gradient(135deg, #fef9c3, #fef08a); color: #713f12; }
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
    .quick-stat {
        background: linear-gradient(135deg, #059669, #10b981);
        border-radius: 16px; padding: 0.75rem 1.5rem; color: white; margin-bottom: 1.5rem;
    }
    .form-control-modern, .form-select-modern {
        border-radius: 12px; border: 1.5px solid #e2e8f0;
        padding: 0.625rem 1rem; transition: var(--transition-smooth);
    }
    .form-control-modern:focus, .form-select-modern:focus {
        border-color: #059669; box-shadow: 0 0 0 3px rgba(5,150,105,0.1);
    }
    .empty-state { text-align: center; padding: 4rem 2rem; background: linear-gradient(135deg, #f8fafc, #f1f5f9); border-radius: 20px; }
    .empty-state i { font-size: 4rem; color: #cbd5e1; margin-bottom: 1rem; }

    /* ── Species breakdown styles ───────────────────────────────── */
    .species-breakdown-section { margin-bottom: 1.5rem; }
    .species-card {
        border: 1px solid #e2e8f0; border-radius: 16px; overflow: hidden;
        transition: var(--transition-smooth);
    }
    .species-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.08); transform: translateY(-2px); }
    .species-card-header {
        padding: 0.75rem 1rem;
        display: flex; align-items: center; justify-content: space-between;
        background: linear-gradient(135deg, #f8fafc, #f1f5f9);
        border-bottom: 1px solid #e2e8f0;
    }
    .species-badge {
        font-size: 0.7rem; font-weight: 700; padding: 0.25rem 0.6rem;
        border-radius: 20px; letter-spacing: 0.05em; text-transform: uppercase;
        background: #1e293b; color: #f8fafc;
    }
    .species-stat-pill {
        display: inline-flex; flex-direction: column; align-items: center;
        padding: 0.6rem 0.9rem; border-radius: 12px;
        font-size: 0.8rem; min-width: 80px; text-align: center;
    }
    .species-stat-pill .val { font-size: 1.15rem; font-weight: 800; line-height: 1.1; }
    .species-stat-pill .lbl { font-size: 0.65rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.04em; margin-top: 2px; }
    .species-share-bar { height: 6px; border-radius: 99px; background: #e2e8f0; overflow: hidden; margin-top: 0.5rem; }
    .species-share-bar-fill { height: 100%; border-radius: 99px; background: linear-gradient(90deg, #059669, #10b981); transition: width 0.6s cubic-bezier(0.4,0,0.2,1); }
    .tab-toggle { display: flex; background: #f1f5f9; border-radius: 12px; padding: 3px; gap: 2px; }
    .tab-toggle button {
        flex: 1; border: none; background: transparent; border-radius: 10px;
        padding: 0.4rem 0.75rem; font-size: 0.75rem; font-weight: 600;
        color: #64748b; cursor: pointer; transition: var(--transition-smooth);
    }
    .tab-toggle button.active { background: white; color: #059669; box-shadow: 0 1px 4px rgba(0,0,0,0.1); }
    .species-section-title {
        font-size: 0.75rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: 0.06em; color: #64748b; margin-bottom: 0.75rem;
        display: flex; align-items: center; gap: 0.5rem;
    }

    @media (max-width: 768px) {
        .stat-value-modern { font-size: 1.5rem; }
        .stat-icon-modern { width: 44px; height: 44px; font-size: 20px; }
        .quick-stat { flex-direction: column; text-align: center; }
        .species-stat-pill { min-width: 60px; padding: 0.4rem 0.5rem; }
        .species-stat-pill .val { font-size: 0.95rem; }
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-3">

    {{-- Page Header --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
        <div>
            <h1 class="display-6 fw-bold mb-2" style="background: linear-gradient(135deg, #065f46, #059669); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
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
            <a href="{{ route('produces.inventory') }}" class="btn btn-outline-success btn-lg">
                <i class="fas fa-chart-line me-2"></i>Inventory Analytics
            </a>
            @endif
            @if(in_array(auth()->user()->role ?? '', ['admin','manager','worker']))
            <button class="btn btn-success btn-lg shadow-sm" id="newProduceBtn" style="background: var(--primary-gradient); border: none;">
                <i class="fas fa-plus-circle me-2"></i>New Record
            </button>
            @endif
        </div>
    </div>

    {{-- Summary Stats --}}
    @php
        $icons = [
            'eggs' => 'fa-egg',
            'live_bird' => 'fa-dove',
            'meat' => 'fa-drumstick-bite',
            'manure' => 'fa-seedling'
        ];
    @endphp
    <div class="row g-4 mb-5">
        @foreach($monthlyStats as $stat)
        <div class="col-md-6 col-xl-3">
            <div class="stat-card-modern" style="cursor:pointer;" onclick="openStatDetail('{{ $stat->product_type }}')">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="stat-icon-modern">
                        {{ \App\Models\FarmProduce::productIcon($stat->product_type) }}
                    </div>
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
                    <div class="progress" style="height: 6px; border-radius: 10px; background: #e2e8f0;">
                        <div class="progress-bar" style="width: 100%; background: linear-gradient(90deg, #059669, #10b981); border-radius: 10px;"></div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Quick Insight Banner --}}
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
                <h6 class="mb-0"><i class="fas fa-calendar-alt me-1"></i> {{ \Carbon\Carbon::parse($startDate)->format('d M') }} - {{ \Carbon\Carbon::parse($endDate)->format('d M, Y') }}</h6>
            </div>
        </div>
    </div>

    {{-- Filter Section --}}
    <div class="filter-bar-modern">
        <form method="GET" action="{{ route('produces.index') }}" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label fw-semibold small text-uppercase text-muted mb-1">
                    <i class="fas fa-people-arrows me-1"></i> Select Flock
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
                    <i class="fas fa-tag me-1"></i> Product Type
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
                    <i class="fas fa-calendar-alt me-1"></i> From Date
                </label>
                <input type="date" name="start_date" class="form-control form-control-modern" value="{{ $startDate }}">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold small text-uppercase text-muted mb-1">
                    <i class="fas fa-calendar-check me-1"></i> To Date
                </label>
                <input type="date" name="end_date" class="form-control form-control-modern" value="{{ $endDate }}">
            </div>
            <div class="col-md-3">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success px-4 py-2">
                        <i class="fas fa-filter me-2"></i>Apply Filters
                    </button>
                    <a href="{{ route('produces.index') }}" class="btn btn-outline-secondary px-4 py-2">
                        <i class="fas fa-undo-alt me-2"></i>Reset
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- Data Table --}}
    <div class="table-modern">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4"><i class="fas fa-calendar-day me-2"></i>Date</th>
                        <th><i class="fas fa-boxes me-2"></i>Product</th>
                        <th><i class="fas fa-weight-hanging me-2"></i>Quantity</th>
                        <th><i class="fas fa-people-arrows me-2"></i>Flock</th>
                        <th><i class="fas fa-user-check me-2"></i>Recorded By</th>
                        <th><i class="fas fa-sticky-note me-2"></i>Notes</th>
                        <th class="text-end pe-4"><i class="fas fa-cog me-2"></i>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($produces as $produce)
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
                            <div style="font-size:0.7rem; margin-top:2px;">
                                <span class="text-muted">{{ number_format($produce->quantity, 0) }} collected</span>
                                <span class="text-danger ms-1">· {{ number_format($produce->quantity_damaged, 0) }} dmg</span>
                            </div>
                            @endif
                        </td>
                        <td>
                            @if($produce->flock)
                                <a href="{{ route('flocks.show', $produce->flock_id) }}" class="text-decoration-none fw-semibold">
                                    <i class="fas fa-people-arrows me-1"></i>{{ $produce->flock->flock_number }}
                                </a>
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
                                <button class="action-btn view" onclick="viewProduce({{ $produce->id }})" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </button>
                                @if(in_array(auth()->user()->role ?? '', ['admin','manager']))
                                <button class="action-btn edit" onclick="editProduce({{ $produce->id }})" title="Edit Record">
                                    <i class="fas fa-pencil-alt"></i>
                                </button>
                                @endif
                                @if(in_array(auth()->user()->role ?? '', ['admin','manager']) || $produce->created_by === auth()->id())
                                <button class="action-btn delete" onclick="deleteProduce({{ $produce->id }})" title="Delete Record">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7">
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
        <div class="p-3 bg-light border-top">
            {{ $produces->links() }}
        </div>
        @endif
    </div>
</div>

{{-- CREATE MODAL --}}
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

{{-- VIEW MODAL --}}
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

{{-- EDIT MODAL --}}
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

{{-- STAT DETAIL MODAL --}}
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
{{-- SweetAlert2 CDN (load BEFORE any script that calls Swal) --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// ─── Helpers ──────────────────────────────────────────────────────────────────

function ucfirst(str) {
    return str.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
}

function autoFillUnit(type, unitSelectId) {
    const unitMap = {
        eggs: 'pieces', milk: 'litres', meat: 'kg',
        live_bird: 'birds', manure: 'bags', wool: 'kg',
        honey: 'kg', breeding_stock: 'birds'
    };
    const key  = type.toLowerCase().replace(/\s+/g, '_');
    const unit = unitMap[key] || 'units';
    const sel  = document.getElementById(unitSelectId);
    if (sel) sel.value = unit;
}

function fetchDefaultUnit(selectEl, unitInputId) {
    const type = selectEl.value;
    if (!type) return;
    fetch(`{{ url('/produces/unit') }}/${type}`)
        .then(r => r.json())
        .then(d => {
            const u = document.getElementById(unitInputId);
            if (u && d.unit) u.value = d.unit;
        });
}

function refreshNetDisplay(qtyId, damagedId, displayId) {
    const qty     = parseFloat(document.getElementById(qtyId)?.value)     || 0;
    const damaged = parseFloat(document.getElementById(damagedId)?.value) || 0;
    const display = document.getElementById(displayId);
    if (!display) return;
    const net = Math.max(0, qty - damaged);
    if (damaged > qty) {
        display.textContent = '⚠ Damaged cannot exceed total quantity';
        display.style.color = '#ef4444';
    } else {
        display.textContent = `✅ Net available for sale: ${net.toFixed(2)}`;
        display.style.color = '#059669';
    }
}

function validateDamaged()     { refreshNetDisplay('cp_qty', 'cp_damaged', 'cp_net_display'); }
function editValidateDamaged() { refreshNetDisplay('ep_qty', 'ep_damaged', 'ep_net_display'); }

// ─── CSRF token ───────────────────────────────────────────────────────────────
const CSRF = '{{ csrf_token() }}';

// ─── Init tooltips ────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));
});

// ─── Shared reload helper — waits for modal to fully hide before reloading ────
function reloadAfterModal(modalId) {
    const modalEl = document.getElementById(modalId);
    const instance = bootstrap.Modal.getInstance(modalEl);

    if (!instance) {
        window.location.reload();
        return;
    }

    modalEl.addEventListener('hidden.bs.modal', function handler() {
        modalEl.removeEventListener('hidden.bs.modal', handler);
        window.location.reload();
    });

    instance.hide();
}

// ─── Open CREATE modal ────────────────────────────────────────────────────────
function openCreateModal() {
    const body = document.getElementById('createProduceBody');
    body.innerHTML = `<div class="text-center py-4"><div class="spinner-border text-success" role="status"></div><p class="mt-2">Loading form...</p></div>`;
    new bootstrap.Modal(document.getElementById('createProduceModal')).show();

    fetch('{{ route("produces.create-form") }}')
        .then(r => r.json())
        .then(data => {
            if (!data.success) { body.innerHTML = `<div class="alert alert-danger">${data.message}</div>`; return; }
            body.innerHTML = buildCreateForm(data);
        })
        .catch(() => { body.innerHTML = `<div class="alert alert-danger">Failed to load form.</div>`; });
}

document.getElementById('newProduceBtn')?.addEventListener('click', openCreateModal);
document.addEventListener('click', function (e) {
    if (e.target.closest('#newProduceBtnEmpty')) openCreateModal();
});

function buildCreateForm(data) {
    const flockOptions = data.flocks.map(f =>
        `<option value="${f.id}">${f.flock_number} – ${f.breed_variety}</option>`
    ).join('');
    const allSuggestions  = [...new Set([...data.existingTypes, ...data.suggestions])];
    const datalistOptions = allSuggestions.map(t =>
        `<option value="${t}">${t.charAt(0).toUpperCase() + t.slice(1).replace(/_/g, ' ')}</option>`
    ).join('');
    const unitOptions = data.units.map(u => `<option value="${u}">${u}</option>`).join('');
    const today = new Date().toISOString().split('T')[0];

    return `
    <form id="createProduceForm">
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label fw-semibold">Product Type <span class="text-danger">*</span>
                    <span class="badge bg-info-soft text-info ms-2 small">Free text — type anything</span>
                </label>
                <input type="text" class="form-control" name="product_type" list="productTypeSuggestions"
                    placeholder="e.g. milk, eggs, wool, honey..." required autocomplete="off"
                    style="border-radius:12px;padding:.625rem 1rem;"
                    onchange="autoFillUnit(this.value,'cp_unit')">
                <datalist id="productTypeSuggestions">${datalistOptions}</datalist>
                <small class="text-muted">Previously recorded types appear as suggestions.</small>
            </div>
            <div class="col-8">
                <label class="form-label fw-semibold">Total Quantity Collected <span class="text-danger">*</span></label>
                <input type="number" class="form-control" name="quantity" id="cp_qty"
                    step="0.01" min="0.01" required placeholder="e.g. 120"
                    style="border-radius:12px;padding:.625rem 1rem;"
                    oninput="validateDamaged()">
            </div>
            <div class="col-4">
                <label class="form-label fw-semibold">Unit <span class="text-danger">*</span></label>
                <select class="form-control" name="unit" id="cp_unit" required
                    style="border-radius:12px;padding:.625rem 1rem;">${unitOptions}</select>
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Damaged / Unusable Quantity
                    <span class="text-muted small fw-normal">(e.g. broken eggs, spoiled milk)</span>
                </label>
                <input type="number" class="form-control" name="quantity_damaged" id="cp_damaged"
                    step="0.01" min="0" value="0" placeholder="0"
                    style="border-radius:12px;padding:.625rem 1rem;"
                    oninput="validateDamaged()">
                <small class="text-muted d-block mt-1" id="cp_net_display"></small>
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                <input type="date" class="form-control" name="produce_date" required
                    value="${today}" max="${today}"
                    style="border-radius:12px;padding:.625rem 1rem;">
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Flock <span class="text-muted small fw-normal">(optional)</span></label>
                <select class="form-control" name="flock_id" style="border-radius:12px;padding:.625rem 1rem;">
                    <option value="">— No specific flock —</option>
                    ${flockOptions}
                </select>
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Notes <span class="text-muted small fw-normal">(optional)</span></label>
                <textarea class="form-control" name="notes" rows="2"
                    placeholder="e.g. Morning collection, House A"
                    style="border-radius:12px;padding:.625rem 1rem;"></textarea>
            </div>
        </div>
        <div class="d-flex gap-2 mt-4">
            <button type="button" class="btn btn-secondary flex-grow-1" data-bs-dismiss="modal" style="border-radius:12px;">Cancel</button>
            <button type="submit" class="btn btn-success flex-grow-1" id="createProduceSubmit"
                style="border-radius:12px;background:linear-gradient(135deg,#059669,#10b981);border:none;">
                <span class="submit-text"><i class="fas fa-save me-1"></i>Save Record</span>
                <span class="spinner-border spinner-border-sm d-none" role="status"></span>
            </button>
        </div>
    </form>`;
}

// ─── CREATE submit ────────────────────────────────────────────────────────────
document.addEventListener('submit', function (e) {
    if (!e.target.matches('#createProduceForm')) return;
    e.preventDefault();

    const damaged = parseFloat(document.getElementById('cp_damaged')?.value) || 0;
    const qty     = parseFloat(document.getElementById('cp_qty')?.value) || 0;
    if (damaged > qty) {
        Swal.fire({ icon: 'warning', title: 'Invalid Entry', text: 'Damaged quantity cannot exceed total collected.', confirmButtonColor: '#059669' });
        return;
    }

    const btn = document.getElementById('createProduceSubmit');
    btn.querySelector('.submit-text').classList.add('d-none');
    btn.querySelector('.spinner-border').classList.remove('d-none');
    btn.disabled = true;

    const formData = Object.fromEntries(new FormData(e.target).entries());

    fetch('{{ route("produces.store-ajax") }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify(formData)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success', title: 'Saved!',
                text: data.message || 'Produce record created successfully.',
                timer: 2000, showConfirmButton: false, timerProgressBar: true
            }).then(() => reloadAfterModal('createProduceModal'));
        } else {
            Swal.fire({ icon: 'error', title: 'Error!', text: data.message, confirmButtonColor: '#d33' });
            btn.querySelector('.submit-text').classList.remove('d-none');
            btn.querySelector('.spinner-border').classList.add('d-none');
            btn.disabled = false;
        }
    })
    .catch(() => {
        Swal.fire({ icon: 'error', title: 'Error!', text: 'Something went wrong. Please try again.', confirmButtonColor: '#d33' });
        btn.querySelector('.submit-text').classList.remove('d-none');
        btn.querySelector('.spinner-border').classList.add('d-none');
        btn.disabled = false;
    });
});

// ─── VIEW ─────────────────────────────────────────────────────────────────────
function viewProduce(id) {
    const body = document.getElementById('viewProduceBody');
    body.innerHTML = `<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Loading details...</p></div>`;
    new bootstrap.Modal(document.getElementById('viewProduceModal')).show();

    fetch(`/produces/${id}/details-json`)
        .then(r => r.json())
        .then(data => {
            if (!data.success) { body.innerHTML = `<div class="alert alert-danger">${data.message}</div>`; return; }
            const p         = data.produce;
            const hasDamage = parseFloat(p.quantity_damaged) > 0;
            body.innerHTML = `
            <div class="detail-view">
                <div class="mb-3 pb-2 border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="badge-product-modern badge-${p.product_type.toLowerCase()}">${p.product_type_label}</span>
                        <small class="text-muted">${p.created_at}</small>
                    </div>
                </div>
                <div class="row mb-3"><div class="col-4 text-muted">Date</div><div class="col-8 fw-semibold">${p.produce_date}</div></div>
                <div class="row mb-3">
                    <div class="col-4 text-muted">Collected</div>
                    <div class="col-8"><span class="fw-bold fs-5">${p.quantity}</span> <span class="text-muted ms-1">${p.unit}</span></div>
                </div>
                ${hasDamage ? `<div class="row mb-3">
                    <div class="col-4 text-muted">Damaged</div>
                    <div class="col-8"><span class="fw-bold fs-5 text-danger">${p.quantity_damaged}</span> <span class="text-muted ms-1">${p.unit}</span></div>
                </div>` : ''}
                <div class="row mb-3">
                    <div class="col-4 text-muted">Net Available</div>
                    <div class="col-8">
                        <span class="fw-bold fs-5 text-success">${p.net_quantity}</span>
                        <span class="text-muted ms-1">${p.unit}</span>
                        ${hasDamage ? `<small class="text-muted ms-2">(${p.quantity} − ${p.quantity_damaged})</small>` : ''}
                    </div>
                </div>
                <div class="row mb-3"><div class="col-4 text-muted">Flock</div><div class="col-8">${p.flock_number}${p.flock_breed ? ' – ' + p.flock_breed : ''}</div></div>
                <div class="row mb-3"><div class="col-4 text-muted">Notes</div><div class="col-8 text-muted">${p.notes}</div></div>
                <div class="row mb-3"><div class="col-4 text-muted">Recorded by</div><div class="col-8"><i class="fas fa-user-circle me-1"></i> ${p.recorded_by}</div></div>
            </div>`;
        })
        .catch(() => { body.innerHTML = `<div class="alert alert-danger">Failed to load details.</div>`; });
}

// ─── EDIT ─────────────────────────────────────────────────────────────────────
function editProduce(id) {
    const body = document.getElementById('editProduceBody');
    body.innerHTML = `<div class="text-center py-4"><div class="spinner-border text-warning" role="status"></div><p class="mt-2">Loading form...</p></div>`;
    new bootstrap.Modal(document.getElementById('editProduceModal')).show();

    fetch(`/produces/${id}/edit-data`)
        .then(r => r.json())
        .then(data => {
            if (!data.success) { body.innerHTML = `<div class="alert alert-danger">${data.message}</div>`; return; }
            const p = data.produce;

            const flockOptions = data.flocks.map(f =>
                `<option value="${f.id}" ${f.id == p.flock_id ? 'selected' : ''}>${f.flock_number} – ${f.breed_variety}</option>`
            ).join('');
            const typeOptions = data.existingTypes.map(t =>
                `<option value="${t}" ${t === p.product_type ? 'selected' : ''}>${ucfirst(t)}</option>`
            ).join('');
            const units       = ['pieces', 'birds', 'kg', 'bags', 'litres', 'trays', 'crates', 'units'];
            const unitOptions = units.map(u => `<option value="${u}" ${u === p.unit ? 'selected' : ''}>${u}</option>`).join('');

            body.innerHTML = `
            <form id="editProduceForm" data-id="${p.id}">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold">Product Type <span class="text-danger">*</span></label>
                        <select class="form-control" name="product_type" required
                            style="border-radius:12px;padding:.625rem 1rem;"
                            onchange="fetchDefaultUnit(this,'ep_unit')">${typeOptions}</select>
                    </div>
                    <div class="col-8">
                        <label class="form-label fw-semibold">Total Collected <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="quantity" id="ep_qty"
                            step="0.01" min="0.01" value="${p.quantity}" required
                            style="border-radius:12px;padding:.625rem 1rem;"
                            oninput="editValidateDamaged()">
                    </div>
                    <div class="col-4">
                        <label class="form-label fw-semibold">Unit</label>
                        <select class="form-control" name="unit" id="ep_unit"
                            style="border-radius:12px;padding:.625rem 1rem;">${unitOptions}</select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Damaged / Unusable
                            <span class="text-muted small fw-normal">(e.g. broken, spoiled)</span>
                        </label>
                        <input type="number" class="form-control" name="quantity_damaged" id="ep_damaged"
                            step="0.01" min="0" value="${p.quantity_damaged ?? 0}"
                            style="border-radius:12px;padding:.625rem 1rem;"
                            oninput="editValidateDamaged()">
                        <small id="ep_net_display" class="mt-1 d-block"></small>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="produce_date"
                            value="${p.produce_date}" required
                            style="border-radius:12px;padding:.625rem 1rem;">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Flock</label>
                        <select class="form-control" name="flock_id"
                            style="border-radius:12px;padding:.625rem 1rem;">
                            <option value="">— No specific flock —</option>
                            ${flockOptions}
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Notes</label>
                        <textarea class="form-control" name="notes" rows="3"
                            style="border-radius:12px;padding:.625rem 1rem;">${p.notes || ''}</textarea>
                    </div>
                </div>
                <div class="d-flex gap-2 mt-4">
                    <button type="button" class="btn btn-secondary flex-grow-1"
                        data-bs-dismiss="modal" style="border-radius:12px;">Cancel</button>
                    <button type="submit" class="btn btn-warning flex-grow-1"
                        id="editProduceSubmit" style="border-radius:12px;">
                        <span class="submit-text"><i class="fas fa-save me-1"></i>Save Changes</span>
                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                    </button>
                </div>
            </form>`;

            editValidateDamaged();
        })
        .catch(() => { body.innerHTML = `<div class="alert alert-danger">Failed to load form.</div>`; });
}

// ─── EDIT submit ──────────────────────────────────────────────────────────────
document.addEventListener('submit', function (e) {
    if (!e.target.matches('#editProduceForm')) return;
    e.preventDefault();

    const damaged = parseFloat(document.getElementById('ep_damaged')?.value) || 0;
    const qty     = parseFloat(document.getElementById('ep_qty')?.value) || 0;
    if (damaged > qty) {
        Swal.fire({ icon: 'warning', title: 'Invalid Entry', text: 'Damaged quantity cannot exceed total collected.', confirmButtonColor: '#f59e0b' });
        return;
    }

    const id  = e.target.dataset.id;
    const btn = document.getElementById('editProduceSubmit');
    btn.querySelector('.submit-text').classList.add('d-none');
    btn.querySelector('.spinner-border').classList.remove('d-none');
    btn.disabled = true;

    const formData = Object.fromEntries(new FormData(e.target).entries());

    fetch(`/produces/${id}/update-ajax`, {
        method: 'PUT',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify(formData)
    })
    .then(r => {
        if (!r.ok) return r.json().then(d => Promise.reject(d));
        return r.json();
    })
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success', title: 'Updated!',
                text: data.message || 'Produce record updated successfully.',
                timer: 2000, showConfirmButton: false, timerProgressBar: true
            }).then(() => reloadAfterModal('editProduceModal'));
        } else {
            Swal.fire({ icon: 'error', title: 'Error!', text: data.message, confirmButtonColor: '#d33' });
            btn.querySelector('.submit-text').classList.remove('d-none');
            btn.querySelector('.spinner-border').classList.add('d-none');
            btn.disabled = false;
        }
    })
    .catch(err => {
        const msg = err?.message || 'Something went wrong. Please try again.';
        Swal.fire({ icon: 'error', title: 'Error!', text: msg, confirmButtonColor: '#d33' });
        btn.querySelector('.submit-text').classList.remove('d-none');
        btn.querySelector('.spinner-border').classList.add('d-none');
        btn.disabled = false;
    });
});

// ─── DELETE ───────────────────────────────────────────────────────────────────
function deleteProduce(id) {
    Swal.fire({
        title: 'Delete this record?',
        text: 'This cannot be undone. Linked daily log data will also be cleared.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then(result => {
        if (!result.isConfirmed) return;

        Swal.fire({
            title: 'Deleting…', text: 'Please wait',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        fetch(`/produces/${id}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': CSRF,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-HTTP-Method-Override': 'DELETE'
            },
            body: JSON.stringify({ _method: 'DELETE' }),
            credentials: 'same-origin'
        })
        .then(r => {
            if (!r.ok) return r.json().then(d => Promise.reject(d));
            return r.json();
        })
        .then(data => {
            if (data.success) {
                const rows = document.querySelectorAll('table tbody tr');
                rows.forEach(row => {
                    if (row.querySelector(`[onclick="deleteProduce(${id})"]`)) {
                        row.style.transition = 'opacity 0.3s';
                        row.style.opacity = '0';
                        setTimeout(() => row.remove(), 300);
                    }
                });

                Swal.fire({
                    icon: 'success', title: 'Deleted!',
                    text: data.message || 'Record deleted successfully.',
                    timer: 1500, showConfirmButton: false, timerProgressBar: true
                }).then(() => window.location.reload());
            } else {
                Swal.fire({ icon: 'error', title: 'Not Allowed', text: data.message || 'Failed to delete.', confirmButtonColor: '#d33' });
            }
        })
        .catch(err => {
            const msg = err?.message || 'Network error. Please try again.';
            Swal.fire({ icon: 'error', title: 'Error!', text: msg, confirmButtonColor: '#d33' });
        });
    });
}

// ─── STAT CARD DETAIL ─────────────────────────────────────────────────────────

// Tracks active tab state per modal open
let _statTabState = 'month';

function setStatTab(tab) {
    _statTabState = tab;
    document.querySelectorAll('.stat-tab-btn').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.tab === tab);
    });
    document.querySelectorAll('.stat-tab-pane').forEach(pane => {
        pane.style.display = pane.dataset.pane === tab ? '' : 'none';
    });
}

// ── Build the species breakdown cards ────────────────────────────────────────
function buildSpeciesBreakdown(speciesBreakdown, totalProducedAllTime) {
    if (!speciesBreakdown || speciesBreakdown.length === 0) return '';

    const cards = speciesBreakdown.map(s => {
        const sharePct = totalProducedAllTime > 0
            ? Math.round((parseFloat(s.all_time.produced.replace(/,/g, '')) / totalProducedAllTime) * 100)
            : 0;

        return `
        <div class="species-card mb-3">
            <div class="species-card-header">
                <div class="d-flex align-items-center gap-2">
                    <span class="species-badge">${s.species_code}</span>
                    <span class="fw-semibold">${s.species_name}</span>
                    <span class="text-muted small">${s.flock_count} flock${s.flock_count !== 1 ? 's' : ''} · ${s.record_count} record${s.record_count !== 1 ? 's' : ''}</span>
                </div>
                <div class="text-end">
                    <small class="text-muted">${sharePct}% of total</small>
                    <div class="species-share-bar" style="width:80px;">
                        <div class="species-share-bar-fill" style="width:${sharePct}%;"></div>
                    </div>
                </div>
            </div>
            <div class="p-3">
                {{-- This Month row --}}
                <div class="mb-2">
                    <div class="species-section-title">
                        <i class="fas fa-calendar-day" style="color:#059669;"></i>
                        This Month
                        ${s.this_month.damage_pct > 0 ? `<span class="badge" style="background:#fee2e2;color:#991b1b;font-size:.65rem;">${s.this_month.damage_pct}% dmg</span>` : ''}
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <div class="species-stat-pill" style="background:#f0fdf4;border:1px solid #bbf7d0;">
                            <span class="val text-success">${s.this_month.produced}</span>
                            <span class="lbl">Collected</span>
                        </div>
                        <div class="species-stat-pill" style="background:#fee2e2;border:1px solid #fecaca;">
                            <span class="val text-danger">${s.this_month.damaged}</span>
                            <span class="lbl">Damaged</span>
                        </div>
                        <div class="species-stat-pill" style="background:#eff6ff;border:1px solid #bfdbfe;">
                            <span class="val text-primary">${s.this_month.available}</span>
                            <span class="lbl">Net Avail.</span>
                        </div>
                    </div>
                </div>
                {{-- All Time row --}}
                <div>
                    <div class="species-section-title">
                        <i class="fas fa-history" style="color:#94a3b8;"></i>
                        All Time
                        ${s.all_time.damage_pct > 0 ? `<span class="badge" style="background:#fef3c7;color:#92400e;font-size:.65rem;">${s.all_time.damage_pct}% dmg</span>` : ''}
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <div class="species-stat-pill" style="background:#f8fafc;border:1px solid #e2e8f0;">
                            <span class="val" style="color:#059669;">${s.all_time.produced}</span>
                            <span class="lbl">Produced</span>
                        </div>
                        <div class="species-stat-pill" style="background:#f8fafc;border:1px solid #e2e8f0;">
                            <span class="val text-danger">${s.all_time.damaged}</span>
                            <span class="lbl">Damaged</span>
                        </div>
                        <div class="species-stat-pill" style="background:#f8fafc;border:1px solid #e2e8f0;">
                            <span class="val text-primary">${s.all_time.available}</span>
                            <span class="lbl">Net Avail.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>`;
    }).join('');

    return `
    <div class="species-breakdown-section">
        <div class="species-section-title mb-3">
            <i class="fas fa-layer-group" style="color:#7c3aed;"></i>
            Breakdown by Species
            <span class="badge" style="background:#ede9fe;color:#4c1d95;font-size:.7rem;">${speciesBreakdown.length} species</span>
        </div>
        ${cards}
    </div>`;
}

// ── Build flock top-contributors table ────────────────────────────────────────
function buildFlockBreakdown(flockBreakdown, totalProducedRaw) {
    if (!flockBreakdown || flockBreakdown.length === 0) return '';

    const rows = flockBreakdown.map(f => {
        const share = totalProducedRaw > 0
            ? Math.round((f.raw_produced / totalProducedRaw) * 100)
            : 0;
        return `
        <tr>
            <td class="fw-semibold ps-3">
                <i class="fas fa-layer-group me-1 text-muted" style="font-size:.75rem;"></i>
                ${f.flock_number}
            </td>
            <td>
                <span class="species-badge" style="font-size:.65rem;">${f.species_code}</span>
                <span class="ms-1 small text-muted">${f.breed_variety}</span>
            </td>
            <td class="fw-bold text-success">${f.produced}</td>
            <td class="text-danger">${f.damaged}</td>
            <td class="text-primary fw-semibold">${f.available}</td>
            <td>
                <div class="d-flex align-items-center gap-2">
                    <div style="flex:1;height:5px;background:#e2e8f0;border-radius:99px;min-width:50px;">
                        <div style="width:${share}%;height:100%;background:linear-gradient(90deg,#059669,#10b981);border-radius:99px;"></div>
                    </div>
                    <span class="small text-muted">${share}%</span>
                </div>
            </td>
            <td class="text-end pe-3">
                ${f.damage_pct > 0
                    ? `<span class="badge" style="background:#fee2e2;color:#991b1b;font-size:.65rem;">${f.damage_pct}%</span>`
                    : `<span class="text-muted small">—</span>`
                }
            </td>
        </tr>`;
    }).join('');

    return `
    <div class="mb-4">
        <div class="species-section-title mb-2">
            <i class="fas fa-trophy" style="color:#d97706;"></i>
            Top Contributing Flocks
            <span class="badge" style="background:#fef3c7;color:#92400e;font-size:.7rem;">all time</span>
        </div>
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0" style="font-size:.82rem;">
                <thead>
                    <tr style="background:#f8fafc;">
                        <th class="ps-3 fw-semibold text-muted" style="font-size:.7rem;">Flock</th>
                        <th class="fw-semibold text-muted" style="font-size:.7rem;">Species / Breed</th>
                        <th class="fw-semibold text-muted" style="font-size:.7rem;">Produced</th>
                        <th class="fw-semibold text-muted" style="font-size:.7rem;">Damaged</th>
                        <th class="fw-semibold text-muted" style="font-size:.7rem;">Net Avail.</th>
                        <th class="fw-semibold text-muted" style="font-size:.7rem;">Share</th>
                        <th class="text-end pe-3 fw-semibold text-muted" style="font-size:.7rem;">Dmg %</th>
                    </tr>
                </thead>
                <tbody>${rows}</tbody>
            </table>
        </div>
    </div>`;
}

// ── Main openStatDetail function ──────────────────────────────────────────────
function openStatDetail(productType) {
    const modal  = new bootstrap.Modal(document.getElementById('statDetailModal'));
    const header = document.getElementById('statDetailHeader');
    const title  = document.getElementById('statDetailTitle');
    const body   = document.getElementById('statDetailBody');

    _statTabState = 'month';
    body.innerHTML = `<div class="text-center py-4"><div class="spinner-border text-success" role="status"></div><p class="mt-2">Loading...</p></div>`;
    modal.show();

    fetch(`/produces/stat/${productType}`)
        .then(r => r.json())
        .then(data => {
            if (!data.success) { body.innerHTML = `<div class="alert alert-danger">${data.message}</div>`; return; }

            const m   = data.this_month;
            const a   = data.all_time;
            const mon = new Date().toLocaleString('default', { month: 'long', year: 'numeric' });

            header.style.background = 'linear-gradient(135deg, #059669, #10b981)';
            title.innerHTML = `${data.icon} ${data.label} — Detailed Breakdown`;

            // Parse raw all-time produced for share% calculations
            const totalProducedRaw = parseFloat(a.produced.replace(/,/g, '')) || 0;

            // ── Species breakdown (only when >1 species) ──────────────
            const speciesHtml = data.has_species_breakdown
                ? buildSpeciesBreakdown(data.species_breakdown, totalProducedRaw)
                : '';

            // ── Flock breakdown ───────────────────────────────────────
            const flockHtml = buildFlockBreakdown(data.flock_breakdown, totalProducedRaw);

            // ── Recent records table ──────────────────────────────────
            const recentHtml = data.recent_records.length > 0 ? `
            <div class="mb-2">
                <div class="species-section-title mb-2">
                    <i class="fas fa-clock" style="color:#64748b;"></i>
                    5 Most Recent Records
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle" style="font-size:.82rem;">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Flock</th>
                                <th>Species</th>
                                <th class="text-end">Collected</th>
                                <th class="text-end text-danger">Damaged</th>
                                <th class="text-end text-success">Net</th>
                                <th>Unit</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${data.recent_records.map(r => `
                            <tr>
                                <td class="fw-semibold">${r.date}</td>
                                <td>${r.flock}</td>
                                <td>
                                    ${r.species_code !== '—'
                                        ? `<span class="species-badge" style="font-size:.6rem;">${r.species_code}</span>`
                                        : `<span class="text-muted">—</span>`
                                    }
                                </td>
                                <td class="text-end">${r.quantity}</td>
                                <td class="text-end text-danger">${r.damaged}</td>
                                <td class="text-end text-success fw-bold">${r.net}</td>
                                <td class="text-muted small">${r.unit}</td>
                            </tr>`).join('')}
                        </tbody>
                    </table>
                </div>
            </div>` : '';

            body.innerHTML = `
            {{-- ── Tab Toggle ──────────────────────────────────────── --}}
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div class="tab-toggle">
                    <button class="stat-tab-btn active" data-tab="month" onclick="setStatTab('month')">
                        <i class="fas fa-calendar-alt me-1"></i>This Month
                    </button>
                    <button class="stat-tab-btn" data-tab="alltime" onclick="setStatTab('alltime')">
                        <i class="fas fa-history me-1"></i>All Time
                    </button>
                </div>
                <small class="text-muted">${mon}</small>
            </div>

            {{-- ── This Month Pane ──────────────────────────────────── --}}
            <div class="stat-tab-pane" data-pane="month">
                <div class="row g-3 mb-4">
                    <div class="col-6 col-md-4">
                        <div class="p-3 rounded-3 text-center" style="background:#f0fdf4;border:1px solid #bbf7d0;">
                            <div class="fw-bold fs-4 text-success">${m.produced}</div>
                            <div class="small text-muted">Total Collected</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="p-3 rounded-3 text-center" style="background:#fee2e2;border:1px solid #fecaca;">
                            <div class="fw-bold fs-4 text-danger">${m.damaged}</div>
                            <div class="small text-muted">Damaged</div>
                            ${m.damage_pct > 0 ? `<div class="small text-danger">${m.damage_pct}% damage rate</div>` : ''}
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="p-3 rounded-3 text-center" style="background:#eff6ff;border:1px solid #bfdbfe;">
                            <div class="fw-bold fs-4 text-primary">${m.available}</div>
                            <div class="small text-muted">Net Available</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="p-3 rounded-3 text-center" style="background:#fef3c7;border:1px solid #fde68a;">
                            <div class="fw-bold fs-4" style="color:#d97706;">${m.sold}</div>
                            <div class="small text-muted">Sold This Month</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="p-3 rounded-3 text-center" style="background:#f0fdf4;border:1px solid #bbf7d0;">
                            <div class="fw-bold fs-4 text-success">₵${m.revenue}</div>
                            <div class="small text-muted">Revenue This Month</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="p-3 rounded-3 text-center" style="background:#fdf4ff;border:1px solid #e9d5ff;">
                            <div class="fw-bold fs-4" style="color:#7c3aed;">${m.remaining}</div>
                            <div class="small text-muted">Ready for Sale</div>
                        </div>
                    </div>
                </div>
                ${speciesHtml}
                ${recentHtml}
            </div>

            {{-- ── All Time Pane ────────────────────────────────────── --}}
            <div class="stat-tab-pane" data-pane="alltime" style="display:none;">
                <div class="row g-3 mb-4">
                    <div class="col-6 col-md-3">
                        <div class="p-3 rounded-3 text-center bg-light">
                            <div class="fw-bold fs-4 text-success">${a.produced}</div>
                            <div class="small text-muted">Ever Produced</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-3 rounded-3 text-center bg-light">
                            <div class="fw-bold fs-4 text-danger">${a.damaged}</div>
                            <div class="small text-muted">Total Damaged</div>
                            ${a.damage_pct > 0 ? `<div class="small text-danger">${a.damage_pct}%</div>` : ''}
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-3 rounded-3 text-center bg-light">
                            <div class="fw-bold fs-4 text-warning">${a.sold}</div>
                            <div class="small text-muted">Total Sold</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-3 rounded-3 text-center bg-light">
                            <div class="fw-bold fs-4 text-success">₵${a.revenue}</div>
                            <div class="small text-muted">Total Revenue</div>
                        </div>
                    </div>
                </div>
                ${speciesHtml}
                ${flockHtml}
            </div>`;
        })
        .catch(err => { body.innerHTML = `<div class="alert alert-danger">Error: ${err.message}</div>`; });
}
</script>
@endpush