@extends('layouts.master')

@section('title', 'User Management')

@section('content')
<div class="container-fluid px-4">

    {{-- Page Header --}}
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <div class="d-flex align-items-center gap-3">
                    <div class="page-icon bg-primary-soft">
                        <i class="fas fa-users fs-1 text-primary"></i>
                    </div>
                    <div>
                        <h1 class="page-title mb-1">User Management</h1>
                        <p class="page-description text-muted mb-0">Manage system accounts and role assignments</p>
                    </div>
                </div>
            </div>
            <div class="col-auto d-flex align-items-center gap-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">User Management</li>
                    </ol>
                </nav>
                <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                    <i class="fas fa-user-plus me-2"></i>Add User
                </a>
            </div>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Stats Row --}}
    <div class="row g-3 mb-4">
        @php
            $roleMeta = [
                'admin'         => ['icon' => 'fa-shield-alt',   'color' => 'danger',  'label' => 'Admins'],
                'manager'       => ['icon' => 'fa-user-tie',     'color' => 'warning', 'label' => 'Managers'],
                'worker'        => ['icon' => 'fa-hard-hat',     'color' => 'success', 'label' => 'Workers'],
                'veterinarian'  => ['icon' => 'fa-stethoscope',  'color' => 'info',    'label' => 'Vets'],
                'accountant'    => ['icon' => 'fa-calculator',   'color' => 'primary', 'label' => 'Accountants'],
            ];
        @endphp

        <div class="col-6 col-md-2">
            <div class="stat-card text-center">
                <div class="stat-card-body">
                    <div class="stat-card-icon bg-secondary-soft d-inline-flex p-3 rounded-circle mb-2">
                        <i class="fas fa-users text-secondary fa-lg"></i>
                    </div>
                    <h6 class="text-muted mb-1 small">Total Users</h6>
                    <h3 class="mb-0">{{ $users->count() }}</h3>
                </div>
            </div>
        </div>

        @foreach($roleMeta as $role => $meta)
        <div class="col-6 col-md-2">
            <div class="stat-card text-center">
                <div class="stat-card-body">
                    <div class="stat-card-icon bg-{{ $meta['color'] }}-soft d-inline-flex p-3 rounded-circle mb-2">
                        <i class="fas {{ $meta['icon'] }} text-{{ $meta['color'] }} fa-lg"></i>
                    </div>
                    <h6 class="text-muted mb-1 small">{{ $meta['label'] }}</h6>
                    <h3 class="mb-0">{{ $users->where('role', $role)->count() }}</h3>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Users Table --}}
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-0 py-3 d-flex align-items-center justify-content-between">
            <h5 class="card-title mb-0 fw-semibold">
                <i class="fas fa-list me-2 text-primary"></i>All Users
            </h5>
            <span class="badge bg-primary-soft text-primary">{{ $users->count() }} total</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">User</th>
                            <th>Role</th>
                            <th>Phone</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th class="text-center pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                        @php
                            $roleColors = [
                                'admin'        => 'danger',
                                'manager'      => 'warning',
                                'worker'       => 'success',
                                'veterinarian' => 'info',
                                'accountant'   => 'primary',
                            ];
                            $roleIcons = [
                                'admin'        => 'fa-shield-alt',
                                'manager'      => 'fa-user-tie',
                                'worker'       => 'fa-hard-hat',
                                'veterinarian' => 'fa-stethoscope',
                                'accountant'   => 'fa-calculator',
                            ];
                            $rc = $roleColors[$user->role] ?? 'secondary';
                            $ri = $roleIcons[$user->role]  ?? 'fa-user';
                        @endphp
                        <tr id="user-row-{{ $user->id }}">
                            {{-- User Details --}}
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="avatar-circle bg-{{ $rc }}-soft text-{{ $rc }} fw-bold">
                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="fw-semibold">{{ $user->name }}</div>
                                        <small class="text-muted">{{ $user->email }}</small>
                                    </div>
                                </div>
                            </td>

                            {{-- Role --}}
                            <td>
                                <span class="badge bg-{{ $rc }}-soft text-{{ $rc }} px-3 py-2">
                                    <i class="fas {{ $ri }} me-1"></i>
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>

                            {{-- Phone --}}
                            <td>
                                <span class="text-muted small">
                                    {{ $user->phone ?? '—' }}
                                </span>
                            </td>

                            {{-- Status badge — updated by JS after AJAX toggle --}}
                            <td>
                                @if($user->is_active)
                                    <span class="status-badge badge bg-success-soft text-success">
                                        <i class="fas fa-circle me-1" style="font-size:7px; vertical-align:middle;"></i>Active
                                    </span>
                                @else
                                    <span class="status-badge badge bg-secondary-soft text-secondary">
                                        <i class="fas fa-circle me-1" style="font-size:7px; vertical-align:middle;"></i>Inactive
                                    </span>
                                @endif
                            </td>

                            {{-- Joined --}}
                            <td>
                                <span class="text-muted small">{{ $user->created_at->format('M d, Y') }}</span>
                            </td>

                            {{-- Actions --}}
                            <td class="text-center pe-4">
                                <div class="d-flex align-items-center justify-content-center gap-2">

                                    {{-- Edit --}}
                                    <a href="{{ route('admin.users.edit', $user->id) }}"
                                       class="action-btn action-btn-edit"
                                       title="Edit user">
                                        <i class="fas fa-pen"></i>
                                    </a>

                                    {{-- Toggle Status (AJAX) --}}
                                    @if($user->id !== auth()->id())
                                    <button type="button"
                                            class="action-btn {{ $user->is_active ? 'action-btn-warning' : 'action-btn-success' }} toggle-status-btn"
                                            data-user-id="{{ $user->id }}"
                                            data-user-name="{{ $user->name }}"
                                            data-is-active="{{ $user->is_active ? '1' : '0' }}"
                                            data-toggle-url="{{ route('admin.users.toggle-status', $user->id) }}"
                                            title="{{ $user->is_active ? 'Deactivate' : 'Activate' }} user">
                                        <i class="fas {{ $user->is_active ? 'fa-user-slash' : 'fa-user-check' }}"></i>
                                    </button>

                                    {{-- Delete --}}
                                    <button type="button"
                                            class="action-btn action-btn-danger delete-user-btn"
                                            data-user-id="{{ $user->id }}"
                                            data-user-name="{{ $user->name }}"
                                            title="Delete user">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                    <form id="delete-form-{{ $user->id }}"
                                          action="{{ route('admin.users.destroy', $user->id) }}"
                                          method="POST" class="d-none">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                    @else
                                    {{-- Logged-in admin: no toggle/delete on own row --}}
                                    <span class="action-btn-placeholder"></span>
                                    <span class="action-btn-placeholder"></span>
                                    @endif

                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fas fa-users-slash fa-2x mb-3 d-block opacity-50"></i>
                                No users found. <a href="{{ route('admin.users.create') }}">Add the first user</a>.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection

@push('styles')
<style>
    /* ── Avatar initials circle ─────────────────────────────────── */
    .avatar-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
        flex-shrink: 0;
    }

    /* ── Action buttons ─────────────────────────────────────────── */
    .action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        border-radius: 8px;
        border: none;
        cursor: pointer;
        font-size: 0.8rem;
        transition: transform 0.15s ease, box-shadow 0.15s ease, opacity 0.15s ease;
        text-decoration: none;
    }
    .action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    .action-btn:active {
        transform: translateY(0);
    }

    .action-btn-edit    { background: #eff6ff; color: #3b82f6; }
    .action-btn-edit:hover { background: #3b82f6; color: #fff; }

    .action-btn-warning { background: #fffbeb; color: #f59e0b; }
    .action-btn-warning:hover { background: #f59e0b; color: #fff; }

    .action-btn-success { background: #f0fdf4; color: #10b981; }
    .action-btn-success:hover { background: #10b981; color: #fff; }

    .action-btn-danger  { background: #fff5f5; color: #ef4444; }
    .action-btn-danger:hover { background: #ef4444; color: #fff; }

    .action-btn-placeholder {
        display: inline-block;
        width: 34px;
        height: 34px;
    }

    /* ── Spinner while toggling ─────────────────────────────────── */
    .action-btn.is-loading {
        pointer-events: none;
        opacity: 0.6;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// ── CSRF token for AJAX ───────────────────────────────────────────────────────
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// ── Delete ────────────────────────────────────────────────────────────────────
document.querySelectorAll('.delete-user-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        const name   = this.dataset.userName;
        const userId = this.dataset.userId;

        Swal.fire({
            title: 'Delete User?',
            html: `<p>You are about to permanently delete <strong>${name}</strong>.</p>
                   <p class="text-muted small mb-0">This action cannot be undone.</p>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor:  '#6b7280',
            confirmButtonText: '<i class="fas fa-trash-alt me-1"></i> Yes, delete',
            cancelButtonText:  '<i class="fas fa-times me-1"></i> Cancel',
            customClass: { popup: 'rounded-3' },
            focusCancel: true,
        }).then(result => {
            if (result.isConfirmed) {
                document.getElementById(`delete-form-${userId}`).submit();
            }
        });
    });
});

// ── Toggle Status (AJAX — updates DOM instantly) ──────────────────────────────
document.querySelectorAll('.toggle-status-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        const button   = this;
        const name     = button.dataset.userName;
        const userId   = button.dataset.userId;
        const isActive = button.dataset.isActive === '1';   // current state
        const action   = isActive ? 'Deactivate' : 'Activate';
        const icon     = isActive ? 'warning' : 'question';
        const color    = isActive ? '#f59e0b' : '#10b981';

        Swal.fire({
            title: `${action} User?`,
            html: `<p>${isActive
                ? `<strong>${name}</strong> will lose access to the system.`
                : `<strong>${name}</strong> will regain access to the system.`
            }</p>`,
            icon,
            showCancelButton: true,
            confirmButtonColor: color,
            cancelButtonColor:  '#6b7280',
            confirmButtonText: `<i class="fas fa-${isActive ? 'user-slash' : 'user-check'} me-1"></i> Yes, ${action.toLowerCase()}`,
            cancelButtonText:  '<i class="fas fa-times me-1"></i> Cancel',
            customClass: { popup: 'rounded-3' },
        }).then(result => {
            if (!result.isConfirmed) return;

            // Show loading state on button
            button.classList.add('is-loading');
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

            fetch(button.dataset.toggleUrl, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    'Accept':       'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            })
            .then(res => res.json())
            .then(data => {
                if (!data.success) throw new Error(data.message ?? 'Toggle failed');

                const nowActive = data.is_active; // true | false returned from controller

                // ── 1. Update button appearance ──────────────────────────
                button.classList.remove('is-loading', 'action-btn-warning', 'action-btn-success');
                button.classList.add(nowActive ? 'action-btn-warning' : 'action-btn-success');
                button.innerHTML = `<i class="fas ${nowActive ? 'fa-user-slash' : 'fa-user-check'}"></i>`;
                button.title     = nowActive ? 'Deactivate user' : 'Activate user';

                // ── 2. Flip data attribute for next click ────────────────
                button.dataset.isActive = nowActive ? '1' : '0';

                // ── 3. Update status badge in the same row ───────────────
                const row         = document.getElementById(`user-row-${userId}`);
                const statusBadge = row.querySelector('.status-badge');
                if (nowActive) {
                    statusBadge.className = 'status-badge badge bg-success-soft text-success';
                    statusBadge.innerHTML = '<i class="fas fa-circle me-1" style="font-size:7px; vertical-align:middle;"></i>Active';
                } else {
                    statusBadge.className = 'status-badge badge bg-secondary-soft text-secondary';
                    statusBadge.innerHTML = '<i class="fas fa-circle me-1" style="font-size:7px; vertical-align:middle;"></i>Inactive';
                }

                // ── 4. Success toast ─────────────────────────────────────
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: `${name} ${nowActive ? 'activated' : 'deactivated'} successfully`,
                    showConfirmButton: false,
                    timer: 2500,
                    timerProgressBar: true,
                });
            })
            .catch(err => {
                // Restore button to previous state on error
                button.classList.remove('is-loading');
                button.classList.add(isActive ? 'action-btn-warning' : 'action-btn-success');
                button.innerHTML = `<i class="fas ${isActive ? 'fa-user-slash' : 'fa-user-check'}"></i>`;

                Swal.fire({
                    icon: 'error',
                    title: 'Something went wrong',
                    text: err.message ?? 'Could not update user status. Please try again.',
                    customClass: { popup: 'rounded-3' },
                });
            });
        });
    });
});
</script>
@endpush