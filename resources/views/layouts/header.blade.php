@php
    // ── Defined FIRST so every part of this template can use it ──
    $userRole = auth()->check() ? auth()->user()->role : null;

    $roleBadgeClass = match($userRole) {
        'admin'        => 'badge-danger',
        'manager'      => 'badge-warning',
        'head_worker'  => 'badge-info',
        'worker'       => 'badge-primary',
        'veterinarian' => 'badge-success',
        'accountant'   => 'badge-secondary',
        default        => 'badge-light'
    };

    $roleDisplay = match($userRole) {
        'admin'        => 'Administrator',
        'manager'      => 'Farm Manager',
        'head_worker'  => 'Head Worker',
        'worker'       => 'Farm Worker',
        'veterinarian' => 'Veterinarian',
        'accountant'   => 'Accountant',
        default        => 'Staff'
    };

    // ── Role-based search config ──────────────────────────────────
    $searchConfig = match($userRole) {
        'worker' => [
            'placeholder' => 'Search daily logs, tasks…',
            'scopes'      => ['daily_logs'],
        ],
        'head_worker' => [
            'placeholder' => 'Search flocks, logs, feed…',
            'scopes'      => ['flocks', 'daily_logs'],
        ],
        'veterinarian' => [
            'placeholder' => 'Search treatments, vaccinations, health…',
            'scopes'      => ['treatments'],
        ],
        'accountant' => [
            'placeholder' => 'Search expenses, sales, revenue…',
            'scopes'      => ['expenses'],
        ],
        'manager' => [
            'placeholder' => 'Search flocks, houses, treatments, expenses…',
            'scopes'      => ['flocks', 'houses', 'treatments', 'daily_logs', 'expenses'],
        ],
        'admin' => [
            'placeholder' => 'Search anything — flocks, logs, expenses…',
            'scopes'      => [],
        ],
        default => [
            'placeholder' => 'Search…',
            'scopes'      => [],
        ],
    };

    $searchScopes = implode(',', $searchConfig['scopes']);

    // ── Notifications (max 5 for dropdown) ───────────────────────
    use App\Models\Notification;
    try {
        $unreadCount  = auth()->check()
            ? Notification::where('user_id', auth()->id())->whereNull('read_at')->count()
            : 0;
        $recentNotifs = auth()->check()
            ? Notification::where('user_id', auth()->id())->latest()->take(5)->get()
            : collect();
        $totalNotifs  = auth()->check()
            ? Notification::where('user_id', auth()->id())->count()
            : 0;
    } catch (\Exception $e) {
        $unreadCount  = 0;
        $recentNotifs = collect();
        $totalNotifs  = 0;
    }
@endphp

