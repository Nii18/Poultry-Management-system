{{-- resources/views/daily-logs/index.blade.php --}}
@extends('layouts.master')

@section('content')

<style>
    /* =========================================
       TABLE
    ========================================== */
    .daily-log-table th {
        white-space: nowrap;
        font-size: 13px;
        font-weight: 600;
        background: #f8f9fa;
        color: #495057;
        vertical-align: middle;
    }

    .daily-log-table td {
        vertical-align: middle;
    }

    .actions-column {
        width: 210px;
        white-space: nowrap;
    }

    .action-btn-group {
        display: flex;
        gap: 6px;
        flex-wrap: nowrap;
    }

    .action-btn-group .btn {
        padding: 5px 10px;
        font-size: 12px;
        border-radius: 8px;
    }

    /* =========================================
       MODALS
    ========================================== */
    #createLogModal .modal-content,
    #viewLogModal .modal-content,
    #editLogModal .modal-content {
        border: none;
        border-radius: 16px;
        overflow: hidden;
    }

    #createLogModal .modal-header,
    #viewLogModal .modal-header,
    #editLogModal .modal-header {
        background: linear-gradient(135deg, #2f9088, #276f69);
        color: #fff;
        border-bottom: none;
        padding: 1.2rem 1.5rem;
    }

    #createLogModal .modal-header h4,
    #viewLogModal .modal-header h4,
    #editLogModal .modal-header h5,
    #createLogModal .modal-header small,
    #viewLogModal .modal-header small {
        color: #fff !important;
    }

    #createLogModal .modal-body,
    #viewLogModal .modal-body,
    #editLogModal .modal-body {
        background: #f5f7fb;
        padding: 1.5rem;
    }

    #createLogModal label,
    #editLogModal label {
        color: #495057 !important;
        font-weight: 600;
        margin-bottom: 6px;
    }

    #createLogModal .form-control,
    #createLogModal .form-select,
    #editLogModal .form-control,
    #editLogModal .form-select {
        border-radius: 10px;
        border: 1px solid #dce1e7;
        min-height: 46px;
        background: #fff !important;
        color: #212529 !important;
    }

    #createLogModal textarea,
    #editLogModal textarea {
        min-height: 120px;
        resize: vertical;
    }

    #createLogModal .modal-footer,
    #viewLogModal .modal-footer,
    #editLogModal .modal-footer {
        background: #fff;
        border-top: 1px solid #edf2f7;
        padding: 1rem 1.5rem;
    }

    #createLogModal .btn-close,
    #viewLogModal .btn-close,
    #editLogModal .btn-close {
        filter: brightness(0) invert(1);
        opacity: 1;
    }

    /* =========================================
       VIEW MODAL CARDS
    ========================================== */
    #viewLogModal .card {
        border: 1px solid #edf2f7;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: none;
    }

    #viewLogModal .card-header {
        background: #ffffff !important;
        color: #212529 !important;
        border-bottom: 1px solid #edf2f7;
        padding: 1rem 1.2rem;
    }

    #viewLogModal .card-body {
        background: #fff;
        color: #495057 !important;
    }

    #viewLogModal table th {
        color: #495057 !important;
        font-weight: 600;
    }

    #viewLogModal table td {
        color: #212529 !important;
    }

    /* =========================================
       RESPONSIVE
    ========================================== */
    @media (max-width: 768px) {

        .actions-column {
            width: 100%;
        }

        .action-btn-group {
            flex-wrap: wrap;
        }

        #createLogModal .modal-body,
        #viewLogModal .modal-body,
        #editLogModal .modal-body {
            padding: 1rem;
        }
    }
</style>

