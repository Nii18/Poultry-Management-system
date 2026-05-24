<?php
// app/Http/Controllers/FlockController.php

namespace App\Http\Controllers;

use App\Models\Flock;
use App\Models\Species;
use App\Models\House;
use App\Models\PerformanceMetric;
use App\Helpers\AuditHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class FlockController extends Controller
{
    /**
     * Display a listing of flocks
     */
    public function index(Request $request)
    {
        $speciesId = $request->get('species_id');
        $status = $request->get('status', 'active');
    
        $query = Flock::with(['species', 'house', 'dailyLogs' => function($q) {
            $q->latest()->limit(1);
        }]);
    
        if ($speciesId) $query->where('species_id', $speciesId);
        if ($status)    $query->where('status', $status);
    
        $flocks = $query->orderBy('start_date', 'desc')->paginate(10);
    
        // Correct aggregates — separate queries, not from paginator
        $totalAnimals = Flock::when($speciesId, fn($q) => $q->where('species_id', $speciesId))
            ->when($status, fn($q) => $q->where('status', $status))
            ->sum('current_count');
    
        $species = Species::where('is_active', true)->get();
    
        return view('flocks.index', compact('flocks', 'species', 'speciesId', 'status', 'totalAnimals'));
    }
    
    /**
     * Show the form for creating a new flock
     */
    public function create()
    {
        $species = Species::where('is_active', true)->get();
        $houses = House::where('status', 'active')->get();
        
        return view('flocks.create', compact('species', 'houses'));
    }
    
    /**
     * Store a newly created flock
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'species_id' => 'required|exists:species,id',
            'house_id' => 'required|exists:houses,id',
            'breed_variety' => 'required|string|max:255',
            'start_date' => 'required|date|before_or_equal:today',
            'initial_count' => 'required|integer|min:1',
            'source' => 'nullable|string|max:255',
            'production_type' => 'required|in:meat,eggs,milk,breeding,dual_purpose',
            'is_breeding_stock' => 'nullable|boolean',
            'parity_number' => 'nullable|integer|min:0',
            'notes' => 'nullable|string'
        ]);
        
        if ($validator->fails()) {

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
        
            return back()->withErrors($validator)->withInput();
        }
        
        DB::beginTransaction();
        
        try {
            // Generate unique flock number
            $flockNumber = $this->generateFlockNumber($request->species_id, $request->house_id);
            
            $flock = Flock::create([
                'species_id' => $request->species_id,
                'house_id' => $request->house_id,
                'flock_number' => $flockNumber,
                'breed_variety' => $request->breed_variety,
                'start_date' => $request->start_date,
                'initial_count' => $request->initial_count,
                'current_count' => $request->initial_count,
                'source' => $request->source,
                'production_type' => $request->production_type,
                'is_breeding_stock' => $request->is_breeding_stock ?? false,
                'parity_number' => $request->parity_number,
                'notes' => $request->notes,
                'created_by' => auth()->id()
            ]);
            
            // Log the creation
            AuditHelper::log(
                'create',
                "Created new flock #{$flock->flock_number} with {$flock->initial_count} animals",
                'flock',
                $flock->id,
                null,
                $flock->toArray()
            );
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Flock created successfully',
                'flock_id' => $flock->id
            ]);
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create flock: ' . $e->getMessage());
        }
    }
    
    /**
     * Display the specified flock
     */
    public function show($id)
    {
        $flock = Flock::with([
            'species', 
            'house', 
            'dailyLogs' => function($q) {
                $q->latest()->limit(30);
            },
            'vaccinations',
            'treatments' => function($q) {
                $q->where('withdrawal_end_date', '>', now())
                  ->orWhereNull('withdrawal_end_date');
            },
            'feedIssuances' => function($q) {
                $q->latest()->limit(10);
            },
            'performanceMetrics' => function($q) {
                $q->latest()->limit(1);
            },
            'breedingRecords' => function($q) {
                $q->latest()->limit(5);
            }
        ])->findOrFail($id);
        
        // Calculate summary statistics
        $summary = [
            'age_days' => $flock->age_in_days,
            'age_weeks' => $flock->age_in_weeks,
            'mortality_rate' => $flock->mortality_rate,
            'current_count' => $flock->current_count,
            'total_feed' => $flock->total_feed_consumed,
            'avg_daily_gain' => $flock->average_daily_gain,
            'fcr' => $flock->feed_conversion_ratio,
            'survival_rate' => 100 - $flock->mortality_rate
        ];
        
        // Get weekly performance data for charts
        $weeklyData = $flock->dailyLogs()
            ->select(
                DB::raw('WEEK(log_date) as week'),
                DB::raw('AVG(average_weight_kg) as avg_weight'),
                DB::raw('SUM(feed_intake_kg) as total_feed'),
                DB::raw('SUM(mortality_count) as total_mortality')
            )
            ->groupBy('week')
            ->orderBy('week')
            ->get();
        
        // Get upcoming treatments with withdrawal
        $activeTreatments = $flock->treatments()
            ->where('withdrawal_end_date', '>', now())
            ->orderBy('withdrawal_end_date')
            ->get();
        
        return view('flocks.show', compact('flock', 'summary', 'weeklyData', 'activeTreatments'));
    }
    
    /**
     * Show the form for editing the specified flock
     */
    public function edit($id)
    {
        $flock = Flock::findOrFail($id);
        $species = Species::where('is_active', true)->get();
        $houses = House::where('status', 'active')->get();
        
        return view('flocks.edit', compact('flock', 'species', 'houses'));
    }
    
    /**
     * Update the specified flock
     */
    public function update(Request $request, $id)
{
    $validator = Validator::make($request->all(), [
        'breed_variety'     => 'required|string|max:255',
        'house_id'          => 'required|exists:houses,id',
        'production_type'   => 'required|in:meat,eggs,milk,breeding,dual_purpose',
        'is_breeding_stock' => 'nullable|boolean',
        'notes'             => 'nullable|string'
    ]);

    if ($validator->fails()) {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }
        return back()->withErrors($validator)->withInput();
    }

    $flock = Flock::findOrFail($id);
    $oldValues = $flock->toArray();

    DB::beginTransaction();

    try {
        $flock->update([
            'breed_variety'     => $request->breed_variety,
            'house_id'          => $request->house_id,
            'production_type'   => $request->production_type,
            'is_breeding_stock' => $request->is_breeding_stock ?? false,
            'notes'             => $request->notes
        ]);

        AuditHelper::log(
            'update',
            "Updated flock #{$flock->flock_number}",
            'flock',
            $flock->id,
            $oldValues,
            $flock->toArray()
        );

        DB::commit();

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Flock updated successfully']);
        }
        return redirect()->route('flocks.show', $flock)->with('success', 'Flock updated successfully');

    } catch (\Exception $e) {
        DB::rollBack();

        if ($request->expectsJson()) {
            return response()->json(['success' => false, 'message' => 'Failed to update: ' . $e->getMessage()], 500);
        }
        return back()->with('error', 'Failed to update flock: ' . $e->getMessage());
    }
}
    
    /**
     * Close a flock (mark as completed)
     */
    public function close(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'end_date' => 'required|date',
            'final_count' => 'required|integer|min:0',
            'total_weight_kg' => 'required|numeric|min:0',
            'average_price_per_kg' => 'required|numeric|min:0'
        ]);
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        $flock = Flock::findOrFail($id);
        
        // Validate final count doesn't exceed initial count
        if ($request->final_count > $flock->initial_count) {
            return back()->with('error', 'Final count cannot exceed initial count.');
        }
        
        // Validate end date is after start date
        if ($request->end_date < $flock->start_date) {
            return back()->with('error', 'End date must be after start date.');
        }
        
        DB::beginTransaction();
        
        try {
            $totalRevenue = $request->total_weight_kg * $request->average_price_per_kg;
            
            $oldValues = $flock->toArray();
            
            $flock->update([
                'status' => 'closed',
                'end_date' => $request->end_date,
                'final_count' => $request->final_count,
                'total_weight_kg' => $request->total_weight_kg,
                'average_price_per_kg' => $request->average_price_per_kg,
                'total_revenue' => $totalRevenue
            ]);
            
            // Log the closure
            AuditHelper::log(
                'close',
                "Closed flock #{$flock->flock_number} with final count of {$flock->final_count} animals",
                'flock',
                $flock->id,
                $oldValues,
                $flock->toArray()
            );
            
            // Calculate and save final performance metrics
            $this->calculatePerformanceMetrics($flock);
            
            DB::commit();
            
            return redirect()->route('flocks.show', $flock)
                ->with('success', 'Flock closed successfully');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to close flock: ' . $e->getMessage());
        }
    }
    
    /**
     * Remove the specified flock
     */
    public function destroy($id)
    {
        $flock = Flock::findOrFail($id);
        
        if ($flock->status === 'active') {
            return back()->with('error', 'Cannot delete active flock. Please close it first.');
        }
        
        $flockNumber = $flock->flock_number;
        $oldValues = $flock->toArray();
        
        // Log the deletion before deleting
        AuditHelper::log(
            'delete',
            "Deleted flock #{$flockNumber}",
            'flock',
            $flock->id,
            $oldValues,
            null
        );
        
        $flock->delete();
        
        return redirect()->route('flocks.index')
            ->with('success', 'Flock deleted successfully');
    }
    
    /**
     * Display flock performance metrics
     */
    public function performance($id)
    {
        $flock = Flock::findOrFail($id);
        
        $metrics = $flock->performanceMetrics()
            ->orderBy('calculated_date', 'desc')
            ->paginate(20);
            
        return view('flocks.performance', compact('flock', 'metrics'));
    }
    
    /**
     * Generate unique flock number
     */
    private function generateFlockNumber($speciesId, $houseId)
    {
        $species = Species::find($speciesId);
        $house = House::find($houseId);
        $year = Carbon::now()->format('Y');
        
        $count = Flock::where('species_id', $speciesId)
            ->whereYear('start_date', $year)
            ->count() + 1;
        
        return "{$year}-{$species->code}-{$house->house_code}-" . str_pad($count, 3, '0', STR_PAD_LEFT);
    }
    
    /**
     * Calculate and save performance metrics for closed flock
     */
    private function calculatePerformanceMetrics(Flock $flock)
    {
        // Calculate total feed cost
        $averageFeedCost = DB::table('feed_deliveries')->avg('cost_per_kg');
        $feedCost = $flock->feedIssuances()->sum('quantity_kg') * ($averageFeedCost ?? 0);
        
        // Get total expenses
        $totalExpenses = $flock->expenses()->sum('amount') + $feedCost;
        
        // Calculate total revenue
        $totalRevenue = $flock->total_revenue ?? 0;
        
        // Calculate net profit
        $netProfit = $totalRevenue - $totalExpenses;
        
        // Calculate ROI
        $roiPercentage = $totalExpenses > 0 ? ($netProfit / $totalExpenses) * 100 : 0;
        
        $performanceMetric = PerformanceMetric::create([
            'flock_id' => $flock->id,
            'mortality_rate' => $flock->mortality_rate,
            'feed_conversion_ratio' => $flock->feed_conversion_ratio,
            'average_daily_gain_kg' => $flock->average_daily_gain,
            'total_feed_consumed_kg' => $flock->total_feed_consumed,
            'total_weight_gained_kg' => $flock->total_weight_kg ?? 0,
            'total_revenue' => $totalRevenue,
            'total_cost' => $totalExpenses,
            'net_profit' => $netProfit,
            'roi_percentage' => $roiPercentage,
            'calculated_date' => now()
        ]);
        
        return $performanceMetric;
    }

    /**
     * Get flock details for AJAX modal
     */
    public function getFlockDetails($id)
    {
        try {
            $flock = Flock::with(['species', 'house'])->findOrFail($id);
            
            $summary = [
                'age_days' => $flock->age_in_days,
                'age_weeks' => $flock->age_in_weeks,
                'mortality_rate' => $flock->mortality_rate,
                'survival_rate' => 100 - $flock->mortality_rate,
                'current_count' => $flock->current_count,
                'total_feed' => $flock->total_feed_consumed,
                'avg_daily_gain' => $flock->average_daily_gain,
                'fcr' => $flock->feed_conversion_ratio,
            ];
            
            return response()->json([
                'success' => true,
                'flock' => [
                    'id' => $flock->id,
                    'flock_number' => $flock->flock_number,
                    'species_name' => $flock->species->name ?? 'N/A',
                    'species_code' => $flock->species->code ?? 'N/A',
                    'house_name' => $flock->house->name ?? 'N/A',
                    'house_code' => $flock->house->house_code ?? 'N/A',
                    'breed_variety' => $flock->breed_variety,
                    'start_date' => $flock->start_date->format('Y-m-d'),
                    'source' => $flock->source,
                    'initial_count' => $flock->initial_count,
                    'production_type' => $flock->production_type,
                    'is_breeding_stock' => $flock->is_breeding_stock,
                    'parity_number' => $flock->parity_number,
                    'status' => $flock->status,
                    'notes' => $flock->notes,
                ],
                'summary' => $summary
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Get flock edit data for AJAX modal
     */
    public function getFlockEditData($id)
    {
        try {
            $flock = Flock::findOrFail($id);
            $houses = House::where('status', 'active')->get(['id', 'name', 'capacity']);
            
            return response()->json([
                'success' => true,
                'flock' => [
                    'id' => $flock->id,
                    'breed_variety' => $flock->breed_variety,
                    'house_id' => $flock->house_id,
                    'production_type' => $flock->production_type,
                    'is_breeding_stock' => $flock->is_breeding_stock,
                    'notes' => $flock->notes,
                ],
                'houses' => $houses
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}