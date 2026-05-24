@extends('layouts.master')

@section('title', 'Switch User Account - Poultry Management System')

@section('content')
<div class="container-fluid">
    
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Switch User Account</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Switch User</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>     
    <!-- end page title --> 

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="mdi mdi-alert-circle me-2"></i>
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="mdi mdi-check-circle me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if($users->count() > 0)
                       <!-- Summary Statistics Cards - Clean Floating Style -->
<div class="row mb-4">

    <!-- Total Users -->
    <div class="col-md-3">
        <div class="modern-stat-card">
            <div class="stat-icon bg-primary-soft text-primary">
                <i class="mdi mdi-account-group"></i>
            </div>

            <div class="stat-content">
                <p class="stat-label">TOTAL USERS</p>
                <h2 class="stat-value">{{ $users->count() }}</h2>
                <span class="stat-subtitle">System users</span>
            </div>
        </div>
    </div>

    <!-- Online Users -->
    <div class="col-md-3">
        <div class="modern-stat-card">
            <div class="stat-icon bg-success-soft text-success">
                <i class="mdi mdi-account-check"></i>
            </div>

            <div class="stat-content">
                <p class="stat-label">CURRENTLY ONLINE</p>
                <h2 class="stat-value">
                    {{ $users->filter(function($user) { return $user->isOnline(); })->count() }}
                </h2>
                <span class="stat-subtitle">Active now</span>
            </div>
        </div>
    </div>

    <!-- Active Today -->
    <div class="col-md-3">
        <div class="modern-stat-card">
            <div class="stat-icon bg-danger-soft text-danger">
                <i class="mdi mdi-calendar-today"></i>
            </div>

            <div class="stat-content">
                <p class="stat-label">ACTIVE TODAY</p>
                <h2 class="stat-value">
                    {{ $users->filter(function($user) { return $user->last_seen_at && $user->last_seen_at->isToday(); })->count() }}
                </h2>
                <span class="stat-subtitle">Today's activity</span>
            </div>
        </div>
    </div>

    <!-- Workers -->
    <div class="col-md-3">
        <div class="modern-stat-card">
            <div class="stat-icon bg-info-soft text-info">
                <i class="mdi mdi-account-hard-hat"></i>
            </div>

            <div class="stat-content">
                <p class="stat-label">WORKERS</p>
                <h2 class="stat-value">
                    {{ $users->where('role', 'worker')->count() }}
                </h2>
                <span class="stat-subtitle">Worker accounts</span>
            </div>
        </div>
    </div>

