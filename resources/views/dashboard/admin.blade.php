{{-- resources/views/dashboard/admin.blade.php --}}
@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6 col-12">
                <h2>Livestock Dashboard</h2>
                <p class="mb-0 text-title-gray">Welcome back, {{ Auth::user()->name ?? 'Farm Manager' }}! Here's your farm overview.</p>
            </div>
            <div class="col-sm-6 col-12">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="iconly-Home icli svg-color"></i></a></li>
                    <li class="breadcrumb-item">Dashboard</li>
                    <li class="breadcrumb-item active">Livestock</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Container-fluid starts -->
<div class="container-fluid dashboard-2">
    <div class="row">
        <!-- Stats Cards Row - CLICKABLE -->
        <div class="col-xxl-3 col-xl-4 col-sm-6 box-col-4">
            <div class="card clickable-card" onclick="window.location.href='{{ route('flocks.index', ['status' => 'active']) }}'">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="flex-shrink-0 bg-light-primary rounded-circle p-3">
                            <i class="fas fa-users text-primary fs-4"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="mb-1">Active Flocks</h5>
                            <h3 class="mb-0">{{ $activeFlocks->count() }}</h3>
                            <small class="text-muted">Closed: {{ $closedFlocks }}</small>
                        </div>
                        <i class="fas fa-arrow-right text-muted"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xxl-3 col-xl-4 col-sm-6 box-col-4">
            <div class="card clickable-card" onclick="window.location.href='{{ route('flocks.index') }}'">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="flex-shrink-0 bg-light-success rounded-circle p-3">
                            <i class="fas fa-paw text-success fs-4"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="mb-1">Total Animals</h5>
                            <h3 class="mb-0">{{ number_format($totalAnimals) }}</h3>
                            <small class="text-muted">Active flocks only</small>
                        </div>
                        <i class="fas fa-arrow-right text-muted"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xxl-3 col-xl-4 col-sm-6 box-col-4">
            <div class="card clickable-card" onclick="window.location.href='{{ route('daily-logs.index') }}'">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="flex-shrink-0 bg-light-danger rounded-circle p-3">
                            <i class="fas fa-skull text-danger fs-4"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="mb-1">Today's Mortality</h5>
                            <h3 class="mb-0 {{ $totalMortalityToday > 10 ? 'text-danger' : '' }}">{{ number_format($totalMortalityToday) }}</h3>
                            <small class="text-muted">Across all flocks</small>
                        </div>
                        <i class="fas fa-arrow-right text-muted"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xxl-3 col-xl-4 col-sm-6 box-col-4">
            <div class="card clickable-card" onclick="window.location.href='{{ route('reports.performance') }}'">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="flex-shrink-0 bg-light-warning rounded-circle p-3">
                            <i class="fas fa-chart-line text-warning fs-4"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="mb-1">Avg Feed Conversion</h5>
                            <h3 class="mb-0">{{ number_format($avgFCR, 2) }}</h3>
                            <small class="text-muted">FCR (lower is better)</small>
                        </div>
                        <i class="fas fa-arrow-right text-muted"></i>
                    </div>
                </div>
            </div>
        </div>

       <!-- Species Overview Section - CLICKABLE MODAL -->
