@extends('layouts.master')

@section('title', 'Attendance Reports')

@section('content')
<div class="container-fluid px-4">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <div class="d-flex align-items-center gap-3">
                    <div class="page-icon bg-info-soft">
                        <i class="fas fa-user-clock fs-1 text-info"></i>
                    </div>
                    <div>
                        <h1 class="page-title mb-1">Attendance Reports</h1>
                        <p class="page-description text-muted mb-0">Monitor worker clock-in consistency</p>
                    </div>
                </div>
            </div>
            <div class="col-auto">
                <a href="{{ route('manager.tasks') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-tasks me-1"></i> Task Management
                </a>
            </div>
        </div>
    </div>

    <!-- Consistency Overview -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white border-0 py-3">
            <h5 class="card-title mb-0 fw-semibold">
                <i class="fas fa-chart-bar me-2 text-warning"></i>Consistency Overview (Last 30 Days)
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Worker</th>
                            <th>Total Shifts</th>
                            <th>Auto Clock-Outs</th>
                            <th>Consistency</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($consistencyOverview as $row)
                        <tr>
                            <td>{{ $row['name'] }}</td>
                            <td>{{ $row['total_shifts'] }}</td>
                            <td>
                                @if($row['auto_closed_count'] > 0)
                                    <span class="badge bg-warning-soft text-warning">
                                        <i class="fas fa-exclamation-triangle me-1"></i>{{ $row['auto_closed_count'] }}
                                    </span>
                                @else
                                    <span class="text-muted">0</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge {{ $row['consistency_rate'] >= 90 ? 'bg-success-soft text-success' : ($row['consistency_rate'] >= 70 ? 'bg-warning-soft text-warning' : 'bg-danger-soft text-danger') }}">
                                        {{ $row['consistency_rate'] }}%
                                    </span>
                                    <div class="progress flex-grow-1" style="height: 6px; max-width: 100px;">
                                        <div class="progress-bar {{ $row['consistency_rate'] >= 90 ? 'bg-success' : ($row['consistency_rate'] >= 70 ? 'bg-warning' : 'bg-danger') }}"
                                             style="width: {{ $row['consistency_rate'] }}%"></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <a href="{{ route('manager.attendance', ['worker_id' => $row['worker_id']]) }}" class="btn btn-sm btn-outline-primary">
                                    View History
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No active workers found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Individual Worker History -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-0 py-3">
            <form method="GET" action="{{ route('manager.attendance') }}" class="row g-2 align-items-center">
                <div class="col-auto">
                    <label class="form-label fw-semibold mb-0">Worker:</label>
                </div>
                <div class="col-auto">
                    <select name="worker_id" class="form-select" onchange="this.form.submit()">
                        <option value="">Select a worker</option>
                        @foreach($workers as $worker)
                            <option value="{{ $worker->id }}" {{ (string)$selectedWorker === (string)$worker->id ? 'selected' : '' }}>
                                {{ $worker->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>
        <div class="card-body">
            @if(!$selectedWorker)
                <div class="text-center text-muted py-4">
                    <i class="fas fa-user-clock fa-2x mb-2"></i>
                    <p>Select a worker above to view their attendance history.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Clock In</th>
                                <th>Clock Out</th>
                                <th>Hours Worked</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($attendance as $record)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($record->date)->format('M d, Y') }}</td>
                                <td>{{ $record->clock_in ? \Carbon\Carbon::createFromFormat('H:i:s', $record->clock_in)->format('h:i A') : '-' }}</td>
                                <td>
                                    {{ $record->clock_out ? \Carbon\Carbon::createFromFormat('H:i:s', $record->clock_out)->format('h:i A') : 'Not clocked out' }}
                                    @if($record->is_auto_closed)
                                        <span class="badge bg-warning-soft text-warning ms-1" title="{{ $record->notes }}">
                                            <i class="fas fa-robot me-1"></i>Auto-closed
                                        </span>
                                    @endif
                                </td>
                                <td>{{ $record->hours_worked ? number_format($record->hours_worked, 1) : '-' }}</td>
                                <td>
                                    <span class="badge {{ $record->status === 'present' ? 'bg-success-soft text-success' : ($record->status === 'late' ? 'bg-warning-soft text-warning' : 'bg-secondary-soft text-secondary') }}">
                                        {{ ucfirst($record->status) }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">No attendance records found for this worker.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection