<?php
// app/Http/Controllers/SearchController.php

namespace App\Http\Controllers;

use App\Models\Flock;
use App\Models\House;
use App\Models\Treatment;
use App\Models\DailyLog;
use App\Models\Expense;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class SearchController extends Controller
{
    // ──────────────────────────────────────────────────────────────
    // Role → allowed search scopes, derived from sidebar visibility.
    //
    // admin / manager   : full access (all sidebar sections)
    // worker            : Daily Operations, Flocks, Housing, Breeding
    // veterinarian      : Health (treatments, vaccinations, health_records)
    // accountant        : Finance (expenses, sales/revenue)
    // head_worker       : same as worker + a bit of feed visibility
    // ──────────────────────────────────────────────────────────────
    private const ROLE_SCOPES = [
        'admin'        => ['flocks', 'houses', 'treatments', 'daily_logs', 'expenses', 'sales', 'breeding_records'],
        'manager'      => ['flocks', 'houses', 'treatments', 'daily_logs', 'expenses', 'sales', 'breeding_records'],
        'head_worker'  => ['flocks', 'houses', 'daily_logs', 'breeding_records'],
        'worker'       => ['flocks', 'houses', 'daily_logs', 'breeding_records'],
        'veterinarian' => ['treatments'],
        'accountant'   => ['expenses', 'sales'],
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
                'flocks'           => collect(),
                'houses'           => collect(),
                'treatments'       => collect(),
                'daily_logs'       => collect(),
                'expenses'         => collect(),
                'sales'            => collect(),
                'breeding_records' => collect(),
                'query'            => '',
                'total'            => 0,
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
                'total'            => 0,
                'flocks'           => [],
                'houses'           => [],
                'treatments'       => [],
                'daily_logs'       => [],
                'expenses'         => [],
                'sales'            => [],
                'breeding_records' => [],
            ]);
        }

        $results = $this->performSearch($query, $scopes);

        return response()->json($results);
    }

    // ──────────────────────────────────────────────────────────────
    // Core search
    // ──────────────────────────────────────────────────────────────
    public function performSearch(string $query, array $scopes = []): array
    {
        $user     = auth()->user();
        $userRole = $user->role;

        if (empty($scopes)) {
            $scopes = self::ROLE_SCOPES[$userRole] ?? [];
        }

        $results = [
            'flocks'           => collect(),
            'houses'           => collect(),
            'treatments'       => collect(),
            'daily_logs'       => collect(),
            'expenses'         => collect(),
            'sales'            => collect(),
            'breeding_records' => collect(),
            'total'            => 0,
        ];

        // ── Flocks ──────────────────────────────────────────────
        if (in_array('flocks', $scopes)) {
            try {
                $flockQuery = Flock::query();

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
                        if (Schema::hasColumn('flocks', 'breed_variety')) {
                            $q->orWhere('breed_variety', 'LIKE', "%{$query}%");
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
                $results['total'] += $flocks->count();
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
                $results['total'] += $houses->count();
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
                        if (Schema::hasColumn('treatments', 'administration_route')) {
                            $q->orWhere('administration_route', 'LIKE', "%{$query}%");
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
        // Sidebar: Finance section → admin, manager, accountant
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

        // ── Sales & Revenue ─────────────────────────────────────
        // Sidebar: "Sales & Revenue" → admin, manager, accountant
        if (in_array('sales', $scopes)) {
            try {
                $sales = Sale::with('flock')
                    ->where(function ($q) use ($query) {
                        $q->where('description', 'LIKE', "%{$query}%")
                          ->orWhere('product_type', 'LIKE', "%{$query}%")
                          ->orWhere('customer_name', 'LIKE', "%{$query}%");
                        if (Schema::hasColumn('sales', 'receipt_number')) {
                            $q->orWhere('receipt_number', 'LIKE', "%{$query}%");
                        }
                        $q->orWhereHas('flock', function ($sub) use ($query) {
                            $sub->where('flock_number', 'LIKE', "%{$query}%");
                        });
                    })
                    ->limit(5)
                    ->get()
                    ->map(function ($sale) {
                        $label = $sale->product_type_label ?? ucfirst(str_replace('_', ' ', $sale->product_type));
                        return [
                            'id'       => $sale->id,
                            'name'     => $label . ($sale->customer_name ? ' — ' . $sale->customer_name : ''),
                            'amount'   => $sale->total_amount,
                            'type'     => 'sale',
                            'url'      => route('sales.index'),   // show/edit page if you have one, else index
                            'icon'     => 'fa-chart-line',
                            'color'    => 'success',
                            'subtitle' => 'Flock #' . ($sale->flock->flock_number ?? 'N/A')
                                        . ' · ₵' . number_format($sale->total_amount, 2)
                                        . ' · ' . \Carbon\Carbon::parse($sale->sale_date)->format('M d, Y'),
                        ];
                    });

                $results['sales']  = $sales;
                $results['total'] += $sales->count();
            } catch (\Exception $e) {
                Log::error('Sales search error: ' . $e->getMessage());
            }
        }

        // ── Breeding Records ────────────────────────────────────
        // Sidebar: Breeding → admin, manager, worker
        if (in_array('breeding_records', $scopes)) {
            try {
                if (class_exists(\App\Models\BreedingRecord::class)) {
                    $breedingQuery = \App\Models\BreedingRecord::with('flock');

                    $breeding_records = $breedingQuery
                        ->where(function ($q) use ($query) {
                            if (Schema::hasColumn('breeding_records', 'notes')) {
                                $q->where('notes', 'LIKE', "%{$query}%");
                            }
                            if (Schema::hasColumn('breeding_records', 'status')) {
                                $q->orWhere('status', 'LIKE', "%{$query}%");
                            }
                            $q->orWhereHas('flock', function ($sub) use ($query) {
                                $sub->where('flock_number', 'LIKE', "%{$query}%");
                            });
                        })
                        ->limit(5)
                        ->get()
                        ->map(function ($record) {
                            $date = $record->created_at instanceof \Carbon\Carbon
                                ? $record->created_at
                                : \Carbon\Carbon::parse($record->created_at);
                            return [
                                'id'       => $record->id,
                                'name'     => 'Breeding Record #' . $record->id,
                                'type'     => 'breeding_record',
                                'url'      => route('breeding-records.index'),
                                'icon'     => 'fa-heart',
                                'color'    => 'pink',
                                'subtitle' => 'Flock #' . ($record->flock->flock_number ?? 'N/A')
                                            . ' · ' . ucfirst($record->status ?? 'pending')
                                            . ' · ' . $date->format('M d, Y'),
                            ];
                        });

                    $results['breeding_records']  = $breeding_records;
                    $results['total']            += $breeding_records->count();
                }
            } catch (\Exception $e) {
                Log::error('Breeding records search error: ' . $e->getMessage());
            }
        }

        return $results;
    }

    // ──────────────────────────────────────────────────────────────
    // Resolve + validate scopes from the request.
    // Intersect requested scopes with what the role is allowed —
    // so devtools edits can't expose data outside the role's scope.
    // ──────────────────────────────────────────────────────────────
    private function resolveScopes(?string $rawScopes = ''): array
    {
        $userRole  = auth()->user()?->role ?? 'guest';
        $permitted = self::ROLE_SCOPES[$userRole] ?? [];

        if (empty(trim($rawScopes ?? ''))) {
            return $permitted;
        }

        $requested = array_filter(
            array_map('trim', explode(',', $rawScopes ?? ''))
        );

        return array_values(array_intersect($requested, $permitted));
    }
}