<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6 col-12">
                <h2>Daily Logs</h2>
                <p class="mb-0 text-title-gray">
                    View all daily records and observations
                </p>
            </div>

            <div class="col-sm-6 col-12">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard') }}">
                            <i class="iconly-Home icli svg-color"></i>
                        </a>
                    </li>

                    <li class="breadcrumb-item">
                        Daily Logs
                    </li>

                    <li class="breadcrumb-item active">
                        All Logs
                    </li>
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
                            <h3 class="mb-1">
                                Daily Log Records
                            </h3>

                            <small class="text-muted">
                                Track operational flock performance
                            </small>
                        </div>

                        <button type="button"
                                class="btn btn-primary"
                                data-bs-toggle="modal"
                                data-bs-target="#createLogModal">

                            <i class="fa fa-plus me-1"></i>
                            New Daily Log
                        </button>

                    </div>
                </div>

                <div class="card-body">

                    <!-- FILTERS -->
                    <form method="GET"
                          action="{{ route('daily-logs.index') }}"
                          class="row mb-4">

                        <div class="col-md-3 mb-2">
                            <label class="form-label">
                                Flock
                            </label>

                            <select name="flock_id" class="form-select">

                                <option value="">
                                    All Flocks
                                </option>

                                @foreach($flocks as $flock)

                                    <option value="{{ $flock->id }}"
                                        {{ request('flock_id') == $flock->id ? 'selected' : '' }}>

                                        {{ $flock->flock_number }}

                                    </option>

                                @endforeach

                            </select>
                        </div>

                        <div class="col-md-3 mb-2">
                            <label class="form-label">
                                Start Date
                            </label>

                            <input type="date"
                                   name="start_date"
                                   class="form-control"
                                   value="{{ $startDate->format('Y-m-d') }}">
                        </div>

                        <div class="col-md-3 mb-2">
                            <label class="form-label">
                                End Date
                            </label>

                            <input type="date"
                                   name="end_date"
                                   class="form-control"
                                   value="{{ $endDate->format('Y-m-d') }}">
                        </div>

                        <div class="col-md-3 mb-2 d-flex align-items-end">
                            <button type="submit"
                                    class="btn btn-primary me-2">

                                Filter
                            </button>

                            <a href="{{ route('daily-logs.index') }}"
                               class="btn btn-warning border">

                                Reset
                            </a>
                        </div>

                    </form>

              <!-- Updated Table Section - Borderless like Flocks -->
<div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
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
                <th class="py-3">Eggs</th>
                <th class="py-3">Temp (°C)</th>
                <th class="py-3 text-center">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
            <tr>
                <td>{{ $log->log_date->format('d M Y') }}</td>
                <td>
                    <button type="button" class="btn btn-link p-0 text-primary fw-semibold text-decoration-none view-log-btn" 
                            data-log='@json($log)' data-bs-toggle="modal" data-bs-target="#viewLogModal">
                        {{ $log->flock->flock_number ?? 'N/A' }}
                    </button>
                </td>
                <td>{{ $log->flock->species->name ?? 'N/A' }}</td>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <div class="progress flex-grow-1" style="height: 6px;">
                            <div class="progress-bar bg-danger" style="width: {{ min(100, ($log->mortality_count / max($log->flock->current_count, 1)) * 100) }}%"></div>
                        </div>
                        <span class="small fw-semibold {{ $log->mortality_count > 0 ? 'text-danger' : 'text-muted' }}">
                            {{ $log->mortality_count }}
                        </span>
                    </div>
                </td>
                <td class="text-center">{{ $log->culling_count }}</td>
                <td>{{ number_format($log->feed_intake_kg) }} kg</td>
                <td>{{ number_format($log->water_consumption_liters) }} L</td>
                <td>{{ $log->average_weight_kg ? number_format($log->average_weight_kg, 2) . ' kg' : 'N/A' }}</td>
                <td>
                    @if($log->eggs_collected > 0)
                        <span class="badge bg-success-soft text-success">
                            {{ number_format($log->eggs_collected, 0) }}
                        </span>
                        @if($log->eggs_damaged > 0)
                            <small class="text-danger d-block">{{ $log->eggs_damaged }} dmg</small>
                        @endif
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </td>
                <td>
                    @if($log->min_temperature_c && $log->max_temperature_c)
                        <span class="badge bg-info-soft text-info">
                            {{ $log->min_temperature_c }}° - {{ $log->max_temperature_c }}°
                        </span>
                    @else
                        N/A
                    @endif
                </td>
                <td>
                    <div class="btn-group gap-1">
                        <button type="button" class="btn btn-sm btn-outline-primary view-log-btn" 
                                data-log='@json($log)' data-bs-toggle="modal" data-bs-target="#viewLogModal" title="View Details">
                            <i class="fas fa-eye"></i> View
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-warning edit-log-btn" 
                                data-log='@json($log)' data-bs-toggle="modal" data-bs-target="#editLogModal" title="Edit">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger delete-log-btn" 
                                data-id="{{ $log->id }}" title="Delete">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="10" class="text-center py-5">
                    <div class="empty-state">
                        <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No Daily Logs Found</h5>
                        <p class="text-muted mb-3">Start recording daily flock operations.</p>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createLogModal">
                            <i class="fas fa-plus me-2"></i>Create Your First Daily Log
                        </button>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

                    <!-- DELETE FORM - Hidden -->
