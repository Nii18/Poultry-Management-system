<?php

namespace App\Http\Controllers;

use App\Models\Flock;
use App\Models\DailyLog;
use App\Models\Treatment;
use App\Models\Vaccination;
use App\Models\HealthRecord;
use App\Models\Expense;
use App\Models\Sale;
use App\Models\Notification;
use App\Models\Species;
use App\Models\FeedDelivery;
use App\Models\House;
use App\Models\User;
use App\Models\WorkerTask;
use App\Models\WorkerTaskAssignment;
use App\Services\DailyTaskService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function __construct(protected DailyTaskService $taskService) {}

    public function index(Request $request)
    {
        $user = auth()->user();
        $role = $user->role;

        switch ($role) {
            case 'admin':
                return $this->adminDashboard($request);
            case 'manager':
                return $this->managerDashboard($request);
            case 'accountant':
                return $this->accountantDashboard($request);
            case 'veterinarian':
                return $this->veterinarianDashboard($request);
            case 'head_worker':
                return $this->workerDashboard($request);
            case 'worker':
                return $this->workerDashboard($request);
            default:
                return $this->adminDashboard($request);
        }
    }

    private function adminDashboard(Request $request)
    {
        $speciesId = $request->get('species_id');

        $activeFlocksQuery = Flock::with(['species', 'house'])
            ->where('status', 'active');

        $flocksQuery    = Flock::with(['species', 'house']);
        $dailyLogsQuery = DailyLog::with('flock');

        if ($speciesId) {
            $flocksQuery->where('species_id', $speciesId);
            $dailyLogsQuery->whereHas('flock', function ($q) use ($speciesId) {
                $q->where('species_id', $speciesId);
            });
        }

        $activeFlocks  = $flocksQuery->where('status', 'active')->get();
        $closedFlocks  = $flocksQuery->where('status', 'closed')->count();

        // FIX: current_count is a computed accessor, not a DB column.
        // ->sum('current_count') at the query-builder level returns 0/null
        // because it compiles to raw SQL SUM() on a nonexistent column.
        // Sum over the already-loaded $activeFlocks collection instead so
        // the PHP accessor actually runs.
        $totalAnimals = $activeFlocks->sum('current_count');

        $totalMortalityToday = $dailyLogsQuery->whereDate('log_date', Carbon::today())->sum('mortality_count');

        $avgFCR = $activeFlocks->avg(function ($flock) {
            return $flock->feed_conversion_ratio;
        }) ?: 0;

        $mortalityTrend = DailyLog::whereHas('flock', function ($q) use ($speciesId) {
                if ($speciesId) $q->where('species_id', $speciesId);
            })
            ->where('log_date', '>=', Carbon::now()->subDays(7))
            ->select(DB::raw('DATE(log_date) as date'), DB::raw('SUM(mortality_count) as total_mortality'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('total_mortality', 'date');

        $activeAlerts        = Notification::whereNull('read_at')->with(['flock', 'user'])->latest()->take(10)->get();
        $criticalAlertsCount = Notification::whereNull('read_at')->where('severity', 'critical')->count();

        $recentActivities = DailyLog::with(['flock.species', 'creator'])
            ->whereHas('flock', function ($q) use ($speciesId) {
                if ($speciesId) $q->where('species_id', $speciesId);
            })
            ->latest()
            ->take(10)
            ->get();

        $lowFeedStock = FeedDelivery::with('feedType')
            ->where('remaining_quantity_kg', '<', 500)
            ->where('expiry_date', '>', now())
            ->get();

        $currentMonthExpenses = Expense::whereMonth('expense_date', Carbon::now()->month)->sum('amount');
        $currentMonthRevenue  = Sale::whereMonth('sale_date', Carbon::now()->month)->sum('total_amount');

        $species = Species::where('is_active', true)->get();

        // FIX: build one active-flocks collection per species with ->get(),
        // then reuse it for sum/count/avg in PHP. Previously this ran
        // ->sum('current_count') on the query builder (broken, see above)
        // and separately re-queried for ->count() and ->get()->avg(),
        // doing 3 DB round-trips per species instead of 1.
        $speciesStats = [];
        $speciesIdMap = [];

        foreach ($species as $spec) {
            $speciesFlock = Flock::where('species_id', $spec->id)
                ->where('status', 'active')
                ->get();

            $speciesName = $spec->name;

            switch ($speciesName) {
                case 'Chicken':  $iconifyIcon = 'twemoji:chicken'; $iconColor = '#e67e22'; break;
                case 'Goat':     $iconifyIcon = 'twemoji:goat';    $iconColor = '#84CC16'; break;
                case 'Pig':      $iconifyIcon = 'mdi:pig';         $iconColor = '#ff69b4'; break;
                case 'Cattle':   $iconifyIcon = 'mdi:cow';         $iconColor = '#8b4513'; break;
                case 'Rabbit':   $iconifyIcon = 'mdi:rabbit';      $iconColor = '#ffc0cb'; break;
                case 'Turkey':   $iconifyIcon = 'mdi:turkey';      $iconColor = '#cd853f'; break;
                case 'Fish':     $iconifyIcon = 'mdi:fish';        $iconColor = '#3B82F6'; break;
                case 'Sheep':    $iconifyIcon = 'mdi:sheep';       $iconColor = '#8B5CF6'; break;
                default:         $iconifyIcon = 'mdi:pokeball';    $iconColor = '#6c5ce7'; break;
            }

            if (!empty($spec->color_hex)) {
                $iconColor = $spec->color_hex;
            }

            $speciesStats[$spec->code] = [
                'id'            => $spec->id,
                'name'          => $spec->name,
                'icon'          => $spec->icon ?? 'fas fa-paw',
                'iconify'       => $iconifyIcon,
                'color'         => $iconColor,
                'total_animals' => $speciesFlock->sum('current_count'),
                'active_flocks' => $speciesFlock->count(),
                'avg_fcr'       => round($speciesFlock->avg('feed_conversion_ratio'), 2),
            ];

            $speciesIdMap[$spec->code] = $spec->id;
        }

        return view('dashboard', compact(
            'activeFlocks', 'closedFlocks', 'totalAnimals', 'totalMortalityToday',
            'avgFCR', 'mortalityTrend', 'activeAlerts', 'criticalAlertsCount',
            'recentActivities', 'lowFeedStock', 'currentMonthExpenses', 'currentMonthRevenue',
            'species', 'speciesStats', 'speciesId', 'speciesIdMap'
        ));
    }

    private function managerDashboard(Request $request)
    {
        $activeFlocks      = Flock::where('status', 'active')->get();
        $activeFlocksCount = $activeFlocks->count();
        $totalAnimals      = $activeFlocks->sum('current_count'); // already correct — collection sum

        $avgFCR = 0;
        try {
            $avgFCR = $activeFlocks->avg('feed_conversion_ratio') ?: 0;
        } catch (\Exception $e) {
            $avgFCR = 0;
        }

        $todayMortality = DailyLog::whereDate('log_date', Carbon::today())->sum('mortality_count');

        $mortalityTrend = DailyLog::where('log_date', '>=', Carbon::now()->subDays(7))
            ->select(DB::raw('DATE(log_date) as date'), DB::raw('SUM(mortality_count) as total_mortality'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('total_mortality', 'date');

        $feedTrend = DailyLog::where('log_date', '>=', Carbon::now()->subDays(7))
            ->select(DB::raw('DATE(log_date) as date'), DB::raw('SUM(feed_intake_kg) as total_feed'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $lowFeedStock = collect();
        try {
            $lowFeedStock = FeedDelivery::with('feedType')
                ->where('remaining_quantity_kg', '<', 500)
                ->get();
        } catch (\Exception $e) {
            $lowFeedStock = collect();
        }

        return view('dashboard.manager', compact(
            'activeFlocks', 'activeFlocksCount', 'totalAnimals', 'avgFCR',
            'todayMortality', 'mortalityTrend', 'feedTrend', 'lowFeedStock'
        ));
    }

    private function accountantDashboard(Request $request)
    {
        $currentMonthExpenses = Expense::whereMonth('expense_date', Carbon::now()->month)->sum('amount');
        $currentMonthRevenue  = Sale::whereMonth('sale_date', Carbon::now()->month)->sum('total_amount');
        $currentMonthProfit   = $currentMonthRevenue - $currentMonthExpenses;

        $monthLabels     = [];
        $monthlyRevenue  = [];
        $monthlyExpenses = [];

        for ($i = 5; $i >= 0; $i--) {
            $date              = Carbon::now()->subMonths($i);
            $monthLabels[]     = $date->format('M Y');
            $monthlyRevenue[]  = Sale::whereMonth('sale_date', $date->month)->whereYear('sale_date', $date->year)->sum('total_amount');
            $monthlyExpenses[] = Expense::whereMonth('expense_date', $date->month)->whereYear('expense_date', $date->year)->sum('amount');
        }

        $expenseCategories = Expense::select('category', DB::raw('SUM(amount) as total'))
            ->whereMonth('expense_date', Carbon::now()->month)
            ->groupBy('category')
            ->get();

        $expenseCategoryNames = $expenseCategories->pluck('category')->map(fn($c) => ucfirst($c))->toArray();
        $expenseAmounts       = $expenseCategories->pluck('total')->toArray();

        $recentExpenses = Expense::latest()->take(5)->get()->map(fn($e) => (object)[
            'date'        => $e->expense_date->format('d M Y'),
            'type'        => 'expense',
            'description' => $e->description,
            'amount'      => $e->amount,
        ]);

        $recentSales = Sale::latest()->take(5)->get()->map(fn($s) => (object)[
            'date'        => $s->sale_date->format('d M Y'),
            'type'        => 'revenue',
            'description' => $s->description ?? 'Sale of ' . str_replace('_', ' ', $s->product_type),
            'amount'      => $s->total_amount,
        ]);

        $recentTransactions = $recentExpenses->concat($recentSales)->sortByDesc('date')->take(10);

        return view('dashboard.accountant', compact(
            'currentMonthExpenses', 'currentMonthRevenue', 'currentMonthProfit',
            'monthLabels', 'monthlyRevenue', 'monthlyExpenses',
            'expenseCategoryNames', 'expenseAmounts', 'recentTransactions'
        ));
    }

    private function veterinarianDashboard(Request $request)
    {
        $todayMortality = DailyLog::whereDate('log_date', Carbon::today())->sum('mortality_count');

        $activeTreatments = Treatment::where(function ($query) {
                $query->where('end_date', '>=', Carbon::today())
                      ->orWhereNull('end_date');
            })
            ->with('flock')
            ->orderBy('end_date', 'asc')
            ->take(10)
            ->get();

        $upcomingVaccinations = Vaccination::where('administration_date', '>=', Carbon::today())
            ->where('administration_date', '<=', Carbon::today()->addDays(14))
            ->with('flock')
            ->orderBy('administration_date', 'asc')
            ->take(10)
            ->get();

        $recentHealthRecords = HealthRecord::with('flock')
            ->orderBy('record_date', 'desc')
            ->take(10)
            ->get();

        $healthAlerts = Notification::where('severity', 'critical')
            ->whereNull('read_at')
            ->latest()
            ->take(10)
            ->get();

        $withdrawalAlerts = Treatment::whereNotNull('withdrawal_end_date')
            ->where('withdrawal_end_date', '>=', Carbon::today())
            ->where('withdrawal_end_date', '<=', Carbon::today()->addDays(7))
            ->with('flock')
            ->orderBy('withdrawal_end_date', 'asc')
            ->get();

        return view('dashboard.veterinarian', compact(
            'todayMortality', 'activeTreatments', 'upcomingVaccinations',
            'recentHealthRecords', 'healthAlerts', 'withdrawalAlerts'
        ));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Worker / Head-worker dashboard
    // ─────────────────────────────────────────────────────────────────────────

    private function workerDashboard(Request $request)
    {
        $user             = auth()->user();
        $userId           = $user->id;
        $isAdminOrManager = in_array($user->role, ['admin', 'manager']);

        $activeFlocks         = Flock::where('status', 'active')->get();
        $todayMortality       = DailyLog::whereDate('log_date', Carbon::today())->sum('mortality_count');
        $todayFeedConsumption = DailyLog::whereDate('log_date', Carbon::today())->sum('feed_intake_kg');

        $todayTasks = collect();

        $currentWindow = $this->taskService->currentWindow();

        if (in_array($user->role, ['worker', 'head_worker'])) {
            $this->taskService->generateForWorker($userId);

            $todayTasks = WorkerTaskAssignment::where('assigned_to', $userId)
                ->whereDate('assignment_date', Carbon::today())
                ->with([
                    'task' => fn($q) => $q->select(
                        'id', 'title', 'description', 'priority',
                        'start_time', 'end_time', 'window'
                    ),
                ])
                ->get()
                ->sortBy(fn($assignment) => $assignment->task?->start_time)
                ->values();
        }

        $totalTasksToday     = $todayTasks->count();
        $completedTasksToday = $todayTasks->where('status', 'completed')->count();

        $myRecentLogs = DailyLog::with('flock')
            ->where('created_by', $userId)
            ->latest()
            ->take(5)
            ->get();

        $teamRecentLogs = collect();
        if ($isAdminOrManager) {
            $teamRecentLogs = DailyLog::with(['flock', 'creator'])
                ->whereDate('log_date', Carbon::today())
                ->latest()
                ->take(10)
                ->get();
        }

        $feedTrend = DailyLog::where('log_date', '>=', Carbon::now()->subDays(7))
            ->select(DB::raw('DATE(log_date) as date'), DB::raw('SUM(feed_intake_kg) as total_feed'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $mortalityTrend = DailyLog::where('log_date', '>=', Carbon::now()->subDays(7))
            ->select(DB::raw('DATE(log_date) as date'), DB::raw('SUM(mortality_count) as total_mortality'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $lowFeedStock = FeedDelivery::with('feedType')
            ->where('remaining_quantity_kg', '<', 500)
            ->where('expiry_date', '>', now())
            ->get();

        $teamMembers = collect();
        if ($isAdminOrManager) {
            $teamMembers = User::where('role', 'worker')
                ->where('is_active', true)
                ->get(['id', 'name', 'role']);
        }

        return view('dashboard.worker', compact(
            'activeFlocks', 'todayMortality', 'todayFeedConsumption',
            'todayTasks', 'totalTasksToday', 'completedTasksToday',
            'myRecentLogs', 'teamRecentLogs', 'feedTrend', 'mortalityTrend',
            'lowFeedStock', 'teamMembers', 'isAdminOrManager', 'currentWindow'
        ));
    }

    // ─────────────────────────────────────────────────────────────────────────

    public function getChartsData(Request $request)
    {
        $period    = $request->get('period', 'week');
        $speciesId = $request->get('species_id');
        $endDate   = Carbon::now();

        $startDate = match ($period) {
            'month' => Carbon::now()->subMonth(),
            'year'  => Carbon::now()->subYear(),
            default => Carbon::now()->subDays(7),
        };

        $query = DailyLog::with('flock')->whereBetween('log_date', [$startDate, $endDate]);

        if ($speciesId) {
            $query->whereHas('flock', fn($q) => $q->where('species_id', $speciesId));
        }

        $feedData = $query
            ->select(DB::raw('DATE(log_date) as date'), DB::raw('SUM(feed_intake_kg) as total_feed'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json(['feed_consumption' => $feedData, 'period' => $period]);
    }

    public function totalAnimals(Request $request)
    {
        $speciesId = $request->get('species_id');

        // FIX: current_count is a computed accessor (initial_count - total_mortality),
        // not a real DB column. Any ->sum('current_count') or ->orderBy('current_count')
        // run at the query-builder level silently fails (SQL SUM() on a nonexistent
        // column returns 0/null; orderBy on a nonexistent column errors or no-ops
        // depending on strict mode). Load matching flocks into a collection first,
        // then sort/sum/paginate in PHP so the accessor actually executes.
        $allActiveFlocks = Flock::with(['species', 'house'])
            ->where('status', 'active')
            ->when($speciesId, fn($q) => $q->where('species_id', $speciesId))
            ->get()
            ->sortByDesc('current_count')
            ->values();

        $perPage = 10;
        $page    = (int) $request->get('page', 1);

        $flocks = new \Illuminate\Pagination\LengthAwarePaginator(
            $allActiveFlocks->forPage($page, $perPage),
            $allActiveFlocks->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $totalAnimals      = $allActiveFlocks->sum('current_count');
        $activeFlocksCount = $allActiveFlocks->count();
        $totalFlocks       = Flock::where('status', 'active')->count();
        $speciesCount      = Species::where('is_active', true)->count();

        // Distinct houses actually in use by active flocks, rather than
        // House::whereHas(...)->count(), which was correct in principle but
        // easy to confuse with active-flock count — made explicit here.
        $housesUsed = $allActiveFlocks->pluck('house_id')->filter()->unique()->count();

        $speciesBreakdown = [];
        $allActiveTotal   = $totalAnimals;

        foreach (Species::where('is_active', true)->get() as $species) {
            $speciesFlocksCollection = $allActiveFlocks->where('species_id', $species->id);
            $animalsCount = $speciesFlocksCollection->sum('current_count');
            $flocksCount  = $speciesFlocksCollection->count();

            if ($animalsCount === 0) continue;

            $speciesBreakdown[] = [
                'id'            => $species->id,
                'name'          => $species->name,
                'icon'          => $species->icon ?? 'mdi:paw',
                'color'         => $species->color_hex ?? '#0d6e4f',
                'flock_count'   => $flocksCount,
                'total_animals' => $animalsCount,
                'percentage'    => $allActiveTotal > 0
                                    ? round(($animalsCount / $allActiveTotal) * 100, 1)
                                    : 0,
            ];
        }

        usort($speciesBreakdown, fn($a, $b) => $b['total_animals'] <=> $a['total_animals']);

        return view('reports.total-animals', compact(
            'flocks', 'totalAnimals', 'activeFlocksCount', 'totalFlocks',
            'speciesCount', 'housesUsed', 'speciesBreakdown', 'speciesId'
        ));
    }
}