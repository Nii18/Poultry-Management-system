@extends('layouts.master')

@section('title', 'Task Management')

@section('content')
<div class="container-fluid px-4">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <div class="d-flex align-items-center gap-3">
                    <div class="page-icon bg-primary-soft">
                        <i class="fas fa-tasks fs-1 text-primary"></i>
                    </div>
                    <div>
                        <h1 class="page-title mb-1">Task Management</h1>
                        <p class="page-description text-muted mb-0">Assign and track worker tasks</p>
                    </div>
                </div>
            </div>
            <div class="col-auto d-flex gap-2">
                <a href="{{ route('manager.attendance') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-user-clock me-1"></i> Attendance
                </a>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTaskModal">
                    <i class="fas fa-plus me-1"></i> New Task
                </button>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Assigned To</th>
                            <th>Priority</th>
                            <th>Due Date</th>
                            <th>Window</th>
                            <th>Status</th>
                            <th>Recurring</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tasks as $task)
                        <tr>
                            <td>
                                <strong>{{ $task->title }}</strong>
                                @if($task->description)
                                    <div class="text-muted small">{{ \Illuminate\Support\Str::limit($task->description, 60) }}</div>
                                @endif
                            </td>
                            <td>{{ $task->assignedTo->name ?? 'Unassigned' }}</td>
                            <td>
                                <span class="badge bg-{{ $task->priority === 'high' ? 'danger' : ($task->priority === 'medium' ? 'warning' : 'info') }}-soft text-{{ $task->priority === 'high' ? 'danger' : ($task->priority === 'medium' ? 'warning' : 'info') }}">
                                    {{ ucfirst($task->priority) }}
                                </span>
                            </td>
                            <td>{{ \Carbon\Carbon::parse($task->due_date)->format('M d, Y') }}</td>
                            <td>
                                @if($task->start_time && $task->end_time)
                                    {{ \Carbon\Carbon::parse($task->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($task->end_time)->format('h:i A') }}
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $task->status === 'completed' ? 'success' : ($task->status === 'missed' ? 'secondary' : 'primary') }}-soft text-{{ $task->status === 'completed' ? 'success' : ($task->status === 'missed' ? 'secondary' : 'primary') }}">
                                    {{ ucfirst($task->status) }}
                                </span>
                            </td>
                            <td>
                                @if($task->is_recurring)
                                    <i class="fas fa-sync-alt text-primary" title="{{ ucfirst($task->recurring_pattern) }}"></i>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <button type="button"
                                    class="btn btn-sm btn-outline-primary edit-task-btn"
                                    data-id="{{ $task->id }}"
                                    data-title="{{ $task->title }}"
                                    data-description="{{ $task->description }}"
                                    data-priority="{{ $task->priority }}"
                                    data-due-date="{{ \Carbon\Carbon::parse($task->due_date)->format('Y-m-d') }}"
                                    data-start-time="{{ $task->start_time ? \Carbon\Carbon::parse($task->start_time)->format('H:i') : '' }}"
                                    data-end-time="{{ $task->end_time ? \Carbon\Carbon::parse($task->end_time)->format('H:i') : '' }}"
                                    data-assigned-to="{{ $task->assigned_to }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger delete-task-btn" data-id="{{ $task->id }}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">No tasks found in the last 7 days.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $tasks->links() }}
            </div>
        </div>
    </div>
</div>

