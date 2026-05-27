{{-- resources/views/daily-logs/index.blade.php --}}
@extends('layouts.master')

@section('content')

<style>
    .daily-log-table th {
        white-space: nowrap;
        font-size: 13px;
        font-weight: 600;
        background: #f8f9fa;
        color: #495057;
        vertical-align: middle;
    }
    .daily-log-table td { vertical-align: middle; }
    .actions-column { width: 210px; white-space: nowrap; }
    .action-btn-group { display: flex; gap: 6px; flex-wrap: nowrap; }
    .action-btn-group .btn { padding: 5px 10px; font-size: 12px; border-radius: 8px; }

    #createLogModal .modal-content,
    #viewLogModal .modal-content,
    #editLogModal .modal-content {
        border: none; border-radius: 16px; overflow: hidden;
    }
    #createLogModal .modal-header,
    #viewLogModal .modal-header,
    #editLogModal .modal-header {
        background: linear-gradient(135deg, #2f9088, #276f69);
        color: #fff; border-bottom: none; padding: 1.2rem 1.5rem;
    }
    #createLogModal .modal-header h4,
    #viewLogModal .modal-header h4,
    #editLogModal .modal-header h5,
    #createLogModal .modal-header small,
    #viewLogModal .modal-header small { color: #fff !important; }
    #createLogModal .modal-body,
    #viewLogModal .modal-body,
    #editLogModal .modal-body { background: #f5f7fb; padding: 1.5rem; }
    #createLogModal label, #editLogModal label {
        color: #495057 !important; font-weight: 600; margin-bottom: 6px;
    }
    #createLogModal .form-control, #createLogModal .form-select,
    #editLogModal .form-control, #editLogModal .form-select {
        border-radius: 10px; border: 1px solid #dce1e7;
        min-height: 46px; background: #fff !important; color: #212529 !important;
    }
    #createLogModal textarea, #editLogModal textarea { min-height: 120px; resize: vertical; }
    #createLogModal .modal-footer, #viewLogModal .modal-footer, #editLogModal .modal-footer {
        background: #fff; border-top: 1px solid #edf2f7; padding: 1rem 1.5rem;
    }
    #createLogModal .btn-close, #viewLogModal .btn-close, #editLogModal .btn-close {
        filter: brightness(0) invert(1); opacity: 1;
    }
    #viewLogModal .card { border: 1px solid #edf2f7; border-radius: 14px; overflow: hidden; box-shadow: none; }
    #viewLogModal .card-header { background: #ffffff !important; color: #212529 !important; border-bottom: 1px solid #edf2f7; padding: 1rem 1.2rem; }
    #viewLogModal .card-body { background: #fff; color: #495057 !important; }
    #viewLogModal table th { color: #495057 !important; font-weight: 600; }
    #viewLogModal table td { color: #212529 !important; }

    /* Production badge colours per type */
    .prod-badge-eggs      { background: #fef9c3; color: #713f12; }
    .prod-badge-milk      { background: #dbeafe; color: #1e40af; }
    .prod-badge-wool      { background: #f3e8ff; color: #6b21a8; }
    .prod-badge-honey     { background: #fef3c7; color: #92400e; }
    .prod-badge-meat      { background: #fee2e2; color: #991b1b; }
    .prod-badge-live_bird { background: #d1fae5; color: #065f46; }
    .prod-badge-default   { background: #f1f5f9; color: #475569; }

    @media (max-width: 768px) {
        .actions-column { width: 100%; }
        .action-btn-group { flex-wrap: wrap; }
        #createLogModal .modal-body, #viewLogModal .modal-body, #editLogModal .modal-body { padding: 1rem; }
    }
</style>

<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6 col-12">
                <h2>Daily Logs</h2>
                <p class="mb-0 text-title-gray">View all daily records and observations</p>
            </div>
            <div class="col-sm-6 col-12">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard') }}"><i class="iconly-Home icli svg-color"></i></a>
                    </li>
                    <li class="breadcrumb-item">Daily Logs</li>
                    <li class="breadcrumb-item active">All Logs</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">

                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div>
                            <h3 class="mb-1">Daily Log Records</h3>
                            <small class="text-muted">Track operational flock performance</small>
                        </div>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createLogModal">
                            <i class="fa fa-plus me-1"></i> New Daily Log
                        </button>
                    </div>
                </div>

                <div class="card-body">

                    {{-- FILTERS --}}
                    <form method="GET" action="{{ route('daily-logs.index') }}" class="row mb-4">
                        <div class="col-md-3 mb-2">
                            <label class="form-label">Flock</label>
                            <select name="flock_id" class="form-select">
                                <option value="">All Flocks</option>
                                @foreach($flocks as $flock)
                                    <option value="{{ $flock->id }}" {{ request('flock_id') == $flock->id ? 'selected' : '' }}>
                                        {{ $flock->flock_number }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="form-label">Start Date</label>
                            <input type="date" name="start_date" class="form-control"
                                   value="{{ $startDate instanceof \Carbon\Carbon ? $startDate->format('Y-m-d') : $startDate }}">
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="form-label">End Date</label>
                            <input type="date" name="end_date" class="form-control"
                                   value="{{ $endDate instanceof \Carbon\Carbon ? $endDate->format('Y-m-d') : $endDate }}">
                        </div>
                        <div class="col-md-3 mb-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">Filter</button>
                            <a href="{{ route('daily-logs.index') }}" class="btn btn-warning border">Reset</a>
                        </div>
                    </form>

                    {{-- TABLE --}}
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 daily-log-table">
                            <thead class="table-light">
                                <tr>
                                    <th class="py-3">Date</th>
                                    <th class="py-3">Flock</th>
                                    <th class="py-3">Species</th>
                                    <th class="py-3">Mortality</th>
                                    <th class="py-3">Culling</th>
                                    <th class="py-3">Feed (kg)</th>
                                    <th class="py-3">Water (L)</th>
                                    <th class="py-3">Avg Weight</th>
                                    <th class="py-3">Production</th>
                                    <th class="py-3">Temp (°C)</th>
                                    <th class="py-3 text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="logsTableBody">
                                @forelse($logs as $log)
                                @php
                                    $speciesCode = $log->flock->species->code ?? '';
                                    $speciesName = $log->flock->species->name ?? '';

                                    // Safely cast species_metrics to array
                                    $metrics = $log->species_metrics ?? [];
                                    if (is_object($metrics)) {
                                        $metrics = (array) $metrics;
                                    }

                                    // Determine what this species produces and pull the right value
                                    $prodType    = 'none';
                                    $prodQty     = 0;
                                    $prodDamaged = 0;
                                    $prodUnit    = '';
                                    $prodLabel   = '';

                                    if (in_array($speciesCode, ['CH', 'QU', 'DK', 'GS', 'TK'])) {
                                        // Poultry → eggs
                                        $prodType    = 'eggs';
                                        $prodQty     = $log->eggs_collected ?? 0;
                                        $prodDamaged = $log->eggs_damaged   ?? 0;
                                        $prodUnit    = 'pcs';
                                        $prodLabel   = 'Eggs';
                                    } elseif (in_array($speciesCode, ['CT', 'GT', 'SH', 'BF'])) {
                                        // Dairy/ruminants → milk
                                        $prodType    = 'milk';
                                        $prodQty     = $metrics['milk_litres']         ?? 0;
                                        $prodDamaged = $metrics['milk_litres_damaged']  ?? 0;
                                        $prodUnit    = 'L';
                                        $prodLabel   = 'Milk';
                                    } elseif (in_array($speciesCode, ['RB', 'PG'])) {
                                        // Rabbits / Pigs → meat weight
                                        $prodType  = 'meat';
                                        $prodQty   = $metrics['meat_kg'] ?? 0;
                                        $prodUnit  = 'kg';
                                        $prodLabel = 'Meat';
                                    } elseif ($speciesCode === 'BE') {
                                        // Bees → honey
                                        $prodType  = 'honey';
                                        $prodQty   = $metrics['honey_kg'] ?? 0;
                                        $prodUnit  = 'kg';
                                        $prodLabel = 'Honey';
                                    }

                                    $netProd = max(0, $prodQty - $prodDamaged);
                                @endphp
                                <tr data-log-id="{{ $log->id }}">
                                    <td>{{ $log->log_date->format('d M Y') }}</td>
                                    <td>
                                        <button type="button"
                                                class="btn btn-link p-0 text-primary fw-semibold text-decoration-none view-log-btn"
                                                data-log-id="{{ $log->id }}">
                                            {{ $log->flock->flock_number ?? 'N/A' }}
                                        </button>
                                    </td>
                                    <td>{{ $speciesName ?: 'N/A' }}</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1" style="height:6px;">
                                                <div class="progress-bar bg-danger"
                                                     style="width:{{ min(100, ($log->mortality_count / max($log->flock->current_count, 1)) * 100) }}%"></div>
                                            </div>
                                            <span class="small fw-semibold {{ $log->mortality_count > 0 ? 'text-danger' : 'text-muted' }}">
                                                {{ $log->mortality_count }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="text-center">{{ $log->culling_count }}</td>
                                    <td>{{ number_format($log->feed_intake_kg) }} kg</td>
                                    <td>{{ number_format($log->water_consumption_liters) }} L</td>
                                    <td>{{ $log->average_weight_kg ? number_format($log->average_weight_kg, 2).' kg' : 'N/A' }}</td>

                                    {{-- Dynamic Production Cell --}}
                                    <td>
                                        @if($prodQty > 0)
                                            <span class="badge prod-badge-{{ $prodType }} fw-bold px-2 py-1" style="border-radius:8px;">
                                                {{ number_format($netProd, 0) }} {{ $prodUnit }}
                                            </span>
                                            <div style="font-size:0.7rem; margin-top:2px;">
                                                <span class="text-muted">{{ number_format($prodQty, 0) }} {{ strtolower($prodLabel) }}</span>
                                                @if($prodDamaged > 0)
                                                    <span class="text-danger ms-1">· {{ number_format($prodDamaged, 0) }} dmg</span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>

                                    <td>
                                        @if($log->min_temperature_c && $log->max_temperature_c)
                                            <span class="badge bg-info-soft text-info">
                                                {{ $log->min_temperature_c }}° – {{ $log->max_temperature_c }}°
                                            </span>
                                        @else
                                            N/A
                                        @endif
                                    </td>

                                    <td>
                                        <div class="btn-group gap-1">
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-primary view-log-btn"
                                                    data-log-id="{{ $log->id }}"
                                                    title="View Details">
                                                <i class="fas fa-eye"></i> View
                                            </button>
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-warning edit-log-btn"
                                                    data-log-id="{{ $log->id }}"
                                                    title="Edit">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-danger delete-log-btn"
                                                    data-id="{{ $log->id }}"
                                                    title="Delete">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr id="emptyRow">
                                    <td colspan="11" class="text-center py-5">
                                        <div class="empty-state">
                                            <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                                            <h5 class="text-muted">No Daily Logs Found</h5>
                                            <p class="text-muted mb-3">Start recording daily flock operations.</p>
                                            <button type="button" class="btn btn-primary"
                                                    data-bs-toggle="modal" data-bs-target="#createLogModal">
                                                <i class="fas fa-plus me-2"></i>Create Your First Daily Log
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- PAGINATION --}}
                    @if($logs->hasPages())
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div class="text-muted">
                            Showing {{ $logs->firstItem() }} to {{ $logs->lastItem() }} of {{ $logs->total() }} daily logs
                        </div>
                        <nav>
                            <ul class="pagination mb-0">
                                @if($logs->onFirstPage())
                                    <li class="page-item disabled"><span class="page-link">‹ Previous</span></li>
                                @else
                                    <li class="page-item"><a class="page-link" href="{{ $logs->previousPageUrl() }}">‹ Previous</a></li>
                                @endif
                                @php
                                    $current = $logs->currentPage(); $last = $logs->lastPage();
                                    $start = max(1, $current - 2); $end = min($last, $current + 2);
                                @endphp
                                @if($start > 1)
                                    <li class="page-item"><a class="page-link" href="{{ $logs->url(1) }}">1</a></li>
                                    @if($start > 2)<li class="page-item disabled"><span class="page-link">...</span></li>@endif
                                @endif
                                @for($page = $start; $page <= $end; $page++)
                                    @if($page == $current)
                                        <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                                    @else
                                        <li class="page-item"><a class="page-link" href="{{ $logs->url($page) }}">{{ $page }}</a></li>
                                    @endif
                                @endfor
                                @if($end < $last)
                                    @if($end < $last - 1)<li class="page-item disabled"><span class="page-link">...</span></li>@endif
                                    <li class="page-item"><a class="page-link" href="{{ $logs->url($last) }}">{{ $last }}</a></li>
                                @endif
                                @if($logs->hasMorePages())
                                    <li class="page-item"><a class="page-link" href="{{ $logs->nextPageUrl() }}">Next ›</a></li>
                                @else
                                    <li class="page-item disabled"><span class="page-link">Next ›</span></li>
                                @endif
                            </ul>
                        </nav>
                    </div>
                    @endif

                </div>{{-- card-body --}}
            </div>{{-- card --}}
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════
    VIEW MODAL — loads via AJAX fetch /daily-logs/{id}/json
════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="viewLogModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg overflow-hidden">
            <div class="modal-header border-0">
                <div>
                    <h4 class="mb-1 fw-bold"><i class="fa fa-clipboard-list me-2"></i>Daily Log Details</h4>
                    <small class="opacity-75">Detailed operational information</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body bg-light" id="viewLogBody">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 text-muted">Loading…</p>
                </div>
            </div>
            <div class="modal-footer bg-white border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════
    EDIT MODAL — populated via AJAX
════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="editLogModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header" style="background:linear-gradient(135deg,#f59e0b,#d97706);">
                <h5 class="modal-title" style="color:#fff;"><i class="fa fa-edit me-2"></i>Edit Daily Log</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="editLogBody">
                <div class="text-center py-5">
                    <div class="spinner-border text-warning" role="status"></div>
                    <p class="mt-2 text-muted">Loading…</p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════
    CREATE MODAL
════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="createLogModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg overflow-hidden">
            <div class="modal-header bg-primary text-white border-0">
                <div>
                    <h4 class="mb-1 fw-bold"><i class="fa fa-plus-circle me-2"></i>New Daily Log</h4>
                    <small class="opacity-75">Record daily operational data</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="createLogForm" method="POST" action="{{ route('daily-logs.store') }}">
                @csrf
                <div class="modal-body bg-light">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Select Flock <span class="text-danger">*</span></label>
                            <select name="flock_id" class="form-select" required id="create_flock_id">
                                <option value="">Choose flock</option>
                                @foreach($flocks as $flock)
                                    <option value="{{ $flock->id }}"
                                            data-species="{{ $flock->species->code ?? '' }}"
                                            data-species-name="{{ $flock->species->name ?? '' }}">
                                        {{ $flock->flock_number }} – {{ $flock->species->name ?? '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Log Date <span class="text-danger">*</span></label>
                            <input type="date" name="log_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Mortality</label>
                            <input type="number" name="mortality_count" class="form-control" value="0" min="0">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Culling</label>
                            <input type="number" name="culling_count" class="form-control" value="0" min="0">
                        </div>

                        {{-- Dynamic Production Fields --}}
                        <div class="col-12" id="create_prod_section" style="display:none;">
                            <div class="row g-3 p-3 rounded-3" style="background:#f0fdf4;border:1px solid #bbf7d0;">
                                <div class="col-12 mb-1">
                                    <span class="fw-semibold text-success" id="create_prod_label">
                                        <i class="fas fa-box me-1"></i> Production
                                    </span>
                                    <small class="text-muted ms-2" id="create_prod_subtitle"></small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" id="create_prod_qty_label">Quantity Collected</label>
                                    {{--
                                        NOTE: name="eggs_collected" is intentionally reused for ALL species.
                                        The controller's resolveProductionFields() method maps it to the
                                        correct column (eggs_collected, milk_litres, meat_kg, honey_kg)
                                        based on the selected flock's species code.
                                    --}}
                                    <input type="number" name="eggs_collected" id="create_prod_qty"
                                           class="form-control" value="0" min="0" step="0.01">
                                </div>
                                <div class="col-md-6" id="create_prod_dmg_wrapper">
                                    <label class="form-label" id="create_prod_dmg_label">Damaged / Unusable</label>
                                    <input type="number" name="eggs_damaged" id="create_prod_dmg"
                                           class="form-control" value="0" min="0" step="0.01">
                                    <small class="text-muted" id="create_prod_net"></small>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Feed Intake (kg)</label>
                            <input type="number" name="feed_intake_kg" class="form-control" step="0.1">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Water (L)</label>
                            <input type="number" name="water_consumption_liters" class="form-control" step="0.1">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Avg Weight (kg)</label>
                            <input type="number" name="average_weight_kg" class="form-control" step="0.01">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Min Temp °C</label>
                            <input type="number" name="min_temperature_c" class="form-control" step="0.1">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Max Temp °C</label>
                            <input type="number" name="max_temperature_c" class="form-control" step="0.1">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Min Humidity %</label>
                            <input type="number" name="min_humidity" class="form-control" step="0.1">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Max Humidity %</label>
                            <input type="number" name="max_humidity" class="form-control" step="0.1">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Ammonia (ppm)</label>
                            <input type="number" name="ammonia_ppm" class="form-control" step="0.1">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" rows="4" class="form-control"
                                      placeholder="Enter observations, unusual behaviour, feed issues, disease signs, etc."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-white border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="createLogSubmit">
                        <span class="submit-text"><i class="fa fa-save me-1"></i>Save Daily Log</span>
                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- SweetAlert2 --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// ─── Species → production type map ────────────────────────────────────────────
// These codes must match the species codes in your database.
// The form always sends production qty as `eggs_collected` and damage as
// `eggs_damaged` — the controller maps them to the right column per species.
const SPECIES_PROD = {
    // Poultry → eggs (stored in eggs_collected / eggs_damaged columns)
    CH: { type: 'eggs',  qtyLabel: 'Eggs Collected',          dmgLabel: 'Eggs Damaged (cracked/soiled)', unit: 'pieces', title: '🥚 Egg Production',    hasDamage: true  },
    QU: { type: 'eggs',  qtyLabel: 'Eggs Collected',          dmgLabel: 'Eggs Damaged',                  unit: 'pieces', title: '🥚 Egg Production',    hasDamage: true  },
    DK: { type: 'eggs',  qtyLabel: 'Eggs Collected',          dmgLabel: 'Eggs Damaged',                  unit: 'pieces', title: '🥚 Egg Production',    hasDamage: true  },
    GS: { type: 'eggs',  qtyLabel: 'Eggs Collected',          dmgLabel: 'Eggs Damaged',                  unit: 'pieces', title: '🥚 Egg Production',    hasDamage: true  },
    TK: { type: 'eggs',  qtyLabel: 'Eggs Collected',          dmgLabel: 'Eggs Damaged',                  unit: 'pieces', title: '🥚 Egg Production',    hasDamage: true  },
    // Dairy → milk (stored in species_metrics.milk_litres)
    CT: { type: 'milk',  qtyLabel: 'Milk Collected (L)',       dmgLabel: 'Milk Wasted (L)',               unit: 'litres', title: '🥛 Milk Production',   hasDamage: true  },
    GT: { type: 'milk',  qtyLabel: 'Milk Collected (L)',       dmgLabel: 'Milk Wasted (L)',               unit: 'litres', title: '🥛 Milk Production',   hasDamage: true  },
    SH: { type: 'milk',  qtyLabel: 'Milk Collected (L)',       dmgLabel: 'Milk Wasted (L)',               unit: 'litres', title: '🥛 Milk Production',   hasDamage: true  },
    BF: { type: 'milk',  qtyLabel: 'Milk Collected (L)',       dmgLabel: 'Milk Wasted (L)',               unit: 'litres', title: '🥛 Milk Production',   hasDamage: true  },
    // Rabbits / Pigs → meat (stored in species_metrics.meat_kg)
    RB: { type: 'meat',  qtyLabel: 'Meat Weight (kg)',         dmgLabel: 'Wastage (kg)',                  unit: 'kg',     title: '🥩 Meat Production',   hasDamage: false },
    PG: { type: 'meat',  qtyLabel: 'Live Weight / Meat (kg)', dmgLabel: 'Wastage (kg)',                  unit: 'kg',     title: '🐷 Meat Production',   hasDamage: false },
    // Bees → honey (stored in species_metrics.honey_kg)
    BE: { type: 'honey', qtyLabel: 'Honey Collected (kg)',     dmgLabel: 'Honey Wasted (kg)',             unit: 'kg',     title: '🍯 Honey Production',  hasDamage: false },
};

const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

// ─── Helpers ─────────────────────────────────────────────────────────────────

function reloadAfterModal(modalId) {
    const el       = document.getElementById(modalId);
    const instance = bootstrap.Modal.getInstance(el);
    if (!instance) { window.location.reload(); return; }
    el.addEventListener('hidden.bs.modal', function handler() {
        el.removeEventListener('hidden.bs.modal', handler);
        window.location.reload();
    });
    instance.hide();
}

function removeTableRow(logId) {
    const row = document.querySelector(`tr[data-log-id="${logId}"]`);
    if (row) {
        row.style.transition = 'opacity 0.3s';
        row.style.opacity    = '0';
        setTimeout(() => {
            row.remove();
            if (!document.querySelector('#logsTableBody tr[data-log-id]')) {
                document.getElementById('logsTableBody').innerHTML = `
                    <tr id="emptyRow">
                        <td colspan="11" class="text-center py-5">
                            <div class="empty-state">
                                <i class="fas fa-clipboard-list fa-3x text-muted mb-3 d-block"></i>
                                <h5 class="text-muted">No Daily Logs Found</h5>
                            </div>
                        </td>
                    </tr>`;
            }
        }, 300);
    }
}

/** Format a raw number for display — falls back to 'N/A' for null/undefined */
function fmt(val, decimals = 1) {
    if (val === null || val === undefined || val === '' || val === 'N/A') return 'N/A';
    const n = parseFloat(val);
    return isNaN(n) ? 'N/A' : n.toFixed(decimals);
}

// ─── CREATE: show/hide production section based on selected flock ─────────────
document.getElementById('create_flock_id')?.addEventListener('change', function () {
    const opt     = this.options[this.selectedIndex];
    const code    = opt.dataset.species ?? '';
    const prod    = SPECIES_PROD[code];
    const section = document.getElementById('create_prod_section');
    const dmgWrap = document.getElementById('create_prod_dmg_wrapper');

    if (prod) {
        document.getElementById('create_prod_label').textContent      = prod.title;
        document.getElementById('create_prod_subtitle').textContent   = 'Syncs automatically to Produce Records';
        document.getElementById('create_prod_qty_label').textContent  = prod.qtyLabel;
        document.getElementById('create_prod_dmg_label').textContent  = prod.dmgLabel;
        // Hide damage field for species where it doesn't apply (meat, honey)
        dmgWrap.style.display = prod.hasDamage ? '' : 'none';
        section.style.display = '';
    } else {
        section.style.display = 'none';
    }

    // Reset values when flock changes
    document.getElementById('create_prod_qty').value = 0;
    document.getElementById('create_prod_dmg').value = 0;
    document.getElementById('create_prod_net').textContent = '';
});

// Live net display in create form
['create_prod_qty', 'create_prod_dmg'].forEach(id => {
    document.getElementById(id)?.addEventListener('input', updateCreateNet);
});

function updateCreateNet() {
    const qty = parseFloat(document.getElementById('create_prod_qty')?.value) || 0;
    const dmg = parseFloat(document.getElementById('create_prod_dmg')?.value) || 0;
    const net = document.getElementById('create_prod_net');
    if (!net) return;
    if (dmg > qty) {
        net.textContent = '⚠ Damaged cannot exceed collected';
        net.style.color = '#ef4444';
    } else {
        net.textContent = `✅ Net: ${(qty - dmg).toFixed(2)}`;
        net.style.color = '#059669';
    }
}

// ─── CREATE form: AJAX submit ─────────────────────────────────────────────────
document.getElementById('createLogForm')?.addEventListener('submit', function (e) {
    e.preventDefault();

    const btn = document.getElementById('createLogSubmit');
    btn.querySelector('.submit-text').classList.add('d-none');
    btn.querySelector('.spinner-border').classList.remove('d-none');
    btn.disabled = true;

    fetch(this.action, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' },
        body: new FormData(this)
    })
    .then(r => { if (!r.ok) return r.json().then(d => Promise.reject(d)); return r.json(); })
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success', title: 'Saved!', text: data.message,
                timer: 2000, showConfirmButton: false, timerProgressBar: true
            }).then(() => reloadAfterModal('createLogModal'));
        } else {
            Swal.fire({ icon: 'error', title: 'Error!', text: data.message, confirmButtonColor: '#d33' });
            btn.querySelector('.submit-text').classList.remove('d-none');
            btn.querySelector('.spinner-border').classList.add('d-none');
            btn.disabled = false;
        }
    })
    .catch(err => {
        Swal.fire({ icon: 'error', title: 'Error!', text: err?.message ?? 'Something went wrong.', confirmButtonColor: '#d33' });
        btn.querySelector('.submit-text').classList.remove('d-none');
        btn.querySelector('.spinner-border').classList.add('d-none');
        btn.disabled = false;
    });
});

// ─── VIEW: fetch log JSON and render ─────────────────────────────────────────
document.addEventListener('click', function (e) {
    const btn = e.target.closest('.view-log-btn');
    if (!btn) return;

    const logId = btn.dataset.logId;
    const body  = document.getElementById('viewLogBody');
    body.innerHTML = `<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div><p class="mt-2 text-muted">Loading…</p></div>`;
    new bootstrap.Modal(document.getElementById('viewLogModal')).show();

    fetch(`/daily-logs/${logId}/json`, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success) {
            body.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
            return;
        }
        const l = data.log;

        const prodLabels = {
            eggs:  '🥚 Eggs',
            milk:  '🥛 Milk',
            meat:  '🥩 Meat',
            honey: '🍯 Honey',
            wool:  '🧶 Wool',
        };

        // Format numbers for display in the view modal
        const feedFmt   = fmt(l.feed_intake_kg,           1);
        const waterFmt  = fmt(l.water_consumption_liters, 1);
        const weightFmt = l.average_weight_kg !== null && l.average_weight_kg !== undefined
            ? fmt(l.average_weight_kg, 2) + ' kg'
            : 'N/A';

        body.innerHTML = `
        <div class="row g-4">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0 fw-semibold"><i class="fa fa-circle-info text-primary me-2"></i>Basic Information</h5>
                    </div>
                    <div class="card-body">
                        <table class="table align-middle mb-0">
                            <tr><th width="45%">Date</th><td>${l.log_date}</td></tr>
                            <tr><th>Flock</th><td>${l.flock_number}</td></tr>
                            <tr><th>Species</th><td>${l.species_name}</td></tr>
                            <tr><th>Recorded By</th><td>${l.recorded_by}</td></tr>
                            <tr><th>Mortality</th><td><span class="${l.mortality_count > 0 ? 'text-danger fw-bold' : ''}">${l.mortality_count}</span></td></tr>
                            <tr><th>Culling</th><td>${l.culling_count}</td></tr>
                            <tr><th>Total Loss</th><td>${l.total_loss} <small class="text-muted">(${l.mortality_rate}% rate)</small></td></tr>
                            <tr><th>Feed Intake</th><td>${feedFmt} kg</td></tr>
                            <tr><th>Water</th><td>${waterFmt} L</td></tr>
                            <tr><th>Avg Weight</th><td>${weightFmt}</td></tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0 fw-semibold"><i class="fa fa-temperature-half text-danger me-2"></i>Environmental Data</h5>
                    </div>
                    <div class="card-body">
                        <table class="table align-middle mb-0">
                            <tr><th width="45%">Temperature</th><td>${l.min_temp ?? 'N/A'}°C – ${l.max_temp ?? 'N/A'}°C</td></tr>
                            <tr><th>Humidity</th><td>${l.min_humidity ?? 'N/A'}% – ${l.max_humidity ?? 'N/A'}%</td></tr>
                            <tr><th>Ammonia</th><td>${l.ammonia_ppm ?? 'N/A'} ppm</td></tr>
                        </table>
                    </div>
                </div>
                ${l.production ? `
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0 fw-semibold">${prodLabels[l.production.type] ?? '📦 Production'}</h5>
                    </div>
                    <div class="card-body">
                        <table class="table align-middle mb-0">
                            <tr><th width="45%">Collected</th><td class="fw-bold">${l.production.qty} ${l.production.unit}</td></tr>
                            <tr><th>Damaged</th><td class="text-danger">${l.production.damaged} ${l.production.unit}</td></tr>
                            <tr><th>Net</th><td class="text-success fw-bold">${l.production.net} ${l.production.unit}</td></tr>
                        </table>
                    </div>
                </div>` : ''}
            </div>
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0 fw-semibold"><i class="fa fa-note-sticky text-warning me-2"></i>Notes & Observations</h5>
                    </div>
                    <div class="card-body">
                        <div class="bg-light rounded p-3 border">
                            <p class="mb-0 text-dark">${l.notes ?? 'No notes added.'}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>`;
    })
    .catch(() => {
        body.innerHTML = `<div class="alert alert-danger">Failed to load log details.</div>`;
    });
});

// ─── EDIT: fetch log data, build form, submit via AJAX ────────────────────────
document.addEventListener('click', function (e) {
    const btn = e.target.closest('.edit-log-btn');
    if (!btn) return;

    const logId = btn.dataset.logId;
    const body  = document.getElementById('editLogBody');
    body.innerHTML = `<div class="text-center py-5"><div class="spinner-border text-warning" role="status"></div><p class="mt-2 text-muted">Loading…</p></div>`;
    new bootstrap.Modal(document.getElementById('editLogModal')).show();

    fetch(`/daily-logs/${logId}/json`, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success) {
            body.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
            return;
        }

        const l           = data.log;
        const speciesCode = data.species_code ?? '';
        const prod        = data.production ?? null;
        const prodCfg     = SPECIES_PROD[speciesCode];

        // Production section — only shown when species has known production
        const prodSection = prodCfg && prod ? `
        <div class="col-12">
            <div class="row g-3 p-3 rounded-3" style="background:#fffbeb;border:1px solid #fde68a;">
                <div class="col-12 mb-1">
                    <span class="fw-semibold text-warning">${prodCfg.title}</span>
                    <small class="text-muted ms-2">Synced to Produce Records</small>
                </div>
                <div class="col-md-6">
                    <label class="form-label">${prodCfg.qtyLabel}</label>
                    <input type="number" name="eggs_collected" id="ep_prod_qty"
                           class="form-control" value="${prod.qty ?? 0}" min="0" step="0.01"
                           oninput="editProdNet()">
                </div>
                <div class="col-md-6" ${!prodCfg.hasDamage ? 'style="display:none;"' : ''}>
                    <label class="form-label">${prodCfg.dmgLabel}</label>
                    <input type="number" name="eggs_damaged" id="ep_prod_dmg"
                           class="form-control" value="${prod.damaged ?? 0}" min="0" step="0.01"
                           oninput="editProdNet()">
                    <small id="ep_prod_net" class="mt-1 d-block"></small>
                </div>
            </div>
        </div>` : '';

        body.innerHTML = `
        <form id="editLogForm" data-id="${l.id}">
            <div class="row g-3 p-3">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Mortality Count</label>
                    <input type="number" name="mortality_count" class="form-control" value="${l.mortality_count}" min="0">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Culling Count</label>
                    <input type="number" name="culling_count" class="form-control" value="${l.culling_count}" min="0">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Feed Intake (kg)</label>
                    <input type="number" name="feed_intake_kg" class="form-control" step="0.1"
                           value="${l.feed_intake_kg ?? ''}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Water (L)</label>
                    <input type="number" name="water_consumption_liters" class="form-control" step="0.1"
                           value="${l.water_consumption_liters ?? ''}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Avg Weight (kg)</label>
                    <input type="number" name="average_weight_kg" class="form-control" step="0.01"
                           value="${l.average_weight_kg ?? ''}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Min Temp °C</label>
                    <input type="number" name="min_temperature_c" class="form-control" step="0.1"
                           value="${l.min_temp ?? ''}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Max Temp °C</label>
                    <input type="number" name="max_temperature_c" class="form-control" step="0.1"
                           value="${l.max_temp ?? ''}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Ammonia (ppm)</label>
                    <input type="number" name="ammonia_ppm" class="form-control" step="0.1"
                           value="${l.ammonia_ppm ?? ''}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Min Humidity %</label>
                    <input type="number" name="min_humidity" class="form-control" step="0.1"
                           value="${l.min_humidity ?? ''}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Max Humidity %</label>
                    <input type="number" name="max_humidity" class="form-control" step="0.1"
                           value="${l.max_humidity ?? ''}">
                </div>
                ${prodSection}
                <div class="col-12">
                    <label class="form-label fw-semibold">Notes</label>
                    <textarea name="notes" rows="4" class="form-control">${l.notes ?? ''}</textarea>
                </div>
                <div class="col-12">
                    <div class="d-flex gap-2 justify-content-end">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning" id="editLogSubmit">
                            <span class="submit-text"><i class="fa fa-save me-1"></i>Update Log</span>
                            <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                        </button>
                    </div>
                </div>
            </div>
        </form>`;

        editProdNet();
    })
    .catch(() => {
        body.innerHTML = `<div class="alert alert-danger">Failed to load log.</div>`;
    });
});