<header class="page-header row">
    {{-- ── Logo ─────────────────────────────────────────────────── --}}
    <div class="logo-wrapper d-flex align-items-center col-auto">
        <a href="{{ route('dashboard') }}" class="poultry-logo-link">
            <div class="poultry-logo-wrap">
                <div class="poultry-icon-ring">
                    <span class="poultry-icon visible" id="pl-cur">🐔</span>
                    <span class="poultry-icon hidden"  id="pl-next">🥚</span>
                </div>
                <div class="poultry-logo-text">
                    <strong>POULTRY</strong>
                    <span>Management System</span>
                </div>
            </div>
        </a>
        <a class="close-btn toggle-sidebar" href="javascript:void(0)">
            <svg class="svg-color">
                <use href="{{ asset('assets/svg/iconly-sprite.svg#Category') }}"></use>
            </svg>
        </a>
    </div>

    <div class="page-main-header col position-relative">

        {{-- ── Centred title ──────────────────────────────────────── --}}
        <div class="header-center text-center">
            <h4 class="mb-0 f-w-700 system-title">Poultry Management System</h4>
            <p class="mb-0 small text-muted">Enterprise Poultry Farm Solution</p>
        </div>

        {{-- ── Search ─────────────────────────────────────────────── --}}
        <div class="header-left d-flex align-items-center">
            <li class="app-search dropdown me-3" style="list-style:none;" id="searchWrapper">
                <form action="{{ route('search') }}" method="GET" id="headerSearchForm">
                    @csrf
                    <input type="hidden" name="scopes" value="{{ $searchScopes }}">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input
                            type="search"
                            name="query"
                            class="form-control border-start-0 border-end-0"
                            placeholder="{{ $searchConfig['placeholder'] }}"
                            id="top-search"
                            value="{{ request('query') }}"
                            autocomplete="off"
                            style="width:320px;"
                            data-scopes="{{ $searchScopes }}"
                        >
                        <button type="submit" class="btn btn-primary border-start-0" style="border-radius:0 4px 4px 0;">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>

                {{-- Live-search dropdown with improved UI --}}
               
            </li>
        </div>

        {{-- ── Right nav ──────────────────────────────────────────── --}}
        <div class="nav-right">
            <ul class="header-right">

                {{-- Dark Mode --}}
                <li>
                    <a class="dark-mode" href="javascript:void(0)" title="Dark/Light Mode">
                        <svg><use href="{{ asset('assets/svg/iconly-sprite.svg#moondark') }}"></use></svg>
                    </a>
                </li>

                {{-- Audit Logs (admin / manager only) --}}
                @if(in_array($userRole, ['admin', 'manager']))
                <li class="custom-dropdown">
                    <a href="{{ route('audit-logs.index') }}" title="Audit Logs">
                        <i class="fas fa-history fa-lg"></i>
                    </a>
                </li>
                @endif

                {{-- Quick Actions --}}
                <li class="custom-dropdown">
                    <a href="javascript:void(0)" title="Quick Actions">
                        <svg><use href="{{ asset('assets/svg/iconly-sprite.svg#Document') }}"></use></svg>
                    </a>
                    <div class="custom-menu cart-dropdown py-0 overflow-hidden" style="width:250px;">
                        <h3 class="title dropdown-title">Quick Actions</h3>
                        <ul class="pb-0">
                            @if(in_array($userRole, ['admin','manager','head_worker','worker']))
                            <li>
                                <a href="javascript:void(0)" onclick="openModal('createLogModal')" class="d-flex align-items-center">
                                    <i class="fas fa-clipboard-list me-2 text-primary"></i><span>Add Daily Log</span>
                                </a>
                            </li>
                            @endif
                            @if(in_array($userRole, ['admin','manager','head_worker']))
                            <li>
                                <a href="javascript:void(0)" onclick="openModal('createFlockModal')" class="d-flex align-items-center">
                                    <i class="fas fa-users me-2 text-success"></i><span>Create New Flock</span>
                                </a>
                            </li>
                            @endif
                            @if(in_array($userRole, ['admin','manager']))
                            <li>
                                <a href="javascript:void(0)" onclick="openModal('createDeliveryModal')" class="d-flex align-items-center">
                                    <i class="fas fa-truck me-2 text-warning"></i><span>Record Feed Delivery</span>
                                </a>
                            </li>
                            @endif
                            @if(in_array($userRole, ['admin','manager','veterinarian']))
                            <li>
                                <a href="javascript:void(0)" onclick="openModal('createTreatmentModal')" class="d-flex align-items-center">
                                    <i class="fas fa-stethoscope me-2 text-danger"></i><span>Record Treatment</span>
                                </a>
                            </li>
                            @endif
                            @if(in_array($userRole, ['admin','manager','accountant']))
                            <li>
                                <a href="javascript:void(0)" onclick="openModal('createExpenseModal')" class="d-flex align-items-center">
                                    <i class="fas fa-dollar-sign me-2 text-info"></i><span>Add Expense</span>
                                </a>
                            </li>
                            @endif
                        </ul>
                    </div>
                </li>

                {{-- ── Notifications ──────────────────────────────── --}}
                <li class="custom-dropdown" id="notifDropdownLi">
                    <a href="javascript:void(0)" id="notificationBell" title="Notifications">
                        <svg><use href="{{ asset('assets/svg/iconly-sprite.svg#notification') }}"></use></svg>
                    </a>

                    <span class="badge rounded-pill badge-primary notification-badge-header"
                          id="notificationBadge"
                          style="{{ $unreadCount == 0 ? 'display:none;' : '' }}">
                        {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                    </span>

                    <div class="custom-menu notification-dropdown py-0 overflow-hidden" style="min-width:360px;">

                        {{-- Dropdown header row --}}
                        <div class="d-flex align-items-center justify-content-between px-3 py-2 bg-primary-light border-bottom">
                            <h6 class="mb-0 fw-bold">
                                Notifications
                                @if($unreadCount > 0)
                                    <span class="badge badge-primary ms-1" style="font-size:.65rem;">{{ $unreadCount }} new</span>
                                @endif
                            </h6>
                            <div class="d-flex gap-2">
                                <button class="btn btn-xs btn-outline-secondary py-0 px-2"
                                        id="markAllReadBtn"
                                        title="Mark all as read"
                                        style="font-size:.72rem;">
                                    <i class="fas fa-check-double me-1"></i>All read
                                </button>
                                <a href="{{ route('notifications.index') }}"
                                   class="btn btn-xs btn-outline-primary py-0 px-2"
                                   style="font-size:.72rem;">
                                    View all
                                </a>
                            </div>
                        </div>

                        {{-- Scrollable list: max 5 items shown --}}
                        <ul class="activity-timeline notif-scroll-list mb-0" id="notificationList"
                            style="max-height:300px;overflow-y:auto;padding:0;">
                            @forelse($recentNotifs as $notif)
                                <li class="notification-item px-3 py-2 border-bottom {{ $notif->read_at ? '' : 'notif-unread' }}"
                                    data-id="{{ $notif->id }}"
                                    onclick="viewNotification({{ $notif->id }})">
                                    <div class="d-flex align-items-start gap-2">
                                        <div class="flex-shrink-0 pt-1">
                                            <span class="notif-dot {{ $notif->read_at ? 'notif-dot-read' : 'notif-dot-unread' }}"></span>
                                        </div>
                                        <div class="flex-grow-1 overflow-hidden">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <strong class="d-block text-truncate" style="max-width:210px;font-size:.85rem;">
                                                    {{ $notif->title }}
                                                </strong>
                                                @if(!$notif->read_at)
                                                    <span class="badge badge-primary badge-sm ms-1 flex-shrink-0">New</span>
                                                @endif
                                            </div>
                                            <p class="mb-0 text-truncate" style="font-size:.75rem;color:#6c757d;">
                                                {{ \Illuminate\Support\Str::limit($notif->message, 65) }}
                                            </p>
                                            <small class="text-muted" style="font-size:.68rem;">
                                                {{ $notif->created_at->diffForHumans() }}
                                            </small>
                                        </div>
                                    </div>
                                </li>
                            @empty
                                <li class="text-center py-4" id="emptyNotifMsg">
                                    <i class="fas fa-bell-slash text-muted fa-lg mb-2 d-block"></i>
                                    <small class="text-muted">No notifications yet</small>
                                </li>
                            @endforelse
                        </ul>

                        {{-- Footer — only visible when there are more than 5 --}}
                        <div class="text-center py-2 border-top bg-light" id="notifFooter"
                             style="{{ $totalNotifs <= 5 ? 'display:none;' : '' }}">
                            <small class="text-muted">
                                Showing 5 of <strong id="notifTotalCount">{{ $totalNotifs }}</strong> ·
                                <a href="{{ route('notifications.index') }}" class="text-primary">See all</a>
                            </small>
                        </div>
                    </div>
                </li>

                {{-- ── User Profile ───────────────────────────────── --}}
                <li class="profile-nav custom-dropdown">
                    <div class="user-wrap">
                        <div class="user-img">
                            @auth
                                <img src="{{ auth()->user()->avatar ? auth()->user()->avatar_url : asset('assets/images/genericavatarimage.jpg') }}" alt="user">
                            @else
                                <img src="{{ asset('assets/images/genericavatarimage.jpg') }}" alt="user">
                            @endauth
                        </div>
                    </div>

                    <div class="custom-menu overflow-hidden" style="min-width:220px;">
                        <ul class="profile-body">
                            <li class="d-flex align-items-center border-bottom mb-2 pb-2"></li>

                            <li class="d-flex align-items-center">
                                <i class="fas fa-user me-2"></i>
                                <a href="{{ route('account.edit') }}">Account</a>
                            </li>
                            <li class="d-flex align-items-center">
                                <i class="fas fa-lock me-2"></i>
                                <a href="{{ route('account.password.edit') }}">Change Password</a>
                            </li>

                            @if(in_array($userRole, ['admin','manager']))
                            <li class="d-flex align-items-center">
                                <i class="fas fa-exchange-alt me-2"></i>
                                <a href="{{ route('user.switch') }}">Switch User</a>
                            </li>
                            @endif

                            @if($userRole === 'admin')
                            <li class="d-flex align-items-center">
                                <i class="fas fa-cog me-2"></i>
                                <a href="{{ route('settings.index') }}">Settings</a>
                            </li>
                            @endif

                            <li class="d-flex align-items-center">
                                <i class="fas fa-sign-out-alt text-danger me-2"></i>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="btn-link text-danger p-0 border-0 bg-transparent">
                                        Log Out
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </li>

            </ul>
        </div>{{-- /nav-right --}}
    </div>{{-- /page-main-header --}}
</header>


@push('styles')
<style>
/* ── Layout ──────────────────────────────────────────────────── */
.page-main-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    position: relative;
}
.header-center {
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
    text-align: center;
    z-index: 1;
    pointer-events: none;
}
.system-title {
    background: linear-gradient(135deg, #4CAF50 0%, #2196F3 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    font-size: 1.2rem;
}
.header-left  { flex-shrink: 0; z-index: 100; }
.nav-right    { flex-shrink: 0; z-index: 2;   }
.header-right {
    display: flex;
    align-items: center;
    gap: 12px;
    margin: 0; padding: 0;
    list-style: none;
}
.header-right > li {
    display: flex;
    align-items: center;
    position: relative;
}

/* ── Improved Search Dropdown UI ──────────────────────────────────── */
.app-search { position: relative; }
.dropdown-lg { 
    min-width: 500px; 
    width: 500px;
}

.dropdown-menu {
    position: absolute;
    top: calc(100% + 8px);
    left: 0;
    z-index: 1000;
    display: none;
    background-color: #fff;
    background-clip: padding-box;
    border: 1px solid rgba(0,0,0,.1);
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0,0,0,.15);
    padding: 0;
    overflow: hidden;
}

