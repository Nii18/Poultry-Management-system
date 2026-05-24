{{-- resources/views/dashboard.blade.php --}}
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
        <!-- Stats Cards Row - ALL CLICKABLE -->
        <div class="col-xxl-3 col-xl-4 col-sm-6 box-col-4">
            <div class="card stat-card-clickable" onclick="window.location.href='{{ route('flocks.index') }}'" style="cursor: pointer;">
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
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xxl-3 col-xl-4 col-sm-6 box-col-4">
            <div class="card stat-card-clickable" onclick="window.location.href='{{ route('reports.total-animals') }}'" style="cursor: pointer;">
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
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xxl-3 col-xl-4 col-sm-6 box-col-4">
            <div class="card stat-card-clickable" onclick="window.location.href='{{ route('reports.health') }}'" style="cursor: pointer;">
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
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xxl-3 col-xl-4 col-sm-6 box-col-4">
            <div class="card stat-card-clickable" onclick="window.location.href='{{ route('reports.performance') }}'" style="cursor: pointer;">
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
                    </div>
                </div>
            </div>
        </div>

        <!-- Species Overview Section - Clickable -->
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
                            <div class="col-xxl-2 col-xl-3 col-md-4 col-sm-6 mb-3 species-card" data-species="{{ $code }}">
                                <div class="text-center p-3 border rounded-3 hover-shadow transition species-clickable" 
                                     onclick="showSpeciesDetails({{ $stats['id'] }})" style="cursor: pointer;">
                                    <div class="species-icon-wrapper">
                                        <span class="iconify fs-1 species-icon" data-icon="{{ $stats['iconify'] }}" style="color: {{ $stats['color'] }}"></span>
                                    </div>
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

        <!-- Active Flocks Table - Using Modals -->
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
                                                data-id="{{ $flock->id }}" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#viewFlockModal"
                                                title="View Details">
                                            <i class="fas fa-eye"></i> View
                                        </button>

                                                <button type="button" class="btn btn-sm btn-outline-success" 
                                                        onclick="openCreateLogModal({{ $flock->id }})">
                                                    <i class="fas fa-plus"></i> Add Daily Log
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

        <!-- Recent Activities - Clickable using Daily Log Modal -->
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
                            <div class="d-flex align-items-start mb-3 activity-item" 
                                 onclick="showDailyLogDetails({{ $activity->id }})" style="cursor: pointer;">
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
                                <div class="text-muted">
                                    <i class="fas fa-chevron-right"></i>
                                </div>
                            </div>
                        @empty
                            <p class="text-center text-muted py-4">No recent activities</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Alerts - Clickable using Notification Modal -->
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
                            <div class="d-flex align-items-start mb-3 p-2 rounded alert-item 
                                {{ $alert->severity === 'critical' ? 'bg-light-danger' : ($alert->severity === 'warning' ? 'bg-light-warning' : 'bg-light-primary') }}"
                                onclick="showNotificationDetails({{ $alert->id }})" style="cursor: pointer;">
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
                                <div class="text-muted">
                                    <i class="fas fa-chevron-right"></i>
                                </div>
                            </div>
                        @empty
                            <p class="text-center text-muted py-4">No active alerts</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Low Stock Alerts - Clickable -->
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
                                <div class="p-3 border rounded-3 bg-light-warning stock-item" 
                                     onclick="window.location.href='{{ route('feed-deliveries.low-stock') }}'" 
                                     style="cursor: pointer;">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1">{{ $stock->name }}</h6>
                                            <p class="mb-0 small">Remaining: {{ number_format($stock->remaining_quantity_kg) }} kg</p>
                                        </div>
                                        <i class="fa fa-truck text-warning fa-lg"></i>
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
            <div class="card">
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
                                <h3 class="text-danger">${{ number_format($currentMonthExpenses, 2) }}</h3>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="text-center p-4 border rounded-3">
                                <p class="text-muted mb-1">Year to Date Revenue</p>
                                <h3 class="text-success">$0.00</h3>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="text-center p-4 border rounded-3">
                                <p class="text-muted mb-1">Net Profit (YTD)</p>
                                <h3 class="text-primary">$0.00</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ==================== MODALS ==================== -->

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
            </div>
        </div>
    </div>
</div>

