<?php
// app/Http/Controllers/SearchController.php
// Drop-in replacement — preserves all your existing logic,
// adds scope-aware filtering for the role-based header search.

namespace App\Http\Controllers;

use App\Models\Flock;
use App\Models\House;
use App\Models\Treatment;
use App\Models\DailyLog;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class SearchController extends Controller
{
    // ──────────────────────────────────────────────────────────────
    // What each role is allowed to search — single source of truth.
    // Keep this in sync with the $searchConfig array in your header blade.
    // ──────────────────────────────────────────────────────────────
    private const ROLE_SCOPES = [
        'admin'       => ['flocks', 'houses', 'treatments', 'daily_logs', 'expenses'],
        'manager'     => ['flocks', 'houses', 'treatments', 'daily_logs', 'expenses'],
        'head_worker' => ['flocks', 'daily_logs'],
        'worker'      => ['daily_logs'],
        'veterinarian'=> ['treatments'],
        'accountant'  => ['expenses'],
    ];

    // ──────────────────────────────────────────────────────────────
    // Full-page search results (Blade view)
    // ──────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $query  = $request->get('query', '');
        $scopes = $this->resolveScopes($request->get('scopes', ''));

        if (!$query) {
            return view('search.results', [
                'flocks'     => collect(),
                'houses'     => collect(),
                'treatments' => collect(),
                'daily_logs' => collect(),
                'expenses'   => collect(),
                'query'      => '',
                'total'      => 0,
            ]);
        }

        $results = $this->performSearch($query, $scopes);

        return view('search.results', array_merge(['query' => $query], $results));
    }

    // ──────────────────────────────────────────────────────────────
    // AJAX endpoint for real-time header search (JSON)
    // ──────────────────────────────────────────────────────────────
    public function apiSearch(Request $request)
    {
        $query  = $request->get('query', '');
        $scopes = $this->resolveScopes($request->get('scopes', ''));

        if (strlen($query) < 2) {
            return response()->json([
                'total'      => 0,
                'flocks'     => [],
                'houses'     => [],
                'treatments' => [],
                'daily_logs' => [],
                'expenses'   => [],
            ]);
        }

        $results = $this->performSearch($query, $scopes);

        return response()->json($results);
    }

    // ──────────────────────────────────────────────────────────────
    // Core search — unchanged logic, now scope-aware
    // ──────────────────────────────────────────────────────────────
    public function performSearch(string $query, array $scopes = []): array
    {
        $user     = auth()->user();
        $userRole = $user->role;

        // If no scopes supplied (e.g. called directly), default to role's full set
        if (empty($scopes)) {
            $scopes = self::ROLE_SCOPES[$userRole] ?? [];
        }

        $results = [
            'flocks'     => collect(),
            'houses'     => collect(),
            'treatments' => collect(),
            'daily_logs' => collect(),
            'expenses'   => collect(),
            'total'      => 0,
        ];

        // ── Flocks ──────────────────────────────────────────────
        if (in_array('flocks', $scopes)) {
            try {
                $flockQuery = Flock::query();

                // Workers/head workers only see their assigned flocks
                if (in_array($userRole, ['worker', 'head_worker'])) {
                    $flockQuery->whereHas('assignedWorkers', function ($q) use ($user) {
                        $q->where('user_id', $user->id);
                    });
                }

                $flocks = $flockQuery
                    ->where(function ($q) use ($query) {
                        $q->where('flock_number', 'LIKE', "%{$query}%");
                        if (Schema::hasColumn('flocks', 'name')) {
                            $q->orWhere('name', 'LIKE', "%{$query}%");
                        }
                        if (Schema::hasColumn('flocks', 'description')) {
                            $q->orWhere('description', 'LIKE', "%{$query}%");
                        }
                    })
                    ->limit(5)
                    ->get()
                    ->map(function ($flock) {
                        $speciesName = method_exists($flock, 'species') && $flock->species
                            ? ($flock->species->name ?? '')
                            : '';

                        return [
                            'id'       => $flock->id,
                            'name'     => "Flock #{$flock->flock_number}",
                            'species'  => $speciesName,
                            'type'     => 'flock',
                            'url'      => route('flocks.show', $flock->id),
                            'icon'     => 'fa-users',
                            'color'    => 'primary',
                            'subtitle' => ($speciesName ? $speciesName . ' · ' : '')
                                        . ($flock->bird_count ?? 0) . ' birds',
                        ];
                    });

                $results['flocks']  = $flocks;
                $results['total']  += $flocks->count();
            } catch (\Exception $e) {
                Log::error('Flocks search error: ' . $e->getMessage());
            }
        }

        // ── Houses ──────────────────────────────────────────────
        if (in_array('houses', $scopes)) {
            try {
                $houses = House::where(function ($q) use ($query) {
                        $q->where('name', 'LIKE', "%{$query}%");
                        if (Schema::hasColumn('houses', 'code')) {
                            $q->orWhere('code', 'LIKE', "%{$query}%");
                        }
                        if (Schema::hasColumn('houses', 'house_code')) {
                            $q->orWhere('house_code', 'LIKE', "%{$query}%");
                        }
                    })
                    ->limit(5)
                    ->get()
                    ->map(function ($house) {
                        $code = $house->house_code ?? $house->code ?? 'N/A';
                        return [
                            'id'       => $house->id,
                            'name'     => $house->name,
                            'code'     => $code,
                            'capacity' => $house->capacity ?? 'N/A',
                            'type'     => 'house',
                            'url'      => route('houses.show', $house->id),
                            'icon'     => 'fa-building',
                            'color'    => 'success',
                            'subtitle' => 'Code: ' . $code . ' · Capacity: ' . ($house->capacity ?? 'N/A'),
                        ];
                    });

                $results['houses']  = $houses;
                $results['total']  += $houses->count();
            } catch (\Exception $e) {
                Log::error('Houses search error: ' . $e->getMessage());
            }
        }

        // ── Treatments ──────────────────────────────────────────
        if (in_array('treatments', $scopes)) {
            try {
                $treatments = Treatment::with('flock')
                    ->where(function ($q) use ($query) {
                        $q->where('medication_name', 'LIKE', "%{$query}%");
                        if (Schema::hasColumn('treatments', 'diagnosis')) {
                            $q->orWhere('diagnosis', 'LIKE', "%{$query}%");
                        }
                        $q->orWhereHas('flock', function ($sub) use ($query) {
                            $sub->where('flock_number', 'LIKE', "%{$query}%");
                        });
                    })
                    ->limit(5)
                    ->get()
                    ->map(function ($treatment) {
                        return [
                            'id'           => $treatment->id,
                            'name'         => $treatment->medication_name,
                            'flock_number' => $treatment->flock->flock_number ?? 'N/A',
                            'type'         => 'treatment',
                            'url'          => route('treatments.show', $treatment->id),
                            'icon'         => 'fa-stethoscope',
                            'color'        => 'danger',
                            'subtitle'     => 'Flock #' . ($treatment->flock->flock_number ?? 'N/A')
                                           . (isset($treatment->diagnosis) ? ' · ' . $treatment->diagnosis : ''),
                        ];
                    });

                $results['treatments']  = $treatments;
                $results['total']      += $treatments->count();
            } catch (\Exception $e) {
                Log::error('Treatments search error: ' . $e->getMessage());
            }
        }

        // ── Daily Logs ──────────────────────────────────────────
        if (in_array('daily_logs', $scopes)) {
            try {
                $logQuery = DailyLog::with('flock');

                // Workers only see their own logs
                if (in_array($userRole, ['worker', 'head_worker'])) {
                    $logQuery->where('user_id', $user->id);
                }

                $daily_logs = $logQuery
                    ->where(function ($q) use ($query) {
                        $q->where('notes', 'LIKE', "%{$query}%")
                          ->orWhereHas('flock', function ($sub) use ($query) {
                              $sub->where('flock_number', 'LIKE', "%{$query}%");
                          });
                    })
                    ->limit(5)
                    ->get()
                    ->map(function ($log) {
                        $date = $log->log_date instanceof \Carbon\Carbon
                            ? $log->log_date
                            : \Carbon\Carbon::parse($log->log_date);

                        return [
                            'id'       => $log->id,
                            'name'     => 'Daily Log #' . $log->id,
                            'date'     => $date->format('Y-m-d'),
                            'type'     => 'daily_log',
                            'url'      => route('daily-logs.show', $log->id),
                            'icon'     => 'fa-clipboard-list',
                            'color'    => 'info',
                            'subtitle' => 'Flock #' . ($log->flock->flock_number ?? 'N/A')
                                        . ' · ' . $date->format('M d, Y'),
                        ];
                    });

                $results['daily_logs']  = $daily_logs;
                $results['total']      += $daily_logs->count();
            } catch (\Exception $e) {
                Log::error('Daily logs search error: ' . $e->getMessage());
            }
        }

        // ── Expenses ────────────────────────────────────────────
        if (in_array('expenses', $scopes)) {
            try {
                $expenses = Expense::where(function ($q) use ($query) {
                        $q->where('description', 'LIKE', "%{$query}%")
                          ->orWhere('category', 'LIKE', "%{$query}%");
                        if (Schema::hasColumn('expenses', 'vendor_name')) {
                            $q->orWhere('vendor_name', 'LIKE', "%{$query}%");
                        }
                        if (Schema::hasColumn('expenses', 'reference_number')) {
                            $q->orWhere('reference_number', 'LIKE', "%{$query}%");
                        }
                    })
                    ->limit(5)
                    ->get()
                    ->map(function ($expense) {
                        return [
                            'id'       => $expense->id,
                            'name'     => $expense->description,
                            'amount'   => $expense->amount,
                            'category' => $expense->category,
                            'type'     => 'expense',
                            'url'      => route('expenses.show', $expense->id),
                            'icon'     => 'fa-receipt',
                            'color'    => 'warning',
                            'subtitle' => ucfirst($expense->category)
                                        . ' · ₵' . number_format($expense->amount, 2),
                        ];
                    });

                $results['expenses']  = $expenses;
                $results['total']    += $expenses->count();
            } catch (\Exception $e) {
                Log::error('Expenses search error: ' . $e->getMessage());
            }
        }

        return $results;
    }

    // ──────────────────────────────────────────────────────────────
    // Resolve + validate scopes from the request
    // ──────────────────────────────────────────────────────────────

    /**
     * Parse the comma-separated scopes string sent by the header search,
     * then intersect with what the current role is actually allowed to see.
     *
     * This means even if someone edits the hidden <input> in devtools,
     * they cannot access data outside their role's permitted set.
     */
    private function resolveScopes(?string $rawScopes = ''): array
    {
        $userRole  = auth()->user()?->role ?? 'guest';
        $permitted = self::ROLE_SCOPES[$userRole] ?? [];

        if (empty(trim($rawScopes ?? ''))) {
            // No scopes sent → return everything the role can see
            return $permitted;
        }

        $requested = array_filter(
            array_map('trim', explode(',', $rawScopes ?? ''))
        );

        // Silently drop any scope the role isn't allowed
        return array_values(array_intersect($requested, $permitted));
    }
}