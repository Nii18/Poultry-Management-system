<aside class="page-sidebar"> 
  <div class="left-arrow" id="left-arrow">
    <i data-feather="arrow-left"></i>
  </div>
  <div class="main-sidebar" id="main-sidebar">
    <ul class="sidebar-menu" id="simple-bar">
      
      <!-- Dashboard - Everyone sees this -->
      <li class="sidebar-list">
        <i class="fa-solid fa-thumbtack"></i>
        <a class="sidebar-link" href="{{ route('dashboard') }}">
          <svg class="stroke-icon">
            <use href="{{asset('assets/svg/iconly-sprite.svg#Home-dashboard')}}"></use>
          </svg>
          <h6>Dashboard</h6>
        </a>
      </li>

      <!-- ==================== ADMIN & MANAGER ONLY ==================== -->
      @if(in_array(auth()->user()->role ?? '', ['admin','manager']))
      
      <li class="sidebar-main-title">
        <h5 class="sidebar-title f-w-700">Farm Management</h5>
      </li>

      <li class="sidebar-list has-submenu">
        <i class="fa-solid fa-thumbtack"></i>
        <a class="sidebar-link" href="javascript:void(0)">
          <svg class="stroke-icon">
            <use href="{{asset('assets/svg/iconly-sprite.svg#Document')}}"></use>
          </svg>
          <h6>Flocks & Herds</h6>
          <i class="fa-solid fa-chevron-right submenu-arrow"></i>
        </a>
        <ul class="sidebar-submenu">
          <li><a href="{{ route('flocks.index') }}">All Flocks</a></li>
          <li><a href="javascript:void(0)" onclick="redirectAndOpenModal('flocks.index', 'createFlockModal')">Add New Flock</a></li>
          <li><a href="{{ route('flocks.index',['status'=>'active']) }}">Active</a></li>
          <li><a href="{{ route('flocks.index',['status'=>'closed']) }}">Closed</a></li>
        </ul>
      </li>

      <li class="sidebar-list has-submenu">
        <i class="fa-solid fa-thumbtack"></i>
        <a class="sidebar-link" href="javascript:void(0)">
          <svg class="stroke-icon">
            <use href="{{asset('assets/svg/iconly-sprite.svg#Home')}}"></use>
          </svg>
          <h6>Housing</h6>
          <i class="fa-solid fa-chevron-right submenu-arrow"></i>
        </a>
        <ul class="sidebar-submenu">
          <li><a href="{{ route('houses.index') }}">All Houses</a></li>
          <li><a href="javascript:void(0)" onclick="redirectAndOpenModal('houses.index', 'createHouseModal')">Add House</a></li>
          <li><a href="{{ route('houses.occupancy-report') }}">Occupancy</a></li>
        </ul>
      </li>

      <li class="sidebar-list">
        <i class="fa-solid fa-thumbtack"></i>
        <a class="sidebar-link" href="{{ route('species.index') }}">
          <svg class="stroke-icon">
            <use href="{{asset('assets/svg/iconly-sprite.svg#Category')}}"></use>
          </svg>
          <h6>Species</h6>
        </a>
      </li>
      @endif

      <!-- ==================== OPERATIONS (Admin, Manager, Worker) ==================== -->
      <li class="sidebar-main-title">
        <h5 class="sidebar-title f-w-700">Operations</h5>
      </li>

      @if(in_array(auth()->user()->role ?? '', ['admin','manager','worker']))
      <li class="sidebar-list has-submenu">
        <i class="fa-solid fa-thumbtack"></i>
        <a class="sidebar-link" href="javascript:void(0)">
          <svg class="stroke-icon">
            <use href="{{asset('assets/svg/iconly-sprite.svg#Clipboard')}}"></use>
          </svg>
          <h6>Daily Operations</h6>
          <i class="fa-solid fa-chevron-right submenu-arrow"></i>
        </a>
        <ul class="sidebar-submenu">
          <li><a href="javascript:void(0)" onclick="redirectAndOpenModal('daily-logs.index', 'createLogModal')">Quick Log</a></li>
          <li><a href="{{ route('daily-logs.index') }}">All Logs</a></li>
          <li><a href="javascript:void(0)" onclick="redirectAndOpenModal('feed-issuances.index', 'createFeedIssuanceModal')">Feed Issuance</a></li>
        </ul>
      </li>
      @endif
      
