@extends('layouts.master')

@section('title', 'My Attendance')

@section('content')
<div class="container-fluid px-4">
    <!-- Page Header -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <div class="d-flex align-items-center gap-3">
                    <div class="page-icon bg-info-soft">
                        <i class="fas fa-clock fs-1 text-info"></i>
                    </div>
                    <div>
                        <h1 class="page-title mb-1">My Attendance</h1>
                        <p class="page-description text-muted mb-0">Track your work hours and attendance records</p>
                    </div>
                </div>
            </div>
            <div class="col-auto">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">My Attendance</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <!-- Clock In/Out Card -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="clock-card text-center">
                <div class="clock-time" id="currentTime">--:--:--</div>
                <div class="clock-date" id="currentDate">{{ now()->format('l, F j, Y') }}</div>
                <div class="clock-status mt-3">
                    <span class="badge" id="clockStatus">
                        @if($isClockedIn)
                            <i class="fas fa-play-circle me-1"></i> Working
                        @else
                            <i class="fas fa-stop-circle me-1"></i> Not Clocked In
                        @endif
                    </span>
                </div>
                <div class="mt-4">
                    <button class="btn btn-success btn-lg px-5 {{ $isClockedIn ? 'd-none' : '' }}" id="clockInBtn" onclick="clockIn()">
                        <i class="fas fa-sign-in-alt me-2"></i>Clock In
                    </button>
                    <button class="btn btn-danger btn-lg px-5 {{ $isClockedIn ? '' : 'd-none' }}" id="clockOutBtn" onclick="clockOut()">
                        <i class="fas fa-sign-out-alt me-2"></i>Clock Out
                    </button>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="row g-4">
                <div class="col-6">
                    <div class="stat-card text-center">
                        <div class="stat-card-body">
                            <div class="stat-card-icon bg-primary-soft d-inline-flex p-3 rounded-circle mb-2">
                                <i class="fas fa-calendar-day text-primary fa-2x"></i>
                            </div>
                            <h6 class="text-muted mb-1">Days Worked (This Month)</h6>
                            <h3 class="mb-0" id="daysWorked">{{ $stats['days_worked'] }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="stat-card text-center">
                        <div class="stat-card-body">
                            <div class="stat-card-icon bg-success-soft d-inline-flex p-3 rounded-circle mb-2">
                                <i class="fas fa-hourglass-half text-success fa-2x"></i>
                            </div>
                            <h6 class="text-muted mb-1">Total Hours (This Month)</h6>
                            <h3 class="mb-0" id="totalHours">{{ number_format($stats['total_hours'], 1) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Stats Row -->
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="stat-card text-center">
                <div class="stat-card-body">
                    <div class="stat-card-icon bg-success-soft d-inline-flex p-3 rounded-circle mb-2">
                        <i class="fas fa-check-circle text-success fa-2x"></i>
                    </div>
                    <h6 class="text-muted mb-1">On-Time Days</h6>
                    <h3 class="mb-0">{{ $stats['on_time_days'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="stat-card text-center">
                <div class="stat-card-body">
                    <div class="stat-card-icon bg-warning-soft d-inline-flex p-3 rounded-circle mb-2">
                        <i class="fas fa-clock text-warning fa-2x"></i>
                    </div>
                    <h6 class="text-muted mb-1">Late Arrivals</h6>
                    <h3 class="mb-0">{{ $stats['late_days'] }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance History -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-0 py-3">
            <h5 class="card-title mb-0 fw-semibold">
                <i class="fas fa-history me-2 text-primary"></i>Attendance History (Last 30 Days)
            </h5>
        </div>
        <div class="card-body">
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
                    <tbody id="attendanceHistory">
                        @forelse($history as $record)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($record->date)->format('M d, Y') }}</td>
                            <td>{{ $record->clock_in ? \Carbon\Carbon::parse($record->clock_in)->format('h:i A') : '-' }}</td>
                            <td>{{ $record->clock_out ? \Carbon\Carbon::parse($record->clock_out)->format('h:i A') : 'Not clocked out' }}</td>
                            <td>{{ $record->hours_worked ? number_format($record->hours_worked, 1) : '-' }}</td>
                            <td>
                                <span class="badge {{ $record->status === 'present' ? 'bg-success-soft text-success' : ($record->status === 'late' ? 'bg-warning-soft text-warning' : 'bg-secondary-soft text-secondary') }}">
                                    {{ ucfirst($record->status) }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">No attendance records found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    let clockedIn = {{ $isClockedIn ? 'true' : 'false' }};
    
    function clockIn() {
        fetch('{{ route("worker.clock-in") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                clockedIn = true;
                document.getElementById('clockInBtn').classList.add('d-none');
                document.getElementById('clockOutBtn').classList.remove('d-none');
                document.getElementById('clockStatus').innerHTML = '<i class="fas fa-play-circle me-1"></i> Working';
                document.getElementById('clockStatus').className = 'badge bg-success';
                
                Swal.fire({
                    icon: 'success',
                    title: 'Clocked In!',
                    text: `You clocked in at ${data.time}`,
                    timer: 2000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to clock in. Please try again.'
            });
        });
    }
    
    function clockOut() {
        fetch('{{ route("worker.clock-out") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                clockedIn = false;
                document.getElementById('clockInBtn').classList.remove('d-none');
                document.getElementById('clockOutBtn').classList.add('d-none');
                document.getElementById('clockStatus').innerHTML = '<i class="fas fa-stop-circle me-1"></i> Not Clocked In';
                document.getElementById('clockStatus').className = 'badge bg-secondary';
                
                Swal.fire({
                    icon: 'success',
                    title: 'Clocked Out!',
                    text: `You worked ${data.hours_worked} hours today. Great job!`,
                    timer: 3000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
                
                // Refresh the page to update stats and history
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to clock out. Please try again.'
            });
        });
    }
    
    // Update clock display
    function updateClock() {
        const now = new Date();
        document.getElementById('currentTime').textContent = now.toLocaleTimeString();
    }
    setInterval(updateClock, 1000);
    updateClock();
</script>
@endpush

@push('styles')
<style>
    .clock-card {
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        border-radius: 20px;
        padding: 2rem;
        color: white;
        height: 100%;
        box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.3);
    }
    .clock-time {
        font-size: 3rem;
        font-weight: 700;
        font-family: monospace;
    }
    .clock-date {
        font-size: 1rem;
        opacity: 0.9;
    }
    .clock-card .badge {
        padding: 0.5rem 1rem;
        font-size: 0.9rem;
    }
</style>
@endpush

@endsection