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

    /* Modern Stat Cards */
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

    .stat-card-modern:hover {
        transform: translateY(-4px);
        box-shadow: var(--hover-shadow);
    }

    .stat-card-modern::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: var(--primary-gradient);
    }

    .stat-icon-modern {
        width: 52px;
        height: 52px;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
        color: #059669;
    }

    .stat-value-modern {
        font-size: 2rem;
        font-weight: 800;
        color: #1f2937;
        line-height: 1.2;
        margin-bottom: 0.25rem;
    }

    /* Modern Filter Bar */
    .filter-bar-modern {
        background: white;
        border-radius: 20px;
        padding: 1.25rem 1.5rem;
        margin-bottom: 2rem;
        box-shadow: var(--card-shadow);
        border: 1px solid rgba(0, 0, 0, 0.05);
    }

    /* Enhanced Table */
    .table-modern {
        border-radius: 20px;
        overflow: hidden;
        box-shadow: var(--card-shadow);
        background: white;
    }

    .table-modern thead th {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #475569;
        padding: 1rem 1rem;
        border-bottom: 2px solid #e2e8f0;
    }

    .table-modern tbody tr {
        transition: var(--transition-smooth);
        border-bottom: 1px solid #f1f5f9;
    }

    .table-modern tbody tr:hover {
        background: linear-gradient(90deg, #fefce8 0%, #fef9c3 100%);
        transform: scale(1.01);
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    /* Enhanced Badges */
    .badge-product-modern {
        font-size: 0.7rem;
        padding: 0.35rem 0.85rem;
        border-radius: 30px;
        font-weight: 600;
        letter-spacing: 0.02em;
        transition: var(--transition-smooth);
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .badge-product-modern i {
        font-size: 0.7rem;
    }

    .badge-product-modern:hover {
        transform: translateY(-1px);
        filter: brightness(0.98);
    }

    .badge-eggs { background: linear-gradient(135deg, #fef3c7, #fde68a); color: #92400e; }
    .badge-live_bird { background: linear-gradient(135deg, #d1fae5, #a7f3d0); color: #065f46; }
    .badge-meat { background: linear-gradient(135deg, #fee2e2, #fecaca); color: #991b1b; }
    .badge-breeding_stock { background: linear-gradient(135deg, #ede9fe, #ddd6fe); color: #4c1d95; }
    .badge-manure { background: linear-gradient(135deg, #fef9c3, #fef08a); color: #713f12; }

    /* Action Buttons */
    .action-btn {
        width: 34px;
        height: 34px;
        padding: 0;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: var(--transition-smooth);
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        color: #64748b;
        cursor: pointer;
    }

    .action-btn:hover {
        transform: translateY(-2px);
    }

    .action-btn.view:hover {
        background: #0ea5e9;
        border-color: #0ea5e9;
        color: white;
    }

    .action-btn.edit:hover {
        background: #f59e0b;
        border-color: #f59e0b;
        color: white;
    }

    .action-btn.delete:hover {
        background: #ef4444;
        border-color: #ef4444;
        color: white;
    }

    /* Quick Stats Row */
    .quick-stat {
        background: linear-gradient(135deg, #059669, #10b981);
        border-radius: 16px;
        padding: 0.75rem 1.5rem;
        color: white;
        margin-bottom: 1.5rem;
    }

    /* Modal Enhancements */
    .modal-content-modern {
        border-radius: 28px;
        border: none;
        overflow: hidden;
    }

    .modal-header-modern {
        background: linear-gradient(135deg, #059669, #10b981);
        color: white;
        padding: 1.25rem 1.5rem;
        border: none;
    }

    .form-control-modern, .form-select-modern {
        border-radius: 12px;
        border: 1.5px solid #e2e8f0;
        padding: 0.625rem 1rem;
        transition: var(--transition-smooth);
    }

    .form-control-modern:focus, .form-select-modern:focus {
        border-color: #059669;
        box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.1);
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        background: linear-gradient(135deg, #f8fafc, #f1f5f9);
        border-radius: 20px;
    }

    .empty-state i {
        font-size: 4rem;
        color: #cbd5e1;
        margin-bottom: 1rem;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .stat-value-modern {
            font-size: 1.5rem;
        }
        .stat-icon-modern {
            width: 44px;
            height: 44px;
            font-size: 20px;
        }
        .quick-stat {
            flex-direction: column;
            text-align: center;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-3">

    {{-- Modern Page Header --}}
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

    {{-- Enhanced Summary Stats --}}
    @php
        $icons = [
            'eggs' => 'fa-egg',
            'live_bird' => 'fa-dove',
            'meat' => 'fa-drumstick-bite',
            'manure' => 'fa-seedling'
        ];
    @endphp
    <div class="row g-4 mb-5">
        @php
            $productTypes = \App\Models\FarmProduce::productTypeLabels();
        @endphp
        @foreach($productTypes as $key => $label)
        <div class="col-md-6 col-xl-3">
            <div class="stat-card-modern">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="stat-icon-modern">
                        <i class="fas {{ $icons[$key] ?? 'fa-box' }}"></i>
                    </div>
                    <div class="text-end">
                        <small class="text-muted text-uppercase fw-semibold">This Month</small>
                        <div class="stat-value-modern">
                            {{ number_format(\App\Models\FarmProduce::where('product_type',$key)->whereMonth('produce_date', now()->month)->sum('quantity'), 0) }}
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between">
                    <span class="fw-semibold">{{ $label }}</span>
                    <span class="badge-product-modern badge-{{ $key }}">
                        <i class="fas {{ $icons[$key] ?? 'fa-box' }}"></i>
                        {{ ucfirst(str_replace('_', ' ', $key)) }}
                    </span>
                </div>
                <div class="mt-3">
                    <div class="progress" style="height: 6px; border-radius: 10px; background: #e2e8f0;">
                        @php
                            $total = \App\Models\FarmProduce::where('product_type',$key)->whereMonth('produce_date', now()->month)->sum('quantity');
                            $percentage = min(100, ($total / 1000) * 100);
                        @endphp
                        <div class="progress-bar" style="width: {{ $percentage }}%; background: linear-gradient(90deg, #059669, #10b981); border-radius: 10px;"></div>
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

    {{-- Modern Filter Section --}}
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
                    @foreach($productTypes as $key => $label)
                        <option value="{{ $key }}" {{ $productType === $key ? 'selected' : '' }}>{{ $label }}</option>
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

    {{-- Enhanced Data Table --}}
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
                            <span class="fw-bold fs-5">{{ number_format($produce->quantity, 0) }}</span>
                            <span class="text-muted small ms-1">{{ $produce->unit }}</span>
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
                                <div class="bg-light rounded-circle p-1" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
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

{{-- ===================== CREATE MODAL ===================== --}}
<div class="modal fade" id="createProduceModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 28px; border: none; overflow: hidden;">
            <div class="modal-header" style="background: linear-gradient(135deg, #059669, #10b981); color: white; padding: 1.25rem 1.5rem; border: none;">
                <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Record New Produce</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="createProduceBody" style="padding: 1.5rem;">
                <div class="text-center py-4"><div class="spinner-border text-success" role="status"></div><p class="mt-2">Loading form...</p></div>
            </div>
        </div>
    </div>
</div>

{{-- ===================== VIEW MODAL ===================== --}}
<div class="modal fade" id="viewProduceModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 28px; border: none; overflow: hidden;">
            <div class="modal-header" style="background: linear-gradient(135deg, #0ea5e9, #3b82f6); color: white; padding: 1.25rem 1.5rem; border: none;">
                <h5 class="modal-title"><i class="fas fa-eye me-2"></i>Produce Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewProduceBody" style="padding: 1.5rem;">
                <div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Loading details...</p></div>
            </div>
        </div>
    </div>
</div>

{{-- ===================== EDIT MODAL ===================== --}}
<div class="modal fade" id="editProduceModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 28px; border: none; overflow: hidden;">
            <div class="modal-header" style="background: linear-gradient(135deg, #f59e0b, #d97706); color: white; padding: 1.25rem 1.5rem; border: none;">
                <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Produce Record</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="editProduceBody" style="padding: 1.5rem;">
                <div class="text-center py-4"><div class="spinner-border text-warning" role="status"></div><p class="mt-2">Loading form...</p></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Initialize tooltips
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl)
})

// ── Unit auto-fill ────────────────────────────────────────────
function fetchDefaultUnit(selectEl, unitInputId) {
    const type = selectEl.value;
    if (!type) return;
    fetch(`{{ url('/produces/unit') }}/${type}`)
        .then(r => r.json())
        .then(d => { 
            const unitSelect = document.getElementById(unitInputId);
            if (unitSelect && d.unit) {
                unitSelect.value = d.unit;
            }
        });
}

// ── Create ────────────────────────────────────────────────────
document.getElementById('newProduceBtn')?.addEventListener('click', function () {
    const body = document.getElementById('createProduceBody');
    body.innerHTML = `<div class="text-center py-4"><div class="spinner-border text-success" role="status"></div><p class="mt-2">Loading form...</p></div>`;
    const modal = new bootstrap.Modal(document.getElementById('createProduceModal'));
    modal.show();

    fetch('{{ route("produces.create-form") }}')
        .then(r => r.json())
        .then(data => {
            if (!data.success) { body.innerHTML = `<div class="alert alert-danger">${data.message}</div>`; return; }
            body.innerHTML = buildCreateForm(data);
        });
});

document.getElementById('newProduceBtnEmpty')?.addEventListener('click', function () {
    document.getElementById('newProduceBtn').click();
});

function buildCreateForm(data) {
    const flockOptions = data.flocks.map(f =>
        `<option value="${f.id}">${f.flock_number} – ${f.breed_variety}</option>`
    ).join('');
    const typeOptions = Object.entries(data.productTypes).map(([k,v]) =>
        `<option value="${k}">${v}</option>`
    ).join('');
    const unitOptions = data.units.map(u => `<option value="${u}">${u}</option>`).join('');

    return `
    <form id="createProduceForm">
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label fw-semibold">Product Type <span class="text-danger">*</span></label>
                <select class="form-control" id="cp_product_type" name="product_type" required
                    onchange="fetchDefaultUnit(this,'cp_unit')" style="border-radius: 12px; padding: 0.625rem 1rem;">
                    <option value="">Select product...</option>
                    ${typeOptions}
                </select>
            </div>
            <div class="col-8">
                <label class="form-label fw-semibold">Quantity <span class="text-danger">*</span></label>
                <input type="number" class="form-control" name="quantity" step="0.01" min="0.01" required placeholder="e.g. 120" style="border-radius: 12px; padding: 0.625rem 1rem;">
            </div>
            <div class="col-4">
                <label class="form-label fw-semibold">Unit <span class="text-danger">*</span></label>
                <select class="form-control" name="unit" id="cp_unit" required style="border-radius: 12px; padding: 0.625rem 1rem;">
                    ${unitOptions}
                </select>
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                <input type="date" class="form-control" name="produce_date" required value="${new Date().toISOString().split('T')[0]}" max="${new Date().toISOString().split('T')[0]}" style="border-radius: 12px; padding: 0.625rem 1rem;">
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Flock <span class="text-muted small">(optional)</span></label>
                <select class="form-control" name="flock_id" style="border-radius: 12px; padding: 0.625rem 1rem;">
                    <option value="">— No specific flock —</option>
                    ${flockOptions}
                </select>
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Notes <span class="text-muted small">(optional)</span></label>
                <textarea class="form-control" name="notes" rows="3" placeholder="e.g. Morning collection, House A" style="border-radius: 12px; padding: 0.625rem 1rem;"></textarea>
            </div>
        </div>
        <div class="d-flex gap-2 mt-4">
            <button type="button" class="btn btn-secondary flex-grow-1" data-bs-dismiss="modal" style="border-radius: 12px; padding: 0.625rem;">Cancel</button>
            <button type="submit" class="btn btn-success flex-grow-1" id="createProduceSubmit" style="border-radius: 12px; padding: 0.625rem; background: linear-gradient(135deg, #059669, #10b981); border: none;">
                <span class="submit-text">Save Record</span>
                <span class="spinner-border spinner-border-sm d-none" role="status"></span>
            </button>
        </div>
    </form>`;
}

document.addEventListener('submit', function (e) {
    if (e.target.id === 'createProduceForm') {
        e.preventDefault();
        const btn = document.getElementById('createProduceSubmit');
        btn.querySelector('.submit-text').classList.add('d-none');
        btn.querySelector('.spinner-border').classList.remove('d-none');
        btn.disabled = true;

        const fd = new FormData(e.target);
        fetch('{{ route("produces.store-ajax") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
            body: JSON.stringify(Object.fromEntries(fd))
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('createProduceModal')).hide();
                location.reload();
            } else {
                alert(data.message);
                btn.querySelector('.submit-text').classList.remove('d-none');
                btn.querySelector('.spinner-border').classList.add('d-none');
                btn.disabled = false;
            }
        });
    }
});

// ── View ──────────────────────────────────────────────────────
function viewProduce(id) {
    const body = document.getElementById('viewProduceBody');
    body.innerHTML = `<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Loading details...</p></div>`;
    new bootstrap.Modal(document.getElementById('viewProduceModal')).show();

    fetch(`/produces/${id}/details-json`)
        .then(r => r.json())
        .then(data => {
            if (!data.success) { body.innerHTML = `<div class="alert alert-danger">${data.message}</div>`; return; }
            const p = data.produce;
            body.innerHTML = `
            <div class="detail-view">
                <div class="mb-3 pb-2 border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="badge-product-modern badge-${p.product_type.toLowerCase()}">${p.product_type}</span>
                        <small class="text-muted">${p.created_at}</small>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-4 text-muted">Date</div>
                    <div class="col-8 fw-semibold">${p.produce_date}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-4 text-muted">Quantity</div>
                    <div class="col-8"><span class="fw-bold fs-4">${p.quantity}</span> <span class="text-muted">${p.unit}</span></div>
                </div>
                <div class="row mb-3">
                    <div class="col-4 text-muted">Flock</div>
                    <div class="col-8">${p.flock_number}${p.flock_breed ? ' – '+p.flock_breed : ''}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-4 text-muted">Notes</div>
                    <div class="col-8">${p.notes}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-4 text-muted">Recorded by</div>
                    <div class="col-8"><i class="fas fa-user-circle me-1"></i> ${p.recorded_by}</div>
                </div>
            </div>`;
        });
}

// ── Edit ──────────────────────────────────────────────────────
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
            const typeOptions = Object.entries(data.productTypes).map(([k,v]) =>
                `<option value="${k}" ${k === p.product_type ? 'selected' : ''}>${v}</option>`
            ).join('');
            const units = ['pieces','birds','kg','bags','litres','trays'];
            const unitOptions = units.map(u => `<option value="${u}" ${u === p.unit ? 'selected' : ''}>${u}</option>`).join('');

            body.innerHTML = `
            <form id="editProduceForm" data-id="${p.id}">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold">Product Type</label>
                        <select class="form-control" name="product_type" required style="border-radius: 12px; padding: 0.625rem 1rem;"
                            onchange="fetchDefaultUnit(this,'ep_unit')">${typeOptions}</select>
                    </div>
                    <div class="col-8">
                        <label class="form-label fw-semibold">Quantity</label>
                        <input type="number" class="form-control" name="quantity" step="0.01" min="0.01" value="${p.quantity}" required style="border-radius: 12px; padding: 0.625rem 1rem;">
                    </div>
                    <div class="col-4">
                        <label class="form-label fw-semibold">Unit</label>
                        <select class="form-control" name="unit" id="ep_unit" style="border-radius: 12px; padding: 0.625rem 1rem;">${unitOptions}</select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Date</label>
                        <input type="date" class="form-control" name="produce_date" value="${p.produce_date}" required style="border-radius: 12px; padding: 0.625rem 1rem;">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Flock</label>
                        <select class="form-control" name="flock_id" style="border-radius: 12px; padding: 0.625rem 1rem;">
                            <option value="">— No specific flock —</option>
                            ${flockOptions}
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Notes</label>
                        <textarea class="form-control" name="notes" rows="3" style="border-radius: 12px; padding: 0.625rem 1rem;">${p.notes || ''}</textarea>
                    </div>
                </div>
                <div class="d-flex gap-2 mt-4">
                    <button type="button" class="btn btn-secondary flex-grow-1" data-bs-dismiss="modal" style="border-radius: 12px; padding: 0.625rem;">Cancel</button>
                    <button type="submit" class="btn btn-warning flex-grow-1" id="editProduceSubmit" style="border-radius: 12px; padding: 0.625rem;">Save Changes</button>
                </div>
            </form>`;
        });
}

document.addEventListener('submit', function (e) {
    if (e.target.id === 'editProduceForm') {
        e.preventDefault();
        const id  = e.target.dataset.id;
        const btn = document.getElementById('editProduceSubmit');
        btn.disabled = true; btn.textContent = 'Saving...';
        const fd = new FormData(e.target);

        fetch(`/produces/${id}/update-ajax`, {
            method: 'PUT',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
            body: JSON.stringify(Object.fromEntries(fd))
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('editProduceModal')).hide();
                location.reload();
            } else {
                alert(data.message);
                btn.disabled = false; btn.textContent = 'Save Changes';
            }
        });
    }
});

// ── Delete ────────────────────────────────────────────────────
function deleteProduce(id) {
    Swal.fire({
        title: 'Delete Produce Record?',
        text: "This action cannot be undone!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/produces/${id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Deleted!', 'Record has been deleted.', 'success').then(() => location.reload());
                } else {
                    Swal.fire('Error!', data.message, 'error');
                }
            });
        }
    });
}
</script>
@endpush