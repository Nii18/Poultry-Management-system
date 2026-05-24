@extends('layouts.master')

@section('title', 'Total Animals Report')

@section('content')
<div class="container-fluid px-4">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <div class="d-flex align-items-center gap-3">
                    <div class="page-icon bg-success-soft">
                        <i class="fas fa-paw fs-1 text-success"></i>
                    </div>
                    <div>
                        <h1 class="page-title mb-1">Total Animals Report</h1>
                        <p class="page-description text-muted mb-0">Detailed breakdown of all animals across flocks</p>
                    </div>
                </div>
            </div>
            <div class="col-auto">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Total Animals</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <!-- Species Filter -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('reports.total-animals') }}" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Filter by Species</label>
                    <select name="species_id" class="form-select">
                        <option value="">All Species</option>
                        @foreach($speciesBreakdown as $species)
                            <option value="{{ $species['id'] }}" {{ request('species_id') == $species['id'] ? 'selected' : '' }}>
                                {{ $species['name'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-2"></i>Filter
                    </button>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <a href="{{ route('reports.total-animals') }}" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-times me-2"></i>Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-card-icon bg-primary-soft">
                        <i class="fas fa-paw text-primary"></i>
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
                    <div class="stat-card-icon bg-success-soft">
                        <i class="fas fa-play-circle text-success"></i>
                    </div>
                    <div class="stat-card-info">
                        <span class="stat-card-label">Active Flocks</span>
                        <h3 class="stat-card-value">{{ $activeFlocksCount }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-card-icon bg-info-soft">
                        <i class="fas fa-tag text-info"></i>
                    </div>
                    <div class="stat-card-info">
                        <span class="stat-card-label">Species Types</span>
                        <h3 class="stat-card-value">{{ $speciesCount }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-card-icon bg-warning-soft">
                        <i class="fas fa-building text-warning"></i>
                    </div>
                    <div class="stat-card-info">
                        <span class="stat-card-label">Houses Used</span>
                        <h3 class="stat-card-value">{{ $housesUsed }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- By Species -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white border-0 py-3">
            <h5 class="card-title mb-0 fw-semibold">
                <i class="fas fa-chart-pie me-2 text-primary"></i>By Species
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Species</th>
                            <th>Active Flocks</th>
                            <th>Total Animals</th>
                            <th>% of Total</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($speciesBreakdown as $species)
                        <tr>
                            <td>
                                <i class="{{ $species['icon'] }} me-2" style="color: {{ $species['color'] }}"></i>
                                {{ $species['name'] }}
                            </td>
                            <td>{{ $species['flock_count'] }}</td>
                            <td><strong>{{ number_format($species['total_animals']) }}</strong></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height: 6px;">
                                        <div class="progress-bar bg-success" style="width: {{ $species['percentage'] }}%"></div>
                                    </div>
                                    <span>{{ number_format($species['percentage'], 1) }}%</span>
                                </div>
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-primary view-species-btn" 
                                        data-id="{{ $species['id'] }}" data-bs-toggle="modal" data-bs-target="#viewSpeciesModal">
                                    <i class="fas fa-eye"></i> View
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>Total</th>
                            <th>{{ $totalFlocks }}</th>
                            <th><strong>{{ number_format($totalAnimals) }}</strong></th>
                            <th>100%</th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- By Flock -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-0 py-3">
            <h5 class="card-title mb-0 fw-semibold">
                <i class="fas fa-list me-2 text-primary"></i>Flock Breakdown
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Flock Number</th>
                            <th>Species</th>
                            <th>Breed</th>
                            <th>House</th>
                            <th>Current Count</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($flocks as $flock)
                        <tr>
                            <td>
                                <button type="button" class="btn btn-link p-0 text-primary fw-semibold text-decoration-none view-flock-btn" 
                                        data-id="{{ $flock->id }}" data-bs-toggle="modal" data-bs-target="#viewFlockModal">
                                    {{ $flock->flock_number }}
                                </button>
                            </td>
                            <td>{{ $flock->species->name ?? 'N/A' }}</td>
                            <td>{{ $flock->breed_variety }}</td>
                            <td>{{ $flock->house->name ?? 'N/A' }}</td>
                            <td><strong>{{ number_format($flock->current_count) }}</strong> / {{ number_format($flock->initial_count) }}</td>
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
                                <span class="badge bg-{{ $statusColor }}-soft text-{{ $statusColor }}">
                                    {{ ucfirst($flock->status) }}
                                </span>
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-primary view-flock-btn" 
                                        data-id="{{ $flock->id }}" data-bs-toggle="modal" data-bs-target="#viewFlockModal">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
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

@push('styles')
<style>
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
    
    .progress {
        background-color: #e2e8f0;
        border-radius: 10px;
        overflow: hidden;
    }
    
    .pagination .page-link {
        border-radius: 8px;
        margin: 0 2px;
        border: none;
        color: #475569;
        padding: 0.5rem 0.875rem;
    }
    
    .pagination .page-item.active .page-link {
        background-color: #0d6e4f;
        color: white;
    }
    
    .pagination .page-link:hover {
        background-color: #e2e8f0;
        color: #0d6e4f;
    }
    
    .detail-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1rem;
    }
</style>
@endpush

@push('scripts')
<script>
function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}

// View Flock buttons
document.querySelectorAll('.view-flock-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const flockId = this.dataset.id;
        const modalBody = document.getElementById('viewFlockContent');
        
        modalBody.innerHTML = `<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Loading flock details...</p></div>`;
        
        fetch(`/flocks/${flockId}/details`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayFlockDetailsInModal(data.flock, data.summary);
                } else {
                    modalBody.innerHTML = `<div class="alert alert-danger">Failed to load details: ${data.message}</div>`;
                }
            })
            .catch(error => {
                modalBody.innerHTML = `<div class="alert alert-danger">Error loading data</div>`;
            });
    });
});

