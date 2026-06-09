{{-- resources/views/worker/tasks.blade.php --}}
@extends('layouts.master')

@section('title', 'My Tasks')

@php
$windowMeta = [
    'morning'   => ['label' => 'Morning',   'icon' => 'fa-sun',       'time' => '06:00 – 12:00', 'color' => 'warning'],
    'afternoon' => ['label' => 'Afternoon',  'icon' => 'fa-cloud-sun', 'time' => '12:00 – 17:00', 'color' => 'primary'],
    'evening'   => ['label' => 'Evening',    'icon' => 'fa-moon',      'time' => '17:00 – 22:00', 'color' => 'info'],
];
@endphp

@section('content')
<div class="container-fluid px-4">

    {{-- Page Header --}}
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <div class="d-flex align-items-center gap-3">
                    <div class="page-icon bg-warning-soft">
                        <i class="fas fa-tasks fs-1 text-warning"></i>
                    </div>
                    <div>
                        <h1 class="page-title mb-1">My Tasks</h1>
                        <p class="page-description text-muted mb-0">
                            {{ now()->format('l, d F Y') }} &middot;
                            @if($currentWindow !== 'none')
                                <span class="badge bg-success-soft text-success">
                                    <span class="pulse-dot me-1"></span>
                                    {{ ucfirst($currentWindow) }} window active
                                </span>
                            @else
                                <span class="badge bg-secondary-soft text-secondary">
                                    Outside working hours
                                </span>
                            @endif
                        </p>
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

    {{-- Stats Row --}}
    <div class="row g-4 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-card-icon bg-primary-soft">
                        <i class="fas fa-list-check text-primary"></i>
                    </div>
                    <div class="stat-card-info">
                        <span class="stat-card-label">Total Today</span>
                        <h3 class="stat-card-value" id="stat-total">{{ $stats['total'] }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-card-icon bg-success-soft">
                        <i class="fas fa-check-circle text-success"></i>
                    </div>
                    <div class="stat-card-info">
                        <span class="stat-card-label">Completed</span>
                        <h3 class="stat-card-value text-success" id="stat-completed">{{ $stats['completed'] }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-card-icon bg-danger-soft">
                        <i class="fas fa-exclamation-circle text-danger"></i>
                    </div>
                    <div class="stat-card-info">
                        <span class="stat-card-label">Missed</span>
                        <h3 class="stat-card-value text-danger" id="stat-missed">{{ $stats['missed'] }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-card-icon bg-info-soft">
                        <i class="fas fa-chart-line text-info"></i>
                    </div>
                    <div class="stat-card-info">
                        <span class="stat-card-label">30-day Rate</span>
                        <h3 class="stat-card-value">{{ $stats['completion_rate'] }}%</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Time Window Cards --}}
    @foreach($windowMeta as $windowKey => $meta)
    @php
        $assignments = $grouped[$windowKey];
        $isActive    = $currentWindow === $windowKey;
        $isPast      = match($currentWindow) {
            'afternoon' => $windowKey === 'morning',
            'evening'   => in_array($windowKey, ['morning', 'afternoon']),
            'none'      => true,
            default     => false,
        };
        $total       = $assignments->count();
        $doneCount   = $assignments->where('status', 'completed')->count();
        $pct         = $total > 0 ? round(($doneCount / $total) * 100) : 0;
    @endphp

    <div class="card shadow-sm border-0 mb-4 window-card {{ $isActive ? 'window-active' : '' }} {{ $isPast ? 'window-past' : '' }}"
         id="window-{{ $windowKey }}">

        {{-- Card Header --}}
        <div class="card-header bg-white border-0 py-3">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">

                {{-- Left: title + badges --}}
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    @if($isActive)
                        <span class="pulse-dot" aria-label="Active"></span>
                    @endif
                    <i class="fas {{ $meta['icon'] }} text-{{ $meta['color'] }}" aria-hidden="true"></i>
                    <h5 class="card-title mb-0 fw-semibold">{{ $meta['label'] }}</h5>
                    <span class="badge bg-secondary-soft text-secondary">
                        <i class="fas fa-clock me-1"></i>{{ $meta['time'] }}
                    </span>
                    @if($isActive)
                        <span class="badge bg-success text-white">
                            <span class="pulse-dot me-1" style="width:6px;height:6px;"></span>Live
                        </span>
                    @elseif($isPast)
                        <span class="badge bg-secondary text-white">Past</span>
                    @else
                        <span class="badge bg-{{ $meta['color'] }}-soft text-{{ $meta['color'] }}">Upcoming</span>
                    @endif
                </div>

                {{-- Right: counter --}}
                <span class="text-muted small window-counter" data-window="{{ $windowKey }}">
                    {{ $doneCount }} / {{ $total }} done
                </span>
            </div>

            {{-- Progress bar --}}
            @if($total > 0)
            <div class="progress mt-2" style="height: 5px; border-radius: 3px;">
                <div class="progress-bar bg-{{ $meta['color'] }} window-progress"
                     data-window="{{ $windowKey }}"
                     style="width: {{ $pct }}%; transition: width 0.4s ease;">
                </div>
            </div>
            @endif
        </div>

        {{-- Task List --}}
        <div class="card-body pt-2">
            @forelse($assignments->sortBy(fn($a) => $a->task?->start_time) as $assignment)
            @php
                $task = $assignment->task;
                $pColors = ['high' => 'danger', 'medium' => 'warning', 'low' => 'info'];
                $pc = $pColors[$task?->priority] ?? 'secondary';
            @endphp

            <div class="task-item d-flex justify-content-between align-items-center p-3 mb-2 rounded-3 status-{{ $assignment->status }}"
                 data-assignment-id="{{ $assignment->id }}"
                 data-window="{{ $windowKey }}">

                <div class="d-flex align-items-center gap-3">

                    {{-- Status indicator --}}
                    @if($assignment->status === 'missed')
                        <span class="flex-shrink-0" title="Missed">
                            <i class="fas fa-times-circle text-danger fs-5"></i>
                        </span>
                    @else
                        <input type="checkbox"
                               class="task-checkbox form-check-input flex-shrink-0"
                               data-assignment-id="{{ $assignment->id }}"
                               data-window="{{ $windowKey }}"
                               {{ $assignment->status === 'completed' ? 'checked' : '' }}>
                    @endif

                    {{-- Task details --}}
                    <div>
                        <h6 class="mb-1 task-title
                            {{ $assignment->status === 'completed' ? 'text-decoration-line-through text-muted' : '' }}
                            {{ $assignment->status === 'missed' ? 'text-muted fst-italic' : '' }}">
                            {{ $task?->title ?? 'Untitled task' }}
                        </h6>

                        @if($task?->description)
                            <small class="text-muted d-block mb-1">{{ $task->description }}</small>
                        @endif

                        <div class="d-flex flex-wrap gap-1 mt-1">
                            {{-- Time window badge --}}
                            @if($task?->start_time && $task?->end_time)
                            <span class="badge bg-secondary-soft text-secondary">
                                <i class="fas fa-clock me-1"></i>
                                {{ \Carbon\Carbon::parse($task->start_time)->format('h:i A') }}
                                – {{ \Carbon\Carbon::parse($task->end_time)->format('h:i A') }}
                            </span>
                            @endif

                            {{-- Completed at --}}
                            @if($assignment->status === 'completed' && $assignment->completed_at)
                            <span class="badge bg-success-soft text-success">
                                <i class="fas fa-check me-1"></i>
                                Done at {{ $assignment->completed_at->format('h:i A') }}
                            </span>
                            @endif

                            {{-- Missed label --}}
                            @if($assignment->status === 'missed')
                            <span class="badge bg-danger-soft text-danger">
                                <i class="fas fa-clock me-1"></i>Window closed — not completed
                            </span>
                            @endif

                            {{-- In progress --}}
                            @if($assignment->status === 'in_progress')
                            <span class="badge bg-primary-soft text-primary">
                                <i class="fas fa-spinner me-1"></i>In progress
                            </span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Priority badge --}}
                <span class="badge bg-{{ $pc }}-soft text-{{ $pc }} flex-shrink-0 ms-2">
                    {{ ucfirst($task?->priority ?? 'medium') }}
                </span>

            </div>
            @empty
            <div class="text-center py-5 text-muted">
                <i class="fas fa-calendar-check fa-2x mb-3 opacity-50"></i>
                <p class="mb-0 small">No tasks scheduled for this window.</p>
            </div>
            @endforelse
        </div>
    </div>
    @endforeach

