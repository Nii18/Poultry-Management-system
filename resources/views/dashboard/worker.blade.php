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
                        <i class="fas fa-play-circle text-success"></i>
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
                        {{--
                            We count ALL of today's assignments (dashboard + tasks page
                            share the same WorkerTaskAssignment pool). The counter here
                            reflects only the dashboard card's list; the tasks page has
                            its own grouped view. Both write to the same DB rows.
                        --}}
                        <h3 class="stat-card-value" id="tasksDoneCounter">
                            {{ $todayTasks->where('status', 'completed')->count() }}/{{ $todayTasks->count() }}
                        </h3>
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

    <!-- Today's Tasks -->
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="card-title mb-0 fw-semibold">
                        <i class="fas fa-tasks me-2 text-primary"></i>Today's Tasks
                        <small class="text-muted ms-2">Assigned to you by your manager</small>
                    </h5>
                </div>
                <div class="card-body">
                    @php
                        $gracePeriodMinutes = 15;

                        // Window ordering, used to determine whether a task's window
                        // is still locked relative to the current window. Mirrors the
                        // same WINDOW_ORDER used in DailyTaskService::isWindowLocked()
                        // and in worker/tasks.blade.php — kept in sync intentionally.
                        $windowOrderMap = ['morning' => 0, 'afternoon' => 1, 'evening' => 2];
                        $currentIdx     = $windowOrderMap[$currentWindow] ?? -1; // -1 when 'none'
                    @endphp

                    @forelse($todayTasks as $assignment)
                    @php
                        $task        = $assignment->task;           // WorkerTask via relationship
                        $isCompleted = $assignment->status === 'completed';
                        $isMissed    = $assignment->status === 'missed';

                        // Is this task's window still locked (hasn't opened yet today)?
                        // A task with no recognised window is never locked (fail open),
                        // matching DailyTaskService::isWindowLocked().
                        $taskIdx  = $windowOrderMap[$task?->window] ?? null;
                        $isLocked = !$isCompleted && !$isMissed && $taskIdx !== null && $taskIdx > $currentIdx;

                        // Is the undo window still open? (completed_at within 15 min)
                        $canUndo = $isCompleted
                            && $assignment->completed_at
                            && $assignment->completed_at->gt(now()->subMinutes($gracePeriodMinutes));

                        // ISO string for JS countdown (null-safe)
                        $undoDeadlineIso = $canUndo
                            ? $assignment->completed_at->addMinutes($gracePeriodMinutes)->toIso8601String()
                            : null;
                    @endphp

                    <div class="task-item d-flex justify-content-between align-items-center p-3 mb-2 bg-light rounded-3
                                {{ $isCompleted ? 'task-item--completed' : '' }}
                                {{ $isMissed    ? 'task-item--missed'    : '' }}
                                {{ $isLocked    ? 'task-item--locked'    : '' }}"
                         data-assignment-id="{{ $assignment->id }}">

                        <div class="d-flex align-items-center gap-3 flex-grow-1">

                            {{-- Status indicator --}}
                            @if($isMissed)
                                <span class="flex-shrink-0" title="Missed">
                                    <i class="fas fa-times-circle text-danger fs-5"></i>
                                </span>
                            @elseif($isCompleted)
                                {{-- Static checkmark; undo shown separately --}}
                                <span class="flex-shrink-0" title="Completed">
                                    <i class="fas fa-check-circle text-success fs-5"></i>
                                </span>
                            @elseif($isLocked)
                                {{-- Locked: window hasn't opened yet --}}
                                <span class="flex-shrink-0 text-muted" title="Locked until window opens">
                                    <i class="fas fa-lock fs-5"></i>
                                </span>
                            @else
                                <input class="form-check-input task-checkbox flex-shrink-0"
                                       type="checkbox"
                                       data-assignment-id="{{ $assignment->id }}"
                                       title="Mark as completed">
                            @endif

                            <div class="flex-grow-1">
                                <h6 class="mb-1 task-title
                                    {{ $isCompleted ? 'text-decoration-line-through text-muted' : 'text-dark' }}
                                    {{ $isMissed    ? 'text-muted fst-italic' : '' }}
                                    {{ $isLocked    ? 'text-muted' : '' }}">
                                    {{ $task?->title ?? 'Untitled task' }}
                                </h6>

                                @if($task?->description)
                                    <small class="text-muted d-block mb-1">{{ $task->description }}</small>
                                @endif

                                <div class="d-flex flex-wrap gap-1 mt-1">
                                    @if($task?->start_time && $task?->end_time)
                                    <span class="badge bg-secondary-soft text-secondary">
                                        <i class="fas fa-clock me-1"></i>
                                        {{ \Carbon\Carbon::parse($task->start_time)->format('h:i A') }}
                                        – {{ \Carbon\Carbon::parse($task->end_time)->format('h:i A') }}
                                    </span>
                                    @endif

                                    @if($isCompleted && $assignment->completed_at)
                                    <span class="badge bg-success-soft text-success">
                                        <i class="fas fa-check me-1"></i>
                                        Done at {{ $assignment->completed_at->format('h:i A') }}
                                    </span>
                                    @endif

                                    @if($isMissed)
                                    <span class="badge bg-danger-soft text-danger">
                                        <i class="fas fa-clock me-1"></i>Window closed
                                    </span>
                                    @endif

                                    @if($isLocked)
                                    <span class="badge bg-secondary-soft text-secondary">
                                        <i class="fas fa-lock me-1"></i>
                                        Unlocks {{ $task?->window === 'afternoon' ? 'at 12:00 PM' : 'at 5:00 PM' }}
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="d-flex flex-column align-items-end gap-1 ms-2 flex-shrink-0">
                            {{-- Priority badge --}}
                            @php
                                $pColors = ['high' => 'danger', 'medium' => 'warning', 'low' => 'info'];
                                $pc = $pColors[$task?->priority ?? 'low'] ?? 'secondary';
                            @endphp
                            <span class="badge bg-{{ $pc }}-soft text-{{ $pc }}">
                                {{ ucfirst($task?->priority ?? 'low') }}
                            </span>

                            {{-- Undo button (grace period only) --}}
                            @if($canUndo)
                            <button class="btn btn-sm btn-outline-secondary undo-task-btn"
                                    data-assignment-id="{{ $assignment->id }}"
                                    data-undo-deadline="{{ $undoDeadlineIso }}"
                                    title="Undo completion (available for {{ $gracePeriodMinutes }} min)">
                                <i class="fas fa-undo fa-xs me-1"></i>
                                <span class="undo-countdown"></span>
                            </button>
                            @endif
                        </div>

                    </div>
                    @empty
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-check-circle fa-2x mb-2"></i>
                        <p>No tasks assigned for today.</p>
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
                        <i class="fas fa-drumstick-bite fa-2x text-primary mb-2"></i>
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
    @if(isset($lowFeedStock) && $lowFeedStock->count() > 0 && $isAdminOrManager)
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
                    <div class="p-3 bg-light rounded-3 d-flex justify-content-between align-items-center">
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
                            <input type="number" name="mortality_count" class="form-control" value="0" min="0">
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
        transition: background 0.25s ease, box-shadow 0.25s ease;
    }
    .task-item:hover {
        background: white !important;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    .task-item--completed {
        background: #f0fdf4 !important;
        border-left: 3px solid #10b981;
        border-radius: 0 0.5rem 0.5rem 0 !important;
    }
    .task-item--missed {
        background: #fff5f5 !important;
        opacity: 0.8;
    }
    .task-item--locked {
        opacity: 0.65;
        pointer-events: none;
    }
    .task-checkbox {
        cursor: pointer;
        width: 20px;
        height: 20px;
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
    .stat-card-info { flex: 1; }
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
    .bg-primary-soft   { background: #e0f2fe; }
    .bg-success-soft   { background: #dcfce7; }
    .bg-danger-soft    { background: #fee2e2; }
    .bg-info-soft      { background: #d1fae5; }
    .bg-warning-soft   { background: #fef3c7; }
    .bg-secondary-soft { background: #f1f5f9; }

    .undo-task-btn {
        font-size: 0.7rem;
        padding: 2px 8px;
        border-radius: 20px;
        white-space: nowrap;
    }
    .undo-countdown {
        font-variant-numeric: tabular-nums;
        font-size: 0.7rem;
    }

    #createLogModal .modal-content  { border: none; border-radius: 16px; overflow: hidden; }
    #createLogModal .modal-header   { background: linear-gradient(135deg, #2f9088, #276f69); color: #fff; border-bottom: none; padding: 1.2rem 1.5rem; }
    #createLogModal .modal-body     { background: #f5f7fb; padding: 1.5rem; }
    #createLogModal label           { color: #495057 !important; font-weight: 600; margin-bottom: 6px; }
    #createLogModal .form-control,
    #createLogModal .form-select    { border-radius: 10px; border: 1px solid #dce1e7; min-height: 46px; background: #fff !important; color: #212529 !important; }
    #createLogModal textarea        { min-height: 120px; resize: vertical; }
    #createLogModal .modal-footer   { background: #fff; border-top: 1px solid #edf2f7; padding: 1rem 1.5rem; }
    #createLogModal .btn-close      { filter: brightness(0) invert(1); opacity: 1; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function () {
    'use strict';

    const CSRF              = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const GRACE_MINUTES     = {{ $gracePeriodMinutes ?? 15 }};
    const counterEl         = document.getElementById('tasksDoneCounter');
    const undoTimers        = {}; // assignmentId → interval ID

    // ── Counter ───────────────────────────────────────────────────────────────
    function updateCounter() {
        if (!counterEl) return;
        const total     = document.querySelectorAll('.task-item[data-assignment-id]').length;
        const completed = document.querySelectorAll('.task-item--completed').length;

        counterEl.textContent = `${completed}/${total}`;
        counterEl.style.color = completed === total && total > 0
            ? '#10b981'
            : completed > 0 ? '#f59e0b' : '#1e293b';
    }

    // ── Mark item as completed in the DOM ────────────────────────────────────
    function applyCompleted(taskItem, assignmentId, completedAt) {
        taskItem.classList.add('task-item--completed');
        taskItem.classList.remove('task-item--missed');

        // Replace checkbox with a static checkmark
        const checkboxWrapper = taskItem.querySelector('.task-checkbox');
        if (checkboxWrapper) {
            const icon = document.createElement('span');
            icon.className = 'flex-shrink-0';
            icon.title     = 'Completed';
            icon.innerHTML = '<i class="fas fa-check-circle text-success fs-5"></i>';
            checkboxWrapper.replaceWith(icon);
        }

        // Strikethrough title
        const titleEl = taskItem.querySelector('.task-title');
        if (titleEl) titleEl.classList.add('text-decoration-line-through', 'text-muted');

        // Show undo button (grace period)
        const priorityBadge = taskItem.querySelector('.badge');
        const actionCol     = taskItem.querySelector('.d-flex.flex-column');
        if (actionCol) {
            const existingUndo = actionCol.querySelector('.undo-task-btn');
            if (!existingUndo) {
                const deadline = new Date(completedAt);
                deadline.setMinutes(deadline.getMinutes() + GRACE_MINUTES);

                const undoBtn     = document.createElement('button');
                undoBtn.className = 'btn btn-sm btn-outline-secondary undo-task-btn';
                undoBtn.dataset.assignmentId  = assignmentId;
                undoBtn.dataset.undoDeadline  = deadline.toISOString();
                undoBtn.title     = `Undo (available ${GRACE_MINUTES} min after completion)`;
                undoBtn.innerHTML = '<i class="fas fa-undo fa-xs me-1"></i><span class="undo-countdown"></span>';
                actionCol.appendChild(undoBtn);

                bindUndoButton(undoBtn);
                startCountdown(undoBtn, deadline, assignmentId);
            }
        }

        updateCounter();
    }

    // ── Revert item to pending in the DOM ────────────────────────────────────
    function applyPending(taskItem, assignmentId) {
        taskItem.classList.remove('task-item--completed');

        // Replace static icon with a checkbox
        const iconSpan = taskItem.querySelector('span[title="Completed"]');
        if (iconSpan) {
            const cb       = document.createElement('input');
            cb.type        = 'checkbox';
            cb.className   = 'form-check-input task-checkbox flex-shrink-0';
            cb.dataset.assignmentId = assignmentId;
            cb.title       = 'Mark as completed';
            iconSpan.replaceWith(cb);
            bindCheckbox(cb);
        }

        // Remove strikethrough
        const titleEl = taskItem.querySelector('.task-title');
        if (titleEl) titleEl.classList.remove('text-decoration-line-through', 'text-muted');

        // Remove undo button & clear its countdown timer
        const undoBtn = taskItem.querySelector('.undo-task-btn');
        if (undoBtn) undoBtn.remove();
        if (undoTimers[assignmentId]) {
            clearInterval(undoTimers[assignmentId]);
            delete undoTimers[assignmentId];
        }

        // Remove "done at" badge
        taskItem.querySelectorAll('.badge.bg-success-soft').forEach(b => b.remove());

        updateCounter();
    }

    // ── Countdown timer for undo button ──────────────────────────────────────
    function startCountdown(btn, deadline, assignmentId) {
        const countdownEl = btn.querySelector('.undo-countdown');

        function tick() {
            const remaining = Math.floor((deadline - Date.now()) / 1000);
            if (remaining <= 0) {
                // Grace period expired — remove the button
                clearInterval(undoTimers[assignmentId]);
                delete undoTimers[assignmentId];
                btn.remove();
                return;
            }
            const m = Math.floor(remaining / 60);
            const s = remaining % 60;
            countdownEl.textContent = `${m}:${String(s).padStart(2, '0')}`;
        }

        tick();
        undoTimers[assignmentId] = setInterval(tick, 1000);
    }

    // ── Bind undo button click ────────────────────────────────────────────────
    function bindUndoButton(btn) {
        btn.addEventListener('click', function () {
            const assignmentId = this.dataset.assignmentId;
            const deadline     = new Date(this.dataset.undoDeadline);

            if (Date.now() > deadline.getTime()) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Too late!',
                    text: 'The undo window has expired.',
                    toast: true, position: 'top-end',
                    timer: 2500, showConfirmButton: false,
                });
                this.remove();
                return;
            }

            Swal.fire({
                icon              : 'question',
                title             : 'Undo completion?',
                text              : 'This will mark the task as pending again.',
                showCancelButton  : true,
                confirmButtonText : 'Yes, undo it',
                cancelButtonText  : 'Keep completed',
                confirmButtonColor: '#6b7280',
            }).then(result => {
                if (!result.isConfirmed) return;
                sendStatusUpdate(assignmentId, 'pending');
            });
        });
    }

    // ── Bind checkbox (check → complete) ─────────────────────────────────────
    function bindCheckbox(checkbox) {
        checkbox.addEventListener('change', function () {
            if (!this.checked) return; // checkboxes only go one direction: → completed
            const assignmentId = this.dataset.assignmentId;
            sendStatusUpdate(assignmentId, 'completed');
        });
    }

    // ── AJAX status update ────────────────────────────────────────────────────
    function sendStatusUpdate(assignmentId, newStatus) {
        const taskItem = document.querySelector(`.task-item[data-assignment-id="${assignmentId}"]`);

        fetch(`/worker/tasks/${assignmentId}/status`, {
            method : 'PUT',
            headers: {
                'Content-Type' : 'application/json',
                'X-CSRF-TOKEN' : CSRF,
                'Accept'       : 'application/json',
            },
            body: JSON.stringify({ status: newStatus }),
        })
        .then(r => r.json())
        .then(data => {
            if (!data.success) throw new Error(data.message ?? 'Unknown error');

            if (newStatus === 'completed') {
                applyCompleted(taskItem, assignmentId, data.assignment.completed_at);

                Swal.fire({
                    icon             : 'success',
                    title            : 'Task completed! 🎉',
                    text             : 'Great work. Keep it up!',
                    timer            : 2000,
                    showConfirmButton : false,
                    toast            : true,
                    position         : 'top-end',
                });
            } else {
                applyPending(taskItem, assignmentId);

                Swal.fire({
                    icon             : 'info',
                    title            : 'Task reopened',
                    text             : 'Task marked as pending.',
                    timer            : 1800,
                    showConfirmButton : false,
                    toast            : true,
                    position         : 'top-end',
                });
            }
        })
        .catch(err => {
            console.error('Task update failed:', err);

            // Revert any optimistic UI
            const checkbox = taskItem?.querySelector('.task-checkbox');
            if (checkbox) checkbox.checked = false;

            Swal.fire({
                icon : 'error',
                title: 'Update failed',
                text : err.message || 'Could not save task status. Please try again.',
            });
        });
    }

    // ── Initial binding ───────────────────────────────────────────────────────
    document.querySelectorAll('.task-checkbox').forEach(bindCheckbox);
    document.querySelectorAll('.undo-task-btn').forEach(btn => {
        bindUndoButton(btn);
        const deadline = new Date(btn.dataset.undoDeadline);
        startCountdown(btn, deadline, btn.dataset.assignmentId);
    });

    updateCounter();

    // ── View log buttons ──────────────────────────────────────────────────────
    document.querySelectorAll('.view-log-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            window.location.href = `/daily-logs/${this.dataset.id}`;
        });
    });

    // ── Reminder tips ─────────────────────────────────────────────────────────
    window.showReminderTip = function (type) {
        const tips = {
            feed  : { title: '🐓 Feed Check Reminder',   html: '• Check all feeders are full<br>• Ensure feed is fresh (no mold)<br>• Record feed intake in Quick Log<br>• Report any feed quality issues' },
            water : { title: '💧 Water Quality Check',    html: '• Check waterers are clean<br>• Ensure water is flowing properly<br>• Refill empty waterers<br>• Check for leaks' },
            health: { title: '🩺 Health Observation',     html: '• Watch for sick or injured birds<br>• Check for unusual behaviour<br>• Monitor feed/water intake<br>• Report concerns to your supervisor immediately' },
        };
        const tip = tips[type] ?? { title: 'Daily Reminder', html: 'Stay focused and follow safety guidelines.' };
        Swal.fire({ ...tip, icon: 'info', confirmButtonColor: '#10b981', confirmButtonText: 'Got it!' });
    };

    // ── Trend chart (admin/manager only) ─────────────────────────────────────
    @if($isAdminOrManager)
    const feedTrendData     = @json($feedTrend ?? []);
    const mortalityTrendData = @json($mortalityTrend ?? []);

    if (feedTrendData.length > 0) {
        const ctx = document.getElementById('workerTrendChart')?.getContext('2d');
        if (ctx) {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels  : feedTrendData.map(i => i.date),
                    datasets: [
                        {
                            label          : 'Feed Consumption (kg)',
                            data           : feedTrendData.map(i => i.total_feed),
                            borderColor    : '#10b981',
                            backgroundColor: 'rgba(16,185,129,0.1)',
                            tension        : 0.3,
                            fill           : true,
                            yAxisID        : 'y',
                        },
                        {
                            label          : 'Mortality',
                            data           : mortalityTrendData.map(i => i.total_mortality),
                            borderColor    : '#dc2626',
                            backgroundColor: 'rgba(220,38,38,0.1)',
                            tension        : 0.3,
                            fill           : true,
                            yAxisID        : 'y1',
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    scales: {
                        y  : { title: { display: true, text: 'Feed (kg)' } },
                        y1 : { position: 'right', title: { display: true, text: 'Mortality' }, grid: { drawOnChartArea: false } },
                    },
                },
            });
        }
    }
    @endif

    // ── Modal helpers ─────────────────────────────────────────────────────────
    window.openCreateDailyLogModal = function () {
        new bootstrap.Modal(document.getElementById('createLogModal')).show();
    };

    window.redirectAndOpenModal = function (routeName, modalId) {
        sessionStorage.setItem('openModalOnLoad', modalId);
        window.location.href = routeMap[routeName] ?? '/';
    };

    const routeMap = {
        'feed-issuances.index': '{{ route("feed-issuances.index") }}',
    };

})();
</script>
@endpush

@endsection