<!-- View Daily Log Modal -->
<div class="modal fade" id="viewLogModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg overflow-hidden">
            <div class="modal-header bg-primary text-white border-0">
                <div>
                    <h4 class="mb-1 fw-bold">
                        <i class="fa fa-clipboard-list me-2"></i>
                        Daily Log Details
                    </h4>
                    <small class="opacity-75">Detailed operational information</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body bg-light" id="viewLogContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2">Loading log details...</p>
                </div>
            </div>
            <div class="modal-footer bg-white border-0">
                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Create Daily Log Modal -->
<div class="modal fade" id="createLogModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg overflow-hidden">

            <div class="modal-header border-0" style="background: linear-gradient(135deg, #2f9088, #276f69);">
                <div>
                    <h4 class="mb-1 fw-bold" style="color:#fff;">
                        <i class="fa fa-plus-circle me-2"></i>New Daily Log
                    </h4>
                    <small style="color:rgba(255,255,255,0.75);">Record daily operational data</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form method="POST" action="{{ route('daily-logs.store') }}" id="dashboardCreateLogForm">
                @csrf

                <div class="modal-body" style="background:#f5f7fb; padding:1.5rem;">
                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="color:#495057;">Select Flock</label>
                            <select name="flock_id" class="form-select" required
                                    style="border-radius:10px;border:1px solid #dce1e7;min-height:46px;background:#fff;color:#212529;">
                                <option value="">Choose flock</option>
                                @foreach($activeFlocks as $flock)
                                    <option value="{{ $flock->id }}">{{ $flock->flock_number }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="color:#495057;">Log Date</label>
                            <input type="date" name="log_date" class="form-control" value="{{ date('Y-m-d') }}" required
                                   style="border-radius:10px;border:1px solid #dce1e7;min-height:46px;background:#fff;color:#212529;">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label" style="color:#495057;font-weight:600;">Mortality</label>
                            <input type="number" name="mortality_count" class="form-control" value="0" min="0"
                                   style="border-radius:10px;border:1px solid #dce1e7;min-height:46px;background:#fff;color:#212529;">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label" style="color:#495057;font-weight:600;">Culling</label>
                            <input type="number" name="culling_count" class="form-control" value="0" min="0"
                                   style="border-radius:10px;border:1px solid #dce1e7;min-height:46px;background:#fff;color:#212529;">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label" style="color:#495057;font-weight:600;">Feed Intake (kg)</label>
                            <input type="number" name="feed_intake_kg" class="form-control" step="0.1"
                                   style="border-radius:10px;border:1px solid #dce1e7;min-height:46px;background:#fff;color:#212529;">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label" style="color:#495057;font-weight:600;">Water (L)</label>
                            <input type="number" name="water_consumption_liters" class="form-control" step="0.1"
                                   style="border-radius:10px;border:1px solid #dce1e7;min-height:46px;background:#fff;color:#212529;">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label" style="color:#495057;font-weight:600;">Avg Weight (kg)</label>
                            <input type="number" name="average_weight_kg" class="form-control" step="0.01"
                                   style="border-radius:10px;border:1px solid #dce1e7;min-height:46px;background:#fff;color:#212529;">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label" style="color:#495057;font-weight:600;">Min Temp °C</label>
                            <input type="number" name="min_temperature_c" class="form-control" step="0.1"
                                   style="border-radius:10px;border:1px solid #dce1e7;min-height:46px;background:#fff;color:#212529;">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label" style="color:#495057;font-weight:600;">Max Temp °C</label>
                            <input type="number" name="max_temperature_c" class="form-control" step="0.1"
                                   style="border-radius:10px;border:1px solid #dce1e7;min-height:46px;background:#fff;color:#212529;">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label" style="color:#495057;font-weight:600;">Min Humidity %</label>
                            <input type="number" name="min_humidity" class="form-control" step="0.1"
                                   style="border-radius:10px;border:1px solid #dce1e7;min-height:46px;background:#fff;color:#212529;">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label" style="color:#495057;font-weight:600;">Max Humidity %</label>
                            <input type="number" name="max_humidity" class="form-control" step="0.1"
                                   style="border-radius:10px;border:1px solid #dce1e7;min-height:46px;background:#fff;color:#212529;">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label" style="color:#495057;font-weight:600;">Ammonia (ppm)</label>
                            <input type="number" name="ammonia_ppm" class="form-control" step="0.1"
                                   style="border-radius:10px;border:1px solid #dce1e7;min-height:46px;background:#fff;color:#212529;">
                        </div>

                        <div class="col-12">
                            <label class="form-label" style="color:#495057;font-weight:600;">Notes</label>
                            <textarea name="notes" rows="4" class="form-control"
                                      placeholder="Enter observations, unusual behaviour, feed issues, disease signs, etc."
                                      style="border-radius:10px;border:1px solid #dce1e7;background:#fff;color:#212529;resize:vertical;min-height:120px;"></textarea>
                        </div>

                    </div>
                </div>

                <div class="modal-footer border-0" style="background:#fff;">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save me-1"></i>Save Daily Log
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<!-- View Notification Modal -->
<div class="modal fade" id="viewNotificationModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header" id="notificationModalHeader">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-white bg-opacity-25 p-2 me-3" id="modalIcon">
                        <i class="fas fa-bell fs-5 text-white"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0 text-white" id="notificationModalTitle">Notification Details</h5>
                        <small class="text-white-50" id="notificationModalDate"></small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4" id="notificationModalBody">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading notification details...</p>
                </div>
            </div>
            <div class="modal-footer border-0 px-4 pb-4">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Close
                </button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.stat-card-clickable {
    transition: all 0.3s ease;
    cursor: pointer;
}

.stat-card-clickable:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.stat-card-clickable:active {
    transform: translateY(-2px);
}

.species-clickable {
    transition: all 0.3s ease;
}

.species-clickable:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    border-color: #0d6e4f !important;
}