function editProdNet() {
    const qty = parseFloat(document.getElementById('ep_prod_qty')?.value) || 0;
    const dmg = parseFloat(document.getElementById('ep_prod_dmg')?.value) || 0;
    const net = document.getElementById('ep_prod_net');
    if (!net) return;
    if (dmg > qty) {
        net.textContent = '⚠ Damaged cannot exceed collected';
        net.style.color = '#ef4444';
    } else {
        net.textContent = `✅ Net: ${(qty - dmg).toFixed(2)}`;
        net.style.color = '#059669';
    }
}

// EDIT submit (delegated — form is injected dynamically)
document.addEventListener('submit', function (e) {
    if (!e.target.matches('#editLogForm')) return;
    e.preventDefault();

    const id  = e.target.dataset.id;
    const btn = document.getElementById('editLogSubmit');
    btn.querySelector('.submit-text').classList.add('d-none');
    btn.querySelector('.spinner-border').classList.remove('d-none');
    btn.disabled = true;

    const fd = new FormData(e.target);
    fd.append('_method', 'PUT');

    fetch(`/daily-logs/${id}`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' },
        body: fd
    })
    .then(r => { if (!r.ok) return r.json().then(d => Promise.reject(d)); return r.json(); })
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success', title: 'Updated!', text: data.message,
                timer: 2000, showConfirmButton: false, timerProgressBar: true
            }).then(() => reloadAfterModal('editLogModal'));
        } else {
            Swal.fire({ icon: 'error', title: 'Error!', text: data.message, confirmButtonColor: '#d33' });
            btn.querySelector('.submit-text').classList.remove('d-none');
            btn.querySelector('.spinner-border').classList.add('d-none');
            btn.disabled = false;
        }
    })
    .catch(err => {
        Swal.fire({ icon: 'error', title: 'Error!', text: err?.message ?? 'Something went wrong.', confirmButtonColor: '#d33' });
        btn.querySelector('.submit-text').classList.remove('d-none');
        btn.querySelector('.spinner-border').classList.add('d-none');
        btn.disabled = false;
    });
});

