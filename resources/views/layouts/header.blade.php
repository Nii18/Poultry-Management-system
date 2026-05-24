<header class="page-header row">
    <div class="logo-wrapper d-flex align-items-center col-auto">
        <a href="{{ route('dashboard') }}" class="poultry-logo-link">
            <div class="poultry-logo-wrap">
                <div class="poultry-icon-ring">
                    <span class="poultry-icon visible" id="pl-cur">🐔</span>
                    <span class="poultry-icon hidden" id="pl-next">🥚</span>
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
        <!-- System Name Centered -->
        <div class="header-center text-center">
            <h4 class="mb-0 f-w-700 system-title">Poultry Management System</h4>
            <p class="mb-0 small text-muted">Enterprise Poultry Farm Solution</p>
        </div>

        <div class="header-left d-flex align-items-center">
           <!-- Search Form - Using working dropdown pattern like pharmacy -->
<li class="app-search dropdown me-3" style="list-style: none;">
    <form action="{{ route('search') }}" method="GET">
        @csrf
        <div class="input-group">
            <span class="input-group-text bg-white border-end-0">
                <i class="fas fa-search text-muted"></i>
            </span>
            <input 
                type="search" 
                name="query" 
                class="form-control border-start-0" 
                placeholder="Search flocks, houses, treatments, expenses..." 
                id="top-search" 
                value="{{ request('query') }}" 
                autocomplete="off"
                style="width: 300px;">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i> Search
            </button>
        </div>
    </form>
    <div class="dropdown-menu dropdown-menu-animated dropdown-lg" id="search-dropdown">
        <div class="dropdown-header noti-title">
            <h5 class="text-overflow mb-2" id="search-results-count">Start typing to search...</h5>
        </div>
        <div id="search-results"></div>
    </div>