{{-- ===== CREATE Task Modal ===== --}}
<div class="modal fade" id="createTaskModal" tabindex="-1" aria-labelledby="createTaskModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-semibold" id="createTaskModalLabel">
                    <i class="fas fa-plus-circle text-primary me-2"></i>Assign New Task
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-2">
                <div id="taskFormErrors" class="alert alert-danger d-none"></div>
                <form id="createTaskForm">
                    @csrf
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-medium">Task Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-medium">Description</label>
                            <textarea name="description" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Assign To <span class="text-danger">*</span></label>
                            <select name="assigned_to" class="form-select" required>
                                <option value="">— Select worker —</option>
                                @foreach($workers as $worker)
                                    <option value="{{ $worker->id }}">{{ $worker->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Priority <span class="text-danger">*</span></label>
                            <select name="priority" class="form-select" required>
                                <option value="">— Select —</option>
                                <option value="high">🔴 High</option>
                                <option value="medium">🟡 Medium</option>
                                <option value="low">🔵 Low</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-medium">Due Date <span class="text-danger">*</span></label>
                            <input type="date" name="due_date" class="form-control" required min="{{ now()->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-medium">Start Time</label>
                            <input type="time" name="start_time" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-medium">End Time</label>
                            <input type="time" name="end_time" class="form-control">
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_recurring" id="isRecurringCheck" value="1">
                                <label class="form-check-label fw-medium" for="isRecurringCheck">Recurring task</label>
                            </div>
                        </div>
                        <div id="recurringOptions" class="col-12 d-none">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Repeat Pattern</label>
                                    <select name="recurring_pattern" class="form-select">
                                        <option value="">— Select —</option>
                                        <option value="daily">Daily</option>
                                        <option value="weekly">Weekly</option>
                                        <option value="monthly">Monthly</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">For how many weeks?</label>
                                    <input type="number" name="recurring_weeks" class="form-control" min="1" max="12" placeholder="1 – 12">
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary px-4" id="saveTaskBtn">
                    <span id="saveTaskSpinner" class="spinner-border spinner-border-sm me-1 d-none" role="status"></span>
                    Assign Task
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ===== EDIT Task Modal ===== --}}
<div class="modal fade" id="editTaskModal" tabindex="-1" aria-labelledby="editTaskModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-semibold" id="editTaskModalLabel">
                    <i class="fas fa-edit text-primary me-2"></i>Edit Task
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-2">
                <div id="editTaskFormErrors" class="alert alert-danger d-none"></div>
                <form id="editTaskForm">
                    @csrf
                    @method('PUT')
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-medium">Task Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="editTitle" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-medium">Description</label>
                            <textarea name="description" id="editDescription" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Assign To <span class="text-danger">*</span></label>
                            <select name="assigned_to" id="editAssignedTo" class="form-select" required>
                                <option value="">— Select worker —</option>
                                @foreach($workers as $worker)
                                    <option value="{{ $worker->id }}">{{ $worker->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Priority <span class="text-danger">*</span></label>
                            <select name="priority" id="editPriority" class="form-select" required>
                                <option value="">— Select —</option>
                                <option value="high">🔴 High</option>
                                <option value="medium">🟡 Medium</option>
                                <option value="low">🔵 Low</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-medium">Due Date <span class="text-danger">*</span></label>
                            <input type="date" name="due_date" id="editDueDate" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-medium">Start Time</label>
                            <input type="time" name="start_time" id="editStartTime" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-medium">End Time</label>
                            <input type="time" name="end_time" id="editEndTime" class="form-control">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary px-4" id="updateTaskBtn">
                    <span id="updateTaskSpinner" class="spinner-border spinner-border-sm me-1 d-none" role="status"></span>
                    Save Changes
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Hidden delete forms --}}
@foreach($tasks as $task)
<form id="deleteTaskForm{{ $task->id }}" action="{{ route('manager.tasks.delete', $task->id) }}" method="POST" class="d-none">
    @csrf
    @method('DELETE')
