@extends('layouts.master')

@section('title', 'Produce Inventory Analytics')

@push('styles')
<style>
    :root {
        --inventory-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --card-radius: 24px;
    }

    .inventory-card-modern {
        background: white;
        border-radius: var(--card-radius);
        overflow: hidden;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 20px 35px -12px rgba(0, 0, 0, 0.08);
        border: 1px solid rgba(0, 0, 0, 0.05);
        position: relative;
        height: 100%;
    }

    .inventory-card-modern:hover {
        transform: translateY(-8px);
        box-shadow: 0 30px 50px -15px rgba(0, 0, 0, 0.15);
    }

    .inventory-card-modern::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: var(--inventory-gradient);
    }

    .inv-header-modern {
        padding: 1.5rem;
        background: linear-gradient(135deg, #f8fafc, #ffffff);
        border-bottom: 2px solid #f1f5f9;
    }

    .inv-stats-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .inv-stat-modern {
        text-align: center;
        padding: 1rem;
        background: linear-gradient(135deg, #f8fafc, #ffffff);
        border-radius: 16px;
        transition: all 0.3s ease;
    }

    .inv-stat-modern:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }

    .inv-stat-value {
        font-size: 1.8rem;
        font-weight: 800;
        line-height: 1;
        margin-bottom: 0.5rem;
    }

    .trend-up {
        background: linear-gradient(135deg, #10b981, #059669);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .trend-down {
        background: linear-gradient(135deg, #ef4444, #dc2626);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .stock-indicator {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        border-radius: 30px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .stock-critical {
        background: #fee2e2;
        color: #991b1b;
        animation: pulse 2s infinite;
    }

    .stock-low {
        background: #fef3c7;
        color: #92400e;
    }

    .stock-healthy {
        background: #d1fae5;
        color: #065f46;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.7; }
    }

    .progress-bar-custom {
        height: 8px;
        border-radius: 10px;
        background: #e2e8f0;
        overflow: hidden;
    }

    .progress-fill-custom {
        height: 100%;
        border-radius: 10px;
        transition: width 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }

    .progress-fill-custom::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        bottom: 0;
        right: 0;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
        animation: shimmer 2s infinite;
    }

    @keyframes shimmer {
        0% { transform: translateX(-100%); }
        100% { transform: translateX(100%); }
    }

    .sparkline-container {
        background: linear-gradient(135deg, #f8fafc, #ffffff);
        border-radius: 16px;
        padding: 1rem;
        margin-top: 1rem;
    }

    .badge-product-modern {
        font-size: 0.8rem;
        padding: 0.4rem 1rem;
        border-radius: 30px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .badge-eggs { background: linear-gradient(135deg, #fef3c7, #fde68a); color: #92400e; }
    .badge-live_bird { background: linear-gradient(135deg, #d1fae5, #a7f3d0); color: #065f46; }
    .badge-meat { background: linear-gradient(135deg, #fee2e2, #fecaca); color: #991b1b; }
    .badge-breeding_stock { background: linear-gradient(135deg, #ede9fe, #ddd6fe); color: #4c1d95; }
    .badge-manure { background: linear-gradient(135deg, #fef9c3, #fef08a); color: #713f12; }

    /* Responsive */
    @media (max-width: 768px) {
        .inv-stat-value {
            font-size: 1.3rem;
        }
        .inventory-card-modern {
            margin-bottom: 1rem;
        }
        .inv-stats-grid {
            gap: 0.5rem;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-3">

    {{-- Modern Header --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
        <div>
            <h1 class="display-6 fw-bold mb-2" style="background: linear-gradient(135deg, #667eea, #764ba2); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                <i class="fas fa-chart-line me-2"></i>Produce Inventory Analytics
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('produces.index') }}">Produce</a></li>
                    <li class="breadcrumb-item active">Inventory Analytics</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-sm-0">
            <a href="{{ route('produces.index') }}" class="btn btn-outline-primary btn-lg">
                <i class="fas fa-table-list me-2"></i>View Records
            </a>
            @if(in_array(auth()->user()->role ?? '', ['admin','manager','worker']))
            <button class="btn btn-primary btn-lg shadow-sm" id="newProduceBtn">
                <i class="fas fa-plus-circle me-2"></i>Quick Record
            </button>
            @endif
        </div>
    </div>

    {{-- Enhanced Filters --}}
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 20px;">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('produces.inventory') }}" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label fw-semibold mb-2">
                        <i class="fas fa-people-arrows me-2"></i>Filter by Flock
                    </label>
                    <select name="flock_id" class="form-select form-select-lg" style="border-radius: 12px;">
                        <option value="">All Flocks</option>
                        @foreach($flocks as $flock)
                            <option value="{{ $flock->id }}" {{ $flockId == $flock->id ? 'selected' : '' }}>
                                {{ $flock->flock_number }} – {{ $flock->breed_variety }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold mb-2">
                        <i class="fas fa-calendar-alt me-2"></i>Analysis Year
                    </label>
                    <select name="year" class="form-select form-select-lg" style="border-radius: 12px;">
                        @foreach($years as $y)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-lg px-4" style="border-radius: 12px;">
                            <i class="fas fa-chart-line me-2"></i>Analyze
                        </button>
                        <a href="{{ route('produces.inventory') }}" class="btn btn-outline-secondary btn-lg px-4" style="border-radius: 12px;">
                            <i class="fas fa-undo-alt me-2"></i>Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Inventory Dashboard Cards --}}
    <div class="row g-4 mb-5">
        @foreach($inventory as $item)
        @php
            $remaining = $item['remaining'];
            $produced = $item['produced'];
            $sold = $item['sold'];
            $sellThrough = $item['sell_through'];
            
            if ($remaining <= 0) {
                $stockStatus = 'Critical';
                $stockClass = 'stock-critical';
                $stockIcon = 'fa-exclamation-triangle';
            } elseif ($sellThrough >= 80) {
                $stockStatus = 'Low Stock';
                $stockClass = 'stock-low';
                $stockIcon = 'fa-chart-line';
            } else {
                $stockStatus = 'Healthy';
                $stockClass = 'stock-healthy';
                $stockIcon = 'fa-check-circle';
            }
            
            $stockColor = $remaining <= 0 ? '#ef4444' : ($sellThrough >= 80 ? '#f59e0b' : '#10b981');
            
            $icons = [
                'eggs' => 'fa-egg',
                'live_bird' => 'fa-dove',
                'meat' => 'fa-drumstick-bite',
                'manure' => 'fa-seedling'
            ];
        @endphp
        <div class="col-md-6 col-xl-4">
            <div class="inventory-card-modern">
                <div class="inv-header-modern">
                    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                        <div>
                            <span class="badge-product-modern badge-{{ $item['type'] }}">
                                <i class="fas {{ $icons[$item['type']] ?? 'fa-box' }} me-2"></i>
                                {{ $item['label'] }}
                            </span>
                        </div>
                        <div class="stock-indicator {{ $stockClass }}">
                            <i class="fas {{ $stockIcon }} me-1"></i>
                            {{ $stockStatus }}
                        </div>
                    </div>
                    <div class="inv-stats-grid">
                        <div class="inv-stat-modern">
                            <div class="inv-stat-value trend-up">{{ number_format($produced, 0) }}</div>
                            <small class="text-muted">Total Produced</small>
                        </div>
                        <div class="inv-stat-modern">
                            <div class="inv-stat-value trend-down">{{ number_format($sold, 0) }}</div>
                            <small class="text-muted">Total Sold</small>
                        </div>
                        <div class="inv-stat-modern">
                            <div class="inv-stat-value" style="color: {{ $stockColor }}">{{ number_format($remaining, 0) }}</div>
                            <small class="text-muted">In Stock</small>
                        </div>
                    </div>
                </div>

                <div class="p-4">
                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <small class="text-muted fw-semibold">Stock Utilization</small>
                            <small class="fw-semibold">{{ number_format($sellThrough, 1) }}% Sold</small>
                        </div>
                        <div class="progress-bar-custom">
                            <div class="progress-fill-custom" style="width: {{ $sellThrough }}%; background: {{ $stockColor }};"></div>
                        </div>
                    </div>

                    <div class="sparkline-container">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="fw-semibold small">
                                <i class="fas fa-chart-line me-1"></i>Monthly Trends ({{ $year }})
                            </span>
                            <div class="d-flex gap-3 small">
                                <span><i class="fas fa-circle text-success" style="font-size: 8px;"></i> Produced</span>
                                <span><i class="fas fa-circle text-danger" style="font-size: 8px;"></i> Sold</span>
                            </div>
                        </div>
                        <canvas id="sparkline-{{ $loop->index }}" height="60" width="100%"></canvas>
                    </div>

                    <div class="mt-4 pt-3 border-top">
                        <div class="row text-center small">
                            <div class="col-4">
                                <div class="text-muted mb-1">Monthly Avg</div>
                                <div class="fw-bold">{{ number_format($produced / 12, 0) }}</div>
                            </div>
                            <div class="col-4">
                                <div class="text-muted mb-1">Sell Through</div>
                                <div class="fw-bold">{{ number_format($sellThrough, 0) }}%</div>
                            </div>
                            <div class="col-4">
                                <div class="text-muted mb-1">Turnover</div>
                                <div class="fw-bold">{{ $produced > 0 ? number_format(($sold / $produced) * 100, 0) : 0 }}%</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Recent Activity Feed --}}
    <div class="card border-0 shadow-sm" style="border-radius: 20px; overflow: hidden;">
        <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-0">
            <h5 class="mb-0 fw-bold">
                <i class="fas fa-clock me-2 text-primary"></i>Recent Production Activity
            </h5>
            <a href="{{ route('produces.index') }}" class="btn btn-sm btn-link text-decoration-none">
                View All Records <i class="fas fa-arrow-right ms-1"></i>
            </a>
        </div>
        <div class="card-body p-0">
            <div class="list-group list-group-flush">
                @forelse($recentEntries as $entry)
                @php
                    $icons = [
                        'eggs' => 'fa-egg',
                        'live_bird' => 'fa-dove',
                        'meat' => 'fa-drumstick-bite',
                        'manure' => 'fa-seedling'
                    ];
                @endphp
                <div class="list-group-item d-flex align-items-center justify-content-between py-3 px-4 hover-shadow" style="transition: all 0.3s ease;">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-light rounded-circle p-2" style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas {{ $icons[$entry->product_type] ?? 'fa-box' }} text-primary fs-5"></i>
                        </div>
                        <div>
                            <div class="fw-semibold">{{ $entry->product_type_label }}</div>
                            <small class="text-muted">
                                <i class="fas fa-calendar-alt me-1"></i>{{ $entry->produce_date->diffForHumans() }} 
                                • Flock: {{ $entry->flock->flock_number ?? 'N/A' }}
                                • By: {{ $entry->creator->name ?? 'System' }}
                            </small>
                        </div>
                    </div>
                    <div class="text-end">
                        <div class="fw-bold fs-4">{{ number_format($entry->quantity, 0) }}</div>
                        <small class="text-muted">{{ $entry->unit }}</small>
                    </div>
                </div>
                @empty
                <div class="text-center py-5">
                    <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                    <p class="text-muted mb-0">No production activity recorded for {{ $year }}</p>
                    @if(in_array(auth()->user()->role ?? '', ['admin','manager','worker']))
                    <button class="btn btn-primary btn-sm mt-3" id="newProduceBtn">
                        <i class="fas fa-plus-circle me-2"></i>Record First Produce
                    </button>
                    @endif
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- Create Modal (same as index page) --}}
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
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Initialize sparkline charts for each inventory item
@foreach($inventory as $index => $item)
    const ctx{{ $index }} = document.getElementById('sparkline-{{ $index }}')?.getContext('2d');
    if (ctx{{ $index }}) {
        const monthlyData = @json($item['monthly']);
        new Chart(ctx{{ $index }}, {
            type: 'line',
            data: {
                labels: monthlyData.map(m => m.month),
                datasets: [
                    {
                        label: 'Produced',
                        data: monthlyData.map(m => m.produced),
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 0,
                        pointHoverRadius: 5,
                        pointBackgroundColor: '#10b981'
                    },
                    {
                        label: 'Sold',
                        data: monthlyData.map(m => m.sold),
                        borderColor: '#ef4444',
                        backgroundColor: 'rgba(239, 68, 68, 0.05)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 0,
                        pointHoverRadius: 5,
                        pointBackgroundColor: '#ef4444'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { display: false },
                    tooltip: { 
                        mode: 'index', 
                        intersect: false,
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + context.parsed.y.toFixed(0) + ' units';
                            }
                        }
                    }
                },
                scales: {
                    x: { display: false },
                    y: { display: false }
                },
                elements: { line: { borderJoin: 'round' } }
            }
        });
    }
@endforeach

// Unit auto-fill function
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

// Create produce modal
document.getElementById('newProduceBtn')?.addEventListener('click', function () {
    const body = document.getElementById('createProduceBody');
    body.innerHTML = `<div class="text-center py-4"><div class="spinner-border text-success" role="status"></div><p class="mt-2">Loading form...</p></div>`;
    const modal = new bootstrap.Modal(document.getElementById('createProduceModal'));
    modal.show();

    fetch('{{ route("produces.create-form") }}')
        .then(r => r.json())
        .then(data => {
            if (!data.success) { body.innerHTML = `<div class="alert alert-danger">${data.message}</div>`; return; }
            const flockOptions = data.flocks.map(f =>
                `<option value="${f.id}">${f.flock_number} – ${f.breed_variety}</option>`
            ).join('');
            const typeOptions = Object.entries(data.productTypes).map(([k,v]) =>
                `<option value="${k}">${v}</option>`
            ).join('');
            const unitOptions = data.units.map(u => `<option value="${u}">${u}</option>`).join('');
            body.innerHTML = `
            <form id="createProduceForm">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold">Product Type <span class="text-danger">*</span></label>
                        <select class="form-control" id="cp_product_type" name="product_type" required
                            onchange="fetchDefaultUnit(this,'cp_unit')" style="border-radius: 12px; padding: 0.625rem 1rem;">
                            <option value="">Select product...</option>${typeOptions}
                        </select>
                    </div>
                    <div class="col-8">
                        <label class="form-label fw-semibold">Quantity <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="quantity" step="0.01" min="0.01" required placeholder="e.g. 120" style="border-radius: 12px; padding: 0.625rem 1rem;">
                    </div>
                    <div class="col-4">
                        <label class="form-label fw-semibold">Unit <span class="text-danger">*</span></label>
                        <select class="form-control" name="unit" id="cp_unit" required style="border-radius: 12px; padding: 0.625rem 1rem;">${unitOptions}</select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="produce_date" required value="${new Date().toISOString().split('T')[0]}" max="${new Date().toISOString().split('T')[0]}" style="border-radius: 12px; padding: 0.625rem 1rem;">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Flock</label>
                        <select class="form-control" name="flock_id" style="border-radius: 12px; padding: 0.625rem 1rem;">
                            <option value="">— No specific flock —</option>${flockOptions}
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Notes</label>
                        <textarea class="form-control" name="notes" rows="3" placeholder="e.g. Morning collection, House A" style="border-radius: 12px; padding: 0.625rem 1rem;"></textarea>
                    </div>
                </div>
                <div class="d-flex gap-2 mt-4">
                    <button type="button" class="btn btn-secondary flex-grow-1" data-bs-dismiss="modal" style="border-radius: 12px; padding: 0.625rem;">Cancel</button>
                    <button type="submit" class="btn btn-success flex-grow-1" id="createProduceSubmit" style="border-radius: 12px; padding: 0.625rem; background: linear-gradient(135deg, #059669, #10b981); border: none;">
                        <span class="submit-text">Save Record</span>
                        <span class="spinner-border spinner-border-sm d-none"></span>
                    </button>
                </div>
            </form>`;
        });
});

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
</script>
@endpush