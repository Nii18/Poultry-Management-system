<?php
// app/Http/Controllers/OffspringRecordController.php

namespace App\Http\Controllers;

use App\Models\OffspringRecord;
use App\Models\BreedingRecord;
use App\Models\Flock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class OffspringRecordController extends Controller
{
    /**
     * Show the form for creating a new offspring record
     */
    public function create(Request $request)
    {
        $breedingRecordId = $request->get('breeding_record_id');
        $breedingRecord = null;
        
        if ($breedingRecordId) {
            $breedingRecord = BreedingRecord::with(['female.species'])->findOrFail($breedingRecordId);
        }
        
        $activeFlocks = Flock::where('status', 'active')->get();
        
        return view('offspring-records.create', compact('breedingRecord', 'activeFlocks'));
    }
    
    /**
     * Store a newly created offspring record
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'breeding_record_id' => 'required|exists:breeding_records,id',
            'new_flock_id' => 'nullable|exists:flocks,id',
            'count' => 'required|integer|min:1',
            'average_birth_weight_kg' => 'nullable|numeric|min:0',
            'ear_tag_prefix' => 'nullable|string|max:10',
            'ear_tag_start_number' => 'nullable|integer|min:1',
            'notes' => 'nullable|string'
        ]);
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        DB::beginTransaction();
        
        try {
            $offspring = OffspringRecord::create([
                'breeding_record_id' => $request->breeding_record_id,
                'new_flock_id' => $request->new_flock_id,
                'count' => $request->count,
                'average_birth_weight_kg' => $request->average_birth_weight_kg,
                'ear_tag_prefix' => $request->ear_tag_prefix,
                'ear_tag_start_number' => $request->ear_tag_start_number,
                'notes' => $request->notes
            ]);
            
            DB::commit();
            
            return redirect()->route('breeding-records.show', $request->breeding_record_id)
                ->with('success', 'Offspring record created successfully');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create offspring record: ' . $e->getMessage());
        }
    }
    
    /**
     * Display the specified offspring record
     */
    public function show($id)
    {
        $offspring = OffspringRecord::with(['breedingRecord.female', 'newFlock'])->findOrFail($id);
        
        return view('offspring-records.show', compact('offspring'));
    }
    
    /**
     * Show the form for editing the specified offspring record
     */
    public function edit($id)
    {
        $offspring = OffspringRecord::findOrFail($id);
        $activeFlocks = Flock::where('status', 'active')->get();
        
        return view('offspring-records.edit', compact('offspring', 'activeFlocks'));
    }
    
    /**
     * Update the specified offspring record
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'new_flock_id' => 'nullable|exists:flocks,id',
            'count' => 'required|integer|min:1',
            'average_birth_weight_kg' => 'nullable|numeric|min:0',
            'ear_tag_prefix' => 'nullable|string|max:10',
            'ear_tag_start_number' => 'nullable|integer|min:1',
            'notes' => 'nullable|string'
        ]);
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        $offspring = OffspringRecord::findOrFail($id);
        
        try {
            $offspring->update([
                'new_flock_id' => $request->new_flock_id,
                'count' => $request->count,
                'average_birth_weight_kg' => $request->average_birth_weight_kg,
                'ear_tag_prefix' => $request->ear_tag_prefix,
                'ear_tag_start_number' => $request->ear_tag_start_number,
                'notes' => $request->notes
            ]);
            
            return redirect()->route('offspring-records.show', $offspring->id)
                ->with('success', 'Offspring record updated successfully');
                
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update offspring record: ' . $e->getMessage());
        }
    }
    
    /**
     * Remove the specified offspring record
     */
    public function destroy($id)
    {
        $offspring = OffspringRecord::findOrFail($id);
        $breedingRecordId = $offspring->breeding_record_id;
        $offspring->delete();
        
        return redirect()->route('breeding-records.show', $breedingRecordId)
            ->with('success', 'Offspring record deleted successfully');
    }
}