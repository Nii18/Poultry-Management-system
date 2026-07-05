{{-- resources/views/breeding-records/index.blade.php --}}
@extends('layouts.master')

@section('content')
<div class="container-fluid px-4">
    <!-- Page Header -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <div class="d-flex align-items-center gap-3">
                    <div class="page-icon">
                        <i class="fas fa-baby-carriage fs-1 text-primary"></i>
                    </div>
                    <div>
                        <h1 class="page-title mb-1">Breeding Records</h1>
                        <p class="page-description text-muted mb-0">Track breeding activities, pregnancy progress, and offspring management</p>
                    </div>
                </div>
            </div>
            <div class="col-auto">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Breeding Records</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-card-icon bg-primary-soft">
                        <i class="fas fa-heartbeat text-primary"></i>
                    </div>
                    <div class="stat-card-info">
                        <span class="stat-card-label">Total Breedings</span>
                        <h3 class="stat-card-value">{{ $totalBreedings }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-card-icon bg-warning-soft">
                        <i class="fas fa-clock text-warning"></i>
                    </div>
                    <div class="stat-card-info">
                        <span class="stat-card-label">Pending Deliveries</span>
                        <h3 class="stat-card-value">{{ $pendingCount }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-card-icon bg-success-soft">
                        <i class="fas fa-check-circle text-success"></i>
                    </div>
                    <div class="stat-card-info">
                        <span class="stat-card-label">Successful</span>
                        <h3 class="stat-card-value">{{ $successfulCount }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-card-icon bg-info-soft">
                        <i class="fas fa-baby text-info"></i>
                    </div>
                    <div class="stat-card-info">
                        <span class="stat-card-label">Total Offspring</span>
                        <h3 class="stat-card-value">{{ number_format($totalOffspring) }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Card -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-0 py-3">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="card-title mb-0 fw-semibold">
                        <i class="fas fa-list me-2 text-primary"></i>Breeding Records
                    </h5>
                </div>
                <div class="col-auto">
                    <a href="{{ route('breeding-records.pending') }}" class="btn btn-info me-2">
                        <i class="fas fa-clock me-2"></i>Pending Deliveries
                    </a>
                    <button type="button" class="btn btn-primary" id="newBreedingRecordBtn">
                        <i class="fas fa-plus me-2"></i>New Breeding Record
                    </button>
                </div>
            </div>
        </div>

        <div class="card-body">
            <!-- Filters -->
            <div class="filter-section mb-4 p-3 bg-light rounded-3">
                <div class="row align-items-end g-3">
                    <div class="col-md-5">
                        <label class="form-label fw-semibold mb-2 text-dark">
                            <i class="fas fa-paw me-1 text-muted"></i>Flock (Female)
                        </label>
                        <select name="flock_id" class="form-select" id="flockFilter">
                            <option value="">All Flocks</option>
                            @foreach($flocks as $flock)
                                <option value="{{ $flock->id }}" {{ request('flock_id') == $flock->id ? 'selected' : '' }}>
                                    {{ $flock->flock_number }} ({{ $flock->breed_variety }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label fw-semibold mb-2 text-dark">
                            <i class="fas fa-filter me-1 text-muted"></i>Status
                        </label>
                        <select name="status" class="form-select" id="statusFilter">
                            <option value="all" {{ request('status', 'all') == 'all' ? 'selected' : '' }}>All Records</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending Delivery</option>
                            <option value="successful" {{ request('status') == 'successful' ? 'selected' : '' }}>Successful</option>
                            <option value="unsuccessful" {{ request('status') == 'unsuccessful' ? 'selected' : '' }}>Unsuccessful</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-primary w-100" id="applyFilters">
                            <i class="fas fa-filter me-2"></i>Filter
                        </button>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="py-3">Female Flock</th>
                            <th class="py-3">Male Flock</th>
                            <th class="py-3">Breeders (F × M)</th>
                            <th class="py-3">Breeding Date</th>
                            <th class="py-3">Expected Delivery</th>
                            <th class="py-3">Actual Delivery</th>
                            <th class="py-3">Offspring</th>
                            <th class="py-3">Conception Rate</th>
                            <th class="py-3">Status</th>
                            <th class="py-3 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $record)
                        <tr>
                            <td>
                                <button type="button" class="btn btn-link p-0 text-primary fw-semibold text-decoration-none view-record-btn"
                                        data-id="{{ $record->id }}" data-bs-toggle="modal" data-bs-target="#viewBreedingModal">
                                    {{ $record->female->flock_number ?? 'N/A' }}
                                </button>
                                <br>
                                <small class="text-muted">{{ $record->female->breed_variety ?? 'N/A' }}</small>
                            </td>
                            <td>
                                @if($record->male)
                                    <strong>{{ $record->male->flock_number }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $record->male->breed_variety }}</small>
                                @else
                                    <span class="badge bg-info-soft text-info">
                                        <i class="fas fa-syringe me-1"></i>External / AI
                                    </span>
                                @endif
                            </td>
                            {{-- Breeder population snapshot --}}
                            <td>
                                <div class="d-flex align-items-center gap-1">
                                    <span class="badge bg-primary-soft text-primary">
                                        <i class="fas fa-venus me-1"></i>{{ $record->female_breeder_count ?? $record->female?->current_count ?? '?' }}
                                    </span>
                                    @if($record->mate_id)
                                        <span class="text-muted">×</span>
                                        <span class="badge bg-info-soft text-info">
                                            <i class="fas fa-mars me-1"></i>{{ $record->male_breeder_count ?? $record->male?->current_count ?? '?' }}
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-soft text-secondary">AI</span>
                                    @endif
                                </div>
                            </td>
                            <td>{{ $record->breeding_date->format('d M Y') }}</td>
                            <td>
                                {{ $record->expected_delivery_date->format('d M Y') }}
                                @if(!$record->actual_delivery_date && $record->expected_delivery_date > now())
                                    <br><small class="text-warning">({{ now()->diffInDays($record->expected_delivery_date) }} days left)</small>
                                @elseif(!$record->actual_delivery_date && $record->expected_delivery_date <= now())
                                    <br><small class="text-danger">(Overdue)</small>
                                @endif
                            </td>
                            <td>
                                @if($record->actual_delivery_date)
                                    {{ $record->actual_delivery_date->format('d M Y') }}
                                @else
                                    <span class="text-muted">Pending</span>
                                @endif
                            </td>
                            <td>
                                @if($record->offspring_count)
                                    <div class="d-flex flex-column">
                                        <span class="fw-semibold">{{ $record->offspring_count }} born</span>
                                        <small class="text-muted">Weaned: {{ $record->weaned_count ?? 0 }}</small>
                                        @php $opf = $record->offspring_per_female; @endphp
                                        @if($opf)
                                            <small class="text-info">{{ $opf }} per dam</small>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $cr = $record->conception_rate;
                                    $rateColor = $cr >= 80 ? 'success' : ($cr >= 50 ? 'warning' : 'danger');
                                @endphp
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height: 6px;">
                                        <div class="progress-bar bg-{{ $rateColor }}" style="width: {{ $cr }}%"></div>
                                    </div>
                                    <span class="small fw-semibold">{{ $cr }}%</span>
                                </div>
                            </td>
                            <td>
                                @php
                                    if ($record->is_successful) {
                                        $statusColor = 'success'; $statusText = 'Successful'; $statusIcon = 'fa-check-circle';
                                    } elseif ($record->actual_delivery_date && !$record->is_successful) {
                                        $statusColor = 'danger'; $statusText = 'Failed'; $statusIcon = 'fa-times-circle';
                                    } else {
                                        $statusColor = 'warning'; $statusText = 'Pending'; $statusIcon = 'fa-hourglass-half';
                                    }
                                @endphp
                                <span class="badge bg-{{ $statusColor }}-soft text-{{ $statusColor }} px-3 py-2 rounded-pill">
                                    <i class="fas {{ $statusIcon }} me-1" style="font-size: 8px;"></i>{{ $statusText }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group gap-1">
                                    <button type="button" class="btn btn-sm btn-outline-primary view-record-btn"
                                            data-id="{{ $record->id }}" data-bs-toggle="modal" data-bs-target="#viewBreedingModal">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                    @if(!$record->actual_delivery_date)
                                        <button type="button" class="btn btn-sm btn-outline-success record-delivery-btn"
                                                data-id="{{ $record->id }}" data-flock="{{ $record->female->flock_number ?? 'N/A' }}">
                                            <i class="fas fa-baby"></i> Delivery
                                        </button>
                                    @endif
                                    @if(auth()->user()->role === 'admin')
                                        <button type="button" class="btn btn-sm btn-outline-danger delete-record-btn"
                                                data-id="{{ $record->id }}" data-info="{{ $record->female->flock_number ?? 'N/A' }} - {{ $record->breeding_date->format('Y-m-d') }}">
                                            <i class="fas fa-trash-alt"></i> Delete
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-5">
                                <div class="empty-state">
                                    <i class="fas fa-heart-broken fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">No Breeding Records Found</h5>
                                    <p class="text-muted mb-3">Get started by creating your first breeding record</p>
                                    <button type="button" class="btn btn-primary" id="emptyStateNewBtn">
                                        <i class="fas fa-plus me-2"></i>New Breeding Record
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($records->hasPages())
            <div class="d-flex justify-content-between align-items-center mt-4 pt-2">
                <div class="text-muted small">
                    Showing {{ $records->firstItem() ?? 0 }} to {{ $records->lastItem() ?? 0 }} of {{ $records->total() }} results
                </div>
                {{ $records->withQueryString()->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════════════════
     CREATE MODAL
═══════════════════════════════════════════════════════════════════════════ -->
<div class="modal fade" id="createBreedingModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title text-white"><i class="fas fa-plus-circle me-2"></i>New Breeding Record</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="createBreedingContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2">Loading form...</p>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveCreateBreeding">Create Record</button>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════════════════
     VIEW MODAL
═══════════════════════════════════════════════════════════════════════════ -->
<div class="modal fade" id="viewBreedingModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title text-white"><i class="fas fa-eye me-2"></i>Breeding Record Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewBreedingContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2">Loading...</p>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════════════════
     DELIVERY MODAL
═══════════════════════════════════════════════════════════════════════════ -->
<div class="modal fade" id="recordDeliveryModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-success text-white border-0">
                <h5 class="modal-title text-white"><i class="fas fa-baby me-2"></i>Record Delivery</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form id="recordDeliveryForm">
                    @csrf
                    <div class="alert alert-info mb-3">
                        <i class="fas fa-info-circle me-2"></i>
                        Recording delivery for flock: <strong id="deliveryFlockName"></strong>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Actual Delivery Date <span class="text-danger">*</span></label>
                        <input type="date" name="actual_delivery_date" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Offspring Count <span class="text-danger">*</span></label>
                            <input type="number" name="offspring_count" class="form-control" min="0" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Stillborn Count</label>
                            <input type="number" name="stillborn_count" class="form-control" min="0" value="0">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Weaned Count</label>
                            <input type="number" name="weaned_count" class="form-control" min="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Status</label>
                            <select name="is_successful" class="form-select" required>
                                <option value="1">Successful</option>
                                <option value="0">Unsuccessful</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 pt-0 pb-4 px-4">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success px-4" id="submitDeliveryBtn">
                    <i class="fas fa-save me-1"></i>Save Delivery
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Hidden delete form -->
<form id="deleteBreedingForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

@push('styles')
<style>
    .page-header { margin-bottom: 1.5rem; }
    .page-icon { width:50px;height:50px;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#e8f4f8,#d1e9f0);border-radius:12px; }
    .page-title { font-size:1.75rem;font-weight:600;color:#1e293b; }
    .stat-card { background:white;border-radius:16px;padding:1rem;transition:all .3s;border:1px solid #e2e8f0; }
    .stat-card:hover { transform:translateY(-2px);box-shadow:0 8px 25px rgba(0,0,0,.05); }
    .stat-card-body { display:flex;align-items:center;gap:1rem; }
    .stat-card-icon { width:48px;height:48px;display:flex;align-items:center;justify-content:center;border-radius:12px;font-size:1.5rem; }
    .stat-card-label { font-size:.75rem;text-transform:uppercase;letter-spacing:.5px;color:#64748b;font-weight:600; }
    .stat-card-value { font-size:1.75rem;font-weight:700;margin:0;line-height:1.2;color:#1e293b; }
    .bg-primary-soft   { background:#e0f2fe; }
    .bg-success-soft   { background:#dcfce7; }
    .bg-warning-soft   { background:#fef3c7; }
    .bg-info-soft      { background:#d1fae5; }
    .bg-danger-soft    { background:#fee2e2; }
    .bg-secondary-soft { background:#f1f5f9; }
    .text-primary { color:#0d6e4f !important; }
    .text-success { color:#10b981 !important; }
    .text-info    { color:#3b82f6 !important; }
    .text-warning { color:#f59e0b !important; }
    .text-danger  { color:#dc2626 !important; }
    .filter-section { background:#f8fafc;border-radius:12px; }
    .table th { font-weight:600;font-size:.875rem;color:#475569;border-bottom-width:1px; }
    .table td { font-size:.875rem;color:#334155;vertical-align:middle; }
    .badge { font-weight:500;font-size:.75rem; }
    .empty-state { text-align:center;padding:2rem; }
    .progress { background:#e2e8f0;border-radius:10px;overflow:hidden; }
    .btn-group .btn { border-radius:8px !important;margin:0 2px;padding:.25rem .5rem; }
    .pagination { margin-bottom:0; }
    .page-link { border-radius:8px;margin:0 2px;border:none;color:#475569;padding:.5rem .875rem; }
    .page-item.active .page-link { background:#0d6e4f;color:white; }
    .page-link:hover { background:#e2e8f0;color:#0d6e4f; }
    .modal-body { padding:1.5rem;max-height:70vh;overflow-y:auto; }
    .detail-section { margin-bottom:1.5rem; }
    .detail-section h6 { font-weight:600;color:#1e293b;margin-bottom:1rem;padding-bottom:.5rem;border-bottom:2px solid #e2e8f0; }
    .detail-grid { display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1rem; }
    .detail-item { display:flex;flex-direction:column; }
    .detail-label { font-size:.7rem;text-transform:uppercase;color:#64748b;font-weight:600;margin-bottom:.25rem; }
    .detail-value { font-size:1rem;font-weight:500;color:#1e293b; }
    .stats-card { background:#f8fafc;border-radius:12px;padding:1rem;border:1px solid #e2e8f0; }
    .stats-icon { width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;margin:0 auto .5rem; }
    .stats-number { font-size:1.5rem;font-weight:700;color:#1e293b; }
    .stats-label { font-size:.7rem;text-transform:uppercase;letter-spacing:.5px;color:#64748b;font-weight:600; }
    .offspring-card { background:#f8fafc;border-radius:10px;padding:.75rem;border-left:3px solid #0d6e4f; }
    /* Breeder info box shown in create form */
    .breeder-info-box          { background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:.75rem 1rem;font-size:.875rem; }
    .breeder-info-box.mode-full{ background:#eff6ff;border-color:#bfdbfe; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Helpers ──────────────────────────────────────────────────────────────
    function escapeHtml(str) {
        if (!str) return '';
        return String(str).replace(/[&<>]/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;' }[m]));
    }

    function closeAllModals() {
        document.querySelectorAll('.modal.show').forEach(modal => {
            bootstrap.Modal.getInstance(modal)?.hide();
        });
        document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
        document.body.classList.remove('modal-open');
        document.body.style.overflow    = '';
        document.body.style.paddingRight = '';
    }

    function cleanupModal(modalId, instanceRef) {
        document.getElementById(modalId)?.addEventListener('hidden.bs.modal', function () {
            instanceRef.current?.dispose();
            instanceRef.current = null;
            document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
            document.body.classList.remove('modal-open');
            document.body.style.overflow    = '';
            document.body.style.paddingRight = '';
        });
    }

    // ── Filters ──────────────────────────────────────────────────────────────
    document.getElementById('applyFilters')?.addEventListener('click', function () {
        const params  = new URLSearchParams();
        const flockId = document.getElementById('flockFilter').value;
        const status  = document.getElementById('statusFilter').value;
        if (flockId) params.append('flock_id', flockId);
        if (status && status !== 'all') params.append('status', status);
        window.location.href = '{{ route("breeding-records.index") }}' + (params.toString() ? '?' + params.toString() : '');
    });

    // ════════════════════════════════════════════════════════════════════════
    // CREATE MODAL
    // ════════════════════════════════════════════════════════════════════════
    const createRef = { current: null };
    cleanupModal('createBreedingModal', createRef);

    function openCreateBreedingModal() {
        closeAllModals();
        const modalEl = document.getElementById('createBreedingModal');
        createRef.current = new bootstrap.Modal(modalEl, { backdrop: 'static', keyboard: false });
        document.getElementById('createBreedingContent').innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-2">Loading form...</p>
            </div>`;
        createRef.current.show();

        fetch('{{ route("breeding-records.create-form") }}', {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                'Accept': 'application/json'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                buildCreateForm(data.female_flocks, data.male_flocks);
            } else {
                document.getElementById('createBreedingContent').innerHTML =
                    `<div class="alert alert-danger m-3">Failed to load form: ${escapeHtml(data.message)}</div>`;
            }
        })
        .catch(err => {
            document.getElementById('createBreedingContent').innerHTML =
                `<div class="alert alert-danger m-3">Error: ${escapeHtml(err.message)}</div>`;
        });
    }

    // ── Breeder info box ─────────────────────────────────────────────────────
    // FIX: reads effective_count and breeder_mode (matching getCreateForm() JSON keys)
    // FIX: mode comparison uses actual API values ('breeder_subset' / 'whole_flock')
    function buildBreederInfoHtml(flock, sex) {
        if (!flock) return '';

        const icon  = sex === 'female' ? 'fa-venus text-primary' : 'fa-mars text-info';
        const count = flock.effective_count;   // ← was flock.effective_breeders
        const mode  = flock.breeder_mode;      // ← was flock.population_mode

        let boxClass = 'breeder-info-box';
        let label    = '';

        if (mode === 'breeder_subset') {
            // has a designated breeder subset — green box (default)
            label = `<i class="fas fa-check-circle text-success me-1"></i>Designated breeders`;
        } else {
            // whole_flock fallback — blue box
            boxClass += ' mode-full';
            label = `<i class="fas fa-info-circle text-primary me-1"></i>Whole flock (no breeder subset set)`;
        }

        return `
            <div class="${boxClass} mt-2">
                <i class="fas ${icon} me-1"></i>
                <strong>${escapeHtml(String(count))}</strong> animals will be recorded as breeders
                <br><small>${label}</small>
            </div>`;
    }

    // ── Build create form HTML ────────────────────────────────────────────────
    // FIX: data-effective and data-mode use correct API keys (effective_count / breeder_mode)
    function buildCreateForm(femaleFlocks, maleFlocks) {

        const femaleOptions = femaleFlocks.map(f =>
            `<option value="${f.id}"
                data-gestation="${f.gestation_days  || 0}"
                data-effective="${f.effective_count}"
                data-mode="${escapeHtml(f.breeder_mode)}"
                data-current="${f.current_count}"
                data-breeders="${f.breeder_count}">
                ${escapeHtml(f.flock_number)} — ${escapeHtml(f.breed_variety)} (${escapeHtml(f.species_name)})
            </option>`
        ).join('');

        const maleOptions = maleFlocks.map(f =>
            `<option value="${f.id}"
                data-effective="${f.effective_count}"
                data-mode="${escapeHtml(f.breeder_mode)}"
                data-current="${f.current_count}"
                data-breeders="${f.breeder_count}">
                ${escapeHtml(f.flock_number)} — ${escapeHtml(f.breed_variety)}
            </option>`
        ).join('');

        document.getElementById('createBreedingContent').innerHTML = `
            <form id="createBreedingForm">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Female Flock (Dam) <span class="text-danger">*</span></label>
                        <select name="flock_id" class="form-select" required id="femaleFlockSelect">
                            <option value="">Select Female Flock</option>
                            ${femaleOptions}
                        </select>
                        <div id="femaleBreederInfo"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Male Flock (Sire)</label>
                        <select name="mate_id" class="form-select" id="maleFlockSelect">
                            <option value="">Select Male Flock (or leave for AI)</option>
                            ${maleOptions}
                        </select>
                        <small class="text-muted">Leave empty for Artificial Insemination</small>
                        <div id="maleBreederInfo"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Breeding Date <span class="text-danger">*</span></label>
                        <input type="date" name="breeding_date" class="form-control"
                               value="${new Date().toISOString().split('T')[0]}" required id="breedingDateInput">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Expected Delivery Date <span class="text-danger">*</span></label>
                        <input type="date" name="expected_delivery_date" class="form-control" required id="expectedDeliveryInput">
                        <small class="text-muted">Auto-calculated from species gestation period</small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Breeding Method <span class="text-danger">*</span></label>
                        <select name="breeding_method" class="form-select" required id="breedingMethodSelect">
                            <option value="natural">Natural</option>
                            <option value="artificial_insemination">Artificial Insemination</option>
                        </select>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label fw-semibold">Notes</label>
                        <textarea name="notes" class="form-control" rows="3"
                                  placeholder="Any additional notes..."></textarea>
                    </div>
                </div>
            </form>`;

        const femaleSelect = document.getElementById('femaleFlockSelect');
        const maleSelect   = document.getElementById('maleFlockSelect');
        const breedingDate = document.getElementById('breedingDateInput');
        const expectedDate = document.getElementById('expectedDeliveryInput');
        const methodSelect = document.getElementById('breedingMethodSelect');

        // Read the dataset from the selected option and pass as a plain object
        // with the keys buildBreederInfoHtml() expects.
        function flockFromOption(opt) {
            if (!opt?.value) return null;
            return {
                effective_count: opt.dataset.effective,
                breeder_mode:    opt.dataset.mode,
                current_count:   opt.dataset.current,
                breeder_count:   opt.dataset.breeders,
            };
        }

        function updateFemaleInfo() {
            const opt = femaleSelect.options[femaleSelect.selectedIndex];
            document.getElementById('femaleBreederInfo').innerHTML =
                buildBreederInfoHtml(flockFromOption(opt), 'female');
        }

        function updateMaleInfo() {
            const opt = maleSelect.options[maleSelect.selectedIndex];
            document.getElementById('maleBreederInfo').innerHTML =
                buildBreederInfoHtml(flockFromOption(opt), 'male');
        }

        function autoSwitchMethod() {
            methodSelect.value = maleSelect.value
                ? 'natural'
                : 'artificial_insemination';
        }

        function calcExpectedDate() {
            const opt      = femaleSelect.options[femaleSelect.selectedIndex];
            const gestation = parseInt(opt?.dataset.gestation || 0);
            const dateVal   = breedingDate.value;
            if (gestation > 0 && dateVal) {
                const d = new Date(dateVal);
                d.setDate(d.getDate() + gestation);
                expectedDate.value = d.toISOString().split('T')[0];
            }
        }

        femaleSelect.addEventListener('change', () => { updateFemaleInfo(); calcExpectedDate(); });
        maleSelect.addEventListener('change',   () => { updateMaleInfo();   autoSwitchMethod(); });
        breedingDate.addEventListener('change', calcExpectedDate);
    }

    // ── Save create form ─────────────────────────────────────────────────────
    document.getElementById('saveCreateBreeding')?.addEventListener('click', function () {
        const form = document.getElementById('createBreedingForm');
        if (!form) return;

        const data = {};
        new FormData(form).forEach((v, k) => { data[k] = v; });

        if (!data.flock_id || !data.breeding_date || !data.expected_delivery_date || !data.breeding_method) {
            Swal.fire({ icon: 'error', title: 'Validation Error', text: 'Please fill in all required fields' });
            return;
        }

        const btn = this;
        btn.disabled    = true;
        btn.innerHTML   = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';

        fetch('{{ route("breeding-records.store-ajax") }}', {
            method:  'POST',
            headers: {
                'X-CSRF-TOKEN':  document.querySelector('meta[name="csrf-token"]')?.content,
                'Accept':        'application/json',
                'Content-Type':  'application/json',
            },
            body: JSON.stringify(data),
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                createRef.current?.hide();
                Swal.fire({
                    icon: 'success',
                    title: 'Created!',
                    html: `Breeding record created.<br>
                           <small>Female breeders: <strong>${res.record.female_breeder_count}</strong>
                           &nbsp;|&nbsp;
                           Male breeders: <strong>${res.record.male_breeder_count ?? 'AI'}</strong></small>`,
                    timer: 2500,
                    showConfirmButton: false,
                }).then(() => window.location.reload());
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: res.message || 'Failed to create record' });
                btn.disabled  = false;
                btn.innerHTML = 'Create Record';
            }
        })
        .catch(() => {
            Swal.fire({ icon: 'error', title: 'Error', text: 'An error occurred' });
            btn.disabled  = false;
            btn.innerHTML = 'Create Record';
        });
    });

    document.getElementById('newBreedingRecordBtn')?.addEventListener('click', openCreateBreedingModal);
    document.getElementById('emptyStateNewBtn')?.addEventListener('click', openCreateBreedingModal);

    // ════════════════════════════════════════════════════════════════════════
    // VIEW MODAL
    // ════════════════════════════════════════════════════════════════════════
    const viewRef = { current: null };
    cleanupModal('viewBreedingModal', viewRef);

    document.querySelectorAll('.view-record-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            closeAllModals();
            const id      = this.dataset.id;
            const modalEl = document.getElementById('viewBreedingModal');
            viewRef.current = new bootstrap.Modal(modalEl, { backdrop: 'static', keyboard: true });
            document.getElementById('viewBreedingContent').innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2">Loading...</p>
                </div>`;
            viewRef.current.show();

            fetch(`/breeding-records/${id}/details-json`, {
                headers: {
                    'Accept':        'application/json',
                    'X-CSRF-TOKEN':  document.querySelector('meta[name="csrf-token"]')?.content,
                }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) buildViewContent(data.record);
                else document.getElementById('viewBreedingContent').innerHTML =
                    `<div class="alert alert-danger m-3">Failed: ${escapeHtml(data.message)}</div>`;
            })
            .catch(err => {
                document.getElementById('viewBreedingContent').innerHTML =
                    `<div class="alert alert-danger m-3">Error: ${escapeHtml(err.message)}</div>`;
            });
        });
    });

    function buildViewContent(r) {
        const statusClass = r.is_successful ? 'success' : (r.actual_delivery_date ? 'danger' : 'warning');
        const statusText  = r.is_successful ? 'Successful' : (r.actual_delivery_date ? 'Failed' : 'Pending');

        const maleBreederHtml = r.male_breeder_count !== null && r.male_breeder_count !== undefined
            ? `<span class="badge bg-info-soft text-info ms-2">
                   <i class="fas fa-mars me-1"></i>${r.male_breeder_count} male breeders
               </span>`
            : `<span class="badge bg-secondary-soft text-secondary ms-2">AI — no male flock</span>`;

        const analyticsExtra = r.actual_delivery_date ? `
            <div class="col-md-4">
                <div class="stats-card text-center">
                    <div class="stats-icon bg-primary-soft"><i class="fas fa-female text-primary"></i></div>
                    <div class="stats-number">${r.offspring_per_female ?? '—'}</div>
                    <div class="stats-label">Offspring / Dam</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card text-center">
                    <div class="stats-icon bg-info-soft"><i class="fas fa-male text-info"></i></div>
                    <div class="stats-number">${r.offspring_per_male ?? '—'}</div>
                    <div class="stats-label">Offspring / Sire</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card text-center">
                    <div class="stats-icon bg-warning-soft"><i class="fas fa-balance-scale text-warning"></i></div>
                    <div class="stats-number">${r.male_to_female_ratio ? '1 : ' + Math.round(1 / r.male_to_female_ratio) : '—'}</div>
                    <div class="stats-label">Sire : Dam Ratio</div>
                </div>
            </div>` : '';

        const offspringHtml = r.offspring_records?.length ? `
            <div class="detail-section">
                <h6><i class="fas fa-paw me-2"></i>Offspring Flocks</h6>
                <div class="offspring-list">
                    ${r.offspring_records.map(o => `
                        <div class="offspring-card mb-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>${escapeHtml(o.flock_number)}</strong>
                                    <span class="badge bg-info ms-2">${o.count} animals</span>
                                </div>
                                <small class="text-muted">${o.ear_tag_range !== 'N/A' ? 'Tags: ' + escapeHtml(o.ear_tag_range) : ''}</small>
                            </div>
                            ${o.avg_birth_weight ? `<small class="text-muted d-block mt-1">Avg Birth Weight: ${o.avg_birth_weight} kg</small>` : ''}
                        </div>`).join('')}
                </div>
            </div>` : '';

        document.getElementById('viewBreedingContent').innerHTML = `
            <div class="detail-section">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h5 class="mb-1">
                            ${escapeHtml(r.female_flock_number)}
                            <i class="fas fa-times mx-2 text-muted"></i>
                            ${escapeHtml(r.male_flock_number)}
                        </h5>
                        <p class="text-muted mb-1">
                            ${escapeHtml(r.female_breed || '')}
                            ${r.male_flock_number !== 'External / AI' && r.male_breed ? '× ' + escapeHtml(r.male_breed) : ''}
                        </p>
                        <div class="d-flex align-items-center flex-wrap gap-1 mt-1">
                            <span class="badge bg-primary-soft text-primary">
                                <i class="fas fa-venus me-1"></i>${r.female_breeder_count} female breeders
                            </span>
                            ${maleBreederHtml}
                        </div>
                    </div>
                    <span class="badge bg-${statusClass}-soft text-${statusClass} px-3 py-2 rounded-pill">
                        <i class="fas fa-circle me-1" style="font-size:8px"></i>${statusText}
                    </span>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="stats-card text-center">
                        <div class="stats-icon bg-primary-soft"><i class="fas fa-chart-line text-primary"></i></div>
                        <div class="stats-number">${r.conception_rate ?? 0}%</div>
                        <div class="stats-label">Conception Rate</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stats-card text-center">
                        <div class="stats-icon bg-success-soft"><i class="fas fa-heartbeat text-success"></i></div>
                        <div class="stats-number">${r.live_birth_rate ?? 0}%</div>
                        <div class="stats-label">Live Birth Rate</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stats-card text-center">
                        <div class="stats-icon bg-info-soft"><i class="fas fa-baby text-info"></i></div>
                        <div class="stats-number">${r.weaning_rate ?? 0}%</div>
                        <div class="stats-label">Weaning Rate</div>
                    </div>
                </div>
                ${analyticsExtra}
            </div>

            <div class="detail-section">
                <h6><i class="fas fa-info-circle me-2"></i>Breeding Information</h6>
                <div class="detail-grid">
                    <div class="detail-item">
                        <span class="detail-label">Breeding Date</span>
                        <span class="detail-value">${r.breeding_date ?? 'N/A'}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Expected Delivery</span>
                        <span class="detail-value">${r.expected_delivery_date ?? 'N/A'}</span>
                    </div>
                    ${r.actual_delivery_date ? `
                    <div class="detail-item">
                        <span class="detail-label">Actual Delivery</span>
                        <span class="detail-value">${r.actual_delivery_date}</span>
                    </div>` : ''}
                    <div class="detail-item">
                        <span class="detail-label">Breeding Method</span>
                        <span class="detail-value">${escapeHtml(r.breeding_method ?? 'N/A')}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Recorded By</span>
                        <span class="detail-value">${escapeHtml(r.recorded_by ?? 'N/A')}</span>
                    </div>
                </div>
            </div>

            ${r.actual_delivery_date && r.offspring_count !== undefined ? `
            <div class="detail-section">
                <h6><i class="fas fa-baby-carriage me-2"></i>Delivery Information</h6>
                <div class="detail-grid">
                    <div class="detail-item">
                        <span class="detail-label">Total Offspring</span>
                        <span class="detail-value">${r.offspring_count ?? 0}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Stillborn</span>
                        <span class="detail-value">${r.stillborn_count ?? 0}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Live Births</span>
                        <span class="detail-value">${(r.offspring_count ?? 0) - (r.stillborn_count ?? 0)}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Weaned</span>
                        <span class="detail-value">${r.weaned_count ?? 0}</span>
                    </div>
                </div>
            </div>` : ''}

            ${offspringHtml}

            ${r.notes ? `
            <div class="detail-section">
                <h6><i class="fas fa-pencil-alt me-2"></i>Notes</h6>
                <p class="mb-0 p-3 bg-secondary rounded">${escapeHtml(r.notes)}</p>
            </div>` : ''}
        `;
    }

    // ════════════════════════════════════════════════════════════════════════
    // DELIVERY MODAL
    // ════════════════════════════════════════════════════════════════════════
    const deliveryRef     = { current: null };
    let currentDeliveryId = null;
    cleanupModal('recordDeliveryModal', deliveryRef);

    document.querySelectorAll('.record-delivery-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            closeAllModals();
            currentDeliveryId = this.dataset.id;
            const modalEl     = document.getElementById('recordDeliveryModal');
            deliveryRef.current = new bootstrap.Modal(modalEl, { backdrop: 'static', keyboard: true });
            const form = document.getElementById('recordDeliveryForm');
            form.reset();
            form.querySelector('input[name="actual_delivery_date"]').value =
                new Date().toISOString().split('T')[0];
            document.getElementById('deliveryFlockName').innerText = this.dataset.flock;
            deliveryRef.current.show();
        });
    });

    document.getElementById('submitDeliveryBtn')?.addEventListener('click', function () {
        const form     = document.getElementById('recordDeliveryForm');
        const formData = new FormData(form);
        const btn      = this;
        const origText = btn.innerHTML;

        if (!form.querySelector('input[name="offspring_count"]').value) {
            Swal.fire({ icon: 'error', title: 'Validation Error', text: 'Please enter offspring count' });
            return;
        }

        btn.disabled  = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';

        fetch(`/breeding-records/${currentDeliveryId}/record-delivery-ajax`, {
            method:  'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                'Accept':       'application/json',
            },
            body: formData,
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                deliveryRef.current?.hide();
                Swal.fire({
                    icon: 'success', title: 'Success!', text: 'Delivery recorded',
                    timer: 1500, showConfirmButton: false,
                }).then(() => window.location.reload());
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: res.message || 'Failed' });
                btn.disabled  = false;
                btn.innerHTML = origText;
            }
        })
        .catch(() => {
            Swal.fire({ icon: 'error', title: 'Error', text: 'An error occurred' });
            btn.disabled  = false;
            btn.innerHTML = origText;
        });
    });

    // ════════════════════════════════════════════════════════════════════════
    // DELETE
    // ════════════════════════════════════════════════════════════════════════
    document.querySelectorAll('.delete-record-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const id   = this.dataset.id;
            const info = this.dataset.info;
            Swal.fire({
                title: 'Delete Breeding Record',
                text:  `Delete record for "${info}"?`,
                icon:  'warning',
                showCancelButton:   true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor:  '#6c757d',
                confirmButtonText:  'Yes, delete it!',
            }).then(result => {
                if (result.isConfirmed) {
                    const form = document.getElementById('deleteBreedingForm');
                    form.action = `/breeding-records/${id}`;
                    form.submit();
                }
            });
        });
    });

    // Clean up any lingering backdrops on load / back-navigation
    document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
    window.addEventListener('pageshow', () => {
        document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
    });
});
</script>
@endpush
@endsection