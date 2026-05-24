<?php
// app/Http/Controllers/FeedIssuanceController.php

namespace App\Http\Controllers;

use App\Models\FeedIssuance;
use App\Models\Flock;
use App\Models\FeedDelivery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class FeedIssuanceController extends Controller
{
    /**
     * Display a listing of feed issuances
     */
    public function index(Request $request)
    {
        $flockId = $request->get('flock_id');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        
        $query = FeedIssuance::with(['flock', 'feedDelivery.feedType', 'issuer']);
        
        if ($flockId) {
            $query->where('flock_id', $flockId);
        }
        
        if ($startDate) {
            $query->where('issuance_date', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->where('issuance_date', '<=', $endDate);
        }
        
        $issuances = $query->orderBy('issuance_date', 'desc')
            ->paginate(20);
        
        $flocks = Flock::where('status', 'active')->get();
        
        return view('feed-issuances.index', compact('issuances', 'flocks', 'flockId'));
    }
    
    /**
     * Show the form for creating a new feed issuance
     */
    public function create(Request $request)
    {
        $flockId = $request->get('flock_id');
        $flock = null;
        
        if ($flockId) {
            $flock = Flock::findOrFail($flockId);
        }
        
        $flocks = Flock::where('status', 'active')->get();
        $feedDeliveries = FeedDelivery::with('feedType')
            ->where('remaining_quantity_kg', '>', 0)
            ->where('expiry_date', '>', now())
            ->orderBy('expiry_date')
            ->get();
        
        return view('feed-issuances.create', compact('flocks', 'feedDeliveries', 'flock'));
    }
    
    /**
     * Store a newly created feed issuance
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'flock_id' => 'required|exists:flocks,id',
            'feed_delivery_id' => 'required|exists:feed_deliveries,id',
            'quantity_kg' => 'required|numeric|min:0.01',
            'issuance_date' => 'required|date|before_or_equal:today',
            'issuance_time' => 'nullable|date_format:H:i',
            'notes' => 'nullable|string'
        ]);
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        DB::beginTransaction();
        
        try {
            $feedDelivery = FeedDelivery::find($request->feed_delivery_id);
            
            // Check if enough stock is available
            if ($feedDelivery->remaining_quantity_kg < $request->quantity_kg) {
                return back()->with('error', 'Insufficient feed stock. Available: ' . $feedDelivery->remaining_quantity_kg . 'kg');
            }
            
            // Create issuance
            $issuance = FeedIssuance::create([
                'flock_id' => $request->flock_id,
                'feed_delivery_id' => $request->feed_delivery_id,
                'quantity_kg' => $request->quantity_kg,
                'issuance_date' => $request->issuance_date,
                'issuance_time' => $request->issuance_time,
                'notes' => $request->notes,
                'issued_by' => auth()->id()
            ]);
            
            // Update remaining stock
            $feedDelivery->update([
                'remaining_quantity_kg' => $feedDelivery->remaining_quantity_kg - $request->quantity_kg
            ]);
            
            DB::commit();
            
            return redirect()->route('feed-issuances.show', $issuance->id)
                ->with('success', 'Feed issuance recorded successfully');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to record feed issuance: ' . $e->getMessage());
        }
    }
    
    /**
     * Display the specified feed issuance
     */
    public function show($id)
    {
        $issuance = FeedIssuance::with(['flock', 'feedDelivery.feedType', 'issuer'])
            ->findOrFail($id);
        
        return view('feed-issuances.show', compact('issuance'));
    }
    
    /**
     * Show the form for editing the specified feed issuance
     */
    public function edit($id)
    {
        $issuance = FeedIssuance::findOrFail($id);
        $flocks = Flock::where('status', 'active')->get();
        
        return view('feed-issuances.edit', compact('issuance', 'flocks'));
    }
    
    /**
     * Update the specified feed issuance
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'flock_id'       => 'required|exists:flocks,id',
            'quantity_kg'    => 'required|numeric|min:0.01',
            'issuance_date'  => 'required|date',
            'issuance_time'  => 'nullable|date_format:H:i',
            'notes'          => 'nullable|string'
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
    
        $issuance = FeedIssuance::findOrFail($id);
    
        DB::beginTransaction();
    
        try {
            // Restore previous quantity to feed delivery
            $feedDelivery = FeedDelivery::find($issuance->feed_delivery_id);
            $feedDelivery->update([
                'remaining_quantity_kg' => $feedDelivery->remaining_quantity_kg + $issuance->quantity_kg
            ]);
    
            // Update issuance
            $issuance->update([
                'flock_id'       => $request->flock_id,
                'quantity_kg'    => $request->quantity_kg,
                'issuance_date'  => $request->issuance_date,
                'issuance_time'  => $request->issuance_time,
                'notes'          => $request->notes
            ]);
    
            // Deduct new quantity
            $feedDelivery->update([
                'remaining_quantity_kg' => $feedDelivery->remaining_quantity_kg - $request->quantity_kg
            ]);
    
            DB::commit();
    
            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Feed issuance updated successfully']);
            }
            return redirect()->route('feed-issuances.show', $issuance->id)->with('success', 'Feed issuance updated successfully');
    
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return back()->with('error', 'Failed to update feed issuance: ' . $e->getMessage());
        }
    }
    
    /**
     * Remove the specified feed issuance
     */
    public function destroy($id)
    {
        $issuance = FeedIssuance::findOrFail($id);
        
        DB::beginTransaction();
        
        try {
            // Restore quantity to feed delivery
            $feedDelivery = FeedDelivery::find($issuance->feed_delivery_id);
            $feedDelivery->update([
                'remaining_quantity_kg' => $feedDelivery->remaining_quantity_kg + $issuance->quantity_kg
            ]);
            
            $issuance->delete();
            
            DB::commit();
            
            return redirect()->route('feed-issuances.index')
                ->with('success', 'Feed issuance deleted successfully');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete feed issuance: ' . $e->getMessage());
        }
    }

    /**
 * Get issuance details for AJAX modal
 */
public function getIssuanceDetails($id)
{
    try {
        $issuance = FeedIssuance::with(['flock.species', 'feedDelivery.feedType', 'issuer'])->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'issuance' => [
                'id' => $issuance->id,
                'issuance_date' => $issuance->issuance_date->format('Y-m-d'),
                'issuance_time' => $issuance->issuance_time,
                'flock_number' => $issuance->flock->flock_number ?? 'N/A',
                'breed_variety' => $issuance->flock->breed_variety ?? 'N/A',
                'species_name' => $issuance->flock->species->name ?? 'N/A',
                'feed_type_name' => $issuance->feedDelivery->feedType->name ?? 'N/A',
                'category' => $issuance->feedDelivery->feedType->category ?? 'N/A',
                'batch_number' => $issuance->feedDelivery->batch_number,
                'supplier_name' => $issuance->feedDelivery->supplier_name,
                'quantity_kg' => (float) $issuance->quantity_kg,
                'issued_by_name' => $issuance->issuer->name ?? 'N/A',
                'notes' => $issuance->notes,
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()]);
    }
}

/**
 * Get issuance edit data for AJAX modal
 */
public function getIssuanceEditData($id)
{
    try {
        $issuance = FeedIssuance::findOrFail($id);
        $flocks = Flock::where('status', 'active')->get(['id', 'flock_number', 'breed_variety']);
        
        return response()->json([
            'success' => true,
            'issuance' => [
                'id' => $issuance->id,
                'flock_id' => $issuance->flock_id,
                'quantity_kg' => (float) $issuance->quantity_kg,
                'issuance_date' => $issuance->issuance_date->format('Y-m-d'),
                'issuance_time' => $issuance->issuance_time,
                'notes' => $issuance->notes,
            ],
            'flocks' => $flocks
        ]);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()]);
    }
}
}