</li>
        </div>

        <div class="nav-right">
            <ul class="header-right">
                @php
                    $userRole = auth()->check() ? auth()->user()->role : null;
                    $roleBadgeClass = match($userRole) {
                        'admin' => 'badge-danger',
                        'manager' => 'badge-warning',
                        'head_worker' => 'badge-info',
                        'worker' => 'badge-primary',
                        'veterinarian' => 'badge-success',
                        'accountant' => 'badge-secondary',
                        default => 'badge-light'
                    };
                    
                    $roleDisplay = match($userRole) {
                        'admin' => 'Administrator',
                        'manager' => 'Farm Manager',
                        'head_worker' => 'Head Worker',
                        'worker' => 'Farm Worker',
                        'veterinarian' => 'Veterinarian',
                        'accountant' => 'Accountant',
                        default => 'Staff'
                    };
                @endphp

                <!-- Dark Mode -->
                <li>
                    <a class="dark-mode" href="javascript:void(0)" title="Dark/Light Mode">
                        <svg>
                            <use href="{{ asset('assets/svg/iconly-sprite.svg#moondark') }}"></use>
                        </svg>
                    </a>
                </li>

                <!-- AUDIT LOGS (Admin/Manager only) -->
                @if(in_array($userRole, ['admin', 'manager']))
                <li class="custom-dropdown">
                    <a href="{{ route('audit-logs.index') }}" title="Audit Logs">
                        <i class="fas fa-history fa-lg"></i>
                    </a>
                </li>
                @endif

                <!-- Quick Actions - Using Modals -->
                <li class="custom-dropdown">
                    <a href="javascript:void(0)" title="Quick Actions">
                        <svg>
                            <use href="{{ asset('assets/svg/iconly-sprite.svg#Document') }}"></use>
                        </svg>
                    </a>
                    <div class="custom-menu cart-dropdown py-0 overflow-hidden" style="width: 250px;">
                        <h3 class="title dropdown-title">Quick Actions</h3>
                        <ul class="pb-0">
                            @if(in_array($userRole, ['admin', 'manager', 'head_worker', 'worker']))
                                <li>
                                    <a href="javascript:void(0)" onclick="openModal('createLogModal')" class="d-flex align-items-center">
                                        <i class="fas fa-clipboard-list me-2 text-primary"></i>
                                        <span>Add Daily Log</span>
                                    </a>
                                </li>
                            @endif
                            
                            @if(in_array($userRole, ['admin', 'manager', 'head_worker']))
                                <li>
                                    <a href="javascript:void(0)" onclick="openModal('createFlockModal')" class="d-flex align-items-center">
                                        <i class="fas fa-users me-2 text-success"></i>
                                        <span>Create New Flock</span>
                                    </a>
                                </li>
                            @endif
                            
                            @if(in_array($userRole, ['admin', 'manager']))
                                <li>
                                    <a href="javascript:void(0)" onclick="openModal('createDeliveryModal')" class="d-flex align-items-center">
                                        <i class="fas fa-truck me-2 text-warning"></i>
                                        <span>Record Feed Delivery</span>
                                    </a>
                                </li>
                            @endif
                            
                            @if(in_array($userRole, ['admin', 'manager', 'veterinarian']))
                                <li>
                                    <a href="javascript:void(0)" onclick="openModal('createTreatmentModal')" class="d-flex align-items-center">
                                        <i class="fas fa-stethoscope me-2 text-danger"></i>
                                        <span>Record Treatment</span>
                                    </a>
                                </li>
                            @endif
                            
                            @if(in_array($userRole, ['admin', 'manager', 'accountant']))
                                <li>
                                    <a href="javascript:void(0)" onclick="openModal('createExpenseModal')" class="d-flex align-items-center">
                                        <i class="fas fa-dollar-sign me-2 text-info"></i>
                                        <span>Add Expense</span>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </div>
                </li>

                <!-- Notifications -->
                <li class="custom-dropdown">
                    <a href="javascript:void(0)" id="notificationBell" title="Notifications">
                        <svg>
                            <use href="{{ asset('assets/svg/iconly-sprite.svg#notification') }}"></use>
                        </svg>
                    </a>

                    @php
                        use App\Models\Notification;

                        try {
                            $unreadCount = auth()->check() 
                                ? Notification::where('user_id', auth()->id())->whereNull('read_at')->count() 
                                : 0;

                            $recentNotifs = auth()->check() 
                                ? Notification::where('user_id', auth()->id())->latest()->take(5)->get() 
                                : collect();
                        } catch (\Exception $e) {
                            $unreadCount = 0;
                            $recentNotifs = collect();
                        }
                    @endphp

                    <span class="badge rounded-pill badge-primary notification-badge-header" 
                          id="notificationBadge"
                          style="{{ $unreadCount == 0 ? 'display:none;' : '' }}">
                        {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                    </span>

                    <div class="custom-menu notification-dropdown py-0 overflow-hidden" style="min-width: 350px;">
                        <h3 class="title bg-primary-light dropdown-title">
                            Notifications
                            <a href="{{ route('notifications.index') }}" class="float-end">View all</a>
                        </h3>

                        <ul class="activity-timeline" id="notificationList">
                            @forelse($recentNotifs as $notification)
                                <li class="notification-item" data-id="{{ $notification->id }}" onclick="viewNotification({{ $notification->id }})">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <strong>{{ $notification->title }}</strong>
                                            <p class="mb-0 small">{{ \Illuminate\Support\Str::limit($notification->message, 60) }}</p>
                                            <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                        </div>
                                        @if(!$notification->read_at)
                                            <span class="badge badge-primary badge-sm">New</span>
                                        @endif
                                    </div>
                                </li>
                            @empty
                                <li class="text-center py-3">
                                    <i class="fas fa-bell-slash text-muted"></i>
                                    <p class="mb-0">No notifications</p>
                                </li>
                            @endforelse
                        </ul>
                    </div>
                </li>

                <!-- User Profile -->
                <li class="profile-nav custom-dropdown">
                    <div class="user-wrap">
                        <div class="user-img">
                            @auth
                                @if(auth()->user()->avatar)
                                    <img src="{{ auth()->user()->avatar_url }}" alt="user">
                                @else
                                    <img src="{{ asset('assets/images/genericavatarimage.jpg') }}" alt="user">
                                @endif
                            @else
                                <img src="{{ asset('assets/images/genericavatarimage.jpg') }}" alt="user">
                            @endauth
                        </div>
                    </div>

                    <div class="custom-menu overflow-hidden" style="min-width: 220px;">
                        <ul class="profile-body">
                            <li class="d-flex align-items-center border-bottom mb-2 pb-2">
                            
                            </li>

                            <li class="d-flex align-items-center">
                                <i class="fas fa-user me-2"></i>
                                <a href="{{ route('account.edit') }}">Account</a>
                            </li>

                            <li class="d-flex align-items-center">
                                <i class="fas fa-lock me-2"></i>
                                <a href="{{ route('account.password.edit') }}">Change Password</a>
                            </li>

                            @if(in_array($userRole, ['admin', 'manager']))
                            <li class="d-flex align-items-center">
                                <i class="fas fa-exchange-alt me-2"></i>
                                <a href="{{ route('user.switch') }}">Switch User</a>
                            </li>
                            @endif

                            @if(Auth::user()->role === 'admin')
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
        </div>
    </div>
</header>

@push('styles')
<style>
    .page-main-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
        position: relative;
    }
    
    /* Center the title */
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
    
    /* Left section - search */
    .header-left {
        flex-shrink: 0;
        z-index: 100;
    }
    
    /* Right section - nav icons */
    .nav-right {
        flex-shrink: 0;
        z-index: 2;
    }
    
    .header-right {
        display: flex;
        align-items: center;
        gap: 12px;
        margin: 0;
        padding: 0;
        list-style: none;
    }
    
    .header-right > li {
        display: flex;
        align-items: center;
        position: relative;
    }
    
    /* Search Dropdown Styles */
    .app-search {
        position: relative;
    }
    
    .dropdown-lg {
        min-width: 400px;
    }
    
    .dropdown-menu {
        position: absolute;
        top: 100%;
        left: 0;
        z-index: 1000;
        display: none;
        float: left;
        min-width: 10rem;
        padding: 0.5rem 0;
        margin: 0.125rem 0 0;
        font-size: 0.875rem;
        color: #212529;
        text-align: left;
        list-style: none;
        background-color: #fff;
        background-clip: padding-box;
        border: 1px solid rgba(0,0,0,.15);
        border-radius: 0.25rem;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    
    .dropdown-menu.show {
        display: block;
    }
    
    .dropdown-header {
        display: block;
        padding: 0.5rem 1rem;
        margin-bottom: 0;
        font-size: 0.75rem;
        color: #6c757d;
        white-space: nowrap;
    }
    
    .dropdown-item {
        display: block;
        width: 100%;
        padding: 0.5rem 1rem;
        clear: both;
        font-weight: 400;
        color: #212529;
        text-align: inherit;
        text-decoration: none;
        white-space: nowrap;
        background-color: transparent;
        border: 0;
    }
    
    .dropdown-item:hover {
        background-color: #f8f9fa;
    }
    
    .search-result-item {
        display: flex;
        align-items: center;
        padding: 10px 15px;
        text-decoration: none;
        color: #333;
        border-bottom: 1px solid #f0f0f0;
        transition: background-color 0.2s;
        cursor: pointer;
    }
    
    .search-result-item:hover {
        background-color: #f8f9fa;
    }
    
    .result-icon {
        width: 35px;
        height: 35px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        margin-right: 12px;
    }
    
    .result-content {
        flex: 1;
    }
    
    .result-title {
        font-weight: 600;
        margin-bottom: 2px;
        font-size: 0.9rem;
    }
    
    .result-subtitle {
        font-size: 0.7rem;
        color: #6c757d;
    }
    
    .result-arrow {
        color: #adb5bd;
        font-size: 12px;
    }
    
    /* Notification Badge */
    .notification-badge-header {
        position: absolute;
        top: -8px;
        right: -8px;
        font-size: 0.6rem;
        padding: 2px 5px;
    }
    
    .notification-item {
        cursor: pointer;
        transition: background-color 0.2s;
        padding: 10px;
        list-style: none;
    }
    
    .notification-item:hover {
        background-color: #f8f9fa;
    }
    
    .badge-sm {
        font-size: 0.65rem;
        padding: 2px 6px;
    }
    
    /* Modal detail styles */
    .detail-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1rem;
    }
    
    .detail-item {
        display: flex;
        flex-direction: column;
    }
    
    .detail-label {
        font-size: 0.7rem;
        text-transform: uppercase;
        color: #64748b;
        font-weight: 600;
        margin-bottom: 0.25rem;
    }
    
    .detail-value {
        font-size: 0.95rem;
        font-weight: 500;
        color: #1e293b;
    }
    
    .badge-active { background: #dcfce7; color: #166534; padding: 5px 12px; border-radius: 20px; font-size: 0.75rem; }
    .badge-inactive { background: #fee2e2; color: #991b1b; padding: 5px 12px; border-radius: 20px; font-size: 0.75rem; }
    
    /* Dark mode */
    .dark .dropdown-menu {
        background: #1a1a2e;
        border-color: #2d2d44;
        color: #e0e0e0;
    }
    
    .dark .search-result-item {
        color: #e0e0e0;
        border-bottom-color: #2d2d44;
    }
    
    .dark .search-result-item:hover {
        background-color: #2d2d44;
    }
    
    .dark .dropdown-header {
        color: #adb5bd;
    }
    
    .dark .detail-value {
        color: #e0e0e0;
    }
    
    .dark .badge-active { background: #065f46; color: #dcfce7; }
    .dark .badge-inactive { background: #991b1b; color: #fee2e2; }
    
    /* Mobile responsive */
    @media (max-width: 992px) {
        .header-center h4 {
            font-size: 0.9rem;
        }
        .header-center p {
            display: none;
        }
        .dropdown-lg {
            min-width: 300px;
            right: 0;
            left: auto;
        }
        #top-search {
            width: 220px !important;
        }
    }
    
    @media (max-width: 768px) {
        #top-search {
            width: 180px !important;
        }
        .header-center h4 {
            font-size: 0.75rem;
        }
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // ========== SEARCH FUNCTIONALITY ==========
    let searchTimeout;
    const searchInput = $('#top-search');
    const searchDropdown = $('#search-dropdown');
    const searchResults = $('#search-results');
    const resultsCount = $('#search-results-count');
    
    // Close dropdown when clicking outside
    $(document).on('click', function(e) {
        if (!searchDropdown.is(e.target) && !searchInput.is(e.target) && !searchInput.parent().is(e.target) && searchDropdown.has(e.target).length === 0) {
            searchDropdown.removeClass('show');
        }
    });
    
    searchInput.on('focus', function() {
        const query = $(this).val().trim();
        if (query.length >= 2) {
            performSearch(query);
        } else {
            resultsCount.text('Start typing to search...');
            searchResults.html('');
            searchDropdown.addClass('show');
        }
    });
    
    searchInput.on('input', function() {
        clearTimeout(searchTimeout);
        const query = $(this).val().trim();
        
        if (query.length < 2) {
            resultsCount.text('Start typing to search...');
            searchResults.html('');
            searchDropdown.addClass('show');
            return;
        }
        
        searchTimeout = setTimeout(() => {
            performSearch(query);
        }, 300);
    });
    
    function performSearch(query) {
        resultsCount.text('Searching...');
        searchResults.html('<div class="text-center p-3"><i class="fas fa-spinner fa-spin"></i> Searching...</div>');
        searchDropdown.addClass('show');
        
        $.ajax({
            url: '/api/search',
            method: 'GET',
            data: { query: query },
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                displaySearchResults(response, query);
            },
            error: function(xhr) {
                console.error('Search error:', xhr);
                resultsCount.text('Error searching');
                searchResults.html('<div class="text-center p-3 text-danger">Error occurred. Please try again.</div>');
            }
        });
    }
    
    function displaySearchResults(data, query) {
        if (data.total === 0) {
            resultsCount.text('No results found');
            searchResults.html('<div class="text-center p-3 text-muted">No results found for "' + escapeHtml(query) + '"</div>');
            return;
        }
        
        resultsCount.text(data.total + ' result(s) found');
        
        let html = '';
        
        // Flocks
        if (data.flocks && data.flocks.length > 0) {
            html += '<div class="dropdown-header bg-light fw-bold">🐔 FLOCKS & HERDS</div>';
            data.flocks.forEach(item => {
                html += `
                    <div class="search-result-item" onclick="showFlockDetailsFromSearch(${item.id})" style="cursor: pointer;">
                        <div class="result-icon" style="background: rgba(13,110,253,0.1); color: #0d6efd;">
                            <i class="fas ${item.icon}"></i>
                        </div>
                        <div class="result-content">
                            <div class="result-title">${escapeHtml(item.name)}</div>
                            <div class="result-subtitle">${escapeHtml(item.subtitle)}</div>
                        </div>
                        <div class="result-arrow">
                            <i class="fas fa-chevron-right"></i>
                        </div>
                    </div>
                `;
            });
        }
        
        // Houses
        if (data.houses && data.houses.length > 0) {
            html += '<div class="dropdown-header bg-light fw-bold mt-1">🏠 HOUSES</div>';
            data.houses.forEach(item => {
                html += `
                    <div class="search-result-item" onclick="showHouseDetailsFromSearch(${item.id})" style="cursor: pointer;">
                        <div class="result-icon" style="background: rgba(25,135,84,0.1); color: #198754;">
                            <i class="fas ${item.icon}"></i>
                        </div>
                        <div class="result-content">
                            <div class="result-title">${escapeHtml(item.name)}</div>
                            <div class="result-subtitle">${escapeHtml(item.subtitle)}</div>
                        </div>
                        <div class="result-arrow">
                            <i class="fas fa-chevron-right"></i>
                        </div>
                    </div>
                `;
            });
        }
        
        // Treatments
        if (data.treatments && data.treatments.length > 0) {
            html += '<div class="dropdown-header bg-light fw-bold mt-1">💊 TREATMENTS</div>';
            data.treatments.forEach(item => {
                html += `
                    <div class="search-result-item" onclick="showTreatmentDetailsFromSearch(${item.id})" style="cursor: pointer;">
                        <div class="result-icon" style="background: rgba(220,53,69,0.1); color: #dc3545;">
                            <i class="fas ${item.icon}"></i>
                        </div>
                        <div class="result-content">
                            <div class="result-title">${escapeHtml(item.name)}</div>
                            <div class="result-subtitle">${escapeHtml(item.subtitle)}</div>
                        </div>
                        <div class="result-arrow">
                            <i class="fas fa-chevron-right"></i>
                        </div>
                    </div>
                `;
            });
        }
        
        // Daily Logs
        if (data.daily_logs && data.daily_logs.length > 0) {
            html += '<div class="dropdown-header bg-light fw-bold mt-1">📋 DAILY LOGS</div>';
            data.daily_logs.forEach(item => {
                html += `
                    <div class="search-result-item" onclick="showDailyLogDetailsFromSearch(${item.id})" style="cursor: pointer;">
                        <div class="result-icon" style="background: rgba(13,202,240,0.1); color: #0dcaf0;">
                            <i class="fas ${item.icon}"></i>
                        </div>
                        <div class="result-content">
                            <div class="result-title">${escapeHtml(item.name)}</div>
                            <div class="result-subtitle">${escapeHtml(item.subtitle)}</div>
                        </div>
                        <div class="result-arrow">
                            <i class="fas fa-chevron-right"></i>
                        </div>
                    </div>
                `;
            });
        }
        
        // Expenses
        if (data.expenses && data.expenses.length > 0) {
            html += '<div class="dropdown-header bg-light fw-bold mt-1">💰 EXPENSES</div>';
            data.expenses.forEach(item => {
                html += `
                    <div class="search-result-item" onclick="showExpenseDetailsFromSearch(${item.id})" style="cursor: pointer;">
                        <div class="result-icon" style="background: rgba(255,193,7,0.1); color: #ffc107;">
                            <i class="fas ${item.icon}"></i>
                        </div>
                        <div class="result-content">
                            <div class="result-title">${escapeHtml(item.name)}</div>
                            <div class="result-subtitle">${escapeHtml(item.subtitle)}</div>
                        </div>
                        <div class="result-arrow">
                            <i class="fas fa-chevron-right"></i>
                        </div>
                    </div>
                `;
            });
        }
        
        html += '<div class="dropdown-footer text-center p-2 border-top mt-1">';
        html += `<a href="/search?query=${encodeURIComponent(query)}" class="text-primary text-decoration-none">View all results →</a>`;
        html += '</div>';
        
        searchResults.html(html);
    }
    
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // ========== SEARCH MODAL FUNCTIONS ==========
    // Flock modal from search
    window.showFlockDetailsFromSearch = function(flockId) {
        $('#search-dropdown').removeClass('show');
        
        let modalElement = document.getElementById('viewFlockModal');
        
        if (modalElement) {
            const modal = new bootstrap.Modal(modalElement);
            const modalBody = document.getElementById('viewFlockContent');
            
            modalBody.innerHTML = `<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Loading flock details...</p></div>`;
            modal.show();
            
            fetch(`/flocks/${flockId}/details`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayFlockDetailsInModal(data.flock, data.summary);
                } else {
                    modalBody.innerHTML = `<div class="alert alert-danger">Failed to load flock details: ${data.message}</div>`;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                modalBody.innerHTML = `<div class="alert alert-danger">Error loading data</div>`;
            });
        } else {
            window.location.href = `/flocks/${flockId}`;
        }
    };
    
    // House modal from search
    window.showHouseDetailsFromSearch = function(houseId) {
        $('#search-dropdown').removeClass('show');
        
        let modalElement = document.getElementById('viewHouseModal');
        
        if (modalElement) {
            const modal = new bootstrap.Modal(modalElement);
            const modalBody = document.getElementById('viewHouseContent');
            
            modalBody.innerHTML = `<div class="text-center py-4"><div class="spinner-border text-success" role="status"></div><p class="mt-2">Loading house details...</p></div>`;
            modal.show();
            
            fetch(`/houses/${houseId}/details`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayHouseDetailsInModal(data.house, data.stats);
                } else {
                    modalBody.innerHTML = `<div class="alert alert-danger">Failed to load house details: ${data.message}</div>`;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                modalBody.innerHTML = `<div class="alert alert-danger">Error loading data</div>`;
            });
        } else {
            window.location.href = `/houses/${houseId}`;
        }
    };
    
    // Treatment modal from search
    window.showTreatmentDetailsFromSearch = function(treatmentId) {
        $('#search-dropdown').removeClass('show');
        
        let modalElement = document.getElementById('viewTreatmentModal');
        
        if (modalElement) {
            const modal = new bootstrap.Modal(modalElement);
            const modalBody = document.getElementById('viewTreatmentContent');
            
            modalBody.innerHTML = `<div class="text-center py-4"><div class="spinner-border text-danger" role="status"></div><p class="mt-2">Loading treatment details...</p></div>`;
            modal.show();
            
            fetch(`/treatments/${treatmentId}/details`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayTreatmentDetailsInModal(data.treatment);
                } else {
                    modalBody.innerHTML = `<div class="alert alert-danger">Failed to load treatment details: ${data.message}</div>`;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                modalBody.innerHTML = `<div class="alert alert-danger">Error loading data</div>`;
            });
        } else {
            window.location.href = `/treatments/${treatmentId}`;
        }
    };
    
    // Daily Log modal from search
    window.showDailyLogDetailsFromSearch = function(logId) {
        $('#search-dropdown').removeClass('show');
        
        let modalElement = document.getElementById('viewDailyLogModal');
        
        if (modalElement) {
            const modal = new bootstrap.Modal(modalElement);
            const modalBody = document.getElementById('viewDailyLogContent');
            
            modalBody.innerHTML = `<div class="text-center py-4"><div class="spinner-border text-info" role="status"></div><p class="mt-2">Loading log details...</p></div>`;
            modal.show();
            
            fetch(`/daily-logs/${logId}/details`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayDailyLogDetailsInModal(data.log);
                } else {
                    modalBody.innerHTML = `<div class="alert alert-danger">Failed to load log details: ${data.message}</div>`;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                modalBody.innerHTML = `<div class="alert alert-danger">Error loading data</div>`;
            });
        } else {
            window.location.href = `/daily-logs/${logId}`;
        }
    };
    
    // Expense modal from search
    window.showExpenseDetailsFromSearch = function(expenseId) {
        $('#search-dropdown').removeClass('show');
        
        let modalElement = document.getElementById('viewExpenseModal');
        
        if (modalElement) {
            const modal = new bootstrap.Modal(modalElement);
            const modalBody = document.getElementById('viewExpenseContent');
            
            modalBody.innerHTML = `<div class="text-center py-4"><div class="spinner-border text-warning" role="status"></div><p class="mt-2">Loading expense details...</p></div>`;
            modal.show();
            
            fetch(`/expenses/${expenseId}/details-json`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayExpenseDetailsInModal(data.expense);
                } else {
                    modalBody.innerHTML = `<div class="alert alert-danger">Failed to load expense details: ${data.message}</div>`;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                modalBody.innerHTML = `<div class="alert alert-danger">Error loading data</div>`;
            });
        } else {
            window.location.href = `/expenses/${expenseId}`;
        }
    };
    
    // Display functions for modal content
    function displayFlockDetailsInModal(flock, summary) {
        const modalBody = document.getElementById('viewFlockContent');
        
        modalBody.innerHTML = `
            <div class="detail-section">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h5 class="mb-1">${escapeHtml(flock.flock_number)}</h5>
                        <p class="text-muted mb-0">${escapeHtml(flock.breed_variety)}</p>
                    </div>
                    <span class="badge ${flock.status === 'active' ? 'badge-active' : 'badge-inactive'} px-3 py-2">${escapeHtml(flock.status || 'N/A')}</span>
                </div>
            </div>
            
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="text-center p-3 bg-light rounded">
                        <div class="fs-4 fw-bold text-primary">${summary.age_days}</div>
                        <small class="text-muted">Age (days)</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center p-3 bg-light rounded">
                        <div class="fs-4 fw-bold text-success">${summary.current_count.toLocaleString()}</div>
                        <small class="text-muted">Current Count</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center p-3 bg-light rounded">
                        <div class="fs-4 fw-bold text-danger">${summary.mortality_rate}%</div>
                        <small class="text-muted">Mortality Rate</small>
                    </div>
                </div>
            </div>
            
            <div class="detail-section">
                <h6>Basic Information</h6>
                <div class="detail-grid">
                    <div class="detail-item"><span class="detail-label">Species</span><span class="detail-value">${escapeHtml(flock.species_name)}</span></div>
                    <div class="detail-item"><span class="detail-label">House</span><span class="detail-value">${escapeHtml(flock.house_name)}</span></div>
                    <div class="detail-item"><span class="detail-label">Breed</span><span class="detail-value">${escapeHtml(flock.breed_variety)}</span></div>
                    <div class="detail-item"><span class="detail-label">Start Date</span><span class="detail-value">${flock.start_date}</span></div>
                    <div class="detail-item"><span class="detail-label">Initial Count</span><span class="detail-value">${flock.initial_count.toLocaleString()}</span></div>
                    <div class="detail-item"><span class="detail-label">Production Type</span><span class="detail-value">${escapeHtml(flock.production_type)}</span></div>
                </div>
            </div>
            
            ${flock.notes ? `<div class="detail-section"><h6>Notes</h6><p class="mb-0 p-3 bg-light rounded">${escapeHtml(flock.notes)}</p></div>` : ''}
        `;
    }
    
    function displayHouseDetailsInModal(house, stats) {
        const modalBody = document.getElementById('viewHouseContent');
        
        modalBody.innerHTML = `
            <div class="detail-section">
                <h5 class="mb-2">${escapeHtml(house.name)}</h5>
                <p class="text-muted">Code: ${escapeHtml(house.house_code)}</p>
            </div>
            
            <div class="detail-section">
                <h6>Basic Information</h6>
                <div class="detail-grid">
                    <div class="detail-item"><span class="detail-label">House Code</span><span class="detail-value">${escapeHtml(house.house_code)}</span></div>
                    <div class="detail-item"><span class="detail-label">Species</span><span class="detail-value">${escapeHtml(house.species_name || 'Not Assigned')}</span></div>
                    <div class="detail-item"><span class="detail-label">Status</span><span class="detail-value">${escapeHtml(house.status)}</span></div>
                    <div class="detail-item"><span class="detail-label">Capacity</span><span class="detail-value">${house.capacity.toLocaleString()}</span></div>
                    <div class="detail-item"><span class="detail-label">Total Flocks</span><span class="detail-value">${stats.total_flocks}</span></div>
                    <div class="detail-item"><span class="detail-label">Total Animals</span><span class="detail-value">${stats.total_animals.toLocaleString()}</span></div>
                </div>
            </div>
            
            ${house.notes ? `<div class="detail-section"><h6>Notes</h6><p class="mb-0 p-3 bg-light rounded">${escapeHtml(house.notes)}</p></div>` : ''}
        `;
    }
    
    function displayTreatmentDetailsInModal(treatment) {
        const modalBody = document.getElementById('viewTreatmentContent');
        
        modalBody.innerHTML = `
            <div class="detail-section">
                <h5 class="mb-1">${escapeHtml(treatment.medication_name)}</h5>
                <p class="text-muted">${escapeHtml(treatment.diagnosis)}</p>
            </div>
            
            <div class="detail-section">
                <h6>Treatment Information</h6>
                <div class="detail-grid">
                    <div class="detail-item"><span class="detail-label">Flock</span><span class="detail-value">${escapeHtml(treatment.flock_number)}</span></div>
                    <div class="detail-item"><span class="detail-label">Start Date</span><span class="detail-value">${treatment.start_date}</span></div>
                    <div class="detail-item"><span class="detail-label">End Date</span><span class="detail-value">${treatment.end_date}</span></div>
                    <div class="detail-item"><span class="detail-label">Dosage</span><span class="detail-value">${escapeHtml(treatment.dosage)}</span></div>
                    <div class="detail-item"><span class="detail-label">Route</span><span class="detail-value">${escapeHtml(treatment.administration_route)}</span></div>
                    <div class="detail-item"><span class="detail-label">Animals Treated</span><span class="detail-value">${treatment.animals_treated || 'N/A'}</span></div>
                </div>
            </div>
            
            ${treatment.notes ? `<div class="detail-section"><h6>Notes</h6><p class="mb-0 p-3 bg-light rounded">${escapeHtml(treatment.notes)}</p></div>` : ''}
        `;
    }
    
    function displayDailyLogDetailsInModal(log) {
        const modalBody = document.getElementById('viewDailyLogContent');
        
        modalBody.innerHTML = `
            <div class="detail-section">
                <h6>Log Information</h6>
                <div class="detail-grid">
                    <div class="detail-item"><span class="detail-label">Date</span><span class="detail-value">${log.log_date}</span></div>
                    <div class="detail-item"><span class="detail-label">Flock</span><span class="detail-value">${escapeHtml(log.flock_number)}</span></div>
                    <div class="detail-item"><span class="detail-label">Mortality</span><span class="detail-value">${log.mortality_count}</span></div>
                    <div class="detail-item"><span class="detail-label">Culling</span><span class="detail-value">${log.culling_count}</span></div>
                    <div class="detail-item"><span class="detail-label">Feed Intake</span><span class="detail-value">${log.feed_intake_kg} kg</span></div>
                    <div class="detail-item"><span class="detail-label">Water Consumption</span><span class="detail-value">${log.water_consumption_liters} L</span></div>
                    <div class="detail-item"><span class="detail-label">Avg Weight</span><span class="detail-value">${log.average_weight_kg} kg</span></div>
                    <div class="detail-item"><span class="detail-label">Temperature</span><span class="detail-value">${log.min_temperature_c}°C - ${log.max_temperature_c}°C</span></div>
                </div>
            </div>
            
            ${log.notes ? `<div class="detail-section"><h6>Notes</h6><p class="mb-0 p-3 bg-light rounded">${escapeHtml(log.notes)}</p></div>` : ''}
        `;
    }
    
    function displayExpenseDetailsInModal(expense) {
        const modalBody = document.getElementById('viewExpenseContent');
        
        modalBody.innerHTML = `
            <div class="detail-section">
                <h6>Expense Information</h6>
                <div class="detail-grid">
                    <div class="detail-item"><span class="detail-label">Date</span><span class="detail-value">${expense.expense_date}</span></div>
                    <div class="detail-item"><span class="detail-label">Category</span><span class="detail-value">${escapeHtml(expense.category)}</span></div>
                    <div class="detail-item"><span class="detail-label">Description</span><span class="detail-value">${escapeHtml(expense.description)}</span></div>
                    <div class="detail-item"><span class="detail-label">Amount</span><span class="detail-value text-danger fw-bold">₵${parseFloat(expense.amount).toLocaleString()}</span></div>
                    <div class="detail-item"><span class="detail-label">Vendor</span><span class="detail-value">${escapeHtml(expense.vendor_name || 'N/A')}</span></div>
                    <div class="detail-item"><span class="detail-label">Payment Method</span><span class="detail-value">${escapeHtml(expense.payment_method || 'N/A')}</span></div>
                    <div class="detail-item"><span class="detail-label">Associated Flock</span><span class="detail-value">${escapeHtml(expense.flock_number || 'None')}</span></div>
                </div>
            </div>
            
            ${expense.notes ? `<div class="detail-section"><h6>Notes</h6><p class="mb-0 p-3 bg-light rounded">${escapeHtml(expense.notes)}</p></div>` : ''}
        `;
    }
    
    // ========== MODAL FUNCTIONS ==========
    window.openModal = function(modalId) {
        let modalElement = document.getElementById(modalId);
        
        if (modalElement) {
            const modal = new bootstrap.Modal(modalElement);
            modal.show();
        } else {
            let targetUrl = '';
            switch(modalId) {
                case 'createLogModal':
                    targetUrl = '{{ route("daily-logs.index") }}';
                    break;
                case 'createFlockModal':
                    targetUrl = '{{ route("flocks.index") }}';
                    break;
                case 'createDeliveryModal':
                    targetUrl = '{{ route("feed-deliveries.index") }}';
                    break;
                case 'createTreatmentModal':
                    targetUrl = '{{ route("treatments.index") }}';
                    break;
                case 'createExpenseModal':
                    targetUrl = '{{ route("expenses.index") }}';
                    break;
                default:
                    targetUrl = '{{ route("dashboard") }}';
            }
            sessionStorage.setItem('openModalOnLoad', modalId);
            window.location.href = targetUrl;
        }
    };
    
    // Check and open modal on page load
    function checkAndOpenModalOnLoad() {
        const modalToOpen = sessionStorage.getItem('openModalOnLoad');
        if (modalToOpen) {
            sessionStorage.removeItem('openModalOnLoad');
            setTimeout(() => {
                const modalElement = document.getElementById(modalToOpen);
                if (modalElement) {
                    const modal = new bootstrap.Modal(modalElement);
                    modal.show();
                }
            }, 500);
        }
    }
    
    // ========== NOTIFICATIONS ==========
    function loadNotifications() {
        fetch('{{ route("api.notifications") }}', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateNotificationBadge(data.unread_count);
                updateNotificationList(data.notifications);
            }
        })
        .catch(error => console.error('Error loading notifications:', error));
    }
    
    function updateNotificationBadge(count) {
        const badge = document.getElementById('notificationBadge');
        if (badge) {
            if (count > 0) {
                badge.textContent = count > 99 ? '99+' : count;
                badge.style.display = 'inline-block';
            } else {
                badge.style.display = 'none';
            }
        }
    }
    
    function updateNotificationList(notifications) {
        const list = document.getElementById('notificationList');
        if (!list) return;
        
        if (!notifications || notifications.length === 0) {
            list.innerHTML = '<li class="text-center py-3"><i class="fas fa-bell-slash text-muted"></i><p class="mb-0">No notifications</p></li>';
            return;
        }
        
        let html = '';
        notifications.forEach(notif => {
            html += `
                <li class="notification-item" data-id="${notif.id}" onclick="viewNotification(${notif.id})">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <strong>${escapeHtml(notif.title)}</strong>
                            <p class="mb-0 small">${escapeHtml(notif.message.substring(0, 60))}</p>
                            <small class="text-muted">${notif.time_ago}</small>
                        </div>
                        ${!notif.read_at ? '<span class="badge badge-primary badge-sm">New</span>' : ''}
                    </div>
                </li>
            `;
        });
        
        list.innerHTML = html;
    }
    
    window.viewNotification = function(id) {
        fetch(`/notifications/${id}/json`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotificationModal(data.notification);
                loadNotifications();
            }
        })
        .catch(error => console.error('Error viewing notification:', error));
    };
    
    function showNotificationModal(notification) {
        const modalHtml = `
            <div class="modal fade" id="notificationModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title">${escapeHtml(notification.title)}</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p>${escapeHtml(notification.message)}</p>
                            <small class="text-muted">Received: ${notification.time_ago}</small>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        const existingModal = document.getElementById('notificationModal');
        if (existingModal) existingModal.remove();
        
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        const modal = new bootstrap.Modal(document.getElementById('notificationModal'));
        modal.show();
        
        document.getElementById('notificationModal').addEventListener('hidden.bs.modal', function() {
            this.remove();
        });
    }
    
    // ========== DARK MODE ==========
    const darkModeBtn = document.querySelector('.dark-mode');
    if (darkModeBtn) {
        darkModeBtn.addEventListener('click', function() {
            document.body.classList.toggle('dark');
            localStorage.setItem('darkMode', document.body.classList.contains('dark'));
        });
    }
    
    if (localStorage.getItem('darkMode') === 'true') {
        document.body.classList.add('dark');
    }
    
    // Initialize
    checkAndOpenModalOnLoad();
    loadNotifications();
    
    // Poll for notifications every 30 seconds
    setInterval(() => {
        if (document.hasFocus()) {
            loadNotifications();
        }
    }, 30000);
});
</script>
@endpush