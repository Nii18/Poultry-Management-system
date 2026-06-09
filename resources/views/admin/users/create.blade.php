@extends('layouts.master')

@section('title', 'Add New User')

@section('content')
<div class="container-fluid px-4">

    {{-- Page Header --}}
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <div class="d-flex align-items-center gap-3">
                    <div class="page-icon bg-success-soft">
                        <i class="fas fa-user-plus fs-1 text-success"></i>
                    </div>
                    <div>
                        <h1 class="page-title mb-1">Add New User</h1>
                        <p class="page-description text-muted mb-0">Create a new system account and assign a role</p>
                    </div>
                </div>
            </div>
            <div class="col-auto">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">User Management</a></li>
                        <li class="breadcrumb-item active">Add User</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <div class="row g-4">

        {{-- LEFT: Form (9 cols) --}}
        <div class="col-md-9">
            <form action="{{ route('admin.users.store') }}" method="POST" id="createUserForm">
                @csrf

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
                                <label for="name" class="form-label fw-medium">
                                    Full Name <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       class="form-control @error('name') is-invalid @enderror"
                                       id="name" name="name"
                                       value="{{ old('name') }}"
                                       placeholder="e.g. Kwame Mensah"
                                       required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="email" class="form-label fw-medium">
                                    Email Address <span class="text-danger">*</span>
                                </label>
                                <input type="email"
                                       class="form-control @error('email') is-invalid @enderror"
                                       id="email" name="email"
                                       value="{{ old('email') }}"
                                       placeholder="e.g. kwame@mainfarm.com"
                                       required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="phone" class="form-label fw-medium">Phone Number</label>
                                <input type="text"
                                       class="form-control @error('phone') is-invalid @enderror"
                                       id="phone" name="phone"
                                       value="{{ old('phone') }}"
                                       placeholder="e.g. 0241234567">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="farm_name" class="form-label fw-medium">Farm Name</label>
                                <input type="text"
                                       class="form-control @error('farm_name') is-invalid @enderror"
                                       id="farm_name" name="farm_name"
                                       value="{{ old('farm_name') }}"
                                       placeholder="e.g. Main Farm">
                                @error('farm_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Role --}}
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h6 class="card-title mb-0 fw-semibold">
                            <i class="fas fa-shield-alt me-2 text-warning"></i>Role Assignment
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @php
                                $roles = [
                                    'admin'        => ['emoji' => '🛡️', 'label' => 'Admin',        'sub' => 'Full System Control'],
                                    'manager'      => ['emoji' => '👔', 'label' => 'Farm Manager',  'sub' => 'Operational Management'],
                                    'worker'       => ['emoji' => '👷', 'label' => 'Farm Worker',   'sub' => 'Field Operations'],
                                    'veterinarian' => ['emoji' => '🩺', 'label' => 'Veterinarian',  'sub' => 'Health Management'],
                                    'accountant'   => ['emoji' => '🧾', 'label' => 'Accountant',    'sub' => 'Financial Management'],
                                ];
                            @endphp

                            @foreach($roles as $value => $meta)
                            <div class="col-md-4">
                                <label class="role-card {{ old('role') === $value ? 'selected' : '' }}"
                                       for="role_{{ $value }}">
                                    <input type="radio" name="role" id="role_{{ $value }}"
                                           value="{{ $value }}"
                                           {{ old('role') === $value ? 'checked' : '' }}
                                           required>
                                    <div class="role-card-body">
                                        <span class="role-emoji">{{ $meta['emoji'] }}</span>
                                        <div class="fw-semibold">{{ $meta['label'] }}</div>
                                        <small class="text-muted">{{ $meta['sub'] }}</small>
                                    </div>
                                </label>
                            </div>
                            @endforeach

                            @error('role')
                                <div class="col-12">
                                    <div class="text-danger small">
                                        <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
                                    </div>
                                </div>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Password --}}
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h6 class="card-title mb-0 fw-semibold">
                            <i class="fas fa-lock me-2 text-info"></i>Set Password
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="password" class="form-label fw-medium">
                                    Password <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="password"
                                           class="form-control @error('password') is-invalid @enderror"
                                           id="password" name="password"
                                           placeholder="Min. 8 characters" required>
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
                                <label for="password_confirmation" class="form-label fw-medium">
                                    Confirm Password <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="password"
                                           class="form-control"
                                           id="password_confirmation"
                                           name="password_confirmation"
                                           placeholder="Repeat password" required>
                                    <button class="btn btn-outline-secondary toggle-pw" type="button"
                                            data-target="password_confirmation">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="d-flex gap-3 justify-content-end">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary px-4">
                        <i class="fas fa-times me-2"></i>Cancel
                    </a>
                    <button type="button" class="btn btn-success px-5" id="createBtn">
                        <i class="fas fa-user-plus me-2"></i>Create User
                    </button>
                </div>

            </form>
        </div>

        {{-- RIGHT: Role Guide (3 cols) --}}
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="card-title mb-0 fw-semibold">
                        <i class="fas fa-info-circle me-2 text-info"></i>Role Guide
                    </h6>
                </div>
                <div class="card-body p-0">
                    @php
                        $roleGuide = [
                            ['role' => 'Admin',        'icon' => 'fa-shield-alt',  'color' => 'danger',  'desc' => 'Full system access and user management.'],
                            ['role' => 'Manager',      'icon' => 'fa-user-tie',    'color' => 'warning', 'desc' => 'Manages farm operations and workers.'],
                            ['role' => 'Worker',       'icon' => 'fa-hard-hat',    'color' => 'success', 'desc' => 'Field tasks, attendance, and daily logs.'],
                            ['role' => 'Veterinarian', 'icon' => 'fa-stethoscope', 'color' => 'info',    'desc' => 'Bird health records and treatments.'],
                            ['role' => 'Accountant',   'icon' => 'fa-calculator',  'color' => 'primary', 'desc' => 'Financial records and expense tracking.'],
                        ];
                    @endphp
                    @foreach($roleGuide as $r)
                    <div class="d-flex align-items-start gap-3 px-3 py-2 border-bottom">
                        <div class="flex-shrink-0 mt-1">
                            <span class="bg-{{ $r['color'] }}-soft text-{{ $r['color'] }} p-2 rounded-circle d-inline-flex">
                                <i class="fas {{ $r['icon'] }}" style="width:14px;text-align:center;"></i>
                            </span>
                        </div>
                        <div>
                            <div class="fw-semibold small">{{ $r['role'] }}</div>
                            <div class="text-muted" style="font-size:0.75rem;">{{ $r['desc'] }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('styles')
<style>
    /* ── Role selector cards ─────────────────────────────────────── */
    .role-card {
        display: block;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        cursor: pointer;
        transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
        overflow: hidden;
    }
    .role-card input[type="radio"] {
        display: none;
    }
    .role-card-body {
        padding: 1rem;
        text-align: center;
    }
    .role-emoji {
        font-size: 1.8rem;
        display: block;
        margin-bottom: 0.4rem;
    }
    .role-card:hover {
        border-color: #3b82f6;
        background: #eff6ff;
        box-shadow: 0 4px 12px rgba(59,130,246,0.1);
    }
    .role-card.selected,
    .role-card:has(input:checked) {
        border-color: #3b82f6;
        background: #eff6ff;
        box-shadow: 0 4px 16px rgba(59,130,246,0.15);
    }
    .role-card.selected .fw-semibold,
    .role-card:has(input:checked) .fw-semibold {
        color: #3b82f6;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // ── Role card selection highlight ─────────────────────────────────────────
    document.querySelectorAll('.role-card').forEach(card => {
        card.addEventListener('click', function () {
            document.querySelectorAll('.role-card').forEach(c => c.classList.remove('selected'));
            this.classList.add('selected');
        });
    });

    // ── Create confirmation ───────────────────────────────────────────────────
    document.getElementById('createBtn').addEventListener('click', function () {
        const name  = document.getElementById('name').value.trim();
        const email = document.getElementById('email').value.trim();
        const role  = document.querySelector('input[name="role"]:checked');

        if (!name || !email || !role) {
            Swal.fire({
                icon: 'warning',
                title: 'Incomplete Form',
                text: 'Please fill in all required fields and select a role.',
                confirmButtonColor: '#3b82f6',
                customClass: { popup: 'rounded-3' },
            });
            return;
        }

        Swal.fire({
            title: 'Create User?',
            html: `Create account for <strong>${name}</strong> as <strong>${role.value}</strong>?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor:  '#6b7280',
            confirmButtonText: '<i class="fas fa-user-plus me-1"></i> Yes, create',
            cancelButtonText:  '<i class="fas fa-times me-1"></i> Cancel',
            customClass: { popup: 'rounded-3' },
        }).then(result => {
            if (result.isConfirmed) {
                document.getElementById('createUserForm').submit();
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