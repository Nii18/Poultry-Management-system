<?php
// app/Http/Controllers/TreatmentController.php

namespace App\Http\Controllers;

use App\Models\Treatment;
use App\Models\Flock;
use App\Models\Notification;
use App\Helpers\AuditHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class TreatmentController extends Controller
{
    /**
     * Display a listing of treatments
     */
    public function index(Request $request)
    {
        $flockId = $request->get('flock_id');
        $status = $request->get('status', 'active');
        
        $query = Treatment::with(['flock.species', 'prescriber']);
        
        // Apply flock filter
        if ($flockId) {
            $query->where('flock_id', $flockId);
        }
        
        // Apply status filter
        if ($status === 'active') {
            $query->where('withdrawal_end_date', '>', now())
                  ->orWhereNull('withdrawal_end_date');
        } elseif ($status === 'withdrawal') {
            $query->where('withdrawal_end_date', '>', now())
                  ->whereNotNull('withdrawal_end_date');
        } elseif ($status === 'completed') {
            $query->where('end_date', '<', now());
        }
        
        $treatments = $query->orderBy('start_date', 'desc')
            ->paginate(20);
        
        $flocks = Flock::where('status', 'active')->get();
        
        return view('treatments.index', compact('treatments', 'flocks', 'flockId', 'status'));
    }
    
    /**
     * Show the form for creating a new treatment
     */
    public function create(Request $request)
    {
        $flockId = $request->get('flock_id');
        $flock = null;
        
        if ($flockId) {
            $flock = Flock::with('species')->findOrFail($flockId);
        }
        
        $activeFlocks = Flock::where('status', 'active')
            ->with('species')
            ->orderBy('start_date', 'desc')
            ->get();
        
        return view('treatments.create', compact('activeFlocks', 'flock'));
    }
    
    /**
     * Store a newly created treatment
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'flock_id' => 'required|exists:flocks,id',
            'diagnosis' => 'required|string|max:255',
            'product_name' => 'required|string|max:255',
            'active_ingredient' => 'nullable|string|max:255',
            'dosage' => 'required|string',
            'administration_route' => 'required|in:water,feed,injection,topical',
            'start_date' => 'required|date|before_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'withdrawal_days' => 'nullable|integer|min:0',
            'batch_number' => 'nullable|string',
            'animals_treated' => 'nullable|integer|min:0',
            'cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string'
        ]);
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        DB::beginTransaction();
        
        try {
            // Calculate withdrawal end date
            $withdrawalEndDate = null;
            if ($request->withdrawal_days) {
                $withdrawalEndDate = Carbon::parse($request->end_date)
                    ->addDays($request->withdrawal_days);
            }
            
            $treatment = Treatment::create([
                'flock_id' => $request->flock_id,
                'diagnosis' => $request->diagnosis,
                'product_name' => $request->product_name,
                'active_ingredient' => $request->active_ingredient,
                'dosage' => $request->dosage,
                'administration_route' => $request->administration_route,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'withdrawal_days' => $request->withdrawal_days,
                'withdrawal_end_date' => $withdrawalEndDate,
                'batch_number' => $request->batch_number,
                'animals_treated' => $request->animals_treated,
                'cost' => $request->cost,
                'notes' => $request->notes,
                'prescribed_by' => auth()->id()
            ]);
            
            // Log the creation
            AuditHelper::log(
                'create',
                "Recorded treatment '{$treatment->product_name}' for flock #{$treatment->flock->flock_number}",
                'treatment',
                $treatment->id,
                null,
                $treatment->toArray()
            );
            
            // Create notification for withdrawal period
            if ($withdrawalEndDate && $withdrawalEndDate > now()) {
                $flock = Flock::find($request->flock_id);
                Notification::create([
                    'user_id' => auth()->id(),
                    'flock_id' => $flock->id,
                    'type' => 'withdrawal_period',
                    'title' => 'Withdrawal Period Started',
                    'message' => "Withdrawal period for {$request->product_name} ends on {$withdrawalEndDate->format('Y-m-d')}",
                    'severity' => 'info',
                    'data' => json_encode([
                        'treatment_id' => $treatment->id,
                        'withdrawal_end_date' => $withdrawalEndDate
                    ])
                ]);
            }
            
            DB::commit();
            
            return redirect()->route('treatments.show', $treatment)
                ->with('success', 'Treatment record created successfully');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create treatment record: ' . $e->getMessage());
        }
    }
    
    /**
     * Display the specified treatment
     */
    public function show($id)
    {
        $treatment = Treatment::with(['flock.species', 'prescriber'])->findOrFail($id);
        
        $daysUntilWithdrawalEnd = $treatment->days_until_withdrawal_end;
        $isWithdrawalActive = $treatment->is_withdrawal_active;
        
        return view('treatments.show', compact('treatment', 'daysUntilWithdrawalEnd', 'isWithdrawalActive'));
    }
    
    /**
     * Show the form for editing the specified treatment
     */
    public function edit($id)
    {
        $treatment = Treatment::findOrFail($id);
        $activeFlocks = Flock::where('status', 'active')->get();
        
        return view('treatments.edit', compact('treatment', 'activeFlocks'));
    }
    
    /**
     * Update the specified treatment
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'flock_id' => 'required|exists:flocks,id',
            'diagnosis' => 'required|string|max:255',
            'product_name' => 'required|string|max:255',
            'active_ingredient' => 'nullable|string|max:255',
            'dosage' => 'required|string',
            'administration_route' => 'required|in:water,feed,injection,topical',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'withdrawal_days' => 'nullable|integer|min:0',
            'batch_number' => 'nullable|string',
            'animals_treated' => 'nullable|integer|min:0',
            'cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string'
        ]);
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        $treatment = Treatment::findOrFail($id);
        $oldValues = $treatment->toArray();
        
        DB::beginTransaction();
        
        try {
            // Recalculate withdrawal end date
            $withdrawalEndDate = null;
            if ($request->withdrawal_days) {
                $withdrawalEndDate = Carbon::parse($request->end_date)
                    ->addDays($request->withdrawal_days);
            }
            
            $treatment->update([
                'flock_id' => $request->flock_id,
                'diagnosis' => $request->diagnosis,
                'product_name' => $request->product_name,
                'active_ingredient' => $request->active_ingredient,
                'dosage' => $request->dosage,
                'administration_route' => $request->administration_route,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'withdrawal_days' => $request->withdrawal_days,
                'withdrawal_end_date' => $withdrawalEndDate,
                'batch_number' => $request->batch_number,
                'animals_treated' => $request->animals_treated,
                'cost' => $request->cost,
                'notes' => $request->notes
            ]);
            
            // Log the update
            AuditHelper::log(
                'update',
                "Updated treatment record for flock #{$treatment->flock->flock_number}",
                'treatment',
                $treatment->id,
                $oldValues,
                $treatment->toArray()
            );
            
            DB::commit();
            
            return redirect()->route('treatments.show', $treatment)
                ->with('success', 'Treatment record updated successfully');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update treatment record: ' . $e->getMessage());
        }
    }
    
    /**
     * Remove the specified treatment
     */
    public function destroy($id)
    {
        $treatment = Treatment::findOrFail($id);
        
        // Log the deletion
        AuditHelper::log(
            'delete',
            "Deleted treatment record '{$treatment->product_name}' for flock #{$treatment->flock->flock_number}",
            'treatment',
            $treatment->id,
            $treatment->toArray(),
            null
        );
        
        $treatment->delete();
        
        return redirect()->route('treatments.index')
            ->with('success', 'Treatment record deleted successfully');
    }
    
    /**
     * Display withdrawal alerts
     */
    public function withdrawalAlerts()
    {
        $expiringWithdrawals = Treatment::with(['flock.species'])
            ->where('withdrawal_end_date', '>', now())
            ->where('withdrawal_end_date', '<=', now()->addDays(3))
            ->orderBy('withdrawal_end_date')
            ->get();
        
        $activeWithdrawals = Treatment::with(['flock.species'])
            ->where('withdrawal_end_date', '>', now())
            ->where('withdrawal_end_date', '>', now()->addDays(3))
            ->orderBy('withdrawal_end_date')
            ->get();
        
        return view('treatments.withdrawal-alerts', compact('expiringWithdrawals', 'activeWithdrawals'));
    }

    /**
     * Get create form data for AJAX modal
     */
    public function getCreateForm()
    {
        try {
            $flocks = Flock::where('status', 'active')
                ->with('species')
                ->get(['id', 'flock_number', 'breed_variety']);
            
            $flocksData = $flocks->map(function($flock) {
                return [
                    'id' => $flock->id,
                    'flock_number' => $flock->flock_number,
                    'breed_variety' => $flock->breed_variety,
                ];
            });
            
            return response()->json([
                'success' => true,
                'flocks' => $flocksData
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Get treatment details for AJAX modal
     */
    public function getTreatmentDetails($id)
    {
        try {
            $treatment = Treatment::with(['flock.species', 'prescriber'])->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'treatment' => [
                    'id' => $treatment->id,
                    'flock_number' => $treatment->flock->flock_number ?? 'N/A',
                    'breed_variety' => $treatment->flock->breed_variety ?? 'N/A',
                    'species_name' => $treatment->flock->species->name ?? 'N/A',
                    'diagnosis' => $treatment->diagnosis,
                    'product_name' => $treatment->product_name,
                    'active_ingredient' => $treatment->active_ingredient,
                    'dosage' => $treatment->dosage,
                    'administration_route' => $treatment->administration_route,
                    'start_date' => $treatment->start_date->format('Y-m-d'),
                    'end_date' => $treatment->end_date->format('Y-m-d'),
                    'withdrawal_days' => $treatment->withdrawal_days,
                    'withdrawal_end_date' => $treatment->withdrawal_end_date ? $treatment->withdrawal_end_date->format('Y-m-d') : null,
                    'batch_number' => $treatment->batch_number,
                    'animals_treated' => $treatment->animals_treated,
                    'cost' => (float) $treatment->cost,
                    'notes' => $treatment->notes,
                    'prescribed_by' => $treatment->prescriber->name ?? 'N/A',
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Get treatment edit data for AJAX modal
     */
    public function getTreatmentEditData($id)
    {
        try {
            $treatment = Treatment::findOrFail($id);
            $flocks = Flock::where('status', 'active')->get(['id', 'flock_number', 'breed_variety']);
            
            $flocksData = $flocks->map(function($flock) {
                return [
                    'id' => $flock->id,
                    'flock_number' => $flock->flock_number,
                    'breed_variety' => $flock->breed_variety,
                ];
            });
            
            return response()->json([
                'success' => true,
                'treatment' => [
                    'id' => $treatment->id,
                    'flock_id' => $treatment->flock_id,
                    'diagnosis' => $treatment->diagnosis,
                    'product_name' => $treatment->product_name,
                    'active_ingredient' => $treatment->active_ingredient,
                    'dosage' => $treatment->dosage,
                    'administration_route' => $treatment->administration_route,
                    'start_date' => $treatment->start_date->format('Y-m-d'),
                    'end_date' => $treatment->end_date->format('Y-m-d'),
                    'withdrawal_days' => $treatment->withdrawal_days,
                    'batch_number' => $treatment->batch_number,
                    'animals_treated' => $treatment->animals_treated,
                    'cost' => (float) $treatment->cost,
                    'notes' => $treatment->notes,
                ],
                'flocks' => $flocksData
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Update the specified treatment via AJAX
     */
    public function updateTreatment(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'flock_id' => 'required|exists:flocks,id',
            'diagnosis' => 'required|string|max:255',
            'product_name' => 'required|string|max:255',
            'dosage' => 'required|string',
            'administration_route' => 'required|in:water,feed,injection,topical',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'withdrawal_days' => 'nullable|integer|min:0',
            'notes' => 'nullable|string'
        ]);
        
        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }
        
        $treatment = Treatment::findOrFail($id);
        $oldValues = $treatment->toArray();
        
        DB::beginTransaction();
        
        try {
            $withdrawalEndDate = null;
            if ($request->withdrawal_days) {
                $withdrawalEndDate = Carbon::parse($request->end_date)->addDays($request->withdrawal_days);
            }
            
            $treatment->update([
                'flock_id' => $request->flock_id,
                'diagnosis' => $request->diagnosis,
                'product_name' => $request->product_name,
                'active_ingredient' => $request->active_ingredient,
                'dosage' => $request->dosage,
                'administration_route' => $request->administration_route,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'withdrawal_days' => $request->withdrawal_days,
                'withdrawal_end_date' => $withdrawalEndDate,
                'batch_number' => $request->batch_number,
                'animals_treated' => $request->animals_treated,
                'cost' => $request->cost,
                'notes' => $request->notes
            ]);
            
            AuditHelper::log(
                'update',
                "Updated treatment record for flock #{$treatment->flock->flock_number}",
                'treatment',
                $treatment->id,
                $oldValues,
                $treatment->toArray()
            );
            
            DB::commit();
            
            return response()->json(['success' => true, 'message' => 'Treatment updated successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Store a newly created treatment via AJAX
     */
    public function storeTreatment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'flock_id' => 'required|exists:flocks,id',
            'diagnosis' => 'required|string|max:255',
            'product_name' => 'required|string|max:255',
            'dosage' => 'required|string',
            'administration_route' => 'required|in:water,feed,injection,topical',
            'start_date' => 'required|date|before_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'withdrawal_days' => 'nullable|integer|min:0',
            'notes' => 'nullable|string'
        ]);
        
        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }
        
        DB::beginTransaction();
        
        try {
            $withdrawalEndDate = null;
            if ($request->withdrawal_days) {
                $withdrawalEndDate = Carbon::parse($request->end_date)->addDays($request->withdrawal_days);
            }
            
            $treatment = Treatment::create([
                'flock_id' => $request->flock_id,
                'diagnosis' => $request->diagnosis,
                'product_name' => $request->product_name,
                'active_ingredient' => $request->active_ingredient,
                'dosage' => $request->dosage,
                'administration_route' => $request->administration_route,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'withdrawal_days' => $request->withdrawal_days,
                'withdrawal_end_date' => $withdrawalEndDate,
                'batch_number' => $request->batch_number,
                'animals_treated' => $request->animals_treated,
                'cost' => $request->cost,
                'notes' => $request->notes,
                'prescribed_by' => auth()->id()
            ]);
            
            AuditHelper::log(
                'create',
                "Recorded treatment '{$treatment->product_name}' for flock #{$treatment->flock->flock_number}",
                'treatment',
                $treatment->id,
                null,
                $treatment->toArray()
            );
            
            DB::commit();
            
            return response()->json(['success' => true, 'message' => 'Treatment created successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}