.dropdown-menu.show { 
    display: block;
    animation: dropdownSlideIn 0.2s ease-out;
}

@keyframes dropdownSlideIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.dropdown-header {
    display: block;
    padding: 12px 16px;
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    border-bottom: 1px solid #e9ecef;
    font-size: 0.8rem;
    font-weight: 600;
    color: #495057;
}

.dropdown-header h6 {
    font-size: 0.85rem;
    font-weight: 600;
}

/* Search Result Items - Like Second Image */
.search-section-header {
    padding: 10px 16px;
    background: #f8f9fa;
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #6c757d;
    border-bottom: 1px solid #e9ecef;
}

.search-result-item {
    display: flex;
    align-items: center;
    padding: 12px 16px;
    text-decoration: none;
    color: #212529;
    border-bottom: 1px solid #f0f0f0;
    transition: all 0.2s ease;
    cursor: pointer;
}

.search-result-item:hover {
    background-color: #f8f9fa;
}

.result-icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    margin-right: 14px;
    flex-shrink: 0;
    font-size: 1.1rem;
}

.result-content {
    flex: 1;
    min-width: 0;
}

.result-title {
    font-weight: 600;
    font-size: 0.9rem;
    margin-bottom: 4px;
    color: #212529;
}

.result-subtitle {
    font-size: 0.75rem;
    color: #6c757d;
    line-height: 1.3;
}

.result-badge {
    display: inline-block;
    padding: 2px 8px;
    background: #e9ecef;
    border-radius: 12px;
    font-size: 0.65rem;
    font-weight: 600;
    color: #495057;
    margin-left: 8px;
}

.result-arrow {
    color: #cbd5e1;
    font-size: 12px;
    flex-shrink: 0;
    margin-left: 12px;
    transition: transform 0.2s;
}

.search-result-item:hover .result-arrow {
    transform: translateX(4px);
    color: #4f46e5;
}

/* Loading State */
.search-loading {
    padding: 40px;
    text-align: center;
}

.search-loading-spinner {
    width: 36px;
    height: 36px;
    border: 3px solid #e9ecef;
    border-top-color: #4f46e5;
    border-radius: 50%;
    animation: spin 0.6s linear infinite;
    margin: 0 auto 12px;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Empty State */
.search-empty {
    padding: 50px 20px;
    text-align: center;
}

.search-empty-icon {
    font-size: 48px;
    color: #dee2e6;
    margin-bottom: 12px;
}

.search-empty-title {
    font-weight: 500;
    font-size: 0.9rem;
    color: #6c757d;
    margin-bottom: 4px;
}

.search-empty-subtitle {
    font-size: 0.8rem;
    color: #adb5bd;
}

/* Footer */
.search-dropdown-footer {
    padding: 12px 16px;
    background: #f8f9fa;
    border-top: 1px solid #e9ecef;
    text-align: center;
}

.view-all-link {
    text-decoration: none;
    font-size: 0.85rem;
    font-weight: 600;
    color: #4f46e5;
    transition: color 0.2s;
}

.view-all-link:hover {
    color: #6366f1;
}

/* Scrollbar */
#search-results::-webkit-scrollbar {
    width: 6px;
}
#search-results::-webkit-scrollbar-track {
    background: #f1f3f5;
}
#search-results::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 3px;
}
#search-results::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* Search Input Styling */
#top-search {
    border-radius: 8px 0 0 8px;
    font-size: 0.9rem;
    transition: all 0.2s;
}

