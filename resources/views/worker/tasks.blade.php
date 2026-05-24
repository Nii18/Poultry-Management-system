@extends('layouts.master')

@section('title', 'My Tasks')

@section('content')
<div class="container-fluid px-4">
    <!-- Page Header -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <div class="d-flex align-items-center gap-3">
                    <div class="page-icon bg-warning-soft">
                        <i class="fas fa-tasks fs-1 text-warning"></i>
                    </div>
                    <div>
                        <h1 class="page-title mb-1">My Tasks</h1>
                        <p class="page-description text-muted mb-0">View and manage your daily assignments</p>
                    </div>
                </div>
            </div>
            <div class="col-auto">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">My Tasks</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-card-icon bg-primary-soft">
                        <i class="fas fa-list-check text-primary"></i>
                    </div>
                    <div class="stat-card-info">
                        <span class="stat-card-label">Today's Tasks</span>
                        <h3 class="stat-card-value" id="totalTasks">{{ $todayTasks->count() }}</h3>
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
                        <span class="stat-card-label">Completed</span>
                        <h3 class="stat-card-value" id="completedTasks">{{ $todayTasks->where('status', 'completed')->count() }}</h3>
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
                        <span class="stat-card-label">Completion Rate</span>
                        <h3 class="stat-card-value" id="completionRate">
                            @php
                                $total = $todayTasks->count();
                                $completed = $todayTasks->where('status', 'completed')->count();
                                $rate = $total > 0 ? round(($completed / $total) * 100) : 0;
                            @endphp
                            {{ $rate }}%
                        </h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Today's Tasks -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-0 py-3">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="card-title mb-0 fw-semibold">
                        <i class="fas fa-tasks me-2 text-primary"></i>Today's Tasks
                    </h5>
                </div>
                <div class="col-auto">
                    <div class="btn-group">
                        <button class="btn btn-sm btn-outline-primary active" data-filter="all">All</button>
                        <button class="btn btn-sm btn-outline-primary" data-filter="pending">Pending</button>
                        <button class="btn btn-sm btn-outline-success" data-filter="completed">Completed</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body" id="tasksList">
            @forelse($todayTasks as $task)
            <div class="task-item d-flex justify-content-between align-items-center p-3 mb-2 bg-light rounded-3 {{ $task->status === 'completed' ? 'completed-task' : '' }}" data-task-id="{{ $task->id }}" data-status="{{ $task->status }}">
                <div class="d-flex align-items-center gap-3">
                    <input type="checkbox" class="task-checkbox form-check-input" 
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
            <div class="text-center py-5">
                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                <h5 class="text-muted">All caught up!</h5>
                <p class="text-muted">No tasks assigned for today.</p>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Pending Tasks (Overdue) -->
    @if($pendingTasks->count() > 0)
    <div class="card shadow-sm border-0 mt-4">
        <div class="card-header bg-white border-0 py-3 bg-danger-soft">
            <h5 class="card-title mb-0 fw-semibold">
                <i class="fas fa-exclamation-triangle me-2 text-danger"></i>Overdue Tasks
            </h5>
        </div>
        <div class="card-body">
            @foreach($pendingTasks as $task)
            <div class="task-item d-flex justify-content-between align-items-center p-3 mb-2 bg-light rounded-3 border-start border-danger border-3">
                <div class="d-flex align-items-center gap-3">
                    <input type="checkbox" class="task-checkbox-pending form-check-input" 
                           data-task-id="{{ $task->id }}">
                    <div>
                        <h6 class="mb-1">{{ $task->title }}</h6>
                        <small class="text-muted">{{ $task->description }}</small>
                        <div class="mt-1">
                            <span class="badge bg-danger-soft text-danger">
                                <i class="fas fa-calendar-times me-1"></i>Due: {{ \Carbon\Carbon::parse($task->due_date)->format('d M Y') }}
                            </span>
                        </div>
                    </div>
                </div>
                <span class="badge bg-{{ $task->priority === 'high' ? 'danger' : ($task->priority === 'medium' ? 'warning' : 'info') }}-soft">
                    {{ ucfirst($task->priority) }} Priority
                </span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Upcoming Tasks -->
    @if($upcomingTasks->count() > 0)
    <div class="card shadow-sm border-0 mt-4">
        <div class="card-header bg-white border-0 py-3">
            <h5 class="card-title mb-0 fw-semibold">
                <i class="fas fa-calendar-week me-2 text-primary"></i>Upcoming Tasks (Next 7 Days)
            </h5>
        </div>
        <div class="card-body">
            @foreach($upcomingTasks as $task)
            <div class="task-item d-flex justify-content-between align-items-center p-3 mb-2 bg-light rounded-3">
                <div class="d-flex align-items-center gap-3">
                    <div>
                        <h6 class="mb-1">{{ $task->title }}</h6>
                        <small class="text-muted">{{ $task->description }}</small>
                        <div class="mt-1">
                            <span class="badge bg-info-soft text-info">
                                <i class="fas fa-calendar-alt me-1"></i>{{ \Carbon\Carbon::parse($task->due_date)->format('d M Y') }}
                            </span>
                        </div>
                    </div>
                </div>
                <span class="badge bg-{{ $task->priority === 'high' ? 'danger' : ($task->priority === 'medium' ? 'warning' : 'info') }}-soft">
                    {{ ucfirst($task->priority) }} Priority
                </span>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Update stats function
    function updateStats() {
        const totalTasks = {{ $todayTasks->count() }};
        const completedTasks = document.querySelectorAll('.task-checkbox:checked').length;
        const rate = totalTasks > 0 ? Math.round((completedTasks / totalTasks) * 100) : 0;
        
        document.getElementById('totalTasks').textContent = totalTasks;
        document.getElementById('completedTasks').textContent = completedTasks;
        document.getElementById('completionRate').textContent = rate + '%';
    }
    
    // Handle today's task checkboxes
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
            
            // Update stats
            updateStats();
            
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
                updateStats();
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to update task status'
                });
            });
        });
    });
    
    // Handle pending tasks checkboxes (overdue)
    document.querySelectorAll('.task-checkbox-pending').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const taskId = this.dataset.taskId;
            const isChecked = this.checked;
            const status = isChecked ? 'completed' : 'pending';
            const taskDiv = this.closest('.task-item');
            
            if (isChecked) {
                taskDiv.style.opacity = '0.6';
                Swal.fire({
                    icon: 'success',
                    title: 'Task Completed!',
                    text: 'Better late than never! Great job catching up.',
                    timer: 2000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
            }
            
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
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                this.checked = false;
                taskDiv.style.opacity = '1';
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to update task status'
                });
            });
        });
    });
    
    // Filter buttons
    document.querySelectorAll('.btn-group .btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const filter = this.dataset.filter;
            document.querySelectorAll('.btn-group .btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            const tasks = document.querySelectorAll('.task-item');
            tasks.forEach(task => {
                const isCompleted = task.classList.contains('completed-task');
                if (filter === 'all') {
                    task.style.display = 'flex';
                } else if (filter === 'pending') {
                    task.style.display = isCompleted ? 'none' : 'flex';
                } else if (filter === 'completed') {
                    task.style.display = isCompleted ? 'flex' : 'none';
                }
            });
        });
    });
</script>
@endpush

@push('styles')
<style>
    .task-item {
        transition: all 0.3s ease;
    }
    .task-item:hover {
        background: white !important;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    .completed-task {
        opacity: 0.7;
        background: #f1f5f9 !important;
    }
    .task-checkbox {
        width: 20px;
        height: 20px;
        cursor: pointer;
    }
    .task-checkbox-pending {
        width: 20px;
        height: 20px;
        cursor: pointer;
    }
</style>
@endpush

@endsection