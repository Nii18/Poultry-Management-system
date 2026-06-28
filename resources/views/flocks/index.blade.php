{{-- resources/views/flocks/index.blade.php --}}
@extends('layouts.master')

@section('content')
<div class="container-fluid px-4">

    {{-- ── Page Header ──────────────────────────────────────────────────────── --}}
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <div class="d-flex align-items-center gap-3">
                    <div class="page-icon">
                        <i class="fas fa-tractor fs-1 text-primary"></i>
                    </div>
                    <div>
                        <h1 class="page-title mb-1">Flocks Management</h1>
                        <p class="page-description text-muted mb-0">Manage and monitor all your animal groups</p>
                    </div>
                </div>
            </div>
            <div class="col-auto">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Flocks</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    {{-- ── Stat Cards ───────────────────────────────────────────────────────── --}}
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-card-icon bg-primary-soft">
                        <i class="fas fa-users text-primary"></i>
                    </div>
                    <div class="stat-card-info">
                        <span class="stat-card-label">Total Flocks</span>
                        <h3 class="stat-card-value">{{ $flocks->total() }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-card-icon bg-success-soft">
                        <i class="fas fa-play-circle text-success"></i>
                    </div>
                    <div class="stat-card-info">
                        <span class="stat-card-label">Active Flocks</span>
                        <h3 class="stat-card-value">{{ $flocks->getCollection()->where('status','active')->count() }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-card-icon bg-warning-soft">
                        <i class="fas fa-paw text-warning"></i>
                    </div>
                    <div class="stat-card-info">
                        <span class="stat-card-label">Total Animals</span>
                        <h3 class="stat-card-value">{{ number_format($totalAnimals) }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-card-icon bg-info-soft">
                        <i class="fas fa-chart-line text-info"></i>
                    </div>
                    <div class="stat-card-info">
                        <span class="stat-card-label">Avg Mortality Rate</span>
                        <h3 class="stat-card-value">
                            {{ number_format($flocks->getCollection()->avg(fn($f) => $f->mortality_rate), 1) }}%
                        </h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Main Card ────────────────────────────────────────────────────────── --}}
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-0 py-3">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="card-title mb-0 fw-semibold">
                        <i class="fas fa-list me-2 text-primary"></i>Flock Records
                    </h5>
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-primary"
                            data-bs-toggle="modal" data-bs-target="#createFlockModal">
                        <i class="fas fa-plus me-2"></i>Create New Flock
                    </button>
                </div>
            </div>
        </div>

        <div class="card-body">

            {{-- Filters --}}
            <div class="filter-section mb-4 p-3 bg-light rounded-3">
                <div class="row align-items-end g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold text-dark mb-2">
                            <i class="fas fa-paw me-1 text-muted"></i>Species
                        </label>
                        <select class="form-select" id="speciesFilter">
                            <option value="">All Species</option>
                            @foreach($species as $spec)
                                <option value="{{ $spec->id }}" {{ request('species_id') == $spec->id ? 'selected' : '' }}>
                                    {{ $spec->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold text-dark mb-2">
                            <i class="fas fa-flag-checkered me-1 text-muted"></i>Status
                        </label>
                        <select class="form-select" id="statusFilter">
                            <option value="active"      {{ request('status','active') == 'active'      ? 'selected' : '' }}>Active</option>
                            <option value="closed"      {{ request('status') == 'closed'               ? 'selected' : '' }}>Closed</option>
                            <option value="quarantined" {{ request('status') == 'quarantined'          ? 'selected' : '' }}>Quarantined</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-primary px-4" id="applyFilters">
                                <i class="fas fa-filter me-2"></i>Apply Filters
                            </button>
                            <a href="{{ route('flocks.index') }}" class="btn btn-outline-secondary px-4">
                                <i class="fas fa-redo-alt me-2"></i>Reset
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Table --}}
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="py-3">Flock #</th>
                            <th class="py-3">Species</th>
                            <th class="py-3">House</th>
                            <th class="py-3">Breed</th>
                            <th class="py-3">Sex</th>
                            <th class="py-3">Age</th>
                            <th class="py-3">
                                Population
                            </th>
                            <th class="py-3">Breeders</th>
                            <th class="py-3">Sellable</th>
                            <th class="py-3">Mortality %</th>
                            <th class="py-3">Status</th>
                            <th class="py-3 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($flocks as $flock)
                        @php
                            $currentCount  = $flock->current_count;
                            $initialCount  = $flock->initial_count;
                            $totalLost     = $flock->total_mortality;
                            $mortalityRate = $flock->mortality_rate;
                            $breederCount  = $flock->breeder_count;
                            $sellableCount = $flock->sellable_count;
                            $sellablePct   = ($currentCount > 0 && $breederCount > 0)
                                ? round(($sellableCount / $currentCount) * 100) : 0;
                            $survivalPct   = $initialCount > 0
                                ? min(100, round(($currentCount / $initialCount) * 100)) : 0;
                        @endphp
                        <tr>
                            {{-- Flock number --}}
                            <td>
                                <button type="button"
                                        class="btn btn-link p-0 text-primary fw-semibold text-decoration-none view-flock-btn"
                                        data-id="{{ $flock->id }}"
                                        data-bs-toggle="modal" data-bs-target="#viewFlockModal">
                                    {{ $flock->flock_number }}
                                </button>
                            </td>

                            <td>{{ $flock->species->name ?? 'N/A' }}</td>
                            <td>{{ $flock->house->name  ?? 'N/A' }}</td>
                            <td>{{ $flock->breed_variety }}</td>

                            {{-- Sex --}}
                            <td>
                                @if($flock->sex === 'female')
                                    <span class="sex-badge sex-female"><i class="fas fa-venus me-1"></i>Female</span>
                                @elseif($flock->sex === 'male')
                                    <span class="sex-badge sex-male"><i class="fas fa-mars me-1"></i>Male</span>
                                @else
                                    <span class="text-muted small fst-italic">Not set</span>
                                @endif
                            </td>

                            {{-- Age --}}
                            <td>
                                <span class="fw-semibold">{{ $flock->age_in_days }}</span><small class="text-muted">d</small>
                                <span class="text-muted mx-1">/</span>
                                <span class="fw-semibold">{{ $flock->age_in_weeks }}</span><small class="text-muted">w</small>
                            </td>

                            {{-- Population --}}
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-bold text-dark">{{ number_format($currentCount) }}</span>
                                    <small class="text-muted">of {{ number_format($initialCount) }}</small>
                                    @if($initialCount > 0)
                                    <div class="progress mt-1" style="height:3px;width:70px;">
                                        <div class="progress-bar bg-primary"
                                             style="width:{{ $survivalPct }}%;border-radius:10px;"></div>
                                    </div>
                                    @endif
                                </div>
                            </td>

                            {{-- Breeders --}}
                            <td>
                                @if($breederCount > 0)
                                    <span class="breeder-badge">
                                        <i class="fas fa-heart me-1"></i>{{ number_format($breederCount) }}
                                    </span>
                                @else
                                    <span class="text-muted small fst-italic">Not set</span>
                                @endif
                            </td>

                            {{-- Sellable --}}
                            <td>
                                @if($breederCount > 0)
                                    <div class="d-flex flex-column">
                                        <span class="fw-semibold text-success">{{ number_format($sellableCount) }}</span>
                                        <div class="progress mt-1" style="height:4px;width:70px;">
                                            <div class="progress-bar bg-success"
                                                 style="width:{{ $sellablePct }}%;border-radius:10px;"></div>
                                        </div>
                                        <small class="text-muted">{{ $sellablePct }}% of flock</small>
                                    </div>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>

                            {{-- Mortality % --}}
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height:6px;min-width:50px;">
                                        <div class="progress-bar bg-{{ $mortalityRate > 5 ? 'danger' : 'success' }}"
                                             style="width:{{ min(100,$mortalityRate) }}%"></div>
                                    </div>
                                    <span class="small fw-semibold {{ $mortalityRate > 5 ? 'text-danger' : 'text-success' }}">
                                        {{ number_format($mortalityRate,1) }}%
                                    </span>
                                </div>
                            </td>

                            {{-- Status --}}
                            <td>
                                @php
                                    $sc = ['active'=>'success','closed'=>'secondary','quarantined'=>'danger','breeding'=>'info'][$flock->status] ?? 'secondary';
                                @endphp
                                <span class="badge bg-{{ $sc }}-soft text-{{ $sc }} px-3 py-2 rounded-pill">
                                    <i class="fas fa-circle me-1" style="font-size:8px;"></i>
                                    {{ ucfirst($flock->status) }}
                                </span>
                            </td>

                            {{-- Actions --}}
                            <td>
                                <div class="d-flex gap-1 flex-wrap">
                                    <button type="button"
                                            class="btn btn-sm btn-outline-primary view-flock-btn"
                                            data-id="{{ $flock->id }}"
                                            data-bs-toggle="modal" data-bs-target="#viewFlockModal"
                                            title="View Details">
                                        <i class="fas fa-eye"></i> View
                                    </button>

                                    @if($flock->status === 'active')
                                        <button type="button"
                                                class="btn btn-sm btn-outline-warning edit-flock-btn"
                                                data-id="{{ $flock->id }}"
                                                data-bs-toggle="modal" data-bs-target="#editFlockModal"
                                                title="Edit">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>

                                        @if(in_array(auth()->user()->role ?? '', ['admin','manager']))
                                        <button type="button"
                                                class="btn btn-sm btn-outline-breeder set-breeders-btn"
                                                data-id="{{ $flock->id }}"
                                                data-flock-number="{{ $flock->flock_number }}"
                                                data-current-count="{{ $currentCount }}"
                                                title="Manage Breeders">
                                            <i class="fas fa-heart"></i> Breeders
                                        </button>
                                        @endif

                                        <button type="button"
                                                class="btn btn-sm btn-outline-success close-flock-btn"
                                                data-id="{{ $flock->id }}"
                                                data-flock-number="{{ $flock->flock_number }}"
                                                data-initial-count="{{ $initialCount }}"
                                                title="Close Flock">
                                            <i class="fas fa-check-circle"></i> Close
                                        </button>
                                    @endif

                                    @if($flock->status === 'closed' && auth()->user()->role === 'admin')
                                        <button type="button"
                                                class="btn btn-sm btn-outline-danger delete-flock-btn"
                                                data-id="{{ $flock->id }}"
                                                data-flock-number="{{ $flock->flock_number }}"
                                                title="Delete Flock">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="12" class="text-center py-5">
                                <div class="empty-state">
                                    <i class="fas fa-tractor fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">No Flocks Found</h5>
                                    <p class="text-muted mb-3">Get started by creating your first flock</p>
                                    <button type="button" class="btn btn-primary"
                                            data-bs-toggle="modal" data-bs-target="#createFlockModal">
                                        <i class="fas fa-plus me-2"></i>Create New Flock
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($flocks->hasPages())
            <div class="d-flex justify-content-between align-items-center mt-4">
                <div class="text-muted small">
                    Showing {{ $flocks->firstItem() }} to {{ $flocks->lastItem() }} of {{ $flocks->total() }} flocks
                </div>
                <nav>
                    <ul class="pagination mb-0">
                        @if($flocks->onFirstPage())
                            <li class="page-item disabled"><span class="page-link">‹ Previous</span></li>
                        @else
                            <li class="page-item"><a class="page-link" href="{{ $flocks->previousPageUrl() }}">‹ Previous</a></li>
                        @endif
                        @php
                            $cur  = $flocks->currentPage();
                            $last = $flocks->lastPage();
                            $s    = max(1, $cur - 2);
                            $e    = min($last, $cur + 2);
                        @endphp

                        @if($s > 1)
                            <li class="page-item">
                                <a class="page-link" href="{{ $flocks->url(1) }}">1</a>
                            </li>
                            @if($s > 2)
                                <li class="page-item disabled"><span class="page-link">…</span></li>
                            @endif
                        @endif

                        @for($p = $s; $p <= $e; $p++)
                            <li class="page-item {{ $p == $cur ? 'active' : '' }}">
                                @if($p == $cur)
                                    <span class="page-link">{{ $p }}</span>
                                @else
                                    <a class="page-link" href="{{ $flocks->url($p) }}">{{ $p }}</a>
                                @endif
                            </li>
                        @endfor

                        @if($e < $last)
                            @if($e < $last - 1)
                                <li class="page-item disabled"><span class="page-link">…</span></li>
                            @endif
                            <li class="page-item">
                                <a class="page-link" href="{{ $flocks->url($last) }}">{{ $last }}</a>
                            </li>
                        @endif
                        @if($flocks->hasMorePages())
                            <li class="page-item"><a class="page-link" href="{{ $flocks->nextPageUrl() }}">Next ›</a></li>
                        @else
                            <li class="page-item disabled"><span class="page-link">Next ›</span></li>
                        @endif
                    </ul>
                </nav>
            </div>
            @endif

        </div>{{-- /card-body --}}
    </div>{{-- /card --}}
</div>

{{-- ══════════════════════════════════════════════════════════════════════
     VIEW FLOCK MODAL
══════════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="viewFlockModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white"><i class="fas fa-eye me-2"></i>Flock Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewFlockContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2">Loading…</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════
     EDIT FLOCK MODAL
══════════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="editFlockModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title text-white"><i class="fas fa-edit me-2"></i>Edit Flock</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="editFlockContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-warning" role="status"></div>
                    <p class="mt-2">Loading…</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="saveEditFlock">Update Flock</button>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════
     BREEDERS MODAL
══════════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="breedersModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius:20px;overflow:hidden;">
            <div class="modal-header border-0" style="background:linear-gradient(135deg,#7c3aed,#a855f7);padding:1.25rem 1.5rem;">
                <div>
                    <h5 class="modal-title text-white fw-bold mb-0">
                        <i class="fas fa-heart me-2"></i>Breeder Management
                    </h5>
                    <small class="text-white opacity-75" id="breederModalSubtitle">Track retained breeders &amp; sellable stock</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0" id="breedersModalBody">
                <div class="text-center py-5">
                    <div class="spinner-border" style="color:#7c3aed;" role="status"></div>
                    <p class="mt-2 text-muted">Loading…</p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════
     CREATE FLOCK MODAL
     — No green panel. All fields use the same plain form-control style.
     — Sellable preview sits directly below Initial Count as a read-only
       helper field (col-md-6), matching the grid of every other row.
══════════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="createFlockModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title text-white"><i class="fas fa-plus-circle me-2"></i>Create New Flock</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="createFlockForm">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">

                        {{-- Species --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Species <span class="text-danger">*</span></label>
                            <select name="species_id" class="form-select" required>
                                <option value="">Select Species</option>
                                @foreach($species as $spec)
                                    <option value="{{ $spec->id }}">{{ $spec->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- House --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">House <span class="text-danger">*</span></label>
                            <select name="house_id" class="form-select" required>
                                <option value="">Select House</option>
                                @foreach(\App\Models\House::where('status','active')->get() as $house)
                                    <option value="{{ $house->id }}">
                                        {{ $house->name }} (Capacity: {{ number_format($house->capacity) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Breed --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Breed / Variety <span class="text-danger">*</span></label>
                            <input type="text" name="breed_variety" class="form-control" required
                                   placeholder="e.g. Broiler, Friesian, New Zealand White">
                        </div>

                        {{-- Sex --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Sex <span class="text-danger">*</span>
                                <i class="fas fa-circle-info text-muted ms-1"
                                   title="All animals in this flock must be the same sex. This determines which side of a breeding pairing the flock can be used for (dam or sire)."
                                   data-bs-toggle="tooltip"></i>
                            </label>
                            <select name="sex" class="form-select" required>
                                <option value="">Select Sex</option>
                                <option value="female">Female (Hens / Does / Dams)</option>
                                <option value="male">Male (Cocks / Bucks / Sires)</option>
                            </select>
                            <small class="text-muted">Required for pairing in Breeding Records</small>
                        </div>

                        {{-- Start Date --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Start Date <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" class="form-control"
                                   value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}" required>
                        </div>

                        {{-- Initial Count --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Initial Count <span class="text-danger">*</span>
                            </label>
                            <input type="number" name="initial_count" id="cf_initial"
                                   class="form-control" min="1" required
                                   placeholder="Total animals in flock"
                                   oninput="updateCreateSellablePreview()">
                        </div>

                        {{-- Breeders to Retain --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Breeders to Retain
                                <span class="text-muted fw-normal small">(optional)</span>
                            </label>
                            <input type="number" name="breeder_count" id="cf_breeders"
                                   class="form-control" min="0" value="0"
                                   placeholder="Animals kept for breeding"
                                   oninput="updateCreateSellablePreview()">
                            <small class="text-muted">Animals retained for reproduction — not for sale</small>
                        </div>

                        {{-- Sellable preview — plain read-only field, same col width --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Sellable Animals
                                <span class="text-muted fw-normal small">(calculated)</span>
                            </label>
                            <input type="text" id="cf_sellable_display" class="form-control"
                                   readonly placeholder="Auto-calculated" tabindex="-1"
                                   style="background:#f8fafc;color:#334155;cursor:default;">
                            <small id="cf_sellable_note" class="text-muted">Enter initial count and breeders above</small>
                        </div>

                        {{-- Source --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Source</label>
                            <input type="text" name="source" class="form-control"
                                   placeholder="e.g. ABC Hatchery, Own breeding">
                        </div>

                        {{-- Production Type --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Production Purpose
                                <i class="fas fa-circle-info text-muted ms-1"
                                   title="What this flock is primarily raised for"
                                   data-bs-toggle="tooltip"></i>
                            </label>
                            <select name="production_type" class="form-select">
                                <option value="meat">Meat — raised for slaughter</option>
                                <option value="eggs">Eggs — laying flock</option>
                                <option value="milk">Milk — dairy flock</option>
                                <option value="live_sale">Live Sale — sold as live animals</option>
                                <option value="dual_purpose">Dual Purpose — both production + sale</option>
                            </select>
                        </div>

                        {{-- Parity Number --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Parity Number
                                <i class="fas fa-circle-info text-muted ms-1"
                                   title="How many times the females in this group have given birth (0 = never given birth, 1 = one litter/birth, 2 = two, etc.). Most relevant for pigs, dairy cows, and rabbits. Leave blank if not applicable."
                                   data-bs-toggle="tooltip"></i>
                            </label>
                            <input type="number" name="parity_number" class="form-control" min="0"
                                   placeholder="e.g. 0 = first-time, 1 = one birth before">
                            <small class="text-muted">
                                Number of previous birth cycles. Leave blank if unknown or not applicable.
                            </small>
                        </div>

                        {{-- Notes --}}
                        <div class="col-12">
                            <label class="form-label fw-semibold">Notes</label>
                            <textarea name="notes" class="form-control" rows="3"
                                      placeholder="Any additional observations or details about this flock"></textarea>
                        </div>

                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="createFlockSubmit">
                        <span class="submit-text"><i class="fas fa-save me-2"></i>Create Flock</span>
                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Hidden close-flock form (standard POST) --}}
<form id="closeFlockForm" method="POST" style="display:none;">
    @csrf
    <input type="hidden" name="end_date"             id="close_end_date">
    <input type="hidden" name="final_count"           id="close_final_count">
    <input type="hidden" name="total_weight_kg"       id="close_total_weight_kg">
    <input type="hidden" name="average_price_per_kg" id="close_average_price_per_kg">
</form>

@push('styles')
<style>
    /* ── Page ──────────────────────────────────────────────── */
    .page-header { margin-bottom:1.5rem; }
    .page-icon   { width:50px;height:50px;display:flex;align-items:center;justify-content:center;
                   background:linear-gradient(135deg,#e8f4f8,#d1e9f0);border-radius:12px; }
    .page-title  { font-size:1.75rem;font-weight:600;color:#1e293b; }

    /* ── Stat cards ────────────────────────────────────────── */
    .stat-card         { background:#fff;border-radius:16px;padding:1rem;transition:all .3s;border:1px solid #e2e8f0; }
    .stat-card:hover   { transform:translateY(-2px);box-shadow:0 8px 25px rgba(0,0,0,.05); }
    .stat-card-body    { display:flex;align-items:center;gap:1rem; }
    .stat-card-icon    { width:48px;height:48px;display:flex;align-items:center;justify-content:center;border-radius:12px;font-size:1.5rem; }
    .stat-card-info    { flex:1; }
    .stat-card-label   { font-size:.75rem;text-transform:uppercase;letter-spacing:.5px;color:#64748b;font-weight:600; }
    .stat-card-value   { font-size:1.75rem;font-weight:700;margin:0;line-height:1.2;color:#1e293b; }

    /* ── Soft bg ───────────────────────────────────────────── */
    .bg-primary-soft   { background:#e0f2fe; }
    .bg-success-soft   { background:#dcfce7; }
    .bg-warning-soft   { background:#fef3c7; }
    .bg-info-soft      { background:#d1fae5; }

    /* ── Status soft badges ────────────────────────────────── */
    .bg-success-soft   { background:#dcfce7;color:#166534; }
    .bg-secondary-soft { background:#f1f5f9;color:#475569; }
    .bg-danger-soft    { background:#fee2e2;color:#991b1b; }
    .bg-info-soft      { background:#d1fae5;color:#065f46; }
    .badge             { font-weight:500;font-size:.75rem; }

    /* ── Table ─────────────────────────────────────────────── */
    .table th { font-weight:600;font-size:.875rem;color:#475569;border-bottom-width:1px; }
    .table td { font-size:.875rem;color:#334155;vertical-align:middle; }

    /* ── Sex badge ─────────────────────────────────────────── */
    .sex-badge {
        display:inline-flex;align-items:center;
        font-size:.75rem;font-weight:600;
        padding:.35rem .75rem;border-radius:20px;
    }
    .sex-badge.sex-female { background:#fce7f3;color:#9d174d; }
    .sex-badge.sex-male   { background:#dbeafe;color:#1e40af; }

    /* ── Breeder badge ─────────────────────────────────────── */
    .breeder-badge {
        display:inline-flex;align-items:center;
        background:linear-gradient(135deg,#ede9fe,#ddd6fe);
        color:#5b21b6;font-size:.75rem;
        padding:.35rem .75rem;border-radius:20px;font-weight:600;
    }

    /* ── Breeder outline button ────────────────────────────── */
    .btn-outline-breeder { color:#7c3aed;border-color:#7c3aed;transition:all .2s; }
    .btn-outline-breeder:hover { background:#7c3aed;border-color:#7c3aed;color:#fff; }

    /* ── Sellable read-only field error state ──────────────── */
    #cf_sellable_display.is-invalid { border-color:#dc3545 !important;background:#fff5f5 !important; }

    /* ── Population chip strip (view modal) ────────────────── */
    .pop-strip {
        display:flex;gap:.5rem;flex-wrap:wrap;align-items:center;
        background:#f8fafc;border:1px solid #e2e8f0;
        border-radius:12px;padding:.875rem 1rem;
    }
    .pop-chip {
        display:inline-flex;align-items:center;gap:.5rem;
        padding:.4rem .9rem;border-radius:30px;font-weight:700;font-size:.82rem;
    }
    .pop-chip .pop-label { font-size:.65rem;text-transform:uppercase;letter-spacing:.04em;opacity:.8;display:block; }
    .pop-chip.initial  { background:#e0f2fe;color:#0369a1; }
    .pop-chip.current  { background:#dcfce7;color:#166534; }
    .pop-chip.lost     { background:#fee2e2;color:#991b1b; }
    .pop-chip.breeder  { background:#ede9fe;color:#5b21b6; }
    .pop-chip.sellable { background:#d1fae5;color:#065f46; }

    /* ── Detail sections (view modal) ──────────────────────── */
    .detail-section    { margin-bottom:1.5rem; }
    .detail-section h6 { font-weight:600;color:#1e293b;margin-bottom:1rem;
                         padding-bottom:.5rem;border-bottom:2px solid #e2e8f0; }
    .detail-grid       { display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem; }
    .detail-item       { display:flex;flex-direction:column; }
    .detail-label      { font-size:.7rem;text-transform:uppercase;color:#64748b;font-weight:600;margin-bottom:.25rem; }
    .detail-value      { font-size:.95rem;font-weight:500;color:#1e293b; }

    /* ── Breeder timeline ──────────────────────────────────── */
    .breeder-timeline { position:relative;padding-left:2rem; }
    .breeder-timeline::before {
        content:'';position:absolute;left:.75rem;top:0;bottom:0;
        width:2px;background:linear-gradient(180deg,#7c3aed22,#7c3aed66,#7c3aed22);border-radius:2px;
    }
    .timeline-entry { position:relative;margin-bottom:1.25rem; }
    .timeline-entry::before {
        content:'';position:absolute;left:-1.4rem;top:.65rem;
        width:12px;height:12px;border-radius:50%;
        background:#7c3aed;border:2px solid #fff;box-shadow:0 0 0 3px #ede9fe;
    }
    .timeline-entry:first-child::before { background:#059669;box-shadow:0 0 0 3px #dcfce7; }
    .timeline-card { background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:.875rem 1rem;transition:all .2s; }
    .timeline-card:hover { border-color:#a78bfa;box-shadow:0 2px 8px rgba(124,58,237,.08); }
    .timeline-card.latest { background:linear-gradient(135deg,#fdf4ff,#ede9fe);border-color:#a78bfa; }

    /* ── Count pills ───────────────────────────────────────── */
    .count-pill { display:inline-flex;align-items:center;gap:.4rem;
                  padding:.45rem 1rem;border-radius:30px;font-weight:700;font-size:.85rem; }
    .count-pill.breeder  { background:#ede9fe;color:#5b21b6; }
    .count-pill.sellable { background:#dcfce7;color:#166534; }
    .count-pill.total    { background:#e0f2fe;color:#0369a1; }

    /* ── Breeder form panel ────────────────────────────────── */
    .breeder-form-panel {
        background:linear-gradient(135deg,#fdf4ff,#f5f3ff);
        border:1.5px solid #a78bfa;border-radius:16px;padding:1.25rem;
    }

    /* ── Filter ────────────────────────────────────────────── */
    .filter-section { background:#f8fafc;border-radius:12px; }

    /* ── Progress ──────────────────────────────────────────── */
    .progress { background-color:#e2e8f0;border-radius:10px;overflow:hidden; }

    /* ── Pagination ────────────────────────────────────────── */
    .page-link { border-radius:8px;margin:0 2px;border:none;color:#475569;padding:.5rem .875rem; }
    .page-item.active .page-link { background-color:#0d6efd;color:#fff; }
    .page-link:hover { background-color:#e2e8f0;color:#0d6efd; }

    /* ── Modals ────────────────────────────────────────────── */
    .modal-header { padding:1rem 1.5rem; }

    /* ── SweetAlert ────────────────────────────────────────── */
    .swal2-popup { border-radius:16px!important; }
    .swal2-html-container .form-control {
        border-radius:8px;border:1px solid #e2e8f0;padding:8px 12px;width:100%;box-sizing:border-box;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const CSRF = '{{ csrf_token() }}';

// ── Utility ───────────────────────────────────────────────────────────────────
function escapeHtml(str) {
    if (str === null || str === undefined) return '';
    return String(str).replace(/[&<>"']/g, m =>
        ({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' }[m]));
}
function fmt(n)  { return Number(n ?? 0).toLocaleString(); }
function pct(n)  { return Number(n ?? 0).toFixed(1); }
function sexLabel(sex) {
    if (sex === 'female') return '<span class="sex-badge sex-female"><i class="fas fa-venus me-1"></i>Female</span>';
    if (sex === 'male')   return '<span class="sex-badge sex-male"><i class="fas fa-mars me-1"></i>Male</span>';
    return '<span class="text-muted small fst-italic">Not set</span>';
}

// ── Bootstrap tooltips ────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));
});

// ── Filters ───────────────────────────────────────────────────────────────────
document.getElementById('applyFilters')?.addEventListener('click', function () {
    const params = new URLSearchParams();
    const s  = document.getElementById('speciesFilter').value;
    const st = document.getElementById('statusFilter').value;
    if (s)  params.append('species_id', s);
    if (st) params.append('status', st);
    window.location.href = '{{ route("flocks.index") }}' + (params.toString() ? '?' + params.toString() : '');
});

// ── CREATE MODAL: live sellable preview ───────────────────────────────────────
function updateCreateSellablePreview() {
    const initial  = parseInt(document.getElementById('cf_initial')?.value, 10) || 0;
    const breeders = parseInt(document.getElementById('cf_breeders')?.value, 10) || 0;
    const display  = document.getElementById('cf_sellable_display');
    const note     = document.getElementById('cf_sellable_note');
    if (!display) return;

    if (initial === 0) {
        display.value = '';
        display.classList.remove('is-invalid');
        display.style.background = '#f8fafc';
        display.style.color = '#334155';
        note.textContent = 'Enter initial count and breeders above';
        note.className = 'text-muted';
        return;
    }

    if (breeders > initial) {
        display.value = '⚠ Breeders exceed initial count';
        display.classList.add('is-invalid');
        display.style.background = '#fff5f5';
        display.style.color = '#dc3545';
        note.textContent = 'Breeder count cannot be greater than initial count';
        note.className = 'text-danger';
        return;
    }

    const sellable    = Math.max(0, initial - breeders);
    const sellablePct = initial > 0 ? ((sellable / initial) * 100).toFixed(0) : 0;
    display.value = sellable.toLocaleString() + ' animals (' + sellablePct + '% of flock)';
    display.classList.remove('is-invalid');
    display.style.background = '#f8fafc';
    display.style.color = '#334155';
    note.textContent = 'Available for sale after retaining breeders';
    note.className = 'text-muted';
}

// ── CREATE FLOCK submit ───────────────────────────────────────────────────────
document.getElementById('createFlockForm')?.addEventListener('submit', function (e) {
    e.preventDefault();

    const initial  = parseInt(document.getElementById('cf_initial')?.value, 10) || 0;
    const breeders = parseInt(document.getElementById('cf_breeders')?.value, 10) || 0;
    if (breeders > initial) {
        Swal.fire({ icon:'warning', title:'Invalid Count', text:'Breeders cannot exceed initial count.', confirmButtonColor:'#0d6efd' });
        return;
    }

    const btn = document.getElementById('createFlockSubmit');
    btn.querySelector('.submit-text').classList.add('d-none');
    btn.querySelector('.spinner-border').classList.remove('d-none');
    btn.disabled = true;

    fetch("{{ route('flocks.store') }}", {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        body: new FormData(this)
    })
    .then(async r => {
        const d = await r.json();
        if (!r.ok) {
            const msgs = d.errors ? Object.values(d.errors).flat().join('<br>') : (d.message || 'Failed');
            throw new Error(msgs);
        }
        return d;
    })
    .then(() => {
        Swal.fire({ icon:'success', title:'Flock Created!', text:'Flock created successfully', timer:1500, showConfirmButton:false })
            .then(() => window.location.reload());
    })
    .catch(err => {
        Swal.fire({ icon:'error', title:'Error', html: err.message });
        btn.querySelector('.submit-text').classList.remove('d-none');
        btn.querySelector('.spinner-border').classList.add('d-none');
        btn.disabled = false;
    });
});

// ── VIEW FLOCK MODAL ──────────────────────────────────────────────────────────
document.querySelectorAll('.view-flock-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        const id   = this.dataset.id;
        const body = document.getElementById('viewFlockContent');
        body.innerHTML = `<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Loading…</p></div>`;

        Promise.all([
            fetch(`/flocks/${id}/details`).then(r => r.json()),
            fetch(`/flocks/${id}/breeders`, { headers:{'Accept':'application/json'} }).then(r => r.json()),
        ])
        .then(([det, brd]) => {
            if (!det.success) { body.innerHTML = `<div class="alert alert-danger">${escapeHtml(det.message)}</div>`; return; }

            const f = det.flock;
            const s = det.summary;

            const initialCount  = s.initial_count  ?? f.initial_count;
            const currentCount  = s.current_count;
            const totalLost     = s.total_lost;
            const breederCount  = s.breeder_count;
            const sellableCount = s.sellable_count;
            const survivalPct   = initialCount > 0 ? ((currentCount  / initialCount) * 100).toFixed(1) : 0;
            const breederPct    = currentCount  > 0 && breederCount  > 0 ? ((breederCount  / currentCount) * 100).toFixed(1) : 0;
            const sellablePct   = currentCount  > 0 && breederCount  > 0 ? ((sellableCount / currentCount) * 100).toFixed(1) : 0;

            const statusCls = { active:'badge bg-success', closed:'badge bg-secondary', quarantined:'badge bg-danger' };
            const prodLabels = { meat:'Meat', eggs:'Eggs', milk:'Milk', live_sale:'Live Sale', dual_purpose:'Dual Purpose' };

            let historyHtml = '';
            if (brd.success && brd.history && brd.history.length > 0) {
                const rows = brd.history.map((entry, i) => `
                <div class="timeline-entry">
                    <div class="timeline-card ${i===0?'latest':''}">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <span class="count-pill breeder me-1">
                                    <i class="fas fa-heart"></i> ${fmt(entry.breeder_count)} breeders
                                </span>
                                <span class="count-pill sellable">
                                    <i class="fas fa-tags"></i> ${fmt(entry.sellable_count)} sellable
                                </span>
                            </div>
                            ${i===0 ? '<span class="badge px-2 py-1" style="background:#7c3aed;color:#fff;font-size:.7rem;border-radius:8px;">Latest</span>' : ''}
                        </div>
                        <div class="d-flex gap-3 flex-wrap" style="font-size:.8rem;color:#64748b;">
                            <span><i class="fas fa-calendar-alt me-1"></i>${escapeHtml(entry.date)} at ${escapeHtml(entry.time)}</span>
                            <span><i class="fas fa-clock me-1"></i>${escapeHtml(entry.diff)}</span>
                            <span><i class="fas fa-user me-1"></i>${escapeHtml(entry.set_by)}</span>
                        </div>
                        ${entry.reason && entry.reason !== '—' ? `
                        <div class="mt-2 p-2 rounded-2" style="background:#f1f5f9;font-size:.82rem;color:#334155;">
                            <i class="fas fa-comment-dots me-1 text-muted"></i>${escapeHtml(entry.reason)}
                        </div>` : ''}
                    </div>
                </div>`).join('');
                historyHtml = `
                <div class="detail-section">
                    <h6><i class="fas fa-clock-rotate-left me-2" style="color:#7c3aed;"></i>Breeder Change History</h6>
                    <div class="breeder-timeline">${rows}</div>
                </div>`;
            } else if (brd.success) {
                historyHtml = `
                <div class="detail-section">
                    <h6><i class="fas fa-heart me-2" style="color:#7c3aed;"></i>Breeder History</h6>
                    <div class="text-center py-3 text-muted">
                        <i class="fas fa-heart-crack fa-2x mb-2 d-block" style="color:#a78bfa;"></i>
                        No breeder count set yet.
                    </div>
                </div>`;
            }

            const progressBar = breederCount > 0
                ? `<div class="progress-bar" style="width:${breederPct}%;background:linear-gradient(90deg,#7c3aed,#a855f7);" title="Breeders ${breederPct}%"></div>
                   <div class="progress-bar" style="width:${sellablePct}%;background:linear-gradient(90deg,#059669,#10b981);" title="Sellable ${sellablePct}%"></div>`
                : `<div class="progress-bar" style="width:${survivalPct}%;background:linear-gradient(90deg,#0284c7,#38bdf8);" title="Alive ${survivalPct}%"></div>`;

            const progressLegend = breederCount > 0
                ? `<span><span style="color:#7c3aed;">■</span> Breeders ${breederPct}%</span>
                   <span><span style="color:#059669;">■</span> Sellable ${sellablePct}%</span>`
                : `<span><span style="color:#0284c7;">■</span> Alive ${survivalPct}%</span>
                   <span style="color:#dc2626;">■ Lost ${pct(s.mortality_rate)}%</span>`;

            body.innerHTML = `
            <div class="detail-section">
                <h6><i class="fas fa-info-circle me-2 text-primary"></i>Basic Information</h6>
                <div class="detail-grid">
                    <div class="detail-item"><span class="detail-label">Flock #</span><span class="detail-value fw-bold">${escapeHtml(f.flock_number)}</span></div>
                    <div class="detail-item"><span class="detail-label">Species</span><span class="detail-value">${escapeHtml(f.species_name)} <span class="text-muted">(${escapeHtml(f.species_code)})</span></span></div>
                    <div class="detail-item"><span class="detail-label">House</span><span class="detail-value">${escapeHtml(f.house_name)}</span></div>
                    <div class="detail-item"><span class="detail-label">Breed / Variety</span><span class="detail-value">${escapeHtml(f.breed_variety)}</span></div>
                    <div class="detail-item"><span class="detail-label">Sex</span><span class="detail-value">${sexLabel(f.sex)}</span></div>
                    <div class="detail-item"><span class="detail-label">Start Date</span><span class="detail-value">${escapeHtml(f.start_date)}</span></div>
                    <div class="detail-item"><span class="detail-label">Source</span><span class="detail-value">${escapeHtml(f.source || 'N/A')}</span></div>
                    <div class="detail-item"><span class="detail-label">Production Purpose</span><span class="detail-value">${escapeHtml(prodLabels[f.production_type] ?? f.production_type)}</span></div>
                    <div class="detail-item"><span class="detail-label">Status</span><span class="${statusCls[f.status] || 'badge bg-secondary'}">${escapeHtml(f.status)}</span></div>
                    ${f.parity_number != null ? `<div class="detail-item"><span class="detail-label">Parity # <span style="font-size:.65rem;color:#94a3b8;">(birth cycles)</span></span><span class="detail-value">${escapeHtml(f.parity_number)}</span></div>` : ''}
                </div>
            </div>

            <div class="detail-section">
                <h6><i class="fas fa-layer-group me-2 text-primary"></i>Population Breakdown</h6>
                <div class="pop-strip mb-3">
                    <div class="pop-chip initial">
                        <i class="fas fa-database"></i>
                        <div><span class="pop-label">Initial</span><strong>${fmt(initialCount)}</strong></div>
                    </div>
                    <span class="text-muted px-1">−</span>
                    <div class="pop-chip lost">
                        <i class="fas fa-skull-crossbones"></i>
                        <div><span class="pop-label">Lost (mortality + culls)</span><strong>${fmt(totalLost)}</strong></div>
                    </div>
                    <span class="text-muted px-1">=</span>
                    <div class="pop-chip current">
                        <i class="fas fa-circle-check"></i>
                        <div><span class="pop-label">Current Live</span><strong>${fmt(currentCount)}</strong></div>
                    </div>
                    ${breederCount > 0 ? `
                    <span class="text-muted px-1">→</span>
                    <div class="pop-chip breeder">
                        <i class="fas fa-heart"></i>
                        <div><span class="pop-label">Breeders (${breederPct}%)</span><strong>${fmt(breederCount)}</strong></div>
                    </div>
                    <div class="pop-chip sellable">
                        <i class="fas fa-tags"></i>
                        <div><span class="pop-label">Sellable (${sellablePct}%)</span><strong>${fmt(sellableCount)}</strong></div>
                    </div>` : `<span class="text-muted small fst-italic ms-2"><i class="fas fa-circle-info me-1"></i>No breeder split set</span>`}
                </div>
                <div class="progress" style="height:12px;border-radius:10px;background:#e2e8f0;">
                    ${progressBar}
                </div>
                <div class="d-flex justify-content-between mt-1 flex-wrap gap-2" style="font-size:.72rem;color:#64748b;">
                    ${progressLegend}
                </div>
            </div>

            <div class="detail-section">
                <h6><i class="fas fa-chart-line me-2 text-primary"></i>Performance Metrics</h6>
                <div class="detail-grid">
                    <div class="detail-item"><span class="detail-label">Age</span><span class="detail-value">${s.age_days} days / ${s.age_weeks} wks</span></div>
                    <div class="detail-item"><span class="detail-label">Mortality Rate</span><span class="detail-value ${s.mortality_rate>5?'text-danger':'text-success'}">${pct(s.mortality_rate)}%</span></div>
                    <div class="detail-item"><span class="detail-label">Survival Rate</span><span class="detail-value text-success">${pct(s.survival_rate)}%</span></div>
                    <div class="detail-item"><span class="detail-label">Feed Conversion Ratio</span><span class="detail-value">${pct(s.fcr)}</span></div>
                    <div class="detail-item"><span class="detail-label">Total Feed Consumed</span><span class="detail-value">${fmt(s.total_feed)} kg</span></div>
                    <div class="detail-item"><span class="detail-label">Avg Daily Gain</span><span class="detail-value">${s.avg_daily_gain} kg</span></div>
                </div>
            </div>

            ${historyHtml}

            ${f.notes ? `
            <div class="detail-section mb-0">
                <h6><i class="fas fa-sticky-note me-2 text-primary"></i>Notes</h6>
                <p class="mb-0" style="background:#f8fafc;border-radius:10px;padding:.875rem 1rem;font-size:.875rem;">${escapeHtml(f.notes)}</p>
            </div>` : ''}`;
        })
        .catch(err => {
            document.getElementById('viewFlockContent').innerHTML =
                `<div class="alert alert-danger">Error loading flock details. ${escapeHtml(err.message)}</div>`;
        });
    });
});

// ── EDIT FLOCK MODAL ──────────────────────────────────────────────────────────
document.querySelectorAll('.edit-flock-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        const id   = this.dataset.id;
        const body = document.getElementById('editFlockContent');
        body.innerHTML = `<div class="text-center py-4"><div class="spinner-border text-warning" role="status"></div><p class="mt-2">Loading…</p></div>`;
        window.currentEditFlockId = id;

        fetch(`/flocks/${id}/edit-data`)
            .then(r => r.json())
            .then(data => {
                if (!data.success) { body.innerHTML = `<div class="alert alert-danger">${escapeHtml(data.message)}</div>`; return; }
                const f = data.flock;
                const houseOpts = data.houses.map(h =>
                    `<option value="${h.id}" ${f.house_id==h.id?'selected':''}>${escapeHtml(h.name)} (Cap: ${h.capacity.toLocaleString()})</option>`
                ).join('');
                const prodTypes = [
                    { val:'meat',         label:'Meat — raised for slaughter' },
                    { val:'eggs',         label:'Eggs — laying flock' },
                    { val:'milk',         label:'Milk — dairy flock' },
                    { val:'live_sale',    label:'Live Sale — sold as live animals' },
                    { val:'dual_purpose', label:'Dual Purpose — both production + sale' },
                ];
                const prodOpts = prodTypes.map(t =>
                    `<option value="${t.val}" ${f.production_type===t.val?'selected':''}>${t.label}</option>`
                ).join('');

                body.innerHTML = `
                <form id="editFlockForm">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Breed/Variety <span class="text-danger">*</span></label>
                            <input type="text" name="breed_variety" class="form-control" value="${escapeHtml(f.breed_variety)}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Sex <span class="text-danger">*</span></label>
                            <select name="sex" class="form-select" required>
                                <option value="">Select Sex</option>
                                <option value="female" ${f.sex==='female'?'selected':''}>Female (Hens / Does / Dams)</option>
                                <option value="male" ${f.sex==='male'?'selected':''}>Male (Cocks / Bucks / Sires)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">House <span class="text-danger">*</span></label>
                            <select name="house_id" class="form-select" required>
                                <option value="">Select House</option>${houseOpts}
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Production Purpose</label>
                            <select name="production_type" class="form-select">${prodOpts}</select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Notes</label>
                            <textarea name="notes" class="form-control" rows="3">${escapeHtml(f.notes||'')}</textarea>
                        </div>
                    </div>
                </form>`;
            })
            .catch(() => { body.innerHTML = `<div class="alert alert-danger">Failed to load.</div>`; });
    });
});

document.getElementById('saveEditFlock')?.addEventListener('click', function () {
    const data = {};
    new FormData(document.getElementById('editFlockForm')).forEach((v,k) => data[k]=v);

    fetch(`/flocks/${window.currentEditFlockId}`, {
        method:'PUT',
        headers:{'X-CSRF-TOKEN':CSRF,'Accept':'application/json','Content-Type':'application/json'},
        body: JSON.stringify(data)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            Swal.fire({ icon:'success', title:'Updated!', text:'Flock updated', timer:1500, showConfirmButton:false })
                .then(() => window.location.reload());
        } else {
            Swal.fire({ icon:'error', title:'Error', text:data.message||'Failed' });
        }
    });
});

// ── SET BREEDERS MODAL ────────────────────────────────────────────────────────
document.addEventListener('click', function (e) {
    const btn = e.target.closest('.set-breeders-btn');
    if (!btn) return;

    const flockId     = btn.dataset.id;
    const flockNumber = btn.dataset.flockNumber;

    const modal    = new bootstrap.Modal(document.getElementById('breedersModal'));
    const subtitle = document.getElementById('breederModalSubtitle');
    const body     = document.getElementById('breedersModalBody');

    subtitle.textContent = `Flock ${flockNumber}`;
    body.innerHTML = `<div class="text-center py-5"><div class="spinner-border" style="color:#7c3aed;" role="status"></div><p class="mt-2 text-muted">Loading…</p></div>`;
    modal.show();

    fetch(`/flocks/${flockId}/breeders`, { headers:{'Accept':'application/json'} })
        .then(r => r.json())
        .then(data => {
            if (!data.success) { body.innerHTML = `<div class="alert alert-danger m-3">${escapeHtml(data.message)}</div>`; return; }

            const liveCount   = data.current_count;
            const latest      = data.breeder_count;
            const sellable    = data.sellable_count;
            const breederPct  = liveCount > 0 ? ((latest  / liveCount)*100).toFixed(1) : 0;
            const sellablePct = liveCount > 0 ? ((sellable / liveCount)*100).toFixed(1) : 0;

            subtitle.textContent = `Flock ${flockNumber} — ${fmt(liveCount)} animals`;

            const timelineHtml = data.history.length > 0
                ? data.history.map((entry, i) => `
                    <div class="timeline-entry">
                        <div class="timeline-card ${i===0?'latest':''}">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <span class="count-pill breeder me-1"><i class="fas fa-heart"></i> ${fmt(entry.breeder_count)} breeders</span>
                                    <span class="count-pill sellable"><i class="fas fa-tags"></i> ${fmt(entry.sellable_count)} sellable</span>
                                </div>
                                ${i===0?'<span class="badge px-2 py-1" style="background:#7c3aed;color:#fff;font-size:.7rem;border-radius:8px;">Latest</span>':''}
                            </div>
                            <div class="d-flex gap-3 flex-wrap" style="font-size:.8rem;color:#64748b;">
                                <span><i class="fas fa-calendar-alt me-1"></i>${escapeHtml(entry.date)} at ${escapeHtml(entry.time)}</span>
                                <span><i class="fas fa-clock me-1"></i>${escapeHtml(entry.diff)}</span>
                                <span><i class="fas fa-user me-1"></i>${escapeHtml(entry.set_by)}</span>
                            </div>
                            ${entry.reason && entry.reason!=='—' ? `<div class="mt-2 p-2 rounded-2" style="background:#f1f5f9;font-size:.82rem;color:#334155;"><i class="fas fa-comment-dots me-1 text-muted"></i>${escapeHtml(entry.reason)}</div>` : ''}
                        </div>
                    </div>`).join('')
                : `<div class="text-center py-4 text-muted"><i class="fas fa-heart-crack fa-2x mb-2 d-block" style="color:#a78bfa;"></i>No breeder count set yet.</div>`;

            body.innerHTML = `
            <div class="p-4 border-bottom" style="background:linear-gradient(135deg,#fdf4ff,#f5f3ff);">
                <div class="row g-3 text-center">
                    <div class="col-4">
                        <div class="count-pill total d-block text-center py-3 rounded-3">
                            <div style="font-size:1.5rem;font-weight:800;">${fmt(liveCount)}</div>
                            <div style="font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;">Current Flock</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="count-pill breeder d-block text-center py-3 rounded-3" style="padding:.75rem 1rem;">
                            <div style="font-size:1.5rem;font-weight:800;">${fmt(latest)}</div>
                            <div style="font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;">Breeders (${breederPct}%)</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="count-pill sellable d-block text-center py-3 rounded-3" style="padding:.75rem 1rem;">
                            <div style="font-size:1.5rem;font-weight:800;">${fmt(sellable)}</div>
                            <div style="font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;">Sellable (${sellablePct}%)</div>
                        </div>
                    </div>
                </div>
                ${latest > 0 ? `
                <div class="mt-3">
                    <div class="progress" style="height:10px;border-radius:10px;background:#e2e8f0;">
                        <div class="progress-bar" style="width:${breederPct}%;background:linear-gradient(90deg,#7c3aed,#a855f7);border-radius:10px 0 0 10px;"></div>
                        <div class="progress-bar" style="width:${sellablePct}%;background:linear-gradient(90deg,#059669,#10b981);border-radius:0 10px 10px 0;"></div>
                    </div>
                    <div class="d-flex justify-content-between mt-1" style="font-size:.72rem;color:#64748b;">
                        <span><span style="color:#7c3aed;">■</span> Breeders ${breederPct}%</span>
                        <span><span style="color:#059669;">■</span> Sellable ${sellablePct}%</span>
                    </div>
                </div>` : ''}
            </div>

            <div class="p-4 border-bottom">
                <div class="breeder-form-panel">
                    <h6 class="fw-bold mb-3" style="color:#5b21b6;"><i class="fas fa-sliders me-2"></i>Update Breeder Count</h6>
                    <div class="row g-3">
                        <div class="col-md-5">
                            <label class="form-label fw-semibold" style="font-size:.85rem;">
                                Breeder Count <span class="text-muted fw-normal">(max: ${fmt(liveCount)})</span>
                            </label>
                            <input type="number" id="breederCountInput" class="form-control"
                                   min="0" max="${liveCount}" value="${latest}"
                                   style="border-radius:10px;border:1.5px solid #a78bfa;"
                                   oninput="updateBreederPreview(${liveCount})">
                        </div>
                        <div class="col-md-7">
                            <label class="form-label fw-semibold" style="font-size:.85rem;">
                                Reason <span class="text-muted fw-normal">(optional)</span>
                            </label>
                            <input type="text" id="breederReasonInput" class="form-control"
                                   placeholder="e.g. Post-selection, removed weak breeders…"
                                   maxlength="255"
                                   style="border-radius:10px;border:1.5px solid #a78bfa;">
                        </div>
                        <div class="col-12">
                            <div id="breederPreview" class="d-flex gap-2 align-items-center flex-wrap" style="font-size:.85rem;min-height:28px;"></div>
                        </div>
                        <div class="col-12 d-flex justify-content-end">
                            <button class="btn px-4 py-2" id="saveBreederBtn"
                                    onclick="saveBreederCount(${flockId}, ${liveCount})"
                                    style="background:linear-gradient(135deg,#7c3aed,#a855f7);color:#fff;border:none;border-radius:12px;font-weight:600;">
                                <span class="submit-text"><i class="fas fa-check me-1"></i>Save Breeder Count</span>
                                <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-4">
                <h6 class="fw-bold mb-4" style="color:#5b21b6;font-size:.8rem;text-transform:uppercase;letter-spacing:.06em;">
                    <i class="fas fa-clock-rotate-left me-2"></i>Change History
                </h6>
                <div class="breeder-timeline">${timelineHtml}</div>
            </div>`;

            updateBreederPreview(liveCount);
        })
        .catch(() => { body.innerHTML = `<div class="alert alert-danger m-3">Failed to load breeder data.</div>`; });
});

function updateBreederPreview(currentCount) {
    const val      = parseInt(document.getElementById('breederCountInput')?.value, 10) || 0;
    const sellable = Math.max(0, currentCount - val);
    const preview  = document.getElementById('breederPreview');
    if (!preview) return;
    if (val > currentCount) {
        preview.innerHTML = `<span style="color:#ef4444;font-weight:600;"><i class="fas fa-triangle-exclamation me-1"></i>Cannot exceed ${fmt(currentCount)} animals</span>`;
        return;
    }
    preview.innerHTML = `
        <span class="count-pill breeder"><i class="fas fa-heart me-1"></i>${fmt(val)} breeders retained</span>
        <span class="count-pill sellable"><i class="fas fa-tags me-1"></i>${fmt(sellable)} sellable</span>`;
}

function saveBreederCount(flockId, currentCount) {
    const count  = parseInt(document.getElementById('breederCountInput')?.value, 10);
    const reason = document.getElementById('breederReasonInput')?.value?.trim();

    if (isNaN(count) || count < 0 || count > currentCount) {
        Swal.fire({ icon:'warning', title:'Invalid Count', text:`Breeder count must be 0–${fmt(currentCount)}.`, confirmButtonColor:'#7c3aed' });
        return;
    }

    const btn = document.getElementById('saveBreederBtn');
    btn.querySelector('.submit-text').classList.add('d-none');
    btn.querySelector('.spinner-border').classList.remove('d-none');
    btn.disabled = true;

    fetch(`/flocks/${flockId}/breeders`, {
        method:'POST',
        headers:{'X-CSRF-TOKEN':CSRF,'Content-Type':'application/json','Accept':'application/json'},
        body: JSON.stringify({ breeder_count: count, reason: reason || null })
    })
    .then(r => { if (!r.ok) return r.json().then(d => Promise.reject(d)); return r.json(); })
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon:'success', title:'Breeders Updated!',
                html:`<strong>${fmt(count)}</strong> breeders retained.<br>
                      <span style="color:#059669;font-weight:600;">${fmt(data.sellable_count)}</span> animals available for sale.`,
                timer:2500, showConfirmButton:false, timerProgressBar:true
            }).then(() => window.location.reload());
        } else {
            Swal.fire({ icon:'error', title:'Error', text:data.message, confirmButtonColor:'#7c3aed' });
            btn.querySelector('.submit-text').classList.remove('d-none');
            btn.querySelector('.spinner-border').classList.add('d-none');
            btn.disabled = false;
        }
    })
    .catch(err => {
        Swal.fire({ icon:'error', title:'Error', text:err?.message||'Something went wrong.', confirmButtonColor:'#7c3aed' });
        btn.querySelector('.submit-text').classList.remove('d-none');
        btn.querySelector('.spinner-border').classList.add('d-none');
        btn.disabled = false;
    });
}

// ── CLOSE FLOCK ───────────────────────────────────────────────────────────────
document.querySelectorAll('.close-flock-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        const flockId      = this.dataset.id;
        const flockNumber  = this.dataset.flockNumber;
        const initialCount = parseInt(this.dataset.initialCount, 10);

        Swal.fire({
            title: `<i class="fas fa-check-circle text-success me-2"></i>Close Flock`,
            html: `
            <div class="text-start" style="padding:.5rem;">
                <div class="alert alert-warning mb-4" style="background:#fef3c7;border:none;border-radius:10px;color:#92400e;">
                    <i class="fas fa-info-circle me-2"></i>Closing flock: <strong>${escapeHtml(flockNumber)}</strong>
                </div>
                <div class="mb-3"><label class="form-label fw-semibold">End Date <span class="text-danger">*</span></label>
                    <input type="date" id="swal_end_date" class="form-control" value="${new Date().toISOString().split('T')[0]}"></div>
                <div class="mb-3"><label class="form-label fw-semibold">Final Count <span class="text-danger">*</span></label>
                    <input type="number" id="swal_final_count" class="form-control" min="0" max="${initialCount}" placeholder="Enter final count">
                    <small class="text-muted">Maximum: ${fmt(initialCount)}</small></div>
                <div style="display:flex;gap:16px;margin-bottom:12px;">
                    <div style="flex:1;"><label class="form-label fw-semibold">Total Weight (kg) <span class="text-danger">*</span></label>
                        <input type="number" id="swal_total_weight_kg" class="form-control" step="0.01" min="0" placeholder="Total weight"></div>
                    <div style="flex:1;"><label class="form-label fw-semibold">Avg Price / kg <span class="text-danger">*</span></label>
                        <input type="number" id="swal_average_price_per_kg" class="form-control" step="0.01" min="0" placeholder="Price"></div>
                </div>
            </div>`,
            showCancelButton:true,
            confirmButtonText:'<i class="fas fa-check-circle me-2"></i>Close Flock',
            cancelButtonText:'Cancel',
            confirmButtonColor:'#28a745',
            cancelButtonColor:'#6c757d',
            width:'500px',
            allowOutsideClick:false,
            preConfirm: () => {
                const endDate    = document.getElementById('swal_end_date').value;
                const finalCount = document.getElementById('swal_final_count').value;
                const weight     = document.getElementById('swal_total_weight_kg').value;
                const price      = document.getElementById('swal_average_price_per_kg').value;
                if (!endDate)                                            { Swal.showValidationMessage('Select end date'); return false; }
                if (!finalCount||finalCount<0||finalCount>initialCount) { Swal.showValidationMessage(`Final count must be 0–${initialCount}`); return false; }
                if (!weight||weight<0)                                   { Swal.showValidationMessage('Enter total weight'); return false; }
                if (!price||price<0)                                     { Swal.showValidationMessage('Enter price per kg'); return false; }
                return { endDate, finalCount, totalWeightKg:weight, avgPricePerKg:price };
            }
        }).then(result => {
            if (!result.isConfirmed) return;
            document.getElementById('close_end_date').value             = result.value.endDate;
            document.getElementById('close_final_count').value          = result.value.finalCount;
            document.getElementById('close_total_weight_kg').value      = result.value.totalWeightKg;
            document.getElementById('close_average_price_per_kg').value = result.value.avgPricePerKg;
            const form = document.getElementById('closeFlockForm');
            form.action = `/flocks/${flockId}/close`;
            form.submit();
        });
    });
});

// ── DELETE FLOCK ──────────────────────────────────────────────────────────────
document.querySelectorAll('.delete-flock-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        const id  = this.dataset.id;
        const num = this.dataset.flockNumber;
        Swal.fire({
            title:'Delete Flock?',
            html:`Are you sure you want to delete flock <strong>${escapeHtml(num)}</strong>?<br><span class="text-danger">This cannot be undone.</span>`,
            icon:'warning', showCancelButton:true, confirmButtonColor:'#dc3545', confirmButtonText:'Yes, Delete'
        }).then(result => {
            if (!result.isConfirmed) return;
            fetch(`/flocks/${id}`, { method:'DELETE', headers:{'X-CSRF-TOKEN':CSRF,'Accept':'application/json'} })
                .then(() => {
                    Swal.fire({ icon:'success', title:'Deleted!', timer:1500, showConfirmButton:false })
                        .then(() => window.location.reload());
                })
                .catch(() => { Swal.fire({ icon:'error', title:'Error', text:'Failed to delete flock.' }); });
        });
    });
});
</script>
@endpush
@endsection