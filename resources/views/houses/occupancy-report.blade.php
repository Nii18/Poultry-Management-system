@extends('layouts.master')

@section('title', 'House Occupancy Report')

@section('content')
<div class="container-fluid px-4">
    <!-- Modern Header Section -->
    <div class="report-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <div class="d-flex align-items-center gap-3">
                    <div class="header-icon">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                    <div>
                        <h1 class="header-title mb-1">House Occupancy Report</h1>
                        <p class="header-subtitle text-muted mb-0">Real-time occupancy analytics and utilization metrics</p>
                    </div>
                </div>
            </div>
            <div class="col-auto">
                <div class="action-buttons">
                    <button onclick="window.print()" class="btn btn-outline-secondary">
                        <i class="fas fa-print me-2"></i>Print Report
                    </button>
                    <a href="{{ route('houses.index') }}" class="btn btn-primary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Houses
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Modern Stats Cards -->
    <div class="stats-grid mb-4">
        <div class="stat-card">
            <div class="stat-icon bg-primary">
                <i class="fas fa-building"></i>
            </div>
            <div class="stat-details">
                <span class="stat-label">Total Houses</span>
                <h2 class="stat-value">{{ count($report) }}</h2>
                <span class="stat-trend">Total facilities</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon bg-success">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-details">
                <span class="stat-label">Occupied Houses</span>
                <h2 class="stat-value">{{ collect($report)->where('status', 'Occupied')->count() }}</h2>
                <span class="stat-trend text-success">Currently in use</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon bg-warning">
                <i class="fas fa-door-open"></i>
            </div>
            <div class="stat-details">
                <span class="stat-label">Vacant Houses</span>
                <h2 class="stat-value">{{ collect($report)->where('status', 'Vacant')->count() }}</h2>
                <span class="stat-trend text-warning">Available for use</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon bg-info">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stat-details">
                <span class="stat-label">Avg Occupancy Rate</span>
                <h2 class="stat-value">{{ number_format(collect($report)->avg('occupancy_rate'), 1) }}%</h2>
                <span class="stat-trend">Overall utilization</span>
            </div>
        </div>
    </div>

    @if(count($report) > 0)
        <!-- Occupancy Grid View -->
        <div class="occupancy-grid mb-4">
            <div class="section-header">
                <h5><i class="fas fa-th-large me-2"></i>House Occupancy Overview</h5>
                <span class="section-badge">{{ count($report) }} Houses</span>
            </div>
            <div class="row g-4">
                @foreach($report as $house)
                <div class="col-xl-4 col-lg-6">
                    <div class="occupancy-card {{ $house['status'] == 'Occupied' ? 'occupied' : 'vacant' }}">
                        <div class="card-header-bar"></div>
                        <div class="card-content">
                            <div class="card-header-info">
                                <div>
                                    <h5 class="house-name">{{ $house['house'] }}</h5>
                                    <span class="house-species">{{ $house['species'] }}</span>
                                </div>
                                <div class="status-badge {{ $house['status'] == 'Occupied' ? 'status-occupied' : 'status-vacant' }}">
                                    <i class="fas {{ $house['status'] == 'Occupied' ? 'fa-home' : 'fa-door-open' }} me-1"></i>
                                    {{ $house['status'] }}
                                </div>
                            </div>
                            <div class="capacity-stats">
                                <div class="stat-row">
                                    <span class="stat-icon-small">
                                        <i class="fas fa-users"></i>
                                    </span>
                                    <div class="stat-info">
                                        <span class="stat-label-small">Capacity</span>
                                        <strong class="stat-number">{{ number_format($house['capacity']) }}</strong>
                                    </div>
                                    <div class="stat-divider"></div>
                                    <span class="stat-icon-small">
                                        <i class="fas fa-paw"></i>
                                    </span>
                                    <div class="stat-info">
                                        <span class="stat-label-small">Current</span>
                                        <strong class="stat-number">{{ number_format($house['current_animals']) }}</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="progress-section">
                                <div class="progress-label">
                                    <span>Occupancy Rate</span>
                                    <strong>{{ number_format($house['occupancy_rate'], 1) }}%</strong>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar {{ $house['occupancy_rate'] > 80 ? 'bg-danger' : ($house['occupancy_rate'] > 50 ? 'bg-warning' : 'bg-success') }}" 
                                         style="width: {{ $house['occupancy_rate'] }}%">
                                    </div>
                                </div>
                            </div>
                            @if($house['flock_number'] != 'Empty')
                            <div class="flock-info">
                                <i class="fas fa-tractor me-2"></i>
                                <span>Current Flock: </span>
                                <a href="{{ route('flocks.show', $house['flock_number']) }}" class="flock-link">
                                    {{ $house['flock_number'] }}
                                </a>
                            </div>
                            @else
                            <div class="empty-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <span>No active flock currently assigned</span>
                            </div>
                            @endif
                            <div class="card-actions">
                                <a href="{{ route('houses.show', $house['id']) }}" class="btn-view">
                                    <i class="fas fa-eye me-2"></i>View Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Charts Section -->
        <div class="charts-section">
            <div class="row g-4">
                <div class="col-lg-7">
                    <div class="chart-card">
                        <div class="chart-header">
                            <div>
                                <i class="fas fa-chart-bar me-2 text-primary"></i>
                                <span>Occupancy by House</span>
                            </div>
                            <span class="chart-badge">Bar Chart</span>
                        </div>
                        <div class="chart-body">
                            <canvas id="occupancyBarChart" height="300"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="chart-card">
                        <div class="chart-header">
                            <div>
                                <i class="fas fa-chart-pie me-2 text-primary"></i>
                                <span>Occupancy Summary</span>
                            </div>
                            <span class="chart-badge">Pie Chart</span>
                        </div>
                        <div class="chart-body">
                            <canvas id="occupancyPieChart" height="300"></canvas>
                        </div>
                        <div class="chart-footer">
                            <div class="legend-item">
                                <span class="legend-color" style="background: #10b981;"></span>
                                <span>Occupied ({{ collect($report)->where('status', 'Occupied')->count() }})</span>
                            </div>
                            <div class="legend-item">
                                <span class="legend-color" style="background: #94a3b8;"></span>
                                <span>Vacant ({{ collect($report)->where('status', 'Vacant')->count() }})</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="empty-state">
            <div class="empty-icon">
                <i class="fas fa-home"></i>
            </div>
            <h4>No Houses Found</h4>
            <p>There are no active houses to display the occupancy report.</p>
            <a href="{{ route('houses.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Create Your First House
            </a>
        </div>
    @endif
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    @if(count($report) > 0)
    // Bar Chart
    const barCtx = document.getElementById('occupancyBarChart').getContext('2d');
    const houseNames = @json(collect($report)->pluck('house'));
    const occupancyRates = @json(collect($report)->pluck('occupancy_rate'));
    
    new Chart(barCtx, {
        type: 'bar',
        data: {
            labels: houseNames,
            datasets: [{
                label: 'Occupancy Rate (%)',
                data: occupancyRates,
                backgroundColor: occupancyRates.map(v => v > 80 ? '#dc2626' : (v > 50 ? '#f59e0b' : '#10b981')),
                borderRadius: 8,
                barPercentage: 0.7,
                categoryPercentage: 0.8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (context) => `Occupancy Rate: ${context.raw.toFixed(1)}%`
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    grid: { color: '#e2e8f0', drawBorder: false },
                    title: { display: true, text: 'Occupancy Rate (%)', font: { size: 12, weight: '500' } }
                },
                x: {
                    grid: { display: false },
                    ticks: { rotation: -45, autoSkip: true, maxRotation: 45, minRotation: 45 }
                }
            }
        }
    });
    
    // Pie Chart
    const pieCtx = document.getElementById('occupancyPieChart').getContext('2d');
    const occupiedCount = {{ collect($report)->where('status', 'Occupied')->count() }};
    const vacantCount = {{ collect($report)->where('status', 'Vacant')->count() }};
    
    new Chart(pieCtx, {
        type: 'doughnut',
        data: {
            labels: ['Occupied', 'Vacant'],
            datasets: [{
                data: [occupiedCount, vacantCount],
                backgroundColor: ['#10b981', '#94a3b8'],
                borderWidth: 0,
                hoverOffset: 10
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            cutout: '65%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (context) => {
                            const total = occupiedCount + vacantCount;
                            const percentage = ((context.raw / total) * 100).toFixed(1);
                            return `${context.label}: ${context.raw} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
    @endif
</script>
@endpush

@push('styles')
<style>
    /* Modern CSS Variables */
    :root {
        --primary: #0d6e4f;
        --primary-dark: #0a5a40;
        --success: #10b981;
        --warning: #f59e0b;
        --danger: #dc2626;
        --info: #3b82f6;
        --gray-50: #f8fafc;
        --gray-100: #f1f5f9;
        --gray-200: #e2e8f0;
        --gray-600: #64748b;
        --gray-800: #1e293b;
    }

    /* Header Styles */
    .report-header {
        margin-bottom: 2rem;
    }

    .header-icon {
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .header-icon i {
        font-size: 28px;
        color: white;
    }

    .header-title {
        font-size: 28px;
        font-weight: 700;
        color: var(--gray-800);
        letter-spacing: -0.02em;
    }

    .header-subtitle {
        font-size: 14px;
    }

    .action-buttons {
        display: flex;
        gap: 0.75rem;
    }

    .action-buttons .btn {
        padding: 0.5rem 1.25rem;
        border-radius: 10px;
        font-weight: 500;
        font-size: 14px;
    }

    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: white;
        border-radius: 20px;
        padding: 1.25rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        border: 1px solid var(--gray-200);
        transition: all 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.08);
    }

    .stat-icon {
        width: 55px;
        height: 55px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .stat-icon i {
        font-size: 24px;
        color: white;
    }

    .stat-icon.bg-primary { background: linear-gradient(135deg, var(--primary), var(--primary-dark)); }
    .stat-icon.bg-success { background: linear-gradient(135deg, #10b981, #059669); }
    .stat-icon.bg-warning { background: linear-gradient(135deg, #f59e0b, #d97706); }
    .stat-icon.bg-info { background: linear-gradient(135deg, #3b82f6, #2563eb); }

    .stat-details {
        flex: 1;
    }

    .stat-label {
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--gray-600);
        font-weight: 600;
    }

    .stat-value {
        font-size: 32px;
        font-weight: 700;
        color: var(--gray-800);
        margin: 0.25rem 0;
        line-height: 1.2;
    }

    .stat-trend {
        font-size: 12px;
        color: var(--gray-600);
    }

    .stat-trend.text-success { color: #10b981; }
    .stat-trend.text-warning { color: #f59e0b; }

    /* Section Header */
    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .section-header h5 {
        font-size: 18px;
        font-weight: 600;
        color: var(--gray-800);
        margin: 0;
    }

    .section-badge {
        background: var(--gray-100);
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 12px;
        color: var(--gray-600);
    }

    /* Occupancy Grid */
    .occupancy-grid {
        margin-bottom: 2rem;
    }

    .occupancy-card {
        background: white;
        border-radius: 20px;
        overflow: hidden;
        transition: all 0.3s ease;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        border: 1px solid var(--gray-200);
        height: 100%;
    }

    .occupancy-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.1);
    }

    .card-header-bar {
        height: 4px;
    }

    .occupancy-card.occupied .card-header-bar { background: linear-gradient(90deg, #10b981, #34d399); }
    .occupancy-card.vacant .card-header-bar { background: linear-gradient(90deg, #94a3b8, #cbd5e1); }

    .card-content {
        padding: 1.25rem;
    }

    .card-header-info {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1rem;
    }

    .house-name {
        font-size: 18px;
        font-weight: 700;
        color: var(--gray-800);
        margin: 0 0 0.25rem 0;
    }

    .house-species {
        font-size: 12px;
        color: var(--gray-600);
    }

    .status-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
    }

    .status-occupied {
        background: #d1fae5;
        color: #065f46;
    }

    .status-vacant {
        background: #f1f5f9;
        color: #475569;
    }

    /* Capacity Stats */
    .capacity-stats {
        background: var(--gray-50);
        border-radius: 12px;
        padding: 1rem;
        margin-bottom: 1rem;
    }

    .stat-row {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .stat-icon-small {
        color: var(--gray-600);
        font-size: 14px;
    }

    .stat-info {
        display: flex;
        flex-direction: column;
    }

    .stat-label-small {
        font-size: 11px;
        color: var(--gray-600);
    }

    .stat-number {
        font-size: 18px;
        color: var(--gray-800);
    }

    .stat-divider {
        width: 1px;
        height: 30px;
        background: var(--gray-200);
    }

    /* Progress Section */
    .progress-section {
        margin-bottom: 1rem;
    }

    .progress-label {
        display: flex;
        justify-content: space-between;
        font-size: 12px;
        margin-bottom: 0.5rem;
        color: var(--gray-600);
    }

    .progress {
        height: 8px;
        background: var(--gray-200);
        border-radius: 10px;
        overflow: hidden;
    }

    .progress-bar {
        border-radius: 10px;
        transition: width 0.5s ease;
    }

    /* Flock Info */
    .flock-info, .empty-info {
        background: var(--gray-50);
        border-radius: 10px;
        padding: 0.75rem;
        font-size: 13px;
        margin-bottom: 1rem;
    }

    .flock-info {
        color: var(--gray-600);
    }

    .flock-link {
        color: var(--primary);
        text-decoration: none;
        font-weight: 500;
    }

    .flock-link:hover {
        text-decoration: underline;
    }

    .empty-info {
        background: #fef3c7;
        color: #92400e;
    }

    /* Card Actions */
    .card-actions {
        text-align: center;
    }

    .btn-view {
        display: inline-block;
        padding: 0.5rem 1rem;
        background: white;
        border: 1px solid var(--gray-200);
        border-radius: 10px;
        color: var(--gray-600);
        text-decoration: none;
        font-size: 13px;
        font-weight: 500;
        transition: all 0.2s;
        width: 100%;
    }

    .btn-view:hover {
        background: var(--primary);
        border-color: var(--primary);
        color: white;
    }

    /* Charts Section */
    .charts-section {
        margin-top: 1rem;
    }

    .chart-card {
        background: white;
        border-radius: 20px;
        border: 1px solid var(--gray-200);
        overflow: hidden;
        height: 100%;
    }

    .chart-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid var(--gray-200);
        background: var(--gray-50);
    }

    .chart-header span {
        font-weight: 600;
        color: var(--gray-800);
    }

    .chart-badge {
        background: white;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 500;
        color: var(--gray-600);
        border: 1px solid var(--gray-200);
    }

    .chart-body {
        padding: 1.25rem;
    }

    .chart-footer {
        padding: 1rem 1.25rem;
        border-top: 1px solid var(--gray-200);
        display: flex;
        justify-content: center;
        gap: 2rem;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 13px;
        color: var(--gray-600);
    }

    .legend-color {
        width: 12px;
        height: 12px;
        border-radius: 4px;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        background: white;
        border-radius: 20px;
        border: 1px solid var(--gray-200);
    }

    .empty-icon {
        width: 80px;
        height: 80px;
        background: var(--gray-100);
        border-radius: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
    }

    .empty-icon i {
        font-size: 36px;
        color: var(--gray-400);
    }

    .empty-state h4 {
        font-size: 20px;
        font-weight: 600;
        color: var(--gray-800);
        margin-bottom: 0.5rem;
    }

    .empty-state p {
        color: var(--gray-600);
        margin-bottom: 1.5rem;
    }

    /* Print Styles */
    @media print {
        .action-buttons, .card-actions, .btn-view, .chart-badge {
            display: none !important;
        }
        .stat-card, .occupancy-card, .chart-card {
            break-inside: avoid;
            box-shadow: none;
            border: 1px solid #ddd;
        }
        .header-icon {
            background: #666 !important;
        }
    }

    /* Responsive */
    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }
        .stat-value {
            font-size: 24px;
        }
        .header-title {
            font-size: 22px;
        }
        .action-buttons .btn {
            padding: 0.4rem 1rem;
            font-size: 12px;
        }
    }
</style>
@endpush
@endsection