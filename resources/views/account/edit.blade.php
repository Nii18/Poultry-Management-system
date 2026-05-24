@extends('layouts.master')

@section('title', 'Manage Account - Medicine Expiry System')

@section('content')
<div class="container-fluid">
    
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Manage Account</h4>
            </div>
        </div>
    </div>     
    <!-- end page title --> 

    <div class="row">
        <!-- Left Column - Account Information -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body">
                    <h4 class="header-title mb-3">Account Information</h4>
                    
            <!-- Profile Picture Update -->
            <div class="mb-4">
                <h5 class="mb-3">Profile Picture</h5>
                <div class="d-flex align-items-center">
                    <div class="me-4">
                        @if(Auth::user()->avatar)
                            <img src="{{ Auth::user()->avatar_url }}" alt="Current Avatar" class="rounded-circle" width="80" height="80">
                        @else
                            <!-- Generic avatar image -->
                            <img src="{{ asset('assets/images/genericavatarimage.jpg') }}" alt="Default Avatar" class="rounded-circle" width="80" height="80">
                        @endif
                    </div>
                    <div class="flex-grow-1">
                        <form action="{{ route('account.avatar.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label for="avatar" class="form-label">Change Profile Picture</label>
                                <input type="file" class="form-control" id="avatar" name="avatar" accept="image/*">
                                @error('avatar')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="mdi mdi-upload me-1"></i>Upload Picture
                            </button>
                            
                            <!-- Remove Avatar Button (only show if user has an avatar) -->
                            @if(Auth::user()->avatar)
                                <button type="button" class="btn btn-outline-danger btn-sm ms-1" data-bs-toggle="modal" data-bs-target="#deleteAvatarModal">
                                    <i class="mdi mdi-delete me-1"></i>Remove Picture
                                </button>
                            @endif
                        </form>
                    </div>
                </div>
            </div>

            <!-- Delete Avatar Modal -->
            @if(Auth::user()->avatar)
            <div class="modal fade" id="deleteAvatarModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Remove Profile Picture</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p>Are you sure you want to remove your profile picture? The generic avatar will be shown instead.</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <form action="{{ route('account.avatar.delete') }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">Remove Picture</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            @endif

                    <!-- Account Information Form -->
                    <form action="{{ route('account.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Full Name</label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="{{ old('name', Auth::user()->name) }}" required>
                                    @error('name')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="text" class="form-control" id="phone" name="phone" 
                                           value="{{ old('phone', Auth::user()->phone) }}">
                                    @error('phone')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="{{ old('email', Auth::user()->email) }}" required>
                            @error('email')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <textarea class="form-control" id="address" name="address" rows="3">{{ old('address', Auth::user()->address) }}</textarea>
                            @error('address')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <input type="text" class="form-control" value="{{ Auth::user()->role }}" readonly style="background-color: #f8f9fa;">
                            <small class="text-muted">Role cannot be changed from this page.</small>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="mdi mdi-content-save me-1"></i>Save Changes
                            </button>
                            <a href="{{ route('account.password.edit') }}" class="btn btn-outline-secondary ms-2">
                                <i class="mdi mdi-lock-reset me-1"></i>Change Password
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right Column - Current Information & Preview -->
        <div class="col-lg-6">
            <!-- Current Information Card -->
            <div class="card">
                <div class="card-body">
                    <h4 class="header-title mb-3">Current Information</h4>
                    
                    <div class="text-center mb-4">
                        @if(Auth::user()->avatar)
                            <img src="{{ Auth::user()->avatar_url }}" alt="Current Avatar" class="rounded-circle mb-3" width="100" height="100">
                        @else
                             <!-- Generic avatar image -->
                             <img src="{{ asset('assets/images/genericavatarimage.jpg') }}" alt="Default Avatar" class="rounded-circle" width="80" height="80">
                        @endif
                        <h5>{{ Auth::user()->name }}</h5>
                        <span class="badge bg-{{ Auth::user()->role === 'super_admin' ? 'danger' : (Auth::user()->role === 'admin' ? 'warning' : 'info') }}">
                            {{ ucfirst(str_replace('_', ' ', Auth::user()->role)) }}
                        </span>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <tbody>
                                <tr>
                                    <td><strong>Email:</strong></td>
                                    <td>{{ Auth::user()->email }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Phone:</strong></td>
                                    <td>{{ Auth::user()->phone ?: 'Not set' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Address:</strong></td>
                                    <td>{{ Auth::user()->address ?: 'Not set' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Account Created:</strong></td>
                                    <td>{{ Auth::user()->created_at->format('M j, Y') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Last Updated:</strong></td>
                                    <td>{{ Auth::user()->updated_at->format('M j, Y g:i A') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Quick Actions Card -->
            <div class="card">
                <div class="card-body">
                    <h4 class="header-title mb-3">Quick Actions</h4>
                    
                    <div class="d-grid gap-2">
                        <a href="{{ route('account.password.edit') }}" class="btn btn-outline-primary">
                            <i class="mdi mdi-lock-reset me-1"></i>Change Password
                        </a>

                                <!-- User Management (Admin/Super Admin Only) -->
                    @if(Auth::user()->isAdmin())
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-success">
                        <i class="mdi mdi-account-multiple me-1"></i>User Management
                    </a>
                    @endif
                        
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