#top-search:focus {
    box-shadow: none;
    border-color: #4f46e5;
}

#searchSubmitBtn {
    border-radius: 0 8px 8px 0;
    transition: all 0.2s;
}

#searchSubmitBtn:hover {
    background: #4338ca;
    border-color: #4338ca;
}

/* ── Notifications ───────────────────────────────────────────── */
.notification-badge-header {
    position: absolute;
    top: -8px; right: -8px;
    font-size: .6rem;
    padding: 2px 5px;
}
.notif-dot {
    display: inline-block;
    width: 8px; height: 8px;
    border-radius: 50%;
}
.notif-dot-unread { background: #0d6efd; }
.notif-dot-read   { background: #dee2e6; }
.notif-unread     { background: rgba(13,110,253,.04); }

.notification-item {
    cursor: pointer;
    transition: background .15s;
    list-style: none;
}
.notification-item:hover { background: #f8f9fa; }

.notif-scroll-list::-webkit-scrollbar       { width: 4px; }
.notif-scroll-list::-webkit-scrollbar-thumb { background: #dee2e6; border-radius: 4px; }

.badge-sm { font-size: .65rem; padding: 2px 6px; }
.btn-xs   { font-size: .72rem !important; padding: .15rem .4rem !important; }

/* ── Modal detail grids ──────────────────────────────────────── */
.detail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
}
.detail-item  { display: flex; flex-direction: column; }
.detail-label { font-size: .7rem; text-transform: uppercase; color: #64748b; font-weight: 600; margin-bottom: .25rem; }
.detail-value { font-size: .95rem; font-weight: 500; color: #1e293b; }
.badge-active   { background: #dcfce7; color: #166534; padding: 5px 12px; border-radius: 20px; font-size: .75rem; }
.badge-inactive { background: #fee2e2; color: #991b1b; padding: 5px 12px; border-radius: 20px; font-size: .75rem; }

/* ── Dark mode overrides ─────────────────────────────────────── */
.dark .dropdown-menu { 
    background: #1e1e2f; 
    border-color: #2d2d44; 
    box-shadow: 0 10px 30px rgba(0,0,0,.3);
}
.dark .dropdown-header {
    background: linear-gradient(135deg, #25253a 0%, #1e1e2f 100%);
    border-bottom-color: #2d2d44;
    color: #e2e8f0;
}
.dark .search-section-header {
    background: #202033;
    color: #94a3b8;
    border-bottom-color: #2d2d44;
}
.dark .search-result-item {
    color: #e2e8f0;
    border-bottom-color: #2d2d44;
}
.dark .search-result-item:hover { background-color: #2d2d44; }
.dark .result-title { color: #e2e8f0; }
.dark .result-subtitle { color: #94a3b8; }
.dark .result-badge {
    background: #2d2d44;
    color: #cbd5e1;
}
.dark .search-dropdown-footer {
    background: #25253a;
    border-top-color: #2d2d44;
}
.dark .detail-value { color: #e0e0e0; }
.dark .badge-active   { background: #065f46; color: #dcfce7; }
.dark .badge-inactive { background: #991b1b; color: #fee2e2; }
.dark .notification-item:hover { background-color: #2d2d44; }
.dark .notif-unread { background: rgba(13,110,253,.08); }

/* ── Responsive ──────────────────────────────────────────────── */
@media (max-width: 992px) {
    .header-center h4 { font-size: .9rem; }
    .header-center p  { display: none; }
    .dropdown-lg { 
        min-width: 380px; 
        width: 380px;
        right: 0;
        left: auto;
    }
    #top-search { width: 250px !important; }
}

@media (max-width: 768px) {
    .dropdown-lg { 
        min-width: calc(100vw - 32px); 
        width: calc(100vw - 32px);
    }
    #top-search { width: 200px !important; }
    .result-icon { width: 36px; height: 36px; font-size: 1rem; }
    .result-title { font-size: 0.85rem; }
}
</style>
@endpush


@push('scripts')
<script>
$(document).ready(function () {

    // ════════════════════════════════════════════════════════════
    // ENHANCED SEARCH WITH IMPROVED UI
    // ════════════════════════════════════════════════════════════
    let searchTimeout;
    const searchInput    = $('#top-search');
    const searchDropdown = $('#search-dropdown');
    const searchResults  = $('#search-results');
    const resultsCount   = $('#search-results-count');
    const searchScopes   = searchInput.data('scopes') || '';

    // Section configurations for better UI
    const sectionConfig = {
        flocks: { 
            icon: 'fa-users', 
            color: '#4f46e5', 
            bg: 'rgba(79, 70, 229, 0.1)',
            label: 'FLOCKS & HERDS',
            emoji: '🐔'
        },
        houses: { 
            icon: 'fa-building', 
            color: '#10b981', 
            bg: 'rgba(16, 185, 129, 0.1)',
            label: 'HOUSES',
            emoji: '🏠'
        },
        treatments: { 
            icon: 'fa-stethoscope', 
            color: '#ef4444', 
            bg: 'rgba(239, 68, 68, 0.1)',
            label: 'TREATMENTS',
            emoji: '💊'
        },
        daily_logs: { 
            icon: 'fa-clipboard-list', 
            color: '#06b6d4', 
            bg: 'rgba(6, 182, 212, 0.1)',
            label: 'DAILY LOGS',
            emoji: '📋'
        },
        expenses: { 
            icon: 'fa-receipt', 
            color: '#f59e0b', 
            bg: 'rgba(245, 158, 11, 0.1)',
            label: 'EXPENSES',
            emoji: '💰'
        }
    };

    // Close on outside click
    $(document).on('click', function (e) {
        if (
            !searchDropdown.is(e.target) &&
            !searchInput.is(e.target) &&
            !searchInput.closest('.input-group').is(e.target) &&
            searchDropdown.has(e.target).length === 0
        ) {
            searchDropdown.removeClass('show');
        }
    });

    $('#closeSearchDropdown').on('click', function () {
        searchDropdown.removeClass('show');
    });

    // Prevent dropdown from closing when clicking inside
    searchDropdown.on('click', function(e) {
        e.stopPropagation();
    });

    searchInput.on('focus', function () {
        const q = $(this).val().trim();
        if (q.length >= 2) {
            performSearch(q);
        } else {
            resultsCount.html('<i class="fas fa-search me-1"></i> Start typing to search...');
            searchResults.html('');
            searchDropdown.addClass('show');
        }
    });

    searchInput.on('input', function () {
        clearTimeout(searchTimeout);
        const q = $(this).val().trim();
        if (q.length < 2) {
            resultsCount.html('<i class="fas fa-search me-1"></i> Start typing to search...');
            searchResults.html('');
            searchDropdown.addClass('show');
            return;
        }
        searchTimeout = setTimeout(() => performSearch(q), 300);
    });

    function performSearch(query) {
        resultsCount.html('<i class="fas fa-spinner fa-spin me-1"></i> Searching...');
        searchResults.html(`
            <div class="search-loading">
                <div class="search-loading-spinner"></div>
                <div class="text-muted" style="font-size: 0.8rem;">Searching across modules...</div>
            </div>
        `);
        searchDropdown.addClass('show');

        $.ajax({
            url: '/api/search',
            method: 'GET',
            data: { query: query, scopes: searchScopes },
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: data  => displaySearchResults(data, query),
            error:  (xhr)  => {
                console.error('Search error:', xhr);
                resultsCount.html('<i class="fas fa-exclamation-triangle me-1"></i> Error');
                searchResults.html(`
                    <div class="search-empty">
                        <div class="search-empty-icon"><i class="fas fa-exclamation-triangle"></i></div>
                        <div class="search-empty-title">Something went wrong</div>
                        <div class="search-empty-subtitle">Please try again</div>
                    </div>
                `);
            }
        });
    }

    function displaySearchResults(data, query) {
        const totalResults = data.total || 0;
        
        if (totalResults === 0) {
            resultsCount.html('<i class="fas fa-search me-1"></i> No results found');
            searchResults.html(`
                <div class="search-empty">
                    <div class="search-empty-icon"><i class="fas fa-inbox"></i></div>
                    <div class="search-empty-title">No results found for "${escapeHtml(query)}"</div>
                    <div class="search-empty-subtitle">Try different keywords</div>
                </div>
            `);
            return;
        }

        resultsCount.html(`<i class="fas fa-search me-1"></i> ${totalResults} result${totalResults !== 1 ? 's' : ''} found`);
        
        let html = '';
        let hasResults = false;

        // Build sections like the second image
        for (const [key, config] of Object.entries(sectionConfig)) {
            if (data[key] && data[key].length > 0) {
                hasResults = true;
                html += `
                    <div class="search-section-header">
                        <i class="fas ${config.icon} me-2"></i>
                        ${config.label}
                    </div>
                `;
                
                data[key].forEach(item => {
                    html += `
                        <div class="search-result-item" onclick="navigateToResult('${key}', ${item.id})">
                            <div class="result-icon" style="background: ${config.bg}; color: ${config.color};">
                                <i class="fas ${config.icon}"></i>
                            </div>
                            <div class="result-content">
                                <div class="result-title">
                                    ${escapeHtml(item.name)}
                                    ${item.category ? `<span class="result-badge">${escapeHtml(item.category)}</span>` : ''}
                                </div>
                                <div class="result-subtitle">${escapeHtml(item.subtitle)}</div>
                            </div>
                            <div class="result-arrow">
                                <i class="fas fa-chevron-right"></i>
                            </div>
                        </div>
                    `;
                });
            }
        }

        if (!hasResults) {
            html = `
                <div class="search-empty">
                    <div class="search-empty-icon"><i class="fas fa-search"></i></div>
                    <div class="search-empty-title">No results found</div>
                    <div class="search-empty-subtitle">Try a different search term</div>
                </div>
            `;
        } else {
            html += `
                <div class="search-dropdown-footer">
                    <a href="/search?query=${encodeURIComponent(query)}&scopes=${encodeURIComponent(searchScopes)}" 
                       class="view-all-link">
                        View all ${totalResults} results <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            `;
        }

        searchResults.html(html);
    }

    // Navigation function
    window.navigateToResult = function(type, id) {
        const routes = {
            flocks: `/flocks/${id}`,
            houses: `/houses/${id}`,
            treatments: `/treatments/${id}`,
            daily_logs: `/daily-logs/${id}`,
            expenses: `/expenses/${id}`
        };
        
        const url = routes[type];
        if (url) {
            searchDropdown.removeClass('show');
            window.location.href = url;
        }
    };

    // ════════════════════════════════════════════════════════════
    // SEARCH → MODAL DETAIL HANDLERS (keep your existing ones)
    // ════════════════════════════════════════════════════════════
    window.showFlockDetailsFromSearch = function (id) {
        searchDropdown.removeClass('show');
        const el = document.getElementById('viewFlockModal');
        if (!el) { window.location.href = `/flocks/${id}`; return; }
        const modal = new bootstrap.Modal(el);
        const body  = document.getElementById('viewFlockContent');
        body.innerHTML = `<div class="text-center py-4"><div class="spinner-border text-primary"></div><p class="mt-2">Loading…</p></div>`;
        modal.show();
        fetch(`/flocks/${id}/details`, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(d => { if (d.success) displayFlockDetailsInModal(d.flock, d.summary); else body.innerHTML = `<div class="alert alert-danger">${d.message}</div>`; })
            .catch(() => { body.innerHTML = `<div class="alert alert-danger">Error loading data</div>`; });
    };

    window.showHouseDetailsFromSearch = function (id) {
        searchDropdown.removeClass('show');
        const el = document.getElementById('viewHouseModal');
        if (!el) { window.location.href = `/houses/${id}`; return; }
        const modal = new bootstrap.Modal(el);
        const body  = document.getElementById('viewHouseContent');
        body.innerHTML = `<div class="text-center py-4"><div class="spinner-border text-success"></div><p class="mt-2">Loading…</p></div>`;
        modal.show();
        fetch(`/houses/${id}/details`, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(d => { if (d.success) displayHouseDetailsInModal(d.house, d.stats); else body.innerHTML = `<div class="alert alert-danger">${d.message}</div>`; })
            .catch(() => { body.innerHTML = `<div class="alert alert-danger">Error loading data</div>`; });
    };

    window.showTreatmentDetailsFromSearch = function (id) {
        searchDropdown.removeClass('show');
        const el = document.getElementById('viewTreatmentModal');
        if (!el) { window.location.href = `/treatments/${id}`; return; }
        const modal = new bootstrap.Modal(el);
        const body  = document.getElementById('viewTreatmentContent');
        body.innerHTML = `<div class="text-center py-4"><div class="spinner-border text-danger"></div><p class="mt-2">Loading…</p></div>`;
        modal.show();
        fetch(`/treatments/${id}/details`, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(d => { if (d.success) displayTreatmentDetailsInModal(d.treatment); else body.innerHTML = `<div class="alert alert-danger">${d.message}</div>`; })
            .catch(() => { body.innerHTML = `<div class="alert alert-danger">Error loading data</div>`; });
    };

    window.showDailyLogDetailsFromSearch = function (id) {
        searchDropdown.removeClass('show');
        const el = document.getElementById('viewDailyLogModal');
        if (!el) { window.location.href = `/daily-logs/${id}`; return; }
        const modal = new bootstrap.Modal(el);
        const body  = document.getElementById('viewDailyLogContent');
        body.innerHTML = `<div class="text-center py-4"><div class="spinner-border text-info"></div><p class="mt-2">Loading…</p></div>`;
        modal.show();
        fetch(`/daily-logs/${id}/details`, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(d => { if (d.success) displayDailyLogDetailsInModal(d.log); else body.innerHTML = `<div class="alert alert-danger">${d.message}</div>`; })
            .catch(() => { body.innerHTML = `<div class="alert alert-danger">Error loading data</div>`; });
    };

    window.showExpenseDetailsFromSearch = function (id) {
        searchDropdown.removeClass('show');
        const el = document.getElementById('viewExpenseModal');
        if (!el) { window.location.href = `/expenses/${id}`; return; }
        const modal = new bootstrap.Modal(el);
        const body  = document.getElementById('viewExpenseContent');
        body.innerHTML = `<div class="text-center py-4"><div class="spinner-border text-warning"></div><p class="mt-2">Loading…</p></div>`;
        modal.show();
        fetch(`/expenses/${id}/details-json`, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(d => { if (d.success) displayExpenseDetailsInModal(d.expense); else body.innerHTML = `<div class="alert alert-danger">${d.message}</div>`; })
            .catch(() => { body.innerHTML = `<div class="alert alert-danger">Error loading data</div>`; });
    };

    // ════════════════════════════════════════════════════════════
    // MODAL CONTENT RENDERERS (keep your existing ones)
    // ════════════════════════════════════════════════════════════
    function displayFlockDetailsInModal(flock, summary) {
        document.getElementById('viewFlockContent').innerHTML = `
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h5 class="mb-1">${escapeHtml(flock.flock_number)}</h5>
                    <p class="text-muted mb-0">${escapeHtml(flock.breed_variety)}</p>
                </div>
                <span class="badge ${flock.status === 'active' ? 'badge-active' : 'badge-inactive'} px-3 py-2">${escapeHtml(flock.status || 'N/A')}</span>
            </div>
            <div class="row g-3 mb-4">
                <div class="col-md-4"><div class="text-center p-3 bg-light rounded">
                    <div class="fs-4 fw-bold text-primary">${summary.age_days}</div><small class="text-muted">Age (days)</small>
                </div></div>
                <div class="col-md-4"><div class="text-center p-3 bg-light rounded">
                    <div class="fs-4 fw-bold text-success">${summary.current_count.toLocaleString()}</div><small class="text-muted">Current Count</small>
                </div></div>
                <div class="col-md-4"><div class="text-center p-3 bg-light rounded">
                    <div class="fs-4 fw-bold text-danger">${summary.mortality_rate}%</div><small class="text-muted">Mortality Rate</small>
                </div></div>
            </div>
            <h6>Basic Information</h6>
            <div class="detail-grid">
                <div class="detail-item"><span class="detail-label">Species</span><span class="detail-value">${escapeHtml(flock.species_name)}</span></div>
                <div class="detail-item"><span class="detail-label">House</span><span class="detail-value">${escapeHtml(flock.house_name)}</span></div>
                <div class="detail-item"><span class="detail-label">Breed</span><span class="detail-value">${escapeHtml(flock.breed_variety)}</span></div>
                <div class="detail-item"><span class="detail-label">Start Date</span><span class="detail-value">${flock.start_date}</span></div>
                <div class="detail-item"><span class="detail-label">Initial Count</span><span class="detail-value">${flock.initial_count.toLocaleString()}</span></div>
                <div class="detail-item"><span class="detail-label">Production Type</span><span class="detail-value">${escapeHtml(flock.production_type)}</span></div>
            </div>
            ${flock.notes ? `<div class="mt-3"><h6>Notes</h6><p class="mb-0 p-3 bg-light rounded">${escapeHtml(flock.notes)}</p></div>` : ''}`;
    }

    function displayHouseDetailsInModal(house, stats) {
        document.getElementById('viewHouseContent').innerHTML = `
            <h5 class="mb-1">${escapeHtml(house.name)}</h5>
            <p class="text-muted mb-3">Code: ${escapeHtml(house.house_code)}</p>
            <div class="detail-grid">
                <div class="detail-item"><span class="detail-label">House Code</span><span class="detail-value">${escapeHtml(house.house_code)}</span></div>
                <div class="detail-item"><span class="detail-label">Species</span><span class="detail-value">${escapeHtml(house.species_name || 'Not Assigned')}</span></div>
                <div class="detail-item"><span class="detail-label">Status</span><span class="detail-value">${escapeHtml(house.status)}</span></div>
                <div class="detail-item"><span class="detail-label">Capacity</span><span class="detail-value">${house.capacity.toLocaleString()}</span></div>
                <div class="detail-item"><span class="detail-label">Total Flocks</span><span class="detail-value">${stats.total_flocks}</span></div>
                <div class="detail-item"><span class="detail-label">Total Animals</span><span class="detail-value">${stats.total_animals.toLocaleString()}</span></div>
            </div>
            ${house.notes ? `<div class="mt-3"><h6>Notes</h6><p class="mb-0 p-3 bg-light rounded">${escapeHtml(house.notes)}</p></div>` : ''}`;
    }

    function displayTreatmentDetailsInModal(t) {
        document.getElementById('viewTreatmentContent').innerHTML = `
            <h5 class="mb-1">${escapeHtml(t.medication_name)}</h5>
            <p class="text-muted mb-3">${escapeHtml(t.diagnosis)}</p>
            <div class="detail-grid">
                <div class="detail-item"><span class="detail-label">Flock</span><span class="detail-value">${escapeHtml(t.flock_number)}</span></div>
                <div class="detail-item"><span class="detail-label">Start Date</span><span class="detail-value">${t.start_date}</span></div>
                <div class="detail-item"><span class="detail-label">End Date</span><span class="detail-value">${t.end_date}</span></div>
                <div class="detail-item"><span class="detail-label">Dosage</span><span class="detail-value">${escapeHtml(t.dosage)}</span></div>
                <div class="detail-item"><span class="detail-label">Route</span><span class="detail-value">${escapeHtml(t.administration_route)}</span></div>
                <div class="detail-item"><span class="detail-label">Animals Treated</span><span class="detail-value">${t.animals_treated || 'N/A'}</span></div>
            </div>
            ${t.notes ? `<div class="mt-3"><h6>Notes</h6><p class="mb-0 p-3 bg-light rounded">${escapeHtml(t.notes)}</p></div>` : ''}`;
    }

    function displayDailyLogDetailsInModal(log) {
        document.getElementById('viewDailyLogContent').innerHTML = `
            <div class="detail-grid">
                <div class="detail-item"><span class="detail-label">Date</span><span class="detail-value">${log.log_date}</span></div>
                <div class="detail-item"><span class="detail-label">Flock</span><span class="detail-value">${escapeHtml(log.flock_number)}</span></div>
                <div class="detail-item"><span class="detail-label">Mortality</span><span class="detail-value">${log.mortality_count}</span></div>
                <div class="detail-item"><span class="detail-label">Culling</span><span class="detail-value">${log.culling_count}</span></div>
                <div class="detail-item"><span class="detail-label">Feed Intake</span><span class="detail-value">${log.feed_intake_kg} kg</span></div>
                <div class="detail-item"><span class="detail-label">Water</span><span class="detail-value">${log.water_consumption_liters} L</span></div>
                <div class="detail-item"><span class="detail-label">Avg Weight</span><span class="detail-value">${log.average_weight_kg} kg</span></div>
                <div class="detail-item"><span class="detail-label">Temperature</span><span class="detail-value">${log.min_temperature_c}°C – ${log.max_temperature_c}°C</span></div>
            </div>
            ${log.notes ? `<div class="mt-3"><h6>Notes</h6><p class="mb-0 p-3 bg-light rounded">${escapeHtml(log.notes)}</p></div>` : ''}`;
    }

    function displayExpenseDetailsInModal(e) {
        document.getElementById('viewExpenseContent').innerHTML = `
            <div class="detail-grid">
                <div class="detail-item"><span class="detail-label">Date</span><span class="detail-value">${e.expense_date}</span></div>
                <div class="detail-item"><span class="detail-label">Category</span><span class="detail-value">${escapeHtml(e.category)}</span></div>
                <div class="detail-item"><span class="detail-label">Description</span><span class="detail-value">${escapeHtml(e.description)}</span></div>
                <div class="detail-item"><span class="detail-label">Amount</span><span class="detail-value text-danger fw-bold">₵${parseFloat(e.amount).toLocaleString()}</span></div>
                <div class="detail-item"><span class="detail-label">Vendor</span><span class="detail-value">${escapeHtml(e.vendor_name || 'N/A')}</span></div>
                <div class="detail-item"><span class="detail-label">Payment Method</span><span class="detail-value">${escapeHtml(e.payment_method || 'N/A')}</span></div>
                <div class="detail-item"><span class="detail-label">Flock</span><span class="detail-value">${escapeHtml(e.flock_number || 'None')}</span></div>
            </div>
            ${e.notes ? `<div class="mt-3"><h6>Notes</h6><p class="mb-0 p-3 bg-light rounded">${escapeHtml(e.notes)}</p></div>` : ''}`;
    }

    // ════════════════════════════════════════════════════════════
    // QUICK-ACTION MODAL HELPER
    // ════════════════════════════════════════════════════════════
    window.openModal = function (modalId) {
        const el = document.getElementById(modalId);
        if (el) {
            new bootstrap.Modal(el).show();
            return;
        }
        const map = {
            createLogModal:      '{{ route("daily-logs.index") }}',
            createFlockModal:    '{{ route("flocks.index") }}',
            createDeliveryModal: '{{ route("feed-deliveries.index") }}',
            createTreatmentModal:'{{ route("treatments.index") }}',
            createExpenseModal:  '{{ route("expenses.index") }}',
        };
        sessionStorage.setItem('openModalOnLoad', modalId);
        window.location.href = map[modalId] || '{{ route("dashboard") }}';
    };

    (function checkAndOpenModalOnLoad() {
        const m = sessionStorage.getItem('openModalOnLoad');
        if (!m) return;
        sessionStorage.removeItem('openModalOnLoad');
        setTimeout(() => {
            const el = document.getElementById(m);
            if (el) new bootstrap.Modal(el).show();
        }, 500);
    })();

    // ════════════════════════════════════════════════════════════
    // NOTIFICATIONS
    // ════════════════════════════════════════════════════════════
    const NOTIF_LIMIT = 5;

    function loadNotifications() {
        fetch('{{ route("api.notifications") }}', {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            if (!data.success) return;
            updateBadge(data.unread_count);
            renderNotifList(data.notifications.slice(0, NOTIF_LIMIT));
            updateNotifFooter(data.notifications.length);
        })
        .catch(err => console.error('Notifications error:', err));
    }

    function updateBadge(count) {
        const b = document.getElementById('notificationBadge');
        if (!b) return;
        b.textContent = count > 99 ? '99+' : count;
        b.style.display = count > 0 ? 'inline-block' : 'none';
    }

    function renderNotifList(notifications) {
        const list = document.getElementById('notificationList');
        if (!list) return;

        if (!notifications || !notifications.length) {
            list.innerHTML = `<li class="text-center py-4">
                <i class="fas fa-bell-slash text-muted fa-lg mb-2 d-block"></i>
                <small class="text-muted">No notifications yet</small></li>`;
            return;
        }

        list.innerHTML = notifications.map(n => `
            <li class="notification-item px-3 py-2 border-bottom ${n.read_at ? '' : 'notif-unread'}"
                data-id="${n.id}" onclick="viewNotification(${n.id})">
                <div class="d-flex align-items-start gap-2">
                    <div class="flex-shrink-0 pt-1">
                        <span class="notif-dot ${n.read_at ? 'notif-dot-read' : 'notif-dot-unread'}"></span>
                    </div>
                    <div class="flex-grow-1 overflow-hidden">
                        <div class="d-flex justify-content-between align-items-start">
                            <strong class="d-block text-truncate" style="max-width:210px;font-size:.85rem;">
                                ${escapeHtml(n.title)}
                            </strong>
                            ${!n.read_at ? '<span class="badge badge-primary badge-sm ms-1 flex-shrink-0">New</span>' : ''}
                        </div>
                        <p class="mb-0 text-truncate" style="font-size:.75rem;color:#6c757d;">
                            ${escapeHtml((n.message || '').substring(0, 65))}
                        </p>
                        <small class="text-muted" style="font-size:.68rem;">${escapeHtml(n.time_ago)}</small>
                    </div>
                </div>
            </li>`).join('');
    }

    function updateNotifFooter(total) {
        const footer  = document.getElementById('notifFooter');
        const totalEl = document.getElementById('notifTotalCount');
        if (!footer) return;
        footer.style.display = total > NOTIF_LIMIT ? '' : 'none';
        if (totalEl) totalEl.textContent = total;
    }

    $('#markAllReadBtn')?.on('click', function (e) {
        e.stopPropagation();
        fetch('{{ route("notifications.mark-all-read") }}', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept':           'application/json',
                'X-CSRF-TOKEN':     '{{ csrf_token() }}'
            }
        })
        .then(r => r.json())
        .then(d => { if (d.success) loadNotifications(); })
        .catch(console.error);
    });

    window.viewNotification = function (id) {
        fetch(`/notifications/${id}/json`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showNotificationModal(data.notification);
                loadNotifications();
            }
        })
        .catch(err => console.error('View notif error:', err));
    };

    function showNotificationModal(n) {
        $('#notificationModal').remove();

        const severityColor = { critical:'danger', high:'warning', medium:'info', low:'secondary' }[n.severity] || 'primary';

        $('body').append(`
            <div class="modal fade" id="notificationModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-${severityColor} text-white">
                            <h5 class="modal-title">${escapeHtml(n.title)}</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p class="mb-3">${escapeHtml(n.message)}</p>
                            <small class="text-muted"><i class="fas fa-clock me-1"></i>${escapeHtml(n.time_ago)}</small>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>`);

        const modal = new bootstrap.Modal(document.getElementById('notificationModal'));
        modal.show();
        $('#notificationModal').on('hidden.bs.modal', function () { $(this).remove(); });
    }

    // ════════════════════════════════════════════════════════════
    // DARK MODE
    // ════════════════════════════════════════════════════════════
    $('.dark-mode').on('click', function () {
        $('body').toggleClass('dark');
        localStorage.setItem('darkMode', $('body').hasClass('dark'));
    });
    
    if (localStorage.getItem('darkMode') === 'true') {
        $('body').addClass('dark');
    }

    // ════════════════════════════════════════════════════════════
    // SHARED UTILITIES
    // ════════════════════════════════════════════════════════════
    function escapeHtml(text) {
        if (!text) return '';
        const d = document.createElement('div');
        d.textContent = String(text);
        return d.innerHTML;
    }

    // ── Init ─────────────────────────────────────────────────────
    loadNotifications();
    setInterval(() => { if (document.hasFocus()) loadNotifications(); }, 30000);
});
</script>
@endpush