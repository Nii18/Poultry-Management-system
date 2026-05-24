{{-- resources/views/species/index.blade.php --}}
@extends('layouts.master')

@section('content')
<div class="container-fluid px-4">
    <!-- Page Header -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <div class="d-flex align-items-center gap-3">
                    <div class="page-icon">
                        <i class="fas fa-paw fs-1 text-primary"></i>
                    </div>
                    <div>
                        <h1 class="page-title mb-1">Species Management</h1>
                        <p class="page-description text-muted mb-0">Manage all animal species on your farm</p>
                    </div>
                </div>
            </div>
            <div class="col-auto">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Species</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-card-icon bg-primary-soft">
                        <i class="fas fa-paw text-primary"></i>
                    </div>
                    <div class="stat-card-info">
                        <span class="stat-card-label">Total Species</span>
                        <h3 class="stat-card-value">{{ $species->total() }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-card-icon bg-success-soft">
                        <i class="fas fa-check-circle text-success"></i>
                    </div>
                    <div class="stat-card-info">
                        <span class="stat-card-label">Active Species</span>
                        <h3 class="stat-card-value">{{ $species->where('is_active', true)->count() }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-card-icon bg-info-soft">
                        <i class="fas fa-chart-line text-info"></i>
                    </div>
                    <div class="stat-card-info">
                        <span class="stat-card-label">Inactive Species</span>
                        <h3 class="stat-card-value">{{ $species->where('is_active', false)->count() }}</h3>
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
                        <i class="fas fa-list me-2 text-primary"></i>Species Records
                    </h5>
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-primary" id="newSpeciesBtn">
                        <i class="fas fa-plus me-2"></i>New Species
                    </button>
                </div>
            </div>
        </div>
        
        <div class="card-body">
            <!-- Filters Section -->
            <div class="filter-section mb-4 p-3 bg-light rounded-3">
                <div class="row align-items-end g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold mb-2" style="color:#1e293b;">
                            <i class="fas fa-flag-checkered me-1" style="color:#64748b;"></i>Status
                        </label>
                        <select name="is_active" class="form-select" id="statusFilter">
                            <option value="">All</option>
                            <option value="1" {{ request('is_active') == '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ request('is_active') == '0' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-primary px-4" id="applyFilters">
                                <i class="fas fa-filter me-2"></i>Apply Filters
                            </button>
                            <a href="{{ route('species.index') }}" class="btn btn-outline-secondary px-4">
                                <i class="fas fa-redo-alt me-2"></i>Reset
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Species Table -->
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="py-3">Icon</th>
                            <th class="py-3">Name</th>
                            <th class="py-3">Code</th>
                            <th class="py-3">Market Age</th>
                            <th class="py-3">Market Weight</th>
                            <th class="py-3">Status</th>
                            <th class="py-3 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($species as $item)
                        <tr>
                            <td>
                                <span class="iconify"
                                data-icon="{{ $item->icon }}"
                                style="font-size:1.6rem; color:{{ $item->color_hex }};">
                          </span>
                            </td>
                            <td>
                                <button type="button" class="btn btn-link p-0 text-primary fw-semibold text-decoration-none view-species-btn" 
                                        data-id="{{ $item->id }}">
                                    {{ $item->name }}
                                </button>
                            </td>
                            <td>{{ $item->code }}</td>
                            <td>{{ $item->market_age_days ? $item->market_age_days . ' days' : 'N/A' }}</td>
                            <td>{{ $item->market_weight_kg ? $item->market_weight_kg . ' kg' : 'N/A' }}</td>
                            <td>
                                @if($item->is_active)
                                    <span class="badge bg-success-soft text-success px-3 py-2 rounded-pill">
                                        <i class="fas fa-circle me-1" style="font-size: 8px;"></i>Active
                                    </span>
                                @else
                                    <span class="badge bg-secondary-soft text-secondary px-3 py-2 rounded-pill">
                                        <i class="fas fa-circle me-1" style="font-size: 8px;"></i>Inactive
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group gap-1">

                                    <button type="button" class="btn btn-sm btn-outline-primary view-species-btn" 
                                    data-id="{{ $item->id }}" data-bs-toggle="modal" data-bs-target="#viewSpeciesModal" title="View Details">
                                <i class="fas fa-eye"></i> View
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-warning edit-species-btn" 
                                    data-id="{{ $item->id }}" data-bs-toggle="modal" data-bs-target="#editSpeciesModal" title="Edit">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                                    @if(auth()->user()->role === 'admin')
                                        <button type="button" class="btn btn-sm btn-outline-danger delete-species-btn" 
                                                data-id="{{ $item->id }}" data-name="{{ $item->name }}" title="Delete">
                                            <i class="fas fa-trash-alt"></i> Delete
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="empty-state">
                                    <i class="fas fa-paw fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">No Species Found</h5>
                                    <p class="text-muted mb-3">Get started by creating your first species</p>
                                    <button type="button" class="btn btn-primary" id="emptyStateNewBtn">
                                        <i class="fas fa-plus me-2"></i>New Species
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($species->hasPages())
            <div class="d-flex justify-content-between align-items-center mt-4 pt-2">
                <div class="text-muted small">
                    Showing {{ $species->firstItem() ?? 0 }} to {{ $species->lastItem() ?? 0 }} of {{ $species->total() }} results
                </div>
                <div>
                    {{ $species->withQueryString()->links() }}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Create Species Modal -->
<div class="modal fade" id="createSpeciesModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title text-white">
                    <i class="fas fa-plus-circle me-2"></i>Create New Species
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="createSpeciesContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2">Loading form...</p>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveCreateSpecies">Create Species</button>
            </div>
        </div>
    </div>
</div>

<!-- View Species Modal -->
<div class="modal fade" id="viewSpeciesModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title text-white">
                    <i class="fas fa-eye me-2"></i>Species Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewSpeciesContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2">Loading species details...</p>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-warning toggle-status-btn" id="toggleStatusBtn" style="display: none;">
                    <i class="fas fa-exchange-alt me-1"></i>Toggle Status
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Species Modal -->
<div class="modal fade" id="editSpeciesModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-warning text-white border-0">
                <h5 class="modal-title text-white">
                    <i class="fas fa-edit me-2"></i>Edit Species
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="editSpeciesContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-warning" role="status"></div>
                    <p class="mt-2">Loading species details...</p>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="saveEditSpecies">Update Species</button>
            </div>
        </div>
    </div>
</div>

<!-- Hidden form for delete -->
<form id="deleteSpeciesForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

@push('styles')
<style>
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
    .bg-info-soft { background: #d1fae5; }
    .bg-secondary-soft { background: #f1f5f9; }
    .bg-warning-soft { background: #fef3c7; }
    .bg-danger-soft { background: #fee2e2; }
    
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
    
    .filter-section {
        background: #f8fafc;
        border-radius: 12px;
    }
    
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
    
    .badge {
        font-weight: 500;
        font-size: 0.75rem;
    }
    
    .empty-state {
        text-align: center;
        padding: 2rem;
    }
    
    .btn-group .btn {
        border-radius: 8px !important;
        margin: 0 2px;
        padding: 0.25rem 0.5rem;
    }
    
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
    
    .stats-card {
        background: #f8fafc;
        border-radius: 12px;
        padding: 1rem;
        text-align: center;
    }
    
    .stats-number {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1e293b;
    }
    
    .stats-label {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #64748b;
        font-weight: 600;
    }
    
    .json-preview {
        background: #f8fafc;
        padding: 0.75rem;
        border-radius: 8px;
        font-family: monospace;
        font-size: 12px;
        border-left: 3px solid #0d6e4f;
    }
    
    .json-preview pre {
        margin: 0;
        white-space: pre-wrap;
        word-wrap: break-word;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.iconify.design/3/3.1.0/iconify.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    function closeAllModals() {
        const modals = document.querySelectorAll('.modal.show');
        modals.forEach(modal => {
            const modalInstance = bootstrap.Modal.getInstance(modal);
            if (modalInstance) {
                modalInstance.hide();
            }
        });
        const backdrops = document.querySelectorAll('.modal-backdrop');
        backdrops.forEach(backdrop => backdrop.remove());
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
    }
    // Force Iconify to scan the table icons
setTimeout(() => {
    if (window.Iconify) Iconify.scan();
}, 300);

    // Apply Filters
    document.getElementById('applyFilters')?.addEventListener('click', function() {
        const isActive = document.getElementById('statusFilter').value;
        let url = '{{ route("species.index") }}';
        const params = new URLSearchParams();
        
        if (isActive !== '') params.append('is_active', isActive);
        
        if (params.toString()) {
            url += '?' + params.toString();
        }
        
        window.location.href = url;
    });
    
    // ==================== CREATE SPECIES MODAL ====================
    let createModal = null;
    
    function openCreateSpeciesModal() {
    closeAllModals();
    
    const modalElement = document.getElementById('createSpeciesModal');
    createModal = new bootstrap.Modal(modalElement, {
        backdrop: 'static',
        keyboard: false
    });
    const modalBody = document.getElementById('createSpeciesContent');
    
    modalBody.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2">Loading form...</p>
        </div>
    `;
    createModal.show();
    
    fetch('{{ route("species.create-form") }}', {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('createSpeciesContent').innerHTML = data.html;
        } else {
            modalBody.innerHTML = `<div class="alert alert-danger m-3">Failed to load form: ${data.message}</div>`;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        modalBody.innerHTML = `<div class="alert alert-danger m-3">Error loading form: ${error.message}</div>`;
    });
}
    
    document.getElementById('createSpeciesModal')?.addEventListener('hidden.bs.modal', function() {
        if (createModal) {
            createModal.dispose();
            createModal = null;
        }
        const backdrops = document.querySelectorAll('.modal-backdrop');
        backdrops.forEach(backdrop => backdrop.remove());
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
    });
    
    function displaySpeciesCreateForm() {
        document.getElementById('createSpeciesContent').innerHTML = `
            <form id="createSpeciesForm">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g., Chicken, Cattle, Goat" required>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Code <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control" maxlength="5" placeholder="e.g., CH, CT, GT" required>
                        <small class="text-muted">Unique 3-5 character code</small>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Icon Class</label>
                        <input type="text" name="icon" class="form-control" value="fas fa-drumstick" placeholder="e.g., fas fa-drumstick">
                        <small class="text-muted">FontAwesome icon class</small>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Color (Hex)</label>
                        <input type="color" name="color_hex" class="form-control" value="#3B82F6">
                    </div>
                    
                    <div class="col-12 mb-3">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Brief description of the species..."></textarea>
                    </div>
                    
                    <div class="col-md-12 mb-3">
                        <div class="card">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">Lifecycle Parameters</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Gestation Days</label>
                                        <input type="number" name="gestation_days" class="form-control" placeholder="Days until birth" min="0">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Weaning Days</label>
                                        <input type="number" name="weaning_days" class="form-control" placeholder="Days until weaning" min="0">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Sexual Maturity (Days)</label>
                                        <input type="number" name="sexual_maturity_days" class="form-control" placeholder="Days until breeding age" min="0">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Market Age (Days)</label>
                                        <input type="number" name="market_age_days" class="form-control" placeholder="Days until ready for market" min="0">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Market Weight (kg)</label>
                                        <input type="number" name="market_weight_kg" class="form-control" step="0.01" placeholder="Target weight at market" min="0">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Lifespan (Years)</label>
                                        <input type="number" name="lifespan_years" class="form-control" placeholder="Average lifespan" min="0">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-12 mb-3">
                        <div class="card">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">Default Performance Metrics (JSON)</h6>
                            </div>
                            <div class="card-body">
                                <textarea name="default_metrics" class="form-control" rows="3" placeholder='{"fcr_target": 1.8, "mortality_target": 5, "egg_production_target": 85}'></textarea>
                                <small class="text-muted">Enter as valid JSON format. Example: {"fcr_target": 1.8, "mortality_target": 5}</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-12 mb-3">
                        <div class="card">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">Growth Standards (JSON)</h6>
                            </div>
                            <div class="card-body">
                                <textarea name="growth_standards" class="form-control" rows="3" placeholder='{"week1": 0.18, "week2": 0.45, "week3": 0.85, "week4": 1.35, "week5": 1.95, "week6": 2.5}'></textarea>
                                <small class="text-muted">Enter as valid JSON format. Example: {"week1": 0.18, "week2": 0.45}</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-12 mb-3">
                        <div class="card">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">Health Indicators (JSON)</h6>
                            </div>
                            <div class="card-body">
                                <textarea name="health_indicators" class="form-control" rows="3" placeholder='{"normal_temperature": 41.5, "normal_heart_rate": 250, "normal_respiration": 20}'></textarea>
                                <small class="text-muted">Enter as valid JSON format. Example: {"normal_temperature": 41.5, "normal_heart_rate": 250}</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active" checked>
                            <label class="form-check-label" for="is_active">Active (available for selection)</label>
                        </div>
                    </div>
                </div>
            </form>
        `;
    }
    
    document.getElementById('saveCreateSpecies')?.addEventListener('click', function() {
        const form = document.getElementById('createSpeciesForm');
        if (!form) return;
        
        const formData = new FormData(form);
        const data = {};
        formData.forEach((value, key) => { data[key] = value; });
        
        // Validate required fields
        if (!data.name || !data.code) {
            Swal.fire({ icon: 'error', title: 'Validation Error', text: 'Please fill in all required fields (Name and Code)' });
            return;
        }
        
        // Validate JSON fields
        const jsonFields = ['default_metrics', 'growth_standards', 'health_indicators'];
        for (let field of jsonFields) {
            if (data[field] && data[field].trim()) {
                try {
                    JSON.parse(data[field]);
                } catch (e) {
                    Swal.fire({ icon: 'error', title: 'Invalid JSON', text: `${field.replace('_', ' ')} has invalid JSON format` });
                    return;
                }
            }
        }
        
        const saveBtn = this;
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Creating...';
        
        fetch('{{ route("species.store-ajax") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (createModal) createModal.hide();
                Swal.fire({ icon: 'success', title: 'Created!', text: 'Species created successfully', timer: 1500, showConfirmButton: false })
                    .then(() => window.location.reload());
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Failed to create species' });
                saveBtn.disabled = false;
                saveBtn.innerHTML = 'Create Species';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({ icon: 'error', title: 'Error', text: 'An error occurred while creating' });
            saveBtn.disabled = false;
            saveBtn.innerHTML = 'Create Species';
        });
    });
    
    document.getElementById('newSpeciesBtn')?.addEventListener('click', openCreateSpeciesModal);
    document.getElementById('emptyStateNewBtn')?.addEventListener('click', openCreateSpeciesModal);
    
    // ==================== VIEW SPECIES MODAL ====================
    let viewModal = null;
    let currentSpeciesId = null;
    
    document.querySelectorAll('.view-species-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            closeAllModals();
            
            currentSpeciesId = this.dataset.id;
            const modalElement = document.getElementById('viewSpeciesModal');
            viewModal = new bootstrap.Modal(modalElement, {
                backdrop: 'static',
                keyboard: true
            });
            const modalBody = document.getElementById('viewSpeciesContent');
            
            modalBody.innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2">Loading species details...</p>
                </div>
            `;
            viewModal.show();
            
            fetch(`/species/${currentSpeciesId}/details-json`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displaySpeciesDetails(data.species);
                } else {
                    modalBody.innerHTML = `<div class="alert alert-danger m-3">Failed to load details: ${data.message}</div>`;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                modalBody.innerHTML = `<div class="alert alert-danger m-3">Error loading data: ${error.message}</div>`;
            });
        });
    });
    
    document.getElementById('viewSpeciesModal')?.addEventListener('hidden.bs.modal', function() {
        if (viewModal) {
            viewModal.dispose();
            viewModal = null;
        }
        currentSpeciesId = null;
        const backdrops = document.querySelectorAll('.modal-backdrop');
        backdrops.forEach(backdrop => backdrop.remove());
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
    });
    
    function displaySpeciesDetails(species) {
    const statusBadge = species.is_active
        ? '<span class="badge bg-success-soft text-success px-3 py-2 rounded-pill"><i class="fas fa-circle me-1" style="font-size:8px;"></i>Active</span>'
        : '<span class="badge bg-secondary-soft text-secondary px-3 py-2 rounded-pill"><i class="fas fa-circle me-1" style="font-size:8px;"></i>Inactive</span>';

    function renderJson(data, emptyMsg) {
        if (!data || (typeof data === 'object' && Object.keys(data).length === 0) || data === '') {
            return `<p class="text-muted fst-italic mb-0">${emptyMsg}</p>`;
        }
        const obj = typeof data === 'string' ? JSON.parse(data) : data;
        const rows = Object.entries(obj).map(([k, v]) => `
            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                <span class="text-muted small text-capitalize">${k.replace(/_/g, ' ')}</span>
                <span class="fw-semibold" style="color:#1e293b;">${v}</span>
            </div>`).join('');
        return `<div class="rounded-3 border px-3 pt-1 pb-0 bg-white">${rows}</div>`;
    }

    // Build the iconify icon HTML
    const iconHtml = (species.icon && species.icon.includes(':'))
        ? `<span class="iconify" data-icon="${species.icon}" style="font-size:2.5rem; color:${species.color_hex};"></span>`
        : `<i class="${species.icon} fa-2x" style="color:${species.color_hex};"></i>`;

    document.getElementById('viewSpeciesContent').innerHTML = `
        <!-- Hero Banner -->
        <div class="rounded-3 mb-4 p-4 d-flex align-items-center gap-3"
             style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border: 1px solid #bbf7d0;">
            <div class="d-flex align-items-center justify-content-center rounded-circle shadow-sm flex-shrink-0"
                 style="width:64px; height:64px; background:${species.color_hex}20; border: 2px solid ${species.color_hex}40;">
                ${iconHtml}
            </div>
            <div class="flex-grow-1">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <h4 class="mb-0 fw-bold" style="color:#1e293b;">${escapeHtml(species.name)}</h4>
                    ${statusBadge}
                </div>
                <div class="d-flex align-items-center gap-2 mt-1 flex-wrap">
                    <span class="badge rounded-pill px-3 py-1"
                          style="background:${species.color_hex}20; color:${species.color_hex}; font-size:0.75rem; border:1px solid ${species.color_hex}40;">
                        <i class="fas fa-tag me-1"></i>${escapeHtml(species.code)}
                    </span>
                    ${species.description
                        ? `<span style="color:#475569; font-size:0.85rem;"><i class="fas fa-info-circle me-1" style="color:#94a3b8;"></i>${escapeHtml(species.description)}</span>`
                        : ''}
                </div>
            </div>
        </div>

        <!-- Stats Row -->
        <div class="row g-3 mb-4">
            <div class="col-4">
                <div class="text-center p-3 rounded-3 border h-100" style="background:#f8fafc;">
                    <div class="fw-bold fs-4 mb-0" style="color:#1e293b;">${species.stats.flock_count}</div>
                    <div style="color:#64748b; font-size:0.68rem; text-transform:uppercase; letter-spacing:.5px; font-weight:600;">Total Flocks</div>
                </div>
            </div>
            <div class="col-4">
                <div class="text-center p-3 rounded-3 border h-100" style="background:#f0fdf4;">
                    <div class="fw-bold fs-4 mb-0" style="color:#16a34a;">${species.stats.active_flocks}</div>
                    <div style="color:#64748b; font-size:0.68rem; text-transform:uppercase; letter-spacing:.5px; font-weight:600;">Active Flocks</div>
                </div>
            </div>
            <div class="col-4">
                <div class="text-center p-3 rounded-3 border h-100" style="background:#eff6ff;">
                    <div class="fw-bold fs-4 mb-0" style="color:#2563eb;">${species.stats.total_animals}</div>
                    <div style="color:#64748b; font-size:0.68rem; text-transform:uppercase; letter-spacing:.5px; font-weight:600;">Total Animals</div>
                </div>
            </div>
        </div>

        <!-- Basic Info -->
        <div class="mb-4">
            <div class="d-flex align-items-center gap-2 mb-3">
                <div class="rounded-2 d-flex align-items-center justify-content-center" style="width:28px;height:28px;background:#e0f2fe;">
                    <i class="fas fa-info-circle text-primary" style="font-size:13px;"></i>
                </div>
                <h6 class="mb-0 fw-semibold" style="color:#1e293b;">Basic Information</h6>
            </div>
            <div class="row g-3">
                <div class="col-6 col-md-3">
                    <div class="p-3 rounded-3 border h-100" style="background:#f8fafc;">
                        <div style="color:#64748b; font-size:0.68rem; text-transform:uppercase; letter-spacing:.5px; font-weight:600; margin-bottom:4px;">Name</div>
                        <div class="fw-semibold" style="color:#1e293b;">${escapeHtml(species.name)}</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-3 rounded-3 border h-100" style="background:#f8fafc;">
                        <div style="color:#64748b; font-size:0.68rem; text-transform:uppercase; letter-spacing:.5px; font-weight:600; margin-bottom:4px;">Code</div>
                        <div class="fw-semibold" style="color:#1e293b;"><code>${escapeHtml(species.code)}</code></div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-3 rounded-3 border h-100" style="background:#f8fafc;">
                        <div style="color:#64748b; font-size:0.68rem; text-transform:uppercase; letter-spacing:.5px; font-weight:600; margin-bottom:4px;">Icon</div>
                        <div class="d-flex align-items-center gap-2">
                            ${iconHtml}
                            <small style="color:#64748b;">${escapeHtml(species.icon)}</small>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-3 rounded-3 border h-100" style="background:#f8fafc;">
                        <div style="color:#64748b; font-size:0.68rem; text-transform:uppercase; letter-spacing:.5px; font-weight:600; margin-bottom:4px;">Color</div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="rounded-2 border" style="width:22px;height:22px;background:${species.color_hex};display:inline-block;flex-shrink:0;"></span>
                            <small class="fw-semibold" style="color:#1e293b;">${species.color_hex}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lifecycle Parameters -->
        <div class="mb-4">
            <div class="d-flex align-items-center gap-2 mb-3">
                <div class="rounded-2 d-flex align-items-center justify-content-center" style="width:28px;height:28px;background:#fef3c7;">
                    <i class="fas fa-seedling text-warning" style="font-size:13px;"></i>
                </div>
                <h6 class="mb-0 fw-semibold" style="color:#1e293b;">Lifecycle Parameters</h6>
            </div>
            <div class="row g-2">
                ${[
                    { label: 'Gestation',       value: species.gestation_days,      icon: 'fa-egg',            color: '#8b5cf6' },
                    { label: 'Weaning',         value: species.weaning_days,        icon: 'fa-child',          color: '#06b6d4' },
                    { label: 'Sexual Maturity', value: species.sexual_maturity_days, icon: 'fa-venus-mars',    color: '#ec4899' },
                    { label: 'Market Age',      value: species.market_age_days,     icon: 'fa-store',          color: '#f59e0b' },
                    { label: 'Market Weight',   value: species.market_weight_kg,    icon: 'fa-weight-hanging', color: '#10b981' },
                    { label: 'Lifespan',        value: species.lifespan_years,      icon: 'fa-hourglass-half', color: '#6366f1' },
                ].map(item => `
                    <div class="col-6 col-md-4">
                        <div class="d-flex align-items-center gap-2 p-2 rounded-3 border" style="background:#f8fafc;">
                            <div class="rounded-2 d-flex align-items-center justify-content-center flex-shrink-0"
                                 style="width:32px;height:32px;background:${item.color}18;">
                                <i class="fas ${item.icon}" style="color:${item.color};font-size:13px;"></i>
                            </div>
                            <div>
                                <div style="color:#64748b; font-size:0.65rem; text-transform:uppercase; letter-spacing:.4px; font-weight:600;">${item.label}</div>
                                <div class="fw-semibold" style="font-size:0.9rem; color:#1e293b;">${item.value ?? 'N/A'}</div>
                            </div>
                        </div>
                    </div>`).join('')}
            </div>
        </div>

        <!-- Performance Metrics -->
        <div class="mb-4">
            <div class="d-flex align-items-center gap-2 mb-3">
                <div class="rounded-2 d-flex align-items-center justify-content-center" style="width:28px;height:28px;background:#dcfce7;">
                    <i class="fas fa-chart-bar text-success" style="font-size:13px;"></i>
                </div>
                <h6 class="mb-0 fw-semibold" style="color:#1e293b;">Default Performance Metrics</h6>
            </div>
            ${renderJson(species.default_metrics, 'No performance metrics configured')}
        </div>

        <!-- Growth Standards -->
        <div class="mb-4">
            <div class="d-flex align-items-center gap-2 mb-3">
                <div class="rounded-2 d-flex align-items-center justify-content-center" style="width:28px;height:28px;background:#ede9fe;">
                    <i class="fas fa-chart-line" style="color:#7c3aed; font-size:13px;"></i>
                </div>
                <h6 class="mb-0 fw-semibold" style="color:#1e293b;">Growth Standards</h6>
            </div>
            ${renderJson(species.growth_standards, 'No growth standards configured')}
        </div>

        <!-- Health Indicators -->
        <div class="mb-4">
            <div class="d-flex align-items-center gap-2 mb-3">
                <div class="rounded-2 d-flex align-items-center justify-content-center" style="width:28px;height:28px;background:#fee2e2;">
                    <i class="fas fa-heartbeat text-danger" style="font-size:13px;"></i>
                </div>
                <h6 class="mb-0 fw-semibold" style="color:#1e293b;">Health Indicators</h6>
            </div>
            ${renderJson(species.health_indicators, 'No health indicators configured')}
        </div>
    `;

    // Refresh Iconify so newly injected icons render
    if (window.Iconify) Iconify.scan(document.getElementById('viewSpeciesContent'));

    const toggleBtn = document.getElementById('toggleStatusBtn');
    if (toggleBtn && '{{ auth()->user()->role }}' === 'admin') {
        toggleBtn.style.display = 'inline-block';
        toggleBtn.onclick = () => toggleSpeciesStatus(currentSpeciesId);
    }
}
    
    // ==================== TOGGLE STATUS FUNCTION ====================
    function toggleSpeciesStatus(id) {
        Swal.fire({
            title: 'Toggle Status',
            text: 'Are you sure you want to change the status of this species?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#f59e0b',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, toggle status',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`/species/${id}/toggle-status-ajax`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Status Updated',
                            text: data.message,
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error', text: data.message });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({ icon: 'error', title: 'Error', text: 'An error occurred' });
                });
            }
        });
    }
    
    // ==================== EDIT SPECIES MODAL ====================
    let editModal = null;
    let currentEditId = null;
    
    document.querySelectorAll('.edit-species-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            closeAllModals();
            currentEditId = this.dataset.id;
            
            const modalElement = document.getElementById('editSpeciesModal');
            editModal = new bootstrap.Modal(modalElement, {
                backdrop: 'static',
                keyboard: true
            });
            const modalBody = document.getElementById('editSpeciesContent');
            
            modalBody.innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border text-warning" role="status"></div>
                    <p class="mt-2">Loading species details...</p>
                </div>
            `;
            editModal.show();
            
            fetch(`/species/${currentEditId}/edit-data`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displaySpeciesEditForm(data.species);
                } else {
                    modalBody.innerHTML = `<div class="alert alert-danger m-3">Failed to load details: ${data.message}</div>`;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                modalBody.innerHTML = `<div class="alert alert-danger m-3">Error loading data: ${error.message}</div>`;
            });
        });
    });
    
    document.getElementById('editSpeciesModal')?.addEventListener('hidden.bs.modal', function() {
        if (editModal) {
            editModal.dispose();
            editModal = null;
        }
        currentEditId = null;
        const backdrops = document.querySelectorAll('.modal-backdrop');
        backdrops.forEach(backdrop => backdrop.remove());
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
    });
    
    function displaySpeciesEditForm(species) {
        document.getElementById('editSpeciesContent').innerHTML = `
            <form id="editSpeciesForm">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="${escapeHtml(species.name)}" required>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Code <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control" value="${escapeHtml(species.code)}" maxlength="5" required>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Icon Class</label>
                        <input type="text" name="icon" class="form-control" value="${escapeHtml(species.icon)}" placeholder="e.g., fas fa-drumstick">
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Color (Hex)</label>
                        <input type="color" name="color_hex" class="form-control" value="${species.color_hex}">
                    </div>
                    
                    <div class="col-12 mb-3">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" class="form-control" rows="2">${escapeHtml(species.description || '')}</textarea>
                    </div>
                    
                    <div class="col-md-12 mb-3">
                        <div class="card">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">Lifecycle Parameters</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Gestation Days</label>
                                        <input type="number" name="gestation_days" class="form-control" value="${species.gestation_days || ''}" min="0">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Weaning Days</label>
                                        <input type="number" name="weaning_days" class="form-control" value="${species.weaning_days || ''}" min="0">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Sexual Maturity (Days)</label>
                                        <input type="number" name="sexual_maturity_days" class="form-control" value="${species.sexual_maturity_days || ''}" min="0">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Market Age (Days)</label>
                                        <input type="number" name="market_age_days" class="form-control" value="${species.market_age_days || ''}" min="0">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Market Weight (kg)</label>
                                        <input type="number" name="market_weight_kg" class="form-control" step="0.01" value="${species.market_weight_kg || ''}" min="0">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Lifespan (Years)</label>
                                        <input type="number" name="lifespan_years" class="form-control" value="${species.lifespan_years || ''}" min="0">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-12 mb-3">
                        <div class="card">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">Default Performance Metrics (JSON)</h6>
                            </div>
                            <div class="card-body">
                                <textarea name="default_metrics" class="form-control" rows="3">${escapeHtml(species.default_metrics || '')}</textarea>
                                <small class="text-muted">Enter as valid JSON format</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-12 mb-3">
                        <div class="card">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">Growth Standards (JSON)</h6>
                            </div>
                            <div class="card-body">
                                <textarea name="growth_standards" class="form-control" rows="3">${escapeHtml(species.growth_standards || '')}</textarea>
                                <small class="text-muted">Enter as valid JSON format</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-12 mb-3">
                        <div class="card">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">Health Indicators (JSON)</h6>
                            </div>
                            <div class="card-body">
                                <textarea name="health_indicators" class="form-control" rows="3">${escapeHtml(species.health_indicators || '')}</textarea>
                                <small class="text-muted">Enter as valid JSON format</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active_edit" ${species.is_active ? 'checked' : ''}>
                            <label class="form-check-label" for="is_active_edit">Active (available for selection)</label>
                        </div>
                    </div>
                </div>
            </form>
        `;
    }
    
    document.getElementById('saveEditSpecies')?.addEventListener('click', function() {
        const form = document.getElementById('editSpeciesForm');
        if (!form) return;
        
        const formData = new FormData(form);
        const data = {};
        formData.forEach((value, key) => { data[key] = value; });
        
        // Validate JSON fields
        const jsonFields = ['default_metrics', 'growth_standards', 'health_indicators'];
        for (let field of jsonFields) {
            if (data[field] && data[field].trim()) {
                try {
                    JSON.parse(data[field]);
                } catch (e) {
                    Swal.fire({ icon: 'error', title: 'Invalid JSON', text: `${field.replace('_', ' ')} has invalid JSON format` });
                    return;
                }
            }
        }
        
        const saveBtn = this;
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Updating...';
        
        fetch(`/species/${currentEditId}/update-ajax`, {
            method: 'PUT',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (editModal) editModal.hide();
                Swal.fire({ icon: 'success', title: 'Updated!', text: 'Species updated successfully', timer: 1500, showConfirmButton: false })
                    .then(() => window.location.reload());
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Failed to update species' });
                saveBtn.disabled = false;
                saveBtn.innerHTML = 'Update Species';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({ icon: 'error', title: 'Error', text: 'An error occurred while updating' });
            saveBtn.disabled = false;
            saveBtn.innerHTML = 'Update Species';
        });
    });
    
    // ==================== DELETE SPECIES (Admin Only) ====================
    document.querySelectorAll('.delete-species-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const name = this.dataset.name;
            
            Swal.fire({
                title: 'Delete Species',
                text: `Are you sure you want to delete "${name}"? This action cannot be undone.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`/species/${id}/destroy-ajax`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({ icon: 'success', title: 'Deleted!', text: data.message, timer: 1500, showConfirmButton: false })
                                .then(() => window.location.reload());
                        } else {
                            Swal.fire({ icon: 'error', title: 'Error', text: data.message });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({ icon: 'error', title: 'Error', text: 'An error occurred while deleting' });
                    });
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
});
</script>
@endpush

@endsection