{{-- For workers + managers: record produce --}}
@if(in_array(auth()->user()->role ?? '', ['admin','manager','worker']))
<li class="sidebar-list">
    <i class="fa-solid fa-thumbtack"></i>
    <a class="sidebar-link" href="{{ route('produces.index') }}">
        <svg class="stroke-icon">
            <use href="{{asset('assets/svg/iconly-sprite.svg#Bag')}}"></use>
        </svg>
        <h6>Produce Records</h6>
    </a>
</li>
@endif
 
{{-- For admin, manager, accountant: inventory summary --}}
@if(in_array(auth()->user()->role ?? '', ['admin','manager','accountant']))
<li class="sidebar-list">
    <i class="fa-solid fa-thumbtack"></i>
    <a class="sidebar-link" href="{{ route('produces.inventory') }}">
        <svg class="stroke-icon">
            <use href="{{asset('assets/svg/iconly-sprite.svg#Chart')}}"></use>
        </svg>
        <h6>Produce Inventory</h6>
    </a>
</li>
@endif
      

      <!-- Feed Management (Admin & Manager only) -->
      @if(in_array(auth()->user()->role ?? '', ['admin','manager']))
      <li class="sidebar-list has-submenu">
        <i class="fa-solid fa-thumbtack"></i>
        <a class="sidebar-link" href="javascript:void(0)">
          <svg class="stroke-icon">
            <use href="{{asset('assets/svg/iconly-sprite.svg#Bag')}}"></use>
          </svg>
          <h6>Feed Management</h6>
          <i class="fa-solid fa-chevron-right submenu-arrow"></i>
        </a>
        <ul class="sidebar-submenu">
          <li><a href="{{ route('feed-types.index') }}">Feed Types</a></li>
          <li><a href="{{ route('feed-deliveries.index') }}">Deliveries</a></li>
          <li><a href="{{ route('feed-issuances.index') }}">Consumption</a></li>
          <li><a href="{{ route('feed-deliveries.low-stock') }}">Low Stock</a></li>
        </ul>
      </li>
      @endif

      <!-- ==================== HEALTH (Admin, Manager, Veterinarian) ==================== -->
      @if(in_array(auth()->user()->role ?? '', ['admin','manager','veterinarian']))
      <li class="sidebar-list has-submenu">
        <i class="fa-solid fa-thumbtack"></i>
        <a class="sidebar-link" href="javascript:void(0)">
          <svg class="stroke-icon">
            <use href="{{asset('assets/svg/iconly-sprite.svg#Activity')}}"></use>
          </svg>
          <h6>Health</h6>
          <i class="fa-solid fa-chevron-right submenu-arrow"></i>
        </a>
        <ul class="sidebar-submenu">
          <li><a href="{{ route('vaccinations.index') }}">Vaccinations</a></li>
          <li><a href="{{ route('treatments.index') }}">Treatments</a></li>
          <li><a href="{{ route('health-records.index') }}">Records</a></li>
          <li><a href="{{ route('treatments.withdrawal-alerts') }}">Alerts</a></li>
        </ul>
      </li>
      @endif

      <!-- ==================== BREEDING (Admin, Manager, Worker) ==================== -->
      @if(in_array(auth()->user()->role ?? '', ['admin','manager','worker']))
      <li class="sidebar-list has-submenu">
        <i class="fa-solid fa-thumbtack"></i>
        <a class="sidebar-link" href="javascript:void(0)">
          <svg class="stroke-icon">
            <use href="{{asset('assets/svg/iconly-sprite.svg#Heart')}}"></use>
          </svg>
          <h6>Breeding</h6>
          <i class="fa-solid fa-chevron-right submenu-arrow"></i>
        </a>
        <ul class="sidebar-submenu">
          <li><a href="{{ route('breeding-records.index') }}">Records</a></li>
          <li><a href="javascript:void(0)" onclick="redirectAndOpenModal('breeding-records.index', 'createBreedingModal')">Add</a></li>
          <li><a href="{{ route('breeding-records.pending') }}">Pending</a></li>
        </ul>
      </li>
      @endif

      <!-- ==================== FINANCE (Admin, Manager, Accountant) ==================== -->
      @if(in_array(auth()->user()->role ?? '', ['admin','manager','accountant']))
      
      <li class="sidebar-main-title">
        <h5 class="sidebar-title f-w-700">Finance</h5>
      </li>

      <li class="sidebar-list has-submenu">
        <i class="fa-solid fa-thumbtack"></i>
        <a class="sidebar-link" href="javascript:void(0)">
          <svg class="stroke-icon">
            <use href="{{asset('assets/svg/iconly-sprite.svg#Wallet')}}"></use>
          </svg>
          <h6>Expenses</h6>
          <i class="fa-solid fa-chevron-right submenu-arrow"></i>
        </a>
        <ul class="sidebar-submenu">
          <li><a href="{{ route('expenses.index') }}">All Expenses</a></li>
          <li><a href="javascript:void(0)" onclick="redirectAndOpenModal('expenses.index', 'createExpenseModal')">Add Expense</a></li>
          <li><a href="{{ route('expenses.by-category') }}">Category Analysis</a></li>
        </ul>
      </li>

      <li class="sidebar-list has-submenu">
        <i class="fa-solid fa-thumbtack"></i>
        <a class="sidebar-link" href="javascript:void(0)">
          <svg class="stroke-icon">
            <use href="{{asset('assets/svg/iconly-sprite.svg#Chart')}}"></use>
          </svg>
          <h6>Sales & Revenue</h6>
          <i class="fa-solid fa-chevron-right submenu-arrow"></i>
        </a>
        <ul class="sidebar-submenu">
          <li><a href="{{ route('sales.index') }}">All Sales</a></li>
          <li><a href="{{ route('sales.by-product') }}">Product Analysis</a></li>
        </ul>
      </li>

      <li class="sidebar-list">
        <i class="fa-solid fa-thumbtack"></i>
        <a class="sidebar-link" href="javascript:void(0)" onclick="openProfitLossModal()">
          <svg class="stroke-icon">
            <use href="{{asset('assets/svg/iconly-sprite.svg#Activity')}}"></use>
          </svg>
          <h6>Profit & Loss</h6>
        </a>
      </li>

      <li class="sidebar-list">
        <i class="fa-solid fa-thumbtack"></i>
        <a class="sidebar-link" href="{{ route('reports.financial') }}">
          <svg class="stroke-icon">
            <use href="{{asset('assets/svg/iconly-sprite.svg#Activity')}}"></use>
          </svg>
          <h6>Financial Reports</h6>
        </a>
      </li>
      @endif

      <!-- ==================== REPORTS & ANALYTICS ==================== -->
      @if(in_array(auth()->user()->role ?? '', ['admin','manager','accountant','veterinarian']))
      
      <li class="sidebar-main-title">
        <h5 class="sidebar-title f-w-700">Reports & Analytics</h5>
      </li>

      <li class="sidebar-list has-submenu">
        <i class="fa-solid fa-thumbtack"></i>
        <a class="sidebar-link" href="javascript:void(0)">
          <svg class="stroke-icon">
            <use href="{{asset('assets/svg/iconly-sprite.svg#Chart')}}"></use>
          </svg>
          <h6>Reports</h6>
          <i class="fa-solid fa-chevron-right submenu-arrow"></i>
        </a>
        <ul class="sidebar-submenu">
          <li><a href="{{ route('reports.performance') }}">Performance</a></li>
          <li><a href="{{ route('reports.health') }}">Health</a></li>
          <li><a href="{{ route('analytics.index') }}">Analytics</a></li>
        </ul>
      </li>
      @endif

    <!-- ==================== NOTIFICATIONS ==================== -->
