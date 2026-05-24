@extends('layouts.master')

@section('content')
<div class="container-fluid px-4">
    
    @include('dashboard.partials.role-header')
    
    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-card-icon bg-primary-soft">
                        <i class="fas fa-chicken text-primary"></i>
                    </div>
                    <div class="stat-card-info">
                        <span class="stat-card-label">Active Flocks</span>
                        <h3 class="stat-card-value">{{ $activeFlocks->count() }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-card-icon bg-danger-soft">
                        <i class="fas fa-skull text-danger"></i>
                    </div>
                    <div class="stat-card-info">
                        <span class="stat-card-label">Today's Mortality</span>
                        <h3 class="stat-card-value">{{ $todayMortality ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-card-icon bg-success-soft">
                        <i class="fas fa-seedling text-success"></i>
                    </div>
                    <div class="stat-card-info">
                        <span class="stat-card-label">Feed Today</span>
                        <h3 class="stat-card-value">{{ number_format($todayFeedConsumption ?? 0) }} kg</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-card-icon bg-info-soft">
                        <i class="fas fa-check-circle text-info"></i>
                    </div>
                    <div class="stat-card-info">
                        <span class="stat-card-label">Tasks Done</span>
                        <h3 class="stat-card-value" id="tasksDoneCounter">0/{{ count($todayTasks) }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions Row -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="quick-action-card" onclick="openCreateDailyLogModal()">
                <i class="fas fa-plus-circle fa-2x text-success mb-2"></i>
                <h5>Quick Log</h5>
                <small>Record daily activities</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="quick-action-card" onclick="redirectAndOpenModal('feed-issuances.index', 'createFeedIssuanceModal')">
                <i class="fas fa-seedling fa-2x text-primary mb-2"></i>
                <h5>Feed Issuance</h5>
                <small>Record feed given</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="quick-action-card" onclick="window.location.href='{{ route('daily-logs.index') }}'">
                <i class="fas fa-history fa-2x text-info mb-2"></i>
                <h5>My Logs</h5>
                <small>View all my records</small>
            </div>
        </div>
    </div>

    <!-- Today's Tasks - Using Database -->
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="card-title mb-0 fw-semibold">
                        <i class="fas fa-tasks me-2 text-primary"></i>Today's Tasks
                        <small class="text-muted ms-2">Check off tasks as you complete them</small>
                    </h5>
                </div>
                <div class="card-body">
                    @forelse($todayTasks as $task)
                    <div class="task-item d-flex justify-content-between align-items-center p-3 mb-2 bg-light rounded-3" data-task-id="{{ $task->id }}">
                        <div class="d-flex align-items-center gap-3">
                            <input class="form-check-input task-checkbox" type="checkbox" 
                                   data-task-id="{{ $task->id }}" 
                                   {{ $task->status === 'completed' ? 'checked' : '' }}>
                            <div>
                                <h6 class="mb-1 {{ $task->status === 'completed' ? 'text-decoration-line-through text-muted' : '' }}">
                                    {{ $task->title }}
                                </h6>
                                <small class="text-muted">{{ $task->description }}</small>
                                @if($task->start_time && $task->end_time)
                                    <div class="mt-1">
                                        <span class="badge bg-secondary-soft text-secondary">
                                            <i class="fas fa-clock me-1"></i>{{ \Carbon\Carbon::parse($task->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($task->end_time)->format('h:i A') }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <span class="badge bg-{{ $task->priority === 'high' ? 'danger' : ($task->priority === 'medium' ? 'warning' : 'info') }}-soft">
                            {{ ucfirst($task->priority) }} Priority
                        </span>
                    </div>
                    @empty
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-check-circle fa-2x mb-2"></i>
                        <p>No tasks assigned for today. Great job!</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="card-title mb-0 fw-semibold">
                        <i class="fas fa-history me-2 text-primary"></i>My Recent Activity
                    </h5>
                </div>
                <div class="card-body">
                    @forelse($myRecentLogs as $log)
                    <div class="activity-item d-flex gap-3 mb-3 p-2">
                        <div class="activity-icon bg-primary-soft rounded-circle p-2">
                            <i class="fas fa-clipboard-list text-primary"></i>
                        </div>
                        <div class="activity-content flex-grow-1">
                            <h6 class="mb-1">Log for {{ $log->flock->flock_number ?? 'N/A' }}</h6>
                            <p class="text-muted mb-0 small">Mortality: {{ $log->mortality_count ?? 0 }} | Feed: {{ number_format($log->feed_intake_kg ?? 0) }} kg</p>
                            <small class="text-muted">{{ $log->created_at->diffForHumans() }}</small>
                        </div>
                        <button class="btn btn-sm btn-outline-primary view-log-btn" data-id="{{ $log->id }}" title="View Details">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    @empty
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-clipboard-list fa-2x mb-2"></i>
                        <p>No recent activity. Start logging your daily tasks!</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Team Activity Section (Visible to Admin and Manager only) -->
    @if($isAdminOrManager)
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="card-title mb-0 fw-semibold">
                        <i class="fas fa-users me-2 text-primary"></i>Team Recent Activity
                    </h5>
                </div>
                <div class="card-body">
                    @forelse($teamRecentLogs as $log)
                    <div class="activity-item d-flex gap-3 mb-3 p-2">
                        <div class="activity-icon bg-info-soft rounded-circle p-2">
                            <i class="fas fa-user-check text-info"></i>
                        </div>
                        <div class="activity-content flex-grow-1">
                            <h6 class="mb-1">{{ $log->creator->name ?? 'Unknown' }} - {{ $log->flock->flock_number ?? 'N/A' }}</h6>
                            <p class="text-muted mb-0 small">Mortality: {{ $log->mortality_count ?? 0 }} | Feed: {{ number_format($log->feed_intake_kg ?? 0) }} kg</p>
                            <small class="text-muted">{{ $log->created_at->diffForHumans() }}</small>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-users fa-2x mb-2"></i>
                        <p>No team activity today</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="card-title mb-0 fw-semibold">
                        <i class="fas fa-chart-line me-2 text-primary"></i>Feed & Mortality Trend
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="workerTrendChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Daily Reminders -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-0 py-3">
            <h5 class="card-title mb-0 fw-semibold">
                <i class="fas fa-lightbulb me-2 text-warning"></i>Daily Reminders
            </h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="reminder-item p-3 bg-light rounded-3 text-center" onclick="showReminderTip('feed')">
                        <i class="fas fa-chicken fa-2x text-primary mb-2"></i>
                        <h6>Check Feed Levels</h6>
                        <small class="text-muted">Ensure feeders are full before leaving</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="reminder-item p-3 bg-light rounded-3 text-center" onclick="showReminderTip('water')">
                        <i class="fas fa-tint fa-2x text-info mb-2"></i>
                        <h6>Water Quality Check</h6>
                        <small class="text-muted">Clean and refill waterers daily</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="reminder-item p-3 bg-light rounded-3 text-center" onclick="showReminderTip('health')">
                        <i class="fas fa-heartbeat fa-2x text-danger mb-2"></i>
                        <h6>Health Observation</h6>
                        <small class="text-muted">Report sick or injured birds immediately</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Low Stock Alerts (Visible to Admin and Manager only) -->
    @if($lowFeedStock->count() > 0 && $isAdminOrManager)
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

<!-- ==================== CREATE DAILY LOG MODAL ==================== -->
<div class="modal fade" id="createLogModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg overflow-hidden">
            <div class="modal-header bg-primary text-white border-0">
                <div>
                    <h4 class="mb-1 fw-bold">
                        <i class="fa fa-plus-circle me-2"></i>
                        New Daily Log
                    </h4>
                    <small class="opacity-75">Record daily operational data</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('daily-logs.store') }}">
                @csrf
                <div class="modal-body bg-light">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Select Flock <span class="text-danger">*</span></label>
                            <select name="flock_id" class="form-select" required>
                                <option value="">Choose flock</option>
                                @foreach($activeFlocks as $flock)
                                    <option value="{{ $flock->id }}">{{ $flock->flock_number }} ({{ $flock->breed_variety ?? '' }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Log Date <span class="text-danger">*</span></label>
                            <input type="date" name="log_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Mortality</label>
                            <input type="number" name="mortality_count" class="form-control" value="0" min="0>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Culling</label>
                            <input type="number" name="culling_count" class="form-control" value="0" min="0">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Feed Intake (kg)</label>
                            <input type="number" name="feed_intake_kg" class="form-control" step="0.1">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Water (L)</label>
                            <input type="number" name="water_consumption_liters" class="form-control" step="0.1">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Avg Weight (kg)</label>
                            <input type="number" name="average_weight_kg" class="form-control" step="0.01">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Min Temp °C</label>
                            <input type="number" name="min_temperature_c" class="form-control" step="0.1">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Max Temp °C</label>
                            <input type="number" name="max_temperature_c" class="form-control" step="0.1">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Min Humidity %</label>
                            <input type="number" name="min_humidity" class="form-control" step="0.1">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Max Humidity %</label>
                            <input type="number" name="max_humidity" class="form-control" step="0.1">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Ammonia (ppm)</label>
                            <input type="number" name="ammonia_ppm" class="form-control" step="0.1">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" rows="4" class="form-control" placeholder="Enter observations..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-white border-0">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fa fa-save me-1"></i>Save Daily Log</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
    .quick-action-card {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        border: 1px solid #e2e8f0;
        height: 100%;
    }
    .quick-action-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        border-color: #10b981;
    }
    .task-item {
        transition: all 0.3s ease;
    }
    .task-item:hover {
        background: white !important;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    .task-checkbox {
        cursor: pointer;
        width: 20px;
        height: 20px;
        margin-right: 10px;
    }
    .activity-item {
        transition: all 0.3s ease;
        border-radius: 10px;
    }
    .activity-item:hover {
        background: #f8fafc;
    }
    .reminder-item {
        transition: all 0.3s ease;
        cursor: pointer;
    }
    .reminder-item:hover {
        background: white !important;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }
    .stat-card {
        background: white;
        border-radius: 16px;
        padding: 1rem;
        transition: all 0.3s ease;
        border: 1px solid #e2e8f0;
    }
    .stat-card-body {
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    .stat-card-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }
    .stat-card-info {
        flex: 1;
    }
    .stat-card-label {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #64748b;
        font-weight: 600;
    }
    .stat-card-value {
        font-size: 1.5rem;
        font-weight: 700;
        margin: 0;
        color: #1e293b;
    }
    .bg-primary-soft { background: #e0f2fe; }
    .bg-success-soft { background: #dcfce7; }
    .bg-danger-soft { background: #fee2e2; }
    .bg-info-soft { background: #d1fae5; }
    .bg-warning-soft { background: #fef3c7; }

    .completed-task {
        opacity: 0.6;
        background: #f1f5f9 !important;
    }

    #createLogModal .modal-content {
        border: none;
        border-radius: 16px;
        overflow: hidden;
    }
    #createLogModal .modal-header {
        background: linear-gradient(135deg, #2f9088, #276f69);
        color: #fff;
        border-bottom: none;
        padding: 1.2rem 1.5rem;
    }
    #createLogModal .modal-body {
        background: #f5f7fb;
        padding: 1.5rem;
    }
    #createLogModal label {
        color: #495057 !important;
        font-weight: 600;
        margin-bottom: 6px;
    }
    #createLogModal .form-control,
    #createLogModal .form-select {
        border-radius: 10px;
        border: 1px solid #dce1e7;
        min-height: 46px;
        background: #fff !important;
        color: #212529 !important;
    }
    #createLogModal textarea {
        min-height: 120px;
        resize: vertical;
    }
    #createLogModal .modal-footer {
        background: #fff;
        border-top: 1px solid #edf2f7;
        padding: 1rem 1.5rem;
    }
    #createLogModal .btn-close {
        filter: brightness(0) invert(1);
        opacity: 1;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Update task status via AJAX (Database)
    function updateTaskCounter() {
        const totalTasks = {{ count($todayTasks) }};
        const completedTasks = document.querySelectorAll('.task-checkbox:checked').length;
        const counterEl = document.getElementById('tasksDoneCounter');
        
        if (counterEl) {
            counterEl.textContent = `${completedTasks}/${totalTasks}`;
            
            if (completedTasks === totalTasks && totalTasks > 0) {
                counterEl.style.color = '#10b981';
            } else if (completedTasks > 0) {
                counterEl.style.color = '#f59e0b';
            } else {
                counterEl.style.color = '#1e293b';
            }
        }
    }
    
    // Initialize counter on page load
    document.addEventListener('DOMContentLoaded', function() {
        updateTaskCounter();
    });
    
    // Handle task checkbox changes
    document.querySelectorAll('.task-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const taskId = this.dataset.taskId;
            const isChecked = this.checked;
            const status = isChecked ? 'completed' : 'pending';
            const taskDiv = this.closest('.task-item');
            
            // Update UI immediately
            if (isChecked) {
                taskDiv.classList.add('completed-task');
                const label = taskDiv.querySelector('h6');
                if (label) label.classList.add('text-decoration-line-through', 'text-muted');
            } else {
                taskDiv.classList.remove('completed-task');
                const label = taskDiv.querySelector('h6');
                if (label) label.classList.remove('text-decoration-line-through', 'text-muted');
            }
            
            // Update counter
            updateTaskCounter();
            
            // Send AJAX request to update database
            fetch(`/worker/tasks/${taskId}/status`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ status: status })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && isChecked) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Task Completed!',
                        text: 'Great job! Keep up the good work.',
                        timer: 1500,
                        showConfirmButton: false,
                        toast: true,
                        position: 'top-end'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                // Revert UI on error
                if (isChecked) {
                    this.checked = false;
                    taskDiv.classList.remove('completed-task');
                    const label = taskDiv.querySelector('h6');
                    if (label) label.classList.remove('text-decoration-line-through', 'text-muted');
                } else {
                    this.checked = true;
                    taskDiv.classList.add('completed-task');
                    const label = taskDiv.querySelector('h6');
                    if (label) label.classList.add('text-decoration-line-through', 'text-muted');
                }
                updateTaskCounter();
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to update task status'
                });
            });
        });
    });
    
    // View log button
    document.querySelectorAll('.view-log-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const logId = this.dataset.id;
            window.location.href = `/daily-logs/${logId}`;
        });
    });
    
    // Reminder tips
    function showReminderTip(type) {
        let title = '', message = '';
        switch(type) {
            case 'feed':
                title = '🐓 Feed Check Reminder';
                message = '• Check all feeders are full<br>• Ensure feed is fresh (no mold)<br>• Record feed intake in Quick Log<br>• Report any feed quality issues';
                break;
            case 'water':
                title = '💧 Water Quality Check';
                message = '• Check waterers are clean<br>• Ensure water is flowing properly<br>• Refill empty waterers<br>• Check for leaks';
                break;
            case 'health':
                title = '🩺 Health Observation';
                message = '• Watch for sick or injured birds<br>• Check for unusual behavior<br>• Monitor feed/water intake<br>• Report concerns immediately to supervisor';
                break;
            default:
                title = 'Daily Reminder';
                message = 'Stay focused and follow safety guidelines';
        }
        Swal.fire({
            title: title,
            html: message,
            icon: 'info',
            confirmButtonColor: '#10b981',
            confirmButtonText: 'Got it!'
        });
    }
    
    // Chart for Admin/Manager
    @if($isAdminOrManager)
    const feedTrendData = @json($feedTrend ?? []);
    const mortalityTrendData = @json($mortalityTrend ?? []);
    
    if (feedTrendData.length > 0) {
        const ctx = document.getElementById('workerTrendChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: feedTrendData.map(item => item.date),
                datasets: [
                    {
                        label: 'Feed Consumption (kg)',
                        data: feedTrendData.map(item => item.total_feed),
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16,185,129,0.1)',
                        tension: 0.3,
                        fill: true,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Mortality',
                        data: mortalityTrendData.map(item => item.total_mortality),
                        borderColor: '#dc2626',
                        backgroundColor: 'rgba(220,38,38,0.1)',
                        tension: 0.3,
                        fill: true,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + context.raw;
                            }
                        }
                    }
                },
                scales: {
                    y: { title: { display: true, text: 'Feed (kg)' } },
                    y1: { position: 'right', title: { display: true, text: 'Mortality' }, grid: { drawOnChartArea: false } }
                }
            }
        });
    }
    @endif
    
    // Modal functions
    function openCreateDailyLogModal() {
        const modal = new bootstrap.Modal(document.getElementById('createLogModal'));
        modal.show();
    }
    
    function redirectAndOpenModal(routeName, modalId) {
        sessionStorage.setItem('openModalOnLoad', modalId);
        window.location.href = route(routeName);
    }
    
    function route(name) {
        const routes = {
            'feed-issuances.index': '{{ route("feed-issuances.index") }}'
        };
        return routes[name] || '/';
    }
</script>
@endpush

@endsection