<form id="deleteLogForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

             <!-- Enhanced Pagination -->
@if($logs->hasPages())
<div class="d-flex justify-content-between align-items-center mt-4">
    <div class="text-muted">
        Showing {{ $logs->firstItem() }} to {{ $logs->lastItem() }} of {{ $logs->total() }} daily logs
    </div>
    <nav>
        <ul class="pagination mb-0">
            @if($logs->onFirstPage())
                <li class="page-item disabled">
                    <span class="page-link">‹ Previous</span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $logs->previousPageUrl() }}" rel="prev">‹ Previous</a>
                </li>
            @endif

            @php
                $current = $logs->currentPage();
                $last = $logs->lastPage();
                $start = max(1, $current - 2);
                $end = min($last, $current + 2);
            @endphp

            @if($start > 1)
                <li class="page-item">
                    <a class="page-link" href="{{ $logs->url(1) }}">1</a>
                </li>
                @if($start > 2)
                    <li class="page-item disabled">
                        <span class="page-link">...</span>
                    </li>
                @endif
            @endif

            @for($page = $start; $page <= $end; $page++)
                @if($page == $current)
                    <li class="page-item active">
                        <span class="page-link">{{ $page }}</span>
                    </li>
                @else
                    <li class="page-item">
                        <a class="page-link" href="{{ $logs->url($page) }}">{{ $page }}</a>
                    </li>
                @endif
            @endfor

            @if($end < $last)
                @if($end < $last - 1)
                    <li class="page-item disabled">
                        <span class="page-link">...</span>
                    </li>
                @endif
                <li class="page-item">
                    <a class="page-link" href="{{ $logs->url($last) }}">{{ $last }}</a>
                </li>
            @endif

            @if($logs->hasMorePages())
                <li class="page-item">
                    <a class="page-link" href="{{ $logs->nextPageUrl() }}" rel="next">Next ›</a>
                </li>
            @else
                <li class="page-item disabled">
                    <span class="page-link">Next ›</span>
                </li>
            @endif
        </ul>
    </nav>
</div>
@endif

