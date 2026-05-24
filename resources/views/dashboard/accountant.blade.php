@extends('layouts.master')

@section('content')
<div class="container-fluid px-4">
    
    @include('dashboard.partials.role-header')
    
    <!-- KPI Cards with Icons -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="modern-card revenue-card">
                <div class="card-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="card-content">
                    <span class="card-label">Total Revenue</span>
                    <h2 class="card-value">₵{{ number_format($currentMonthRevenue ?? 0, 2) }}</h2>
                    <span class="card-period">This month</span>
                </div>
                <div class="card-trend up">
                    <i class="fas fa-arrow-up"></i> +12%
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="modern-card expense-card">
                <div class="card-icon">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div class="card-content">
                    <span class="card-label">Total Expenses</span>
                    <h2 class="card-value">₵{{ number_format($currentMonthExpenses ?? 0, 2) }}</h2>
                    <span class="card-period">This month</span>
                </div>
                <div class="card-trend down">
                    <i class="fas fa-arrow-down"></i> +5%
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="modern-card profit-card">
                <div class="card-icon">
                    <i class="fas fa-chart-simple"></i>
                </div>
                <div class="card-content">
                    <span class="card-label">Net Profit</span>
                    <h2 class="card-value {{ ($currentMonthProfit ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">₵{{ number_format($currentMonthProfit ?? 0, 2) }}</h2>
                    <span class="card-period">This month</span>
                </div>
                <div class="card-trend {{ ($currentMonthProfit ?? 0) >= 0 ? 'up' : 'down' }}">
                    <i class="fas fa-{{ ($currentMonthProfit ?? 0) >= 0 ? 'arrow-up' : 'arrow-down' }}"></i> 
                    {{ ($currentMonthProfit ?? 0) >= 0 ? 'Profitable' : 'Loss' }}
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions Grid -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="action-grid">
                <button onclick="openCreateExpenseModal()" class="action-card">
                    <div class="action-icon bg-danger">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="action-info">
                        <h6>Record Expense</h6>
                        <small>Add new farm expense</small>
                    </div>
                    <i class="fas fa-chevron-right action-arrow"></i>
                </button>
                <button onclick="openCreateSaleModal()" class="action-card">
                    <div class="action-icon bg-success">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="action-info">
                        <h6>Record Sale</h6>
                        <small>Add new revenue entry</small>
                    </div>
                    <i class="fas fa-chevron-right action-arrow"></i>
                </button>
                <a href="{{ route('reports.financial') }}" class="action-card">
                    <div class="action-icon bg-primary">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                    <div class="action-info">
                        <h6>Financial Report</h6>
                        <small>View detailed reports</small>
                    </div>
                    <i class="fas fa-chevron-right action-arrow"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="chart-card">
                <div class="chart-header">
                    <h5><i class="fas fa-chart-line me-2 text-primary"></i>Revenue vs Expenses Trend</h5>
                    <div class="chart-legend">
                        <span><i class="fas fa-circle text-success"></i> Revenue</span>
                        <span><i class="fas fa-circle text-danger"></i> Expenses</span>
                    </div>
                </div>
                <div class="chart-body">
                    <canvas id="financialTrendChart" height="280"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="chart-card">
                <div class="chart-header">
                    <h5><i class="fas fa-chart-pie me-2 text-primary"></i>Expense Breakdown</h5>
                </div>
                <div class="chart-body text-center">
                    <canvas id="expensePieChart" height="240"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Financial Summary Widgets -->
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="insight-card">
                <div class="insight-header">
                    <h5><i class="fas fa-fire me-2 text-danger"></i>Top Expense Categories</h5>
                    <a href="{{ route('expenses.by-category') }}" class="insight-link">Details →</a>
                </div>
                <div class="insight-body">
                    @php
                        $topCategories = collect($expenseCategoryNames ?? [])->zip($expenseAmounts ?? [])->take(5);
                    @endphp
                    @forelse($topCategories as $index => $category)
                        @php
                            list($catName, $catAmount) = $category;
                            $totalExpenses = array_sum($expenseAmounts ?? []);
                            $percentage = $totalExpenses > 0 ? round(($catAmount / $totalExpenses) * 100) : 0;
                            $colors = ['#10b981', '#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6'];
                        @endphp
                        <div class="category-item">
                            <div class="category-info">
                                <span class="category-name">
                                    <i class="fas fa-tag" style="color: {{ $colors[$index % 5] }}"></i>
                                    {{ $catName }}
                                </span>
                                <span class="category-amount">₵{{ number_format($catAmount, 2) }}</span>
                            </div>
                            <div class="category-progress">
                                <div class="progress-bar" style="width: {{ $percentage }}%; background: {{ $colors[$index % 5] }};"></div>
                            </div>
                            <div class="category-percentage">{{ $percentage }}%</div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted">No expense data available</div>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="insight-card">
                <div class="insight-header">
                    <h5><i class="fas fa-heartbeat me-2 text-primary"></i>Financial Health</h5>
                    <a href="{{ route('reports.financial') }}" class="insight-link">Full Report →</a>
                </div>
                <div class="insight-body">
                    <div class="health-metric">
                        <div class="metric-info">
                            <span class="metric-label">Profit Margin</span>
                            <span class="metric-value">
                                @php
                                    $totalRevenueForMargin = $currentMonthRevenue ?? 0;
                                    $profitMargin = $totalRevenueForMargin > 0 ? round(($currentMonthProfit / $totalRevenueForMargin) * 100, 1) : 0;
                                @endphp
                                {{ $profitMargin }}%
                            </span>
                        </div>
                        <div class="metric-progress">
                            <div class="progress-bar {{ $profitMargin >= 20 ? 'bg-success' : ($profitMargin >= 10 ? 'bg-warning' : 'bg-danger') }}" style="width: {{ min(100, $profitMargin) }}%;"></div>
                        </div>
                        <div class="metric-status {{ $profitMargin >= 20 ? 'text-success' : ($profitMargin >= 10 ? 'text-warning' : 'text-danger') }}">
                            <i class="fas fa-{{ $profitMargin >= 20 ? 'check-circle' : ($profitMargin >= 10 ? 'exclamation-triangle' : 'times-circle') }}"></i>
                            {{ $profitMargin >= 20 ? 'Healthy Margin' : ($profitMargin >= 10 ? 'Moderate Margin' : 'Low Margin') }}
                        </div>
                    </div>
                    <div class="health-metric mt-3">
                        <div class="metric-info">
                            <span class="metric-label">Expense/Revenue Ratio</span>
                            <span class="metric-value">
                                @php
                                    $expenseRatio = $totalRevenueForMargin > 0 ? round(($currentMonthExpenses / $totalRevenueForMargin) * 100, 1) : 0;
                                @endphp
                                {{ $expenseRatio }}%
                            </span>
                        </div>
                        <div class="metric-progress">
                            <div class="progress-bar bg-danger" style="width: {{ min(100, $expenseRatio) }}%;"></div>
                        </div>
                        <div class="metric-status text-muted">
                            <i class="fas fa-chart-line"></i>
                            {{ $expenseRatio <= 60 ? 'Good cost control' : ($expenseRatio <= 80 ? 'Monitor closely' : 'High expenses') }}
                        </div>
                    </div>
                    <div class="health-metric mt-3">
                        <div class="metric-info">
                            <span class="metric-label">Daily Average Revenue</span>
                            <span class="metric-value">₵{{ number_format(($currentMonthRevenue ?? 0) / max(1, now()->daysInMonth), 2) }}</span>
                        </div>
                        <div class="metric-info mt-2">
                            <span class="metric-label">Daily Average Expenses</span>
                            <span class="metric-value">₵{{ number_format(($currentMonthExpenses ?? 0) / max(1, now()->daysInMonth), 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Financial Activity Feed -->
    <div class="activity-feed-card">
        <div class="activity-header">
            <h5><i class="fas fa-bell me-2 text-warning"></i>Recent Financial Activity</h5>
            <div class="activity-filters">
                <button class="filter-btn active" data-filter="all">All</button>
                <button class="filter-btn" data-filter="expense">Expenses</button>
                <button class="filter-btn" data-filter="revenue">Revenue</button>
            </div>
        </div>
        <div class="activity-body">
            @php
                $allActivities = collect();
                
                $recentExpensesList = \App\Models\Expense::latest()->take(7)->get();
                foreach($recentExpensesList as $exp) {
                    $allActivities->push((object)[
                        'type' => 'expense',
                        'date' => $exp->expense_date->format('d M Y'),
                        'description' => $exp->description,
                        'amount' => $exp->amount,
                        'category' => $exp->category,
                        'id' => $exp->id
                    ]);
                }
                
                $recentSalesList = \App\Models\Sale::latest()->take(7)->get();
                foreach($recentSalesList as $sale) {
                    $allActivities->push((object)[
                        'type' => 'revenue',
                        'date' => $sale->sale_date->format('d M Y'),
                        'description' => $sale->description ?? 'Sale of ' . str_replace('_', ' ', $sale->product_type),
                        'amount' => $sale->total_amount,
                        'category' => $sale->product_type,
                        'id' => $sale->id
                    ]);
                }
                
                $allActivities = $allActivities->sortByDesc('date')->take(10);
            @endphp
            
            <div class="activity-list">
                @forelse($allActivities as $activity)
                <div class="activity-item" data-type="{{ $activity->type }}">
                    <div class="activity-icon {{ $activity->type === 'expense' ? 'icon-expense' : 'icon-revenue' }}">
                        <i class="fas {{ $activity->type === 'expense' ? 'fa-arrow-down' : 'fa-arrow-up' }}"></i>
                    </div>
                    <div class="activity-details">
                        <div class="activity-title">
                            {{ $activity->description }}
                            <span class="activity-category">{{ ucfirst($activity->category) }}</span>
                        </div>
                        <div class="activity-date">{{ $activity->date }}</div>
                    </div>
                    <div class="activity-amount {{ $activity->type === 'expense' ? 'amount-expense' : 'amount-revenue' }}">
                        {{ $activity->type === 'expense' ? '-' : '+' }} ₵{{ number_format($activity->amount, 2) }}
                    </div>
                    <button class="activity-view" onclick="{{ $activity->type === 'expense' ? 'viewExpense(' : 'viewSale(' }}{{ $activity->id }})" title="View Details">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                @empty
                <div class="text-center py-4 text-muted">No recent financial activity</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- ==================== MODALS ==================== -->

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
                    <div class="spinner-border text-danger" role="status"></div>
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
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2">Loading expense details...</p>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Create Sale Modal -->
<div class="modal fade" id="createSaleModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-success text-white border-0">
                <h5 class="modal-title text-white"><i class="fas fa-plus-circle me-2"></i>Record Sale</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="createSaleContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-success" role="status"></div>
                    <p class="mt-2">Loading form...</p>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="saveCreateSale">Record Sale</button>
            </div>
        </div>
    </div>
</div>

<!-- View Sale Modal -->
<div class="modal fade" id="viewSaleModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title text-white"><i class="fas fa-eye me-2"></i>Sale Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewSaleContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2">Loading sale details...</p>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Helper function to close all modals
    function closeAllModals() {
        const modals = document.querySelectorAll('.modal.show');
        modals.forEach(modal => {
            const modalInstance = bootstrap.Modal.getInstance(modal);
            if (modalInstance) {
                modalInstance.hide();
            }
        });
        const backdrops = document.querySelectorAll('.modal-backdrop');
        backdrops.forEach(backdrop => backdrop.remove());
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
    }

    // ==================== CHARTS ====================
    new Chart(document.getElementById('financialTrendChart'), {
        type: 'line',
        data: {
            labels: @json($monthLabels ?? []),
            datasets: [
                { label: 'Revenue', data: @json($monthlyRevenue ?? []), borderColor: '#10b981', backgroundColor: 'rgba(16,185,129,0.05)', borderWidth: 2, pointBackgroundColor: '#10b981', pointBorderColor: '#fff', pointRadius: 4, pointHoverRadius: 6, tension: 0.3, fill: true },
                { label: 'Expenses', data: @json($monthlyExpenses ?? []), borderColor: '#ef4444', backgroundColor: 'rgba(239,68,68,0.05)', borderWidth: 2, pointBackgroundColor: '#ef4444', pointBorderColor: '#fff', pointRadius: 4, pointHoverRadius: 6, tension: 0.3, fill: true }
            ]
        },
        options: { responsive: true, maintainAspectRatio: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { callback: function(value) { return '₵' + value.toLocaleString(); } } } } }
    });
    
    new Chart(document.getElementById('expensePieChart'), {
        type: 'doughnut',
        data: { labels: @json($expenseCategoryNames ?? []), datasets: [{ data: @json($expenseAmounts ?? []), backgroundColor: ['#10b981', '#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6'], borderWidth: 0, cutout: '60%' }] },
        options: { responsive: true, maintainAspectRatio: true, plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } } } }
    });
    
    // Activity Filter
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const filter = this.dataset.filter;
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            document.querySelectorAll('.activity-item').forEach(item => {
                if (filter === 'all') { item.style.display = 'flex'; }
                else { item.style.display = item.dataset.type === filter ? 'flex' : 'none'; }
            });
        });
    });

    // ==================== CREATE EXPENSE MODAL ====================
    function openCreateExpenseModal() {
        closeAllModals();
        
        const modalElement = document.getElementById('createExpenseModal');
        const modal = new bootstrap.Modal(modalElement, { backdrop: 'static', keyboard: false });
        const modalBody = document.getElementById('createExpenseContent');
        
        modalBody.innerHTML = `<div class="text-center py-4"><div class="spinner-border text-danger" role="status"></div><p class="mt-2">Loading form...</p></div>`;
        modal.show();
        
        fetch('{{ route("expenses.create-form") }}', {
            headers: { 'Accept': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayExpenseCreateForm(data.flocks, data.houses);
            } else {
                modalBody.innerHTML = `<div class="alert alert-danger">Failed to load form: ${data.message}</div>`;
            }
        })
        .catch(error => {
            modalBody.innerHTML = `<div class="alert alert-danger">Error: ${error.message}</div>`;
        });
    }
    
    function displayExpenseCreateForm(flocks, houses) {
        const flockOptions = flocks.map(flock => `<option value="${flock.id}">${escapeHtml(flock.flock_number)} - ${escapeHtml(flock.breed_variety)}</option>`).join('');
        const houseOptions = houses.map(house => `<option value="${house.id}">${escapeHtml(house.name)}</option>`).join('');
        
        document.getElementById('createExpenseContent').innerHTML = `
            <form id="createExpenseForm">
                <div class="row">
                    <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                        <select name="category" class="form-select" required>
                            <option value="">Select Category</option>
                            <option value="feed">🐓 Feed</option><option value="veterinary">🏥 Veterinary</option>
                            <option value="medication">💊 Medication</option><option value="labor">👥 Labor</option>
                            <option value="equipment">🔧 Equipment</option><option value="utilities">💡 Utilities</option>
                            <option value="maintenance">🛠️ Maintenance</option><option value="transport">🚚 Transport</option>
                            <option value="other">📦 Other</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Expense Date <span class="text-danger">*</span></label>
                        <input type="date" name="expense_date" class="form-control" value="${new Date().toISOString().split('T')[0]}" required>
                    </div>
                    <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Description <span class="text-danger">*</span></label>
                        <input type="text" name="description" class="form-control" placeholder="e.g., 50 bags of layer mash" required>
                    </div>
                    <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Amount (₵) <span class="text-danger">*</span></label>
                        <div class="input-group"><span class="input-group-text">₵</span><input type="number" name="amount" class="form-control" step="0.01" min="0.01" placeholder="0.00" required></div>
                    </div>
                    <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Vendor Name</label>
                        <input type="text" name="vendor_name" class="form-control" placeholder="Supplier name">
                    </div>
                    <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Payment Method</label>
                        <select name="payment_method" class="form-select">
                            <option value="">Select Payment Method</option>
                            <option value="cash">💵 Cash</option><option value="bank_transfer">🏦 Bank Transfer</option>
                            <option value="mobile_money">📱 Mobile Money (MoMo)</option><option value="check">📝 Check</option><option value="credit_card">💳 Credit Card</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Receipt Number</label>
                        <input type="text" name="receipt_number" class="form-control" placeholder="Receipt/invoice number">
                    </div>
                    <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Associated Flock</label>
                        <select name="flock_id" class="form-select"><option value="">None - General Expense</option>${flockOptions}</select>
                    </div>
                    <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Associated House</label>
                        <select name="house_id" class="form-select"><option value="">None - General Expense</option>${houseOptions}</select>
                    </div>
                    <div class="col-12 mb-3"><label class="form-label fw-semibold">Notes</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Additional notes..."></textarea>
                    </div>
                </div>
            </form>
        `;
        
        document.getElementById('saveCreateExpense').onclick = function() {
            const form = document.getElementById('createExpenseForm');
            const formData = new FormData(form);
            const data = {};
            formData.forEach((value, key) => { data[key] = value; });
            
            if (!data.category || !data.expense_date || !data.description || !data.amount) {
                Swal.fire({ icon: 'error', title: 'Validation Error', text: 'Please fill in all required fields' });
                return;
            }
            
            fetch('{{ route("expenses.store-ajax") }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json', 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({ icon: 'success', title: 'Saved!', text: 'Expense recorded successfully', timer: 1500, showConfirmButton: false })
                        .then(() => window.location.reload());
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Failed to save expense' });
                }
            });
        };
    }

    // ==================== VIEW EXPENSE ====================
    function viewExpense(id) {
        const modalElement = document.getElementById('viewExpenseModal');
        const modal = new bootstrap.Modal(modalElement, { backdrop: 'static', keyboard: true });
        const modalBody = document.getElementById('viewExpenseContent');
        
        modalBody.innerHTML = `<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Loading...</p></div>`;
        modal.show();
        
        fetch(`/expenses/${id}/details-json`, {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayExpenseDetails(data.expense);
            } else {
                modalBody.innerHTML = `<div class="alert alert-danger">Failed to load details: ${data.message}</div>`;
            }
        })
        .catch(error => {
            modalBody.innerHTML = `<div class="alert alert-danger">Error: ${error.message}</div>`;
        });
    }
    
    function displayExpenseDetails(expense) {
        document.getElementById('viewExpenseContent').innerHTML = `
            <div class="detail-section"><h6>Expense Information</h6><div class="detail-grid">
                <div class="detail-item"><span class="detail-label">Date</span><span class="detail-value">${expense.expense_date}</span></div>
                <div class="detail-item"><span class="detail-label">Category</span><span class="detail-value">${expense.category}</span></div>
                <div class="detail-item"><span class="detail-label">Description</span><span class="detail-value">${escapeHtml(expense.description)}</span></div>
                <div class="detail-item"><span class="detail-label">Amount</span><span class="detail-value text-danger fw-bold">₵${expense.amount}</span></div>
                <div class="detail-item"><span class="detail-label">Vendor</span><span class="detail-value">${escapeHtml(expense.vendor_name || 'N/A')}</span></div>
                <div class="detail-item"><span class="detail-label">Payment Method</span><span class="detail-value">${escapeHtml(expense.payment_method || 'N/A')}</span></div>
                <div class="detail-item"><span class="detail-label">Receipt Number</span><span class="detail-value">${escapeHtml(expense.receipt_number || 'N/A')}</span></div>
            </div></div>
            ${expense.notes ? `<div class="detail-section"><h6>Notes</h6><p>${escapeHtml(expense.notes)}</p></div>` : ''}
        `;
    }

    // ==================== CREATE SALE MODAL ====================
    function openCreateSaleModal() {
        closeAllModals();
        
        const modalElement = document.getElementById('createSaleModal');
        const modal = new bootstrap.Modal(modalElement, { backdrop: 'static', keyboard: false });
        const modalBody = document.getElementById('createSaleContent');
        
        modalBody.innerHTML = `<div class="text-center py-4"><div class="spinner-border text-success" role="status"></div><p class="mt-2">Loading form...</p></div>`;
        modal.show();
        
        fetch('{{ route("sales.create-form") }}', {
            headers: { 'Accept': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displaySaleCreateForm(data.flocks);
            } else {
                modalBody.innerHTML = `<div class="alert alert-danger">Failed to load form: ${data.message}</div>`;
            }
        })
        .catch(error => {
            modalBody.innerHTML = `<div class="alert alert-danger">Error: ${error.message}</div>`;
        });
    }
    
    function displaySaleCreateForm(flocks) {
        const flockOptions = flocks.map(flock => `<option value="${flock.id}">${escapeHtml(flock.flock_number)} - ${escapeHtml(flock.breed_variety)}</option>`).join('');
        
        document.getElementById('createSaleContent').innerHTML = `
            <form id="createSaleForm">
                <div class="row">
                    <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Product Type <span class="text-danger">*</span></label>
                        <select name="product_type" class="form-select" required>
                            <option value="">Select Product</option>
                            <option value="eggs_tray">🥚 Eggs (Tray - 30 eggs)</option>
                            <option value="eggs_crate">📦 Eggs (Crate - 12 trays / 360 eggs)</option>
                            <option value="eggs_box">📦 Eggs (Box - 360 eggs)</option>
                            <option value="live_bird">🐓 Live Bird</option>
                            <option value="meat_kg">🍗 Meat (per kg)</option>
                            <option value="breeding_stock">🧬 Breeding Stock</option>
                            <option value="manure">💩 Manure</option>
                            <option value="other">📦 Other</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Sale Date <span class="text-danger">*</span></label>
                        <input type="date" name="sale_date" class="form-control" value="${new Date().toISOString().split('T')[0]}" required>
                    </div>
                    <div class="col-md-4 mb-3"><label class="form-label fw-semibold">Quantity <span class="text-danger">*</span></label>
                        <input type="number" name="quantity" id="quantityInput" class="form-control" step="0.01" min="0.01" required>
                    </div>
                    <div class="col-md-4 mb-3"><label class="form-label fw-semibold">Unit Price (₵) <span class="text-danger">*</span></label>
                        <div class="input-group"><span class="input-group-text">₵</span><input type="number" name="unit_price" id="unitPriceInput" class="form-control" step="0.01" min="0.01" required></div>
                    </div>
                    <div class="col-md-4 mb-3"><label class="form-label fw-semibold">Total Amount (₵)</label>
                        <div class="input-group"><span class="input-group-text">₵</span><input type="number" name="total_amount" id="totalAmountInput" class="form-control" step="0.01" readonly style="background:#f8fafc;"></div>
                        <small class="text-muted">Auto-calculated</small>
                    </div>
                    <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Customer Name</label>
                        <input type="text" name="customer_name" class="form-control" placeholder="Customer name">
                    </div>
                    <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Payment Method</label>
                        <select name="payment_method" class="form-select">
                            <option value="">Select Payment Method</option>
                            <option value="cash">💵 Cash</option><option value="bank_transfer">🏦 Bank Transfer</option>
                            <option value="mobile_money">📱 Mobile Money (MoMo)</option><option value="check">📝 Check</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Receipt Number</label>
                        <input type="text" name="receipt_number" class="form-control" placeholder="Receipt/invoice number">
                    </div>
                    <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Associated Flock</label>
                        <select name="flock_id" class="form-select"><option value="">None - General Sale</option>${flockOptions}</select>
                    </div>
                    <div class="col-12 mb-3"><label class="form-label fw-semibold">Description</label>
                        <input type="text" name="description" class="form-control" placeholder="Brief description">
                    </div>
                    <div class="col-12 mb-3"><label class="form-label fw-semibold">Notes</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
            </form>
        `;
        
        const quantityInput = document.getElementById('quantityInput');
        const unitPriceInput = document.getElementById('unitPriceInput');
        const totalAmountInput = document.getElementById('totalAmountInput');
        function calculateTotal() {
            const quantity = parseFloat(quantityInput?.value) || 0;
            const unitPrice = parseFloat(unitPriceInput?.value) || 0;
            if (totalAmountInput) totalAmountInput.value = (quantity * unitPrice).toFixed(2);
        }
        quantityInput?.addEventListener('input', calculateTotal);
        unitPriceInput?.addEventListener('input', calculateTotal);
        
        document.getElementById('saveCreateSale').onclick = function() {
            const form = document.getElementById('createSaleForm');
            const formData = new FormData(form);
            const data = {};
            formData.forEach((value, key) => { data[key] = value; });
            
            if (!data.product_type || !data.sale_date || !data.quantity || !data.unit_price) {
                Swal.fire({ icon: 'error', title: 'Validation Error', text: 'Please fill in all required fields' });
                return;
            }
            
            fetch('{{ route("sales.store-ajax") }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json', 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({ icon: 'success', title: 'Recorded!', text: 'Sale recorded successfully', timer: 1500, showConfirmButton: false })
                        .then(() => window.location.reload());
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Failed to record sale' });
                }
            });
        };
    }

    // ==================== VIEW SALE ====================
    function viewSale(id) {
        const modalElement = document.getElementById('viewSaleModal');
        const modal = new bootstrap.Modal(modalElement, { backdrop: 'static', keyboard: true });
        const modalBody = document.getElementById('viewSaleContent');
        
        modalBody.innerHTML = `<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Loading...</p></div>`;
        modal.show();
        
        fetch(`/sales/${id}/details-json`, {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displaySaleDetails(data.sale);
            } else {
                modalBody.innerHTML = `<div class="alert alert-danger">Failed to load details: ${data.message}</div>`;
            }
        })
        .catch(error => {
            modalBody.innerHTML = `<div class="alert alert-danger">Error: ${error.message}</div>`;
        });
    }
    
    function displaySaleDetails(sale) {
        document.getElementById('viewSaleContent').innerHTML = `
            <div class="detail-section"><h6>Sale Information</h6><div class="detail-grid">
                <div class="detail-item"><span class="detail-label">Date</span><span class="detail-value">${sale.sale_date}</span></div>
                <div class="detail-item"><span class="detail-label">Product Type</span><span class="detail-value">${sale.product_type}</span></div>
                <div class="detail-item"><span class="detail-label">Quantity</span><span class="detail-value">${sale.quantity}</span></div>
                <div class="detail-item"><span class="detail-label">Unit Price</span><span class="detail-value">₵${sale.unit_price}</span></div>
                <div class="detail-item"><span class="detail-label">Total Amount</span><span class="detail-value text-success fw-bold">₵${sale.total_amount}</span></div>
                <div class="detail-item"><span class="detail-label">Customer</span><span class="detail-value">${escapeHtml(sale.customer_name || 'Walk-in')}</span></div>
                <div class="detail-item"><span class="detail-label">Payment Method</span><span class="detail-value">${escapeHtml(sale.payment_method || 'N/A')}</span></div>
            </div></div>
            ${sale.notes ? `<div class="detail-section"><h6>Notes</h6><p>${escapeHtml(sale.notes)}</p></div>` : ''}
        `;
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
</script>
@endpush

@push('styles')
<style>
    .modern-card { background: white; border-radius: 20px; padding: 1.25rem; display: flex; align-items: center; gap: 1rem; transition: all 0.3s ease; border: 1px solid #e2e8f0; position: relative; overflow: hidden; }
    .modern-card::before { content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 4px; }
    .revenue-card::before { background: #10b981; }
    .expense-card::before { background: #ef4444; }
    .profit-card::before { background: #3b82f6; }
    .modern-card:hover { transform: translateY(-3px); box-shadow: 0 10px 30px rgba(0,0,0,0.08); }
    .card-icon { width: 55px; height: 55px; border-radius: 18px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: white; }
    .revenue-card .card-icon { background: linear-gradient(135deg, #10b981, #059669); }
    .expense-card .card-icon { background: linear-gradient(135deg, #ef4444, #dc2626); }
    .profit-card .card-icon { background: linear-gradient(135deg, #3b82f6, #2563eb); }
    .card-content { flex: 1; }
    .card-label { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; font-weight: 600; }
    .card-value { font-size: 1.5rem; font-weight: 700; margin: 0.25rem 0; }
    .card-period { font-size: 0.7rem; color: #94a3b8; }
    .card-trend { font-size: 0.7rem; font-weight: 600; padding: 0.25rem 0.5rem; border-radius: 20px; background: #f1f5f9; }
    .card-trend.up { color: #10b981; background: #dcfce7; }
    .card-trend.down { color: #ef4444; background: #fee2e2; }
    
    .action-grid { display: flex; gap: 1rem; flex-wrap: wrap; }
    .action-card { flex: 1; display: flex; align-items: center; gap: 1rem; padding: 1rem 1.25rem; background: white; border: 1px solid #e2e8f0; border-radius: 16px; text-decoration: none; transition: all 0.3s ease; cursor: pointer; }
    .action-card:hover { background: #f8fafc; transform: translateX(5px); border-color: #10b981; }
    .action-icon { width: 45px; height: 45px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; color: white; }
    .bg-danger { background: linear-gradient(135deg, #ef4444, #dc2626); }
    .bg-success { background: linear-gradient(135deg, #10b981, #059669); }
    .bg-primary { background: linear-gradient(135deg, #3b82f6, #2563eb); }
    .action-info h6 { margin: 0; font-size: 0.9rem; font-weight: 600; color: #1e293b; }
    .action-info small { font-size: 0.7rem; color: #64748b; }
    .action-arrow { color: #94a3b8; margin-left: auto; transition: all 0.3s ease; }
    .action-card:hover .action-arrow { transform: translateX(5px); color: #10b981; }
    
    .chart-card { background: white; border-radius: 20px; border: 1px solid #e2e8f0; overflow: hidden; }
    .chart-header { padding: 1rem 1.25rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem; }
    .chart-header h5 { margin: 0; font-size: 0.95rem; font-weight: 600; }
    .chart-legend { display: flex; gap: 1rem; font-size: 0.75rem; }
    .chart-body { padding: 1rem; }
    
    .insight-card { background: white; border-radius: 20px; border: 1px solid #e2e8f0; height: 100%; }
    .insight-header { padding: 1rem 1.25rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; }
    .insight-header h5 { margin: 0; font-size: 0.95rem; font-weight: 600; }
    .insight-link { font-size: 0.7rem; color: #10b981; text-decoration: none; }
    .insight-body { padding: 1rem 1.25rem; }
    .category-item { margin-bottom: 1rem; }
    .category-info { display: flex; justify-content: space-between; margin-bottom: 0.35rem; font-size: 0.8rem; }
    .category-name i { width: 20px; margin-right: 0.5rem; }
    .category-amount { font-weight: 600; color: #1e293b; }
    .category-progress { background: #e2e8f0; border-radius: 10px; height: 6px; overflow: hidden; margin-bottom: 0.35rem; }
    .progress-bar { height: 100%; border-radius: 10px; transition: width 0.5s ease; }
    .category-percentage { font-size: 0.7rem; color: #64748b; text-align: right; }
    
    .health-metric { background: #f8fafc; padding: 0.75rem; border-radius: 12px; }
    .metric-info { display: flex; justify-content: space-between; margin-bottom: 0.5rem; }
    .metric-label { font-size: 0.75rem; color: #64748b; }
    .metric-value { font-weight: 600; color: #1e293b; }
    .metric-progress { background: #e2e8f0; border-radius: 10px; height: 6px; overflow: hidden; margin-bottom: 0.5rem; }
    .metric-progress .progress-bar { height: 100%; border-radius: 10px; }
    .bg-warning { background: #f59e0b; }
    .metric-status { font-size: 0.7rem; }
    
    .activity-feed-card { background: white; border-radius: 20px; border: 1px solid #e2e8f0; margin-top: 0.5rem; }
    .activity-header { padding: 1rem 1.25rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem; }
    .activity-header h5 { margin: 0; font-size: 0.95rem; font-weight: 600; }
    .activity-filters { display: flex; gap: 0.5rem; }
    .filter-btn { padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.7rem; border: 1px solid #e2e8f0; background: white; cursor: pointer; transition: all 0.2s; }
    .filter-btn.active { background: #10b981; border-color: #10b981; color: white; }
    .activity-list { padding: 0.5rem 0; }
    .activity-item { display: flex; align-items: center; gap: 1rem; padding: 0.75rem 1.25rem; border-bottom: 1px solid #e2e8f0; transition: all 0.2s; }
    .activity-item:hover { background: #f8fafc; }
    .activity-icon { width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1rem; }
    .icon-expense { background: #fee2e2; color: #dc2626; }
    .icon-revenue { background: #dcfce7; color: #10b981; }
    .activity-details { flex: 1; }
    .activity-title { font-weight: 500; font-size: 0.85rem; color: #1e293b; display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap; }
    .activity-category { font-size: 0.65rem; padding: 0.15rem 0.5rem; background: #f1f5f9; border-radius: 20px; color: #64748b; }
    .activity-date { font-size: 0.7rem; color: #94a3b8; margin-top: 0.2rem; }
    .activity-amount { font-weight: 600; font-size: 0.9rem; }
    .amount-revenue { color: #10b981; }
    .amount-expense { color: #ef4444; }
    .activity-view { background: none; border: none; color: #94a3b8; cursor: pointer; padding: 0.25rem; transition: all 0.2s; }
    .activity-view:hover { color: #10b981; }
    
    .detail-section { margin-bottom: 1.5rem; }
    .detail-section h6 { font-weight: 600; color: #1e293b; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 2px solid #e2e8f0; }
    .detail-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; }
    .detail-item { display: flex; flex-direction: column; }
    .detail-label { font-size: 0.7rem; text-transform: uppercase; color: #64748b; font-weight: 600; margin-bottom: 0.25rem; }
    .detail-value { font-size: 1rem; font-weight: 500; color: #1e293b; }
</style>
@endpush

@endsection