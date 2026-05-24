{{-- resources/views/houses/index.blade.php --}}
@extends('layouts.master')

@section('content')
<div class="container-fluid px-4">
    <!-- Page Header -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <div class="d-flex align-items-center gap-3">
                    <div class="page-icon">
                        <i class="fas fa-home fs-1 text-primary"></i>
                    </div>
                    <div>
                        <h1 class="page-title mb-1">Houses Management</h1>
                        <p class="page-description text-muted mb-0">Manage all your farm houses and facilities</p>
                    </div>
                </div>
            </div>
            <div class="col-auto">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Houses</li>
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
                        <i class="fas fa-building text-primary"></i>
                    </div>
                    <div class="stat-card-info">
                        <span class="stat-card-label">Total Houses</span>
                        <h3 class="stat-card-value">{{ $houses->total() }}</h3>
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
                        <span class="stat-card-label">Active Houses</span>
                        <h3 class="stat-card-value">{{ $houses->where('status', 'active')->count() }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-card-icon bg-warning-soft">
                        <i class="fas fa-tools text-warning"></i>
                    </div>
                    <div class="stat-card-info">
                        <span class="stat-card-label">Maintenance</span>
                        <h3 class="stat-card-value">{{ $houses->where('status', 'maintenance')->count() }}</h3>
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
                        <span class="stat-card-label">Total Capacity</span>
                        <h3 class="stat-card-value">{{ number_format($houses->sum('capacity')) }}</h3>
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
                        <i class="fas fa-list me-2 text-primary"></i>House Records
                    </h5>
                </div>
                <div class="col-auto">
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createHouseModal">
                            <i class="fas fa-plus me-2"></i>New House
                        </button>
                        <a href="{{ route('houses.occupancy-report') }}" class="btn btn-outline-info">
                            <i class="fas fa-chart-bar me-2"></i>Occupancy Report
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card-body">
            <!-- Filters -->
            <form method="GET" action="{{ route('houses.index') }}" class="row mb-4">
                <div class="col-md-4 mb-2">
                    <label class="form-label fw-semibold">Species</label>
                    <select name="species_id" class="form-select">
                        <option value="">All Species</option>
                        @foreach($species as $spec)
                            <option value="{{ $spec->id }}" {{ request('species_id') == $spec->id ? 'selected' : '' }}>
                                {{ $spec->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-2">
                    <label class="form-label fw-semibold">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="maintenance" {{ request('status') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                        <option value="cleaning" {{ request('status') == 'cleaning' ? 'selected' : '' }}>Cleaning</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-4 mb-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">Filter</button>
                    <a href="{{ route('houses.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>

            <!-- Houses Table -->
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="py-3">Name</th>
                            <th class="py-3">Code</th>
                            <th class="py-3">Species</th>
                            <th class="py-3">Capacity</th>
                            <th class="py-3">Dimensions</th>
                            <th class="py-3">Equipment</th>
                            <th class="py-3">Status</th>
                            <th class="py-3 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($houses as $house)
                        <tr>
                            <td>
                                <button type="button" class="btn btn-link p-0 text-primary fw-semibold text-decoration-none view-house-btn" 
                                        data-id="{{ $house->id }}" data-bs-toggle="modal" data-bs-target="#viewHouseModal">
                                    {{ $house->name }}
                                </button>
                            </td>
                            <td><code>{{ $house->house_code }}</code></td>
                            <td>
                                @if($house->species)
                                    <div class="d-flex align-items-center gap-2">
                                       
                                        {{ $house->species->name }}
                                    </div>
                                @else
                                    <span class="text-muted">Not Assigned</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-semibold">{{ number_format($house->capacity) }}</span>
                                    <small class="text-muted">animals</small>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span>{{ $house->length_m }}m × {{ $house->width_m }}m</span>
                                    <small class="text-muted">{{ number_format($house->length_m * $house->width_m, 1) }} m²</small>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex gap-1 flex-wrap">
                                    <span class="badge badge-info">F:{{ $house->feeders_count }}</span>
                                            <span class="badge badge-info">D:{{ $house->drinkers_count }}</span>
                                    
                                </div>
                            </td>
                            <td>
                                @php
                                    $statusStyles = [
                                        'active' => ['class' => 'success', 'icon' => 'fa-check-circle'],
                                        'maintenance' => ['class' => 'warning', 'icon' => 'fa-tools'],
                                        'cleaning' => ['class' => 'info', 'icon' => 'fa-broom'],
                                        'inactive' => ['class' => 'secondary', 'icon' => 'fa-ban'],
                                    ];
                                    $style = $statusStyles[$house->status] ?? $statusStyles['inactive'];
                                @endphp
                                <span class="badge bg-{{ $style['class'] }}-soft text-{{ $style['class'] }} px-3 py-2 rounded-pill">
                                    <i class="fas {{ $style['icon'] }} me-1"></i>
                                    {{ ucfirst($house->status) }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group gap-1">
                                    <button type="button" class="btn btn-sm btn-outline-primary view-house-btn" 
                                            data-id="{{ $house->id }}" data-bs-toggle="modal" data-bs-target="#viewHouseModal" title="View Details">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-warning edit-house-btn" 
                                            data-id="{{ $house->id }}" data-bs-toggle="modal" data-bs-target="#editHouseModal" title="Edit">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    @if(auth()->user()->role === 'admin')
                                        <button type="button" class="btn btn-sm btn-outline-danger delete-house-btn" 
                                                data-id="{{ $house->id }}" data-house-name="{{ $house->name }}" title="Delete">
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
                                    <i class="fas fa-home fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">No Houses Found</h5>
                                    <p class="text-muted mb-3">Get started by creating your first house</p>
                                    <a href="{{ route('houses.create') }}" class="btn btn-primary">
                                        <i class="fas fa-plus me-2"></i>Create New House
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($houses->hasPages())
            <div class="d-flex justify-content-between align-items-center mt-4 pt-2">
                <div class="text-muted small">
                    Showing {{ $houses->firstItem() ?? 0 }} to {{ $houses->lastItem() ?? 0 }} of {{ $houses->total() }} results
                </div>
                <div>
                    {{ $houses->withQueryString()->links() }}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- View House Modal -->
<div class="modal fade" id="viewHouseModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white">
                    <i class="fas fa-eye me-2"></i>House Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewHouseContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading house details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit House Modal -->
<div class="modal fade" id="editHouseModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title text-white">
                    <i class="fas fa-edit me-2"></i>Edit House
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="editHouseContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-warning" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading house details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="saveEditHouse">Update House</button>
            </div>
        </div>
    </div>
</div>

<!-- Create House Modal -->
<div class="modal fade" id="createHouseModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">

            <!-- Header -->
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title text-white">
                    <i class="fas fa-plus-circle me-2"></i>Create New House
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <!-- Body -->
            <div class="modal-body px-4 py-4" id="createHouseContent">

                <form id="createHouseForm">
                    @csrf

                    <div class="row g-3">

                        <!-- House Name -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                House Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="name" class="form-control" required>
                        </div>

                        <!-- House Code -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                House Code <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="house_code" class="form-control"
                                   placeholder="e.g. H01, BARN-A" required>
                        </div>

                        <!-- Species -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Species</label>
                            <select name="species_id" class="form-select">
                                <option value="">Not Assigned</option>

                                @foreach($species as $spec)
                                    <option value="{{ $spec->id }}">
                                        {{ $spec->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Capacity -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Capacity</label>
                            <input type="number" name="capacity"
                                   class="form-control" min="0" value="0">
                        </div>

                        <!-- Dimensions -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Length (m)</label>
                            <input type="number" name="length_m"
                                   class="form-control" step="0.01" min="0">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Width (m)</label>
                            <input type="number" name="width_m"
                                   class="form-control" step="0.01" min="0">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Height (m)</label>
                            <input type="number" name="height_m"
                                   class="form-control" step="0.01" min="0">
                        </div>

                        <!-- Equipment -->
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Feeders</label>
                            <input type="number" name="feeders_count"
                                   class="form-control" min="0" value="0">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Drinkers</label>
                            <input type="number" name="drinkers_count"
                                   class="form-control" min="0" value="0">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Fans</label>
                            <input type="number" name="fans_count"
                                   class="form-control" min="0" value="0">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Heaters</label>
                            <input type="number" name="heaters_count"
                                   class="form-control" min="0" value="0">
                        </div>

                        <!-- Status -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Status <span class="text-danger">*</span>
                            </label>

                            <select name="status" class="form-select" required>
                                <option value="active">Active</option>
                                <option value="maintenance">Maintenance</option>
                                <option value="cleaning">Cleaning</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>

                        <!-- Notes -->
                        <div class="col-12">
                            <label class="form-label fw-semibold">Notes</label>
                            <textarea name="notes"
                                      class="form-control"
                                      rows="3"></textarea>
                        </div>

                    </div>
                </form>

            </div>

            <!-- Footer -->
            <div class="modal-footer border-0">
                <button type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">
                    Cancel
                </button>

                <button type="button"
                        class="btn btn-primary"
                        id="saveCreateHouse">
                    <i class="fas fa-save me-2"></i>Create House
                </button>
            </div>

        </div>
    </div>
</div>

<!-- Hidden form for delete (SweetAlert) -->
<form id="deleteHouseForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
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
    
    .bg-warning-soft {
        background-color: #fef3c7;
        color: #92400e;
    }
    
    .bg-info-soft {
        background-color: #d1fae5;
        color: #065f46;
    }
    
    .bg-secondary-soft {
        background-color: #f1f5f9;
        color: #475569;
    }
    
    .badge {
        font-weight: 500;
        font-size: 0.75rem;
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
    
    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 2rem;
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
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
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
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // View House Modal - Load content via AJAX
    document.querySelectorAll('.view-house-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const houseId = this.dataset.id;
            const modalBody = document.getElementById('viewHouseContent');
            
            modalBody.innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2">Loading house details...</p>
                </div>
            `;
            
            fetch(`/houses/${houseId}/details`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        displayHouseDetails(data.house, data.stats, data.currentFlock);
                    } else {
                        modalBody.innerHTML = `<div class="alert alert-danger">Failed to load house details: ${data.message || 'Unknown error'}</div>`;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    modalBody.innerHTML = `<div class="alert alert-danger">Error loading data: ${error.message}</div>`;
                });
        });
    });
    
    function displayHouseDetails(house, stats, currentFlock) {
        const statusClass = {
            'active': 'badge bg-success',
            'maintenance': 'badge bg-warning',
            'cleaning': 'badge bg-info',
            'inactive': 'badge bg-secondary'
        };
        
        document.getElementById('viewHouseContent').innerHTML = `
            <div class="detail-section">
                <h6>Basic Information</h6>
                <div class="detail-grid">
                    <div class="detail-item">
                        <span class="detail-label">House Name</span>
                        <span class="detail-value">${escapeHtml(house.name)}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">House Code</span>
                        <span class="detail-value"><code>${escapeHtml(house.house_code)}</code></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Species</span>
                        <span class="detail-value">${escapeHtml(house.species_name || 'Not Assigned')}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Status</span>
                        <span class="${statusClass[house.status]}">${house.status.charAt(0).toUpperCase() + house.status.slice(1)}</span>
                    </div>
                </div>
            </div>
            
            <div class="detail-section">
                <h6>Statistics</h6>
                <div class="detail-grid">
                    <div class="detail-item">
                        <span class="detail-label">Total Flocks</span>
                        <span class="detail-value">${stats.total_flocks}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Completed Flocks</span>
                        <span class="detail-value">${stats.completed_flocks}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Total Animals</span>
                        <span class="detail-value">${stats.total_animals.toLocaleString()}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Capacity</span>
                        <span class="detail-value">${house.capacity.toLocaleString()} animals</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Occupancy Rate</span>
                        <span class="detail-value">${stats.occupancy_rate}%</span>
                    </div>
                </div>
            </div>
            
            <div class="detail-section">
                <h6>Dimensions & Equipment</h6>
                <div class="detail-grid">
                    <div class="detail-item">
                        <span class="detail-label">Dimensions</span>
                        <span class="detail-value">${house.length_m}m × ${house.width_m}m × ${house.height_m}m</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Area</span>
                        <span class="detail-value">${(house.length_m * house.width_m).toFixed(2)} m²</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Feeders / Drinkers</span>
                        <span class="detail-value">${house.feeders_count} / ${house.drinkers_count}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Fans / Heaters</span>
                        <span class="detail-value">${house.fans_count} / ${house.heaters_count}</span>
                    </div>
                </div>
            </div>
            
            ${currentFlock ? `
            <div class="detail-section">
                <h6>Current Flock</h6>
                <div class="detail-grid">
                    <div class="detail-item">
                        <span class="detail-label">Flock Number</span>
                        <span class="detail-value">${escapeHtml(currentFlock.flock_number)}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Species</span>
                        <span class="detail-value">${escapeHtml(currentFlock.species_name)}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Breed</span>
                        <span class="detail-value">${escapeHtml(currentFlock.breed_variety)}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Age</span>
                        <span class="detail-value">${currentFlock.age_days} days</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Count</span>
                        <span class="detail-value">${currentFlock.current_count.toLocaleString()}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Mortality Rate</span>
                        <span class="detail-value">${currentFlock.mortality_rate}%</span>
                    </div>
                </div>
            </div>
            ` : ''}
            
            ${house.notes ? `
            <div class="detail-section">
                <h6>Notes</h6>
                <p class="mb-0">${escapeHtml(house.notes)}</p>
            </div>
            ` : ''}
        `;
    }
    
    // Edit House Modal - Load content via AJAX
    document.querySelectorAll('.edit-house-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const houseId = this.dataset.id;
            const modalBody = document.getElementById('editHouseContent');
            
            modalBody.innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border text-warning" role="status"></div>
                    <p class="mt-2">Loading house details...</p>
                </div>
            `;
            
            fetch(`/houses/${houseId}/edit-data`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        displayEditForm(data.house, data.species);
                        window.currentEditHouseId = houseId;
                    } else {
                        modalBody.innerHTML = `<div class="alert alert-danger">Failed to load house details: ${data.message || 'Unknown error'}</div>`;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    modalBody.innerHTML = `<div class="alert alert-danger">Error loading data: ${error.message}</div>`;
                });
        });
    });
    
    function displayEditForm(house, species) {
        const speciesOptions = species.map(spec => 
            `<option value="${spec.id}" ${house.species_id == spec.id ? 'selected' : ''}>${escapeHtml(spec.name)} (${spec.code})</option>`
        ).join('');
        
        document.getElementById('editHouseContent').innerHTML = `
            <form id="editHouseForm">
                <input type="hidden" name="id" value="${house.id}">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">House Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="${escapeHtml(house.name)}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">House Code <span class="text-danger">*</span></label>
                        <input type="text" name="house_code" class="form-control" value="${escapeHtml(house.house_code)}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Species Assignment</label>
                        <select name="species_id" class="form-select">
                            <option value="">Not Assigned</option>
                            ${speciesOptions}
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Capacity</label>
                        <input type="number" name="capacity" class="form-control" value="${house.capacity}" min="0">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">Length (m)</label>
                        <input type="number" name="length_m" class="form-control" value="${house.length_m || ''}" step="0.01" min="0">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">Width (m)</label>
                        <input type="number" name="width_m" class="form-control" value="${house.width_m || ''}" step="0.01" min="0">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">Height (m)</label>
                        <input type="number" name="height_m" class="form-control" value="${house.height_m || ''}" step="0.01" min="0">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-semibold">Feeders</label>
                        <input type="number" name="feeders_count" class="form-control" value="${house.feeders_count || 0}" min="0">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-semibold">Drinkers</label>
                        <input type="number" name="drinkers_count" class="form-control" value="${house.drinkers_count || 0}" min="0">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-semibold">Fans</label>
                        <input type="number" name="fans_count" class="form-control" value="${house.fans_count || 0}" min="0">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-semibold">Heaters</label>
                        <input type="number" name="heaters_count" class="form-control" value="${house.heaters_count || 0}" min="0">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select" required>
                            <option value="active" ${house.status === 'active' ? 'selected' : ''}>Active</option>
                            <option value="maintenance" ${house.status === 'maintenance' ? 'selected' : ''}>Maintenance</option>
                            <option value="cleaning" ${house.status === 'cleaning' ? 'selected' : ''}>Cleaning</option>
                            <option value="inactive" ${house.status === 'inactive' ? 'selected' : ''}>Inactive</option>
                        </select>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label fw-semibold">Notes</label>
                        <textarea name="notes" class="form-control" rows="3">${escapeHtml(house.notes || '')}</textarea>
                    </div>
                </div>
            </form>
        `;
    }
    
    // Save Edit House
    document.getElementById('saveEditHouse')?.addEventListener('click', function() {
        const form = document.getElementById('editHouseForm');
        const formData = new FormData(form);
        const data = {};
        formData.forEach((value, key) => { data[key] = value; });
        
        fetch(`/houses/${window.currentEditHouseId}`, {
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
                    text: 'House updated successfully',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    window.location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message || 'Failed to update house'
                });
            }
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred while updating the house'
            });
        });
    });
    
    // Delete House - SweetAlert Confirmation
    document.querySelectorAll('.delete-house-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const houseId = this.dataset.id;
            const houseName = this.dataset.houseName;
            
            Swal.fire({
                title: 'Delete House',
                text: `Are you sure you want to delete "${houseName}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.getElementById('deleteHouseForm');
                    form.action = `/houses/${houseId}`;
                    form.submit();
                }
            });
        });
    });
    
    function escapeHtml(str) {
        if (!str) return '';
        return String(str).replace(/[&<>]/g, function(m) {
            if (m === '&') return '&amp;';
            if (m === '<') return '&lt;';
            if (m === '>') return '&gt;';
            return m;
        });
    }
</script>
@endpush
@endsection