.activity-item, .alert-item {
    transition: all 0.2s ease;
}

.activity-item:hover, .alert-item:hover {
    background-color: #f8fafc;
    transform: translateX(5px);
}

.stock-item {
    transition: all 0.2s ease;
}

.stock-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
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

.bg-success-soft { background: #dcfce7; color: #166534; }
.bg-secondary-soft { background: #f1f5f9; color: #475569; }
.bg-danger-soft { background: #fee2e2; color: #991b1b; }
.bg-info-soft { background: #d1fae5; color: #065f46; }
.bg-warning-soft { background: #fef3c7; color: #92400e; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.iconify.design/3/3.1.0/iconify.min.js"></script>
<script>
let mortalityChart, feedChart;

document.addEventListener('DOMContentLoaded', function() {
    const mortalityData = @json($mortalityTrend);
    const mortalityCtx = document.getElementById('mortalityChart').getContext('2d');
    
    const labels = Object.keys(mortalityData);
    const values = Object.values(mortalityData);
    
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
            plugins: {
                legend: { position: 'top' }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: { display: true, text: 'Number of Deaths' }
                },
                x: {
                    title: { display: true, text: 'Date' }
                }
            }
        }
    });

    loadFeedData('week');
});

// Fix stuck modal backdrop
document.addEventListener('hidden.bs.modal', function () {
    document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
    document.body.classList.remove('modal-open');
    document.body.style.overflow = '';
    document.body.style.paddingRight = '';
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
                        y: { 
                            beginAtZero: true,
                            title: { display: true, text: 'Feed (kg)' }
                        },
                        x: {
                            title: { display: true, text: 'Date' }
                        }
                    }
                }
            });
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

function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}

// ==================== FLOCK MODAL ====================
function showFlockDetails(flockId) {
    const modal = new bootstrap.Modal(document.getElementById('viewFlockModal'));
    const modalBody = document.getElementById('viewFlockContent');
    
    modalBody.innerHTML = `<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Loading flock details...</p></div>`;
    modal.show();
    
    fetch(`/flocks/${flockId}/details`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayFlockDetailsInModal(data.flock, data.summary);
            } else {
                modalBody.innerHTML = `<div class="alert alert-danger">Failed to load flock details: ${data.message}</div>`;
            }
        })
        .catch(error => {
            modalBody.innerHTML = `<div class="alert alert-danger">Error loading data</div>`;
        });
}

