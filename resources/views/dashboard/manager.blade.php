@extends('layouts.master')

@section('content')
<div class="container-fluid px-4">
    
    @include('dashboard.partials.role-header')
    
    <!-- Key Performance Indicators - Proper Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="kpi-card">
                <div class="kpi-card-body">
                    <div class="kpi-icon bg-primary-soft">
                        <i class="fas fa-users text-primary"></i>
                    </div>
                    <div class="kpi-info">
                        <span class="kpi-label">Active Flocks</span>
                        <h3 class="kpi-value">{{ $activeFlocksCount ?? 0 }}</h3>
                        <span class="kpi-trend">Currently active</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="kpi-card">
                <div class="kpi-card-body">
                    <div class="kpi-icon bg-success-soft">
                        <i class="fas fa-paw text-success"></i>
                    </div>
                    <div class="kpi-info">
                        <span class="kpi-label">Total Animals</span>
                        <h3 class="kpi-value">{{ number_format($totalAnimals ?? 0) }}</h3>
                        <span class="kpi-trend">Across all flocks</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="kpi-card">
                <div class="kpi-card-body">
                    <div class="kpi-icon bg-warning-soft">
                        <i class="fas fa-chart-line text-warning"></i>
                    </div>
                    <div class="kpi-info">
                        <span class="kpi-label">Avg FCR</span>
                        <h3 class="kpi-value">{{ number_format($avgFCR ?? 0, 2) }}</h3>
                        <span class="kpi-trend">Feed conversion ratio</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="kpi-card">
                <div class="kpi-card-body">
                    <div class="kpi-icon bg-danger-soft">
                        <i class="fas fa-skull text-danger"></i>
                    </div>
                    <div class="kpi-info">
                        <span class="kpi-label">Today's Mortality</span>
                        <h3 class="kpi-value {{ ($todayMortality ?? 0) > 10 ? 'text-danger' : '' }}">{{ $todayMortality ?? 0 }}</h3>
                        <span class="kpi-trend">Last 24 hours</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions - DIRECT MODAL OPENING (No Redirect) -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="card-title mb-0 fw-semibold">
                        <i class="fas fa-bolt me-2 text-primary"></i>Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-2">
                            <button class="quick-action-btn w-100" onclick="openCreateFlockModal()">
                                <i class="fas fa-plus-circle text-primary"></i>
                                <span>New Flock</span>
                            </button>
                        </div>
                        <div class="col-md-2">
                            <button class="quick-action-btn w-100" onclick="openCreateDailyLogModal()">
                                <i class="fas fa-clipboard-list text-success"></i>
                                <span>Quick Log</span>
                            </button>
                        </div>
                        <div class="col-md-2">
                            <button class="quick-action-btn w-100" onclick="openCreateExpenseModal()">
                                <i class="fas fa-money-bill-wave text-danger"></i>
                                <span>Add Expense</span>
                            </button>
                        </div>
                        <div class="col-md-2">
                            <button class="quick-action-btn w-100" onclick="openCreateSaleModal()">
                                <i class="fas fa-chart-line text-info"></i>
                                <span>Record Sale</span>
                            </button>
                        </div>
                        <div class="col-md-2">
                            <button class="quick-action-btn w-100" onclick="openCreateBreedingModal()">
                                <i class="fas fa-heart text-warning"></i>
                                <span>Breeding</span>
                            </button>
                        </div>
                        <div class="col-md-2">
                            <a href="{{ route('reports.performance') }}" class="quick-action-btn w-100">
                                <i class="fas fa-chart-pie text-primary"></i>
                                <span>Reports</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="card-title mb-0 fw-semibold">
                        <i class="fas fa-chart-line me-2 text-primary"></i>Weekly Mortality Trend
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="mortalityChart" height="250"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="card-title mb-0 fw-semibold">
                        <i class="fas fa-chart-bar me-2 text-primary"></i>Feed Consumption (Weekly)
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="feedChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>
<!-- Active Flocks Table -->
<div class="card shadow-sm border-0 mt-4">
    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0 fw-semibold">
            <i class="fas fa-table me-2 text-primary"></i>Active Flocks
        </h5>
        <a href="{{ route('flocks.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="py-3">Flock Number</th>
                        <th class="py-3">Species</th>
                        <th class="py-3">Breed</th>
                        <th class="py-3">Age (Days)</th>
                        <th class="py-3">Current Count</th>
                        <th class="py-3">Initial Count</th>
                        <th class="py-3">Mortality (%)</th>
                        <th class="py-3">FCR</th>
                        <th class="py-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activeFlocks ?? [] as $flock)
                        <tr>
                            <td class="fw-semibold">
                                <button type="button" 
                                        class="btn btn-link p-0 text-primary text-decoration-none view-flock-btn" 
                                        data-id="{{ $flock->id }}" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#viewFlockModal">
                                    {{ $flock->flock_number }}
                                </button>
                            </td>
                            <td>{{ $flock->species->name ?? 'N/A' }}</td>
                            <td>{{ $flock->breed_variety ?? 'N/A' }}</td>
                            <td>
                                <span class="fw-semibold">{{ $flock->age_in_days ?? 0 }}</span>
                                <small class="text-muted">days</small>
                            </td>
                            <td>
                                <span class="fw-semibold text-success">{{ number_format($flock->current_count ?? 0) }}</span>
                            </td>
                            <td>
                                <span class="text-muted">{{ number_format($flock->initial_count ?? 0) }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height: 6px;">
                                        <div class="progress-bar bg-{{ ($flock->mortality_rate ?? 0) > 5 ? 'danger' : 'success' }}" 
                                             style="width: {{ min(100, $flock->mortality_rate ?? 0) }}%">
                                        </div>
                                    </div>
                                    <span class="small fw-semibold {{ ($flock->mortality_rate ?? 0) > 5 ? 'text-danger' : 'text-success' }}">
                                        {{ number_format($flock->mortality_rate ?? 0, 1) }}%
                                    </span>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-info-soft text-info">
                                    {{ number_format($flock->feed_conversion_ratio ?? 0, 2) }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group gap-1">
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-primary view-flock-btn" 
                                            data-id="{{ $flock->id }}" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#viewFlockModal"
                                            title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    @if(($flock->status ?? '') === 'active')
                                        <a href="{{ route('daily-logs.create', ['flock_id' => $flock->id]) }}" 
                                           class="btn btn-sm btn-outline-success"
                                           title="Add Daily Log">
                                            <i class="fas fa-clipboard-list"></i>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <div class="empty-state">
                                    <i class="fas fa-tractor fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">No Active Flocks</h5>
                                    <p class="text-muted mb-3">There are currently no active flocks in the system.</p>
                                    <button type="button" class="btn btn-primary" onclick="openCreateFlockModal()">
                                        <i class="fas fa-plus me-2"></i>Create New Flock
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
    <!-- Low Stock Alerts -->
    @if(($lowFeedStock ?? collect())->count() > 0)
    <div class="card shadow-sm border-0 mt-4">
        <div class="card-header bg-white border-0 py-3">
            <h5 class="card-title mb-0 fw-semibold">
                <i class="fas fa-exclamation-triangle me-2 text-warning"></i>Low Stock Alerts
            </h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                @foreach($lowFeedStock as $stock)
                <div class="col-md-4">
                    <div class="alert-card p-3 bg-light-warning rounded-3 d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">{{ $stock->feedType->name ?? 'Feed' }}</h6>
                            <small>Remaining: {{ number_format($stock->remaining_quantity_kg) }} kg</small>
                        </div>
                        <a href="{{ route('feed-deliveries.low-stock') }}" class="btn btn-sm btn-warning">Order</a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>

<!-- ==================== EMBEDDED MODALS ==================== -->

<!-- Create Flock Modal -->
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
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Species <span class="text-danger">*</span></label>
                            <select name="species_id" class="form-select" required>
                                <option value="">Select Species</option>
                                @foreach($species ?? [] as $spec)
                                    <option value="{{ $spec->id }}">{{ $spec->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">House <span class="text-danger">*</span></label>
                            <select name="house_id" class="form-select" required>
                                <option value="">Select House</option>
                                @foreach(\App\Models\House::where('status', 'active')->get() as $house)
                                    <option value="{{ $house->id }}">{{ $house->name }} (Capacity: {{ number_format($house->capacity) }})</option>
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
                                <option value="meat">Meat</option><option value="eggs">Eggs</option>
                                <option value="milk">Milk</option><option value="breeding">Breeding</option>
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
                                <label class="form-check-label" for="create_is_breeding_stock">This flock is for breeding stock</label>
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Flock</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Create Daily Log Modal - EXACT COPY FROM DAILY-LOGS INDEX -->
<div class="modal fade" id="createLogModal" tabindex="-1">
    <div class=" modal-dialog modal-xl modal-dialog-centered">
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

                                @foreach($activeFlocks ?? [] as $flock)

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
                            class="btn btn-light border"
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

<!-- Create Expense Modal -->
<div class="modal fade" id="createExpenseModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-danger text-white border-0">
                <h5 class="modal-title text-white"><i class="fas fa-plus-circle me-2"></i>Add Expense</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="createExpenseContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-danger" role="status"></div>
                    <p class="mt-2">Loading form...</p>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="saveCreateExpenseBtn">Save Expense</button>
            </div>
        </div>
    </div>
</div>

<!-- Create Sale Modal -->
<div class="modal fade" id="createSaleModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-success text-white border-0">
                <h5 class="modal-title text-white"><i class="fas fa-plus-circle me-2"></i>Record Sale</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="createSaleContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-success" role="status"></div>
                    <p class="mt-2">Loading form...</p>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="saveCreateSaleBtn">Record Sale</button>
            </div>
        </div>
    </div>
</div>

<!-- Create Breeding Modal -->
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
                <button type="button" class="btn btn-primary" id="saveCreateBreedingBtn">Create Record</button>
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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // ==================== MODAL OPENING FUNCTIONS ====================
    function openCreateFlockModal() {
        const modal = new bootstrap.Modal(document.getElementById('createFlockModal'));
        modal.show();
    }
    
    function openCreateDailyLogModal() {
        const modal = new bootstrap.Modal(document.getElementById('createLogModal'));
        modal.show();
    }
    
    function openCreateExpenseModal() {
        const modal = new bootstrap.Modal(document.getElementById('createExpenseModal'));
        modal.show();
        loadExpenseForm();
    }
    
    function openCreateSaleModal() {
        const modal = new bootstrap.Modal(document.getElementById('createSaleModal'));
        modal.show();
        loadSaleForm();
    }
    
    function openCreateBreedingModal() {
        const modal = new bootstrap.Modal(document.getElementById('createBreedingModal'));
        modal.show();
        loadBreedingForm();
    }
    
    // ==================== LOAD EXPENSE FORM ====================
    function loadExpenseForm() {
        const modalBody = document.getElementById('createExpenseContent');
        modalBody.innerHTML = `<div class="text-center py-4"><div class="spinner-border text-danger" role="status"></div><p class="mt-2">Loading form...</p></div>`;
        
        fetch('{{ route("expenses.create-form") }}', {
            headers: { 'Accept': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayExpenseForm(data.flocks, data.houses);
            } else {
                modalBody.innerHTML = `<div class="alert alert-danger">Failed to load form: ${data.message}</div>`;
            }
        })
        .catch(error => {
            modalBody.innerHTML = `<div class="alert alert-danger">Error: ${error.message}</div>`;
        });
    }
    
    function displayExpenseForm(flocks, houses) {
        const flockOptions = flocks.map(flock => `<option value="${flock.id}">${escapeHtml(flock.flock_number)} - ${escapeHtml(flock.breed_variety)}</option>`).join('');
        const houseOptions = houses.map(house => `<option value="${house.id}">${escapeHtml(house.name)}</option>`).join('');
        
        document.getElementById('createExpenseContent').innerHTML = `
            <form id="createExpenseForm">
                <div class="row">
                    <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                        <select name="category" class="form-select" required>
                            <option value="">Select Category</option>
                            <option value="feed">🐓 Feed</option><option value="veterinary">🏥 Veterinary</option>
                            <option value="medication">💊 Medication</option><option value="labor">👥 Labor</option>
                            <option value="equipment">🔧 Equipment</option><option value="utilities">💡 Utilities</option>
                            <option value="maintenance">🛠️ Maintenance</option><option value="transport">🚚 Transport</option>
                            <option value="other">📦 Other</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Expense Date <span class="text-danger">*</span></label>
                        <input type="date" name="expense_date" class="form-control" value="${new Date().toISOString().split('T')[0]}" required>
                    </div>
                    <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Description <span class="text-danger">*</span></label>
                        <input type="text" name="description" class="form-control" placeholder="e.g., 50 bags of layer mash" required>
                    </div>
                    <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Amount (₵) <span class="text-danger">*</span></label>
                        <div class="input-group"><span class="input-group-text">₵</span><input type="number" name="amount" class="form-control" step="0.01" min="0.01" required></div>
                    </div>
                    <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Vendor Name</label>
                        <input type="text" name="vendor_name" class="form-control">
                    </div>
                    <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Payment Method</label>
                        <select name="payment_method" class="form-select">
                            <option value="">Select</option><option value="cash">💵 Cash</option>
                            <option value="bank_transfer">🏦 Bank Transfer</option><option value="mobile_money">📱 Mobile Money</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Associated Flock</label>
                        <select name="flock_id" class="form-select"><option value="">None - General</option>${flockOptions}</select>
                    </div>
                    <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Associated House</label>
                        <select name="house_id" class="form-select"><option value="">None - General</option>${houseOptions}</select>
                    </div>
                    <div class="col-12 mb-3"><label class="form-label fw-semibold">Notes</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
            </form>
        `;
        
        document.getElementById('saveCreateExpenseBtn').onclick = function() {
            const form = document.getElementById('createExpenseForm');
            const formData = new FormData(form);
            const data = {};
            formData.forEach((value, key) => { data[key] = value; });
            
            if (!data.category || !data.expense_date || !data.description || !data.amount) {
                Swal.fire({ icon: 'error', title: 'Validation Error', text: 'Please fill in all required fields' });
                return;
            }
            
            fetch('{{ route("expenses.store-ajax") }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json', 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({ icon: 'success', title: 'Saved!', text: 'Expense recorded successfully', timer: 1500, showConfirmButton: false })
                        .then(() => window.location.reload());
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Failed to save expense' });
                }
            });
        };
    }
    
    // ==================== LOAD SALE FORM ====================
    function loadSaleForm() {
        const modalBody = document.getElementById('createSaleContent');
        modalBody.innerHTML = `<div class="text-center py-4"><div class="spinner-border text-success" role="status"></div><p class="mt-2">Loading form...</p></div>`;
        
        fetch('{{ route("sales.create-form") }}', {
            headers: { 'Accept': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displaySaleForm(data.flocks);
            } else {
                modalBody.innerHTML = `<div class="alert alert-danger">Failed to load form: ${data.message}</div>`;
            }
        })
        .catch(error => {
            modalBody.innerHTML = `<div class="alert alert-danger">Error: ${error.message}</div>`;
        });
    }
    
    function displaySaleForm(flocks) {
        const flockOptions = flocks.map(flock => `<option value="${flock.id}">${escapeHtml(flock.flock_number)} - ${escapeHtml(flock.breed_variety)}</option>`).join('');
        
        document.getElementById('createSaleContent').innerHTML = `
            <form id="createSaleForm">
                <div class="row">
                    <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Product Type <span class="text-danger">*</span></label>
                        <select name="product_type" class="form-select" required>
                            <option value="">Select Product</option>
                            <option value="eggs_tray">🥚 Eggs (Tray)</option><option value="eggs_crate">📦 Eggs (Crate)</option>
                            <option value="live_bird">🐓 Live Bird</option><option value="meat_kg">🍗 Meat (kg)</option>
                            <option value="manure">💩 Manure</option><option value="other">📦 Other</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Sale Date <span class="text-danger">*</span></label>
                        <input type="date" name="sale_date" class="form-control" value="${new Date().toISOString().split('T')[0]}" required>
                    </div>
                    <div class="col-md-4 mb-3"><label class="form-label fw-semibold">Quantity <span class="text-danger">*</span></label>
                        <input type="number" name="quantity" id="saleQuantity" class="form-control" step="0.01" min="0.01" required>
                    </div>
                    <div class="col-md-4 mb-3"><label class="form-label fw-semibold">Unit Price (₵) <span class="text-danger">*</span></label>
                        <div class="input-group"><span class="input-group-text">₵</span><input type="number" name="unit_price" id="saleUnitPrice" class="form-control" step="0.01" min="0.01" required></div>
                    </div>
                    <div class="col-md-4 mb-3"><label class="form-label fw-semibold">Total Amount (₵)</label>
                        <div class="input-group"><span class="input-group-text">₵</span><input type="number" name="total_amount" id="saleTotal" class="form-control" step="0.01" readonly style="background:#f8fafc;"></div>
                        <small>Auto-calculated</small>
                    </div>
                    <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Customer Name</label>
                        <input type="text" name="customer_name" class="form-control">
                    </div>
                    <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Associated Flock</label>
                        <select name="flock_id" class="form-select"><option value="">None - General Sale</option>${flockOptions}</select>
                    </div>
                </div>
            </form>
        `;
        
        const qty = document.getElementById('saleQuantity');
        const price = document.getElementById('saleUnitPrice');
        const total = document.getElementById('saleTotal');
        function calcTotal() { total.value = ((parseFloat(qty?.value)||0) * (parseFloat(price?.value)||0)).toFixed(2); }
        qty?.addEventListener('input', calcTotal);
        price?.addEventListener('input', calcTotal);
        
        document.getElementById('saveCreateSaleBtn').onclick = function() {
            const form = document.getElementById('createSaleForm');
            const formData = new FormData(form);
            const data = {};
            formData.forEach((value, key) => { data[key] = value; });
            
            if (!data.product_type || !data.sale_date || !data.quantity || !data.unit_price) {
                Swal.fire({ icon: 'error', title: 'Validation Error', text: 'Please fill in all required fields' });
                return;
            }
            
            fetch('{{ route("sales.store-ajax") }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json', 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({ icon: 'success', title: 'Recorded!', text: 'Sale recorded successfully', timer: 1500, showConfirmButton: false })
                        .then(() => window.location.reload());
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Failed to record sale' });
                }
            });
        };
    }
    
    // ==================== LOAD BREEDING FORM ====================
    function loadBreedingForm() {
        const modalBody = document.getElementById('createBreedingContent');
        modalBody.innerHTML = `<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Loading form...</p></div>`;
        
        fetch('{{ route("breeding-records.create-form") }}', {
            headers: { 'Accept': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayBreedingForm(data.female_flocks, data.male_flocks);
            } else {
                modalBody.innerHTML = `<div class="alert alert-danger">Failed to load form: ${data.message}</div>`;
            }
        })
        .catch(error => {
            modalBody.innerHTML = `<div class="alert alert-danger">Error: ${error.message}</div>`;
        });
    }
    
    function displayBreedingForm(femaleFlocks, maleFlocks) {
        const femaleOptions = femaleFlocks.map(flock => `<option value="${flock.id}" data-gestation="${flock.gestation_days || 0}">${escapeHtml(flock.flock_number)} - ${escapeHtml(flock.breed_variety)}</option>`).join('');
        const maleOptions = maleFlocks.map(flock => `<option value="${flock.id}">${escapeHtml(flock.flock_number)} - ${escapeHtml(flock.breed_variety)}</option>`).join('');
        
        document.getElementById('createBreedingContent').innerHTML = `
            <form id="createBreedingForm">
                <div class="row">
                    <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Female Flock (Dam) <span class="text-danger">*</span></label>
                        <select name="flock_id" class="form-select" required id="femaleSelect">${femaleOptions}</select>
                    </div>
                    <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Male Flock (Sire)</label>
                        <select name="mate_id" class="form-select"><option value="">None (AI)</option>${maleOptions}</select>
                    </div>
                    <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Breeding Date <span class="text-danger">*</span></label>
                        <input type="date" name="breeding_date" class="form-control" value="${new Date().toISOString().split('T')[0]}" required id="breedingDate">
                    </div>
                    <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Expected Delivery <span class="text-danger">*</span></label>
                        <input type="date" name="expected_delivery_date" class="form-control" required id="expectedDelivery">
                        <small>Auto-calculated from species gestation</small>
                    </div>
                    <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Breeding Method <span class="text-danger">*</span></label>
                        <select name="breeding_method" class="form-select" required>
                            <option value="natural">Natural</option><option value="artificial_insemination">Artificial Insemination</option>
                        </select>
                    </div>
                    <div class="col-12 mb-3"><label class="form-label fw-semibold">Notes</label>
                        <textarea name="notes" class="form-control" rows="3"></textarea>
                    </div>
                </div>
            </form>
        `;
        
        const femaleSelect = document.getElementById('femaleSelect');
        const breedingDate = document.getElementById('breedingDate');
        const expectedDelivery = document.getElementById('expectedDelivery');
        
        function calcExpected() {
            const option = femaleSelect.options[femaleSelect.selectedIndex];
            const gestation = parseInt(option?.dataset.gestation || 0);
            const date = breedingDate.value;
            if (gestation > 0 && date) {
                const d = new Date(date);
                d.setDate(d.getDate() + gestation);
                expectedDelivery.value = d.toISOString().split('T')[0];
            }
        }
        femaleSelect?.addEventListener('change', calcExpected);
        breedingDate?.addEventListener('change', calcExpected);
        
        document.getElementById('saveCreateBreedingBtn').onclick = function() {
            const form = document.getElementById('createBreedingForm');
            const formData = new FormData(form);
            const data = {};
            formData.forEach((value, key) => { data[key] = value; });
            
            if (!data.flock_id || !data.breeding_date || !data.expected_delivery_date) {
                Swal.fire({ icon: 'error', title: 'Validation Error', text: 'Please fill in all required fields' });
                return;
            }
            
            fetch('{{ route("breeding-records.store-ajax") }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json', 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({ icon: 'success', title: 'Created!', text: 'Breeding record created successfully', timer: 1500, showConfirmButton: false })
                        .then(() => window.location.reload());
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Failed to create record' });
                }
            });
        };
    }
    
    // ==================== CREATE FLOCK FORM SUBMIT ====================
    document.getElementById('createFlockForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        fetch("{{ route('flocks.store') }}", {
            method: "POST",
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            Swal.fire({ icon: 'success', title: 'Success!', text: 'Flock created successfully', timer: 1500, showConfirmButton: false })
                .then(() => window.location.reload());
        })
        .catch(error => {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to create flock' });
        });
    });
    
    // ==================== CHARTS ====================
    const mortalityData = @json($mortalityTrend ?? []);
    if (Object.keys(mortalityData).length > 0) {
        new Chart(document.getElementById('mortalityChart'), {
            type: 'line',
            data: { labels: Object.keys(mortalityData), datasets: [{ label: 'Mortality Count', data: Object.values(mortalityData), borderColor: '#dc2626', fill: true }] }
        });
    } else {
        document.getElementById('mortalityChart')?.parentElement?.insertAdjacentHTML('beforeend', '<div class="text-center text-muted py-5">No mortality data available</div>');
    }
    
    const feedData = @json($feedTrend ?? []);
    if (feedData.length > 0) {
        new Chart(document.getElementById('feedChart'), {
            type: 'bar',
            data: { labels: feedData.map(i => i.date), datasets: [{ label: 'Feed (kg)', data: feedData.map(i => i.total_feed), backgroundColor: '#10b981' }] }
        });
    } else {
        document.getElementById('feedChart')?.parentElement?.insertAdjacentHTML('beforeend', '<div class="text-center text-muted py-5">No feed data available</div>');
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
    // Status badge configuration
    const statusConfig = {
        'active': { class: 'bg-success-soft text-success', icon: 'fa-play-circle' },
        'closed': { class: 'bg-secondary-soft text-secondary', icon: 'fa-stop-circle' },
        'quarantined': { class: 'bg-danger-soft text-danger', icon: 'fa-exclamation-triangle' },
        'breeding': { class: 'bg-info-soft text-info', icon: 'fa-heart' }
    };
    const status = statusConfig[flock.status] || statusConfig['active'];
    
    // Production type icon
    const prodIcons = {
        'meat': 'fa-drumstick-bite',
        'eggs': 'fa-egg',
        'milk': 'fa-tint',
        'breeding': 'fa-heart',
        'dual_purpose': 'fa-chart-line'
    };
    const prodIcon = prodIcons[flock.production_type] || 'fa-tag';
    
    // Update modal header subtitle
    document.getElementById('modalFlockNumber').innerHTML = `<i class="fas fa-tag me-1"></i> ${escapeHtml(flock.flock_number)}`;
    
    document.getElementById('viewFlockContent').innerHTML = `
        <!-- Stats Row -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="stat-card-sm">
                    <div class="stat-icon-sm bg-primary-soft">
                        <i class="fas fa-calendar-alt text-primary"></i>
                    </div>
                    <div class="stat-info-sm">
                        <span class="stat-label-sm">Age</span>
                        <h4 class="stat-value-sm mb-0">${summary.age_days} <small class="text-muted">days</small></h4>
                        <small>(${summary.age_weeks} weeks)</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card-sm">
                    <div class="stat-icon-sm bg-success-soft">
                        <i class="fas fa-chicken text-success"></i>
                    </div>
                    <div class="stat-info-sm">
                        <span class="stat-label-sm">Population</span>
                        <h4 class="stat-value-sm mb-0">${summary.current_count.toLocaleString()}</h4>
                        <small>/ ${flock.initial_count.toLocaleString()} total</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card-sm">
                    <div class="stat-icon-sm ${summary.mortality_rate > 5 ? 'bg-danger-soft' : 'bg-warning-soft'}">
                        <i class="fas fa-skull ${summary.mortality_rate > 5 ? 'text-danger' : 'text-warning'}"></i>
                    </div>
                    <div class="stat-info-sm">
                        <span class="stat-label-sm">Mortality Rate</span>
                        <h4 class="stat-value-sm mb-0">${summary.mortality_rate}%</h4>
                        <small>Survival: ${summary.survival_rate}%</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card-sm">
                    <div class="stat-icon-sm bg-info-soft">
                        <i class="fas fa-chart-line text-info"></i>
                    </div>
                    <div class="stat-info-sm">
                        <span class="stat-label-sm">FCR</span>
                        <h4 class="stat-value-sm mb-0">${summary.fcr}</h4>
                        <small>Feed Conversion Ratio</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Two Column Layout -->
        <div class="row g-4">
            <!-- Left Column - Basic Info -->
            <div class="col-md-6">
                <div class="info-card">
                    <div class="info-card-header">
                        <i class="fas fa-info-circle text-primary me-2"></i>
                        <h6 class="mb-0">Basic Information</h6>
                    </div>
                    <div class="info-card-body">
                        <div class="info-row">
                            <span class="info-label">Flock Number</span>
                            <span class="info-value">${escapeHtml(flock.flock_number)}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Species</span>
                            <span class="info-value">${escapeHtml(flock.species_name)} <span class="text-muted">(${escapeHtml(flock.species_code)})</span></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Breed / Variety</span>
                            <span class="info-value">${escapeHtml(flock.breed_variety)}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">House</span>
                            <span class="info-value">${escapeHtml(flock.house_name)} <span class="text-muted">(${escapeHtml(flock.house_code)})</span></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Start Date</span>
                            <span class="info-value">${escapeHtml(flock.start_date)}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Source</span>
                            <span class="info-value">${escapeHtml(flock.source || 'Not specified')}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Status</span>
                            <span class="info-value">
                                <span class="badge ${status.class} px-3 py-2 rounded-pill">
                                    <i class="fas ${status.icon} me-1"></i> ${flock.status.charAt(0).toUpperCase() + flock.status.slice(1)}
                                </span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Production Info -->
            <div class="col-md-6">
                <div class="info-card">
                    <div class="info-card-header">
                        <i class="fas fa-chart-simple text-success me-2"></i>
                        <h6 class="mb-0">Production Information</h6>
                    </div>
                    <div class="info-card-body">
                        <div class="info-row">
                            <span class="info-label">Production Type</span>
                            <span class="info-value">
                                <i class="fas ${prodIcon} me-1"></i>
                                ${flock.production_type.charAt(0).toUpperCase() + flock.production_type.slice(1).replace('_', ' ')}
                            </span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Breeding Stock</span>
                            <span class="info-value">
                                ${flock.is_breeding_stock ? '<span class="badge bg-primary-soft text-primary"><i class="fas fa-check-circle me-1"></i> Yes</span>' : '<span class="badge bg-secondary-soft text-secondary"><i class="fas fa-times-circle me-1"></i> No</span>'}
                            </span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Parity Number</span>
                            <span class="info-value">${flock.parity_number || 'N/A'}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Current Count</span>
                            <span class="info-value">${summary.current_count.toLocaleString()} animals</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Initial Count</span>
                            <span class="info-value">${flock.initial_count.toLocaleString()} animals</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Total Feed Consumed</span>
                            <span class="info-value">${summary.total_feed.toLocaleString()} kg</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Avg Daily Gain</span>
                            <span class="info-value">${summary.avg_daily_gain} kg/day</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notes Section (Full Width) -->
            ${flock.notes ? `
            <div class="col-12">
                <div class="info-card">
                    <div class="info-card-header">
                        <i class="fas fa-pencil-alt text-warning me-2"></i>
                        <h6 class="mb-0">Additional Notes</h6>
                    </div>
                    <div class="info-card-body">
                        <p class="mb-0">${escapeHtml(flock.notes)}</p>
                    </div>
                </div>
            </div>
            ` : ''}
        </div>
    `;
}
</script>
@endpush

