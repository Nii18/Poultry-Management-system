<?php
// app/Http/Controllers/ReportController.php

namespace App\Http\Controllers;

use App\Models\Flock;
use App\Models\DailyLog;
use App\Models\Expense;
use App\Models\Species;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Display the reports index page
     */
    public function index()
    {
        $species = Species::where('is_active', true)->get();

        return view('reports.index', compact('species'));
    }

    /**
     * Generate performance report
     */
    public function performance(Request $request)
    {
        // Determine filter mode
        $filterMode = $request->get('filter_mode', 'range');

        if ($filterMode === 'year') {
            $filterYear    = $request->get('year', date('Y'));
            $filterQuarter = $request->get('quarter');

            $startDate = Carbon::create($filterYear, 1, 1)->startOfDay();
            $endDate   = Carbon::create($filterYear, 12, 31)->endOfDay();

            if ($filterQuarter) {
                $quarterMap = [
                    1 => [Carbon::create($filterYear, 1,  1)->startOfDay(), Carbon::create($filterYear, 3,  31)->endOfDay()],
                    2 => [Carbon::create($filterYear, 4,  1)->startOfDay(), Carbon::create($filterYear, 6,  30)->endOfDay()],
                    3 => [Carbon::create($filterYear, 7,  1)->startOfDay(), Carbon::create($filterYear, 9,  30)->endOfDay()],
                    4 => [Carbon::create($filterYear, 10, 1)->startOfDay(), Carbon::create($filterYear, 12, 31)->endOfDay()],
                ];
                if (isset($quarterMap[$filterQuarter])) {
                    [$startDate, $endDate] = $quarterMap[$filterQuarter];
                }
            }

            $fromMonth = null;
            $toMonth   = null;

        } elseif ($filterMode === 'month') {
            $fromMonth = $request->get('from_month', date('Y-01'));
            $toMonth   = $request->get('to_month',   date('Y-m'));

            $startDate     = Carbon::parse($fromMonth . '-01')->startOfDay();
            $endDate       = Carbon::parse($toMonth   . '-01')->endOfMonth()->endOfDay();
            $filterYear    = $startDate->year;
            $filterQuarter = null;

        } else {
            // Default: date range
            $startDate = $request->has('start_date')
                ? Carbon::parse($request->get('start_date'))->startOfDay()
                : Carbon::now()->subDays(30)->startOfDay();

            $endDate = $request->has('end_date')
                ? Carbon::parse($request->get('end_date'))->endOfDay()
                : Carbon::now()->endOfDay();

            $filterYear    = $startDate->year;
            $filterQuarter = null;
            $fromMonth     = null;
            $toMonth       = null;
        }

        $speciesId = $request->get('species_id');

        // Get flocks data
        $query = Flock::with(['species']);

        if ($speciesId) {
            $query->where('species_id', $speciesId);
        }

        $flocks = $query->whereBetween('start_date', [$startDate, $endDate])
            ->orderBy('start_date', 'desc')
            ->paginate(20);

        // ── FIX 2: Revenue pulled from Sales table (consistent with financial report) ──
        $totalRevenue = (float) Sale::whereBetween('sale_date', [$startDate, $endDate])
            ->when($speciesId, fn($q) => $q->whereHas('flock', fn($f) => $f->where('species_id', $speciesId)))
            ->sum('total_amount');

        $totalExpenses = $this->getTotalExpenses($startDate, $endDate, $speciesId);

        // Calculate summary statistics
        $summary = [
            'total_flocks'                => $flocks->total(),
            'total_animals'               => $flocks->sum('initial_count'),
            'current_animals_in_system'   => Flock::where('status', 'active')->sum('current_count'),
            'avg_mortality_rate'          => $flocks->avg('mortality_rate'),
            'avg_fcr'                     => $flocks->avg('feed_conversion_ratio'),
            'avg_adg'                     => $flocks->avg('average_daily_gain'),
            'total_revenue'               => $totalRevenue,
            'total_expenses'              => $totalExpenses,
        ];

        $summary['net_profit'] = $summary['total_revenue'] - $summary['total_expenses'];
        $summary['avg_roi']    = $summary['total_expenses'] > 0
            ? ($summary['net_profit'] / $summary['total_expenses']) * 100
            : 0;

        // Get daily trends
        $dailyTrendsQuery = DailyLog::with('flock');

        if ($speciesId) {
            $dailyTrendsQuery->whereHas('flock', function ($q) use ($speciesId) {
                $q->where('species_id', $speciesId);
            });
        }

        $dailyTrends = $dailyTrendsQuery
            ->whereBetween('log_date', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(log_date) as date'),
                DB::raw('SUM(mortality_count) as total_mortality'),
                DB::raw('SUM(feed_intake_kg) as total_feed'),
                DB::raw('AVG(average_weight_kg) as avg_weight')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $species = Species::where('is_active', true)->get();

        return view('reports.performance', compact(
            'flocks',
            'summary',
            'dailyTrends',
            'startDate',
            'endDate',
            'filterMode',
            'filterYear',
            'filterQuarter',
            'fromMonth',
            'toMonth',
            'speciesId',
            'species'
        ));
    }

    /**
     * Generate health report
     */
    public function health(Request $request)
    {
        // ── Date parsing (flexible filter modes) ──────────────────
        $filterMode = $request->get('filter_mode', 'range');

        if ($filterMode === 'year') {
            $filterYear    = $request->get('year', date('Y'));
            $filterQuarter = $request->get('quarter');

            $startDate = Carbon::create($filterYear, 1, 1)->startOfDay();
            $endDate   = Carbon::create($filterYear, 12, 31)->endOfDay();

            if ($filterQuarter) {
                $quarterMap = [
                    1 => [Carbon::create($filterYear, 1,  1), Carbon::create($filterYear, 3,  31)->endOfDay()],
                    2 => [Carbon::create($filterYear, 4,  1), Carbon::create($filterYear, 6,  30)->endOfDay()],
                    3 => [Carbon::create($filterYear, 7,  1), Carbon::create($filterYear, 9,  30)->endOfDay()],
                    4 => [Carbon::create($filterYear, 10, 1), Carbon::create($filterYear, 12, 31)->endOfDay()],
                ];
                if (isset($quarterMap[$filterQuarter])) {
                    [$startDate, $endDate] = $quarterMap[$filterQuarter];
                }
            }

            $fromMonth = null;
            $toMonth   = null;

        } elseif ($filterMode === 'month') {
            $fromMonth = $request->get('from_month', date('Y-01'));
            $toMonth   = $request->get('to_month',   date('Y-m'));

            $startDate     = Carbon::parse($fromMonth . '-01')->startOfDay();
            $endDate       = Carbon::parse($toMonth   . '-01')->endOfMonth()->endOfDay();
            $filterYear    = $startDate->year;
            $filterQuarter = null;

        } else {
            $startDate = $request->has('start_date')
                ? Carbon::parse($request->get('start_date'))->startOfDay()
                : Carbon::now()->subDays(30)->startOfDay();

            $endDate = $request->has('end_date')
                ? Carbon::parse($request->get('end_date'))->endOfDay()
                : Carbon::now()->endOfDay();

            $filterYear    = $startDate->year;
            $filterQuarter = null;
            $fromMonth     = null;
            $toMonth       = null;
        }

        $speciesId = $request->get('species_id');

        // ── Mortality trends from DailyLog ────────────────────────
        $mortalityQuery = DailyLog::with('flock');

        if ($speciesId) {
            $mortalityQuery->whereHas('flock', function ($q) use ($speciesId) {
                $q->where('species_id', $speciesId);
            });
        }

        $mortalityTrends = $mortalityQuery
            ->whereBetween('log_date', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(log_date) as date'),
                DB::raw('SUM(mortality_count) as total_mortality'),
                DB::raw('SUM(culling_count) as total_culling')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // ── Health Records ────────────────────────────────────────
        $hrBase = \App\Models\HealthRecord::with(['flock.species'])
            ->whereBetween('record_date', [$startDate, $endDate]);

        if ($speciesId) {
            $hrBase->whereHas('flock', function ($q) use ($speciesId) {
                $q->where('species_id', $speciesId);
            });
        }

        $criticalAlerts = (clone $hrBase)
            ->where('severity', 'critical')
            ->orderBy('record_date', 'desc')
            ->take(10)
            ->get();

        $bySeverity = (clone $hrBase)
            ->select('severity', DB::raw('COUNT(*) as count'))
            ->groupBy('severity')
            ->get()
            ->keyBy('severity');

        $byRecordType = (clone $hrBase)
            ->select(
                'record_type',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(affected_count) as total_affected')
            )
            ->groupBy('record_type')
            ->orderBy('count', 'desc')
            ->get();

        $totalAffected      = (clone $hrBase)->sum('affected_count');
        $totalHealthRecords = (clone $hrBase)->count();

        $species = Species::where('is_active', true)->get();

        $summary = [
            'total_mortality'      => (int) $mortalityTrends->sum('total_mortality'),
            'total_culling'        => (int) $mortalityTrends->sum('total_culling'),
            'total_losses'         => (int) ($mortalityTrends->sum('total_mortality') + $mortalityTrends->sum('total_culling')),
            'avg_daily_mortality'  => round($mortalityTrends->avg('total_mortality') ?? 0, 1),
            'total_health_records' => $totalHealthRecords,
            'total_affected_birds' => (int) $totalAffected,
            'critical_count'       => (int) ($bySeverity->get('critical')->count ?? 0),
            'warning_count'        => (int) ($bySeverity->get('warning')->count  ?? 0),
            'info_count'           => (int) ($bySeverity->get('info')->count     ?? 0),
        ];

        return view('reports.health', compact(
            'mortalityTrends',
            'summary',
            'species',
            'startDate',
            'endDate',
            'speciesId',
            'filterMode',
            'filterYear',
            'filterQuarter',
            'fromMonth',
            'toMonth',
            'criticalAlerts',
            'byRecordType',
            'bySeverity'
        ));
    }

    /**
     * Generate financial report
     */
    public function financial(Request $request)
    {
        $filterMode = $request->get('filter_mode', 'year');

        // ── MODE 1: Year / Quarter ──────────────────────────────
        if ($filterMode === 'year' || (!$request->has('from_month') && !$request->has('start_date'))) {
            $year    = $request->get('year', date('Y'));
            $quarter = $request->get('quarter');

            $startDate = Carbon::create($year, 1, 1)->startOfDay();
            $endDate   = Carbon::create($year, 12, 31)->endOfDay();

            if ($quarter) {
                $quarterMap = [
                    1 => [Carbon::create($year, 1,  1), Carbon::create($year, 3,  31)->endOfDay()],
                    2 => [Carbon::create($year, 4,  1), Carbon::create($year, 6,  30)->endOfDay()],
                    3 => [Carbon::create($year, 7,  1), Carbon::create($year, 9,  30)->endOfDay()],
                    4 => [Carbon::create($year, 10, 1), Carbon::create($year, 12, 31)->endOfDay()],
                ];
                if (isset($quarterMap[$quarter])) {
                    [$startDate, $endDate] = $quarterMap[$quarter];
                }
            }

            $fromMonth   = null;
            $toMonth     = null;
            $customStart = null;
            $customEnd   = null;

            // Year mode: monthly trend charts scoped to the full selected year
            $monthlyExpenses = Expense::whereYear('expense_date', $year)
                ->select(DB::raw('MONTH(expense_date) as month'), DB::raw('SUM(amount) as total'))
                ->groupBy('month')
                ->orderBy('month')
                ->get();

            $monthlySales = Sale::whereYear('sale_date', $year)
                ->select(DB::raw('MONTH(sale_date) as month'), DB::raw('SUM(total_amount) as total'))
                ->groupBy('month')
                ->orderBy('month')
                ->get();
        }

        // ── MODE 2: Month Range ─────────────────────────────────
        // ── FIX 1: scope monthly chart data to the actual selected range,
        //    not just $startDate->year, to handle cross-year selections ──
        elseif ($filterMode === 'month') {
            $fromMonth = $request->get('from_month', date('Y-01'));
            $toMonth   = $request->get('to_month',   date('Y-m'));

            $startDate = Carbon::parse($fromMonth . '-01')->startOfDay();
            $endDate   = Carbon::parse($toMonth   . '-01')->endOfMonth()->endOfDay();

            $year        = $startDate->year;
            $quarter     = null;
            $customStart = null;
            $customEnd   = null;

            // Month-range mode: scope chart data to the actual from–to window
            $monthlyExpenses = Expense::whereBetween('expense_date', [$startDate, $endDate])
                ->select(DB::raw('MONTH(expense_date) as month'), DB::raw('SUM(amount) as total'))
                ->groupBy('month')
                ->orderBy('month')
                ->get();

            $monthlySales = Sale::whereBetween('sale_date', [$startDate, $endDate])
                ->select(DB::raw('MONTH(sale_date) as month'), DB::raw('SUM(total_amount) as total'))
                ->groupBy('month')
                ->orderBy('month')
                ->get();
        }

        // ── MODE 3: Custom Date Range ───────────────────────────
        else {
            $customStart = $request->get('start_date', date('Y-01-01'));
            $customEnd   = $request->get('end_date',   date('Y-m-d'));

            $startDate = Carbon::parse($customStart)->startOfDay();
            $endDate   = Carbon::parse($customEnd)->endOfDay();

            $year        = $startDate->year;
            $quarter     = null;
            $fromMonth   = null;
            $toMonth     = null;

            // Custom mode: scope chart data to the actual selected range
            $monthlyExpenses = Expense::whereBetween('expense_date', [$startDate, $endDate])
                ->select(DB::raw('MONTH(expense_date) as month'), DB::raw('SUM(amount) as total'))
                ->groupBy('month')
                ->orderBy('month')
                ->get();

            $monthlySales = Sale::whereBetween('sale_date', [$startDate, $endDate])
                ->select(DB::raw('MONTH(sale_date) as month'), DB::raw('SUM(total_amount) as total'))
                ->groupBy('month')
                ->orderBy('month')
                ->get();
        }

        // ── Expenses by category (filtered range) ───────────────
        $expensesByCategory = Expense::whereBetween('expense_date', [$startDate, $endDate])
            ->select('category', DB::raw('SUM(amount) as total'))
            ->groupBy('category')
            ->orderBy('total', 'desc')
            ->get();

        // ── Revenue by flock (filtered range) ───────────────────
        $revenueBySpecies = Sale::with('flock')
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->select('flock_id', DB::raw('SUM(total_amount) as total_revenue'), DB::raw('COUNT(*) as sale_count'))
            ->groupBy('flock_id')
            ->get();

        // ── Totals ───────────────────────────────────────────────
        $totalExpenses = (float) $expensesByCategory->sum('total');
        $totalRevenue  = (float) Sale::whereBetween('sale_date', [$startDate, $endDate])->sum('total_amount');
        $netProfit     = $totalRevenue - $totalExpenses;
        $profitMargin  = $totalRevenue  > 0 ? round(($netProfit     / $totalRevenue)  * 100, 1) : 0;
        $expenseRatio  = $totalRevenue  > 0 ? round(($totalExpenses / $totalRevenue)  * 100, 1) : 0;

        $species = Species::where('is_active', true)->get();

        return view('reports.financial', compact(
            'expensesByCategory',
            'monthlyExpenses',
            'monthlySales',
            'revenueBySpecies',
            'totalExpenses',
            'totalRevenue',
            'netProfit',
            'profitMargin',
            'expenseRatio',
            'year',
            'quarter',
            'filterMode',
            'fromMonth',
            'toMonth',
            'customStart',
            'customEnd',
            'startDate',
            'endDate',
            'species'
        ));
    }

    /**
     * Get total expenses for a date range
     */
    private function getTotalExpenses($startDate, $endDate, $speciesId = null)
    {
        $query = Expense::whereBetween('expense_date', [$startDate, $endDate]);

        if ($speciesId) {
            $query->where(function ($q) use ($speciesId) {
                $q->whereHas('flock', function ($sub) use ($speciesId) {
                    $sub->where('species_id', $speciesId);
                })->orWhereHas('house', function ($sub) use ($speciesId) {
                    $sub->where('species_id', $speciesId);
                });
            });
        }

        return $query->sum('amount');
    }

    /**
     * Get Profit & Loss data for AJAX modal
     */
    public function getProfitLossData(Request $request)
    {
        try {
            $period = $request->get('period', 'month');
            $year   = $request->get('year',   Carbon::now()->year);
            $month  = $request->get('month',  Carbon::now()->month);

            if ($period === 'month') {
                $startDate  = Carbon::create($year, $month, 1)->startOfDay();
                $endDate    = $startDate->copy()->endOfMonth();
                $periodText = Carbon::create($year, $month, 1)->format('F Y');

            } elseif ($period === 'quarter') {
                $quarter    = $request->get('quarter', (int) ceil($month / 3));
                $startDate  = Carbon::create($year, ($quarter - 1) * 3 + 1, 1)->startOfDay();
                $endDate    = $startDate->copy()->addMonths(3)->subDay()->endOfDay();
                $periodText = "Q{$quarter} {$year}";

            } else {
                $startDate  = Carbon::create($year, 1, 1)->startOfDay();
                $endDate    = Carbon::create($year, 12, 31)->endOfDay();
                $periodText = "Year {$year}";
            }

            $totalRevenue  = (float) Sale::whereBetween('sale_date',      [$startDate, $endDate])->sum('total_amount');
            $totalExpenses = (float) Expense::whereBetween('expense_date', [$startDate, $endDate])->sum('amount');
            $netProfit     = $totalRevenue - $totalExpenses;
            $profitMargin  = $totalRevenue > 0 ? round(($netProfit     / $totalRevenue) * 100, 1) : 0;
            $expenseRatio  = $totalRevenue > 0 ? round(($totalExpenses / $totalRevenue) * 100, 1) : 0;

            return response()->json([
                'success'       => true,
                'total_revenue' => $totalRevenue,
                'total_expenses'=> $totalExpenses,
                'net_profit'    => $netProfit,
                'profit_margin' => $profitMargin,
                'expense_ratio' => $expenseRatio,
                'period'        => $periodText,
                'is_profitable' => $netProfit >= 0,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}