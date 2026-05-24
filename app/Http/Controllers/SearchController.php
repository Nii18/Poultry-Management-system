<?php
// app/Http/Controllers/SearchController.php

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
    /**
     * Display search results page (Blade).
     */
    public function index(Request $request)
    {
        $query = $request->get('query');

        if (!$query) {
            return view('search.results', [
                'flocks'      => collect(),
                'houses'      => collect(),
                'treatments'  => collect(),
                'daily_logs'  => collect(),
                'expenses'    => collect(),
                'query'       => '',
                'total'       => 0
            ]);
        }

        $results = $this->performSearch($query);

        return view('search.results', [
            'flocks'      => $results['flocks'],
            'houses'      => $results['houses'],
            'treatments'  => $results['treatments'],
            'daily_logs'  => $results['daily_logs'],
            'expenses'    => $results['expenses'],
            'query'       => $query,
            'total'       => $results['total']
        ]);
    }

    /**
     * API endpoint for real-time search (JSON).
     */
    public function apiSearch(Request $request)
    {
        $query = $request->get('query');

        if (strlen($query) < 2) {
            return response()->json([
                'total'       => 0,
                'flocks'      => [],
                'houses'      => [],
                'treatments'  => [],
                'daily_logs'  => [],
                'expenses'    => []
            ]);
        }

        $results = $this->performSearch($query);

        return response()->json([
            'total' => $results['total'],
            'flocks' => $results['flocks'],
            'houses' => $results['houses'],
            'treatments' => $results['treatments'],
            'daily_logs' => $results['daily_logs'],
            'expenses' => $results['expenses'],
        ]);
    }

    /**
     * Perform the actual search across Flocks, Houses, Treatments, etc.
     */
    public function performSearch($query)
    {
        $user = auth()->user();
        $userRole = $user->role;
        
        $results = [
            'flocks' => collect(),
            'houses' => collect(),
            'treatments' => collect(),
            'daily_logs' => collect(),
            'expenses' => collect(),
            'total' => 0
        ];

        // Flocks Search - Only use columns that exist
        if (in_array($userRole, ['admin', 'manager', 'head_worker', 'worker'])) {
            try {
                $flockQuery = Flock::query();
                
                // Role-based filtering
                if (in_array($userRole, ['worker', 'head_worker'])) {
                    $flockQuery->whereHas('assignedWorkers', function($q) use ($user) {
                        $q->where('user_id', $user->id);
                    });
                }
                
                // Search only on columns that exist in your flocks table
                // Common columns: flock_number, name, species_id, etc.
                $flocks = $flockQuery->where(function($q) use ($query) {
                        $q->where('flock_number', 'LIKE', "%{$query}%");
                        
                        // Only add these if they exist in your table
                        if (Schema::hasColumn('flocks', 'name')) {
                            $q->orWhere('name', 'LIKE', "%{$query}%");
                        }
                        if (Schema::hasColumn('flocks', 'description')) {
                            $q->orWhere('description', 'LIKE', "%{$query}%");
                        }
                    })
                    ->limit(5)
                    ->get()
                    ->map(function($flock) {
                        // Get species name if relationship exists
                        $speciesName = '';
                        if (method_exists($flock, 'species') && $flock->species) {
                            $speciesName = $flock->species->name ?? '';
                        }
                        
                        return [
                            'id' => $flock->id,
                            'name' => "Flock #{$flock->flock_number}",
                            'species' => $speciesName,
                            'bird_count' => $flock->bird_count ?? 0,
                            'type' => 'flock',
                            'url' => route('flocks.show', $flock->id),
                            'icon' => 'fa-users',
                            'color' => 'primary',
                            'subtitle' => ($speciesName ? $speciesName . ' • ' : '') . ($flock->bird_count ?? 0) . ' birds'
                        ];
                    });
                
                $results['flocks'] = $flocks;
                $results['total'] += $flocks->count();
            } catch (\Exception $e) {
                Log::error('Flocks search error: ' . $e->getMessage());
                $results['flocks'] = collect();
            }
        }

        // Houses Search
        if (in_array($userRole, ['admin', 'manager'])) {
            try {
                $houses = House::where(function($q) use ($query) {
                        $q->where('name', 'LIKE', "%{$query}%");
                        if (Schema::hasColumn('houses', 'code')) {
                            $q->orWhere('code', 'LIKE', "%{$query}%");
                        }
                    })
                    ->limit(5)
                    ->get()
                    ->map(function($house) {
                        return [
                            'id' => $house->id,
                            'name' => $house->name,
                            'code' => $house->code ?? 'N/A',
                            'capacity' => $house->capacity ?? 'N/A',
                            'type' => 'house',
                            'url' => route('houses.show', $house->id),
                            'icon' => 'fa-building',
                            'color' => 'success',
                            'subtitle' => 'Code: ' . ($house->code ?? 'N/A') . ' • Capacity: ' . ($house->capacity ?? 'N/A')
                        ];
                    });
                
                $results['houses'] = $houses;
                $results['total'] += $houses->count();
            } catch (\Exception $e) {
                Log::error('Houses search error: ' . $e->getMessage());
                $results['houses'] = collect();
            }
        }

        // Treatments Search
        if (in_array($userRole, ['admin', 'manager', 'veterinarian'])) {
            try {
                $treatments = Treatment::with('flock')
                    ->where(function($q) use ($query) {
                        $q->where('medication_name', 'LIKE', "%{$query}%");
                        if (Schema::hasColumn('treatments', 'diagnosis')) {
                            $q->orWhere('diagnosis', 'LIKE', "%{$query}%");
                        }
                        $q->orWhereHas('flock', function($subq) use ($query) {
                            $subq->where('flock_number', 'LIKE', "%{$query}%");
                        });
                    })
                    ->limit(5)
                    ->get()
                    ->map(function($treatment) {
                        return [
                            'id' => $treatment->id,
                            'name' => $treatment->medication_name,
                            'flock_number' => $treatment->flock->flock_number ?? 'N/A',
                            'type' => 'treatment',
                            'url' => route('treatments.show', $treatment->id),
                            'icon' => 'fa-stethoscope',
                            'color' => 'danger',
                            'subtitle' => 'Flock #' . ($treatment->flock->flock_number ?? 'N/A')
                        ];
                    });
                
                $results['treatments'] = $treatments;
                $results['total'] += $treatments->count();
            } catch (\Exception $e) {
                Log::error('Treatments search error: ' . $e->getMessage());
                $results['treatments'] = collect();
            }
        }

        // Daily Logs Search
        if (in_array($userRole, ['admin', 'manager', 'head_worker', 'worker'])) {
            try {
                $logQuery = DailyLog::with('flock');
                
                if (in_array($userRole, ['worker', 'head_worker'])) {
                    $logQuery->where('user_id', $user->id);
                }
                
                $daily_logs = $logQuery->where(function($q) use ($query) {
                        $q->where('notes', 'LIKE', "%{$query}%")
                          ->orWhereHas('flock', function($subq) use ($query) {
                              $subq->where('flock_number', 'LIKE', "%{$query}%");
                          });
                    })
                    ->limit(5)
                    ->get()
                    ->map(function($log) {
                        return [
                            'id' => $log->id,
                            'name' => 'Daily Log #' . $log->id,
                            'date' => $log->log_date->format('Y-m-d'),
                            'type' => 'daily_log',
                            'url' => route('daily-logs.show', $log->id),
                            'icon' => 'fa-clipboard-list',
                            'color' => 'info',
                            'subtitle' => 'Flock #' . ($log->flock->flock_number ?? 'N/A') . ' • ' . $log->log_date->format('M d, Y')
                        ];
                    });
                
                $results['daily_logs'] = $daily_logs;
                $results['total'] += $daily_logs->count();
            } catch (\Exception $e) {
                Log::error('Daily logs search error: ' . $e->getMessage());
                $results['daily_logs'] = collect();
            }
        }

        // Expenses Search
        if (in_array($userRole, ['admin', 'manager', 'accountant'])) {
            try {
                $expenses = Expense::where(function($q) use ($query) {
                        $q->where('description', 'LIKE', "%{$query}%")
                          ->orWhere('category', 'LIKE', "%{$query}%");
                        if (Schema::hasColumn('expenses', 'reference_number')) {
                            $q->orWhere('reference_number', 'LIKE', "%{$query}%");
                        }
                    })
                    ->limit(5)
                    ->get()
                    ->map(function($expense) {
                        return [
                            'id' => $expense->id,
                            'name' => $expense->description,
                            'amount' => $expense->amount,
                            'category' => $expense->category,
                            'type' => 'expense',
                            'url' => route('expenses.show', $expense->id),
                            'icon' => 'fa-dollar-sign',
                            'color' => 'warning',
                            'subtitle' => ucfirst($expense->category) . ' • $' . number_format($expense->amount, 2)
                        ];
                    });
                
                $results['expenses'] = $expenses;
                $results['total'] += $expenses->count();
            } catch (\Exception $e) {
                Log::error('Expenses search error: ' . $e->getMessage());
                $results['expenses'] = collect();
            }
        }

        return $results;
    }
}