@if(count($speciesStats) > 0)
<div class="col-12">
    <div class="card">
        <div class="card-header card-no-border pb-0">
            <div class="header-top">
                <h3>Species Overview</h3>
                <div class="dropdown icon-dropdown">
                    <button class="btn" id="speciesDropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa-solid fa-ellipsis"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="speciesDropdown">
                        <a class="dropdown-item" href="#" onclick="filterBySpecies('all')">All Species</a>
                        @foreach($speciesStats as $code => $stats)
                            <a class="dropdown-item" href="#" onclick="filterBySpecies('{{ $code }}')">{{ $stats['name'] }}</a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach($speciesStats as $code => $stats)
                    @php
                        $speciesId = $speciesIdMap[$code] ?? 0;
                    @endphp
                    <div class="col-xxl-2 col-xl-3 col-md-4 col-sm-6 mb-3 species-card" 
                         data-species="{{ $code }}" 
                         data-species-id="{{ $speciesId }}">
                        <div class="text-center p-3 border rounded-3 hover-shadow transition clickable-species" 
                             onclick="viewSpeciesDetails({{ $speciesId }})"
                             style="cursor: pointer;">
                            <i class="{{ $stats['icon'] }} fs-1" style="color: {{ $stats['color'] }}"></i>
                            <h6 class="mt-2 mb-1">{{ $stats['name'] }}</h6>
                            <p class="mb-0 text-muted small">{{ number_format($stats['total_animals']) }} animals</p>
                            <p class="mb-0 text-muted small">{{ $stats['active_flocks'] }} flocks</p>
                            <p class="mb-0 fw-bold">FCR: {{ $stats['avg_fcr'] ?? 'N/A' }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endif

        <!-- Charts Row -->
        <div class="col-xxl-6 col-xl-7 col-lg-12 box-col-7">
            <div class="card analytics-card">
                <div class="card-header card-no-border pb-0">
                    <div class="header-top">
                        <h3>Mortality Trend (Last 7 Days)</h3>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="mortalityChart" height="300"></canvas>
                </div>
            </div>
        </div>

        <div class="col-xxl-6 col-xl-5 col-md-12 box-col-12">
            <div class="card">
                <div class="card-header card-no-border pb-0">
                    <div class="header-top">
                        <h3>Feed Consumption Trend</h3>
                        <div class="dropdown icon-dropdown">
                            <button class="btn" id="feedDropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa-solid fa-ellipsis"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="feedDropdown">
                                <a class="dropdown-item" href="#" onclick="changeFeedPeriod('week')">Weekly</a>
                                <a class="dropdown-item" href="#" onclick="changeFeedPeriod('month')">Monthly</a>
                                <a class="dropdown-item" href="#" onclick="changeFeedPeriod('year')">Yearly</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="feedChart" height="300"></canvas>
                </div>
            </div>
        </div>

        <!-- Active Flocks Table - MODAL BUTTONS -->
        <div class="col-12">
            <div class="card">
                <div class="card-header card-no-border pb-0">
                    <div class="header-top">
                        <h3>Active Flocks</h3>
                        <a href="{{ route('flocks.index') }}" class="btn btn-primary btn-sm">View All</a>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="table-responsive theme-scrollbar">
                        <table class="table display table-bordernone" id="active-flocks-table" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Flock Number</th>
                                    <th>Species</th>
                                    <th>House</th>
                                    <th>Breed</th>
                                    <th>Age (Days)</th>
                                    <th>Count</th>
                                    <th>Mortality</th>
                                    <th>FCR</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($activeFlocks as $flock)
                                    <tr>
                                        <td>
                                            <button type="button" class="btn btn-link p-0 text-primary fw-semibold text-decoration-none view-flock-btn" 
                                                    data-id="{{ $flock->id }}" data-bs-toggle="modal" data-bs-target="#viewFlockModal">
                                                {{ $flock->flock_number }}
                                            </button>
                                        </td>
                                        <td>{{ $flock->species->name ?? 'N/A' }}</td>
                                        <td>{{ $flock->house->name ?? 'N/A' }}</td>
                                        <td>{{ $flock->breed_variety }}</td>
                                        <td>{{ $flock->age_in_days }}</td>
                                        <td>{{ number_format($flock->current_count) }} / {{ number_format($flock->initial_count) }}</td>
                                        <td>
                                            <span class="badge {{ $flock->mortality_rate > 5 ? 'badge-danger' : 'badge-success' }}">
                                                {{ $flock->mortality_rate }}%
                                            </span>
                                        </td>
                                        <td>{{ number_format($flock->feed_conversion_ratio, 2) }}</td>
                                        <td>
                                            <div class="btn-group gap-1">
                                                <button type="button" class="btn btn-sm btn-outline-primary view-flock-btn" 
                                                        data-id="{{ $flock->id }}" data-bs-toggle="modal" data-bs-target="#viewFlockModal" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-success" 
                                                        onclick="redirectAndOpenModal('daily-logs.index', 'createLogModal')"
                                                        title="Add Daily Log">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center">No active flocks found. <a href="{{ route('flocks.create') }}">Create your first flock</a></td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>


        <!-- View Species Modal -->
<div class="modal fade" id="viewSpeciesModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-success text-white border-0 py-3">
                <div>
                    <h4 class="modal-title mb-0">
                        <i class="fas fa-paw me-2"></i>Species Details
                    </h4>
                    <small class="opacity-75" id="modalSpeciesName">Loading species information...</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body bg-light" id="viewSpeciesContent">
                <div class="text-center py-5">
                    <div class="spinner-border text-success" style="width: 3rem; height: 3rem;" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3 text-muted">Loading species details...</p>
                </div>
            </div>
            <div class="modal-footer bg-white border-0">
                <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Close
                </button>
            </div>
        </div>
    </div>
</div>

        <!-- Recent Activities and Alerts -->
        <div class="col-xxl-6 col-xl-5 col-sm-6 box-col-6">
            <div class="card">
                <div class="card-header card-no-border pb-0">
                    <div class="header-top">
                        <h3>Recent Activities</h3>
                    </div>
                </div>
                <div class="card-body activity-timeline">
                    <div class="activity-timeline">
                        @forelse($recentActivities as $activity)
                            <div class="d-flex align-items-start mb-3">
                                <div class="activity-dot-primary me-3"></div>
                                <div class="flex-grow-1">
                                    <p class="mb-1">
                                        <span class="font-primary">{{ $activity->log_date->format('Y-m-d') }}</span>
                                    </p>
                                    <span class="fw-bold">Daily Log for {{ $activity->flock->flock_number }}</span>
                                    <p class="mb-0 activity-text">
                                        Mortality: {{ $activity->mortality_count }} | 
                                        Feed: {{ number_format($activity->feed_intake_kg) }} kg
                                    </p>
                                    <small class="text-muted">{{ $activity->created_at->diffForHumans() }}</small>
                                </div>
                                <button type="button" class="btn btn-link text-primary p-0" onclick="viewDailyLog({{ $activity->id }})">
                                    <i class="fa fa-arrow-right"></i>
                                </button>
                            </div>
                        @empty
                            <p class="text-center text-muted py-4">No recent activities</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xxl-6 col-xl-7 col-sm-6 box-col-6">
            <div class="card">
                <div class="card-header card-no-border pb-0">
                    <div class="header-top">
                        <h3>Active Alerts</h3>
                        @if($criticalAlertsCount > 0)
                            <span class="badge badge-danger">{{ $criticalAlertsCount }} Critical</span>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="activity-timeline">
                        @forelse($activeAlerts as $alert)
                            <div class="d-flex align-items-start mb-3 p-2 rounded 
                                {{ $alert->severity === 'critical' ? 'bg-light-danger' : ($alert->severity === 'warning' ? 'bg-light-warning' : 'bg-light-primary') }}">
                                <div class="flex-shrink-0 me-3">
                                    @if($alert->severity === 'critical')
                                        <i class="fa fa-exclamation-circle text-danger fs-5"></i>
                                    @elseif($alert->severity === 'warning')
                                        <i class="fa fa-exclamation-triangle text-warning fs-5"></i>
                                    @else
                                        <i class="fa fa-info-circle text-primary fs-5"></i>
                                    @endif
                                </div>
                                <div class="flex-grow-1">
                                    <span class="fw-bold">{{ $alert->title }}</span>
                                    <p class="mb-0 small">{{ $alert->message }}</p>
                                    <small class="text-muted">{{ $alert->created_at->diffForHumans() }}</small>
                                </div>
                                <a href="{{ route('notifications.show', $alert->id) }}" class="text-muted">
                                    <i class="fa fa-arrow-right"></i>
                                </a>
                            </div>
                        @empty
                            <p class="text-center text-muted py-4">No active alerts</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Low Stock Alerts -->
        @if($lowFeedStock->count() > 0)
        <div class="col-12">
            <div class="card">
                <div class="card-header card-no-border pb-0">
                    <div class="header-top">
                        <h3>Low Stock Alerts</h3>
                        <i class="fa fa-exclamation-triangle text-warning"></i>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($lowFeedStock as $stock)
                            <div class="col-md-4 mb-3">
                                <div class="p-3 border rounded-3 bg-light-warning">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1">{{ $stock->name }}</h6>
                                            <p class="mb-0 small">Remaining: {{ number_format($stock->remaining_quantity_kg) }} kg</p>
                                        </div>
                                        <a href="{{ route('feed-deliveries.low-stock') }}" class="text-warning">
                                            <i class="fa fa-truck"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Financial Summary -->
        <div class="col-12">
            <div class="card clickable-card" onclick="window.location.href='{{ route('reports.financial') }}'">
                <div class="card-header card-no-border pb-0">
                    <div class="header-top">
                        <h3>Financial Summary</h3>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="text-center p-4 border rounded-3">
                                <p class="text-muted mb-1">Current Month Expenses</p>
                                <h3 class="text-danger">₵{{ number_format($currentMonthExpenses, 2) }}</h3>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="text-center p-4 border rounded-3">
                                <p class="text-muted mb-1">Year to Date Revenue</p>
                                <h3 class="text-success">₵0.00</h3>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="text-center p-4 border rounded-3">
                                <p class="text-muted mb-1">Net Profit (YTD)</p>
                                <h3 class="text-primary">₵0.00</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- View Flock Modal -->
<div class="modal fade" id="viewFlockModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white border-0 py-3">
                <div>
                    <h4 class="modal-title mb-0">
                        <i class="fas fa-tractor me-2"></i>Flock Details
                    </h4>
                    <small class="opacity-75" id="modalFlockNumber">Loading flock information...</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body bg-light" id="viewFlockContent">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3 text-muted">Loading flock details...</p>
                </div>
            </div>
            <div class="modal-footer bg-white border-0">
                <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- View Species Modal -->
<div class="modal fade" id="viewSpeciesModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-success text-white border-0 py-3">
                <div>
                    <h4 class="modal-title mb-0">
                        <i class="fas fa-paw me-2"></i>Species Details
                    </h4>
                    <small class="opacity-75" id="modalSpeciesName">Loading species information...</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body bg-light" id="viewSpeciesContent">
                <div class="text-center py-5">
                    <div class="spinner-border text-success" style="width: 3rem; height: 3rem;" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3 text-muted">Loading species details...</p>
                </div>
            </div>
            <div class="modal-footer bg-white border-0">
                <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Close
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let mortalityChart, feedChart;

document.addEventListener('DOMContentLoaded', function() {
    // Initialize Mortality Chart
    const mortalityData = @json($mortalityTrend);
    const mortalityCtx = document.getElementById('mortalityChart').getContext('2d');
    
    const labels = Object.keys(mortalityData);
    const values = Object.values(mortalityData);
    
    if (labels.length > 0) {
        mortalityChart = new Chart(mortalityCtx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Mortality Count',
                    data: values,
                    borderColor: '#dc2626',
                    backgroundColor: 'rgba(220, 38, 38, 0.1)',
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: { legend: { position: 'top' } },
                scales: {
                    y: { beginAtZero: true, title: { display: true, text: 'Number of Deaths' } },
                    x: { title: { display: true, text: 'Date' } }
                }
            }
        });
    }

    // Initialize Feed Consumption Chart (Weekly by default)
    loadFeedData('week');
});

