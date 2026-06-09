@extends('layouts.master')

@section('title', 'Edit User — {{ $user->name }}')

@section('content')
<div class="container-fluid px-4">

    {{-- Page Header --}}
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <div class="d-flex align-items-center gap-3">
                    <div class="page-icon bg-primary-soft">
                        <i class="fas fa-user-edit fs-1 text-primary"></i>
                    </div>
                    <div>
                        <h1 class="page-title mb-1">Edit User</h1>
                        <p class="page-description text-muted mb-0">Update account details for {{ $user->name }}</p>
                    </div>
                </div>
            </div>
            <div class="col-auto">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">User Management</a></li>
                        <li class="breadcrumb-item active">Edit User</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <div class="row g-4">

        {{-- LEFT: Edit Form (9 cols) --}}
        <div class="col-md-9">
            <form action="{{ route('admin.users.update', $user->id) }}" method="POST" id="editUserForm">
                @csrf
                @method('PUT')

                {{-- Personal Info --}}
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h6 class="card-title mb-0 fw-semibold">
                            <i class="fas fa-id-card me-2 text-primary"></i>Personal Information
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label fw-medium">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       id="name" name="name"
                                       value="{{ old('name', $user->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label fw-medium">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                       id="email" name="email"
                                       value="{{ old('email', $user->email) }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="phone" class="form-label fw-medium">Phone Number</label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                       id="phone" name="phone"
                                       value="{{ old('phone', $user->phone) }}"
                                       placeholder="e.g. 0241234567">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="farm_name" class="form-label fw-medium">Farm Name</label>
                                <input type="text" class="form-control @error('farm_name') is-invalid @enderror"
                                       id="farm_name" name="farm_name"
                                       value="{{ old('farm_name', $user->farm_name) }}"
                                       placeholder="e.g. Main Farm">
                                @error('farm_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Role & Status --}}
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h6 class="card-title mb-0 fw-semibold">
                            <i class="fas fa-shield-alt me-2 text-warning"></i>Role & Access
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="role" class="form-label fw-medium">Role <span class="text-danger">*</span></label>
                                <select class="form-select @error('role') is-invalid @enderror"
                                        id="role" name="role" required>
                                    <option value="admin"
                                        {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>
                                        🛡️ Admin — Full System Control
                                    </option>
                                    <option value="manager"
                                        {{ old('role', $user->role) === 'manager' ? 'selected' : '' }}>
                                        👔 Farm Manager — Operational Management
                                    </option>
                                    <option value="worker"
                                        {{ old('role', $user->role) === 'worker' ? 'selected' : '' }}>
                                        👷 Farm Worker — Field Operations
                                    </option>
                                    <option value="veterinarian"
                                        {{ old('role', $user->role) === 'veterinarian' ? 'selected' : '' }}>
                                        🩺 Veterinarian — Health Management
                                    </option>
                                    <option value="accountant"
                                        {{ old('role', $user->role) === 'accountant' ? 'selected' : '' }}>
                                        🧾 Accountant — Financial Management
                                    </option>
                                </select>
                                @error('role')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-medium d-block">Account Status</label>
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox"
                                           id="is_active" name="is_active" value="1"
                                           {{ old('is_active', $user->is_active) ? 'checked' : '' }}
                                           style="width:48px; height:24px;">
                                    <label class="form-check-label ms-2 fw-medium" for="is_active">
                                        Active Account
                                    </label>
                                </div>
                                <small class="text-muted">Inactive users cannot log in.</small>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Password --}}
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h6 class="card-title mb-0 fw-semibold">
                            <i class="fas fa-lock me-2 text-info"></i>Change Password
                            <span class="text-muted fw-normal small ms-2">(leave blank to keep current)</span>
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="password" class="form-label fw-medium">New Password</label>
                                <div class="input-group">
                                    <input type="password"
                                           class="form-control @error('password') is-invalid @enderror"
                                           id="password" name="password"
                                           placeholder="Min. 8 characters">
                                    <button class="btn btn-outline-secondary toggle-pw" type="button"
                                            data-target="password">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="password_confirmation" class="form-label fw-medium">Confirm Password</label>
                                <div class="input-group">
                                    <input type="password"
                                           class="form-control"
                                           id="password_confirmation"
                                           name="password_confirmation"
                                           placeholder="Repeat new password">
                                    <button class="btn btn-outline-secondary toggle-pw" type="button"
                                            data-target="password_confirmation">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Form Actions --}}
                <div class="d-flex gap-3 justify-content-end">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary px-4">
                        <i class="fas fa-times me-2"></i>Cancel
                    </a>
                    <button type="button" class="btn btn-primary px-5" id="saveBtn">
                        <i class="fas fa-save me-2"></i>Save Changes
                    </button>
                </div>

            </form>
        </div>

        {{-- RIGHT: Avatar / Quick Info (3 cols) --}}
        <div class="col-md-3">
            <div class="card shadow-sm border-0 text-center p-4">
                @php
                    $roleColors = [
                        'admin'        => 'danger',
                        'manager'      => 'warning',
                        'worker'       => 'success',
                        'veterinarian' => 'info',
                        'accountant'   => 'primary',
                    ];
                    $rc = $roleColors[$user->role] ?? 'secondary';
                @endphp
                <div class="avatar-circle-lg bg-{{ $rc }}-soft text-{{ $rc }} mx-auto mb-3">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                </div>
                <h5 class="fw-semibold mb-1">{{ $user->name }}</h5>
                <p class="text-muted small mb-3">{{ $user->email }}</p>
                <span class="badge bg-{{ $rc }}-soft text-{{ $rc }} px-3 py-2 mb-3 d-inline-block">
                    {{ ucfirst($user->role) }}
                </span>
                <hr>
                <div class="text-start">
                    <div class="d-flex justify-content-between py-1">
                        <span class="text-muted small">Status</span>
                        <span class="badge {{ $user->is_active ? 'bg-success-soft text-success' : 'bg-secondary-soft text-secondary' }}">
                            {{ $user->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    <div class="d-flex justify-content-between py-1">
                        <span class="text-muted small">Joined</span>
                        <span class="small">{{ $user->created_at->format('M d, Y') }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-1">
                        <span class="text-muted small">Last Login</span>
                        <span class="small">{{ $user->last_login_at ? $user->last_login_at->format('M d, H:i') : 'Never' }}</span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('styles')
<style>
    .avatar-circle-lg {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        font-weight: 700;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // ── Save confirmation ─────────────────────────────────────────────────────
    document.getElementById('saveBtn').addEventListener('click', function () {
        Swal.fire({
            title: 'Save Changes?',
            html: 'Update account details for <strong>{{ $user->name }}</strong>?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3b82f6',
            cancelButtonColor:  '#6b7280',
            confirmButtonText: '<i class="fas fa-save me-1"></i> Yes, save',
            cancelButtonText:  '<i class="fas fa-times me-1"></i> Cancel',
            customClass: { popup: 'rounded-3' },
        }).then(result => {
            if (result.isConfirmed) {
                document.getElementById('editUserForm').submit();
            }
        });
    });

    // ── Password visibility toggle ────────────────────────────────────────────
    document.querySelectorAll('.toggle-pw').forEach(btn => {
        btn.addEventListener('click', function () {
            const input = document.getElementById(this.dataset.target);
            const icon  = this.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        });
    });
</script>
@endpush