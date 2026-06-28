{{-- resources/views/worker/tasks.blade.php --}}
@extends('layouts.master')

@section('title', 'My Tasks')

@php
$windowMeta = [
    'morning'   => ['label' => 'Morning',   'icon' => 'fa-sun',       'time' => '06:00 – 12:00', 'color' => 'warning'],
    'afternoon' => ['label' => 'Afternoon',  'icon' => 'fa-cloud-sun', 'time' => '12:00 – 17:00', 'color' => 'primary'],
    'evening'   => ['label' => 'Evening',    'icon' => 'fa-moon',      'time' => '17:00 – 22:00', 'color' => 'info'],
];

// Numeric order so we can compare windows
$windowOrder   = ['morning' => 0, 'afternoon' => 1, 'evening' => 2];
$currentIdx    = $windowOrder[$currentWindow] ?? -1; // -1 when 'none' (outside hours)

$gracePeriodMinutes = 15;
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
        $assignments  = $grouped[$windowKey];
        $isActive     = $currentWindow === $windowKey;
        $thisIdx      = $windowOrder[$windowKey] ?? 0;
        $isFuture     = $thisIdx > $currentIdx;  // no active window → $currentIdx=-1 → all future
        $isPast       = $thisIdx < $currentIdx;

        $total        = $assignments->count();
        $doneCount    = $assignments->where('status', 'completed')->count();
        $pct          = $total > 0 ? round(($doneCount / $total) * 100) : 0;
    @endphp

    <div class="card shadow-sm border-0 mb-4 window-card
                {{ $isActive  ? 'window-active'  : '' }}
                {{ $isPast    ? 'window-past'     : '' }}
                {{ $isFuture  ? 'window-future'   : '' }}"
         id="window-{{ $windowKey }}">

        {{-- Card Header --}}
        <div class="card-header bg-white border-0 py-3">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
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
                    @elseif($isFuture)
                        <span class="badge bg-{{ $meta['color'] }}-soft text-{{ $meta['color'] }}">
                            <i class="fas fa-lock me-1"></i>Upcoming
                        </span>
                    @endif
                </div>

                <span class="text-muted small window-counter" data-window="{{ $windowKey }}">
                    {{ $doneCount }} / {{ $total }} done
                </span>
            </div>

            @if($total > 0)
            <div class="progress mt-2" style="height: 5px; border-radius: 3px;">
                <div class="progress-bar bg-{{ $meta['color'] }} window-progress"
                     data-window="{{ $windowKey }}"
                     style="width: {{ $pct }}%; transition: width 0.4s ease;">
                </div>
            </div>
            @endif

            {{-- Future window notice --}}
            @if($isFuture)
            <div class="mt-2">
                <small class="text-muted fst-italic">
                    <i class="fas fa-info-circle me-1"></i>
                    Tasks in this window will unlock at
                    {{ $windowKey === 'afternoon' ? '12:00 PM' : '5:00 PM' }}.
                </small>
            </div>
            @endif
        </div>

        {{-- Task List --}}
        <div class="card-body pt-2 {{ $isFuture ? 'window-body-locked' : '' }}">
            @forelse($assignments->sortBy(fn($a) => $a->task?->start_time) as $assignment)
            @php
                $task      = $assignment->task;
                $pColors   = ['high' => 'danger', 'medium' => 'warning', 'low' => 'info'];
                $pc        = $pColors[$task?->priority] ?? 'secondary';
                $isCompleted = $assignment->status === 'completed';
                $isMissed    = $assignment->status === 'missed';

                // Undo availability
                $canUndo = $isCompleted
                    && $assignment->completed_at
                    && $assignment->completed_at->gt(now()->subMinutes($gracePeriodMinutes));

                $undoDeadlineIso = $canUndo
                    ? $assignment->completed_at->addMinutes($gracePeriodMinutes)->toIso8601String()
                    : null;
            @endphp

            <div class="task-item d-flex justify-content-between align-items-center p-3 mb-2 rounded-3 status-{{ $assignment->status }}"
                 data-assignment-id="{{ $assignment->id }}"
                 data-window="{{ $windowKey }}">

                <div class="d-flex align-items-center gap-3 flex-grow-1">

                    {{-- Status indicator --}}
                    @if($isMissed)
                        <span class="flex-shrink-0" title="Missed">
                            <i class="fas fa-times-circle text-danger fs-5"></i>
                        </span>

                    @elseif($isCompleted)
                        <span class="flex-shrink-0" title="Completed">
                            <i class="fas fa-check-circle text-success fs-5"></i>
                        </span>

                    @elseif($isFuture)
                        {{-- Locked: future window --}}
                        <span class="flex-shrink-0 text-muted" title="Locked until window opens">
                            <i class="fas fa-lock fs-5"></i>
                        </span>

                    @else
                        {{-- Active or past (not yet completed / missed) — checkable --}}
                        <input type="checkbox"
                               class="task-checkbox form-check-input flex-shrink-0"
                               data-assignment-id="{{ $assignment->id }}"
                               data-window="{{ $windowKey }}">
                    @endif

                    {{-- Task details --}}
                    <div class="flex-grow-1">
                        <h6 class="mb-1 task-title
                            {{ $isCompleted ? 'text-decoration-line-through text-muted' : '' }}
                            {{ $isMissed    ? 'text-muted fst-italic' : '' }}
                            {{ $isFuture && !$isCompleted && !$isMissed ? 'text-muted' : '' }}">
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
                                <i class="fas fa-clock me-1"></i>Window closed — not completed
                            </span>
                            @endif

                            @if($assignment->status === 'in_progress')
                            <span class="badge bg-primary-soft text-primary">
                                <i class="fas fa-spinner me-1"></i>In progress
                            </span>
                            @endif

                            @if($isFuture && !$isCompleted && !$isMissed)
                            <span class="badge bg-secondary-soft text-secondary">
                                <i class="fas fa-lock me-1"></i>
                                Unlocks {{ $windowKey === 'afternoon' ? 'at 12:00 PM' : 'at 5:00 PM' }}
                            </span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Right column: priority + undo --}}
                <div class="d-flex flex-column align-items-end gap-1 ms-2 flex-shrink-0">
                    <span class="badge bg-{{ $pc }}-soft text-{{ $pc }}">
                        {{ ucfirst($task?->priority ?? 'medium') }}
                    </span>

                    @if($canUndo)
                    <button class="btn btn-sm btn-outline-secondary undo-task-btn"
                            data-assignment-id="{{ $assignment->id }}"
                            data-window="{{ $windowKey }}"
                            data-undo-deadline="{{ $undoDeadlineIso }}"
                            title="Undo completion">
                        <i class="fas fa-undo fa-xs me-1"></i>
                        <span class="undo-countdown"></span>
                    </button>
                    @endif
                </div>

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
    /* ── Window card ─────────────────────────────────────────── */
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
    .window-future {
        opacity: 0.65;
    }
    .window-body-locked {
        pointer-events: none;     /* Prevents any click interaction */
        user-select: none;
    }
    /* Re-enable pointer on undo buttons even if somehow rendered inside locked window */
    .window-body-locked .undo-task-btn {
        pointer-events: auto;
    }

    /* ── Pulse dot ───────────────────────────────────────────── */
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

    /* ── Task item ───────────────────────────────────────────── */
    .task-item {
        background: #f8fafc;
        transition: background 0.2s ease, box-shadow 0.2s ease;
    }
    .task-item:hover {
        background: #ffffff !important;
        box-shadow: 0 2px 10px rgba(0,0,0,0.07);
    }
    .task-item.status-completed {
        background: #f0fdf4 !important;
        border-left: 3px solid #10b981;
        border-radius: 0 0.5rem 0.5rem 0 !important;
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

    /* ── Checkbox ────────────────────────────────────────────── */
    .task-checkbox {
        width: 20px;
        height: 20px;
        cursor: pointer;
        flex-shrink: 0;
    }

    /* ── Undo button ─────────────────────────────────────────── */
    .undo-task-btn {
        font-size: 0.7rem;
        padding: 2px 8px;
        border-radius: 20px;
        white-space: nowrap;
        pointer-events: auto !important; /* always clickable */
    }
    .undo-countdown {
        font-variant-numeric: tabular-nums;
        font-size: 0.7rem;
    }

    /* ── Soft colour helpers ─────────────────────────────────── */
    .bg-primary-soft   { background: #e0f2fe; }
    .bg-success-soft   { background: #dcfce7; }
    .bg-danger-soft    { background: #fee2e2; }
    .bg-info-soft      { background: #d1fae5; }
    .bg-warning-soft   { background: #fef3c7; }
    .bg-secondary-soft { background: #f1f5f9; }

    /* ── Stat card ───────────────────────────────────────────── */
    .stat-card        { background: white; border-radius: 16px; padding: 1rem; border: 1px solid #e2e8f0; }
    .stat-card-body   { display: flex; align-items: center; gap: 1rem; }
    .stat-card-icon   { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; }
    .stat-card-info   { flex: 1; }
    .stat-card-label  { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; font-weight: 600; }
    .stat-card-value  { font-size: 1.5rem; font-weight: 700; margin: 0; color: #1e293b; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(function () {
    'use strict';

    const CSRF             = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const GRACE_MINUTES    = {{ $gracePeriodMinutes }};
    const windowOrder      = { morning: 0, afternoon: 1, evening: 2 };
    const currentWindow    = '{{ $currentWindow }}';   // 'morning' | 'afternoon' | 'evening' | 'none'
    const currentIdx       = windowOrder[currentWindow] ?? -1;
    const undoTimers       = {}; // assignmentId → setInterval ID

    // ── Stat counters ─────────────────────────────────────────────────────────
    function updateGlobalStats() {
        const completed = document.querySelectorAll('.task-item.status-completed').length;
        const el = document.getElementById('stat-completed');
        if (el) el.textContent = completed;
    }

    function updateWindowProgress(windowKey) {
        const card = document.getElementById(`window-${windowKey}`);
        if (!card) return;

        const allItems  = card.querySelectorAll('.task-item');
        const total     = allItems.length;
        const completed = card.querySelectorAll('.task-item.status-completed').length;
        const pct       = total > 0 ? Math.round((completed / total) * 100) : 0;

        const bar     = card.querySelector('.window-progress');
        const counter = card.querySelector('.window-counter');
        if (bar)     bar.style.width = pct + '%';
        if (counter) counter.textContent = `${completed} / ${total} done`;
    }

    // ── Apply completed state to a task item ─────────────────────────────────
    function applyCompleted(taskItem, assignmentId, completedAtIso, windowKey) {
        taskItem.classList.remove('status-pending', 'status-in_progress', 'status-missed');
        taskItem.classList.add('status-completed');

        // Replace checkbox with a static check icon
        const cb = taskItem.querySelector('.task-checkbox');
        if (cb) {
            const icon = document.createElement('span');
            icon.className = 'flex-shrink-0';
            icon.title     = 'Completed';
            icon.innerHTML = '<i class="fas fa-check-circle text-success fs-5"></i>';
            cb.replaceWith(icon);
        }

        // Strikethrough title
        const titleEl = taskItem.querySelector('.task-title');
        if (titleEl) titleEl.classList.add('text-decoration-line-through', 'text-muted');

        // Inject "Done at …" badge
        const badgeRow = taskItem.querySelector('.d-flex.flex-wrap.gap-1');
        if (badgeRow && completedAtIso) {
            const doneTime = new Date(completedAtIso).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            const badge    = document.createElement('span');
            badge.className = 'badge bg-success-soft text-success done-at-badge';
            badge.innerHTML = `<i class="fas fa-check me-1"></i>Done at ${doneTime}`;
            badgeRow.appendChild(badge);
        }

        // Inject undo button
        const actionCol = taskItem.querySelector('.d-flex.flex-column.align-items-end');
        if (actionCol && completedAtIso) {
            const deadline = new Date(completedAtIso);
            deadline.setMinutes(deadline.getMinutes() + GRACE_MINUTES);

            const undoBtn = document.createElement('button');
            undoBtn.className             = 'btn btn-sm btn-outline-secondary undo-task-btn';
            undoBtn.dataset.assignmentId  = assignmentId;
            undoBtn.dataset.window        = windowKey;
            undoBtn.dataset.undoDeadline  = deadline.toISOString();
            undoBtn.title                 = `Undo (${GRACE_MINUTES} min grace period)`;
            undoBtn.innerHTML             = '<i class="fas fa-undo fa-xs me-1"></i><span class="undo-countdown"></span>';
            actionCol.appendChild(undoBtn);

            bindUndoButton(undoBtn);
            startCountdown(undoBtn, deadline, assignmentId);
        }

        updateWindowProgress(windowKey);
        updateGlobalStats();
    }

    // ── Revert to pending ─────────────────────────────────────────────────────
    function applyPending(taskItem, assignmentId, windowKey) {
        taskItem.classList.remove('status-completed', 'status-in_progress', 'status-missed');
        taskItem.classList.add('status-pending');

        // Replace static icon with a live checkbox
        const icon = taskItem.querySelector('span[title="Completed"]');
        if (icon) {
            const cb = document.createElement('input');
            cb.type    = 'checkbox';
            cb.className = 'form-check-input task-checkbox flex-shrink-0';
            cb.dataset.assignmentId = assignmentId;
            cb.dataset.window       = windowKey;
            cb.title   = 'Mark as completed';
            icon.replaceWith(cb);
            bindCheckbox(cb);
        }

        // Remove strikethrough
        const titleEl = taskItem.querySelector('.task-title');
        if (titleEl) titleEl.classList.remove('text-decoration-line-through', 'text-muted');

        // Remove "done at" badge
        taskItem.querySelectorAll('.done-at-badge').forEach(b => b.remove());

        // Remove undo button + stop timer
        const undoBtn = taskItem.querySelector('.undo-task-btn');
        if (undoBtn) undoBtn.remove();
        if (undoTimers[assignmentId]) {
            clearInterval(undoTimers[assignmentId]);
            delete undoTimers[assignmentId];
        }

        updateWindowProgress(windowKey);
        updateGlobalStats();
    }

    // ── Undo countdown ────────────────────────────────────────────────────────
    function startCountdown(btn, deadline, assignmentId) {
        const countdownEl = btn.querySelector('.undo-countdown');

        function tick() {
            const remaining = Math.floor((deadline - Date.now()) / 1000);
            if (remaining <= 0) {
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

    // ── Bind undo button ──────────────────────────────────────────────────────
    function bindUndoButton(btn) {
        btn.addEventListener('click', function () {
            const assignmentId = this.dataset.assignmentId;
            const windowKey    = this.dataset.window;
            const deadline     = new Date(this.dataset.undoDeadline);

            if (Date.now() > deadline.getTime()) {
                Swal.fire({
                    icon: 'warning', title: 'Too late!',
                    text: 'The undo window has expired.',
                    toast: true, position: 'top-end',
                    timer: 2500, showConfirmButton: false,
                });
                this.remove();
                return;
            }

            Swal.fire({
                icon             : 'question',
                title            : 'Undo completion?',
                text             : 'This will mark the task as pending again.',
                showCancelButton : true,
                confirmButtonText: 'Yes, undo it',
                cancelButtonText : 'Keep completed',
                confirmButtonColor: '#6b7280',
            }).then(result => {
                if (!result.isConfirmed) return;
                sendStatusUpdate(assignmentId, 'pending', windowKey);
            });
        });
    }

    // ── Bind checkbox ─────────────────────────────────────────────────────────
    function bindCheckbox(checkbox) {
        checkbox.addEventListener('change', function () {
            const assignmentId = this.dataset.assignmentId;
            const windowKey    = this.dataset.window;
            const taskIdx      = windowOrder[windowKey] ?? 0;

            // Guard: block future windows (safety net — they should already be
            // non-interactive via CSS pointer-events:none on the card body)
            if (taskIdx > currentIdx) {
                this.checked = false;
                Swal.fire({
                    icon : 'warning',
                    title: 'Not yet!',
                    text : `${windowKey.charAt(0).toUpperCase() + windowKey.slice(1)} tasks unlock when that window opens.`,
                    toast: true, position: 'top-end',
                    timer: 2500, showConfirmButton: false,
                });
                return;
            }

            if (this.checked) {
                sendStatusUpdate(assignmentId, 'completed', windowKey);
            }
            // Unchecking via checkbox is intentionally disabled;
            // workers use the undo button within the grace period instead.
            // Reset the visual to remain checked if somehow unchecked.
            this.checked = true;
        });
    }

    // ── AJAX call ─────────────────────────────────────────────────────────────
    function sendStatusUpdate(assignmentId, newStatus, windowKey) {
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
                applyCompleted(taskItem, assignmentId, data.assignment.completed_at, windowKey);

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
                applyPending(taskItem, assignmentId, windowKey);

                Swal.fire({
                    icon             : 'info',
                    title            : 'Task reopened',
                    text             : 'Marked as pending.',
                    timer            : 1800,
                    showConfirmButton : false,
                    toast            : true,
                    position         : 'top-end',
                });
            }
        })
        .catch(err => {
            console.error('Task update failed:', err);

            // Revert any optimistic UI — uncheck the checkbox if it was just checked
            const cb = taskItem?.querySelector('.task-checkbox');
            if (cb) cb.checked = false;

            Swal.fire({
                icon : 'error',
                title: 'Update failed',
                text : err.message || 'Could not save. Please try again.',
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

    updateGlobalStats();

})();
</script>
@endpush