function loadFeedData(period) {
    fetch('{{ route("dashboard.charts") }}?period=' + period)
        .then(response => response.json())
        .then(data => {
            const feedCtx = document.getElementById('feedChart').getContext('2d');
            
            if (feedChart) {
                feedChart.destroy();
            }
            
            const labels = data.feed_consumption.map(item => item.date);
            const values = data.feed_consumption.map(item => item.total_feed);
            
            if (labels.length > 0) {
                feedChart = new Chart(feedCtx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Feed Consumption (kg)',
                            data: values,
                            backgroundColor: 'rgba(34, 197, 94, 0.5)',
                            borderColor: '#22c55e',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        scales: { 
                            y: { beginAtZero: true, title: { display: true, text: 'Feed (kg)' } },
                            x: { title: { display: true, text: 'Date' } }
                        }
                    }
                });
            }
        })
        .catch(error => {
            console.error('Error loading feed data:', error);
        });
}

function changeFeedPeriod(period) {
    loadFeedData(period);
}

function filterBySpecies(speciesCode) {
    const cards = document.querySelectorAll('.species-card');
    if (speciesCode === 'all') {
        cards.forEach(card => card.style.display = 'block');
    } else {
        cards.forEach(card => {
            if (card.dataset.species === speciesCode) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    }
}

// ==================== VIEW FLOCK MODAL ====================
document.querySelectorAll('.view-flock-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const flockId = this.dataset.id;
        const modalBody = document.getElementById('viewFlockContent');
        
        modalBody.innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-3 text-muted">Loading flock details...</p>
            </div>
        `;
        
        fetch(`/flocks/${flockId}/details`)
            .then(response => {
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    displayFlockDetails(data.flock, data.summary);
                } else {
                    modalBody.innerHTML = `<div class="alert alert-danger m-3">Failed to load flock details: ${data.message || 'Unknown error'}</div>`;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                modalBody.innerHTML = `<div class="alert alert-danger m-3">Error loading data: ${error.message}</div>`;
            });
    });
});

function displayFlockDetails(flock, summary) {
    const statusConfig = {
        'active': { class: 'bg-success-soft text-success', icon: 'fa-play-circle' },
        'closed': { class: 'bg-secondary-soft text-secondary', icon: 'fa-stop-circle' },
        'quarantined': { class: 'bg-danger-soft text-danger', icon: 'fa-exclamation-triangle' },
        'breeding': { class: 'bg-info-soft text-info', icon: 'fa-heart' }
    };
    const status = statusConfig[flock.status] || statusConfig['active'];
    
    const prodIcons = {
        'meat': 'fa-drumstick-bite',
        'eggs': 'fa-egg',
        'milk': 'fa-tint',
        'breeding': 'fa-heart',
        'dual_purpose': 'fa-chart-line'
    };
    const prodIcon = prodIcons[flock.production_type] || 'fa-tag';
    
    document.getElementById('modalFlockNumber').innerHTML = `<i class="fas fa-tag me-1"></i> ${escapeHtml(flock.flock_number)}`;
    
    document.getElementById('viewFlockContent').innerHTML = `
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="stat-card-sm">
                    <div class="stat-icon-sm bg-primary-soft"><i class="fas fa-calendar-alt text-primary"></i></div>
                    <div class="stat-info-sm">
                        <span class="stat-label-sm">Age</span>
                        <h4 class="stat-value-sm mb-0">${summary.age_days} <small class="text-muted">days</small></h4>
                        <small>(${summary.age_weeks} weeks)</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card-sm">
                    <div class="stat-icon-sm bg-success-soft"><i class="fas fa-chicken text-success"></i></div>
                    <div class="stat-info-sm">
                        <span class="stat-label-sm">Population</span>
                        <h4 class="stat-value-sm mb-0">${summary.current_count.toLocaleString()}</h4>
                        <small>/ ${flock.initial_count.toLocaleString()} total</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card-sm ${summary.mortality_rate > 5 ? 'bg-danger-soft' : 'bg-warning-soft'}">
                    <div class="stat-icon-sm"><i class="fas fa-skull ${summary.mortality_rate > 5 ? 'text-danger' : 'text-warning'}"></i></div>
                    <div class="stat-info-sm">
                        <span class="stat-label-sm">Mortality Rate</span>
                        <h4 class="stat-value-sm mb-0">${summary.mortality_rate}%</h4>
                        <small>Survival: ${summary.survival_rate}%</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card-sm">
                    <div class="stat-icon-sm bg-info-soft"><i class="fas fa-chart-line text-info"></i></div>
                    <div class="stat-info-sm">
                        <span class="stat-label-sm">FCR</span>
                        <h4 class="stat-value-sm mb-0">${summary.fcr}</h4>
                        <small>Feed Conversion Ratio</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-md-6">
                <div class="info-card">
                    <div class="info-card-header"><i class="fas fa-info-circle text-primary me-2"></i><h6 class="mb-0">Basic Information</h6></div>
                    <div class="info-card-body">
                        <div class="info-row"><span class="info-label">Flock Number</span><span class="info-value">${escapeHtml(flock.flock_number)}</span></div>
                        <div class="info-row"><span class="info-label">Species</span><span class="info-value">${escapeHtml(flock.species_name)} <span class="text-muted">(${escapeHtml(flock.species_code)})</span></span></div>
                        <div class="info-row"><span class="info-label">Breed / Variety</span><span class="info-value">${escapeHtml(flock.breed_variety)}</span></div>
                        <div class="info-row"><span class="info-label">House</span><span class="info-value">${escapeHtml(flock.house_name)} <span class="text-muted">(${escapeHtml(flock.house_code)})</span></span></div>
                        <div class="info-row"><span class="info-label">Start Date</span><span class="info-value">${escapeHtml(flock.start_date)}</span></div>
                        <div class="info-row"><span class="info-label">Source</span><span class="info-value">${escapeHtml(flock.source || 'Not specified')}</span></div>
                        <div class="info-row"><span class="info-label">Status</span><span class="info-value"><span class="badge ${status.class} px-3 py-2 rounded-pill"><i class="fas ${status.icon} me-1"></i> ${flock.status.charAt(0).toUpperCase() + flock.status.slice(1)}</span></span></div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="info-card">
                    <div class="info-card-header"><i class="fas fa-chart-simple text-success me-2"></i><h6 class="mb-0">Production Information</h6></div>
                    <div class="info-card-body">
                        <div class="info-row"><span class="info-label">Production Type</span><span class="info-value"><i class="fas ${prodIcon} me-1"></i> ${flock.production_type.charAt(0).toUpperCase() + flock.production_type.slice(1).replace('_', ' ')}</span></div>
                        <div class="info-row"><span class="info-label">Breeding Stock</span><span class="info-value">${flock.is_breeding_stock ? '<span class="badge bg-primary-soft text-primary"><i class="fas fa-check-circle me-1"></i> Yes</span>' : '<span class="badge bg-secondary-soft text-secondary"><i class="fas fa-times-circle me-1"></i> No</span>'}</span></div>
                        <div class="info-row"><span class="info-label">Parity Number</span><span class="info-value">${flock.parity_number || 'N/A'}</span></div>
                        <div class="info-row"><span class="info-label">Current Count</span><span class="info-value">${summary.current_count.toLocaleString()} animals</span></div>
                        <div class="info-row"><span class="info-label">Initial Count</span><span class="info-value">${flock.initial_count.toLocaleString()} animals</span></div>
                        <div class="info-row"><span class="info-label">Total Feed Consumed</span><span class="info-value">${summary.total_feed.toLocaleString()} kg</span></div>
                        <div class="info-row"><span class="info-label">Avg Daily Gain</span><span class="info-value">${summary.avg_daily_gain} kg/day</span></div>
                    </div>
                </div>
            </div>
            ${flock.notes ? `<div class="col-12"><div class="info-card"><div class="info-card-header"><i class="fas fa-pencil-alt text-warning me-2"></i><h6 class="mb-0">Additional Notes</h6></div><div class="info-card-body"><p class="mb-0">${escapeHtml(flock.notes)}</p></div></div></div>` : ''}
        </div>
    `;
}

// ==================== VIEW SPECIES MODAL ====================
function viewSpeciesDetails(speciesId) {
    if (!speciesId) {
        Swal.fire({ icon: 'error', title: 'Error', text: 'Species ID not found' });
        return;
    }
    
    const modal = new bootstrap.Modal(document.getElementById('viewSpeciesModal'));
    const modalBody = document.getElementById('viewSpeciesContent');
    
    modalBody.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-success" style="width: 3rem; height: 3rem;" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-3 text-muted">Loading species details...</p>
        </div>
    `;
    modal.show();
    
    fetch(`/species/${speciesId}/details-json`, {
        headers: { 'Accept': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displaySpeciesDetails(data.species);
        } else {
            modalBody.innerHTML = `<div class="alert alert-danger m-3">Failed to load species details: ${data.message}</div>`;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        modalBody.innerHTML = `<div class="alert alert-danger m-3">Error loading data: ${error.message}</div>`;
    });
}

function displaySpeciesDetails(species) {
    document.getElementById('modalSpeciesName').innerHTML = `<i class="fas fa-paw me-1"></i> ${escapeHtml(species.name)}`;
    
    const statusBadge = species.is_active 
        ? '<span class="badge bg-success-soft text-success px-3 py-2 rounded-pill"><i class="fas fa-circle me-1" style="font-size: 8px;"></i>Active</span>'
        : '<span class="badge bg-secondary-soft text-secondary px-3 py-2 rounded-pill"><i class="fas fa-circle me-1" style="font-size: 8px;"></i>Inactive</span>';
    
    document.getElementById('viewSpeciesContent').innerHTML = `
        <div class="text-center mb-4">
            <i class="${species.icon} fs-1" style="color: ${species.color_hex}"></i>
            <h3 class="mt-2 mb-0">${escapeHtml(species.name)}</h3>
            <p class="text-muted">Code: ${escapeHtml(species.code)}</p>
            <div>${statusBadge}</div>
        </div>
        
        <div class="row g-3 mb-4">
            <div class="col-md-4"><div class="stats-card-sm"><div class="stats-number">${species.stats.flock_count}</div><div class="stats-label">Total Flocks</div></div></div>
            <div class="col-md-4"><div class="stats-card-sm"><div class="stats-number">${species.stats.active_flocks}</div><div class="stats-label">Active Flocks</div></div></div>
            <div class="col-md-4"><div class="stats-card-sm"><div class="stats-number">${species.stats.total_animals}</div><div class="stats-label">Total Animals</div></div></div>
        </div>
        
        <div class="row g-4">
            <div class="col-md-6">
                <div class="info-card"><div class="info-card-header"><i class="fas fa-info-circle text-primary me-2"></i><h6 class="mb-0">Basic Information</h6></div><div class="info-card-body">
                    <div class="info-row"><span class="info-label">Name</span><span class="info-value">${escapeHtml(species.name)}</span></div>
                    <div class="info-row"><span class="info-label">Code</span><span class="info-value">${escapeHtml(species.code)}</span></div>
                    <div class="info-row"><span class="info-label">Icon</span><span class="info-value"><i class="${species.icon}"></i> ${escapeHtml(species.icon)}</span></div>
                    <div class="info-row"><span class="info-label">Color</span><span class="info-value"><span style="background: ${species.color_hex}; padding: 5px 15px; border-radius: 5px; color: #fff;">${species.color_hex}</span></span></div>
                </div></div>
            </div>
            <div class="col-md-6">
                <div class="info-card"><div class="info-card-header"><i class="fas fa-chart-line text-success me-2"></i><h6 class="mb-0">Lifecycle Parameters</h6></div><div class="info-card-body">
                    <div class="info-row"><span class="info-label">Gestation Days</span><span class="info-value">${species.gestation_days}</span></div>
                    <div class="info-row"><span class="info-label">Weaning Days</span><span class="info-value">${species.weaning_days}</span></div>
                    <div class="info-row"><span class="info-label">Sexual Maturity</span><span class="info-value">${species.sexual_maturity_days}</span></div>
                    <div class="info-row"><span class="info-label">Market Age</span><span class="info-value">${species.market_age_days}</span></div>
                    <div class="info-row"><span class="info-label">Market Weight</span><span class="info-value">${species.market_weight_kg}</span></div>
                    <div class="info-row"><span class="info-label">Lifespan</span><span class="info-value">${species.lifespan_years}</span></div>
                </div></div>
            </div>
        </div>
        
        ${species.description ? `<div class="mt-3"><div class="info-card"><div class="info-card-header"><i class="fas fa-align-left text-warning me-2"></i><h6 class="mb-0">Description</h6></div><div class="info-card-body"><p class="mb-0">${escapeHtml(species.description)}</p></div></div></div>` : ''}
    `;
}

// ==================== VIEW DAILY LOG ====================
function viewDailyLog(id) {
    window.location.href = `/daily-logs/${id}`;
}

function redirectAndOpenModal(routeName, modalId) {
    sessionStorage.setItem('openModalOnLoad', modalId);
    window.location.href = route(routeName);
}

function route(name, params = {}) {
    const routes = {
        'daily-logs.index': '{{ route("daily-logs.index") }}'
    };
    return routes[name] || '/';
}

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
    .clickable-card {
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .clickable-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    .clickable-species {
        transition: all 0.3s ease;
    }
    .clickable-species:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        background: #f8fafc;
    }
    .stat-card-sm {
        background: white;
        border-radius: 16px;
        padding: 1rem;
        border: 1px solid #e2e8f0;
        text-align: center;
    }
    .stat-icon-sm {
        width: 45px;
        height: 45px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        margin: 0 auto 0.75rem;
    }
    .stat-info-sm {
        text-align: center;
    }
    .stat-label-sm {
        font-size: 0.65rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #64748b;
        font-weight: 600;
    }
    .stat-value-sm {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1e293b;
    }
    .stats-card-sm {
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
    .info-card {
        background: white;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        overflow: hidden;
    }
    .info-card-header {
        padding: 1rem 1.25rem;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
    }
    .info-card-header h6 {
        font-size: 0.9rem;
        font-weight: 600;
        color: #1e293b;
        display: inline-flex;
        align-items: center;
    }
    .info-card-body {
        padding: 1rem 1.25rem;
    }
    .info-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.6rem 0;
        border-bottom: 1px solid #f1f5f9;
    }
    .info-row:last-child {
        border-bottom: none;
    }
    .info-label {
        font-size: 0.8rem;
        color: #64748b;
        font-weight: 500;
    }
    .info-value {
        font-size: 0.85rem;
        font-weight: 600;
        color: #1e293b;
        text-align: right;
    }
    .bg-primary-soft { background: #e0f2fe; }
    .bg-success-soft { background: #dcfce7; }
    .bg-warning-soft { background: #fef3c7; }
    .bg-danger-soft { background: #fee2e2; }
    .bg-info-soft { background: #d1fae5; }
    .bg-secondary-soft { background: #f1f5f9; }
</style>
@endpush

@endsection