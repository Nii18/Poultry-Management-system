<?php
// app/Http/Controllers/BreedingRecordController.php

namespace App\Http\Controllers;

use App\Models\BreedingRecord;
use App\Models\Flock;
use App\Models\OffspringRecord;
use App\Helpers\AuditHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BreedingRecordController extends Controller
{
    /**
     * Display a listing of breeding records
     */
    public function index(Request $request)
    {
        $flockId = $request->get('flock_id');
        $status = $request->get('status', 'all');
        
        $query = BreedingRecord::with(['female.species', 'male', 'recorder', 'offspringRecords']);
        
        if ($flockId) {
            $query->where('flock_id', $flockId);
        }
        
        if ($status === 'pending') {
            $query->where('expected_delivery_date', '>', now())
                  ->whereNull('actual_delivery_date');
        } elseif ($status === 'successful') {
            $query->where('is_successful', true);
        } elseif ($status === 'unsuccessful') {
            $query->where('is_successful', false);
        }
        
        $records = $query->orderBy('breeding_date', 'desc')
            ->paginate(20);
        
        $flocks = Flock::where('is_breeding_stock', true)->get();
        
        return view('breeding-records.index', compact('records', 'flocks', 'flockId', 'status'));
    }
    
    /**
     * Show the form for creating a new breeding record
     */
    public function create(Request $request)
    {
        $flockId = $request->get('flock_id');
        $flock = null;
        
        if ($flockId) {
            $flock = Flock::with('species')->findOrFail($flockId);
        }
        
        $femaleFlocks = Flock::where('is_breeding_stock', true)
            ->where('species_id', $flock ? $flock->species_id : null)
            ->where('status', 'active')
            ->get();
        
        $maleFlocks = Flock::where('is_breeding_stock', true)
            ->where('species_id', $flock ? $flock->species_id : null)
            ->where('status', 'active')
            ->get();
        
        return view('breeding-records.create', compact('femaleFlocks', 'maleFlocks', 'flock'));
    }
    
    /**
     * Store a newly created breeding record
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'flock_id' => 'required|exists:flocks,id',
            'mate_id' => 'nullable|exists:flocks,id',
            'breeding_date' => 'required|date|before_or_equal:today',
            'expected_delivery_date' => 'required|date|after:breeding_date',
            'breeding_method' => 'required|in:natural,artificial_insemination',
            'notes' => 'nullable|string'
        ]);
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        DB::beginTransaction();
        
        try {
            $record = BreedingRecord::create([
                'flock_id' => $request->flock_id,
                'mate_id' => $request->mate_id,
                'breeding_date' => $request->breeding_date,
                'expected_delivery_date' => $request->expected_delivery_date,
                'breeding_method' => $request->breeding_method,
                'is_successful' => false,
                'notes' => $request->notes,
                'recorded_by' => auth()->id()
            ]);
            
            // Log the creation
            AuditHelper::log(
                'create',
                "Created breeding record for flock #{$record->female->flock_number} with " . ($record->male ? "flock #{$record->male->flock_number}" : "AI"),
                'breeding_record',
                $record->id,
                null,
                $record->toArray()
            );
            
            // Update flock breeding information
            $flock = Flock::find($request->flock_id);
            $flock->update([
                'last_breeding_date' => $request->breeding_date,
                'expected_delivery_date' => $request->expected_delivery_date
            ]);
            
            DB::commit();
            
            return redirect()->route('breeding-records.show', $record->id)
                ->with('success', 'Breeding record created successfully');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create breeding record: ' . $e->getMessage());
        }
    }
    
    /**
     * Display the specified breeding record
     */
    public function show($id)
    {
        $record = BreedingRecord::with(['female.species', 'male', 'recorder', 'offspringRecords.newFlock'])
            ->findOrFail($id);
        
        $conceptionRate = $record->conception_rate;
        $liveBirthRate = $record->live_birth_rate;
        $weaningRate = $record->weaning_rate;
        
        return view('breeding-records.show', compact('record', 'conceptionRate', 'liveBirthRate', 'weaningRate'));
    }
    
    /**
     * Show the form for editing the specified breeding record
     */
    public function edit($id)
    {
        $record = BreedingRecord::findOrFail($id);
        
        $femaleFlocks = Flock::where('is_breeding_stock', true)
            ->where('status', 'active')
            ->get();
        
        $maleFlocks = Flock::where('is_breeding_stock', true)
            ->where('status', 'active')
            ->get();
        
        return view('breeding-records.edit', compact('record', 'femaleFlocks', 'maleFlocks'));
    }
    
    /**
     * Update the specified breeding record
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'flock_id' => 'required|exists:flocks,id',
            'mate_id' => 'nullable|exists:flocks,id',
            'breeding_date' => 'required|date',
            'expected_delivery_date' => 'required|date|after:breeding_date',
            'actual_delivery_date' => 'nullable|date|after_or_equal:breeding_date',
            'is_successful' => 'nullable|boolean',
            'offspring_count' => 'nullable|integer|min:0',
            'stillborn_count' => 'nullable|integer|min:0',
            'weaned_count' => 'nullable|integer|min:0',
            'breeding_method' => 'required|in:natural,artificial_insemination',
            'notes' => 'nullable|string'
        ]);
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        $record = BreedingRecord::findOrFail($id);
        $oldValues = $record->toArray();
        
        DB::beginTransaction();
        
        try {
            $record->update([
                'flock_id' => $request->flock_id,
                'mate_id' => $request->mate_id,
                'breeding_date' => $request->breeding_date,
                'expected_delivery_date' => $request->expected_delivery_date,
                'actual_delivery_date' => $request->actual_delivery_date,
                'is_successful' => $request->is_successful ?? false,
                'offspring_count' => $request->offspring_count,
                'stillborn_count' => $request->stillborn_count ?? 0,
                'weaned_count' => $request->weaned_count,
                'breeding_method' => $request->breeding_method,
                'notes' => $request->notes
            ]);
            
            // Log the update
            AuditHelper::log(
                'update',
                "Updated breeding record for flock #{$record->female->flock_number}",
                'breeding_record',
                $record->id,
                $oldValues,
                $record->toArray()
            );
            
            DB::commit();
            
            return redirect()->route('breeding-records.show', $record->id)
                ->with('success', 'Breeding record updated successfully');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update breeding record: ' . $e->getMessage());
        }
    }
    
    /**
     * Remove the specified breeding record
     */
    public function destroy($id)
    {
        $record = BreedingRecord::findOrFail($id);
        
        // Log the deletion
        AuditHelper::log(
            'delete',
            "Deleted breeding record for flock #{$record->female->flock_number}",
            'breeding_record',
            $record->id,
            $record->toArray(),
            null
        );
        
        $record->delete();
        
        return redirect()->route('breeding-records.index')
            ->with('success', 'Breeding record deleted successfully');
    }
    
    /**
     * Record delivery and offspring
     */
    public function recordDelivery(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'actual_delivery_date' => 'required|date|before_or_equal:today',
            'offspring_count' => 'required|integer|min:0',
            'stillborn_count' => 'required|integer|min:0',
            'weaned_count' => 'nullable|integer|min:0',
            'is_successful' => 'required|boolean'
        ]);
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        $record = BreedingRecord::findOrFail($id);
        $oldValues = $record->toArray();
        
        DB::beginTransaction();
        
        try {
            $record->update([
                'actual_delivery_date' => $request->actual_delivery_date,
                'offspring_count' => $request->offspring_count,
                'stillborn_count' => $request->stillborn_count,
                'weaned_count' => $request->weaned_count,
                'is_successful' => $request->is_successful
            ]);
            
            // Log the delivery
            AuditHelper::log(
                'delivery',
                "Recorded delivery for flock #{$record->female->flock_number} with {$record->offspring_count} offspring",
                'breeding_record',
                $record->id,
                $oldValues,
                $record->toArray()
            );
            
            DB::commit();
            
            return redirect()->route('breeding-records.show', $record->id)
                ->with('success', 'Delivery recorded successfully');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to record delivery: ' . $e->getMessage());
        }
    }
    
    /**
     * Display pending breedings
     */
    public function pending()
    {
        $pendingBreedings = BreedingRecord::with(['female.species', 'male'])
            ->where('expected_delivery_date', '>', now())
            ->whereNull('actual_delivery_date')
            ->orderBy('expected_delivery_date')
            ->get();
        
        return view('breeding-records.pending', compact('pendingBreedings'));
    }

    /**
     * Get breeding record details as JSON for modal
     */
    public function getDetailsJson($id)
    {
        try {
            if (!auth()->check()) {
                return response()->json(['success' => false, 'message' => 'Please login'], 401);
            }
            
            $record = BreedingRecord::with(['female', 'male', 'offspringRecords.newFlock'])->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'record' => [
                    'id' => $record->id,
                    'female_flock_number' => $record->female->flock_number ?? 'N/A',
                    'female_breed' => $record->female->breed_variety ?? 'N/A',
                    'male_flock_number' => $record->male->flock_number ?? null,
                    'breeding_date' => $record->breeding_date->format('d M Y'),
                    'expected_delivery_date' => $record->expected_delivery_date->format('d M Y'),
                    'actual_delivery_date' => $record->actual_delivery_date ? $record->actual_delivery_date->format('d M Y') : null,
                    'breeding_method' => ucfirst(str_replace('_', ' ', $record->breeding_method)),
                    'is_successful' => $record->is_successful,
                    'offspring_count' => $record->offspring_count,
                    'stillborn_count' => $record->stillborn_count,
                    'weaned_count' => $record->weaned_count,
                    'notes' => $record->notes,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Record delivery via AJAX
     */
    public function recordDeliveryAjax(Request $request, $id)
    {
        try {
            if (!auth()->check()) {
                return response()->json(['success' => false, 'message' => 'Please login'], 401);
            }
            
            $validator = Validator::make($request->all(), [
                'actual_delivery_date' => 'required|date|before_or_equal:today',
                'offspring_count' => 'required|integer|min:0',
                'stillborn_count' => 'required|integer|min:0',
                'weaned_count' => 'nullable|integer|min:0',
                'is_successful' => 'required|boolean'
            ]);
            
            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
            }
            
            $record = BreedingRecord::findOrFail($id);
            $oldValues = $record->toArray();
            
            DB::beginTransaction();
            
            try {
                $record->update([
                    'actual_delivery_date' => $request->actual_delivery_date,
                    'offspring_count' => $request->offspring_count,
                    'stillborn_count' => $request->stillborn_count,
                    'weaned_count' => $request->weaned_count ?? 0,
                    'is_successful' => $request->is_successful
                ]);
                
                AuditHelper::log(
                    'delivery',
                    "Recorded delivery for flock #{$record->female->flock_number} with {$record->offspring_count} offspring",
                    'breeding_record',
                    $record->id,
                    $oldValues,
                    $record->toArray()
                );
                
                DB::commit();
                
                return response()->json(['success' => true, 'message' => 'Delivery recorded successfully']);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get create form data for AJAX modal
     */
    public function getCreateForm()
{
    $femaleFlocks = Flock::with('species')
        ->where('status', 'active')
        ->where('is_breeding_stock', true)
        ->get()
        ->map(fn($f) => [
            'id' => $f->id,
            'flock_number' => $f->flock_number,
            'breed_variety' => $f->breed_variety,
            'species_name' => $f->species->name ?? 'N/A',
            'gestation_days' => $f->species->gestation_days ?? 0,
        ]);

    $maleFlocks = Flock::with('species')
        ->where('status', 'active')
        ->where('is_breeding_stock', true)
        ->get()
        ->map(fn($f) => [
            'id' => $f->id,
            'flock_number' => $f->flock_number,
            'breed_variety' => $f->breed_variety,
        ]);

    return response()->json([
        'success' => true,
        'female_flocks' => $femaleFlocks,
        'male_flocks' => $maleFlocks,
    ]);
}

    /**
     * Store breeding record via AJAX
     */
    public function storeBreedingRecord(Request $request)
    {
        try {
            if (!auth()->check()) {
                return response()->json(['success' => false, 'message' => 'Please login'], 401);
            }
            
            $validator = Validator::make($request->all(), [
                'flock_id' => 'required|exists:flocks,id',
                'mate_id' => 'nullable|exists:flocks,id',
                'breeding_date' => 'required|date|before_or_equal:today',
                'expected_delivery_date' => 'required|date|after:breeding_date',
                'breeding_method' => 'required|in:natural,artificial_insemination',
                'notes' => 'nullable|string'
            ]);
            
            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
            }
            
            DB::beginTransaction();
            
            try {
                $record = BreedingRecord::create([
                    'flock_id' => $request->flock_id,
                    'mate_id' => $request->mate_id,
                    'breeding_date' => $request->breeding_date,
                    'expected_delivery_date' => $request->expected_delivery_date,
                    'breeding_method' => $request->breeding_method,
                    'is_successful' => false,
                    'notes' => $request->notes,
                    'recorded_by' => auth()->id()
                ]);
                
                AuditHelper::log(
                    'create',
                    "Created breeding record for flock #{$record->female->flock_number} with " . ($record->male ? "flock #{$record->male->flock_number}" : "AI"),
                    'breeding_record',
                    $record->id,
                    null,
                    $record->toArray()
                );
                
                // Update flock breeding information
                $flock = Flock::find($request->flock_id);
                if ($flock) {
                    $flock->update([
                        'last_breeding_date' => $request->breeding_date,
                        'expected_delivery_date' => $request->expected_delivery_date
                    ]);
                }
                
                DB::commit();
                
                return response()->json(['success' => true, 'message' => 'Breeding record created successfully']);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}