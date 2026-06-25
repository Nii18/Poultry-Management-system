@extends('layouts.master')

@section('title', 'New Task')

@section('content')
<div class="container-fluid px-4">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <h1 class="page-title mb-1">Create New Task</h1>
                <p class="page-description text-muted mb-0">Assign a task to a worker</p>
            </div>
            <div class="col-auto">
                <a href="{{ route('manager.tasks') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back to Tasks
                </a>
            </div>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('manager.tasks.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Assign To <span class="text-danger">*</span></label>
                        <select name="assigned_to" class="form-select" required>
                            <option value="">Choose worker</option>
                            @foreach($workers as $worker)
                                <option value="{{ $worker->id }}" {{ old('assigned_to') == $worker->id ? 'selected' : '' }}>
                                    {{ $worker->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Priority <span class="text-danger">*</span></label>
                        <select name="priority" class="form-select" required>
                            <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>Low</option>
                            <option value="medium" {{ old('priority') === 'medium' ? 'selected' : '' }}>Medium</option>
                            <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>High</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Due Date <span class="text-danger">*</span></label>
                        <input type="date" name="due_date" class="form-control" value="{{ old('due_date', date('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Start Time</label>
                        <input type="time" name="start_time" class="form-control" value="{{ old('start_time') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">End Time</label>
                        <input type="time" name="end_time" class="form-control" value="{{ old('end_time') }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" rows="3" class="form-control">{{ old('description') }}</textarea>
                    </div>

                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="isRecurring" name="is_recurring" value="1" {{ old('is_recurring') ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="isRecurring">
                                Make this a recurring task
                            </label>
                        </div>
                    </div>

                    <div class="col-md-4 recurring-field" style="display: none;">
                        <label class="form-label">Recurring Pattern</label>
                        <select name="recurring_pattern" class="form-select">
                            <option value="daily" {{ old('recurring_pattern') === 'daily' ? 'selected' : '' }}>Daily</option>
                            <option value="weekly" {{ old('recurring_pattern') === 'weekly' ? 'selected' : '' }}>Weekly</option>
                            <option value="monthly" {{ old('recurring_pattern') === 'monthly' ? 'selected' : '' }}>Monthly</option>
                        </select>
                    </div>
                    <div class="col-md-4 recurring-field" style="display: none;">
                        <label class="form-label">Repeat For (weeks)</label>
                        <input type="number" name="recurring_weeks" class="form-control" min="1" max="12" value="{{ old('recurring_weeks', 4) }}">
                    </div>
                </div>

                <div class="mt-4 d-flex justify-content-end gap-2">
                    <a href="{{ route('manager.tasks') }}" class="btn btn-light border">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Create Task
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const checkbox = document.getElementById('isRecurring');
        const fields = document.querySelectorAll('.recurring-field');

        function toggleFields() {
            fields.forEach(f => f.style.display = checkbox.checked ? 'block' : 'none');
        }

        checkbox.addEventListener('change', toggleFields);
        toggleFields();
    });
</script>
@endpush