<!-- =========================
<!-- =========================
VIEW DAILY LOG MODAL
========================= -->
<div class="modal fade" id="viewLogModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg overflow-hidden">

            <!-- Header -->
            <div class="modal-header bg-primary text-white border-0">
                <div>
                    <h4 class="mb-1 fw-bold">
                        <i class="fa fa-clipboard-list me-2"></i>
                        Daily Log Details
                    </h4>

                    <small class="opacity-75">
                        Detailed operational information
                    </small>
                </div>

                <button type="button"
                        class="btn-close btn-close-white"
                        data-bs-dismiss="modal"></button>
            </div>

            <!-- Body -->
            <div class="modal-body bg-light">

                <div class="row g-4">

                    <!-- Basic Information -->
                    <div class="col-md-6">

                        <div class="card border-0 shadow-sm h-100">

                            <div class="card-header bg-white border-bottom">
                                <h5 class="mb-0 fw-semibold">
                                    <i class="fa fa-circle-info text-primary me-2"></i>
                                    Basic Information
                                </h5>
                            </div>

                            <div class="card-body">

                                <table class="table align-middle mb-0">

                                    <tr>
                                        <th width="40%">Date</th>
                                        <td id="view_date"></td>
                                    </tr>

                                    <tr>
                                        <th>Mortality</th>
                                        <td id="view_mortality"></td>
                                    </tr>

                                    <tr>
                                        <th>Culling</th>
                                        <td id="view_culling"></td>
                                    </tr>

                                    <tr>
                                        <th>Feed Intake</th>
                                        <td id="view_feed"></td>
                                    </tr>

                                    <tr>
                                        <th>Water Consumption</th>
                                        <td id="view_water"></td>
                                    </tr>

                                    <tr>
                                        <th>Average Weight</th>
                                        <td id="view_weight"></td>
                                    </tr>

                                    <tr>
                                        <th>Eggs Collected</th>
                                        <td id="view_eggs_collected"></td>
                                    </tr>
                                    <tr>
                                        <th>Eggs Damaged</th>
                                        <td id="view_eggs_damaged"></td>
                                    </tr>

                                </table>

                            </div>
                        </div>
                    </div>

                    <!-- Environmental -->
                    <div class="col-md-6">

                        <div class="card border-0 shadow-sm h-100">

                            <div class="card-header bg-white border-bottom">
                                <h5 class="mb-0 fw-semibold">
                                    <i class="fa fa-temperature-half text-danger me-2"></i>
                                    Environmental Data
                                </h5>
                            </div>

                            <div class="card-body">

                                <table class="table align-middle mb-0">

                                    <tr>
                                        <th width="40%">Temperature</th>
                                        <td id="view_temp"></td>
                                    </tr>

                                    <tr>
                                        <th>Humidity</th>
                                        <td id="view_humidity"></td>
                                    </tr>

                                    <tr>
                                        <th>Ammonia</th>
                                        <td id="view_ammonia"></td>
                                    </tr>

                                </table>

                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="col-12">

                        <div class="card border-0 shadow-sm">

                            <div class="card-header bg-white border-bottom">
                                <h5 class="mb-0 fw-semibold">
                                    <i class="fa fa-note-sticky text-warning me-2"></i>
                                    Notes & Observations
                                </h5>
                            </div>

                            <div class="card-body">

                                <div class="bg-light rounded p-3 border">
                                    <p class="mb-0 text-dark" id="view_notes"></p>
                                </div>

                            </div>

                        </div>
                    </div>

                </div>

            </div>

            <!-- Footer -->
            <div class="modal-footer bg-white border-0">

                <button type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">

                    Close
                </button>

            </div>

        </div>
    </div>
</div>