// ─── DELETE ───────────────────────────────────────────────────────────────────
document.addEventListener('click', function (e) {
    const btn = e.target.closest('.delete-log-btn');
    if (!btn) return;

    const id = btn.getAttribute('data-id');

    Swal.fire({
        title: 'Delete Daily Log?',
        html: 'This will <strong>restore mortality/culling counts</strong> to the flock.<br>This action cannot be undone!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fa fa-trash me-1"></i> Yes, Delete',
        cancelButtonText: 'Cancel'
    }).then(result => {
        if (!result.isConfirmed) return;

        Swal.fire({ title: 'Deleting…', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

        fetch(`/daily-logs/${id}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': CSRF,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-HTTP-Method-Override': 'DELETE'
            },
            body: JSON.stringify({ _method: 'DELETE' })
        })
        .then(r => { if (!r.ok) return r.json().then(d => Promise.reject(d)); return r.json(); })
        .then(data => {
            if (data.success) {
                removeTableRow(id);
                Swal.fire({
                    icon: 'success', title: 'Deleted!', text: data.message,
                    timer: 1500, showConfirmButton: false, timerProgressBar: true
                });
            } else {
                Swal.fire({ icon: 'error', title: 'Error!', text: data.message || 'Failed to delete.', confirmButtonColor: '#d33' });
            }
        })
        .catch(err => {
            Swal.fire({ icon: 'error', title: 'Error!', text: err?.message ?? 'Network error. Please try again.', confirmButtonColor: '#d33' });
        });
    });
});
</script>

@endsection