<li class="sidebar-list">
  <i class="fa-solid fa-thumbtack"></i>
  <a class="sidebar-link" href="{{ route('notifications.index') }}" id="sidebarNotificationsLink">
      <svg class="stroke-icon">
          <use href="{{asset('assets/svg/iconly-sprite.svg#Message')}}"></use>
      </svg>
      <h6>Notifications</h6>
      @php
          $user = auth()->user();
          $sidebarUnreadCount = \App\Models\Notification::where('user_id', $user->id)
              ->whereNull('read_at')
              ->count();
      @endphp
      @if($sidebarUnreadCount > 0)
          <span class="sidebar-notification-badge" id="sidebarNotificationBadge">{{ $sidebarUnreadCount > 99 ? '99+' : $sidebarUnreadCount }}</span>
      @endif
  </a>
</li>

   <!-- ==================== WORKER ONLY (Below Notifications - Less Vital) ==================== -->
@if(in_array(auth()->user()->role ?? '', ['admin','worker']))
<li class="sidebar-main-title">
    <h5 class="sidebar-title f-w-700">My Tools</h5>
</li>

<!-- My Tasks -->
<li class="sidebar-list">
    <i class="fa-solid fa-thumbtack"></i>
    <a class="sidebar-link" href="{{ route('worker.tasks') }}">
        <svg class="stroke-icon">
            <use href="{{ asset('assets/svg/iconly-sprite.svg#Document') }}"></use>
        </svg>
        <h6>My Tasks</h6>
    </a>