<!-- =========================
EDIT DAILY LOG MODAL
========================= -->
<div class="modal fade" id="editLogModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content border-0 shadow-lg">

            <form id="editLogForm" method="POST">
                @csrf
                @method('PUT')

                <div class="modal-header bg-warning" style="background: linear-gradient(135deg, #f59e0b, #d97706) !important;">
                    <h5 class="modal-title" style="color:#fff;">
                        <i class="fa fa-edit me-2"></i>
                        Edit Daily Log
                    </h5>

                    <button type="button"
        class="btn-close btn-close-white"
        data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <div class="row">

                        <div class="col-md-3 mb-3">
                            <label class="form-label">Mortality Count</label>

                            <input type="number"
                                   name="mortality_count"
                                   id="edit_mortality_count"
                                   class="form-control"
                                   min="0">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">Culling Count</label>

                            <input type="number"
                                   name="culling_count"
                                   id="edit_culling_count"
                                   class="form-control"
                                   min="0">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">Eggs Collected</label>
                            <input type="number" name="eggs_collected" id="edit_eggs_collected"
                                   class="form-control" min="0" step="1" value="0">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Eggs Damaged</label>
                            <input type="number" name="eggs_damaged" id="edit_eggs_damaged"
                                   class="form-control" min="0" step="1" value="0">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">Feed Intake (kg)</label>

                            <input type="number"
                                   name="feed_intake_kg"
                                   id="edit_feed"
                                   class="form-control"
                                   step="0.1">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">Water Consumption</label>

                            <input type="number"
                                   name="water_consumption_liters"
                                   id="edit_water"
                                   class="form-control"
                                   step="0.1">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">Average Weight</label>

                            <input type="number"
                                   name="average_weight_kg"
                                   id="edit_weight"
                                   class="form-control"
                                   step="0.01">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">Min Temp</label>

                            <input type="number"
                                   name="min_temperature_c"
                                   id="edit_min_temp"
                                   class="form-control"
                                   step="0.1">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">Max Temp</label>

                            <input type="number"
                                   name="max_temperature_c"
                                   id="edit_max_temp"
                                   class="form-control"
                                   step="0.1">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">Ammonia (ppm)</label>

                            <input type="number"
                                   name="ammonia_ppm"
                                   id="edit_ammonia"
                                   class="form-control"
                                   step="0.1">
                        </div>

                        <div class="col-12 mb-3">
                            <label class="form-label">Notes</label>

                            <textarea name="notes"
                                      id="edit_notes"
                                      rows="4"
                                      class="form-control"></textarea>
                        </div>

                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal">
                        Cancel
                    </button>

                    <button type="submit"
                            class="btn btn-warning">
                        <i class="fa fa-save me-1"></i>
                        Update Log
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>


<!-- =========================
CREATE DAILY LOG MODAL
========================= -->
<div class="modal fade" id="createLogModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg overflow-hidden">

            <!-- Header -->
            <div class="modal-header bg-primary text-white border-0">
                <div>
                    <h4 class="mb-1 fw-bold">
                        <i class="fa fa-plus-circle me-2"></i>
                        New Daily Log
                    </h4>

                    <small class="opacity-75">
                        Record daily operational data
                    </small>
                </div>

                <button type="button"
                        class="btn-close btn-close-white"
                        data-bs-dismiss="modal"></button>
            </div>

            <!-- Form -->
            <form method="POST" action="{{ route('daily-logs.store') }}">
                @csrf

                <div class="modal-body bg-light">

                    <div class="row g-3">

                        <!-- Flock -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Select Flock
                            </label>

                            <select name="flock_id"
                                    class="form-select"
                                    required>

                                <option value="">
                                    Choose flock
                                </option>

                                @foreach($flocks as $flock)

                                    <option value="{{ $flock->id }}">
                                        {{ $flock->flock_number }}
                                    </option>

                                @endforeach

                            </select>
                        </div>

                        <!-- Date -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Log Date
                            </label>

                            <input type="date"
                                   name="log_date"
                                   class="form-control"
                                   value="{{ date('Y-m-d') }}"
                                   required>
                        </div>

                        <!-- Mortality -->
                        <div class="col-md-3">
                            <label class="form-label">
                                Mortality
                            </label>

                            <input type="number"
                                   name="mortality_count"
                                   class="form-control"
                                   value="0"
                                   min="0">
                        </div>

                        <!-- Culling -->
                        <div class="col-md-3">
                            <label class="form-label">
                                Culling
                            </label>

                            <input type="number"
                                   name="culling_count"
                                   class="form-control"
                                   value="0"
                                   min="0">
                        </div>

                        <!-- Eggs Collected -->
<div class="col-md-3">
    <label class="form-label">
        Eggs Collected
        <small class="text-muted fw-normal">(if applicable)</small>
    </label>
    <input type="number" name="eggs_collected" class="form-control"
           value="0" min="0" step="1" id="create_eggs_collected">
