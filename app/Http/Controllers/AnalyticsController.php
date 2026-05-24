<?php
// app/Http/Controllers/AnalyticsController.php

namespace App\Http\Controllers;

use App\Models\Flock;
use App\Models\DailyLog;
use App\Models\Expense;
use App\Models\Species;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
   /**
 * Display analytics dashboard
 */
public function index(Request $request)
{
    $period = $request->get('period', 'month');
    $speciesId = $request->get('species_id');
    
    $endDate = Carbon::now();
    
    if ($period === 'week') {
        $startDate = Carbon::now()->subDays(7);
    } elseif ($period === 'month') {
        $startDate = Carbon::now()->subMonth();
    } elseif ($period === 'year') {
        $startDate = Carbon::now()->subYear();
    } else {
        $startDate = Carbon::now()->subMonth();
    }
    
    // Performance trends
    $performanceTrends = $this->getPerformanceTrends($startDate, $endDate, $speciesId);
    
    // Financial summary
    $financialSummary = $this->getFinancialSummary($startDate, $endDate, $speciesId);
    
    // Top performing flocks
    $topFlocks = $this->getTopFlocks($startDate, $endDate, $speciesId);
    
    // Species comparison
    $speciesComparison = $this->getSpeciesComparison($startDate, $endDate);
    
    // Get species list for filter
    $species = Species::where('is_active', true)->get();
    
    // Calculate additional metrics for the view
    $daysDifference = $startDate->diffInDays($endDate) + 1;
    $avgDailyRevenue = $daysDifference > 0 ? $financialSummary['total_revenue'] / $daysDifference : 0;
    $avgDailyExpense = $daysDifference > 0 ? $financialSummary['total_expenses'] / $daysDifference : 0;
    
    // Total transactions (expenses)
    $totalExpensesCount = Expense::whereBetween('expense_date', [$startDate, $endDate])->count();
    $totalTransactions = $totalExpensesCount;
    
    // Active flocks and total animals
    $activeFlocks = Flock::where('status', 'active')->count();
    $totalAnimals = Flock::where('status', 'active')->sum('current_count');
    
    // Occupancy rate
    $totalCapacity = \App\Models\House::sum('capacity');
    $occupancyRate = $totalCapacity > 0 ? ($totalAnimals / $totalCapacity) * 100 : 0;
    
    // Production metrics (using daily logs)
    $eggProduction = 0;
    try {
        $eggProduction = DailyLog::whereBetween('log_date', [$startDate, $endDate])->sum(DB::raw("JSON_EXTRACT(species_metrics, '$.egg_count')"));
    } catch (\Exception $e) {
        $eggProduction = 0;
    }
    $eggProductionRate = $totalAnimals > 0 ? ($eggProduction / $totalAnimals / 30) * 100 : 0;
    
    // Mortality rate
    $totalMortality = DailyLog::whereBetween('log_date', [$startDate, $endDate])->sum('mortality_count');
    $mortalityRate = $totalAnimals > 0 ? ($totalMortality / $totalAnimals) * 100 : 0;
    
    // Feed Conversion Ratio - using daily logs only
    $totalFeed = DailyLog::whereBetween('log_date', [$startDate, $endDate])->sum('feed_intake_kg');
    // Approximate weight gain from daily logs average weight
    $avgWeight = DailyLog::whereBetween('log_date', [$startDate, $endDate])->avg('average_weight_kg');
    $totalWeight = $totalAnimals * ($avgWeight ?? 1);
    $fcr = $totalWeight > 0 ? $totalFeed / $totalWeight : 0;
    
    // Chart data for revenue vs expenses
    $chartLabels = [];
    $revenueData = [];
    $expenseData = [];
    
    if ($period === 'week') {
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $chartLabels[] = $date->format('D');
            $revenueData[] = Sale::whereDate('sale_date', $date)->sum('total_amount');
            $expenseData[] = Expense::whereDate('expense_date', $date)->sum('amount');
        }
    } elseif ($period === 'month') {
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $chartLabels[] = $date->format('d M');
            $revenueData[] = Sale::whereDate('sale_date', $date)->sum('total_amount');
            $expenseData[] = Expense::whereDate('expense_date', $date)->sum('amount');
        }
    } else {
        for ($i = 1; $i <= 12; $i++) {
            $month = Carbon::create($endDate->year, $i, 1);
            $chartLabels[] = $month->format('M');
            $revenueData[] = Sale::whereYear('sale_date', $endDate->year)
            ->whereMonth('sale_date', $i)->sum('total_amount');
            $expenseData[] = Expense::whereYear('expense_date', $endDate->year)
                ->whereMonth('expense_date', $i)->sum('amount');
        }
    }
    
    // Expense categories for chart
    $expenseCategories = Expense::whereBetween('expense_date', [$startDate, $endDate])
        ->select('category', DB::raw('SUM(amount) as total'))
        ->groupBy('category')
        ->orderBy('total', 'desc')
        ->limit(6)
        ->get();
    
    $expenseCategoriesArray = $expenseCategories->pluck('category');
    $expenseAmountsArray = $expenseCategories->pluck('total');
    
    // Revenue sources (from species)
    $revenueSources = Flock::with('species')
        ->where('status', 'closed')
        ->whereBetween('end_date', [$startDate, $endDate])
        ->select('species_id', DB::raw('SUM(total_revenue) as total'))
        ->groupBy('species_id')
        ->get();
    
    $revenueSourcesArray = $revenueSources->map(function($item) {
        return $item->species->name ?? 'Unknown';
    });
    $revenueAmountsArray = $revenueSources->pluck('total');
    
    // If no revenue sources, add default
    if ($revenueSourcesArray->isEmpty()) {
        $revenueSourcesArray = collect(['No Data']);
        $revenueAmountsArray = collect([0]);
    }
    
    // Recent transactions (expenses)
    $recentTransactions = Expense::with(['flock', 'creator'])
        ->whereBetween('expense_date', [$startDate, $endDate])
        ->orderBy('expense_date', 'desc')
        ->limit(10)
        ->get();
    
    return view('analytics.index', compact(
        'performanceTrends',
        'financialSummary',
        'topFlocks',
        'speciesComparison',
        'period',
        'startDate',
        'endDate',
        'species',
        'avgDailyRevenue',
        'avgDailyExpense',
        'totalTransactions',
        'activeFlocks',
        'totalAnimals',
        'occupancyRate',
        'eggProduction',
        'eggProductionRate',
        'mortalityRate',
        'fcr',
        'chartLabels',
        'revenueData',
        'expenseData',
        'expenseCategoriesArray',
        'expenseAmountsArray',
        'revenueSourcesArray',
        'revenueAmountsArray',
        'recentTransactions'
    ));
}
    
    /**
     * Get performance trends data - Using actual columns from daily_logs
     */
    private function getPerformanceTrends($startDate, $endDate, $speciesId)
    {
        $query = DailyLog::whereBetween('log_date', [$startDate, $endDate]);
        
        if ($speciesId) {
            $query->whereHas('flock', function($q) use ($speciesId) {
                $q->where('species_id', $speciesId);
            });
        }
        
        // Use actual columns from your migration
        $dailyData = $query->select(
                DB::raw('DATE(log_date) as date'),
                DB::raw('SUM(mortality_count) as total_mortality'),
                DB::raw('SUM(culling_count) as total_culling'),
                DB::raw('SUM(feed_intake_kg) as total_feed'),
                DB::raw('AVG(average_weight_kg) as avg_weight'),
                DB::raw('AVG(min_temperature_c) as avg_min_temp'),
                DB::raw('AVG(max_temperature_c) as avg_max_temp'),
                DB::raw('COUNT(*) as flock_count')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        return $dailyData;
    }
    
    /**
     * Get financial summary
     */
    private function getFinancialSummary($startDate, $endDate, $speciesId)
    {
        // Revenue from Sales table (actual recorded sales)
        $revenueQuery = \App\Models\Sale::whereBetween('sale_date', [$startDate, $endDate]);
        
        if ($speciesId) {
            $revenueQuery->whereHas('flock', function($q) use ($speciesId) {
                $q->where('species_id', $speciesId);
            });
        }
        
        $totalRevenue = $revenueQuery->sum('total_amount');
        
        // Expenses
        $expenseQuery = Expense::whereBetween('expense_date', [$startDate, $endDate]);
        
        if ($speciesId) {
            $expenseQuery->where(function($q) use ($speciesId) {
                $q->whereHas('flock', function($sub) use ($speciesId) {
                    $sub->where('species_id', $speciesId);
                })->orWhereHas('house', function($sub) use ($speciesId) {
                    $sub->where('species_id', $speciesId);
                });
            });
        }
        
        $totalExpenses = $expenseQuery->sum('amount');
        
        $expensesByCategory = Expense::whereBetween('expense_date', [$startDate, $endDate])
            ->select('category', DB::raw('SUM(amount) as total'))
            ->groupBy('category')
            ->get();
        
        return [
            'total_revenue' => $totalRevenue,
            'total_expenses' => $totalExpenses,
            'net_profit' => $totalRevenue - $totalExpenses,
            'expenses_by_category' => $expensesByCategory
        ];
    }
    
    /**
 * Get top performing flocks
 */
private function getTopFlocks($startDate, $endDate, $speciesId)
{
    $query = Flock::with('species')
        ->where('status', 'closed')
        ->whereBetween('end_date', [$startDate, $endDate]);
    
    if ($speciesId) {
        $query->where('species_id', $speciesId);
    }
    
    // Get flocks
    $flocks = $query->get();
    
    // Enhance each flock with calculated metrics from daily logs
    foreach ($flocks as $flock) {
        // Get daily logs for this flock
        $dailyLogs = DailyLog::where('flock_id', $flock->id)
            ->whereBetween('log_date', [$startDate, $endDate])
            ->get();
        
        // Calculate total feed consumed
        $flock->total_feed_consumed = $dailyLogs->sum('feed_intake_kg');
        
        // Calculate total mortality
        $flock->total_mortality = $dailyLogs->sum('mortality_count') + $dailyLogs->sum('culling_count');
        
        // Calculate average weight from daily logs
        $avgWeight = $dailyLogs->avg('average_weight_kg');
        
        // Calculate FCR (Feed Conversion Ratio) using average weight
        $totalWeightGain = $flock->initial_count * ($avgWeight ?? 1);
        $flock->calculated_fcr = $totalWeightGain > 0 ? $flock->total_feed_consumed / $totalWeightGain : 0;
        
        // Calculate mortality rate from daily logs
        $flock->calculated_mortality_rate = $flock->initial_count > 0 
            ? ($flock->total_mortality / $flock->initial_count) * 100 
            : 0;
        
        // Calculate ROI (if revenue exists)
        $totalCost = $flock->total_feed_consumed * 0.5; // approximate feed cost per kg
        $flock->calculated_roi = $totalCost > 0 && $flock->total_revenue > 0
            ? (($flock->total_revenue - $totalCost) / $totalCost) * 100 
            : 0;
    }
    
    // Sort by FCR (lower is better) and take top 10
    $flocks = $flocks->sortBy('calculated_fcr')->take(10);
    
    return $flocks;
}
    
    /**
     * Get species comparison
     */
    private function getSpeciesComparison($startDate, $endDate)
    {
        $comparison = DB::table('flocks')
            ->join('species', 'flocks.species_id', '=', 'species.id')
            ->whereBetween('flocks.end_date', [$startDate, $endDate])
            ->where('flocks.status', 'closed')
            ->select(
                'species.name',
                'species.code',
                DB::raw('COUNT(*) as flock_count'),
                DB::raw('SUM(flocks.initial_count) as total_animals'),
                DB::raw('SUM(flocks.total_revenue) as total_revenue'),
                DB::raw('SUM(CASE WHEN flocks.end_date IS NOT NULL THEN 1 ELSE 0 END) as completed_flocks')
            )
            ->groupBy('species.id', 'species.name', 'species.code')
            ->get();
        
        // Add calculated metrics for each species based on daily logs
        foreach ($comparison as $species) {
            // Get all flocks for this species
            $speciesModel = Species::where('name', $species->name)->first();
            if (!$speciesModel) {
                $species->avg_mortality = 0;
                $species->avg_fcr = 0;
                continue;
            }
            
            $flockIds = Flock::where('species_id', $speciesModel->id)->pluck('id');
            
            $dailyLogs = DailyLog::whereIn('flock_id', $flockIds)
                ->whereBetween('log_date', [$startDate, $endDate])
                ->get();
            
            // Calculate total metrics from daily logs
            $totalMortality = $dailyLogs->sum('mortality_count');
            $totalFeed = $dailyLogs->sum('feed_intake_kg');
            
            // Calculate mortality rate
            $totalAnimals = $species->total_animals;
            $species->avg_mortality = $totalAnimals > 0 ? ($totalMortality / $totalAnimals) * 100 : 0;
            
            // Calculate FCR
            $avgWeight = $dailyLogs->avg('average_weight_kg');
            $totalWeightGain = $totalAnimals * ($avgWeight ?? 1);
            $species->avg_fcr = $totalWeightGain > 0 ? $totalFeed / $totalWeightGain : 0;
        }
        
        return $comparison;
    }
    
    /**
     * Export analytics data
     */
    public function export(Request $request)
    {
        $format = $request->get('format', 'excel');
        $startDate = Carbon::parse($request->get('start_date', Carbon::now()->subMonth()));
        $endDate = Carbon::parse($request->get('end_date', Carbon::now()));
        
        $data = [
            'performance_trends' => $this->getPerformanceTrends($startDate, $endDate, null),
            'financial_summary' => $this->getFinancialSummary($startDate, $endDate, null),
            'top_flocks' => $this->getTopFlocks($startDate, $endDate, null),
            'species_comparison' => $this->getSpeciesComparison($startDate, $endDate),
            'start_date' => $startDate,
            'end_date' => $endDate
        ];
        
        if ($format === 'pdf') {
            $pdf = Pdf::loadView('analytics.export-pdf', $data);
            return $pdf->download('analytics-report-' . $startDate->format('Y-m-d') . '.pdf');
        }
        
        // CSV Export
        $csvFileName = 'analytics-report-' . $startDate->format('Y-m-d') . '.csv';
        $handle = fopen('php://temp', 'w+');
        
        fputcsv($handle, ['Farm Analytics Report']);
        fputcsv($handle, ['Generated: ' . now()->format('Y-m-d H:i:s')]);
        fputcsv($handle, ['Period: ' . $startDate->format('d M Y') . ' to ' . $endDate->format('d M Y')]);
        fputcsv($handle, []);
        
        fputcsv($handle, ['FINANCIAL SUMMARY']);
        fputcsv($handle, ['Metric', 'Amount']);
        fputcsv($handle, ['Total Revenue', '$' . number_format($data['financial_summary']['total_revenue'], 2)]);
        fputcsv($handle, ['Total Expenses', '$' . number_format($data['financial_summary']['total_expenses'], 2)]);
        fputcsv($handle, ['Net Profit', '$' . number_format($data['financial_summary']['net_profit'], 2)]);
        fputcsv($handle, []);
        
        fputcsv($handle, ['PERFORMANCE TRENDS']);
        fputcsv($handle, ['Date', 'Mortality', 'Culling', 'Feed (kg)', 'Avg Weight (kg)']);
        foreach ($data['performance_trends'] as $trend) {
            fputcsv($handle, [
                $trend->date,
                $trend->total_mortality,
                $trend->total_culling,
                number_format($trend->total_feed, 2),
                number_format($trend->avg_weight, 3)
            ]);
        }
        
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);
        
        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $csvFileName . '"',
        ]);
    }
}