<?php
// app/Http/Controllers/HouseController.php

namespace App\Http\Controllers;

use App\Models\House;
use App\Models\Species;
use App\Models\Flock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class HouseController extends Controller
{
    /**
     * Display a listing of houses
     */
    public function index(Request $request)
    {
        $speciesId = $request->get('species_id');
        $status = $request->get('status');
        
        $query = House::with('species');
        
        if ($speciesId) {
            $query->where('species_id', $speciesId);
        }
        
        if ($status) {
            $query->where('status', $status);
        }
        
        $houses = $query->orderBy('name')->paginate(20);
        
        $species = Species::where('is_active', true)->get();
        
        return view('houses.index', compact('houses', 'species', 'speciesId', 'status'));
    }
    
    /**
     * Show the form for creating a new house
     */
    public function create()
    {
        $species = Species::where('is_active', true)->get();
        
        return view('houses.create', compact('species'));
    }
    
    /**
     * Store a newly created house
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'house_code' => 'required|string|max:50|unique:houses',
            'species_id' => 'nullable|exists:species,id',
            'capacity' => 'nullable|integer|min:0',
            'length_m' => 'nullable|numeric|min:0',
            'width_m' => 'nullable|numeric|min:0',
            'height_m' => 'nullable|numeric|min:0',
            'feeders_count' => 'nullable|integer|min:0',
            'drinkers_count' => 'nullable|integer|min:0',
            'fans_count' => 'nullable|integer|min:0',
            'heaters_count' => 'nullable|integer|min:0',
            'status' => 'required|in:active,maintenance,cleaning,inactive',
            'notes' => 'nullable|string'
        ]);
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        try {
            $house = House::create([
                'name' => $request->name,
                'house_code' => strtoupper($request->house_code),
                'species_id' => $request->species_id,
                'capacity' => $request->capacity ?? 0,
                'length_m' => $request->length_m,
                'width_m' => $request->width_m,
                'height_m' => $request->height_m,
                'feeders_count' => $request->feeders_count ?? 0,
                'drinkers_count' => $request->drinkers_count ?? 0,
                'fans_count' => $request->fans_count ?? 0,
                'heaters_count' => $request->heaters_count ?? 0,
                'status' => $request->status,
                'notes' => $request->notes
            ]);
            
            return redirect()->route('houses.index')
    ->with('success', 'House created successfully');
                
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to create house: ' . $e->getMessage());
        }
    }
    
    /**
     * Display the specified house
     */
    public function show($id)
    {
        $house = House::with('species')->findOrFail($id);
        
        // Get current flock in this house
        $currentFlock = Flock::where('house_id', $id)
            ->where('status', 'active')
            ->with('species')
            ->first();
        
        // Get house statistics
        $totalFlocks = Flock::where('house_id', $id)->count();
        $completedFlocks = Flock::where('house_id', $id)->where('status', 'closed')->count();
        $totalAnimals = Flock::where('house_id', $id)->sum('current_count');
        
        $stats = [
            'total_flocks' => $totalFlocks,
            'completed_flocks' => $completedFlocks,
            'total_animals' => $totalAnimals,
            'occupancy_rate' => $currentFlock ? ($currentFlock->current_count / $house->capacity) * 100 : 0
        ];
        
        return view('houses.show', compact('house', 'currentFlock', 'stats'));
    }
    
    /**
     * Show the form for editing the specified house
     */
    public function edit($id)
    {
        $house = House::findOrFail($id);
        $species = Species::where('is_active', true)->get();
        
        return view('houses.edit', compact('house', 'species'));
    }
    
    /**
     * Update the specified house
     */
    public function update(Request $request, $id)
{
    $validator = Validator::make($request->all(), [
        'name'          => 'required|string|max:255',
        'house_code'    => 'required|string|max:50|unique:houses,house_code,' . $id,
        'species_id'    => 'nullable|exists:species,id',
        'capacity'      => 'nullable|integer|min:0',
        'length_m'      => 'nullable|numeric|min:0',
        'width_m'       => 'nullable|numeric|min:0',
        'height_m'      => 'nullable|numeric|min:0',
        'feeders_count' => 'nullable|integer|min:0',
        'drinkers_count'=> 'nullable|integer|min:0',
        'fans_count'    => 'nullable|integer|min:0',
        'heaters_count' => 'nullable|integer|min:0',
        'status'        => 'required|in:active,maintenance,cleaning,inactive',
        'notes'         => 'nullable|string'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => $validator->errors()->first()
        ], 422);
    }

    try {
        $house = House::findOrFail($id);
        $house->update([
            'name'           => $request->name,
            'house_code'     => strtoupper($request->house_code),
            'species_id'     => $request->species_id,
            'capacity'       => $request->capacity ?? 0,
            'length_m'       => $request->length_m,
            'width_m'        => $request->width_m,
            'height_m'       => $request->height_m,
            'feeders_count'  => $request->feeders_count ?? 0,
            'drinkers_count' => $request->drinkers_count ?? 0,
            'fans_count'     => $request->fans_count ?? 0,
            'heaters_count'  => $request->heaters_count ?? 0,
            'status'         => $request->status,
            'notes'          => $request->notes
        ]);

        return response()->json([
            'success' => true,
            'message' => 'House updated successfully'
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to update house: ' . $e->getMessage()
        ], 500);
    }
}
    
    /**
     * Remove the specified house
     */
    public function destroy($id)
    {
        $house = House::findOrFail($id);
        
        // Check if house has active flocks
        $activeFlocks = Flock::where('house_id', $id)->where('status', 'active')->count();
        
        if ($activeFlocks > 0) {
            return back()->with('error', 'Cannot delete house with active flocks.');
        }
        
        $house->delete();
        
        return redirect()->route('houses.index')
            ->with('success', 'House deleted successfully');
    }
    
  /**
 * Get house occupancy report
 */
