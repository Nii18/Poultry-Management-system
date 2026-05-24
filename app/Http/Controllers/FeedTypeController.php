<?php
// app/Http/Controllers/FeedTypeController.php

namespace App\Http\Controllers;

use App\Models\FeedType;
use App\Models\Species;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FeedTypeController extends Controller
{
    /**
     * Display a listing of feed types
     */
    public function index(Request $request)
    {
        $speciesId = $request->get('species_id');
        
        $query = FeedType::with('species');
        
        if ($speciesId) {
            $query->where('species_id', $speciesId);
        }
        
        $feedTypes = $query->orderBy('name')->paginate(20);
        
        $species = Species::where('is_active', true)->get();
        
        return view('feed-types.index', compact('feedTypes', 'species', 'speciesId'));
    }
    
    /**
     * Show the form for creating a new feed type
     */
    public function create()
    {
        $species = Species::where('is_active', true)->get();
        
        return view('feed-types.index', compact('species'));
    }
    
    /**
     * Store a newly created feed type
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'species_id' => 'required|exists:species,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:feed_types',
            'category' => 'required|in:starter,grower,finisher,layer,breeder,maintenance',
            'protein_percentage' => 'nullable|numeric|min:0|max:100',
            'energy_mj_kg' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean'
        ]);
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        try {
            $feedType = FeedType::create([
                'species_id' => $request->species_id,
                'name' => $request->name,
                'code' => strtoupper($request->code),
                'category' => $request->category,
                'protein_percentage' => $request->protein_percentage,
                'energy_mj_kg' => $request->energy_mj_kg,
                'description' => $request->description,
                'is_active' => $request->is_active ?? true
            ]);
            
            return redirect()->route('feed-types.index')
            ->with('success', 'Feed type created successfully');
                
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to create feed type: ' . $e->getMessage());
        }
    }
    
    /**
     * Display the specified feed type
     */
    public function show($id)
    {
        $feedType = FeedType::with('species')->findOrFail($id);
        
        // Get usage statistics
        $totalDeliveries = $feedType->feedDeliveries()->count();
        $totalQuantity = $feedType->feedDeliveries()->sum('quantity_kg');
        $currentStock = $feedType->feedDeliveries()->sum('remaining_quantity_kg');
        
        $stats = [
            'total_deliveries' => $totalDeliveries,
            'total_quantity' => $totalQuantity,
            'current_stock' => $currentStock,
            'avg_cost_per_kg' => $feedType->feedDeliveries()->avg('cost_per_kg')
        ];
        
        return view('feed-types.show', compact('feedType', 'stats'));
    }
    
    /**
     * Show the form for editing the specified feed type
     */
    public function edit($id)
    {
        $feedType = FeedType::findOrFail($id);
        $species = Species::where('is_active', true)->get();
        
        return view('feed-types.edit', compact('feedType', 'species'));
    }
    
    /**
     * Update the specified feed type
     */
    public function update(Request $request, $id)
{
    $validator = Validator::make($request->all(), [
        'species_id'         => 'required|exists:species,id',
        'name'               => 'required|string|max:255',
        'code'               => 'required|string|max:50|unique:feed_types,code,' . $id,
        'category'           => 'required|in:starter,grower,finisher,layer,breeder,maintenance',
        'protein_percentage' => 'nullable|numeric|min:0|max:100',
        'energy_mj_kg'       => 'nullable|numeric|min:0',
        'description'        => 'nullable|string',
        'is_active'          => 'nullable|boolean'
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

    try {
        $feedType = FeedType::findOrFail($id);
        $feedType->update([
            'species_id'         => $request->species_id,
            'name'               => $request->name,
            'code'               => strtoupper($request->code),
            'category'           => $request->category,
            'protein_percentage' => $request->protein_percentage,
            'energy_mj_kg'       => $request->energy_mj_kg,
            'description'        => $request->description,
            'is_active' => $request->has('is_active') ? (bool)$request->is_active : false
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Feed type updated successfully']);
        }
        return redirect()->route('feed-types.index', $feedType->id)->with('success', 'Feed type updated successfully');

    } catch (\Exception $e) {
        if ($request->expectsJson()) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
        return back()->with('error', 'Failed to update feed type: ' . $e->getMessage());
    }
}
    
    /**
     * Remove the specified feed type
     */
    public function destroy($id)
    {
        $feedType = FeedType::findOrFail($id);
        
        // Check if feed type has deliveries
        $deliveryCount = $feedType->feedDeliveries()->count();
        
        if ($deliveryCount > 0) {
            return back()->with('error', 'Cannot delete feed type with existing deliveries. Deactivate it instead.');
        }
        
        $feedType->delete();
        
        return redirect()->route('feed-types.index')
            ->with('success', 'Feed type deleted successfully');
    }
    
    /**
     * Toggle feed type active status
     */
    public function toggleStatus($id)
    {
        $feedType = FeedType::findOrFail($id);
        $feedType->update(['is_active' => !$feedType->is_active]);
        
        $status = $feedType->is_active ? 'activated' : 'deactivated';
        
        return redirect()->route('feed-types.show', $feedType->id)
            ->with('success', "Feed type {$status} successfully");
    }

    /**
 * Get feed type details for AJAX modal
 */
public function getFeedTypeDetails($id)
{
    try {
        $feedType = FeedType::with('species')->findOrFail($id);
        
        $stats = [
            'total_deliveries' => $feedType->feedDeliveries()->count(),
            'total_quantity' => $feedType->feedDeliveries()->sum('quantity_kg'),
            'current_stock' => $feedType->feedDeliveries()->sum('remaining_quantity_kg'),
            'avg_cost_per_kg' => $feedType->feedDeliveries()->avg('cost_per_kg') ?? 0,
            'recent_deliveries' => $feedType->feedDeliveries()
                ->latest('delivery_date')
                ->take(5)
                ->get()
                ->map(function($delivery) {
                    return [
                        'date' => $delivery->delivery_date->format('Y-m-d'),
                        'quantity' => $delivery->quantity_kg,
                        'cost_per_kg' => $delivery->cost_per_kg,
                        'remaining' => $delivery->remaining_quantity_kg,
                    ];
                })
        ];
        
        return response()->json([
            'success' => true,
            'feedType' => [
                'id' => $feedType->id,
                'name' => $feedType->name,
                'code' => $feedType->code,
                'species_name' => $feedType->species->name ?? null,
                'category' => $feedType->category,
                'protein_percentage' => $feedType->protein_percentage,
                'energy_mj_kg' => $feedType->energy_mj_kg,
                'is_active' => $feedType->is_active,
                'description' => $feedType->description,
            ],
            'stats' => $stats
        ]);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()]);
    }
}

/**
 * Get feed type edit data for AJAX modal
 */
public function getFeedTypeEditData($id)
{
    try {
        $feedType = FeedType::findOrFail($id);
        $species = Species::where('is_active', true)->get(['id', 'name']);
        
        return response()->json([
            'success' => true,
            'feedType' => [
                'id' => $feedType->id,
                'name' => $feedType->name,
                'code' => $feedType->code,
                'species_id' => $feedType->species_id,
                'category' => $feedType->category,
                'protein_percentage' => $feedType->protein_percentage,
                'energy_mj_kg' => $feedType->energy_mj_kg,
                'is_active' => $feedType->is_active,
                'description' => $feedType->description,
            ],
            'species' => $species
        ]);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()]);
    }
}
}