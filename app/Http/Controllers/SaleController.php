<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Flock;
use App\Models\FarmProduce;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SaleController extends Controller
{
    /**
     * Display a listing of sales
     */
    public function index(Request $request)
    {
        $flockId = $request->get('flock_id');
        $productType = $request->get('product_type');
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth());
        $endDate = $request->get('end_date', Carbon::now());
        
        $query = Sale::with(['flock', 'creator']);
        
        if ($flockId) {
            $query->where('flock_id', $flockId);
        }
        
        if ($productType) {
            $query->where('product_type', $productType);
        }
        
        $sales = $query->whereBetween('sale_date', [$startDate, $endDate])
            ->orderBy('sale_date', 'desc')
            ->paginate(20);
        
        $flocks = Flock::where('status', 'active')->get();
        $productTypes = Sale::distinct()->pluck('product_type');
        
        $totalRevenue = $sales->sum('total_amount');
        $totalQuantity = $sales->sum('quantity');
        
        return view('sales.index', compact('sales', 'flocks', 'productTypes', 'totalRevenue', 'totalQuantity', 'flockId', 'productType', 'startDate', 'endDate'));
    }
    
    /**
     * Get create form data for AJAX modal
     */
    public function getCreateForm()
    {
        try {
            $flocks = Flock::where('status', 'active')->get(['id', 'flock_number', 'breed_variety']);
    
            // Pull product types from actual produce records — dynamic!
            $produceTypes = FarmProduce::getActiveProductTypes();
    
            return response()->json([
                'success'      => true,
                'flocks'       => $flocks,
                'productTypes' => $produceTypes, // NEW: dynamic from produce table
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Store sale via AJAX
     */
    public function storeSaleAjax(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'product_type' => 'required|string|max:50',
                'quantity' => 'required|numeric|min:0.01',
                'unit_price' => 'required|numeric|min:0.01',
                'total_amount' => 'required|numeric|min:0',
                'sale_date' => 'required|date|before_or_equal:today',
                'flock_id' => 'nullable|exists:flocks,id',
                'customer_name' => 'nullable|string|max:255',
                'payment_method' => 'nullable|string|max:50',
                'receipt_number' => 'nullable|string|max:100',
                'description' => 'nullable|string|max:255',
                'notes' => 'nullable|string'
            ]);
            
            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
            }
            
            $sale = Sale::create([
                'flock_id' => $request->flock_id,
                'product_type' => $request->product_type,
                'quantity' => $request->quantity,
                'unit_price' => $request->unit_price,
                'total_amount' => $request->total_amount,
                'sale_date' => $request->sale_date,
                'customer_name' => $request->customer_name,
                'payment_method' => $request->payment_method,
                'receipt_number' => $request->receipt_number,
                'description' => $request->description,
                'notes' => $request->notes,
                'created_by' => auth()->id()
            ]);
            
            return response()->json(['success' => true, 'message' => 'Sale recorded successfully', 'sale_id' => $sale->id]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Get sale details as JSON for modal
     */
    public function getDetailsJson($id)
    {
        try {
            $sale = Sale::with(['flock', 'creator'])->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'sale' => [
                    'id' => $sale->id,
                    'sale_date' => $sale->sale_date->format('d M Y'),
                    'product_type' => $sale->product_type_label,
                    'product_type_key' => $sale->product_type,
                    'quantity' => number_format($sale->quantity, 2),
                    'unit_price' => number_format($sale->unit_price, 2),
                    'total_amount' => number_format($sale->total_amount, 2),
                    'customer_name' => $sale->customer_name ?? 'N/A',
                    'payment_method' => $sale->payment_method ? ucfirst(str_replace('_', ' ', $sale->payment_method)) : 'N/A',
                    'receipt_number' => $sale->receipt_number ?? 'N/A',
                    'description' => $sale->description ?? 'N/A',
                    'flock_number' => $sale->flock->flock_number ?? null,
                    'flock_breed' => $sale->flock->breed_variety ?? null,
                    'notes' => $sale->notes,
                    'recorded_by' => $sale->creator->name ?? 'N/A',
                    'created_at' => $sale->created_at->format('d M Y H:i'),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Get sale edit data as JSON for modal
     */
    public function getEditData($id)
    {
        try {
            $sale = Sale::findOrFail($id);
            $flocks = Flock::where('status', 'active')->get(['id', 'flock_number', 'breed_variety']);
            
            return response()->json([
                'success' => true,
                'sale' => [
                    'id' => $sale->id,
                    'product_type' => $sale->product_type,
                    'quantity' => $sale->quantity,
                    'unit_price' => $sale->unit_price,
                    'total_amount' => $sale->total_amount,
                    'sale_date' => $sale->sale_date->format('Y-m-d'),
                    'flock_id' => $sale->flock_id,
                    'customer_name' => $sale->customer_name,
                    'payment_method' => $sale->payment_method,
                    'receipt_number' => $sale->receipt_number,
                    'description' => $sale->description,
                    'notes' => $sale->notes,
                ],
                'flocks' => $flocks
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Update sale via AJAX
     */
    public function updateSaleAjax(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'product_type' => 'required|string|max:50',
                'quantity' => 'required|numeric|min:0.01',
                'unit_price' => 'required|numeric|min:0.01',
                'total_amount' => 'required|numeric|min:0',
                'sale_date' => 'required|date',
                'flock_id' => 'nullable|exists:flocks,id',
                'customer_name' => 'nullable|string|max:255',
                'payment_method' => 'nullable|string|max:50',
                'receipt_number' => 'nullable|string|max:100',
                'description' => 'nullable|string|max:255',
                'notes' => 'nullable|string'
            ]);
            
            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
            }
            
            $sale = Sale::findOrFail($id);
            $sale->update($request->all());
            
            return response()->json(['success' => true, 'message' => 'Sale updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Remove the specified sale
     */
    public function destroy($id)
    {
        $sale = Sale::findOrFail($id);
        $sale->delete();
        
        return redirect()->route('sales.index')
            ->with('success', 'Sale deleted successfully');
    }
    
    /**
     * Display sales by product type
     */
    public function byProductType(Request $request)
    {
        $year = $request->get('year', Carbon::now()->year);
        
        $salesByProduct = Sale::select('product_type', DB::raw('SUM(quantity) as total_quantity'), DB::raw('SUM(total_amount) as total_revenue'))
            ->whereYear('sale_date', $year)
            ->groupBy('product_type')
            ->orderBy('total_revenue', 'desc')
            ->get();
        
        $monthlyBreakdown = Sale::select(
                DB::raw('MONTH(sale_date) as month'),
                DB::raw('SUM(total_amount) as total')
            )
            ->whereYear('sale_date', $year)
            ->groupBy('month')
            ->orderBy('month')
            ->get();
        
        $totalRevenue = $salesByProduct->sum('total_revenue');
        
        // Get expense total for profit calculation
        $totalExpenses = \App\Models\Expense::whereYear('expense_date', $year)->sum('amount');
        $netProfit = $totalRevenue - $totalExpenses;
        
        return view('sales.by-product', compact('salesByProduct', 'monthlyBreakdown', 'totalRevenue', 'totalExpenses', 'netProfit', 'year'));
    }
}