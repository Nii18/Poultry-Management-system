{{-- resources/views/feed-types/index.blade.php --}}
@extends('layouts.master')

@section('title', 'Feed Types Management')

@section('content')
<div class="container-fluid px-4">
    <!-- Modern Header -->
    <div class="report-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <div class="d-flex align-items-center gap-3">
                    <div class="header-icon">
                        <i class="fas fa-seedling"></i>
                    </div>
                    <div>
                        <h1 class="header-title mb-1">Feed Types</h1>
                        <p class="header-subtitle text-muted mb-0">Manage feed formulations and nutritional categories</p>
                    </div>
                </div>
            </div>
            <div class="col-auto">
                <div class="action-buttons">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createFeedModal">
                        <i class="fas fa-plus me-2"></i>New Feed Type
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid mb-4">
        <div class="stat-card">
            <div class="stat-icon bg-primary">
                <i class="fas fa-layer-group"></i>
            </div>
            <div class="stat-details">
                <span class="stat-label">Total Feed Types</span>
                <h2 class="stat-value">{{ $feedTypes->total() }}</h2>
                <span class="stat-trend">Active formulations</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon bg-success">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-details">
                <span class="stat-label">Active Types</span>
                <h2 class="stat-value">{{ $feedTypes->where('is_active', true)->count() }}</h2>
                <span class="stat-trend text-success">Currently in use</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon bg-info">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stat-details">
                <span class="stat-label">Categories</span>
                <h2 class="stat-value">{{ $feedTypes->pluck('category')->unique()->count() }}</h2>
                <span class="stat-trend">Different types</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon bg-warning">
                <i class="fas fa-paw"></i>
            </div>
            <div class="stat-details">
                <span class="stat-label">Species Covered</span>
                <h2 class="stat-value">{{ $feedTypes->pluck('species_id')->unique()->count() }}</h2>
                <span class="stat-trend">Animal types</span>
            </div>
        </div>
    </div>

    <!-- Main Card -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-0 py-3">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="card-title mb-0 fw-semibold">
                        <i class="fas fa-list me-2 text-primary"></i>Feed Formulations
                    </h5>
                </div>
                <div class="col-auto">
                    <div class="filter-group">
                        <form method="GET" action="{{ route('feed-types.index') }}" class="d-flex gap-2">
                            <select name="species_id" class="form-select form-select-sm" style="width: 180px;">
                                <option value="">All Species</option>
                                @foreach($species as $spec)
                                    <option value="{{ $spec->id }}" {{ request('species_id') == $spec->id ? 'selected' : '' }}>
                                        {{ $spec->name }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="fas fa-filter me-1"></i>Filter
                            </button>
                            @if(request('species_id'))
                                <a href="{{ route('feed-types.index') }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-times me-1"></i>Clear
                                </a>
                            @endif
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card-body">
            @if($feedTypes->count() > 0)
                <div class="row g-4">
                    @foreach($feedTypes as $feedType)
                    <div class="col-xl-4 col-lg-6">
                        <div class="feed-card {{ !$feedType->is_active ? 'inactive' : '' }}">
                            <div class="feed-card-header">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="feed-info">
                                        <h5 class="feed-name">{{ $feedType->name }}</h5>
                                        <div class="feed-meta">
                                            <span class="feed-code"><i class="fas fa-tag me-1"></i>{{ $feedType->code }}</span>
                                            <span class="feed-species"><i class="fas fa-paw me-1"></i>{{ $feedType->species->name ?? 'N/A' }}</span>
                                        </div>
                                    </div>
                                    <div class="status-badge {{ $feedType->is_active ? 'status-active' : 'status-inactive' }}">
                                        <i class="fas {{ $feedType->is_active ? 'fa-check-circle' : 'fa-ban' }} me-1"></i>
                                        {{ $feedType->is_active ? 'Active' : 'Inactive' }}
                                    </div>
                                </div>
                            </div>
                            <div class="feed-card-body">
                                <div class="category-badge">
                                    <i class="fas {{ 
                                        $feedType->category === 'starter' ? 'fa-baby' : 
                                        ($feedType->category === 'grower' ? 'fa-chart-line' : 
                                        ($feedType->category === 'finisher' ? 'fa-flag-checkered' : 
                                        ($feedType->category === 'layer' ? 'fa-egg' : 
                                        ($feedType->category === 'breeder' ? 'fa-heart' : 'fa-leaf')))) 
                                    }} me-1"></i>
                                    {{ ucfirst($feedType->category) }}
                                </div>
                                <div class="nutritional-info">
                                    <div class="nutrition-item">
                                        <span class="nutrition-label">Crude Protein</span>
                                        <strong class="nutrition-value">{{ $feedType->protein_percentage ?? 'N/A' }}%</strong>
                                    </div>
                                    <div class="nutrition-divider"></div>
                                    <div class="nutrition-item">
                                        <span class="nutrition-label">Metabolizable Energy</span>
                                        <strong class="nutrition-value">{{ $feedType->energy_mj_kg ?? 'N/A' }} MJ/kg</strong>
                                    </div>
                                </div>
                                @if($feedType->description)
                                <div class="feed-description">
                                    <i class="fas fa-align-left me-1 text-muted"></i>
                                    {{ Str::limit($feedType->description, 80) }}
                                </div>
                                @endif
                            </div>
                            <div class="feed-card-footer">
                                <div class="btn-group w-100">
                                    <button type="button" class="btn btn-outline-primary btn-sm view-feed-btn" 
                                            data-id="{{ $feedType->id }}" data-bs-toggle="modal" data-bs-target="#viewFeedModal">
                                        <i class="fas fa-eye me-1"></i>View
                                    </button>
                                    <button type="button" class="btn btn-outline-warning btn-sm edit-feed-btn" 
                                            data-id="{{ $feedType->id }}" data-bs-toggle="modal" data-bs-target="#editFeedModal">
                                        <i class="fas fa-edit me-1"></i>Edit
                                    </button>
                                    @if(auth()->user()->role === 'admin')
                                        <button type="button" class="btn btn-outline-danger btn-sm delete-feed-btn" 
                                                data-id="{{ $feedType->id }}" data-feed-name="{{ $feedType->name }}">
                                            <i class="fas fa-trash-alt me-1"></i>Delete
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                @if($feedTypes->hasPages())
                <div class="d-flex justify-content-between align-items-center mt-4 pt-2">
                    <div class="text-muted small">
                        Showing {{ $feedTypes->firstItem() ?? 0 }} to {{ $feedTypes->lastItem() ?? 0 }} of {{ $feedTypes->total() }} results
                    </div>
                    <div>
                        {{ $feedTypes->withQueryString()->links() }}
                    </div>
                </div>
                @endif
            @else
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-seedling"></i>
                    </div>
                    <h4>No Feed Types Found</h4>
                    <p>Get started by creating your first feed formulation.</p>
                    <a href="{{ route('feed-types.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Create Feed Type
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- View Feed Type Modal -->
<div class="modal fade" id="viewFeedModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white">
                    <i class="fas fa-eye me-2"></i>Feed Type Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewFeedContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading feed type details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Feed Type Modal -->