</div>
@endsection

@push('styles')
<style>
    /* ── Window card ────────────────────────────────────────────── */
    .window-card {
        border-left: 3px solid transparent;
        transition: opacity 0.3s ease;
    }
    .window-active {
        border-left: 3px solid #10b981 !important;
    }
    .window-past {
        opacity: 0.82;
    }

    /* ── Pulse dot ──────────────────────────────────────────────── */
    .pulse-dot {
        display: inline-block;
        width: 8px;
        height: 8px;
        background: #10b981;
        border-radius: 50%;
        animation: pulse-anim 1.5s ease-in-out infinite;
        vertical-align: middle;
    }
    @keyframes pulse-anim {
        0%, 100% { opacity: 1;   transform: scale(1);   }
        50%       { opacity: 0.4; transform: scale(1.5); }
    }

    /* ── Task item base ─────────────────────────────────────────── */
    .task-item {
        background: #f8fafc;
        transition: background 0.2s ease, box-shadow 0.2s ease;
    }
    .task-item:hover {
        background: #ffffff !important;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.07);
    }

    /* ── Task item states ───────────────────────────────────────── */
    .task-item.status-completed {
        background: #f0fdf4 !important;
    }
    .task-item.status-missed {
        background: #fff5f5 !important;
        opacity: 0.85;
    }
    .task-item.status-in_progress {
        background: #eff6ff !important;
        border-left: 3px solid #3b82f6;
        border-radius: 0 0.5rem 0.5rem 0 !important;
    }

    /* ── Checkbox ───────────────────────────────────────────────── */
    .task-checkbox {
        width: 20px;
        height: 20px;
        cursor: pointer;
        flex-shrink: 0;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(function () {
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    // ── Checkbox handler ───────────────────────────────────────────────────
    document.querySelectorAll('.task-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function () {
            const assignmentId = this.dataset.assignmentId;
            const windowKey    = this.dataset.window;
            const checked      = this.checked;
            const newStatus    = checked ? 'completed' : 'pending';
            const taskItem     = this.closest('.task-item');
            const titleEl      = taskItem.querySelector('.task-title');

            // Optimistic UI update
            applyStatusClass(taskItem, newStatus);
            toggleStrikethrough(titleEl, checked);
            updateWindowProgress(windowKey);
            updateGlobalStats();

            // Persist to server
            fetch(`/worker/tasks/${assignmentId}/status`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                    'Accept'      : 'application/json',
                },
                body: JSON.stringify({ status: newStatus }),
            })
            .then(r => r.json())
            .then(data => {
                if (!data.success) throw new Error(data.message ?? 'Unknown error');

                if (checked) {
                    Swal.fire({
                        icon             : 'success',
                        title            : 'Task completed!',
                        text             : 'Great job. Keep it up.',
                        timer            : 1500,
                        showConfirmButton : false,
                        toast            : true,
                        position         : 'top-end',
                    });
                }
            })
            .catch(err => {
                console.error('Task update failed:', err);

                // Revert optimistic update
                const revertStatus = checked ? 'pending' : 'completed';
                this.checked = !checked;
                applyStatusClass(taskItem, revertStatus);
                toggleStrikethrough(titleEl, !checked);
                updateWindowProgress(windowKey);
                updateGlobalStats();

                Swal.fire({
                    icon  : 'error',
                    title : 'Update failed',
                    text  : 'Could not save task status. Please try again.',
                });
            });
        });
    });

    // ── Helpers ────────────────────────────────────────────────────────────

    function applyStatusClass(el, status) {
        el.classList.remove('status-pending', 'status-completed', 'status-in_progress', 'status-missed');
        el.classList.add(`status-${status}`);
    }

    function toggleStrikethrough(el, apply) {
        if (!el) return;
        if (apply) {
            el.classList.add('text-decoration-line-through', 'text-muted');
        } else {
            el.classList.remove('text-decoration-line-through', 'text-muted');
        }
    }

    function updateWindowProgress(windowKey) {
        const card = document.getElementById(`window-${windowKey}`);
        if (!card) return;

        const allItems  = card.querySelectorAll('.task-item');
        const total     = allItems.length;
        const completed = card.querySelectorAll('.task-checkbox:checked').length;
        const pct       = total > 0 ? Math.round((completed / total) * 100) : 0;

        const bar     = card.querySelector('.window-progress');
        const counter = card.querySelector('.window-counter');

        if (bar)     bar.style.width = pct + '%';
        if (counter) counter.textContent = `${completed} / ${total} done`;
    }

    function updateGlobalStats() {
        const totalCompleted = document.querySelectorAll('.task-checkbox:checked').length;
        const statEl = document.getElementById('stat-completed');
        if (statEl) statEl.textContent = totalCompleted;
    }

})();
</script>
@endpush