</div>

                        <!-- Users Table -->
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>User Details</th>
                                        <th>Role</th>
                                        <th>Last Login</th>
                                        <th>Last Seen</th>
                                        <th>Activity Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($users as $user)
                                        @php
                                            $status = $user->getOnlineStatus();
                                            $lastLogin = $user->last_login_at ?? $user->created_at;
                                            $lastSeen = $user->last_seen_at;
                                        @endphp
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="me-2">
                                                        @if($user->avatar)
                                                            <img src="{{ $user->avatar_url }}" class="rounded-circle" width="40" height="40">
                                                        @else
                                                            <img src="{{ asset('assets/images/genericavatarimage.jpg') }}" class="rounded-circle" width="40" height="40">
                                                        @endif
                                                    </div>
                                                    <div>
                                                        <strong>{{ $user->name }}</strong><br>
                                                        <small class="text-muted">{{ $user->email }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $user->role === 'admin' ? 'danger' : ($user->role === 'manager' ? 'warning' : ($user->role === 'worker' ? 'info' : 'secondary')) }}">
                                                    {{ ucfirst($user->role) }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($lastLogin)
                                                    <small class="text-muted">{{ $lastLogin->format('M d, H:i') }}</small>
                                                @else
                                                    <span class="text-muted">Never</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($lastSeen)
                                                    <small class="text-muted">{{ $lastSeen->format('M d, H:i') }}</small>
                                                @else
                                                    <span class="text-muted">Never</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge {{ $status['class'] }}">
                                                    {{ $status['text'] }}
                                                </span>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-primary switch-user-btn" 
                                                    data-user-name="{{ $user->name }}" 
                                                    data-user-id="{{ $user->id }}">
                                                    <i class="mdi mdi-account-switch"></i> Switch
                                                </button>
                                                <form id="switch-form-{{ $user->id }}" action="{{ route('user.switch.to', $user->id) }}" method="POST" class="d-none">
                                                    @csrf
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Weekly Summary - Modern Card Design -->
                        @if(Auth::user()->isManager())
                            <div class="row mt-4">
                                <div class="col-md-6">
                                    <div class="card border-0 shadow-sm h-100">
                                        <div class="card-header bg-white border-0 pt-4">
                                            <h5 class="card-title mb-0">
                                                <i class="mdi mdi-check-circle text-success me-2"></i>
                                                Active Workers (Last 7 days)
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            @php
                                                $activeWorkers = $users->filter(function($user) {
                                                    return $user->role === 'worker' && 
                                                           $user->last_seen_at && 
                                                           $user->last_seen_at->diffInDays(now()) <= 7;
                                                });
                                            @endphp
                                            @forelse($activeWorkers as $worker)
                                                <div class="d-flex align-items-center mb-3">
                                                    <div class="flex-shrink-0">
                                                        <div class="avatar-xs">
                                                            <div class="avatar-title rounded-circle bg-success bg-soft text-success">
                                                                <i class="mdi mdi-account"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        <h6 class="mb-0">{{ $worker->name }}</h6>
                                                        <small class="text-muted">Last active {{ $worker->last_seen_at->diffForHumans() }}</small>
                                                    </div>
                                                    <div>
                                                        <span class="badge bg-success">Active</span>
                                                    </div>
                                                </div>
                                            @empty
                                                <div class="text-center py-4">
                                                    <i class="mdi mdi-account-off mdi-48px text-muted mb-2"></i>
                                                    <p class="text-muted mb-0">No active workers this week</p>
                                                </div>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card border-0 shadow-sm h-100">
                                        <div class="card-header bg-white border-0 pt-4">
                                            <h5 class="card-title mb-0">
                                                <i class="mdi mdi-alert-circle text-warning me-2"></i>
                                                Inactive Workers (>7 days)
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            @php
                                                $inactiveWorkers = $users->filter(function($user) {
                                                    return $user->role === 'worker' && 
                                                           (!$user->last_seen_at || $user->last_seen_at->diffInDays(now()) > 7);
                                                });
                                            @endphp
                                            @forelse($inactiveWorkers as $worker)
                                                <div class="d-flex align-items-center mb-3">
                                                    <div class="flex-shrink-0">
                                                        <div class="avatar-xs">
                                                            <div class="avatar-title rounded-circle bg-warning bg-soft text-warning">
                                                                <i class="mdi mdi-clock"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        <h6 class="mb-0">{{ $worker->name }}</h6>
                                                        <small class="text-muted">
                                                            @if($worker->last_seen_at)
                                                                Inactive for {{ $worker->last_seen_at->diffInDays(now()) }} days
                                                            @else
                                                                Never logged in
                                                            @endif
                                                        </small>
                                                    </div>
                                                    <div>
                                                        <span class="badge bg-warning">Inactive</span>
                                                    </div>
                                                </div>
                                            @empty
                                                <div class="text-center py-4">
                                                    <i class="mdi mdi-check-all mdi-48px text-success mb-2"></i>
                                                    <p class="text-success mb-0">All workers are active! ✓</p>
                                                </div>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                        
                    @else
                        <div class="alert alert-info" role="alert">
                            <i class="mdi mdi-information-outline me-2"></i> 
                            No users available to switch to.
                        </div>
                    @endif

                    <!-- Back to Dashboard -->
                    <div class="mt-4">
                        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                            <i class="mdi mdi-arrow-left me-1"></i>Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Auto-refresh the page every 2 minutes to update online status
    setTimeout(function() { 
        location.reload(); 
    }, 120000);
    
    // SweetAlert for Switch User
    document.querySelectorAll('.switch-user-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            const userName = this.getAttribute('data-user-name');
            const userId = this.getAttribute('data-user-id');
            
            Swal.fire({
                title: 'Switch User Account',
                html: `Are you sure you want to switch to <strong>${userName}</strong>?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: '<i class="mdi mdi-account-switch me-1"></i> Yes, switch now!',
                cancelButtonText: '<i class="mdi mdi-close me-1"></i> Cancel',
                background: '#fff',
                backdrop: true,
                allowOutsideClick: false,
                customClass: {
                    popup: 'rounded-3',
                    title: 'fs-4 fw-bold',
                    confirmButton: 'btn btn-primary px-4',
                    cancelButton: 'btn btn-danger px-4 ms-2'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading state
                    Swal.fire({
                        title: 'Switching...',
                        html: `Switching to ${userName}...`,
                        icon: 'info',
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    // Submit the form
                    document.getElementById(`switch-form-${userId}`).submit();
                }
            });
        });
    });
</script>
@endpush