</li>

<!-- My Attendance -->
<li class="sidebar-list">
    <i class="fa-solid fa-thumbtack"></i>
    <a class="sidebar-link" href="{{ route('worker.attendance') }}">
        <svg class="stroke-icon">
            <use href="{{ asset('assets/svg/iconly-sprite.svg#Calendar') }}"></use>
        </svg>
        <h6>My Attendance</h6>
    </a>
</li>

<!-- Help & Tips -->
<li class="sidebar-list">
    <i class="fa-solid fa-thumbtack"></i>
    <a class="sidebar-link" href="{{ route('worker.help') }}">
        <svg class="stroke-icon">
            <use href="{{ asset('assets/svg/iconly-sprite.svg#Info-circle') }}"></use>
        </svg>
        <h6>Help & Tips</h6>
    </a>
</li>
@endif

<!-- ==================== CLINICAL TOOLS ==================== -->
@if(in_array(auth()->user()->role ?? '', ['admin','veterinarian']))
<li class="sidebar-main-title">
    <h5 class="sidebar-title f-w-700">Clinical Tools</h5>
</li>

<li class="sidebar-list">
    <i class="fa-solid fa-thumbtack"></i>
    <a class="sidebar-link" href="{{ route('health-records.drug-formulary') }}">
        <i class="fas fa-capsules me-2"></i>
        <h6>Drug Formulary</h6>
    </a>
</li>

<li class="sidebar-list">
    <i class="fa-solid fa-thumbtack"></i>
    <a class="sidebar-link" href="{{ route('health-records.disease-guide') }}">
        <i class="fas fa-book-medical me-2"></i>
        <h6>Disease Guide</h6>
    </a>
</li>

<li class="sidebar-list">
    <i class="fa-solid fa-thumbtack"></i>
    <a class="sidebar-link" href="javascript:void(0)" onclick="openHealthCalendarModal()">
        <i class="fas fa-calendar-alt me-2"></i>
        <h6>Health Calendar</h6>
    </a>