public function occupancyReport()
{
    $houses = House::with('species')
        ->where('status', 'active')
        ->get();
    
    $report = [];
    foreach ($houses as $house) {
        $currentFlock = Flock::where('house_id', $house->id)
            ->where('status', 'active')
            ->first();
        
        $report[] = [
            'id' => $house->id,  // ADD THIS LINE - include the house ID
            'house' => $house->name,
            'species' => $house->species ? $house->species->name : 'Not Assigned',
            'capacity' => $house->capacity,
            'current_animals' => $currentFlock ? $currentFlock->current_count : 0,
            'occupancy_rate' => $currentFlock ? ($currentFlock->current_count / max($house->capacity, 1)) * 100 : 0,
            'flock_number' => $currentFlock ? $currentFlock->flock_number : 'Empty',
            'status' => $currentFlock ? 'Occupied' : 'Vacant'
        ];
    }
    
    return view('houses.occupancy-report', compact('report'));
}

/**
 * Get house details for AJAX modal
 */
public function getHouseDetails($id)
{
    try {
        $house = House::with('species')->findOrFail($id);
        
        // Get current flock in this house
        $currentFlock = Flock::where('house_id', $id)
            ->where('status', 'active')
            ->with('species')
            ->first();
        
        $currentFlockData = null;
        if ($currentFlock) {
            $currentFlockData = [
                'flock_number' => $currentFlock->flock_number,
                'species_name' => $currentFlock->species->name ?? 'N/A',
                'breed_variety' => $currentFlock->breed_variety,
                'age_days' => $currentFlock->age_in_days,
                'current_count' => $currentFlock->current_count,
                'mortality_rate' => $currentFlock->mortality_rate,
            ];
        }
        
        $stats = [
            'total_flocks' => Flock::where('house_id', $id)->count(),
            'completed_flocks' => Flock::where('house_id', $id)->where('status', 'closed')->count(),
            'total_animals' => Flock::where('house_id', $id)->sum('current_count'),
            'occupancy_rate' => $currentFlock ? number_format(($currentFlock->current_count / max($house->capacity, 1)) * 100, 1) : 0,
        ];
        
        return response()->json([
            'success' => true,
            'house' => [
                'id' => $house->id,
                'name' => $house->name,
                'house_code' => $house->house_code,
                'species_name' => $house->species->name ?? null,
                'capacity' => $house->capacity,
                'length_m' => $house->length_m,
                'width_m' => $house->width_m,
                'height_m' => $house->height_m,
                'feeders_count' => $house->feeders_count,
                'drinkers_count' => $house->drinkers_count,
                'fans_count' => $house->fans_count,
                'heaters_count' => $house->heaters_count,
                'status' => $house->status,
                'notes' => $house->notes,
            ],
            'stats' => $stats,
            'currentFlock' => $currentFlockData
        ]);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()]);
    }
}

/**
 * Get house edit data for AJAX modal
 */
public function getHouseEditData($id)
{
    try {
        $house = House::findOrFail($id);
        $species = Species::where('is_active', true)->get(['id', 'name', 'code']);
        
        return response()->json([
            'success' => true,
            'house' => [
                'id' => $house->id,
                'name' => $house->name,
                'house_code' => $house->house_code,
                'species_id' => $house->species_id,
                'capacity' => $house->capacity,
                'length_m' => $house->length_m,
                'width_m' => $house->width_m,
                'height_m' => $house->height_m,
                'feeders_count' => $house->feeders_count,
                'drinkers_count' => $house->drinkers_count,
                'fans_count' => $house->fans_count,
                'heaters_count' => $house->heaters_count,
                'status' => $house->status,
                'notes' => $house->notes,
            ],
            'species' => $species
        ]);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()]);
    }
}

}