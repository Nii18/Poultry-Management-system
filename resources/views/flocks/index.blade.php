{{-- resources/views/flocks/index.blade.php --}}
@extends('layouts.master')

@section('content')
<div class="container-fluid px-4">
    <!-- Page Header -->
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

    <!-- Stats Cards -->
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
                        <h3 class="stat-card-value">{{ $flocks->where('status', 'active')->count() }}</h3>
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
                        <h3 class="stat-card-value">{{ number_format($flocks->avg('mortality_rate'), 1) }}%</h3>
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
                        <i class="fas fa-list me-2 text-primary"></i>Flock Records
                    </h5>
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createFlockModal">
                        <i class="fas fa-plus me-2"></i>Create New Flock
                    </button>
                </div>
            </div>
        </div>
        
        <div class="card-body">
            <!-- Filters Section -->
            <div class="filter-section mb-4 p-3 bg-light rounded-3">
                <div class="row align-items-end g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold mb-2" style="color:#1e293b;">
                            <i class="fas fa-paw me-1" style="color:#64748b;"></i>Species
                        </label>
                        <select name="species_id" class="form-select" id="speciesFilter">
                            <option value="">All Species</option>
                            @foreach($species as $spec)
                                <option value="{{ $spec->id }}" {{ request('species_id') == $spec->id ? 'selected' : '' }}>
                                    {{ $spec->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold mb-2" style="color:#1e293b;">
                            <i class="fas fa-flag-checkered me-1" style="color:#64748b;"></i>Status
                        </label>
                        <select name="status" class="form-select" id="statusFilter">
                            <option value="active" {{ request('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed</option>
                            <option value="quarantined" {{ request('status') == 'quarantined' ? 'selected' : '' }}>Quarantined</option>
                            <option value="breeding" {{ request('status') == 'breeding' ? 'selected' : '' }}>Breeding</option>
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

            <!-- Flocks Table -->
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="py-3">Flock Number</th>
                            <th class="py-3">Species</th>
                            <th class="py-3">House</th>
                            <th class="py-3">Breed</th>
                            <th class="py-3">Start Date</th>
                            <th class="py-3">Age</th>
                            <th class="py-3">Population</th>
                            <th class="py-3">Mortality</th>
                            <th class="py-3">Status</th>
                            <th class="py-3 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($flocks as $flock)
                        <tr>
                            <td>
                                <button type="button" class="btn btn-link p-0 text-primary fw-semibold text-decoration-none view-flock-btn" 
                                        data-id="{{ $flock->id }}" data-bs-toggle="modal" data-bs-target="#viewFlockModal">
                                    {{ $flock->flock_number }}
                                </button>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span>{{ $flock->species->name ?? 'N/A' }}</span>
                                </div>
                            </td>
                            <td>{{ $flock->house->name ?? 'N/A' }}</td>
                            <td>{{ $flock->breed_variety }}</td>
                            <td>{{ $flock->start_date->format('d M Y') }}</td>
                            <td>
                                <span class="fw-semibold">{{ $flock->age_in_days }}</span>
                                <small class="text-muted">days</small>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-semibold">{{ number_format($flock->current_count) }}</span>
                                    <small class="text-muted">/ {{ number_format($flock->initial_count) }}</small>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height: 6px;">
                                        <div class="progress-bar bg-{{ $flock->mortality_rate > 5 ? 'danger' : 'success' }}" 
                                             style="width: {{ $flock->mortality_rate }}%"></div>
                                    </div>
                                    <span class="small fw-semibold">{{ $flock->mortality_rate }}%</span>
                                </div>
                            </td>
                            <td>
                                @php
                                    $statusColors = [
                                        'active' => 'success',
                                        'closed' => 'secondary',
                                        'quarantined' => 'danger',
                                        'breeding' => 'info',
                                    ];
                                    $statusColor = $statusColors[$flock->status] ?? 'secondary';
                                @endphp
                                <span class="badge bg-{{ $statusColor }}-soft text-{{ $statusColor }} px-3 py-2 rounded-pill">
                                    <i class="fas fa-circle me-1" style="font-size: 8px;"></i>
                                    {{ ucfirst($flock->status) }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group gap-1">

                                    <button type="button" class="btn btn-sm btn-outline-primary view-flock-btn" 
                                            data-id="{{ $flock->id }}" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#viewFlockModal"
                                            title="View Details">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                
                                    @if($flock->status === 'active')
                                
                                        <button type="button" class="btn btn-sm btn-outline-warning edit-flock-btn" 
                                                data-id="{{ $flock->id }}" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editFlockModal"
                                                title="Edit">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                
                                        <button type="button" class="btn btn-sm btn-outline-success close-flock-btn" 
                                                data-id="{{ $flock->id }}"
                                                data-flock-number="{{ $flock->flock_number }}"
                                                data-initial-count="{{ $flock->initial_count }}"
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
                            <td colspan="10" class="text-center py-5">
                                <div class="empty-state">
                                    <i class="fas fa-tractor fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">No Flocks Found</h5>
                                    <p class="text-muted mb-3">Get started by creating your first flock</p>
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createFlockModal">
                                        <i class="fas fa-plus me-2"></i>Create New Flock
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

          <!-- Enhanced Pagination -->
@if($flocks->hasPages())
<div class="d-flex justify-content-between align-items-center mt-4">
    <div class="text-muted">
        Showing {{ $flocks->firstItem() }} to {{ $flocks->lastItem() }} of {{ $flocks->total() }} flocks
    </div>
    <nav>
        <ul class="pagination mb-0">
            @if($flocks->onFirstPage())
                <li class="page-item disabled">
                    <span class="page-link">‹ Previous</span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $flocks->previousPageUrl() }}" rel="prev">‹ Previous</a>
                </li>
            @endif

            @php
                $current = $flocks->currentPage();
                $last = $flocks->lastPage();
                $start = max(1, $current - 2);
                $end = min($last, $current + 2);
            @endphp

            @if($start > 1)
                <li class="page-item">
                    <a class="page-link" href="{{ $flocks->url(1) }}">1</a>
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
                        <a class="page-link" href="{{ $flocks->url($page) }}">{{ $page }}</a>
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
                    <a class="page-link" href="{{ $flocks->url($last) }}">{{ $last }}</a>
                </li>
            @endif

            @if($flocks->hasMorePages())
                <li class="page-item">
                    <a class="page-link" href="{{ $flocks->nextPageUrl() }}" rel="next">Next ›</a>
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





        </div>
    </div>
</div>

<!-- View Flock Modal -->
<div class="modal fade" id="viewFlockModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white">
                    <i class="fas fa-eye me-2"></i>Flock Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewFlockContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading flock details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Flock Modal -->
<div class="modal fade" id="editFlockModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title text-white">
                    <i class="fas fa-edit me-2"></i>Edit Flock
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="editFlockContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-warning" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading flock details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="saveEditFlock">Update Flock</button>
            </div>
        </div>
    </div>
</div>

<!-- Create Flock Modal -->
<div class="modal fade" id="createFlockModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title text-white">
                    <i class="fas fa-plus-circle me-2"></i>Create New Flock
                </h5>

                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form id="createFlockForm">
                @csrf

                <div class="modal-body">
                    <div class="row">

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Species <span class="text-danger">*</span></label>

                            <select name="species_id" class="form-select" required>
                                <option value="">Select Species</option>

                                @foreach($species as $spec)
                                    <option value="{{ $spec->id }}">
                                        {{ $spec->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">House <span class="text-danger">*</span></label>

                            <select name="house_id" class="form-select" required>
                                <option value="">Select House</option>

                                @foreach(\App\Models\House::where('status', 'active')->get() as $house)
                                    <option value="{{ $house->id }}">
                                        {{ $house->name }} (Capacity: {{ number_format($house->capacity) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Breed/Variety <span class="text-danger">*</span></label>

                            <input type="text" name="breed_variety" class="form-control" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Start Date <span class="text-danger">*</span></label>

                            <input type="date" name="start_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Initial Count <span class="text-danger">*</span></label>

                            <input type="number" name="initial_count" class="form-control" min="1" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Source</label>

                            <input type="text" name="source" class="form-control" placeholder="Hatchery, breeder farm etc.">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Production Type</label>

                            <select name="production_type" class="form-select">
                                <option value="meat">Meat</option>
                                <option value="eggs">Eggs</option>
                                <option value="milk">Milk</option>
                                <option value="breeding">Breeding</option>
                                <option value="dual_purpose">Dual Purpose</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Parity Number</label>

                            <input type="number" name="parity_number" class="form-control" min="0">
                        </div>

                        <div class="col-12 mb-3">
                            <div class="form-check">
                                <input type="checkbox" name="is_breeding_stock" value="1" class="form-check-input" id="create_is_breeding_stock">

                                <label class="form-check-label" for="create_is_breeding_stock">
                                    This flock is for breeding stock
                                </label>
                            </div>
                        </div>

                        <div class="col-12 mb-3">
                            <label class="form-label">Notes</label>

                            <textarea name="notes" class="form-control" rows="3"></textarea>
                        </div>

                    </div>
                </div>

                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancel
                    </button>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Create Flock
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<!-- Hidden form for close flock (SweetAlert) -->
<form id="closeFlockForm" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="end_date" id="close_end_date">
    <input type="hidden" name="final_count" id="close_final_count">
    <input type="hidden" name="total_weight_kg" id="close_total_weight_kg">
    <input type="hidden" name="average_price_per_kg" id="close_average_price_per_kg">
</form>

@push('styles')
<style>
    /* Page Header Styles */
    .page-header {
        margin-bottom: 1.5rem;
    }
    
    .page-icon {
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #e8f4f8 0%, #d1e9f0 100%);
        border-radius: 12px;
    }
    
    .page-title {
        font-size: 1.75rem;
        font-weight: 600;
        color: #1e293b;
    }
    
    .page-description {
        font-size: 0.875rem;
    }
    
    /* Stat Cards */
    .stat-card {
        background: white;
        border-radius: 16px;
        padding: 1rem;
        transition: all 0.3s ease;
        border: 1px solid #e2e8f0;
    }
    
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.05);
    }
    
    .stat-card-body {
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    
    .stat-card-icon {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        font-size: 1.5rem;
    }
    
    .bg-primary-soft { background: #e0f2fe; }
    .bg-success-soft { background: #dcfce7; }
    .bg-warning-soft { background: #fef3c7; }
    .bg-info-soft { background: #d1fae5; }
    
    .stat-card-info {
        flex: 1;
    }
    
    .stat-card-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #64748b;
        font-weight: 600;
    }
    
    .stat-card-value {
        font-size: 1.75rem;
        font-weight: 700;
        margin: 0;
        line-height: 1.2;
        color: #1e293b;
    }
    
    /* Filter Section */
    .filter-section {
        background: #f8fafc;
        border-radius: 12px;
    }
    
    /* Table Styles */
    .table th {
        font-weight: 600;
        font-size: 0.875rem;
        color: #475569;
        border-bottom-width: 1px;
    }
    
    .table td {
        font-size: 0.875rem;
        color: #334155;
        vertical-align: middle;
    }
    
    /* Badge Styles */
    .bg-success-soft {
        background-color: #dcfce7;
        color: #166534;
    }
    
    .bg-secondary-soft {
        background-color: #f1f5f9;
        color: #475569;
    }
    
    .bg-danger-soft {
        background-color: #fee2e2;
        color: #991b1b;
    }
    
    .bg-info-soft {
        background-color: #d1fae5;
        color: #065f46;
    }
    
    .badge {
        font-weight: 500;
        font-size: 0.75rem;
    }
    
    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 2rem;
    }
    
    /* Progress Bar */
    .progress {
        background-color: #e2e8f0;
        border-radius: 10px;
        overflow: hidden;
    }
    
    /* Button Group */
    .btn-group .btn {
        border-radius: 8px !important;
        margin: 0 2px;
        padding: 0.25rem 0.5rem;
    }
    
    /* Pagination */
    .pagination {
        margin-bottom: 0;
    }
    
    .page-link {
        border-radius: 8px;
        margin: 0 2px;
        border: none;
        color: #475569;
        padding: 0.5rem 0.875rem;
    }
    
    .page-item.active .page-link {
        background-color: #0d6e4f;
        color: white;
    }
    
    .page-link:hover {
        background-color: #e2e8f0;
        color: #0d6e4f;
    }
    
    /* Modal Styles */
    .modal-header {
        padding: 1rem 1.5rem;
    }
    
    .modal-body {
        padding: 1.5rem;
        max-height: 70vh;
        overflow-y: auto;
    }
    
    .detail-section {
        margin-bottom: 1.5rem;
    }
    
    .detail-section h6 {
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #e2e8f0;
    }
    
    .detail-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1rem;
    }
    
    .detail-item {
        display: flex;
        flex-direction: column;
    }
    
    .detail-label {
        font-size: 0.7rem;
        text-transform: uppercase;
        color: #64748b;
        font-weight: 600;
        margin-bottom: 0.25rem;
    }
    
    .detail-value {
        font-size: 1rem;
        font-weight: 500;
        color: #1e293b;
    }

    /* SweetAlert Custom Styles */
    .swal2-popup {
        border-radius: 16px !important;
    }

    .swal2-html-container .form-control {
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        padding: 8px 12px;
        width: 100%;
        box-sizing: border-box;
    }

    .swal2-html-container .form-control:focus {
        border-color: #0d6e4f;
        outline: none;
        box-shadow: 0 0 0 2px rgba(13, 110, 79, 0.1);
    }

    .swal2-html-container .alert {
        border-radius: 10px;
        font-size: 14px;
    }
    
    /* Fix for row alignment */
    .swal2-html-container .row {
        display: flex;
        flex-wrap: wrap;
        margin: 0 -8px;
    }
    
    .swal2-html-container .col-md-6 {
        flex: 0 0 50%;
        max-width: 50%;
        padding: 0 8px;
        box-sizing: border-box;
    }
    
    .swal2-html-container .mb-2 {
        margin-bottom: 0.5rem;
    }
    
    .swal2-html-container .mb-3 {
        margin-bottom: 1rem;
    }
    
    .swal2-html-container .mb-4 {
        margin-bottom: 1.5rem;
    }
    
    .swal2-html-container .form-label {
        display: block;
        margin-bottom: 0.5rem;
    }
    
    .swal2-html-container .text-muted {
        font-size: 0.75rem;
        color: #6c757d;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.getElementById('applyFilters')?.addEventListener('click', function() {
        const speciesId = document.getElementById('speciesFilter').value;
        const status = document.getElementById('statusFilter').value;
        let url = '{{ route("flocks.index") }}';
        const params = new URLSearchParams();
        
        if (speciesId) params.append('species_id', speciesId);
        if (status) params.append('status', status);
        
        if (params.toString()) {
            url += '?' + params.toString();
        }
        
        window.location.href = url;
    });

    // View Flock Modal - Load content via AJAX
    document.querySelectorAll('.view-flock-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const flockId = this.dataset.id;
            const modalBody = document.getElementById('viewFlockContent');
            
            modalBody.innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2">Loading flock details...</p>
                </div>
            `;
            
            fetch(`/flocks/${flockId}/details`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        displayFlockDetails(data.flock, data.summary);
                    } else {
                        modalBody.innerHTML = `<div class="alert alert-danger">Failed to load flock details: ${data.message || 'Unknown error'}</div>`;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    modalBody.innerHTML = `<div class="alert alert-danger">Error loading data: ${error.message}</div>`;
                });
        });
    });
    
    function displayFlockDetails(flock, summary) {
        const statusClass = {
            'active': 'badge bg-success',
            'closed': 'badge bg-secondary',
            'quarantined': 'badge bg-danger',
            'breeding': 'badge bg-info'
        };
        
        document.getElementById('viewFlockContent').innerHTML = `
            <div class="detail-section">
                <h6>Basic Information</h6>
                <div class="detail-grid">
                    <div class="detail-item">
                        <span class="detail-label">Flock Number</span>
                        <span class="detail-value">${escapeHtml(flock.flock_number)}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Species</span>
                        <span class="detail-value">${escapeHtml(flock.species_name)} (${escapeHtml(flock.species_code)})</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">House</span>
                        <span class="detail-value">${escapeHtml(flock.house_name)} (${escapeHtml(flock.house_code)})</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Breed/Variety</span>
                        <span class="detail-value">${escapeHtml(flock.breed_variety)}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Start Date</span>
                        <span class="detail-value">${escapeHtml(flock.start_date)}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Source</span>
                        <span class="detail-value">${escapeHtml(flock.source || 'N/A')}</span>
                    </div>
                </div>
            </div>
            
            <div class="detail-section">
                <h6>Performance Metrics</h6>
                <div class="detail-grid">
                    <div class="detail-item">
                        <span class="detail-label">Age</span>
                        <span class="detail-value">${summary.age_days} days (${summary.age_weeks} weeks)</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Current Count</span>
                        <span class="detail-value">${summary.current_count.toLocaleString()} / ${flock.initial_count.toLocaleString()}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Mortality Rate</span>
                        <span class="detail-value ${summary.mortality_rate > 5 ? 'text-danger' : 'text-success'}">${summary.mortality_rate}% (Survival: ${summary.survival_rate}%)</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Feed Conversion Ratio</span>
                        <span class="detail-value">${summary.fcr}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Total Feed Consumed</span>
                        <span class="detail-value">${summary.total_feed.toLocaleString()} kg</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Average Daily Gain</span>
                        <span class="detail-value">${summary.avg_daily_gain} kg</span>
                    </div>
                </div>
            </div>
            
            <div class="detail-section">
                <h6>Production Information</h6>
                <div class="detail-grid">
                    <div class="detail-item">
                        <span class="detail-label">Production Type</span>
                        <span class="detail-value">${flock.production_type.charAt(0).toUpperCase() + flock.production_type.slice(1)}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Breeding Stock</span>
                        <span class="detail-value">${flock.is_breeding_stock ? 'Yes' : 'No'}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Parity Number</span>
                        <span class="detail-value">${flock.parity_number || 'N/A'}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Status</span>
                        <span class="${statusClass[flock.status]}">${flock.status.charAt(0).toUpperCase() + flock.status.slice(1)}</span>
                    </div>
                </div>
            </div>
            
            ${flock.notes ? `
            <div class="detail-section">
                <h6>Notes</h6>
                <p class="mb-0">${escapeHtml(flock.notes)}</p>
            </div>
            ` : ''}
        `;
    }
    
    // Edit Flock Modal - Load content via AJAX
    document.querySelectorAll('.edit-flock-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const flockId = this.dataset.id;
            const modalBody = document.getElementById('editFlockContent');
            
            modalBody.innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border text-warning" role="status"></div>
                    <p class="mt-2">Loading flock details...</p>
                </div>
            `;
            
            fetch(`/flocks/${flockId}/edit-data`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        displayEditForm(data.flock, data.houses);
                        window.currentEditFlockId = flockId;
                    } else {
                        modalBody.innerHTML = `<div class="alert alert-danger">Failed to load flock details: ${data.message || 'Unknown error'}</div>`;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    modalBody.innerHTML = `<div class="alert alert-danger">Error loading data: ${error.message}</div>`;
                });
        });
    });
    
    function displayEditForm(flock, houses) {
        const housesOptions = houses.map(house => 
            `<option value="${house.id}" ${flock.house_id == house.id ? 'selected' : ''}>${escapeHtml(house.name)} (Capacity: ${house.capacity.toLocaleString()})</option>`
        ).join('');
        
        document.getElementById('editFlockContent').innerHTML = `
            <form id="editFlockForm">
                <input type="hidden" name="id" value="${flock.id}">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Breed/Variety <span class="text-danger">*</span></label>
                        <input type="text" name="breed_variety" class="form-control" value="${escapeHtml(flock.breed_variety)}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">House <span class="text-danger">*</span></label>
                        <select name="house_id" class="form-select" required>
                            <option value="">Select House</option>
                            ${housesOptions}
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Production Type</label>
                        <select name="production_type" class="form-select">
                            <option value="meat" ${flock.production_type === 'meat' ? 'selected' : ''}>Meat</option>
                            <option value="eggs" ${flock.production_type === 'eggs' ? 'selected' : ''}>Eggs</option>
                            <option value="milk" ${flock.production_type === 'milk' ? 'selected' : ''}>Milk</option>
                            <option value="breeding" ${flock.production_type === 'breeding' ? 'selected' : ''}>Breeding</option>
                            <option value="dual_purpose" ${flock.production_type === 'dual_purpose' ? 'selected' : ''}>Dual Purpose</option>
                        </select>
                    </div>
                    <div class="col-12 mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="is_breeding_stock" value="1" class="form-check-input" id="is_breeding_stock" ${flock.is_breeding_stock ? 'checked' : ''}>
                            <label class="form-check-label" for="is_breeding_stock">This flock is for breeding stock</label>
                        </div>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label fw-semibold">Notes</label>
                        <textarea name="notes" class="form-control" rows="3">${escapeHtml(flock.notes || '')}</textarea>
                    </div>
                </div>
            </form>
        `;
    }
    
    function escapeHtml(str) {
        if (!str) return '';
        return str.replace(/[&<>]/g, function(m) {
            if (m === '&') return '&amp;';
            if (m === '<') return '&lt;';
            if (m === '>') return '&gt;';
            return m;
        });
    }
    
    // Save Edit Flock
    document.getElementById('saveEditFlock')?.addEventListener('click', function() {
        const form = document.getElementById('editFlockForm');
        const formData = new FormData(form);
        const data = {};
        formData.forEach((value, key) => { data[key] = value; });
        
        fetch(`/flocks/${window.currentEditFlockId}`, {
            method: 'PUT',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Updated!',
                    text: 'Flock updated successfully',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    window.location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message || 'Failed to update flock'
                });
            }
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred while updating the flock'
            });
        });
    });
    
    // Close Flock - Clean Simple Modal
    document.querySelectorAll('.close-flock-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const flockId = this.dataset.id;
            const flockNumber = this.dataset.flockNumber;
            const initialCount = parseInt(this.dataset.initialCount);

            Swal.fire({
                title: '<span style="color: #1e293b;"><i class="fas fa-check-circle text-success me-2"></i> Close Flock</span>',
                html: `
                    <div class="text-start" style="padding: 0.5rem;">
                        <div class="alert alert-warning mb-4" style="background: #fef3c7; border: none; border-radius: 10px; color: #92400e;">
                            <i class="fas fa-info-circle me-2"></i>
                            You are closing flock: <strong style="color: #92400e;">${escapeHtml(flockNumber)}</strong>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold mb-2" style="color: #1e293b;">
                                <i class="fas fa-calendar-alt text-muted me-1"></i> End Date <span class="text-danger">*</span>
                            </label>
                            <input type="date" id="swal_end_date" class="form-control" 
                                   value="${new Date().toISOString().split('T')[0]}">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold mb-2" style="color: #1e293b;">
                                <i class="fas fa-chart-line text-muted me-1"></i> Final Count <span class="text-danger">*</span>
                            </label>
                            <input type="number" id="swal_final_count" class="form-control" 
                                   min="0" max="${initialCount}" placeholder="Enter final count">
                            <small class="text-muted d-block mt-1">
                                Maximum: ${initialCount.toLocaleString()} animals
                            </small>
                        </div>
                        
                        <div style="display: flex; gap: 16px; margin-bottom: 12px;">
                            <div style="flex: 1;">
                                <label class="form-label fw-semibold mb-2" style="color: #1e293b; display: block;">
                                    <i class="fas fa-weight-hanging text-muted me-1"></i> Total Weight (kg) <span class="text-danger">*</span>
                                </label>
                                <input type="number" id="swal_total_weight_kg" class="form-control" 
                                       step="0.01" min="0" placeholder="Enter total weight" style="width: 100%;">
                            </div>
                            <div style="flex: 1;">
                                <label class="form-label fw-semibold mb-2" style="color: #1e293b; display: block;">
                                    <i class="fas fa-tag text-muted me-1"></i> Avg Price per kg <span class="text-danger">*</span>
                                </label>
                                <input type="number" id="swal_average_price_per_kg" class="form-control" 
                                       step="0.01" min="0" placeholder="Enter price" style="width: 100%;">
                            </div>
                        </div>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-check-circle me-2"></i>Yes, close flock!',
                cancelButtonText: '<i class="fas fa-times me-2"></i>Cancel',
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                width: '500px',
                padding: '1.5rem',
                allowOutsideClick: false,
                
                preConfirm: () => {
                    const endDate = document.getElementById('swal_end_date').value;
                    const finalCount = document.getElementById('swal_final_count').value;
                    const totalWeightKg = document.getElementById('swal_total_weight_kg').value;
                    const averagePricePerKg = document.getElementById('swal_average_price_per_kg').value;
                    
                    if (!endDate) {
                        Swal.showValidationMessage('Please select an end date');
                        return false;
                    }
                    if (!finalCount || finalCount < 0 || finalCount > initialCount) {
                        Swal.showValidationMessage(`Final count must be between 0 and ${initialCount.toLocaleString()}`);
                        return false;
                    }
                    if (!totalWeightKg || totalWeightKg < 0) {
                        Swal.showValidationMessage('Please enter total weight');
                        return false;
                    }
                    if (!averagePricePerKg || averagePricePerKg < 0) {
                        Swal.showValidationMessage('Please enter average price per kg');
                        return false;
                    }
                    
                    return { endDate, finalCount, totalWeightKg, averagePricePerKg };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.getElementById('closeFlockForm');
                    document.getElementById('close_end_date').value = result.value.endDate;
                    document.getElementById('close_final_count').value = result.value.finalCount;
                    document.getElementById('close_total_weight_kg').value = result.value.totalWeightKg;
                    document.getElementById('close_average_price_per_kg').value = result.value.averagePricePerKg;
                    form.action = `/flocks/${flockId}/close`;
                    form.submit();
                }
            });
        });
    });

    // Create Flock AJAX
    document.getElementById('createFlockForm')?.addEventListener('submit', function(e) {
        e.preventDefault();

        const form = this;
        const formData = new FormData(form);

        fetch("{{ route('flocks.store') }}", {
            method: "POST",
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(async response => {
            if (!response.ok) {
                const errorData = await response.json();

                if (errorData.errors) {
                    let errorMessages = '';
                    Object.values(errorData.errors).forEach(errors => {
                        errorMessages += errors.join('<br>') + '<br>';
                    });
                    throw new Error(errorMessages);
                }
                throw new Error('Failed to create flock');
            }
            return response.json();
        })
        .then(data => {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: 'Flock created successfully',
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                window.location.reload();
            });
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                html: error.message
            });
        });
    });

    // Delete Flock
    document.querySelectorAll('.delete-flock-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const flockId = this.dataset.id;
            const flockNumber = this.dataset.flockNumber;

            Swal.fire({
                title: 'Delete Flock?',
                html: `
                    <p>
                        Are you sure you want to delete flock:
                        <strong>${flockNumber}</strong>?
                    </p>
                    <p class="text-danger mb-0">
                        This action cannot be undone.
                    </p>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Delete',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`/flocks/${flockId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Failed to delete flock');
                        }
                        return response.text();
                    })
                    .then(() => {
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: 'Flock deleted successfully',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.reload();
                        });
                    })
                    .catch(error => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: error.message
                        });
                    });
                }
            });
        });
    });
</script>
@endpush
@endsection