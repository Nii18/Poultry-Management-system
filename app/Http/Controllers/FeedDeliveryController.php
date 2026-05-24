<?php
// app/Http/Controllers/FeedDeliveryController.php

namespace App\Http\Controllers;

use App\Models\FeedDelivery;
use App\Models\FeedType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FeedDeliveryController extends Controller
{
    /**
     * Display a listing of feed deliveries
     */
    public function index(Request $request)
    {
        $feedTypeId = $request->get('feed_type_id');
        $startDate = $request->get('start_date')  ? Carbon::parse($request->get('start_date')) : Carbon::now()->subDays(30);
        $endDate = $request->get('end_date') ? Carbon::parse($request->get('end_date')) : Carbon::now();
        
        $query = FeedDelivery::with('feedType');
        
        if ($feedTypeId) {
            $query->where('feed_type_id', $feedTypeId);
        }
        
        $deliveries = $query->whereBetween('delivery_date', [$startDate, $endDate])
            ->orderBy('delivery_date', 'desc')
            ->paginate(20);
        
        $feedTypes = FeedType::where('is_active', true)->get();
        
        return view('feed-deliveries.index', compact('deliveries', 'feedTypes', 'feedTypeId', 'startDate', 'endDate'));
    }
    
    /**
     * Show the form for creating a new feed delivery
     */
    public function create()
    {
        $feedTypes = FeedType::where('is_active', true)->get();
        
        return view('feed-deliveries.create', compact('feedTypes'));
    }
    
    /**
     * Store a newly created feed delivery
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'feed_type_id' => 'required|exists:feed_types,id',
            'supplier_name' => 'required|string|max:255',
            'invoice_number' => 'nullable|string|max:100',
            'quantity_kg' => 'required|numeric|min:0.01',
            'cost_per_kg' => 'required|numeric|min:0',
            'delivery_date' => 'required|date|before_or_equal:today',
            'expiry_date' => 'nullable|date|after:delivery_date',
            'batch_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string'
        ]);
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        DB::beginTransaction();
        
        try {
            $totalCost = $request->quantity_kg * $request->cost_per_kg;
            
            $delivery = FeedDelivery::create([
                'feed_type_id' => $request->feed_type_id,
                'supplier_name' => $request->supplier_name,
                'invoice_number' => $request->invoice_number,
                'quantity_kg' => $request->quantity_kg,
                'cost_per_kg' => $request->cost_per_kg,
                'total_cost' => $totalCost,
                'delivery_date' => $request->delivery_date,
                'expiry_date' => $request->expiry_date,
                'remaining_quantity_kg' => $request->quantity_kg,
                'batch_number' => $request->batch_number,
                'notes' => $request->notes,
                'received_by' => auth()->id()
            ]);
            
            DB::commit();
            
            // Replace with:
if ($request->expectsJson()) {
    return response()->json(['success' => true, 'message' => 'Feed delivery recorded successfully']);
}
return redirect()->route('feed-deliveries.index')
    ->with('success', 'Feed delivery recorded successfully');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to record feed delivery: ' . $e->getMessage());
        }
    }
    
    /**
     * Display the specified feed delivery
     */
    public function show($id)
    {
        $delivery = FeedDelivery::with(['feedType', 'receiver'])->findOrFail($id);
        
        // Calculate usage statistics
        $usedQuantity = $delivery->quantity_kg - $delivery->remaining_quantity_kg;
        $usagePercentage = $delivery->usage_percentage;
        
        return redirect()->route('feed-deliveries.index');
    }
    
    /**
     * Show the form for editing the specified feed delivery
     */
    public function edit($id)
    {
        $delivery = FeedDelivery::findOrFail($id);
        $feedTypes = FeedType::where('is_active', true)->get();
        
        return redirect()->route('feed-deliveries.index');
    }
    
    /**
     * Update the specified feed delivery
     */
    public function update(Request $request, $id)
{
    $validator = Validator::make($request->all(), [
        'feed_type_id'          => 'required|exists:feed_types,id',
        'supplier_name'         => 'required|string|max:255',
        'invoice_number'        => 'nullable|string|max:100',
        'quantity_kg'           => 'required|numeric|min:0.01',
        'cost_per_kg'           => 'required|numeric|min:0',
        'delivery_date'         => 'required|date',
        'expiry_date'           => 'nullable|date|after:delivery_date',
        'remaining_quantity_kg' => 'required|numeric|min:0',
        'batch_number'          => 'nullable|string|max:100',
        'notes'                 => 'nullable|string'
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

    DB::beginTransaction();

    try {
        $delivery = FeedDelivery::findOrFail($id);
        $totalCost = $request->quantity_kg * $request->cost_per_kg;

        $delivery->update([
            'feed_type_id'          => $request->feed_type_id,
            'supplier_name'         => $request->supplier_name,
            'invoice_number'        => $request->invoice_number,
            'quantity_kg'           => $request->quantity_kg,
            'cost_per_kg'           => $request->cost_per_kg,
            'total_cost'            => $totalCost,
            'delivery_date'         => $request->delivery_date,
            'expiry_date'           => $request->expiry_date,
            'remaining_quantity_kg' => $request->remaining_quantity_kg,
            'batch_number'          => $request->batch_number,
            'notes'                 => $request->notes
        ]);

        DB::commit();

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Feed delivery updated successfully']);
        }
        return redirect()->route('feed-deliveries.show', $delivery->id)->with('success', 'Feed delivery updated successfully');

    } catch (\Exception $e) {
        DB::rollBack();
        if ($request->expectsJson()) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
        return back()->with('error', 'Failed to update feed delivery: ' . $e->getMessage());
    }
}
    
    /**
     * Remove the specified feed delivery
     */
    public function destroy($id)
    {
        $delivery = FeedDelivery::findOrFail($id);
        
        // Check if delivery has issuances
        $issuanceCount = $delivery->feedIssuances()->count();
        
        if ($issuanceCount > 0) {
            return back()->with('error', 'Cannot delete delivery with feed issuances.');
        }
        
        $delivery->delete();
        
        return redirect()->route('feed-deliveries.index')
            ->with('success', 'Feed delivery deleted successfully');
    }
    
    /**
     * Display low stock alerts
     */
    public function lowStock()
    {
        $lowStockDeliveries = FeedDelivery::with('feedType')
            ->where('remaining_quantity_kg', '<', 500)
            ->where('expiry_date', '>', now())
            ->orderBy('remaining_quantity_kg')
            ->get();
        
        $expiredStock = FeedDelivery::with('feedType')
            ->where('expiry_date', '<=', now())
            ->where('remaining_quantity_kg', '>', 0)
            ->orderBy('expiry_date')
            ->get();
        
        return view('feed-deliveries.low-stock', compact('lowStockDeliveries', 'expiredStock'));
    }

    /**
 * Get delivery details for AJAX modal
 */
public function getDeliveryDetails($id)
{
    try {
        $delivery = FeedDelivery::with(['feedType.species', 'receiver'])->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'delivery' => [
                'id' => $delivery->id,
                'delivery_date' => $delivery->delivery_date->format('Y-m-d'),
                'feed_type_name' => $delivery->feedType->name ?? 'N/A',
                'supplier_name' => $delivery->supplier_name,
                'invoice_number' => $delivery->invoice_number,
                'quantity_kg' => (float) $delivery->quantity_kg,
                'cost_per_kg' => (float) $delivery->cost_per_kg,
                'total_cost' => (float) $delivery->total_cost,
                'remaining_quantity_kg' => (float) $delivery->remaining_quantity_kg,
                'usage_percentage' => $delivery->usage_percentage,
                'batch_number' => $delivery->batch_number,
                'expiry_date' => $delivery->expiry_date ? $delivery->expiry_date->format('Y-m-d') : null,
                'received_by_name' => $delivery->receiver->name ?? 'N/A',
                'notes' => $delivery->notes,
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()]);
    }
}

/**
 * Get delivery edit data for AJAX modal
 */
public function getDeliveryEditData($id)
{
    try {
        $delivery = FeedDelivery::findOrFail($id);
        $feedTypes = FeedType::with('species')->where('is_active', true)->get(['id', 'name', 'category']);
        
        $feedTypesData = $feedTypes->map(function($type) {
            return [
                'id' => $type->id,
                'name' => $type->name,
                'category' => $type->category,
                'species_name' => $type->species->name ?? 'N/A',
            ];
        });
        
        return response()->json([
            'success' => true,
            'delivery' => [
                'id' => $delivery->id,
                'feed_type_id' => $delivery->feed_type_id,
                'delivery_date' => $delivery->delivery_date->format('Y-m-d'),
                'supplier_name' => $delivery->supplier_name,
                'invoice_number' => $delivery->invoice_number,
                'quantity_kg' => (float) $delivery->quantity_kg,
                'cost_per_kg' => (float) $delivery->cost_per_kg,
                'remaining_quantity_kg' => (float) $delivery->remaining_quantity_kg,
                'batch_number' => $delivery->batch_number,
                'expiry_date' => $delivery->expiry_date ? $delivery->expiry_date->format('Y-m-d') : null,
                'notes' => $delivery->notes,
            ],
            'feedTypes' => $feedTypesData
        ]);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()]);
    }
}
}