function displayFlockDetailsInModal(flock, summary) {
    const statusColors = {
        'active': 'success', 'closed': 'secondary',
        'quarantined': 'danger', 'breeding': 'info'
    };
    const statusColor = statusColors[flock.status] ?? 'secondary';

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
                    <span class="badge bg-${statusColor}-soft text-${statusColor} px-3 py-2 rounded-pill">
                        <i class="fas fa-circle me-1" style="font-size:8px;"></i>
                        ${flock.status.charAt(0).toUpperCase() + flock.status.slice(1)}
                    </span>
                </div>
            </div>
        </div>

        ${flock.notes ? `
        <div class="detail-section">
            <h6>Notes</h6>
            <p class="mb-0" style="color:#334155;">${escapeHtml(flock.notes)}</p>
        </div>` : ''}
    `;
}

// View Flock buttons
document.querySelectorAll('.view-flock-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const flockId = this.dataset.id;
        showFlockDetails(flockId);
    });
});

// ==================== SPECIES MODAL ====================
function showSpeciesDetails(speciesId) {
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
                modalBody.innerHTML = `<div class="alert alert-danger">Failed to load species details</div>`;
            }
        })
        .catch(error => {
            modalBody.innerHTML = `<div class="alert alert-danger">Error loading data</div>`;
        });
}

function displaySpeciesDetailsInModal(species) {
    const statusBadge = species.is_active
        ? '<span class="badge bg-success-soft text-success px-3 py-2 rounded-pill">Active</span>'
        : '<span class="badge bg-secondary-soft text-secondary px-3 py-2 rounded-pill">Inactive</span>';

    function renderJson(data, emptyMsg) {
        if (!data || (typeof data === 'object' && Object.keys(data).length === 0) || data === '') {
            return `<p class="fst-italic mb-0" style="color:#64748b;">${emptyMsg}</p>`;
        }
        const obj = typeof data === 'string' ? JSON.parse(data) : data;
        const rows = Object.entries(obj).map(([k, v]) => `
            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                <span class="small text-capitalize" style="color:#64748b;">${k.replace(/_/g, ' ')}</span>
                <span class="fw-semibold" style="color:#1e293b;">${v}</span>
            </div>`).join('');
        return `<div class="rounded-3 border px-3 pt-1 pb-0 bg-white">${rows}</div>`;
    }

    const iconHtml = (species.icon && species.icon.includes(':'))
        ? `<span class="iconify" data-icon="${species.icon}" style="font-size:2.5rem; color:${species.color_hex};"></span>`
        : `<i class="${species.icon} fa-2x" style="color:${species.color_hex};"></i>`;

    document.getElementById('viewSpeciesContent').innerHTML = `
        <!-- Hero Banner -->
        <div class="rounded-3 mb-4 p-4 d-flex align-items-center gap-3"
             style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border: 1px solid #bbf7d0;">
            <div class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0"
                 style="width:60px; height:60px; background:${species.color_hex}20; border: 2px solid ${species.color_hex}40;">
                ${iconHtml}
            </div>
            <div class="flex-grow-1">
                <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                    <h4 class="mb-0 fw-bold" style="color:#1e293b;">${escapeHtml(species.name)}</h4>
                    ${statusBadge}
                </div>
                <span class="badge rounded-pill px-3 py-1"
                      style="background:${species.color_hex}20; color:${species.color_hex}; font-size:0.75rem; border:1px solid ${species.color_hex}40;">
                    <i class="fas fa-tag me-1"></i>${escapeHtml(species.code)}
                </span>
                ${species.description
                    ? `<p class="mb-0 mt-1" style="color:#475569; font-size:0.85rem;">${escapeHtml(species.description)}</p>`
                    : ''}
            </div>
        </div>

        <!-- Stats -->
        <div class="row g-3 mb-4">
            <div class="col-4">
                <div class="text-center p-3 rounded-3 border" style="background:#f8fafc;">
                    <div class="fw-bold fs-4" style="color:#1e293b;">${species.stats.flock_count}</div>
                    <div style="color:#64748b; font-size:0.68rem; text-transform:uppercase; letter-spacing:.5px; font-weight:600;">Total Flocks</div>
                </div>
            </div>
            <div class="col-4">
                <div class="text-center p-3 rounded-3 border" style="background:#f0fdf4;">
                    <div class="fw-bold fs-4" style="color:#16a34a;">${species.stats.active_flocks}</div>
                    <div style="color:#64748b; font-size:0.68rem; text-transform:uppercase; letter-spacing:.5px; font-weight:600;">Active Flocks</div>
                </div>
            </div>
            <div class="col-4">
                <div class="text-center p-3 rounded-3 border" style="background:#eff6ff;">
                    <div class="fw-bold fs-4" style="color:#2563eb;">${species.stats.total_animals}</div>
                    <div style="color:#64748b; font-size:0.68rem; text-transform:uppercase; letter-spacing:.5px; font-weight:600;">Total Animals</div>
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
                    { label: 'Gestation',       value: species.gestation_days,       icon: 'fa-egg',            color: '#8b5cf6' },
                    { label: 'Weaning',         value: species.weaning_days,         icon: 'fa-child',          color: '#06b6d4' },
                    { label: 'Sexual Maturity', value: species.sexual_maturity_days, icon: 'fa-venus-mars',     color: '#ec4899' },
                    { label: 'Market Age',      value: species.market_age_days,      icon: 'fa-store',          color: '#f59e0b' },
                    { label: 'Market Weight',   value: species.market_weight_kg,     icon: 'fa-weight-hanging', color: '#10b981' },
                    { label: 'Lifespan',        value: species.lifespan_years,       icon: 'fa-hourglass-half', color: '#6366f1' },
                ].map(item => `
                    <div class="col-6">
                        <div class="d-flex align-items-center gap-2 p-2 rounded-3 border" style="background:#f8fafc;">
                            <div class="rounded-2 d-flex align-items-center justify-content-center flex-shrink-0"
                                 style="width:30px;height:30px;background:${item.color}18;">
                                <i class="fas ${item.icon}" style="color:${item.color};font-size:12px;"></i>
                            </div>
                            <div>
                                <div style="color:#64748b;font-size:0.62rem;text-transform:uppercase;letter-spacing:.4px;font-weight:600;">${item.label}</div>
                                <div class="fw-semibold" style="font-size:0.88rem;color:#1e293b;">${item.value ?? 'N/A'}</div>
                            </div>
                        </div>
                    </div>`).join('')}
            </div>
        </div>

        <!-- Performance Metrics -->
        <div class="mb-4">
            <div class="d-flex align-items-center gap-2 mb-2">
                <div class="rounded-2 d-flex align-items-center justify-content-center" style="width:28px;height:28px;background:#dcfce7;">
                    <i class="fas fa-chart-bar text-success" style="font-size:13px;"></i>
                </div>
                <h6 class="mb-0 fw-semibold" style="color:#1e293b;">Performance Metrics</h6>
            </div>
            ${renderJson(species.default_metrics, 'No performance metrics configured')}
        </div>

        <!-- Growth Standards -->
        <div class="mb-4">
            <div class="d-flex align-items-center gap-2 mb-2">
                <div class="rounded-2 d-flex align-items-center justify-content-center" style="width:28px;height:28px;background:#ede9fe;">
                    <i class="fas fa-chart-line" style="color:#7c3aed;font-size:13px;"></i>
                </div>
                <h6 class="mb-0 fw-semibold" style="color:#1e293b;">Growth Standards</h6>
            </div>
            ${renderJson(species.growth_standards, 'No growth standards configured')}
        </div>

        <!-- Health Indicators -->
        <div class="mb-4">
            <div class="d-flex align-items-center gap-2 mb-2">
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
}