<div class="modal fade" id="editFeedModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title text-white">
                    <i class="fas fa-edit me-2"></i>Edit Feed Type
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="editFeedContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-warning" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading feed type details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="saveEditFeed">Update Feed Type</button>
            </div>
        </div>
    </div>
</div>

<!-- Create Feed Type Modal -->
<div class="modal fade" id="createFeedModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white">
                    <i class="fas fa-plus me-2"></i>New Feed Type
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form method="POST" action="{{ route('feed-types.store') }}">
                @csrf

                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                    <div class="row">

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Species <span class="text-danger">*</span></label>
                            <select name="species_id" class="form-select" required>
                                <option value="">Select Species</option>
                                @foreach($species as $spec)
                                    <option value="{{ $spec->id }}">{{ $spec->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Feed Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Feed Code <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Category <span class="text-danger">*</span></label>
                            <select name="category" class="form-select" required>
                                <option value="starter">Starter</option>
                                <option value="grower">Grower</option>
                                <option value="finisher">Finisher</option>
                                <option value="layer">Layer</option>
                                <option value="breeder">Breeder</option>
                                <option value="maintenance">Maintenance</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Protein (%)</label>
                            <input type="number" step="0.1" name="protein_percentage" class="form-control">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Energy (MJ/kg)</label>
                            <input type="number" step="0.01" name="energy_mj_kg" class="form-control">
                        </div>

                        <div class="col-12 mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>

                        <div class="col-12">
                            <div class="form-check">
                                <input type="checkbox" name="is_active" value="1" class="form-check-input" checked>
                                <label class="form-check-label">Active</label>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Create Feed Type
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>



<!-- Hidden forms for AJAX actions -->
<form id="deleteFeedForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // View Feed Type - Load content via AJAX
    document.querySelectorAll('.view-feed-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const feedId = this.dataset.id;
            const modalBody = document.getElementById('viewFeedContent');
            
            modalBody.innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2">Loading feed type details...</p>
                </div>
            `;
            
            fetch(`/feed-types/${feedId}/details`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        displayFeedDetails(data.feedType, data.stats);
                    } else {
                        modalBody.innerHTML = `<div class="alert alert-danger">Failed to load feed type details: ${data.message || 'Unknown error'}</div>`;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    modalBody.innerHTML = `<div class="alert alert-danger">Error loading data: ${error.message}</div>`;
                });
        });
    });
    
    function displayFeedDetails(feedType, stats) {
    // Ensure numeric values are properly formatted
    const avgCost = parseFloat(stats.avg_cost_per_kg) || 0;
    const totalQuantity = parseInt(stats.total_quantity) || 0;
    const currentStock = parseInt(stats.current_stock) || 0;
    const totalDeliveries = parseInt(stats.total_deliveries) || 0;
    
    document.getElementById('viewFeedContent').innerHTML = `
        <div class="detail-section">
            <h6>Basic Information</h6>
            <div class="detail-grid">
                <div class="detail-item">
                    <span class="detail-label">Name</span>
                    <span class="detail-value">${escapeHtml(feedType.name)}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Code</span>
                    <span class="detail-value"><code>${escapeHtml(feedType.code)}</code></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Species</span>
                    <span class="detail-value">${escapeHtml(feedType.species_name || 'N/A')}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Category</span>
                    <span class="detail-value">${escapeHtml(feedType.category.charAt(0).toUpperCase() + feedType.category.slice(1))}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Status</span>
                    <span class="badge ${feedType.is_active ? 'bg-success' : 'bg-secondary'}">${feedType.is_active ? 'Active' : 'Inactive'}</span>
                </div>
            </div>
        </div>
        
        <div class="detail-section">
            <h6>Nutritional Information</h6>
            <div class="detail-grid">
                <div class="detail-item">
                    <span class="detail-label">Crude Protein</span>
                    <span class="detail-value">${feedType.protein_percentage || 'N/A'}%</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Metabolizable Energy</span>
                    <span class="detail-value">${feedType.energy_mj_kg || 'N/A'} MJ/kg</span>
                </div>
            </div>
        </div>
        
        <div class="detail-section">
            <h6>Inventory Statistics</h6>
            <div class="detail-grid">
                <div class="detail-item">
                    <span class="detail-label">Total Deliveries</span>
                    <span class="detail-value">${totalDeliveries.toLocaleString()}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Total Quantity Received</span>
                    <span class="detail-value">${totalQuantity.toLocaleString()} kg</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Current Stock</span>
                    <span class="detail-value">${currentStock.toLocaleString()} kg</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Average Cost</span>
                    <span class="detail-value">$${avgCost.toFixed(2)} / kg</span>
                </div>
            </div>
        </div>
        
        ${feedType.description ? `
        <div class="detail-section">
            <h6>Description</h6>
            <p class="mb-0">${escapeHtml(feedType.description)}</p>
        </div>
        ` : ''}
        
        ${stats.recent_deliveries && stats.recent_deliveries.length > 0 ? `
        <div class="detail-section">
            <h6>Recent Deliveries</h6>
            <div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Quantity</th>
                            <th>Cost/kg</th>
                            <th>Remaining</th>
                        </td>
                    </thead>
                    <tbody>
                        ${stats.recent_deliveries.map(d => `
                            <tr>
                                <td>${escapeHtml(d.date)}</td
                                <td>${parseInt(d.quantity).toLocaleString()} kg</td
                                <td>$${parseFloat(d.cost_per_kg).toFixed(2)}</td
                                <td>${parseInt(d.remaining).toLocaleString()} kg</td
                             </tr
                        `).join('')}
                    </tbody>
                 </table
            </div>
        </div>
        ` : ''}
    `;
}
    
    // Edit Feed Type - Load content via AJAX
    document.querySelectorAll('.edit-feed-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const feedId = this.dataset.id;
            const modalBody = document.getElementById('editFeedContent');
            
            modalBody.innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border text-warning" role="status"></div>
                    <p class="mt-2">Loading feed type details...</p>
                </div>
            `;
            
            fetch(`/feed-types/${feedId}/edit-data`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        displayEditForm(data.feedType, data.species);
                        window.currentEditFeedId = feedId;
                    } else {
                        modalBody.innerHTML = `<div class="alert alert-danger">Failed to load feed type details: ${data.message || 'Unknown error'}</div>`;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    modalBody.innerHTML = `<div class="alert alert-danger">Error loading data: ${error.message}</div>`;
                });
        });
    });
    
    function displayEditForm(feedType, species) {
        const speciesOptions = species.map(spec => 
            `<option value="${spec.id}" ${feedType.species_id == spec.id ? 'selected' : ''}>${escapeHtml(spec.name)}</option>`
        ).join('');
        
        document.getElementById('editFeedContent').innerHTML = `
            <form id="editFeedForm">
                <input type="hidden" name="id" value="${feedType.id}">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="${escapeHtml(feedType.name)}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Code <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control" value="${escapeHtml(feedType.code)}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Species</label>
                        <select name="species_id" class="form-select">
                            <option value="">Select Species</option>
                            ${speciesOptions}
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                        <select name="category" class="form-select" required>
                            <option value="starter" ${feedType.category === 'starter' ? 'selected' : ''}>Starter</option>
                            <option value="grower" ${feedType.category === 'grower' ? 'selected' : ''}>Grower</option>
                            <option value="finisher" ${feedType.category === 'finisher' ? 'selected' : ''}>Finisher</option>
                            <option value="layer" ${feedType.category === 'layer' ? 'selected' : ''}>Layer</option>
                            <option value="breeder" ${feedType.category === 'breeder' ? 'selected' : ''}>Breeder</option>
                            <option value="maintenance" ${feedType.category === 'maintenance' ? 'selected' : ''}>Maintenance</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Protein Percentage (%)</label>
                        <input type="number" name="protein_percentage" class="form-control" step="0.1" min="0" max="100" value="${feedType.protein_percentage || ''}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Energy (MJ/kg)</label>
                        <input type="number" name="energy_mj_kg" class="form-control" step="0.01" min="0" value="${feedType.energy_mj_kg || ''}">
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" class="form-control" rows="3">${escapeHtml(feedType.description || '')}</textarea>
                    </div>
                    <div class="col-12 mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active" ${feedType.is_active ? 'checked' : ''}>
                            <label class="form-check-label" for="is_active">Active (available for use)</label>
                        </div>
                    </div>
                </div>
            </form>
        `;
    }
    
    // Save Edit Feed Type
    document.getElementById('saveEditFeed')?.addEventListener('click', function() {
        const form = document.getElementById('editFeedForm');
        const formData = new FormData(form);
        const data = {};
        formData.forEach((value, key) => { data[key] = value; });
        
        fetch(`/feed-types/${window.currentEditFeedId}`, {
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
                    text: 'Feed type updated successfully',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    window.location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message || 'Failed to update feed type'
                });
            }
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred while updating the feed type'
            });
        });
    });
    
    // Delete Feed Type - SweetAlert Confirmation
    document.querySelectorAll('.delete-feed-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const feedId = this.dataset.id;
            const feedName = this.dataset.feedName;
            
            Swal.fire({
                title: 'Delete Feed Type',
                text: `Are you sure you want to delete "${feedName}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.getElementById('deleteFeedForm');
                    form.action = `/feed-types/${feedId}`;
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

@push('styles')
<style>
    /* Existing styles remain the same */
    .report-header { margin-bottom: 1.5rem; }
    .header-icon { width: 55px; height: 55px; background: linear-gradient(135deg, #0d6e4f 0%, #0a5a40 100%); border-radius: 14px; display: flex; align-items: center; justify-content: center; }
    .header-icon i { font-size: 26px; color: white; }
    .header-title { font-size: 26px; font-weight: 700; color: #1e293b; letter-spacing: -0.02em; margin: 0; }
    .header-subtitle { font-size: 14px; margin: 0; }
    .action-buttons { display: flex; gap: 0.75rem; }
    
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem; margin-bottom: 1.5rem; }
    .stat-card { background: white; border-radius: 16px; padding: 1rem; display: flex; align-items: center; gap: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; transition: all 0.3s ease; }
    .stat-card:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0,0,0,0.08); }
    .stat-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; }
    .stat-icon i { font-size: 22px; color: white; }
    .stat-icon.bg-primary { background: linear-gradient(135deg, #0d6e4f, #0a5a40); }
    .stat-icon.bg-success { background: linear-gradient(135deg, #10b981, #059669); }
    .stat-icon.bg-info { background: linear-gradient(135deg, #3b82f6, #2563eb); }
    .stat-icon.bg-warning { background: linear-gradient(135deg, #f59e0b, #d97706); }
    .stat-details { flex: 1; }
    .stat-label { font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; font-weight: 600; }
    .stat-value { font-size: 28px; font-weight: 700; color: #1e293b; margin: 0.15rem 0; line-height: 1.2; }
    .stat-trend { font-size: 11px; color: #64748b; }
    .stat-trend.text-success { color: #10b981; }
    
    .filter-group { display: flex; gap: 0.5rem; align-items: center; }
    
    .feed-card { background: white; border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden; transition: all 0.3s ease; height: 100%; }
    .feed-card:hover { transform: translateY(-3px); box-shadow: 0 12px 30px rgba(0,0,0,0.1); }
    .feed-card.inactive { opacity: 0.7; background: #f8fafc; }
    .feed-card-header { padding: 1rem 1.25rem; background: #f8fafc; border-bottom: 1px solid #e2e8f0; }
    .feed-name { font-size: 18px; font-weight: 700; color: #1e293b; margin: 0 0 0.5rem 0; }
    .feed-meta { display: flex; gap: 1rem; flex-wrap: wrap; font-size: 12px; }
    .feed-code { color: #64748b; background: #e2e8f0; padding: 0.2rem 0.5rem; border-radius: 6px; font-family: monospace; }
    .feed-species { color: #0d6e4f; background: #e8f4f0; padding: 0.2rem 0.5rem; border-radius: 6px; }
    
    .status-badge { padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 11px; font-weight: 600; display: inline-flex; align-items: center; }
    .status-active { background: #d1fae5; color: #065f46; }
    .status-inactive { background: #f1f5f9; color: #475569; }
    
    .feed-card-body { padding: 1rem 1.25rem; }
    .category-badge { display: inline-flex; align-items: center; padding: 0.3rem 0.8rem; background: #e0f2fe; color: #0369a1; border-radius: 20px; font-size: 12px; font-weight: 500; margin-bottom: 1rem; }
    .nutritional-info { display: flex; justify-content: space-between; align-items: center; background: #f8fafc; padding: 0.75rem; border-radius: 12px; margin-bottom: 1rem; }
    .nutrition-item { flex: 1; text-align: center; }
    .nutrition-label { display: block; font-size: 10px; color: #64748b; margin-bottom: 0.25rem; }
    .nutrition-value { font-size: 16px; color: #1e293b; }
    .nutrition-divider { width: 1px; height: 30px; background: #e2e8f0; }
    .feed-description { font-size: 12px; color: #64748b; line-height: 1.5; margin-top: 0.75rem; padding-top: 0.75rem; border-top: 1px solid #e2e8f0; }
    
    .feed-card-footer { padding: 0.75rem 1.25rem; background: #f8fafc; border-top: 1px solid #e2e8f0; }
    .btn-group { display: flex; gap: 0.5rem; }
    .btn-group .btn { flex: 1; border-radius: 8px; font-size: 12px; padding: 0.4rem 0.5rem; }
    
    .empty-state { text-align: center; padding: 3rem; }
    .empty-icon { width: 70px; height: 70px; background: #f1f5f9; border-radius: 35px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; }
    .empty-icon i { font-size: 32px; color: #94a3b8; }
    .empty-state h4 { font-size: 18px; font-weight: 600; color: #1e293b; margin-bottom: 0.5rem; }
    .empty-state p { color: #64748b; margin-bottom: 1.5rem; }
    
    .pagination { margin-bottom: 0; }
    .page-link { border-radius: 8px; margin: 0 2px; border: none; color: #475569; padding: 0.4rem 0.75rem; font-size: 13px; }
    .page-item.active .page-link { background-color: #0d6e4f; color: white; }
    .page-link:hover { background-color: #e2e8f0; color: #0d6e4f; }
    
    /* Modal Styles */
    .modal-header { padding: 1rem 1.5rem; }
    .modal-body { padding: 1.5rem; max-height: 70vh; overflow-y: auto; }
    .detail-section { margin-bottom: 1.5rem; }
    .detail-section h6 { font-weight: 600; color: #1e293b; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 2px solid #e2e8f0; }
    .detail-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; }
    .detail-item { display: flex; flex-direction: column; }
    .detail-label { font-size: 0.7rem; text-transform: uppercase; color: #64748b; font-weight: 600; margin-bottom: 0.25rem; }
    .detail-value { font-size: 1rem; font-weight: 500; color: #1e293b; }
    
    @media (max-width: 768px) {
        .stats-grid { grid-template-columns: repeat(2, 1fr); gap: 1rem; }
        .stat-value { font-size: 22px; }
        .header-title { font-size: 22px; }
        .feed-name { font-size: 16px; }
        .nutritional-info { flex-direction: column; gap: 0.5rem; }
        .nutrition-divider { display: none; }
        .btn-group { flex-direction: column; }
        .filter-group form { flex-wrap: wrap; }
        .detail-grid { grid-template-columns: 1fr; }
    }
</style>
@endpush
@endsection