</form>
@endforeach

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Shared helpers ────────────────────────────────────────────────────────

    function showErrors(container, data) {
        let html = '<ul class="mb-0">';
        if (data.errors) {
            Object.values(data.errors).forEach(msgs => {
                msgs.forEach(m => { html += '<li>' + m + '</li>'; });
            });
        } else {
            html += '<li>' + (data.message || 'Something went wrong.') + '</li>';
        }
        html += '</ul>';
        container.innerHTML = html;
        container.classList.remove('d-none');
    }

    function setBusy(btn, spinner, busy) {
        btn.disabled = busy;
        spinner.classList.toggle('d-none', !busy);
    }

    // ── CREATE modal ──────────────────────────────────────────────────────────

    const createTaskModal = document.getElementById('createTaskModal');
    const createTaskForm  = document.getElementById('createTaskForm');
    const saveTaskBtn     = document.getElementById('saveTaskBtn');
    const saveSpinner     = document.getElementById('saveTaskSpinner');
    const taskErrors      = document.getElementById('taskFormErrors');

    document.getElementById('isRecurringCheck').addEventListener('change', function () {
        document.getElementById('recurringOptions').classList.toggle('d-none', !this.checked);
    });

    createTaskModal.addEventListener('hidden.bs.modal', function () {
        createTaskForm.reset();
        document.getElementById('recurringOptions').classList.add('d-none');
        taskErrors.classList.add('d-none');
        taskErrors.innerHTML = '';
    });

    saveTaskBtn.addEventListener('click', function () {
        const formData = new FormData(createTaskForm);
        formData.set('is_recurring', document.getElementById('isRecurringCheck').checked ? '1' : '0');

        setBusy(saveTaskBtn, saveSpinner, true);
        taskErrors.classList.add('d-none');

        fetch('{{ route('manager.tasks.store') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: formData,
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(createTaskModal).hide();
                Swal.fire({
                    icon: 'success',
                    title: 'Task Assigned!',
                    text: 'The task has been successfully assigned to the worker.',
                    confirmButtonColor: '#0d6efd',
                    timer: 2500,
                    timerProgressBar: true,
                }).then(() => location.reload());
            } else {
                showErrors(taskErrors, data);
            }
        })
        .catch(() => {
            taskErrors.innerHTML = '<ul class="mb-0"><li>Network error. Please try again.</li></ul>';
            taskErrors.classList.remove('d-none');
        })
        .finally(() => setBusy(saveTaskBtn, saveSpinner, false));
    });

    // ── EDIT modal ────────────────────────────────────────────────────────────

    const editTaskModal   = document.getElementById('editTaskModal');
    const editTaskForm    = document.getElementById('editTaskForm');
    const updateTaskBtn   = document.getElementById('updateTaskBtn');
    const updateSpinner   = document.getElementById('updateTaskSpinner');
    const editTaskErrors  = document.getElementById('editTaskFormErrors');
    let   currentEditId   = null;

    // Populate edit modal when edit button is clicked
    document.querySelectorAll('.edit-task-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            currentEditId = this.dataset.id;

            document.getElementById('editTitle').value          = this.dataset.title;
            document.getElementById('editDescription').value    = this.dataset.description || '';
            document.getElementById('editPriority').value       = this.dataset.priority;
            document.getElementById('editDueDate').value        = this.dataset.dueDate;
            document.getElementById('editStartTime').value      = this.dataset.startTime || '';
            document.getElementById('editEndTime').value        = this.dataset.endTime || '';
            document.getElementById('editAssignedTo').value     = this.dataset.assignedTo;

            editTaskErrors.classList.add('d-none');
            editTaskErrors.innerHTML = '';

            new bootstrap.Modal(editTaskModal).show();
        });
    });

    editTaskModal.addEventListener('hidden.bs.modal', function () {
        editTaskForm.reset();
        editTaskErrors.classList.add('d-none');
        editTaskErrors.innerHTML = '';
        currentEditId = null;
    });

    updateTaskBtn.addEventListener('click', function () {
        if (!currentEditId) return;

        const formData = new FormData(editTaskForm);
        formData.append('_method', 'PUT');

        setBusy(updateTaskBtn, updateSpinner, true);
        editTaskErrors.classList.add('d-none');

        fetch('/manager/tasks/' + currentEditId, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: formData,
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(editTaskModal).hide();
                Swal.fire({
                    icon: 'success',
                    title: 'Task Updated!',
                    text: 'The task has been successfully updated.',
                    confirmButtonColor: '#0d6efd',
                    timer: 2500,
                    timerProgressBar: true,
                }).then(() => location.reload());
            } else {
                showErrors(editTaskErrors, data);
            }
        })
        .catch(() => {
            editTaskErrors.innerHTML = '<ul class="mb-0"><li>Network error. Please try again.</li></ul>';
            editTaskErrors.classList.remove('d-none');
        })
        .finally(() => setBusy(updateTaskBtn, updateSpinner, false));
    });

    // ── DELETE ────────────────────────────────────────────────────────────────

    document.querySelectorAll('.delete-task-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const taskId = this.dataset.id;
            Swal.fire({
                title: 'Delete this task?',
                text: 'This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it',
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.getElementById('deleteTaskForm' + taskId);
                    // Submit via fetch so we can show a SweetAlert after
                    fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: new FormData(form),
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: 'The task has been deleted.',
                                confirmButtonColor: '#0d6efd',
                                timer: 2000,
                                timerProgressBar: true,
                            }).then(() => location.reload());
                        } else {
                            Swal.fire('Error', data.message || 'Could not delete the task.', 'error');
                        }
                    })
                    .catch(() => {
                        Swal.fire('Error', 'Network error. Please try again.', 'error');
                    });
                }
            });
        });
    });

});
</script>
@endpush