// ==================== DAILY LOG MODAL ====================
function showDailyLogDetails(logId) {
    const modal = new bootstrap.Modal(document.getElementById('viewLogModal'));
    const modalBody = document.getElementById('viewLogContent');
    
    modalBody.innerHTML = `<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Loading log details...</p></div>`;
    modal.show();
    
    fetch(`/daily-logs/${logId}/json`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayLogDetailsInModal(data.log);
            } else {
                modalBody.innerHTML = `<div class="alert alert-danger">Failed to load log details</div>`;
            }
        })
        .catch(error => {
            modalBody.innerHTML = `<div class="alert alert-danger">Error loading data</div>`;
        });
}

function displayLogDetailsInModal(log) {
    document.getElementById('viewLogContent').innerHTML = `
        <div class="row g-3">
            <!-- Basic Info Card -->
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="mb-0 fw-semibold" style="color:#1e293b;">
                            <i class="fas fa-info-circle text-primary me-2"></i>Log Information
                        </h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm align-middle mb-0">
                            <tr>
                                <td class="text-muted fw-semibold" style="font-size:0.8rem;width:45%;">Date</td>
                                <td style="color:#1e293b;font-weight:500;">${log.log_date}</td>
                            </tr>
                            <tr>
                                <td class="text-muted fw-semibold" style="font-size:0.8rem;">Flock</td>
                                <td style="color:#1e293b;font-weight:500;">${escapeHtml(log.flock_number)}</td>
                            </tr>
                            <tr>
                                <td class="text-muted fw-semibold" style="font-size:0.8rem;">Mortality</td>
                                <td>
                                    <span class="badge ${log.mortality_count > 0 ? 'bg-danger' : 'bg-success'} bg-opacity-10 
                                          ${log.mortality_count > 0 ? 'text-danger' : 'text-success'} px-2 py-1">
                                        ${log.mortality_count}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted fw-semibold" style="font-size:0.8rem;">Culling</td>
                                <td style="color:#1e293b;font-weight:500;">${log.culling_count}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Production Card -->
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="mb-0 fw-semibold" style="color:#1e293b;">
                            <i class="fas fa-chart-bar text-success me-2"></i>Production Data
                        </h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm align-middle mb-0">
                            <tr>
                                <td class="text-muted fw-semibold" style="font-size:0.8rem;width:45%;">Feed Intake</td>
                                <td style="color:#1e293b;font-weight:500;">${log.feed_intake_kg} kg</td>
                            </tr>
                            <tr>
                                <td class="text-muted fw-semibold" style="font-size:0.8rem;">Water</td>
                                <td style="color:#1e293b;font-weight:500;">${log.water_consumption_liters} L</td>
                            </tr>
                            <tr>
                                <td class="text-muted fw-semibold" style="font-size:0.8rem;">Avg Weight</td>
                                <td style="color:#1e293b;font-weight:500;">${log.average_weight_kg ?? 'N/A'} kg</td>
                            </tr>
                            <tr>
                                <td class="text-muted fw-semibold" style="font-size:0.8rem;">Temperature</td>
                                <td style="color:#1e293b;font-weight:500;">${log.min_temp ?? '-'}°C – ${log.max_temp ?? '-'}°C</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Notes Card -->
            ${log.notes ? `
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="mb-0 fw-semibold" style="color:#1e293b;">
                            <i class="fas fa-sticky-note text-warning me-2"></i>Notes & Observations
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="p-3 rounded-3" style="background:#f8fafc;border-left:3px solid #0d6e4f;">
                            <p class="mb-0" style="color:#334155;line-height:1.6;">${escapeHtml(log.notes)}</p>
                        </div>
                    </div>
                </div>
            </div>` : ''}
        </div>
    `;
}