function displayFlockDetailsInModal(flock, summary) {
    const statusClass = {
        'active': 'success',
        'closed': 'secondary',
        'quarantined': 'danger',
        'breeding': 'info'
    };
    
    document.getElementById('viewFlockContent').innerHTML = `
        <div class="detail-section">
            <h6>Basic Information</h6>
            <div class="detail-grid">
                <div class="detail-item"><span class="detail-label">Flock Number</span><span class="detail-value">${escapeHtml(flock.flock_number)}</span></div>
                <div class="detail-item"><span class="detail-label">Species</span><span class="detail-value">${escapeHtml(flock.species_name)}</span></div>
                <div class="detail-item"><span class="detail-label">House</span><span class="detail-value">${escapeHtml(flock.house_name)}</span></div>
                <div class="detail-item"><span class="detail-label">Breed</span><span class="detail-value">${escapeHtml(flock.breed_variety)}</span></div>
                <div class="detail-item"><span class="detail-label">Current Count</span><span class="detail-value">${summary.current_count.toLocaleString()} / ${flock.initial_count.toLocaleString()}</span></div>
                <div class="detail-item"><span class="detail-label">Status</span><span class="badge bg-${statusClass[flock.status]}-soft text-${statusClass[flock.status]}">${flock.status}</span></div>
            </div>
        </div>
    `;
}

// View Species buttons
document.querySelectorAll('.view-species-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const speciesId = this.dataset.id;
        const modal = new bootstrap.Modal(document.getElementById('viewSpeciesModal'));
        const modalBody = document.getElementById('viewSpeciesContent');
        
        modalBody.innerHTML = `<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Loading species details...</p></div>`;
        modal.show();
        
        fetch(`/species/${speciesId}/details-json`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displaySpeciesDetailsInModal(data.species);
                } else {
                    modalBody.innerHTML = `<div class="alert alert-danger">Failed to load details</div>`;
                }
            })
            .catch(error => {
                modalBody.innerHTML = `<div class="alert alert-danger">Error loading data</div>`;
            });
    });
});

function displaySpeciesDetailsInModal(species) {
    const statusBadge = species.is_active 
        ? '<span class="badge bg-success-soft text-success">Active</span>'
        : '<span class="badge bg-secondary-soft text-secondary">Inactive</span>';
    
    document.getElementById('viewSpeciesContent').innerHTML = `
        <div class="text-center mb-4">
            <i class="${species.icon} fs-1" style="color: ${species.color_hex}"></i>
            <h3 class="mt-2">${escapeHtml(species.name)}</h3>
            <p class="text-muted">Code: ${escapeHtml(species.code)}</p>
            ${statusBadge}
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <div class="bg-light p-3 rounded">
                    <small class="text-muted">Total Flocks</small>
                    <h4>${species.stats.flock_count}</h4>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="bg-light p-3 rounded">
                    <small class="text-muted">Active Flocks</small>
                    <h4>${species.stats.active_flocks}</h4>
                </div>
            </div>
            <div class="col-12">
                <div class="bg-light p-3 rounded">
                    <small class="text-muted">Total Animals</small>
                    <h4>${species.stats.total_animals}</h4>
                </div>
            </div>
        </div>
    `;
}
</script>
@endpush
@endsection