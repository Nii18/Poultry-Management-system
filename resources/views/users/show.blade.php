@extends('layouts.master')

@section('title', 'User Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('users.index') }}">User Management</a></li>
                        <li class="breadcrumb-item active">User Details</li>
                    </ol>
                </div>
                <h4 class="page-title">
                    <i class="mdi mdi-account-details me-1"></i>
                    User Details: {{ $user->name }}
                </h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-4 col-lg-5">
            <div class="card text-center">
                <div class="card-body">
                    <div class="mb-3">
                        <div class="avatar-lg mx-auto">
                            <div class="avatar-title bg-{{ 
                                $user->role === 'admin' ? 'danger' : 
                                ($user->role === 'manager' ? 'warning' : 
                                ($user->role === 'veterinarian' ? 'info' : 
                                ($user->role === 'accountant' ? 'primary' : 'success'))) 
                            }} rounded-circle text-white font-size-24">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                        </div>
                    </div>
                    <h4 class="mb-1">{{ $user->name }}</h4>
                    <p class="text-muted">{{ $user->email }}</p>
                    <span class="badge bg-{{ 
                        $user->role === 'admin' ? 'danger' : 
                        ($user->role === 'manager' ? 'warning' : 
                        ($user->role === 'veterinarian' ? 'info' : 
                        ($user->role === 'accountant' ? 'primary' : 'success'))) 
                    }} p-2">
                        {{ ucfirst($user->role) }}
                    </span>
                    
                    <div class="row mt-4">
                        <div class="col-6">
                            <div class="border rounded p-2">
                                <small class="text-muted">Status</small>
                                <div>
                                    @if($user->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-2">
                                <small class="text-muted">User ID</small>
                                <div>
                                    <strong>#{{ $user->id }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-8 col-lg-7">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="mdi mdi-information-outline me-1"></i> User Information
                        </h5>
                        <div class="btn-group">
                            <a href="{{ route('users.edit', $user->id) }}" class="btn btn-warning btn-sm">
                                <i class="mdi mdi-pencil"></i> Edit
                            </a>
                            @if(auth()->id() !== $user->id)
                                <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                    <i class="mdi mdi-delete"></i> Delete
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3">
                                <label class="text-muted mb-1">
                                    <i class="mdi mdi-account me-1"></i> Full Name
                                </label>
                                <h6 class="mb-0">{{ $user->name }}</h6>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3">
                                <label class="text-muted mb-1">
                                    <i class="mdi mdi-email me-1"></i> Email Address
                                </label>
                                <h6 class="mb-0">{{ $user->email }}</h6>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3">
                                <label class="text-muted mb-1">
                                    <i class="mdi mdi-phone me-1"></i> Phone Number
                                </label>
                                <h6 class="mb-0">{{ $user->phone ?? 'Not provided' }}</h6>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3">
                                <label class="text-muted mb-1">
                                    <i class="mdi mdi-farm me-1"></i> Farm Name
                                </label>
                                <h6 class="mb-0">{{ $user->farm_name ?? 'Not provided' }}</h6>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3">
                                <label class="text-muted mb-1">
                                    <i class="mdi mdi-calendar me-1"></i> Account Created
                                </label>
                                <h6 class="mb-0">{{ $user->created_at->format('F d, Y H:i:s') }}</h6>
                                <small class="text-muted">{{ $user->created_at->diffForHumans() }}</small>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3">
                                <label class="text-muted mb-1">
                                    <i class="mdi mdi-clock me-1"></i> Last Updated
                                </label>
                                <h6 class="mb-0">{{ $user->updated_at->format('F d, Y H:i:s') }}</h6>
                                <small class="text-muted">{{ $user->updated_at->diffForHumans() }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Change Password Card -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="mdi mdi-key me-1"></i> Change Password
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('users.update-password', $user->id) }}">
                        @csrf
                        @method('POST')
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">New Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Confirm Password</label>
                                <input type="password" name="password_confirmation" class="form-control" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Update Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
@if(auth()->id() !== $user->id)
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title text-white">Delete User</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <i class="mdi mdi-alert-circle-outline text-danger" style="font-size: 48px;"></i>
                <h5 class="mt-2">Are you sure?</h5>
                <p>You are about to delete <strong>{{ $user->name }}</strong>.</p>
                @if($user->role === 'admin')
                    <div class="alert alert-warning">
                        <i class="mdi mdi-alert me-1"></i>
                        Warning: This is an admin user. Deleting may affect system access.
                    </div>
                @endif
                <p class="text-danger mb-0">This action cannot be undone.</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" action="{{ route('users.destroy', $user->id) }}" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete User</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif

@push('styles')
<style>
    .avatar-lg {
        width: 80px;
        height: 80px;
        line-height: 80px;
        font-size: 32px;
    }
    .avatar-title {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>
@endpush
@endsection