@push('styles')
<style>
    .kpi-card {
        background: white;
        border-radius: 20px;
        border: 1px solid #e2e8f0;
        transition: all 0.3s ease;
        overflow: hidden;
    }
    .kpi-card:hover { transform: translateY(-3px); box-shadow: 0 10px 30px rgba(0,0,0,0.08); }
    .kpi-card-body { padding: 1.25rem; display: flex; align-items: center; gap: 1rem; }
    .kpi-icon { width: 55px; height: 55px; border-radius: 18px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
    .kpi-info { flex: 1; }
    .kpi-label { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; font-weight: 600; display: block; }
    .kpi-value { font-size: 1.75rem; font-weight: 700; margin: 0.25rem 0; color: #1e293b; }
    .kpi-trend { font-size: 0.7rem; color: #94a3b8; }
    
    .quick-action-btn {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.5rem;
        padding: 1rem;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        font-weight: 500;
        color: #1e293b;
        transition: all 0.3s ease;
        text-decoration: none;
        font-size: 0.875rem;
        cursor: pointer;
        width: 100%;
    }
    .quick-action-btn:hover { background: white; border-color: #10b981; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
    .quick-action-btn i { font-size: 1.5rem; }
    
    .bg-primary-soft { background: #e0f2fe; }
    .bg-success-soft { background: #dcfce7; }
    .bg-warning-soft { background: #fef3c7; }
    .bg-danger-soft { background: #fee2e2; }


    /* Daily Log Modal Styles - from daily-logs index */
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

/* View Flock Modal Styles */
.stat-card-sm {
    background: white;
    border-radius: 16px;
    padding: 1rem;
    border: 1px solid #e2e8f0;
    transition: all 0.3s ease;
}
.stat-card-sm:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}
.stat-icon-sm {
    width: 45px;
    height: 45px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    margin-bottom: 0.75rem;
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
.stat-value-sm small {
    font-size: 0.75rem;
    font-weight: normal;
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

/* Badge Colors */
.bg-primary-soft { background: #e0f2fe; }
.bg-success-soft { background: #dcfce7; }
.bg-warning-soft { background: #fef3c7; }
.bg-danger-soft { background: #fee2e2; }
.bg-info-soft { background: #d1fae5; }
.bg-secondary-soft { background: #f1f5f9; }

.text-primary { color: #0d6e4f !important; }
.text-success { color: #10b981 !important; }
.text-warning { color: #f59e0b !important; }
.text-danger { color: #dc2626 !important; }
.text-info { color: #3b82f6 !important; }
</style>
@endpush

@endsection