</div>

<!-- Eggs Damaged -->
<div class="col-md-3">
    <label class="form-label">
        Eggs Damaged
        <small class="text-muted fw-normal">(cracked/soiled)</small>
    </label>
    <input type="number" name="eggs_damaged" class="form-control"
           value="0" min="0" step="1" id="create_eggs_damaged">
</div>

                        <!-- Feed -->
                        <div class="col-md-3">
                            <label class="form-label">
                                Feed Intake (kg)
                            </label>

                            <input type="number"
                                   name="feed_intake_kg"
                                   class="form-control"
                                   step="0.1">
                        </div>

                        <!-- Water -->
                        <div class="col-md-3">
                            <label class="form-label">
                                Water (L)
                            </label>

                            <input type="number"
                                   name="water_consumption_liters"
                                   class="form-control"
                                   step="0.1">
                        </div>

                        <!-- Weight -->
                        <div class="col-md-4">
                            <label class="form-label">
                                Avg Weight (kg)
                            </label>

                            <input type="number"
                                   name="average_weight_kg"
                                   class="form-control"
                                   step="0.01">
                        </div>

                        <!-- Min Temp -->
                        <div class="col-md-4">
                            <label class="form-label">
                                Min Temp °C
                            </label>

                            <input type="number"
                                   name="min_temperature_c"
                                   class="form-control"
                                   step="0.1">
                        </div>

                        <!-- Max Temp -->
                        <div class="col-md-4">
                            <label class="form-label">
                                Max Temp °C
                            </label>

                            <input type="number"
                                   name="max_temperature_c"
                                   class="form-control"
                                   step="0.1">
                        </div>

                        <!-- Humidity -->
                        <div class="col-md-4">
                            <label class="form-label">
                                Min Humidity %
                            </label>

                            <input type="number"
                                   name="min_humidity"
                                   class="form-control"
                                   step="0.1">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">
                                Max Humidity %
                            </label>

                            <input type="number"
                                   name="max_humidity"
                                   class="form-control"
                                   step="0.1">
                        </div>

                        <!-- Ammonia -->
                        <div class="col-md-4">
                            <label class="form-label">
                                Ammonia (ppm)
                            </label>

                            <input type="number"
                                   name="ammonia_ppm"
                                   class="form-control"
                                   step="0.1">
                        </div>

                        <!-- Notes -->
                        <div class="col-12">
                            <label class="form-label">
                                Notes
                            </label>

                            <textarea name="notes"
                                      rows="4"
                                      class="form-control"
                                      placeholder="Enter observations, unusual behaviour, feed issues, disease signs, etc."></textarea>
                        </div>

                    </div>

                </div>

                <!-- Footer -->
                <div class="modal-footer bg-white border-0">

                    <button type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal">

                        Cancel
                    </button>

                    <button type="submit"
                            class="btn btn-primary">

                        <i class="fa fa-save me-1"></i>
                        Save Daily Log
                    </button>

                </div>

            </form>

        </div>
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ==========================================
    // FIX: Remove modal backdrop on close
    // ==========================================
    function removeModalBackdrop() {
        const backdrops = document.querySelectorAll('.modal-backdrop');
        backdrops.forEach(backdrop => backdrop.remove());
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
    }

    // Apply to all modals
    const modals = ['viewLogModal', 'editLogModal', 'createLogModal'];
    modals.forEach(modalId => {
        const modalElement = document.getElementById(modalId);
        if (modalElement) {
            modalElement.addEventListener('hidden.bs.modal', function () {
                removeModalBackdrop();
            });
        }
    });

    /*
    ==========================================
    VIEW MODAL
    ==========================================
    */
    document.querySelectorAll('.view-log-btn').forEach(button => {

        button.addEventListener('click', function () {

            const log = JSON.parse(this.dataset.log);

            document.getElementById('view_date').innerText =
                log.log_date ?? 'N/A';

            document.getElementById('view_mortality').innerText =
                log.mortality_count ?? 0;

            document.getElementById('view_culling').innerText =
                log.culling_count ?? 0;

            document.getElementById('view_feed').innerText =
                (log.feed_intake_kg ?? 0) + ' kg';

            document.getElementById('view_water').innerText =
                (log.water_consumption_liters ?? 0) + ' L';

            document.getElementById('view_weight').innerText =
                (log.average_weight_kg ?? 'N/A') + ' kg';

                document.getElementById('view_eggs_collected').innerText =
               (log.eggs_collected > 0) ? log.eggs_collected + ' pieces' : '—';

         document.getElementById('view_eggs_damaged').innerText =
        (log.eggs_damaged > 0) ? log.eggs_damaged + ' pieces' : '—';

            document.getElementById('view_temp').innerText =
                `${log.min_temperature_c ?? '-'}°C to ${log.max_temperature_c ?? '-'}°C`;

            document.getElementById('view_humidity').innerText =
                `${log.min_humidity ?? '-'}% to ${log.max_humidity ?? '-'}%`;

            document.getElementById('view_ammonia').innerText =
                (log.ammonia_ppm ?? 'N/A') + ' ppm';

            document.getElementById('view_notes').innerText =
                log.notes ?? 'No notes added';

            const modal = new bootstrap.Modal(document.getElementById('viewLogModal'));
            modal.show();
            
            // Clean up after modal closes
            document.getElementById('viewLogModal').addEventListener('hidden.bs.modal', function () {
                removeModalBackdrop();
            }, { once: true });
        });

    });

    /*
    ==========================================
    EDIT MODAL
    ==========================================
    */
    document.querySelectorAll('.edit-log-btn').forEach(button => {

        button.addEventListener('click', function () {

            const log = JSON.parse(this.dataset.log);

            document.getElementById('editLogForm').action =
                `/daily-logs/${log.id}`;

            document.getElementById('edit_mortality_count').value =
                log.mortality_count ?? 0;

            document.getElementById('edit_culling_count').value =
                log.culling_count ?? 0;

            document.getElementById('edit_feed').value =
                log.feed_intake_kg ?? '';

            document.getElementById('edit_water').value =
                log.water_consumption_liters ?? '';

            document.getElementById('edit_weight').value =
                log.average_weight_kg ?? '';

            document.getElementById('edit_min_temp').value =
                log.min_temperature_c ?? '';

            document.getElementById('edit_max_temp').value =
                log.max_temperature_c ?? '';

            document.getElementById('edit_ammonia').value =
                log.ammonia_ppm ?? '';

                document.getElementById('edit_eggs_collected').value =
                log.eggs_collected ?? 0;

         document.getElementById('edit_eggs_damaged').value =
          log.eggs_damaged ?? 0;

            document.getElementById('edit_notes').value =
                log.notes ?? '';

            const modal = new bootstrap.Modal(document.getElementById('editLogModal'));
            modal.show();
            
            document.getElementById('editLogModal').addEventListener('hidden.bs.modal', function () {
                removeModalBackdrop();
            }, { once: true });
        });

    });

    /*
    ==========================================
    CREATE MODAL
    ==========================================
    */
    const createModalElement = document.getElementById('createLogModal');
    if (createModalElement) {
        createModalElement.addEventListener('hidden.bs.modal', function () {
            removeModalBackdrop();
        });
    }

    /*
    ==========================================
    DELETE ALERT
    ==========================================
    */
    document.querySelectorAll('.delete-log-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const id = this.getAttribute('data-id');
            
            Swal.fire({
                title: 'Delete Daily Log?',
                html: 'This will <strong>restore mortality/culling counts</strong> to the flock.<br>This action cannot be undone!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fa fa-trash me-1"></i> Yes, Delete',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    const deleteForm = document.getElementById('deleteLogForm');
                    deleteForm.action = `/daily-logs/${id}`;
                    deleteForm.submit();
                }
            });
        });
    });
});
</script>
@endsection