</li>
@endif

      <!-- ==================== ACCOUNT ==================== -->
      <li class="sidebar-main-title">
        <h5 class="sidebar-title f-w-700">Account</h5>
      </li>

      <li class="sidebar-list">
        <i class="fa-solid fa-thumbtack"></i>
        <a class="sidebar-link" href="{{ route('account.edit') }}">
          <svg class="stroke-icon">
            <use href="{{asset('assets/svg/iconly-sprite.svg#Profile')}}"></use>
          </svg>
          <h6>My Account</h6>
        </a>
      </li>

      <!-- ==================== ADMIN ONLY (Top of sidebar after Dashboard) ==================== -->
      @if(auth()->user()->role === 'admin')
      <li class="sidebar-main-title">
        <h5 class="sidebar-title f-w-700">Administration</h5>
      </li>

      <li class="sidebar-list">
        <i class="fa-solid fa-thumbtack"></i>
        <a class="sidebar-link" href="{{ route('admin.users.index') }}">
          <svg class="stroke-icon">
            <use href="{{asset('assets/svg/iconly-sprite.svg#Users')}}"></use>
          </svg>
          <h6>User Management</h6>
        </a>
      </li>

      <li class="sidebar-list">
        <i class="fa-solid fa-thumbtack"></i>
        <a class="sidebar-link" href="{{ route('settings.index') }}">
          <svg class="stroke-icon">
            <use href="{{asset('assets/svg/iconly-sprite.svg#Setting')}}"></use>
          </svg>
          <h6>Settings</h6>
        </a>
      </li>
      @endif

    </ul>
  </div>
</aside>

<!-- Profit & Loss Modal -->
<div class="modal fade" id="profitLossModal" tabindex="-1" data-bs-backdrop="static">
  <div class="modal-dialog modal-lg">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-dark text-white border-0">
        <h5 class="modal-title text-white">
          <i class="fas fa-chart-line me-2"></i>Profit & Loss Statement
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4" id="profitLossContent">
        <div class="text-center py-4">
          <div class="spinner-border text-primary" role="status"></div>
          <p class="mt-2">Loading financial data...</p>
        </div>
      </div>
      <div class="modal-footer border-0">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <a href="{{ route('reports.financial') }}" class="btn btn-primary">View Full Report</a>
      </div>
    </div>
  </div>
</div>

<style>
.notification-badge {
  background: #dc2626;
  color: white;
  font-size: 0.65rem;
  font-weight: 600;
  padding: 0.15rem 0.45rem;
  border-radius: 20px;
  margin-left: auto;
  min-width: 18px;
  text-align: center;
}

/* Notification Badges */
.notification-badge-header {
    position: absolute;
    top: -8px;
    right: -12px;
    font-size: 10px;
    padding: 0.2rem 0.4rem;
    border-radius: 50%;
    background-color: #dc3545;
    color: white;
}

.sidebar-notification-badge {
    background-color: #dc3545;
    color: white;
    font-size: 0.65rem;
    font-weight: 600;
    padding: 0.15rem 0.45rem;
    border-radius: 20px;
    margin-left: auto;
    min-width: 18px;
    text-align: center;
}

.dropdown-notification-item {
    padding: 0.75rem 1rem;
    cursor: pointer;
    transition: background-color 0.2s;
    border-bottom: 1px solid #e2e8f0;
}

.dropdown-notification-item:hover {
    background-color: #f8fafc;
}

.dropdown-notification-item.unread {
    background-color: #f0f9ff;
}

.dropdown-notification-item.unread:hover {
    background-color: #e0f2fe;
}
</style>

<script>
  // Profit & Loss Modal Function
  function openProfitLossModal() {
    const modal = new bootstrap.Modal(document.getElementById('profitLossModal'));
    const modalBody = document.getElementById('profitLossContent');
    
    modalBody.innerHTML = `<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Loading financial data...</p></div>`;
    modal.show();
    
    fetch('/reports/api/profit-loss')
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          displayProfitLoss(data);
        } else {
          modalBody.innerHTML = `<div class="alert alert-danger">Failed to load data: ${data.message}</div>`;
        }
      })
      .catch(error => {
        modalBody.innerHTML = `<div class="alert alert-danger">Error: ${error.message}</div>`;
      });
  }
  
  function displayProfitLoss(data) {
    const isProfitable = data.net_profit >= 0;
    const profitLossIcon = isProfitable ? 'fa-arrow-up' : 'fa-arrow-down';
    
    document.getElementById('profitLossContent').innerHTML = `
      <div class="row g-3 mb-4">
        <div class="col-md-6">
          <div class="card bg-success-soft border-0">
            <div class="card-body text-center">
              <i class="fas fa-chart-line fa-2x text-success mb-2"></i>
              <h6 class="text-muted mb-1">Total Revenue</h6>
              <h3 class="text-success mb-0">₵${data.total_revenue.toLocaleString()}</h3>
              <small>${data.period}</small>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="card bg-danger-soft border-0">
            <div class="card-body text-center">
              <i class="fas fa-money-bill-wave fa-2x text-danger mb-2"></i>
              <h6 class="text-muted mb-1">Total Expenses</h6>
              <h3 class="text-danger mb-0">₵${data.total_expenses.toLocaleString()}</h3>
              <small>${data.period}</small>
            </div>
          </div>
        </div>
      </div>
  
      <div class="card mb-4 ${isProfitable ? 'bg-success' : 'bg-danger'} text-white">
        <div class="card-body text-center py-4">
          <i class="fas ${profitLossIcon} fa-3x mb-2"></i>
          <h5 class="mb-1">Net Profit / Loss</h5>
          <h2 class="mb-0 fw-bold">₵${Math.abs(data.net_profit).toLocaleString()}</h2>
          <small>${isProfitable ? 'Profit' : 'Loss'} for ${data.period}</small>
        </div>
      </div>
  
      <div class="row g-3">
        <div class="col-6">
          <div class="card border">
            <div class="card-body text-center">
              <h6 class="text-muted mb-1">Profit Margin</h6>
              <h3 class="${data.profit_margin >= 20 ? 'text-success' : (data.profit_margin >= 10 ? 'text-warning' : 'text-danger')} mb-0">
                ${data.profit_margin}%
              </h3>
              <div class="progress mt-2" style="height: 4px;">
                <div class="progress-bar ${data.profit_margin >= 20 ? 'bg-success' : (data.profit_margin >= 10 ? 'bg-warning' : 'bg-danger')}" 
                     style="width: ${Math.min(100, data.profit_margin)}%"></div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-6">
          <div class="card border">
            <div class="card-body text-center">
              <h6 class="text-muted mb-1">Expense Ratio</h6>
              <h3 class="${data.expense_ratio <= 60 ? 'text-success' : (data.expense_ratio <= 80 ? 'text-warning' : 'text-danger')} mb-0">
                ${data.expense_ratio}%
              </h3>
              <div class="progress mt-2" style="height: 4px;">
                <div class="progress-bar ${data.expense_ratio <= 60 ? 'bg-success' : (data.expense_ratio <= 80 ? 'bg-warning' : 'bg-danger')}" 
                     style="width: ${Math.min(100, data.expense_ratio)}%"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    `;
  }
  
  // Redirect and Open Modal function
  function redirectAndOpenModal(routeName, modalId) {
      sessionStorage.setItem('openModalOnLoad', modalId);
      sessionStorage.setItem('modalTriggerRoute', routeName);
      window.location.href = route(routeName);
  }
  
  // Check and open modal on page load - FIXED
  function checkAndOpenModalOnLoad() {
    const modalToOpen = sessionStorage.getItem('openModalOnLoad');
    
    if (modalToOpen) {
        sessionStorage.removeItem('openModalOnLoad');
        
        setTimeout(() => {
            const modalElement = document.getElementById(modalToOpen);
            if (modalElement) {
                // Just show the modal - let the page's own JavaScript handle loading content
                const modal = new bootstrap.Modal(modalElement);
                modal.show();
                
                // For createExpenseModal, trigger the button click that opens it
                if (modalToOpen === 'createExpenseModal') {
                    const newExpenseBtn = document.getElementById('newExpenseBtn');
                    if (newExpenseBtn) {
                        newExpenseBtn.click();
                    }
                }
                if (modalToOpen === 'createLogModal') {
                    const newLogBtn = document.querySelector('#createLogModal .btn-primary');
                    // The modal is already shown, the content should load via its own initialization
                }
                if (modalToOpen === 'createBreedingModal') {
                    const newBreedingBtn = document.getElementById('newBreedingRecordBtn');
                    if (newBreedingBtn) {
                        newBreedingBtn.click();
                    }
                }
            }
        }, 500);
    }
}
  
  function route(name) {
    const routes = {
      'flocks.index': '{{ route("flocks.index") }}',
      'houses.index': '{{ route("houses.index") }}',
      'daily-logs.index': '{{ route("daily-logs.index") }}',
      'feed-issuances.index': '{{ route("feed-issuances.index") }}',
      'breeding-records.index': '{{ route("breeding-records.index") }}',
      'expenses.index': '{{ route("expenses.index") }}'
    };
    return routes[name] || '/';
  }
  
  // Function to open daily log modal directly
  function openCreateDailyLogModalDirect() {
      if (!window.location.href.includes('daily-logs')) {
          sessionStorage.setItem('openModalOnLoad', 'createLogModal');
          window.location.href = '{{ route("daily-logs.index") }}';
      } else {
          const modalElement = document.getElementById('createLogModal');
          if (modalElement) {
              const modal = new bootstrap.Modal(modalElement);
              modal.show();
          }
      }
  }
  
  // Function to open expense modal directly
  function openCreateExpenseModalDirect() {
      if (!window.location.href.includes('expenses')) {
          sessionStorage.setItem('openModalOnLoad', 'createExpenseModal');
          window.location.href = '{{ route("expenses.index") }}';
      } else {
          if (typeof window.openCreateExpenseModal === 'function') {
              window.openCreateExpenseModal();
          } else {
              const modalElement = document.getElementById('createExpenseModal');
              if (modalElement) {
                  const modal = new bootstrap.Modal(modalElement);
                  modal.show();
              }
          }
      }
  }
  
  document.addEventListener('DOMContentLoaded', function() {
    if (typeof feather !== 'undefined') feather.replace();
    checkAndOpenModalOnLoad();
  
    // Sidebar toggle functionality
    const leftArrow = document.getElementById('left-arrow');
    const pageSidebar = document.querySelector('.page-sidebar');
    
    if (leftArrow && pageSidebar) {
      leftArrow.addEventListener('click', function() {
        pageSidebar.classList.toggle('close_icon');
        localStorage.setItem('sidebarClosed', pageSidebar.classList.contains('close_icon'));
        setTimeout(() => window.dispatchEvent(new Event('resize')), 300);
      });
      if (localStorage.getItem('sidebarClosed') === 'true') {
        pageSidebar.classList.add('close_icon');
      }
    }
  
    // Submenu toggle
    const submenuItems = document.querySelectorAll('.sidebar-list.has-submenu > a');
    submenuItems.forEach(item => {
      item.addEventListener('click', function(e) {
        e.preventDefault();
        const parentLi = this.parentElement;
        const submenu = parentLi.querySelector('.sidebar-submenu');
        document.querySelectorAll('.sidebar-list.has-submenu.active').forEach(openItem => {
          if (openItem !== parentLi) {
            openItem.classList.remove('active');
            const openSubmenu = openItem.querySelector('.sidebar-submenu');
            if (openSubmenu) openSubmenu.style.display = 'none';
          }
        });
        parentLi.classList.toggle('active');
        if (submenu) submenu.style.display = parentLi.classList.contains('active') ? 'block' : 'none';
      });
    });
  
    // Restore open submenus
    const openMenus = JSON.parse(localStorage.getItem('openSubmenus') || '[]');
    document.querySelectorAll('.sidebar-list.has-submenu').forEach(menu => {
      const menuText = menu.querySelector('h6')?.innerText || '';
      if (openMenus.includes(menuText)) {
        menu.classList.add('active');
        const submenu = menu.querySelector('.sidebar-submenu');
        if (submenu) submenu.style.display = 'block';
      }
    });
  
    // Real-time notification functions
    function refreshNotifications() {
      fetch('{{ route("api.notifications") }}')
          .then(response => response.json())
          .then(data => {
              if (data.success) {
                  const headerBadge = document.getElementById('notificationBadge');
                  if (headerBadge) {
                      if (data.unread_count > 0) {
                          headerBadge.textContent = data.unread_count > 99 ? '99+' : data.unread_count;
                          headerBadge.style.display = 'inline-flex';
                      } else {
                          headerBadge.style.display = 'none';
                      }
                  }
                  
                  const sidebarBadge = document.getElementById('sidebarNotificationBadge');
                  if (sidebarBadge) {
                      if (data.unread_count > 0) {
                          sidebarBadge.textContent = data.unread_count > 99 ? '99+' : data.unread_count;
                          sidebarBadge.style.display = 'inline-block';
                      } else {
                          sidebarBadge.style.display = 'none';
                      }
                  }
                  
                  const notificationList = document.getElementById('notificationList');
                  if (notificationList && data.notifications.length > 0) {
                      notificationList.innerHTML = data.notifications.map(notif => `
                          <li class="dropdown-notification-item ${!notif.read_at ? 'unread' : ''}" 
                              data-id="${notif.id}" 
                              onclick="viewNotification(${notif.id})">
                              <div class="d-flex justify-content-between align-items-start">
                                  <div class="flex-grow-1">
                                      <div class="d-flex align-items-center gap-2 mb-1">
                                          ${notif.severity === 'critical' ? '<i class="fas fa-exclamation-circle text-danger"></i>' : 
                                            (notif.severity === 'warning' ? '<i class="fas fa-exclamation-triangle text-warning"></i>' : 
                                            '<i class="fas fa-info-circle text-info"></i>')}
                                          <strong class="small">${escapeHtml(notif.title)}</strong>
                                      </div>
                                      <p class="mb-0 small text-muted">${escapeHtml(notif.message.substring(0, 50))}${notif.message.length > 50 ? '...' : ''}</p>
                                      <small class="text-muted" style="font-size: 10px;">${notif.time_ago}</small>
                                  </div>
                                  ${!notif.read_at ? '<span class="badge bg-primary ms-2" style="font-size: 8px;">New</span>' : ''}
                              </div>
                          </li>
                      `).join('');
                  } else if (notificationList) {
                      notificationList.innerHTML = `
                          <li class="text-center py-3">
                              <i class="fas fa-bell-slash text-muted fa-2x mb-2 d-block"></i>
                              <p class="mb-0 text-muted small">No notifications</p>
                          </li>
                      `;
                  }
              }
          })
          .catch(error => console.error('Error fetching notifications:', error));
    }
  
    function viewNotification(id) {
      window.location.href = `/notifications/${id}`;
    }
  
    function escapeHtml(str) {
      if (!str) return '';
      return String(str).replace(/[&<>]/g, function(m) {
          if (m === '&') return '&amp;';
          if (m === '<') return '&lt;';
          if (m === '>') return '&gt;';
          return m;
      });
    }
  
    // Poll for new notifications every 30 seconds
    setInterval(refreshNotifications, 30000);
    refreshNotifications();
  
    // SimpleBar
    const simpleBarElement = document.getElementById('simple-bar');
    if (simpleBarElement && typeof SimpleBar !== 'undefined') {
      new SimpleBar(simpleBarElement.parentElement || simpleBarElement);
    } else if (simpleBarElement) {
      simpleBarElement.style.overflowY = 'auto';
      simpleBarElement.style.maxHeight = 'calc(100vh - 100px)';
    }
  
    // Highlight current menu
    const currentUrl = window.location.href;
    document.querySelectorAll('.sidebar-link, .sidebar-submenu a').forEach(link => {
      if (link.href && currentUrl.includes(link.href)) {
        link.classList.add('active');
        const parentSubmenu = link.closest('.sidebar-submenu');
        if (parentSubmenu) {
          const parentMenuItem = parentSubmenu.closest('.sidebar-list.has-submenu');
          if (parentMenuItem && !parentMenuItem.classList.contains('active')) {
            parentMenuItem.classList.add('active');
            parentSubmenu.style.display = 'block';
          }
        }
        link.closest('.sidebar-list')?.classList.add('active-page');
      }
    });
  });
  </script>