function openCreateLogModal(flockId) {
    const modal = new bootstrap.Modal(document.getElementById('createLogModal'));
    const flockSelect = document.querySelector('#createLogModal select[name="flock_id"]');
    
    if (flockSelect) {
        flockSelect.value = flockId;
    }
    
    modal.show();
}

// Handle dashboard create log form submission
document.getElementById('dashboardCreateLogForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const form = this;
    const formData = new FormData(form);
    
    fetch(form.action, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success || data.message) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: 'Daily log created successfully',
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                window.location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message || 'Failed to create daily log'
            });
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An error occurred while saving'
        });
    });
});

// ==================== NOTIFICATION MODAL ====================
function showNotificationDetails(notificationId) {
    const modal = new bootstrap.Modal(document.getElementById('viewNotificationModal'));
    const modalBody = document.getElementById('notificationModalBody');
    
    modalBody.innerHTML = `<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Loading notification details...</p></div>`;
    modal.show();
    
    fetch(`/notifications/${notificationId}/json`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayNotificationDetailsInModal(data.notification);
                markNotificationAsRead(notificationId);
            } else {
                modalBody.innerHTML = `<div class="alert alert-danger">Failed to load notification details</div>`;
            }
        })
        .catch(error => {
            modalBody.innerHTML = `<div class="alert alert-danger">Error loading data</div>`;
        });
}

function displayNotificationDetailsInModal(notification) {
    const modalHeader = document.getElementById('notificationModalHeader');
    const modalTitle = document.getElementById('notificationModalTitle');
    const modalBody = document.getElementById('notificationModalBody');
    
    let headerClass = 'bg-info';
    let iconClass = 'fas fa-info-circle';
    
    if (notification.severity === 'critical') {
        headerClass = 'bg-danger';
        iconClass = 'fas fa-exclamation-circle';
    } else if (notification.severity === 'warning') {
        headerClass = 'bg-warning';
        iconClass = 'fas fa-exclamation-triangle';
    }
    
    modalHeader.className = `modal-header ${headerClass} text-white border-0`;
    modalTitle.textContent = notification.title;
    
    modalBody.innerHTML = `
        <div class="mb-4">
            <p>${escapeHtml(notification.message)}</p>
            <small class="text-muted">Received: ${notification.time_ago}</small>
        </div>
        ${notification.flock ? `
        <div class="alert alert-info">
            <i class="fas fa-tractor me-2"></i>
            <strong>Flock:</strong> ${escapeHtml(notification.flock.flock_number)}
        </div>
        ` : ''}
    `;
}

function markNotificationAsRead(id) {
    fetch(`/notifications/${id}/mark-read-ajax`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    }).catch(error => console.error('Error marking as read:', error));
}
</script>
@endpush
@endsection