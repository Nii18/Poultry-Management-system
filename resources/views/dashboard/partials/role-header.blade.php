@php
    $role = auth()->user()->role;
    $roleDisplay = [
        'admin' => ['name' => 'Administrator', 'icon' => 'fa-shield-alt', 'color' => 'danger', 'greeting' => 'Farm Overview'],
        'manager' => ['name' => 'Farm Manager', 'icon' => 'fa-clipboard-list', 'color' => 'warning', 'greeting' => 'Operations Overview'],
        'head_worker' => ['name' => 'Head Worker', 'icon' => 'fa-hard-hat', 'color' => 'info', 'greeting' => 'Daily Operations'],
        'worker' => ['name' => 'Farm Worker', 'icon' => 'fa-tractor', 'color' => 'success', 'greeting' => 'My Tasks'],
        'veterinarian' => ['name' => 'Veterinarian', 'icon' => 'fa-stethoscope', 'color' => 'primary', 'greeting' => 'Health Overview'],
        'accountant' => ['name' => 'Accountant', 'icon' => 'fa-calculator', 'color' => 'secondary', 'greeting' => 'Financial Overview'],
    ][$role] ?? ['name' => 'Staff', 'icon' => 'fa-user', 'color' => 'secondary', 'greeting' => 'Welcome'];
    
    $greeting = '';
    $hour = date('H');
    if ($hour < 12) $greeting = 'Good Morning';
    elseif ($hour < 17) $greeting = 'Good Afternoon';
    else $greeting = 'Good Evening';
@endphp

<div class="role-header-card mb-4">
    <div class="row align-items-center">
        <div class="col-md-8">
            <div class="d-flex align-items-center gap-3">
                <div class="role-icon bg-{{ $roleDisplay['color'] }}-soft">
                    <i class="fas {{ $roleDisplay['icon'] }} text-{{ $roleDisplay['color'] }} fa-2x"></i>
                </div>
                <div>
                    <h5 class="text-muted mb-1">{{ $greeting }},</h5>
                    <h2 class="mb-1">{{ auth()->user()->name }}</h2>
                    <p class="mb-0">
                        <span class="badge bg-{{ $roleDisplay['color'] }} px-3 py-2 rounded-pill">
                            <i class="fas {{ $roleDisplay['icon'] }} me-1"></i> {{ $roleDisplay['name'] }}
                        </span>
                        <span class="ms-2 text-muted">
                            <i class="fas fa-calendar-alt me-1"></i> {{ now()->format('l, F j, Y') }}
                        </span>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <div class="role-stats">
                <div class="stat-badge">
                    <i class="fas fa-clock me-1"></i>
                    {{ $roleDisplay['greeting'] }}
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .role-header-card {
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        border-radius: 20px;
        padding: 1.5rem;
        border: 1px solid #e2e8f0;
    }
    .role-icon {
        width: 70px;
        height: 70px;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .bg-danger-soft { background: #fee2e2; }
    .bg-warning-soft { background: #fef3c7; }
    .bg-info-soft { background: #e0f2fe; }
    .bg-success-soft { background: #dcfce7; }
    .bg-primary-soft { background: #d1fae5; }
    .bg-secondary-soft { background: #f1f5f9; }
    .text-danger { color: #dc2626 !important; }
    .text-warning { color: #f59e0b !important; }
    .text-info { color: #3b82f6 !important; }
    .text-success { color: #10b981 !important; }
    .text-primary { color: #0d6e4f !important; }
    .text-secondary { color: #64748b !important; }
</style>
@endpush