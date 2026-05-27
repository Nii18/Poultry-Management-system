{{-- resources/views/expenses/index.blade.php --}}
@extends('layouts.master')

@section('content')
<div class="container-fluid px-4">
    <!-- Page Header -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <div class="d-flex align-items-center gap-3">
                    <div class="page-icon">
                        <i class="fas fa-money-bill-wave fs-1 text-danger"></i>
                    </div>
                    <div>
                        <h1 class="page-title mb-1">Farm Expenses</h1>
                        <p class="page-description text-muted mb-0">Track all farm expenditures and costs</p>
                    </div>
                </div>
            </div>
            <div class="col-auto">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Expenses</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <!-- Stats Cards - ALL CLICKABLE -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="stat-card stat-card-clickable" id="statCardTotal" role="button" tabindex="0" title="Click for details">
                <div class="stat-card-body">
                    <div class="stat-card-icon bg-danger-soft">
                        <i class="fas fa-chart-line text-danger"></i>
                    </div>
                    <div class="stat-card-info">
                        <span class="stat-card-label">Total Expenses</span>
                        <h3 class="stat-card-value">₵{{ number_format($totalAllTimeExpenses, 2) }}</h3>
                    </div>
                </div>
                <div class="stat-card-hint"><i class="fas fa-arrow-right me-1"></i>View full breakdown</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card stat-card-clickable" id="statCardMonth" role="button" tabindex="0" title="Click for details">
                <div class="stat-card-body">
                    <div class="stat-card-icon bg-warning-soft">
                        <i class="fas fa-chart-pie text-warning"></i>
                    </div>
                    <div class="stat-card-info">
                        <span class="stat-card-label">This Month</span>
                        <h3 class="stat-card-value">₵{{ number_format($thisMonthExpenses, 2) }}</h3>
                    </div>
                </div>
                <div class="stat-card-hint"><i class="fas fa-arrow-right me-1"></i>View monthly details</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card stat-card-clickable" id="statCardWeek" role="button" tabindex="0" title="Click for details">
                <div class="stat-card-body">
                    <div class="stat-card-icon bg-info-soft">
                        <i class="fas fa-calendar-week text-info"></i>
                    </div>
                    <div class="stat-card-info">
                        <span class="stat-card-label">This Week</span>
                        <h3 class="stat-card-value">₵{{ number_format($thisWeekExpenses, 2) }}</h3>
                    </div>
                </div>
                <div class="stat-card-hint"><i class="fas fa-arrow-right me-1"></i>View weekly details</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card stat-card-clickable" id="statCardRecords" role="button" tabindex="0" title="Click for details">
                <div class="stat-card-body">
                    <div class="stat-card-icon bg-primary-soft">
                        <i class="fas fa-receipt text-primary"></i>
                    </div>
                    <div class="stat-card-info">
                        <span class="stat-card-label">Total Records</span>
                        <h3 class="stat-card-value">{{ $totalRecordsCount }}</h3>
                    </div>
                </div>
                <div class="stat-card-hint"><i class="fas fa-arrow-right me-1"></i>View record breakdown</div>
            </div>
        </div>
    </div>

    <!-- Main Card -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-0 py-3">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="card-title mb-0 fw-semibold">
                        <i class="fas fa-list me-2 text-danger"></i>Expense Records
                    </h5>
                </div>
                <div class="col-auto">
                    <div class="d-flex gap-2">
                        <a href="{{ route('expenses.by-category') }}" class="btn btn-info">
                            <i class="fas fa-chart-pie me-2"></i>View by Category
                        </a>
                        <button type="button" class="btn btn-danger" id="newExpenseBtn">
                            <i class="fas fa-plus me-2"></i>Add Expense
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card-body">
            <!-- Filters Section -->
            <div class="filter-section mb-4 p-3 bg-light rounded-3">
                <div class="row align-items-end g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold mb-2">
                            <i class="fas fa-paw me-1 text-muted"></i>Flock
                        </label>
                        <select name="flock_id" class="form-select" id="flockFilter">
                            <option value="">All Flocks</option>
                            @foreach($flocks as $flock)
                                <option value="{{ $flock->id }}" {{ request('flock_id') == $flock->id ? 'selected' : '' }}>
                                    {{ $flock->flock_number }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold mb-2">
                            <i class="fas fa-building me-1 text-muted"></i>House
                        </label>
                        <select name="house_id" class="form-select" id="houseFilter">
                            <option value="">All Houses</option>
                            @foreach($houses as $house)
                                <option value="{{ $house->id }}" {{ request('house_id') == $house->id ? 'selected' : '' }}>
                                    {{ $house->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold mb-2">
                            <i class="fas fa-tag me-1 text-muted"></i>Category
                        </label>
                        <select name="category" class="form-select" id="categoryFilter">
                            <option value="">All Categories</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>
                                    {{ ucfirst($cat) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold mb-2">
                            <i class="fas fa-calendar me-1 text-muted"></i>Start Date
                        </label>
                        <input type="date" name="start_date" class="form-control" id="startDateFilter" value="{{ request('start_date') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold mb-2">
                            <i class="fas fa-calendar-end me-1 text-muted"></i>End Date
                        </label>
                        <input type="date" name="end_date" class="form-control" id="endDateFilter" value="{{ request('end_date') }}">
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-danger px-4" id="applyFilters">
                                <i class="fas fa-filter me-2"></i>Apply Filters
                            </button>
                            <a href="{{ route('expenses.index') }}" class="btn btn-outline-secondary px-4">
                                <i class="fas fa-redo-alt me-2"></i>Reset
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Expenses Table -->
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="py-3">Date</th>
                            <th class="py-3">Category</th>
                            <th class="py-3">Description</th>
                            <th class="py-3">Amount</th>
                            <th class="py-3">Associated With</th>
                            <th class="py-3">Vendor</th>
                            <th class="py-3">Payment Method</th>
                            <th class="py-3 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($expenses as $expense)
                        <tr>
                            <td>{{ $expense->expense_date->format('d M Y') }}</td>
                            <td>
                                <span class="badge bg-{{ $expense->category == 'feed' ? 'success' : ($expense->category == 'veterinary' ? 'info' : ($expense->category == 'labor' ? 'primary' : 'secondary')) }}-soft text-{{ $expense->category == 'feed' ? 'success' : ($expense->category == 'veterinary' ? 'info' : ($expense->category == 'labor' ? 'primary' : 'secondary')) }} px-3 py-2 rounded-pill">
                                    <i class="fas {{ $expense->category == 'feed' ? 'fa-seedling' : ($expense->category == 'veterinary' ? 'fa-stethoscope' : ($expense->category == 'labor' ? 'fa-users' : 'fa-tag')) }} me-1"></i>
                                    {{ ucfirst($expense->category) }}
                                </span>
                            </td>
                            <td>{{ Str::limit($expense->description, 40) }}</td>
                            <td>
                                <strong class="text-danger fs-6">₵{{ number_format($expense->amount, 2) }}</strong>
                            </td>
                            <td>
                                @if($expense->flock)
                                    <span class="badge bg-primary-soft text-primary">
                                        <i class="fas fa-paw me-1"></i>{{ $expense->flock->flock_number }}
                                    </span>
                                @elseif($expense->house)
                                    <span class="badge bg-info-soft text-info">
                                        <i class="fas fa-building me-1"></i>{{ $expense->house->name }}
                                    </span>
                                @else
                                    <span class="badge bg-secondary-soft text-secondary">
                                        <i class="fas fa-globe me-1"></i>General
                                    </span>
                                @endif
                            </td>
                            <td>{{ $expense->vendor_name ?? 'N/A' }}</td>
                            <td>
                                @if($expense->payment_method)
                                    <span class="badge bg-success text-dark">
                                        <i class="fas {{ $expense->payment_method == 'cash' ? 'fa-money-bill' : ($expense->payment_method == 'bank_transfer' ? 'fa-university' : 'fa-credit-card') }} me-1"></i>
                                        {{ ucfirst(str_replace('_', ' ', $expense->payment_method)) }}
                                    </span>
                                @else
                                    N/A
                                @endif
                            </td>
                            <td>
                                <div class="btn-group gap-1">
                                    <button type="button" class="btn btn-sm btn-outline-primary view-expense-btn" data-id="{{ $expense->id }}" title="View Details">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-warning edit-expense-btn" data-id="{{ $expense->id }}" title="Edit">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    @if(auth()->user()->role === 'admin')
                                        <button type="button" class="btn btn-sm btn-outline-danger delete-expense-btn" data-id="{{ $expense->id }}" data-info="{{ $expense->description }}" title="Delete">
                                            <i class="fas fa-trash-alt"></i> Delete
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="empty-state">
                                    <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">No Expenses Found</h5>
                                    <p class="text-muted mb-3">Get started by adding your first expense record</p>
                                    <button type="button" class="btn btn-danger" id="emptyStateNewBtn">
                                        <i class="fas fa-plus me-2"></i>Add Expense
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="3"><strong class="fs-5">Total</strong></td>
                            <td><strong class="text-danger fs-5">₵{{ number_format($expenses->sum('amount'), 2) }}</strong></td>
                            <td colspan="4"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Pagination -->
            @if($expenses->hasPages())
            <div class="d-flex justify-content-between align-items-center mt-4 pt-2">
                <div class="text-muted small">
                    Showing {{ $expenses->firstItem() ?? 0 }} to {{ $expenses->lastItem() ?? 0 }} of {{ $expenses->total() }} results
                </div>
                <div>
                    {{ $expenses->withQueryString()->links() }}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- ==================== STAT DETAIL MODALS ==================== --}}

{{-- ---- MODAL 1: Total Expenses (All Time) ---- --}}
<div class="modal fade" id="modalTotal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius:16px;overflow:hidden;">
            <div class="modal-header border-0 text-white" style="background:linear-gradient(135deg,#dc2626,#b91c1c);padding:1.25rem 1.5rem;">
                <div class="d-flex align-items-center gap-3">
                    <div class="smodal-icon"><i class="fas fa-chart-line"></i></div>
                    <div>
                        <h5 class="mb-0 text-white">Total Expenses — All Time</h5>
                        <small class="opacity-75">Complete financial summary across all records</small>
                    </div>
                </div>
                <button type="button" class="modal-custom-close" data-bs-dismiss="modal"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body p-4">

                {{-- Hero --}}
                <div class="smodal-hero" style="background:#fef2f2;border-color:#fecaca;">
                    <div class="smodal-hero-label">Total All-Time Expenditure</div>
                    <div class="smodal-hero-value">₵{{ number_format($totalAllTimeExpenses, 2) }}</div>
                    <div class="smodal-hero-sub">Across {{ $totalRecordsCount }} expense records</div>
                </div>

                {{-- Key metrics row --}}
                <div class="row g-3 mb-4">
                    <div class="col-6 col-md-3">
                        <div class="smodal-metric">
                            <div class="smodal-metric-label">Avg per Record</div>
                            <div class="smodal-metric-value">₵{{ number_format($totalAllTimeExpenses / max($totalRecordsCount, 1), 2) }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="smodal-metric">
                            <div class="smodal-metric-label">This Month</div>
                            <div class="smodal-metric-value">₵{{ number_format($thisMonthExpenses, 2) }}</div>
                            <div class="smodal-metric-sub">{{ now()->format('F Y') }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="smodal-metric">
                            <div class="smodal-metric-label">This Week</div>
                            <div class="smodal-metric-value">₵{{ number_format($thisWeekExpenses, 2) }}</div>
                            <div class="smodal-metric-sub">{{ now()->startOfWeek()->format('M d') }}–{{ now()->endOfWeek()->format('M d') }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="smodal-metric">
                            <div class="smodal-metric-label">Highest Month</div>
                            <div class="smodal-metric-value" style="font-size:0.9rem;">{{ $highestMonthName }}</div>
                            <div class="smodal-metric-sub">₵{{ number_format($highestMonthTotal, 2) }}</div>
                        </div>
                    </div>
                </div>

                {{-- Category breakdown --}}
                <div class="smodal-section-label">All-Time Spend by Category</div>
                @forelse($categoryBreakdown as $cat)
                @php $pct = $totalAllTimeExpenses > 0 ? ($cat->total / $totalAllTimeExpenses) * 100 : 0; @endphp
                <div class="smodal-bar-row">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="smodal-bar-name">{{ ucfirst($cat->category) }}</span>
                        <span class="smodal-bar-meta">₵{{ number_format($cat->total, 2) }} &nbsp;<span class="text-muted">({{ number_format($pct, 1) }}%)</span></span>
                    </div>
                    <div class="smodal-track">
                        <div class="smodal-fill" style="width:{{ $pct }}%;background:#dc2626;"></div>
                    </div>
                </div>
                @empty
                <p class="text-muted text-center py-3">No expense data available.</p>
                @endforelse

            </div>
            <div class="modal-footer border-0 smodal-footer">
                <a href="{{ route('expenses.by-category') }}" class="btn btn-danger btn-sm"><i class="fas fa-chart-pie me-2"></i>View Analytics</a>
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- ---- MODAL 2: This Month ---- --}}
<div class="modal fade" id="modalMonth" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius:16px;overflow:hidden;">
            <div class="modal-header border-0 text-white" style="background:linear-gradient(135deg,#f59e0b,#d97706);padding:1.25rem 1.5rem;">
                <div class="d-flex align-items-center gap-3">
                    <div class="smodal-icon"><i class="fas fa-chart-pie"></i></div>
                    <div>
                        <h5 class="mb-0 text-white">This Month — {{ now()->format('F Y') }}</h5>
                        <small class="opacity-75">Month-to-date expense breakdown</small>
                    </div>
                </div>
                <button type="button" class="modal-custom-close" data-bs-dismiss="modal"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body p-4">

                {{-- Hero --}}
                <div class="smodal-hero" style="background:#fffbeb;border-color:#fde68a;">
                    <div class="smodal-hero-label">Month-to-Date Spend</div>
                    <div class="smodal-hero-value">₵{{ number_format($thisMonthExpenses, 2) }}</div>
                    <div class="smodal-hero-sub">{{ $thisMonthRecordsCount }} records · {{ now()->format('1 M') }}–{{ now()->format('j M Y') }}</div>
                </div>

                {{-- Key metrics --}}
                <div class="row g-3 mb-4">
                    <div class="col-6 col-md-3">
                        <div class="smodal-metric">
                            <div class="smodal-metric-label">Daily Average</div>
                            <div class="smodal-metric-value">₵{{ number_format($thisMonthExpenses / max(now()->day, 1), 2) }}</div>
                            <div class="smodal-metric-sub">Over {{ now()->day }} days</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="smodal-metric">
                            <div class="smodal-metric-label">Projected Total</div>
                            <div class="smodal-metric-value">₵{{ number_format(($thisMonthExpenses / max(now()->day, 1)) * now()->daysInMonth, 2) }}</div>
                            <div class="smodal-metric-sub">Based on daily avg</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="smodal-metric">
                            <div class="smodal-metric-label">Records</div>
                            <div class="smodal-metric-value">{{ $thisMonthRecordsCount }}</div>
                            <div class="smodal-metric-sub">This month</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="smodal-metric">
                            <div class="smodal-metric-label">vs All-Time Avg</div>
                            @php
                                $allTimeMonthlyAvg = $totalAllTimeExpenses / 12;
                                $monthDiff = $thisMonthExpenses - $allTimeMonthlyAvg;
                            @endphp
                            <div class="smodal-metric-value" style="color:{{ $monthDiff >= 0 ? '#dc2626' : '#059669' }};">
                                {{ $monthDiff >= 0 ? '+' : '' }}₵{{ number_format(abs($monthDiff), 2) }}
                            </div>
                            <div class="smodal-metric-sub">{{ $monthDiff >= 0 ? 'Above' : 'Below' }} monthly avg</div>
                        </div>
                    </div>
                </div>

                {{-- Progress bar: month consumed --}}
                @php $daysPct = (now()->day / now()->daysInMonth) * 100; @endphp
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-1">
                        <span style="font-size:0.78rem;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.05em;">Month Progress</span>
                        <span style="font-size:0.78rem;color:#6b7280;">Day {{ now()->day }} of {{ now()->daysInMonth }}</span>
                    </div>
                    <div class="smodal-track" style="height:10px;">
                        <div class="smodal-fill" style="width:{{ $daysPct }}%;background:#f59e0b;"></div>
                    </div>
                    <div class="d-flex justify-content-between mt-1">
                        <span style="font-size:0.72rem;color:#6b7280;">{{ now()->format('1 M') }}</span>
                        <span style="font-size:0.72rem;font-weight:700;color:#f59e0b;">{{ number_format($daysPct, 0) }}% of month elapsed</span>
                        <span style="font-size:0.72rem;color:#6b7280;">{{ now()->endOfMonth()->format('j M') }}</span>
                    </div>
                </div>

                {{-- Top categories this month --}}
                <div class="smodal-section-label">Top Categories This Month</div>
                @forelse($thisMonthCategories as $cat)
                @php $pct = $thisMonthExpenses > 0 ? ($cat->total / $thisMonthExpenses) * 100 : 0; @endphp
                <div class="smodal-bar-row">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="smodal-bar-name">{{ ucfirst($cat->category) }}</span>
                        <span class="smodal-bar-meta">₵{{ number_format($cat->total, 2) }} &nbsp;<span class="text-muted">({{ number_format($pct, 1) }}%)</span></span>
                    </div>
                    <div class="smodal-track">
                        <div class="smodal-fill" style="width:{{ $pct }}%;background:#f59e0b;"></div>
                    </div>
                </div>
                @empty
                <p class="text-muted text-center py-3">No expenses recorded this month.</p>
                @endforelse

            </div>
            <div class="modal-footer border-0 smodal-footer">
                <a href="{{ route('expenses.index') }}?start_date={{ now()->startOfMonth()->format('Y-m-d') }}&end_date={{ now()->endOfMonth()->format('Y-m-d') }}" class="btn btn-warning btn-sm text-white"><i class="fas fa-list me-2"></i>View This Month's Records</a>
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- ---- MODAL 3: This Week ---- --}}
<div class="modal fade" id="modalWeek" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius:16px;overflow:hidden;">
            <div class="modal-header border-0 text-white" style="background:linear-gradient(135deg,#3b82f6,#2563eb);padding:1.25rem 1.5rem;">
                <div class="d-flex align-items-center gap-3">
                    <div class="smodal-icon"><i class="fas fa-calendar-week"></i></div>
                    <div>
                        <h5 class="mb-0 text-white">This Week's Expenses</h5>
                        <small class="opacity-75">{{ now()->startOfWeek()->format('M d') }} – {{ now()->endOfWeek()->format('M d, Y') }}</small>
                    </div>
                </div>
                <button type="button" class="modal-custom-close" data-bs-dismiss="modal"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body p-4">

                {{-- Hero --}}
                <div class="smodal-hero" style="background:#eff6ff;border-color:#bfdbfe;">
                    <div class="smodal-hero-label">Week-to-Date Spend</div>
                    <div class="smodal-hero-value">₵{{ number_format($thisWeekExpenses, 2) }}</div>
                    <div class="smodal-hero-sub">{{ $thisWeekRecordsCount }} records · {{ now()->startOfWeek()->format('M d') }}–{{ now()->endOfWeek()->format('M d') }}</div>
                </div>

                {{-- Key metrics --}}
                <div class="row g-3 mb-4">
                    <div class="col-6">
                        <div class="smodal-metric">
                            <div class="smodal-metric-label">Daily Average</div>
                            <div class="smodal-metric-value">₵{{ number_format($thisWeekExpenses / max(now()->dayOfWeek ?: 7, 1), 2) }}</div>
                            <div class="smodal-metric-sub">This week so far</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="smodal-metric">
                            <div class="smodal-metric-label">Records This Week</div>
                            <div class="smodal-metric-value">{{ $thisWeekRecordsCount }}</div>
                            <div class="smodal-metric-sub">Total entries</div>
                        </div>
                    </div>
                </div>

                {{-- Day-by-day breakdown --}}
                <div class="smodal-section-label">Day-by-Day Breakdown</div>
                @php
                    $dayNames  = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
                    $weekStart = now()->startOfWeek();
                    $dailyAmts = [];
                    for ($i = 0; $i < 7; $i++) {
                        $d = $weekStart->copy()->addDays($i);
                        $dailyAmts[] = [
                            'label'  => $dayNames[$i],
                            'date'   => $d->format('M j'),
                            'amount' => \App\Models\Expense::whereDate('expense_date', $d)->sum('amount'),
                            'future' => $d->isFuture(),
                        ];
                    }
                    $maxDaily = max(array_column($dailyAmts, 'amount')) ?: 1;
                @endphp
                @foreach($dailyAmts as $day)
                @php $barPct = ($day['amount'] / $maxDaily) * 100; @endphp
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div style="flex:0 0 36px;text-align:center;">
                        <div style="font-size:0.78rem;font-weight:700;color:#374151;">{{ $day['label'] }}</div>
                        <div style="font-size:0.68rem;color:#94a3b8;">{{ $day['date'] }}</div>
                    </div>
                    <div style="flex:1;height:10px;background:#f1f5f9;border-radius:99px;overflow:hidden;">
                        <div style="height:100%;width:{{ $day['future'] ? 0 : $barPct }}%;background:{{ $day['amount'] > 0 ? '#3b82f6' : '#e2e8f0' }};border-radius:99px;transition:width 0.4s;"></div>
                    </div>
                    <div style="flex:0 0 110px;text-align:right;">
                        @if($day['future'])
                            <span style="font-size:0.78rem;color:#94a3b8;">—</span>
                        @elseif($day['amount'] > 0)
                            <span style="font-size:0.83rem;font-weight:700;color:#3b82f6;">₵{{ number_format($day['amount'], 2) }}</span>
                        @else
                            <span style="font-size:0.78rem;color:#94a3b8;">₵0.00</span>
                        @endif
                    </div>
                </div>
                @endforeach

                {{-- Week total footer --}}
                <div class="d-flex justify-content-between align-items-center pt-3 mt-1" style="border-top:1.5px solid #e5e7eb;">
                    <span style="font-size:0.83rem;font-weight:700;color:#374151;">Week Total</span>
                    <span style="font-size:1rem;font-weight:800;color:#3b82f6;">₵{{ number_format($thisWeekExpenses, 2) }}</span>
                </div>

            </div>
            <div class="modal-footer border-0 smodal-footer">
                <a href="{{ route('expenses.index') }}?start_date={{ now()->startOfWeek()->format('Y-m-d') }}&end_date={{ now()->endOfWeek()->format('Y-m-d') }}" class="btn btn-primary btn-sm"><i class="fas fa-list me-2"></i>View This Week's Records</a>
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- ---- MODAL 4: Total Records ---- --}}
<div class="modal fade" id="modalRecords" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius:16px;overflow:hidden;">
            <div class="modal-header border-0 text-white" style="background:linear-gradient(135deg,#10b981,#059669);padding:1.25rem 1.5rem;">
                <div class="d-flex align-items-center gap-3">
                    <div class="smodal-icon"><i class="fas fa-receipt"></i></div>
                    <div>
                        <h5 class="mb-0 text-white">Expense Records Summary</h5>
                        <small class="opacity-75">Count breakdown across all timeframes</small>
                    </div>
                </div>
                <button type="button" class="modal-custom-close" data-bs-dismiss="modal"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body p-4">

                {{-- Hero --}}
                <div class="smodal-hero" style="background:#ecfdf5;border-color:#a7f3d0;">
                    <div class="smodal-hero-label">Total Expense Records</div>
                    <div class="smodal-hero-value">{{ number_format($totalRecordsCount) }}</div>
                    <div class="smodal-hero-sub">All entries ever recorded in the system</div>
                </div>

                {{-- Timeframe counts grid --}}
                <div class="row g-3 mb-4">
                    <div class="col-6 col-md-3">
                        <div class="smodal-count-card" style="background:#ecfdf5;border-color:#a7f3d0;">
                            <div class="smodal-count-icon" style="color:#059669;"><i class="fas fa-database"></i></div>
                            <div class="smodal-count-value">{{ number_format($totalRecordsCount) }}</div>
                            <div class="smodal-count-label">All Time</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="smodal-count-card" style="background:#fef2f2;border-color:#fecaca;">
                            <div class="smodal-count-icon" style="color:#dc2626;"><i class="fas fa-calendar-alt"></i></div>
                            <div class="smodal-count-value">{{ $thisMonthRecordsCount }}</div>
                            <div class="smodal-count-label">This Month</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="smodal-count-card" style="background:#eff6ff;border-color:#bfdbfe;">
                            <div class="smodal-count-icon" style="color:#2563eb;"><i class="fas fa-calendar-week"></i></div>
                            <div class="smodal-count-value">{{ $thisWeekRecordsCount }}</div>
                            <div class="smodal-count-label">This Week</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="smodal-count-card" style="background:#fdf4ff;border-color:#e9d5ff;">
                            <div class="smodal-count-icon" style="color:#7c3aed;"><i class="fas fa-clock"></i></div>
                            <div class="smodal-count-value">{{ $todayRecordsCount }}</div>
                            <div class="smodal-count-label">Today</div>
                        </div>
                    </div>
                </div>

                {{-- Records by category --}}
                <div class="smodal-section-label">Records by Category</div>
                @php $maxCount = $categoryRecordCounts->max('count') ?: 1; @endphp
                @forelse($categoryRecordCounts as $cat)
                @php $barPct = ($cat->count / $maxCount) * 100; @endphp
                <div class="smodal-bar-row">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="smodal-bar-name">{{ ucfirst($cat->category) }}</span>
                        <div class="d-flex align-items-center gap-2">
                            <span style="font-size:0.78rem;color:#6b7280;">{{ $cat->count }} {{ $cat->count == 1 ? 'record' : 'records' }}</span>
                            <span class="badge" style="background:#ecfdf5;color:#059669;font-size:0.72rem;">
                                {{ number_format(($cat->count / max($totalRecordsCount, 1)) * 100, 1) }}%
                            </span>
                        </div>
                    </div>
                    <div class="smodal-track">
                        <div class="smodal-fill" style="width:{{ $barPct }}%;background:#10b981;"></div>
                    </div>
                </div>
                @empty
                <p class="text-muted text-center py-3">No expense records found.</p>
                @endforelse

                {{-- Summary note --}}
                <div class="mt-3 p-3 rounded" style="background:#f9fafb;border:1px solid #e5e7eb;">
                    <p class="mb-0" style="font-size:0.78rem;color:#6b7280;">
                        <i class="fas fa-info-circle me-1 text-primary"></i>
                        Average of <strong>₵{{ number_format($totalAllTimeExpenses / max($totalRecordsCount, 1), 2) }}</strong> per record &nbsp;·&nbsp;
                        <strong>{{ $thisMonthRecordsCount }}</strong> new records this month &nbsp;·&nbsp;
                        <strong>{{ $todayRecordsCount }}</strong> recorded today
                    </p>
                </div>

            </div>
            <div class="modal-footer border-0 smodal-footer">
                <a href="{{ route('expenses.index') }}" class="btn btn-success btn-sm"><i class="fas fa-list me-2"></i>View All Records</a>
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- ==================== CRUD MODALS (unchanged) ==================== --}}

<!-- Create Expense Modal -->
<div class="modal fade" id="createExpenseModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-danger text-white border-0">
                <h5 class="modal-title text-white"><i class="fas fa-plus-circle me-2"></i>Add Expense</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="createExpenseContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-danger" role="status"><span class="visually-hidden">Loading...</span></div>
                    <p class="mt-2">Loading form...</p>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="saveCreateExpense">Save Expense</button>
            </div>
        </div>
    </div>
</div>

<!-- View Expense Modal -->
<div class="modal fade" id="viewExpenseModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title text-white"><i class="fas fa-eye me-2"></i>Expense Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewExpenseContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>
                    <p class="mt-2">Loading expense details...</p>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Expense Modal -->
<div class="modal fade" id="editExpenseModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-warning text-white border-0">
                <h5 class="modal-title text-white"><i class="fas fa-edit me-2"></i>Edit Expense</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="editExpenseContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-warning" role="status"><span class="visually-hidden">Loading...</span></div>
                    <p class="mt-2">Loading expense details...</p>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="saveEditExpense">Update Expense</button>
            </div>
        </div>
    </div>
</div>

<form id="deleteExpenseForm" method="POST" style="display:none;">
    @csrf
    @method('DELETE')
</form>

@push('styles')
<style>
    .page-header { margin-bottom: 1.5rem; }
    .page-icon { width:50px;height:50px;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#fee2e2,#fecaca);border-radius:12px; }
    .page-title { font-size:1.75rem;font-weight:600;color:#1e293b; }
    .page-description { font-size:0.875rem; }

    /* Stat cards */
    .stat-card { background:white;border-radius:16px;padding:1rem;transition:all 0.3s ease;border:1px solid #e2e8f0; }
    .stat-card-clickable { cursor:pointer;position:relative; }
    .stat-card-clickable:hover { transform:translateY(-3px);box-shadow:0 8px 25px rgba(0,0,0,0.1);border-color:#94a3b8; }
    .stat-card-clickable:active { transform:translateY(-1px); }
    .stat-card-clickable:focus-visible { outline:2px solid #2563eb;outline-offset:2px; }
    .stat-card-body { display:flex;align-items:center;gap:1rem; }
    .stat-card-icon { width:48px;height:48px;display:flex;align-items:center;justify-content:center;border-radius:12px;font-size:1.5rem; }
    .bg-danger-soft  { background:#fee2e2; }
    .bg-warning-soft { background:#fef3c7; }
    .bg-info-soft    { background:#e0f2fe; }
    .bg-primary-soft { background:#dcfce7; }
    .bg-secondary-soft { background:#f1f5f9; }
    .bg-success-soft { background:#d1fae5; }
    .text-danger  { color:#dc2626 !important; }
    .text-warning { color:#f59e0b !important; }
    .text-info    { color:#3b82f6 !important; }
    .text-primary { color:#0d6e4f !important; }
    .text-success { color:#10b981 !important; }
    .stat-card-info { flex:1; }
    .stat-card-label { font-size:0.75rem;text-transform:uppercase;letter-spacing:0.5px;color:#64748b;font-weight:600; }
    .stat-card-value { font-size:1.5rem;font-weight:700;margin:0;line-height:1.2;color:#1e293b; }
    .stat-card-hint { font-size:0.72rem;font-weight:600;color:#94a3b8;text-align:right;margin-top:0.5rem;opacity:0;transition:opacity 0.2s; }
    .stat-card-clickable:hover .stat-card-hint { opacity:1; }

    /* Table / filter */
    .filter-section { background:#f8fafc;border-radius:12px; }
    .table th { font-weight:600;font-size:0.875rem;color:#475569;border-bottom-width:1px; }
    .table td { font-size:0.875rem;color:#334155;vertical-align:middle; }
    .badge { font-weight:500;font-size:0.75rem; }
    .empty-state { text-align:center;padding:2rem; }
    .btn-group .btn { border-radius:8px !important;margin:0 2px;padding:0.25rem 0.5rem; }
    .pagination { margin-bottom:0; }
    .page-link { border-radius:8px;margin:0 2px;border:none;color:#475569;padding:0.5rem 0.875rem; }
    .page-item.active .page-link { background-color:#dc2626;color:white; }
    .page-link:hover { background-color:#e2e8f0;color:#dc2626; }
    .detail-section { margin-bottom:1.5rem; }
    .detail-section h6 { font-weight:600;color:#1e293b;margin-bottom:1rem;padding-bottom:0.5rem;border-bottom:2px solid #e2e8f0; }
    .detail-grid { display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:1rem; }
    .detail-item { display:flex;flex-direction:column; }
    .detail-label { font-size:0.7rem;text-transform:uppercase;color:#64748b;font-weight:600;margin-bottom:0.25rem; }
    .detail-value { font-size:1rem;font-weight:500;color:#1e293b; }

    /* ---- STAT MODAL shared styles ---- */
    .smodal-icon { width:40px;height:40px;border-radius:10px;background:rgba(255,255,255,0.2);display:flex;align-items:center;justify-content:center;font-size:1.1rem; }
    .modal-custom-close { width:32px;height:32px;border-radius:8px;background:rgba(255,255,255,0.15);border:none;color:#fff;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:0.9rem;transition:background 0.15s;margin-left:auto; }
    .modal-custom-close:hover { background:rgba(255,255,255,0.3); }
    .smodal-footer { background:#f9fafb;gap:0.5rem;flex-direction:row-reverse; }

    .smodal-hero { text-align:center;padding:1.25rem 1.5rem;border-radius:12px;border:1.5px solid;margin-bottom:1.25rem; }
    .smodal-hero-label { font-size:0.72rem;text-transform:uppercase;letter-spacing:0.07em;font-weight:700;color:#6b7280;margin-bottom:0.35rem; }
    .smodal-hero-value { font-size:2rem;font-weight:800;color:#111827;letter-spacing:-0.03em;line-height:1; }
    .smodal-hero-sub { font-size:0.83rem;color:#6b7280;margin-top:0.35rem; }

    .smodal-metric { background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:0.875rem; }
    .smodal-metric-label { font-size:0.68rem;text-transform:uppercase;letter-spacing:0.06em;font-weight:700;color:#6b7280;margin-bottom:0.2rem; }
    .smodal-metric-value { font-size:1.05rem;font-weight:800;color:#111827;line-height:1.2; }
    .smodal-metric-sub { font-size:0.72rem;color:#94a3b8;margin-top:0.15rem; }

    .smodal-section-label { font-size:0.7rem;text-transform:uppercase;letter-spacing:0.07em;font-weight:700;color:#6b7280;border-bottom:1.5px solid #e5e7eb;padding-bottom:0.5rem;margin-bottom:0.875rem; }
    .smodal-bar-row { margin-bottom:0.875rem; }
    .smodal-bar-name { font-size:0.83rem;font-weight:600;color:#374151;text-transform:capitalize; }
    .smodal-bar-meta { font-size:0.83rem;color:#374151;font-weight:600; }
    .smodal-track { height:8px;background:#f1f5f9;border-radius:99px;overflow:hidden; }
    .smodal-fill { height:100%;border-radius:99px;transition:width 0.5s ease; }

    .smodal-count-card { border:1.5px solid;border-radius:12px;padding:1rem;text-align:center; }
    .smodal-count-icon { font-size:1.25rem;margin-bottom:0.4rem; }
    .smodal-count-value { font-size:1.6rem;font-weight:800;color:#111827;line-height:1; }
    .smodal-count-label { font-size:0.72rem;text-transform:uppercase;letter-spacing:0.05em;font-weight:700;color:#6b7280;margin-top:0.3rem; }

    label, .form-label, .modal-body, .modal-body p,
    .detail-section p, .detail-value, .bg-light p,
    .card-body, .card-body p { color:#1e293b !important; }
    .modal-body .bg-light { background-color:#f1f5f9 !important; }
    .form-control, .form-select { background-color:#ffffff !important;color:#1e293b !important; }
    .form-control::placeholder { color:#94a3b8 !important; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// ==================== STAT MODALS (global scope — must be outside DOMContentLoaded) ====================
const _statModals = {};

function openStatModal(type) {
    if (!_statModals[type]) {
        const el = document.getElementById('modal' + type.charAt(0).toUpperCase() + type.slice(1));
        if (!el) { console.warn('Stat modal not found:', type); return; }
        _statModals[type] = new bootstrap.Modal(el);
    }
    _statModals[type].show();
}

document.addEventListener('DOMContentLoaded', function () {

    // Wire stat cards to modals
    document.getElementById('statCardTotal')?.addEventListener('click',   () => openStatModal('total'));
    document.getElementById('statCardMonth')?.addEventListener('click',   () => openStatModal('month'));
    document.getElementById('statCardWeek')?.addEventListener('click',    () => openStatModal('week'));
    document.getElementById('statCardRecords')?.addEventListener('click', () => openStatModal('records'));

    // Keyboard support for stat cards
    document.querySelectorAll('.stat-card-clickable').forEach(card => {
        card.addEventListener('keydown', e => {
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); card.click(); }
        });
    });

    // ==================== HELPERS ====================
    function closeAllModals() {
        document.querySelectorAll('.modal.show').forEach(modal => {
            const inst = bootstrap.Modal.getInstance(modal);
            if (inst) inst.hide();
        });
        setTimeout(() => {
            document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
        }, 200);
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    }

    // ==================== FILTERS ====================
    document.getElementById('applyFilters')?.addEventListener('click', function () {
        const params = new URLSearchParams();
        const v = id => document.getElementById(id).value;
        if (v('flockFilter'))    params.append('flock_id',   v('flockFilter'));
        if (v('houseFilter'))    params.append('house_id',   v('houseFilter'));
        if (v('categoryFilter')) params.append('category',   v('categoryFilter'));
        if (v('startDateFilter'))params.append('start_date', v('startDateFilter'));
        if (v('endDateFilter'))  params.append('end_date',   v('endDateFilter'));
        window.location.href = '{{ route("expenses.index") }}' + (params.toString() ? '?' + params.toString() : '');
    });

    // ==================== CREATE EXPENSE ====================
    let createModal = null;

    function openCreateExpenseModal() {
        closeAllModals();
        const modalEl = document.getElementById('createExpenseModal');
        createModal = new bootstrap.Modal(modalEl, { backdrop: 'static', keyboard: false });
        document.getElementById('createExpenseContent').innerHTML = `<div class="text-center py-4"><div class="spinner-border text-danger" role="status"></div><p class="mt-2">Loading form...</p></div>`;
        createModal.show();

        fetch('{{ route("expenses.create-form") }}', {
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content, 'Accept': 'application/json' }
        }).then(r => r.json()).then(data => {
            if (data.success) displayExpenseCreateForm(data.flocks, data.houses);
            else document.getElementById('createExpenseContent').innerHTML = `<div class="alert alert-danger m-3">Failed to load form: ${data.message}</div>`;
        }).catch(e => {
            document.getElementById('createExpenseContent').innerHTML = `<div class="alert alert-danger m-3">Error: ${e.message}</div>`;
        });
    }

    document.getElementById('createExpenseModal')?.addEventListener('hidden.bs.modal', function () {
        if (createModal) { createModal.dispose(); createModal = null; }
        document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
    });

    function displayExpenseCreateForm(flocks, houses) {
        const fo = flocks.map(f => `<option value="${f.id}">${escapeHtml(f.flock_number)} - ${escapeHtml(f.breed_variety)}</option>`).join('');
        const ho = houses.map(h => `<option value="${h.id}">${escapeHtml(h.name)}</option>`).join('');
        document.getElementById('createExpenseContent').innerHTML = `
            <form id="createExpenseForm"><div class="row">
                <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                    <select name="category" class="form-select" required><option value="">Select Category</option>
                        <option value="feed">🐓 Feed</option><option value="veterinary">🏥 Veterinary</option>
                        <option value="medication">💊 Medication</option><option value="labor">👥 Labor</option>
                        <option value="equipment">🔧 Equipment</option><option value="utilities">💡 Utilities</option>
                        <option value="maintenance">🛠️ Maintenance</option><option value="transport">🚚 Transport</option>
                        <option value="other">📦 Other</option></select></div>
                <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Expense Date <span class="text-danger">*</span></label>
                    <input type="date" name="expense_date" class="form-control" value="${new Date().toISOString().split('T')[0]}" required></div>
                <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Description <span class="text-danger">*</span></label>
                    <input type="text" name="description" class="form-control" placeholder="e.g., 50 bags of layer mash" required></div>
                <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Amount (₵) <span class="text-danger">*</span></label>
                    <div class="input-group"><span class="input-group-text">₵</span>
                    <input type="number" name="amount" class="form-control" step="0.01" min="0.01" placeholder="0.00" required></div></div>
                <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Vendor Name</label>
                    <input type="text" name="vendor_name" class="form-control" placeholder="Supplier name"></div>
                <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Payment Method</label>
                    <select name="payment_method" class="form-select"><option value="">Select Payment Method</option>
                        <option value="cash">💵 Cash</option><option value="bank_transfer">🏦 Bank Transfer</option>
                        <option value="mobile_money">📱 Mobile Money (MoMo)</option><option value="check">📝 Check</option>
                        <option value="credit_card">💳 Credit Card</option></select></div>
                <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Receipt Number</label>
                    <input type="text" name="receipt_number" class="form-control" placeholder="Receipt/invoice number"></div>
                <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Associated Flock (Optional)</label>
                    <select name="flock_id" class="form-select"><option value="">None - General Expense</option>${fo}</select></div>
                <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Associated House (Optional)</label>
                    <select name="house_id" class="form-select"><option value="">None - General Expense</option>${ho}</select></div>
                <div class="col-12 mb-3"><label class="form-label fw-semibold">Notes</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="Additional notes..."></textarea></div>
            </div></form>`;
    }

    document.getElementById('saveCreateExpense')?.addEventListener('click', function () {
        const form = document.getElementById('createExpenseForm');
        if (!form) return;
        const fd = new FormData(form), data = {};
        fd.forEach((v, k) => { data[k] = v; });
        if (!data.category || !data.expense_date || !data.description || !data.amount) {
            Swal.fire({ icon: 'error', title: 'Validation Error', text: 'Please fill in all required fields' }); return;
        }
        const btn = this; btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
        fetch('{{ route("expenses.store-ajax") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content, 'Accept': 'application/json', 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        }).then(r => r.json()).then(res => {
            if (res.success) {
                if (createModal) createModal.hide();
                Swal.fire({ icon: 'success', title: 'Saved!', text: 'Expense recorded successfully', timer: 1500, showConfirmButton: false }).then(() => location.reload());
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: res.message || 'Failed to save expense' });
                btn.disabled = false; btn.innerHTML = 'Save Expense';
            }
        }).catch(() => {
            Swal.fire({ icon: 'error', title: 'Error', text: 'An error occurred while saving' });
            btn.disabled = false; btn.innerHTML = 'Save Expense';
        });
    });

    document.getElementById('newExpenseBtn')?.addEventListener('click', openCreateExpenseModal);
    document.getElementById('emptyStateNewBtn')?.addEventListener('click', openCreateExpenseModal);

    // ==================== VIEW EXPENSE ====================
    let viewModal = null;
    document.querySelectorAll('.view-expense-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            closeAllModals();
            const id = this.dataset.id;
            const modalEl = document.getElementById('viewExpenseModal');
            viewModal = new bootstrap.Modal(modalEl, { backdrop: 'static', keyboard: true });
            document.getElementById('viewExpenseContent').innerHTML = `<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Loading...</p></div>`;
            viewModal.show();
            fetch(`/expenses/${id}/details-json`, { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content } })
            .then(r => r.json()).then(data => {
                if (data.success) displayExpenseDetails(data.expense);
                else document.getElementById('viewExpenseContent').innerHTML = `<div class="alert alert-danger m-3">Failed: ${data.message}</div>`;
            }).catch(e => { document.getElementById('viewExpenseContent').innerHTML = `<div class="alert alert-danger m-3">Error: ${e.message}</div>`; });
        });
    });

    document.getElementById('viewExpenseModal')?.addEventListener('hidden.bs.modal', function () {
        if (viewModal) { viewModal.dispose(); viewModal = null; }
        document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
        document.body.classList.remove('modal-open'); document.body.style.overflow = ''; document.body.style.paddingRight = '';
    });

    function displayExpenseDetails(expense) {
        document.getElementById('viewExpenseContent').innerHTML = `
            <div class="detail-section"><h6><i class="fas fa-info-circle me-2"></i>Expense Information</h6>
                <div class="detail-grid">
                    <div class="detail-item"><span class="detail-label">Date</span><span class="detail-value">${expense.expense_date}</span></div>
                    <div class="detail-item"><span class="detail-label">Category</span><span class="detail-value"><span class="badge bg-secondary">${escapeHtml(expense.category)}</span></span></div>
                    <div class="detail-item"><span class="detail-label">Description</span><span class="detail-value">${escapeHtml(expense.description)}</span></div>
                    <div class="detail-item"><span class="detail-label">Amount</span><span class="detail-value text-danger fs-5 fw-bold">₵${Number(expense.amount).toLocaleString()}</span></div>
                    <div class="detail-item"><span class="detail-label">Vendor</span><span class="detail-value">${escapeHtml(expense.vendor_name || 'N/A')}</span></div>
                    <div class="detail-item"><span class="detail-label">Payment Method</span><span class="detail-value">${escapeHtml(expense.payment_method || 'N/A')}</span></div>
                    <div class="detail-item"><span class="detail-label">Receipt Number</span><span class="detail-value">${escapeHtml(expense.receipt_number || 'N/A')}</span></div>
                    <div class="detail-item"><span class="detail-label">Recorded By</span><span class="detail-value">${escapeHtml(expense.recorded_by || 'N/A')}</span></div>
                </div></div>
            <div class="detail-section"><h6><i class="fas fa-link me-2"></i>Associated Records</h6>
                <div class="detail-grid">
                    <div class="detail-item"><span class="detail-label">Flock</span><span class="detail-value">${expense.flock_number ? '<span class="badge bg-primary-soft text-primary">'+escapeHtml(expense.flock_number)+'</span>' : 'None (General Expense)'}</span></div>
                    <div class="detail-item"><span class="detail-label">House</span><span class="detail-value">${expense.house_name ? '<span class="badge bg-info-soft text-info">'+escapeHtml(expense.house_name)+'</span>' : 'None (General Expense)'}</span></div>
                </div></div>
            ${expense.notes ? `<div class="detail-section"><h6><i class="fas fa-pencil-alt me-2"></i>Notes</h6><p class="mb-0 p-3 bg-light rounded">${escapeHtml(expense.notes)}</p></div>` : ''}`;
    }

    // ==================== EDIT EXPENSE ====================
    let editModal = null, currentEditId = null;
    document.querySelectorAll('.edit-expense-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            closeAllModals();
            currentEditId = this.dataset.id;
            const modalEl = document.getElementById('editExpenseModal');
            editModal = new bootstrap.Modal(modalEl, { backdrop: 'static', keyboard: true });
            document.getElementById('editExpenseContent').innerHTML = `<div class="text-center py-4"><div class="spinner-border text-warning" role="status"></div><p class="mt-2">Loading...</p></div>`;
            editModal.show();
            fetch(`/expenses/${currentEditId}/edit-data`, { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content } })
            .then(r => r.json()).then(data => {
                if (data.success) displayExpenseEditForm(data.expense, data.flocks, data.houses);
                else document.getElementById('editExpenseContent').innerHTML = `<div class="alert alert-danger m-3">Failed: ${data.message}</div>`;
            }).catch(e => { document.getElementById('editExpenseContent').innerHTML = `<div class="alert alert-danger m-3">Error: ${e.message}</div>`; });
        });
    });

    document.getElementById('editExpenseModal')?.addEventListener('hidden.bs.modal', function () {
        if (editModal) { editModal.dispose(); editModal = null; }
        currentEditId = null;
        document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
        document.body.classList.remove('modal-open'); document.body.style.overflow = ''; document.body.style.paddingRight = '';
    });

    function displayExpenseEditForm(expense, flocks, houses) {
        const fo = flocks.map(f => `<option value="${f.id}" ${expense.flock_id==f.id?'selected':''}>${escapeHtml(f.flock_number)} - ${escapeHtml(f.breed_variety)}</option>`).join('');
        const ho = houses.map(h => `<option value="${h.id}" ${expense.house_id==h.id?'selected':''}>${escapeHtml(h.name)}</option>`).join('');
        const cats = [['feed','🐓 Feed'],['veterinary','🏥 Veterinary'],['medication','💊 Medication'],['labor','👥 Labor'],['equipment','🔧 Equipment'],['utilities','💡 Utilities'],['maintenance','🛠️ Maintenance'],['transport','🚚 Transport'],['other','📦 Other']];
        const catOpts = cats.map(([v,l]) => `<option value="${v}" ${expense.category==v?'selected':''}>${l}</option>`).join('');
        const pms = [['cash','💵 Cash'],['bank_transfer','🏦 Bank Transfer'],['mobile_money','📱 Mobile Money (MoMo)'],['check','📝 Check'],['credit_card','💳 Credit Card']];
        const pmOpts = pms.map(([v,l]) => `<option value="${v}" ${expense.payment_method==v?'selected':''}>${l}</option>`).join('');
        document.getElementById('editExpenseContent').innerHTML = `
            <form id="editExpenseForm"><div class="row">
                <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Category <span class="text-danger">*</span></label><select name="category" class="form-select" required>${catOpts}</select></div>
                <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Expense Date <span class="text-danger">*</span></label><input type="date" name="expense_date" class="form-control" value="${escapeHtml(expense.expense_date)}" required></div>
                <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Description <span class="text-danger">*</span></label><input type="text" name="description" class="form-control" value="${escapeHtml(expense.description)}" required></div>
                <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Amount (₵) <span class="text-danger">*</span></label><div class="input-group"><span class="input-group-text">₵</span><input type="number" name="amount" class="form-control" step="0.01" min="0.01" value="${escapeHtml(expense.amount)}" required></div></div>
                <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Vendor Name</label><input type="text" name="vendor_name" class="form-control" value="${escapeHtml(expense.vendor_name||'')}"></div>
                <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Payment Method</label><select name="payment_method" class="form-select"><option value="">Select Payment Method</option>${pmOpts}</select></div>
                <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Receipt Number</label><input type="text" name="receipt_number" class="form-control" value="${escapeHtml(expense.receipt_number||'')}"></div>
                <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Associated Flock</label><select name="flock_id" class="form-select"><option value="">None - General Expense</option>${fo}</select></div>
                <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Associated House</label><select name="house_id" class="form-select"><option value="">None - General Expense</option>${ho}</select></div>
                <div class="col-12 mb-3"><label class="form-label fw-semibold">Notes</label><textarea name="notes" class="form-control" rows="2">${escapeHtml(expense.notes||'')}</textarea></div>
            </div></form>`;
    }

    document.getElementById('saveEditExpense')?.addEventListener('click', function () {
        const form = document.getElementById('editExpenseForm');
        if (!form) return;
        const fd = new FormData(form), data = {};
        fd.forEach((v, k) => { data[k] = v; });
        const btn = this; btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Updating...';
        fetch(`/expenses/${currentEditId}/update-ajax`, {
            method: 'PUT',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content, 'Accept': 'application/json', 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        }).then(r => r.json()).then(res => {
            if (res.success) {
                if (editModal) editModal.hide();
                Swal.fire({ icon: 'success', title: 'Updated!', text: 'Expense updated successfully', timer: 1500, showConfirmButton: false }).then(() => location.reload());
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: res.message || 'Failed to update expense' });
                btn.disabled = false; btn.innerHTML = 'Update Expense';
            }
        }).catch(() => { btn.disabled = false; btn.innerHTML = 'Update Expense'; });
    });

    // ==================== DELETE EXPENSE ====================
    document.querySelectorAll('.delete-expense-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.dataset.id, info = this.dataset.info;
            Swal.fire({
                title: 'Delete Expense', text: `Are you sure you want to delete "${info}"?`,
                icon: 'warning', showCancelButton: true,
                confirmButtonColor: '#dc2626', cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!', cancelButtonText: 'Cancel'
            }).then(result => {
                if (result.isConfirmed) {
                    const form = document.getElementById('deleteExpenseForm');
                    form.action = `/expenses/${id}`; form.submit();
                }
            });
        });
    });

});

// Make globally available for any sidebar or external calls
window.openCreateExpenseModal = function () {
    document.getElementById('newExpenseBtn')?.click();
};
</script>
@endpush
@endsection