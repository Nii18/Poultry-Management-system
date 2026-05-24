{{-- resources/views/expenses/by-category.blade.php --}}
@extends('layouts.master')

@section('content')
<div class="container-fluid px-4">

    <!-- Page Header -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <div class="d-flex align-items-center gap-3">
                    <div class="page-icon bg-danger-soft">
                        <i class="fas fa-chart-pie fs-1 text-danger"></i>
                    </div>
                    <div>
                        <h1 class="page-title mb-1">Expenses by Category</h1>
                        <p class="page-description text-muted mb-0">
                            Financial breakdown and analysis for {{ $year }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-auto">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('expenses.index') }}">Expenses</a></li>
                        <li class="breadcrumb-item active">By Category</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <!-- Stats Cards — each is clickable -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="stat-card stat-card-clickable" onclick="openStatModal('total')" role="button" tabindex="0" title="Click for details">
                <div class="stat-card-body">
                    <div class="stat-card-icon bg-primary-soft">
                        <i class="fas fa-dollar-sign text-primary"></i>
                    </div>
                    <div class="stat-card-info">
                        <span class="stat-card-label">Total Expenses</span>
                        <h3 class="stat-card-value">₵{{ number_format($totalExpenses, 2) }}</h3>
                    </div>
                </div>
                <div class="stat-card-hint"><i class="fas fa-arrow-right me-1"></i>View breakdown</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card stat-card-clickable" onclick="openStatModal('categories')" role="button" tabindex="0" title="Click for details">
                <div class="stat-card-body">
                    <div class="stat-card-icon bg-warning-soft">
                        <i class="fas fa-layer-group text-warning"></i>
                    </div>
                    <div class="stat-card-info">
                        <span class="stat-card-label">Total Categories</span>
                        <h3 class="stat-card-value">{{ $expensesByCategory->count() }}</h3>
                    </div>
                </div>
                <div class="stat-card-hint"><i class="fas fa-arrow-right me-1"></i>View categories</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card stat-card-clickable" onclick="openStatModal('monthly')" role="button" tabindex="0" title="Click for details">
                <div class="stat-card-body">
                    <div class="stat-card-icon bg-success-soft">
                        <i class="fas fa-chart-line text-success"></i>
                    </div>
                    <div class="stat-card-info">
                        <span class="stat-card-label">Monthly Avg</span>
                        <h3 class="stat-card-value">₵{{ number_format($totalExpenses / 12, 2) }}</h3>
                    </div>
                </div>
                <div class="stat-card-hint"><i class="fas fa-arrow-right me-1"></i>View monthly trend</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card stat-card-clickable" onclick="openStatModal('top')" role="button" tabindex="0" title="Click for details">
                <div class="stat-card-body">
                    <div class="stat-card-icon bg-info-soft">
                        <i class="fas fa-trophy text-info"></i>
                    </div>
                    <div class="stat-card-info">
                        <span class="stat-card-label">Top Category</span>
                        <h3 class="stat-card-value" style="font-size:1.2rem; text-transform:capitalize;">{{ $expensesByCategory->first()?->category ?? 'N/A' }}</h3>
                    </div>
                </div>
                <div class="stat-card-hint"><i class="fas fa-arrow-right me-1"></i>View ranking</div>
            </div>
        </div>
    </div>

    <!-- Year Filter -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body py-3">
                    <form method="GET" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold mb-2">
                                <i class="fas fa-calendar-alt text-muted me-1"></i>Select Year
                            </label>
                            <select name="year" class="form-select" onchange="this.form.submit()">
                                @php
                                    $currentYear = date('Y');
                                    $startYear = 2026;
                                @endphp
                                @for($y = $startYear; $y <= $currentYear; $y++)
                                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter me-2"></i>Apply Filter
                            </button>
                            <a href="{{ route('expenses.by-category') }}" class="btn btn-outline-secondary ms-2">
                                <i class="fas fa-redo-alt me-2"></i>Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- ===================== 2x2 MAIN DASHBOARD GRID ===================== -->
    <div class="row g-4">

        <!-- 1. CATEGORY TABLE (Top Left) -->
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0 py-3">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-danger-soft p-2 me-3">
                            <i class="fas fa-chart-simple text-danger"></i>
                        </div>
                        <div>
                            <h5 class="card-title mb-0 fw-semibold">Expenses by Category</h5>
                            <small class="text-muted">Detailed breakdown of all expenses</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="py-3">Category</th>
                                    <th class="py-3 text-end">Total Amount</th>
                                    <th class="py-3 text-end">Percentage</th>
                                    <th class="py-3">Distribution</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($expensesByCategory as $expense)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="category-icon" style="background: {{ $loop->iteration % 2 == 0 ? '#fef3c7' : '#dcfce7' }}; width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-tag text-muted"></i>
                                            </div>
                                            <span class="fw-semibold">{{ ucfirst($expense->category) }}</span>
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <span class="fw-bold text-danger">₵{{ number_format($expense->total, 2) }}</span>
                                    </td>
                                    <td class="text-end">
                                        <span class="badge bg-secondary-soft text-secondary px-3 py-2">
                                            {{ number_format(($expense->total / $totalExpenses) * 100, 1) }}%
                                        </span>
                                    </td>
                                    <td style="width: 35%;">
                                        <div class="progress" style="height: 8px; border-radius: 10px;">
                                            <div class="progress-bar bg-danger"
                                                 style="width: {{ ($expense->total / $totalExpenses) * 100 }}%; border-radius: 10px;">
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-muted">
                                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                        No expense data for {{ $year }}
                                    </td>
                                <tr>
                                @endforelse
                            </tbody>
                            @if($expensesByCategory->isNotEmpty())
                            <tfoot class="table-light">
                                <tr>
                                    <td class="py-3 fw-bold">Total</td>
                                    <td class="text-end fw-bold text-danger">₵{{ number_format($totalExpenses, 2) }}</td>
                                    <td class="text-end fw-bold">100%</td>
                                    <td>
                                        <div class="progress" style="height: 8px; border-radius: 10px;">
                                            <div class="progress-bar bg-danger" style="width: 100%; border-radius: 10px;"></div>
                                        </div>
                                    </td>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. PIE CHART (Top Right) -->
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0 py-3">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-success-soft p-2 me-3">
                            <i class="fas fa-chart-pie text-success"></i>
                        </div>
                        <div>
                            <h5 class="card-title mb-0 fw-semibold">Expense Distribution</h5>
                            <small class="text-muted">Percentage by category</small>
                        </div>
                    </div>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    <canvas id="pieChart" height="260" style="max-width: 100%;"></canvas>
                </div>
            </div>
        </div>

        <!-- 3. MONTHLY TREND (Bottom Left) -->
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0 py-3">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-info-soft p-2 me-3">
                            <i class="fas fa-chart-line text-info"></i>
                        </div>
                        <div>
                            <h5 class="card-title mb-0 fw-semibold">Monthly Trend</h5>
                            <small class="text-muted">Expense distribution throughout the year</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="monthlyChart" height="220"></canvas>
                </div>
            </div>
        </div>

        <!-- 4. MONTH BY MONTH TABLE (Bottom Right) -->
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0 py-3">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-warning-soft p-2 me-3">
                            <i class="fas fa-calendar-alt text-warning"></i>
                        </div>
                        <div>
                            <h5 class="card-title mb-0 fw-semibold">Month by Month</h5>
                            <small class="text-muted">Detailed monthly totals for {{ $year }}</small>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    @php
                        $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                        $monthlyMap = $monthlyBreakdown->keyBy('month');
                        $maxMonthly = $monthlyBreakdown->max('total') ?: 1;
                    @endphp
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="py-2 ps-3" style="font-size:0.78rem;">Month</th>
                                <th class="py-2" style="font-size:0.78rem;">Trend</th>
                                <th class="py-2 pe-3 text-end" style="font-size:0.78rem;">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @for($m = 1; $m <= 12; $m++)
                                @php
                                    $mData = $monthlyMap->get($m);
                                    $mAmt  = $mData ? $mData->total : 0;
                                    $barPct = $maxMonthly > 0 ? ($mAmt / $maxMonthly) * 100 : 0;
                                @endphp
                                <tr style="{{ $mAmt == 0 ? 'opacity:0.45;' : '' }}">
                                    <td class="py-2 ps-3">
                                        <span style="font-size:0.83rem; font-weight:600; color:#374151;">{{ $monthNames[$m-1] }}</span>
                                    </td>
                                    <td class="py-2" style="width:45%;">
                                        <div style="height:6px; background:#f1f5f9; border-radius:99px; overflow:hidden;">
                                            <div style="height:100%; width:{{ $barPct }}%; background:#dc2626; border-radius:99px;"></div>
                                        </div>
                                    </td>
                                    <td class="py-2 pe-3 text-end">
                                        @if($mAmt > 0)
                                            <span style="font-size:0.83rem; font-weight:700; color:#dc2626;">₵{{ number_format($mAmt, 2) }}</span>
                                        @else
                                            <span class="text-muted" style="font-size:0.83rem;">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endfor
                        </tbody>
                        @if($monthlyBreakdown->isNotEmpty())
                        <tfoot class="table-light">
                            <tr>
                                <td class="py-2 ps-3 fw-bold" style="font-size:0.83rem;">Total</td>
                                <td class="py-2"></td>
                                <td class="py-2 pe-3 text-end fw-bold text-danger" style="font-size:0.83rem;">₵{{ number_format($totalExpenses, 2) }}</td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>

    </div>
    <!-- ===================== END 2x2 GRID ===================== -->

</div>

<!-- =================== STAT DETAIL MODALS =================== -->

<!-- Modal: Total Expenses -->
<div class="modal fade" id="modalTotal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius:16px; overflow:hidden;">
            <div class="modal-header border-0 text-white" style="background: linear-gradient(135deg,#2563eb,#1d4ed8); padding:1.25rem 1.5rem;">
                <div class="d-flex align-items-center gap-3">
                    <div style="width:40px;height:40px;border-radius:10px;background:rgba(255,255,255,0.2);display:flex;align-items:center;justify-content:center;font-size:1.1rem;">
                        <i class="fas fa-coins"></i>
                    </div>
                    <div>
                        <h5 class="mb-0 text-white">Total Expenses — {{ $year }}</h5>
                        <small class="opacity-75">Complete financial summary</small>
                    </div>
                </div>
                <button type="button" class="modal-custom-close" data-bs-dismiss="modal"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body p-4">
                <div class="text-center p-4 mb-3" style="background:#eff6ff;border:1.5px solid #bfdbfe;border-radius:12px;">
                    <div style="font-size:0.72rem;text-transform:uppercase;letter-spacing:0.07em;font-weight:700;color:#6b7280;margin-bottom:0.4rem;">Total Expenditure {{ $year }}</div>
                    <div style="font-size:2rem;font-weight:800;color:#111827;letter-spacing:-0.03em;">₵{{ number_format($totalExpenses, 2) }}</div>
                    <div style="font-size:0.83rem;color:#6b7280;">Across {{ $expensesByCategory->count() }} categories</div>
                </div>

                <div class="row g-3 mb-4">
                    @php
                        $highestMonth = $monthlyBreakdown->sortByDesc('total')->first();
                        $lowestMonth  = $monthlyBreakdown->where('total', '>', 0)->sortBy('total')->first();
                        $monthNArr    = ['','Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                    @endphp
                    <div class="col-6">
                        <div class="p-3" style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;">
                            <div style="font-size:0.7rem;text-transform:uppercase;letter-spacing:0.06em;font-weight:700;color:#6b7280;margin-bottom:0.3rem;">Highest Month</div>
                            <div style="font-size:1.1rem;font-weight:800;color:#111827;">{{ $highestMonth ? $monthNArr[$highestMonth->month] : '—' }}</div>
                            <div style="font-size:0.75rem;color:#6b7280;">₵{{ number_format($highestMonth?->total ?? 0, 2) }}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3" style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;">
                            <div style="font-size:0.7rem;text-transform:uppercase;letter-spacing:0.06em;font-weight:700;color:#6b7280;margin-bottom:0.3rem;">Lowest Month</div>
                            <div style="font-size:1.1rem;font-weight:800;color:#111827;">{{ $lowestMonth ? $monthNArr[$lowestMonth->month] : '—' }}</div>
                            <div style="font-size:0.75rem;color:#6b7280;">₵{{ number_format($lowestMonth?->total ?? 0, 2) }}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3" style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;">
                            <div style="font-size:0.7rem;text-transform:uppercase;letter-spacing:0.06em;font-weight:700;color:#6b7280;margin-bottom:0.3rem;">Monthly Average</div>
                            <div style="font-size:1.1rem;font-weight:800;color:#111827;">₵{{ number_format($totalExpenses / 12, 2) }}</div>
                            <div style="font-size:0.75rem;color:#6b7280;">Based on 12 months</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3" style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;">
                            <div style="font-size:0.7rem;text-transform:uppercase;letter-spacing:0.06em;font-weight:700;color:#6b7280;margin-bottom:0.3rem;">Active Months</div>
                            <div style="font-size:1.1rem;font-weight:800;color:#111827;">{{ $monthlyBreakdown->count() }}</div>
                            <div style="font-size:0.75rem;color:#6b7280;">Months with spend</div>
                        </div>
                    </div>
                </div>

                <div style="font-size:0.7rem;text-transform:uppercase;letter-spacing:0.07em;font-weight:700;color:#6b7280;border-bottom:1.5px solid #e5e7eb;padding-bottom:0.5rem;margin-bottom:0.875rem;">Category Contribution</div>
                @foreach($expensesByCategory as $e)
                @php $pct = $totalExpenses > 0 ? ($e->total/$totalExpenses)*100 : 0; @endphp
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span style="font-size:0.83rem;font-weight:600;color:#374151;text-transform:capitalize;">{{ $e->category }}</span>
                        <span style="font-size:0.83rem;color:#6b7280;">₵{{ number_format($e->total,2) }} ({{ number_format($pct,1) }}%)</span>
                    </div>
                    <div style="height:8px;background:#f1f5f9;border-radius:99px;overflow:hidden;">
                        <div style="height:100%;width:{{ $pct }}%;background:#2563eb;border-radius:99px;"></div>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="modal-footer border-0" style="background:#f9fafb; flex-direction:row-reverse; gap:0.5rem;">
                <a href="{{ route('expenses.index') }}" class="btn btn-primary btn-sm"><i class="fas fa-list me-2"></i>View All Records</a>
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Total Categories -->
<div class="modal fade" id="modalCategories" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius:16px; overflow:hidden;">
            <div class="modal-header border-0 text-white" style="background: linear-gradient(135deg,#d97706,#b45309); padding:1.25rem 1.5rem;">
                <div class="d-flex align-items-center gap-3">
                    <div style="width:40px;height:40px;border-radius:10px;background:rgba(255,255,255,0.2);display:flex;align-items:center;justify-content:center;font-size:1.1rem;">
                        <i class="fas fa-layer-group"></i>
                    </div>
                    <div>
                        <h5 class="mb-0 text-white">Expense Categories — {{ $year }}</h5>
                        <small class="opacity-75">Per-category analysis</small>
                    </div>
                </div>
                <button type="button" class="modal-custom-close" data-bs-dismiss="modal"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body p-4">
                <div class="text-center p-4 mb-3" style="background:#fffbeb;border:1.5px solid #fde68a;border-radius:12px;">
                    <div style="font-size:0.72rem;text-transform:uppercase;letter-spacing:0.07em;font-weight:700;color:#6b7280;margin-bottom:0.4rem;">Active Spend Categories</div>
                    <div style="font-size:2rem;font-weight:800;color:#111827;letter-spacing:-0.03em;">{{ $expensesByCategory->count() }}</div>
                    <div style="font-size:0.83rem;color:#6b7280;">Spending types recorded in {{ $year }}</div>
                </div>

                @php
                    $catColors = ['feed'=>['bg'=>'#ecfdf5','bdr'=>'#6ee7b7','bar'=>'#059669','icon'=>'fa-seedling'],'veterinary'=>['bg'=>'#eff6ff','bdr'=>'#93c5fd','bar'=>'#2563eb','icon'=>'fa-stethoscope'],'medication'=>['bg'=>'#fdf4ff','bdr'=>'#e879f9','bar'=>'#7c3aed','icon'=>'fa-pills'],'labor'=>['bg'=>'#fff7ed','bdr'=>'#fdba74','bar'=>'#d97706','icon'=>'fa-users'],'equipment'=>['bg'=>'#f8fafc','bdr'=>'#cbd5e1','bar'=>'#475569','icon'=>'fa-tools'],'utilities'=>['bg'=>'#fefce8','bdr'=>'#fde047','bar'=>'#ca8a04','icon'=>'fa-bolt'],'maintenance'=>['bg'=>'#fef2f2','bdr'=>'#fca5a5','bar'=>'#dc2626','icon'=>'fa-wrench'],'transport'=>['bg'=>'#ecfeff','bdr'=>'#67e8f9','bar'=>'#0891b2','icon'=>'fa-truck'],'other'=>['bg'=>'#f9fafb','bdr'=>'#e5e7eb','bar'=>'#6b7280','icon'=>'fa-tag']];
                @endphp
                <div class="row g-3">
                    @foreach($expensesByCategory as $exp)
                    @php
                        $cc = $catColors[$exp->category] ?? $catColors['other'];
                        $pct2 = $totalExpenses > 0 ? ($exp->total/$totalExpenses)*100 : 0;
                    @endphp
                    <div class="col-md-6">
                        <div class="p-3" style="background:{{ $cc['bg'] }};border:1px solid {{ $cc['bdr'] }};border-radius:10px;border-left:4px solid {{ $cc['bar'] }};">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <div style="width:28px;height:28px;border-radius:7px;background:{{ $cc['bar'] }}1a;display:flex;align-items:center;justify-content:center;color:{{ $cc['bar'] }};font-size:0.85rem;">
                                    <i class="fas {{ $cc['icon'] }}"></i>
                                </div>
                                <span style="font-size:0.875rem;font-weight:700;color:#111827;text-transform:capitalize;">{{ $exp->category }}</span>
                            </div>
                            <div style="font-size:1.1rem;font-weight:800;color:#111827;">₵{{ number_format($exp->total,2) }}</div>
                            <div style="font-size:0.75rem;color:#6b7280;margin-bottom:0.5rem;">{{ number_format($pct2,1) }}% of total spend</div>
                            <div style="height:6px;background:rgba(0,0,0,0.08);border-radius:99px;overflow:hidden;">
                                <div style="height:100%;width:{{ $pct2 }}%;background:{{ $cc['bar'] }};border-radius:99px;"></div>
                            </div>
                            <a href="{{ route('expenses.index') }}?category={{ $exp->category }}" style="font-size:0.75rem;font-weight:600;color:{{ $cc['bar'] }};text-decoration:none;display:inline-block;margin-top:0.6rem;">
                                View records <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="modal-footer border-0" style="background:#f9fafb;">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Monthly Average -->
<div class="modal fade" id="modalMonthly" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius:16px; overflow:hidden;">
            <div class="modal-header border-0 text-white" style="background: linear-gradient(135deg,#059669,#047857); padding:1.25rem 1.5rem;">
                <div class="d-flex align-items-center gap-3">
                    <div style="width:40px;height:40px;border-radius:10px;background:rgba(255,255,255,0.2);display:flex;align-items:center;justify-content:center;font-size:1.1rem;">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div>
                        <h5 class="mb-0 text-white">Monthly Average — {{ $year }}</h5>
                        <small class="opacity-75">Monthly trend and average analysis</small>
                    </div>
                </div>
                <button type="button" class="modal-custom-close" data-bs-dismiss="modal"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body p-4">
                @php
                    $monthlyAvg     = $totalExpenses / 12;
                    $activeMonths   = $monthlyBreakdown->count();
                    $avgActiveOnly  = $activeMonths > 0 ? ($monthlyBreakdown->sum('total') / $activeMonths) : 0;
                @endphp
                <div class="text-center p-4 mb-3" style="background:#ecfdf5;border:1.5px solid #a7f3d0;border-radius:12px;">
                    <div style="font-size:0.72rem;text-transform:uppercase;letter-spacing:0.07em;font-weight:700;color:#6b7280;margin-bottom:0.4rem;">Average Monthly Spend</div>
                    <div style="font-size:2rem;font-weight:800;color:#111827;letter-spacing:-0.03em;">₵{{ number_format($monthlyAvg, 2) }}</div>
                    <div style="font-size:0.83rem;color:#6b7280;">Calculated over 12 months of {{ $year }}</div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-6">
                        <div class="p-3" style="background:#ecfdf5;border:1px solid #a7f3d0;border-radius:10px;">
                            <div style="font-size:0.7rem;text-transform:uppercase;letter-spacing:0.06em;font-weight:700;color:#6b7280;margin-bottom:0.3rem;">Avg (Active Months)</div>
                            <div style="font-size:1.1rem;font-weight:800;color:#111827;">₵{{ number_format($avgActiveOnly, 2) }}</div>
                            <div style="font-size:0.75rem;color:#6b7280;">{{ $activeMonths }} months with data</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3" style="background:#ecfdf5;border:1px solid #a7f3d0;border-radius:10px;">
                            <div style="font-size:0.7rem;text-transform:uppercase;letter-spacing:0.06em;font-weight:700;color:#6b7280;margin-bottom:0.3rem;">Avg (Full Year)</div>
                            <div style="font-size:1.1rem;font-weight:800;color:#111827;">₵{{ number_format($monthlyAvg, 2) }}</div>
                            <div style="font-size:0.75rem;color:#6b7280;">12-month division</div>
                        </div>
                    </div>
                </div>

                <div style="font-size:0.7rem;text-transform:uppercase;letter-spacing:0.07em;font-weight:700;color:#6b7280;border-bottom:1.5px solid #e5e7eb;padding-bottom:0.5rem;margin-bottom:0.875rem;">Month-by-Month vs Average</div>
                @php
                    $mMapM = $monthlyBreakdown->keyBy('month');
                    $mNamesM = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                @endphp
                @for($m = 1; $m <= 12; $m++)
                @php
                    $mD   = $mMapM->get($m);
                    $mAmt = $mD ? $mD->total : 0;
                    $above = $mAmt > $monthlyAvg;
                    $barW  = $monthlyAvg > 0 ? min(100, ($mAmt / max($monthlyAvg * 1.5, 1)) * 100) : 0;
                @endphp
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span style="flex:0 0 36px;font-size:0.78rem;font-weight:600;color:#6b7280;">{{ $mNamesM[$m-1] }}</span>
                    <div style="flex:1;height:16px;background:#f1f5f9;border-radius:4px;position:relative;overflow:hidden;">
                        <div style="height:100%;width:{{ $barW }}%;border-radius:4px;background:{{ $above ? '#fca5a5' : '#93c5fd' }};"></div>
                        <div style="position:absolute;top:0;bottom:0;left:{{ min(100,(1/1.5)*100) }}%;width:2px;background:#374151;"></div>
                    </div>
                    <span style="flex:0 0 95px;font-size:0.78rem;font-weight:600;text-align:right;color:{{ $above ? '#dc2626' : '#6b7280' }};">
                        {{ $mAmt > 0 ? '₵'.number_format($mAmt,0) : '—' }}
                    </span>
                </div>
                @endfor
                <p style="font-size:0.72rem;color:#6b7280;margin-top:0.75rem;">
                    <span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#fca5a5;vertical-align:middle;"></span> Above average &nbsp;
                    <span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#93c5fd;vertical-align:middle;"></span> Below average &nbsp;
                    <span style="display:inline-block;width:14px;height:2px;background:#374151;vertical-align:middle;"></span> Avg line
                </p>
            </div>
            <div class="modal-footer border-0" style="background:#f9fafb;">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Top Category -->
<div class="modal fade" id="modalTop" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius:16px; overflow:hidden;">
            <div class="modal-header border-0 text-white" style="background: linear-gradient(135deg,#0891b2,#0e7490); padding:1.25rem 1.5rem;">
                <div class="d-flex align-items-center gap-3">
                    <div style="width:40px;height:40px;border-radius:10px;background:rgba(255,255,255,0.2);display:flex;align-items:center;justify-content:center;font-size:1.1rem;">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <div>
                        <h5 class="mb-0 text-white">Top Category — {{ $year }}</h5>
                        <small class="opacity-75">Highest expenditure category analysis</small>
                    </div>
                </div>
                <button type="button" class="modal-custom-close" data-bs-dismiss="modal"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body p-4">
                @php
                    $topCat = $expensesByCategory->first();
                    $topPct = $totalExpenses > 0 && $topCat ? ($topCat->total / $totalExpenses) * 100 : 0;
                    $rankMedals = ['🥇','🥈','🥉'];
                @endphp
                @if($topCat)
                <div class="text-center p-4 mb-3" style="background:#ecfeff;border:1.5px solid #a5f3fc;border-radius:12px;">
                    <div style="font-size:0.72rem;text-transform:uppercase;letter-spacing:0.07em;font-weight:700;color:#6b7280;margin-bottom:0.4rem;">🏆 Biggest Spend Category</div>
                    <div style="font-size:1.75rem;font-weight:800;color:#111827;letter-spacing:-0.02em;text-transform:capitalize;">{{ $topCat->category }}</div>
                    <div style="font-size:0.83rem;color:#6b7280;">₵{{ number_format($topCat->total,2) }} — {{ number_format($topPct,1) }}% of total</div>
                </div>

                <div class="mb-4">
                    <div style="font-size:0.7rem;text-transform:uppercase;letter-spacing:0.06em;font-weight:700;color:#6b7280;margin-bottom:0.5rem;">Spend Share</div>
                    <div style="height:12px;background:#f1f5f9;border-radius:99px;overflow:hidden;">
                        <div style="height:100%;width:{{ $topPct }}%;background:#0891b2;border-radius:99px;"></div>
                    </div>
                    <div class="d-flex justify-content-between mt-1">
                        <span style="font-size:0.72rem;color:#6b7280;">0%</span>
                        <span style="font-size:0.72rem;font-weight:700;color:#0891b2;">{{ number_format($topPct,1) }}%</span>
                        <span style="font-size:0.72rem;color:#6b7280;">100%</span>
                    </div>
                </div>

                <div style="font-size:0.7rem;text-transform:uppercase;letter-spacing:0.07em;font-weight:700;color:#6b7280;border-bottom:1.5px solid #e5e7eb;padding-bottom:0.5rem;margin-bottom:0.875rem;">Full Category Ranking</div>
                @foreach($expensesByCategory as $idx => $e)
                @php $rpct = $totalExpenses > 0 ? ($e->total/$totalExpenses)*100 : 0; @endphp
                <div class="d-flex align-items-center gap-2 mb-2 p-2 rounded {{ $idx===0 ? '' : '' }}" style="{{ $idx===0 ? 'background:#ecfeff;border-radius:8px;' : '' }}">
                    <span style="flex:0 0 28px;font-size:1rem;">{{ $rankMedals[$idx] ?? '#'.($idx+1) }}</span>
                    <span style="flex:0 0 110px;font-size:0.83rem;font-weight:600;color:#374151;text-transform:capitalize;">{{ $e->category }}</span>
                    <div style="flex:1;height:8px;background:#f1f5f9;border-radius:99px;overflow:hidden;">
                        <div style="height:100%;width:{{ $rpct }}%;background:{{ $idx===0?'#0891b2':'#94a3b8' }};border-radius:99px;"></div>
                    </div>
                    <span style="flex:0 0 42px;font-size:0.75rem;font-weight:600;color:#6b7280;text-align:right;">{{ number_format($rpct,1) }}%</span>
                    <span style="flex:0 0 100px;font-size:0.8rem;font-weight:700;color:#374151;text-align:right;">₵{{ number_format($e->total,2) }}</span>
                </div>
                @endforeach

                <div class="text-center mt-4">
                    <a href="{{ route('expenses.index') }}?category={{ $topCat->category }}" class="btn btn-sm" style="background:#0891b2;color:#fff;border-radius:8px;">
                        <i class="fas fa-search me-2"></i>View {{ ucfirst($topCat->category) }} Expenses
                    </a>
                </div>
                @else
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-inbox fa-2x mb-2 d-block"></i>No expense data available for {{ $year }}
                </div>
                @endif
            </div>
            <div class="modal-footer border-0" style="background:#f9fafb;">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* Page Header Styles */
    .page-header {
        margin-bottom: 1.5rem;
    }
    
    .page-icon {
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #e8f4f8 0%, #d1e9f0 100%);
        border-radius: 12px;
    }
    
    .page-title {
        font-size: 1.75rem;
        font-weight: 600;
        color: #1e293b;
    }
    
    .page-description {
        font-size: 0.875rem;
    }
    
    /* Stat Cards */
    .stat-card {
        background: white;
        border-radius: 16px;
        padding: 1rem;
        transition: all 0.3s ease;
        border: 1px solid #e2e8f0;
    }
    
    .stat-card-clickable {
        cursor: pointer;
        position: relative;
    }
    
    .stat-card-clickable:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        border-color: #94a3b8;
    }

    .stat-card-clickable:active {
        transform: translateY(-1px);
    }

    .stat-card-clickable:focus-visible {
        outline: 2px solid #2563eb;
        outline-offset: 2px;
    }
    
    .stat-card-body {
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    
    .stat-card-icon {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        font-size: 1.5rem;
    }
    
    .bg-primary-soft { background: #e0f2fe; }
    .bg-success-soft { background: #dcfce7; }
    .bg-warning-soft { background: #fef3c7; }
    .bg-info-soft    { background: #d1fae5; }
    .bg-danger-soft  { background: #fee2e2; }
    
    .stat-card-info {
        flex: 1;
    }
    
    .stat-card-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #64748b;
        font-weight: 600;
    }
    
    .stat-card-value {
        font-size: 1.5rem;
        font-weight: 700;
        margin: 0;
        line-height: 1.2;
        color: #1e293b;
    }

    .stat-card-hint {
        font-size: 0.72rem;
        font-weight: 600;
        color: #94a3b8;
        text-align: right;
        margin-top: 0.5rem;
        opacity: 0;
        transition: opacity 0.2s;
    }

    .stat-card-clickable:hover .stat-card-hint {
        opacity: 1;
    }
    
    /* Badge Styles */
    .bg-secondary-soft {
        background-color: #f1f5f9;
        color: #475569;
    }
    
    /* Table Styles */
    .table th {
        font-weight: 600;
        font-size: 0.875rem;
        color: #475569;
        border-bottom-width: 1px;
    }
    
    .table td {
        font-size: 0.875rem;
        color: #334155;
        vertical-align: middle;
    }
    
    /* Progress Bar */
    .progress {
        background-color: #e2e8f0;
        overflow: hidden;
    }

    /* Modal close button */
    .modal-custom-close {
        width: 32px; height: 32px;
        border-radius: 8px;
        background: rgba(255,255,255,0.15);
        border: none; color: #fff;
        display: flex; align-items: center; justify-content: center;
        cursor: pointer; font-size: 0.9rem;
        transition: background 0.15s;
        margin-left: auto;
    }
    .modal-custom-close:hover { background: rgba(255,255,255,0.3); }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Monthly Breakdown Chart
    const monthlyData = @json($monthlyBreakdown);
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    const values = Array(12).fill(0);
    
    monthlyData.forEach(item => {
        values[item.month - 1] = item.total;
    });
    
    new Chart(document.getElementById('monthlyChart'), {
        type: 'line',
        data: {
            labels: months,
            datasets: [{
                label: 'Expenses (₵)',
                data: values,
                backgroundColor: 'rgba(220, 53, 69, 0.1)',
                borderColor: '#dc3545',
                borderWidth: 2,
                pointBackgroundColor: '#dc3545',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `₵${context.parsed.y.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₵' + (value >= 1000 ? (value/1000).toFixed(1)+'K' : value);
                        },
                        color: '#94a3b8',
                        font: { size: 11 }
                    },
                    grid: { color: 'rgba(0,0,0,0.04)' }
                },
                x: {
                    ticks: { color: '#94a3b8', font: { size: 11 } },
                    grid: { display: false }
                }
            }
        }
    });

    // Pie Chart for Category Distribution
    const pieCategories = @json($expensesByCategory->pluck('category')->map(fn($c) => ucfirst($c)));
    const pieValues = @json($expensesByCategory->pluck('total'));
    const pieColors = ['#dc2626', '#f59e0b', '#10b981', '#3b82f6', '#8b5cf6', '#ec4899', '#06b6d4', '#84cc16', '#6b7280', '#ef4444', '#f97316', '#14b8a6'];
    
    const pieCtx = document.getElementById('pieChart').getContext('2d');
    new Chart(pieCtx, {
        type: 'pie',
        data: {
            labels: pieCategories,
            datasets: [{
                data: pieValues,
                backgroundColor: pieColors.slice(0, pieCategories.length),
                borderWidth: 0,
                hoverOffset: 10
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 12,
                        font: { size: 10 },
                        color: '#475569'
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed;
                            const total = pieValues.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return `${label}: ₵${value.toLocaleString()} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });

    // ---- Stat card modal openers ----
    const statModals = {
        total:      new bootstrap.Modal(document.getElementById('modalTotal')),
        categories: new bootstrap.Modal(document.getElementById('modalCategories')),
        monthly:    new bootstrap.Modal(document.getElementById('modalMonthly')),
        top:        new bootstrap.Modal(document.getElementById('modalTop')),
    };

    function openStatModal(type) {
        statModals[type]?.show();
    }

    // Keyboard support for stat cards
    document.querySelectorAll('.stat-card-clickable').forEach(card => {
        card.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                card.click();
            }
        });
    });
</script>
@endpush
@endsection