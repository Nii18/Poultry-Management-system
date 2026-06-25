@extends('layouts.master')

@section('title', 'Edit Task')

@section('content')
<div class="container-fluid px-4">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <h1 class="page-title mb-1">Edit Task</h1>
                <p class="page-description text-muted mb-0">Update task details</p>
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
            <form method="POST" action="{{ route('manager.tasks.update', $task->id) }}">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" value="{{ old('title', $task->title) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Assign To <span class="text-danger">*</span></label>
                        <select name="assigned_to" class="form-select" required>
                            @foreach($workers as $worker)
                                <option value="{{ $worker->id }}" {{ old('assigned_to', $task->assigned_to) == $worker->id ? 'selected' : '' }}>
                                    {{ $worker->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Priority <span class="text-danger">*</span></label>
                        <select name="priority" class="form-select" required>
                            <option value="low" {{ old('priority', $task->priority) === 'low' ? 'selected' : '' }}>Low</option>
                            <option value="medium" {{ old('priority', $task->priority) === 'medium' ? 'selected' : '' }}>Medium</option>
                            <option value="high" {{ old('priority', $task->priority) === 'high' ? 'selected' : '' }}>High</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Due Date <span class="text-danger">*</span></label>
                        <input type="date" name="due_date" class="form-control" value="{{ old('due_date', \Carbon\Carbon::parse($task->due_date)->format('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Start Time</label>
                        <input type="time" name="start_time" class="form-control" value="{{ old('start_time', $task->start_time ? \Carbon\Carbon::parse($task->start_time)->format('H:i') : '') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">End Time</label>
                        <input type="time" name="end_time" class="form-control" value="{{ old('end_time', $task->end_time ? \Carbon\Carbon::parse($task->end_time)->format('H:i') : '') }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" rows="3" class="form-control">{{ old('description', $task->description) }}</textarea>
                    </div>
                </div>

                <div class="mt-4 d-flex justify-content-end gap-2">
                    <a href="{{ route('manager.tasks') }}" class="btn btn-light border">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection