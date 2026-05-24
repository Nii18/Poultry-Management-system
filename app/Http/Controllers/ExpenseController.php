<?php
// app/Http/Controllers/ExpenseController.php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Flock;
use App\Models\House;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ExpenseController extends Controller
{
    /**
     * Display a listing of expenses
     */
    public function index(Request $request)
    {
        $flockId = $request->get('flock_id');
        $houseId = $request->get('house_id');
        $category = $request->get('category');
        
        // Remove default date filters - show ALL expenses by default
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        
        $query = Expense::with(['flock', 'house', 'creator']);
        
        if ($flockId) {
            $query->where('flock_id', $flockId);
        }
        
        if ($houseId) {
            $query->where('house_id', $houseId);
        }
        
        if ($category) {
            $query->where('category', $category);
        }
        
        // Only apply date filter if user specifically provides dates
        if ($startDate && $endDate) {
            $query->whereBetween('expense_date', [Carbon::parse($startDate), Carbon::parse($endDate)]);
        }
        
        $expenses = $query->orderBy('expense_date', 'desc')
            ->paginate(20);
        
        $flocks = Flock::where('status', 'active')->get();
        $houses = House::where('status', 'active')->get();
        
        $categories = Expense::distinct()->pluck('category');
        
        // For date inputs in the filter bar
        $currentStartDate = $startDate ? Carbon::parse($startDate) : Carbon::now()->subMonths(3);
        $currentEndDate = $endDate ? Carbon::parse($endDate) : Carbon::now();
        
        // ==================== STATS FOR CLICKABLE MODALS ====================
        $totalAllTimeExpenses = Expense::sum('amount');
        $totalRecordsCount = Expense::count();
        
        $thisMonthStart = Carbon::now()->startOfMonth();
        $thisMonthEnd = Carbon::now()->endOfMonth();
        $thisMonthExpenses = Expense::whereBetween('expense_date', [$thisMonthStart, $thisMonthEnd])->sum('amount');
        $thisMonthRecordsCount = Expense::whereBetween('expense_date', [$thisMonthStart, $thisMonthEnd])->count();
        
        $thisWeekStart = Carbon::now()->startOfWeek();
        $thisWeekEnd = Carbon::now()->endOfWeek();
        $thisWeekExpenses = Expense::whereBetween('expense_date', [$thisWeekStart, $thisWeekEnd])->sum('amount');
        $thisWeekRecordsCount = Expense::whereBetween('expense_date', [$thisWeekStart, $thisWeekEnd])->count();
        
        $todayRecordsCount = Expense::whereDate('expense_date', Carbon::today())->count();
        
        $categoryBreakdown = Expense::select('category', DB::raw('SUM(amount) as total'))
            ->groupBy('category')
            ->orderBy('total', 'desc')
            ->get();
        
        $thisMonthCategories = Expense::select('category', DB::raw('SUM(amount) as total'))
            ->whereBetween('expense_date', [$thisMonthStart, $thisMonthEnd])
            ->groupBy('category')
            ->orderBy('total', 'desc')
            ->get();
        
        $categoryRecordCounts = Expense::select('category', DB::raw('COUNT(*) as count'))
            ->groupBy('category')
            ->orderBy('count', 'desc')
            ->get();
        
        $highestMonthData = Expense::select(DB::raw('DATE_FORMAT(expense_date, "%Y-%m") as month'), DB::raw('SUM(amount) as total'))
            ->groupBy('month')
            ->orderBy('total', 'desc')
            ->first();
        
        $highestMonthName = $highestMonthData ? date('F Y', strtotime($highestMonthData->month . '-01')) : 'N/A';
        $highestMonthTotal = $highestMonthData ? $highestMonthData->total : 0;
        
        return view('expenses.index', compact(
            'expenses', 'flocks', 'houses', 'categories', 'flockId', 'houseId', 
            'category', 'currentStartDate', 'currentEndDate',
            'totalAllTimeExpenses', 'totalRecordsCount', 'thisMonthExpenses', 
            'thisMonthRecordsCount', 'thisWeekExpenses', 'thisWeekRecordsCount', 
            'todayRecordsCount', 'categoryBreakdown', 'thisMonthCategories', 
            'categoryRecordCounts', 'highestMonthName', 'highestMonthTotal'
        ));
    }
    
    /**
     * Show the form for creating a new expense
     */
    public function create(Request $request)
    {
        $flockId = $request->get('flock_id');
        $houseId = $request->get('house_id');
        
        $flock = null;
        $house = null;
        
        if ($flockId) {
            $flock = Flock::findOrFail($flockId);
        }
        
        if ($houseId) {
            $house = House::findOrFail($houseId);
        }
        
        $flocks = Flock::where('status', 'active')->get();
        $houses = House::where('status', 'active')->get();
        
        return view('expenses.create', compact('flocks', 'houses', 'flock', 'house'));
    }
    
    /**
     * Store a newly created expense
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'flock_id' => 'nullable|exists:flocks,id',
            'house_id' => 'nullable|exists:houses,id',
            'category' => 'required|string|max:100',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'expense_date' => 'required|date|before_or_equal:today',
            'payment_method' => 'nullable|string|max:50',
            'receipt_number' => 'nullable|string|max:100',
            'vendor_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string'
        ]);
        
        if ($validator->fails()) {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
            }
            return back()->withErrors($validator)->withInput();
        }
        
        try {
            $expense = Expense::create([
                'flock_id' => $request->flock_id,
                'house_id' => $request->house_id,
                'category' => $request->category,
                'description' => $request->description,
                'amount' => $request->amount,
                'expense_date' => $request->expense_date,
                'payment_method' => $request->payment_method,
                'receipt_number' => $request->receipt_number,
                'vendor_name' => $request->vendor_name,
                'notes' => $request->notes,
                'created_by' => auth()->id()
            ]);
            
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Expense recorded successfully']);
            }
            
            return redirect()->route('expenses.index')
                ->with('success', 'Expense recorded successfully');
                
        } catch (\Exception $e) {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return back()->with('error', 'Failed to record expense: ' . $e->getMessage());
        }
    }
    
    /**
     * Display the specified expense
     */
    public function show($id)
    {
        $expense = Expense::with(['flock', 'house', 'creator'])->findOrFail($id);
        
        return view('expenses.show', compact('expense'));
    }
    
    /**
     * Show the form for editing the specified expense
     */
    public function edit($id)
    {
        $expense = Expense::findOrFail($id);
        $flocks = Flock::where('status', 'active')->get();
        $houses = House::where('status', 'active')->get();
        
        return view('expenses.edit', compact('expense', 'flocks', 'houses'));
    }
    
    /**
     * Update the specified expense
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'flock_id' => 'nullable|exists:flocks,id',
            'house_id' => 'nullable|exists:houses,id',
            'category' => 'required|string|max:100',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'payment_method' => 'nullable|string|max:50',
            'receipt_number' => 'nullable|string|max:100',
            'vendor_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string'
        ]);
        
        if ($validator->fails()) {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
            }
            return back()->withErrors($validator)->withInput();
        }
        
        $expense = Expense::findOrFail($id);
        
        try {
            $expense->update([
                'flock_id' => $request->flock_id,
                'house_id' => $request->house_id,
                'category' => $request->category,
                'description' => $request->description,
                'amount' => $request->amount,
                'expense_date' => $request->expense_date,
                'payment_method' => $request->payment_method,
                'receipt_number' => $request->receipt_number,
                'vendor_name' => $request->vendor_name,
                'notes' => $request->notes
            ]);
            
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Expense updated successfully']);
            }
            
            return redirect()->route('expenses.index')
                ->with('success', 'Expense updated successfully');
                
        } catch (\Exception $e) {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return back()->with('error', 'Failed to update expense: ' . $e->getMessage());
        }
    }
    
    /**
     * Remove the specified expense
     */
    public function destroy($id)
    {
        $expense = Expense::findOrFail($id);
        $expense->delete();
        
        return redirect()->route('expenses.index')
            ->with('success', 'Expense deleted successfully');
    }
    
    /**
     * Display expenses by category
     */
    public function byCategory(Request $request)
    {
        $year = $request->get('year', Carbon::now()->year);
        
        $expensesByCategory = Expense::select('category', DB::raw('SUM(amount) as total'))
            ->whereYear('expense_date', $year)
            ->groupBy('category')
            ->orderBy('total', 'desc')
            ->get();
        
        $monthlyBreakdown = Expense::select(
                DB::raw('MONTH(expense_date) as month'),
                DB::raw('SUM(amount) as total')
            )
            ->whereYear('expense_date', $year)
            ->groupBy('month')
            ->orderBy('month')
            ->get();
        
        $totalExpenses = $expensesByCategory->sum('total');
        
        return view('expenses.by-category', compact('expensesByCategory', 'monthlyBreakdown', 'totalExpenses', 'year'));
    }

    /**
     * Get create form data for AJAX modal
     */
    public function getCreateForm()
    {
        try {
            $flocks = Flock::where('status', 'active')->get(['id', 'flock_number', 'breed_variety']);
            $houses = House::where('status', 'active')->get(['id', 'name']);
            
            return response()->json([
                'success' => true,
                'flocks' => $flocks,
                'houses' => $houses
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Store expense via AJAX
     */
    public function storeExpenseAjax(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'category' => 'required|string|max:100',
                'description' => 'required|string|max:255',
                'amount' => 'required|numeric|min:0.01',
                'expense_date' => 'required|date',
                'payment_method' => 'nullable|string|max:50',
                'receipt_number' => 'nullable|string|max:100',
                'vendor_name' => 'nullable|string|max:255',
                'flock_id' => 'nullable|exists:flocks,id',
                'house_id' => 'nullable|exists:houses,id',
                'notes' => 'nullable|string'
            ]);
            
            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
            }
            
            $expense = Expense::create($request->all() + ['created_by' => auth()->id()]);
            
            return response()->json(['success' => true, 'message' => 'Expense created successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get expense details as JSON
     */
    public function getDetailsJson($id)
    {
        try {
            $expense = Expense::with(['flock', 'house', 'creator'])->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'expense' => [
                    'id' => $expense->id,
                    'expense_date' => $expense->expense_date->format('d M Y'),
                    'category' => $expense->category,
                    'description' => $expense->description,
                    'amount' => $expense->amount,
                    'vendor_name' => $expense->vendor_name,
                    'payment_method' => $expense->payment_method,
                    'receipt_number' => $expense->receipt_number,
                    'flock_number' => $expense->flock->flock_number ?? null,
                    'house_name' => $expense->house->name ?? null,
                    'notes' => $expense->notes,
                    'recorded_by' => $expense->creator->name ?? 'N/A',
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get expense edit data as JSON
     */
    public function getEditData($id)
    {
        try {
            $expense = Expense::findOrFail($id);
            $flocks = Flock::where('status', 'active')->get(['id', 'flock_number', 'breed_variety']);
            $houses = House::where('status', 'active')->get(['id', 'name']);
            
            return response()->json([
                'success' => true,
                'expense' => [
                    'id' => $expense->id,
                    'expense_date' => $expense->expense_date->format('Y-m-d'),
                    'category' => $expense->category,
                    'description' => $expense->description,
                    'amount' => $expense->amount,
                    'vendor_name' => $expense->vendor_name,
                    'payment_method' => $expense->payment_method,
                    'receipt_number' => $expense->receipt_number,
                    'flock_id' => $expense->flock_id,
                    'house_id' => $expense->house_id,
                    'notes' => $expense->notes,
                ],
                'flocks' => $flocks,
                'houses' => $houses
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Update expense via AJAX
     */
    public function updateExpenseAjax(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'category' => 'required|string|max:100',
                'description' => 'required|string|max:255',
                'amount' => 'required|numeric|min:0.01',
                'expense_date' => 'required|date',
                'payment_method' => 'nullable|string|max:50',
                'receipt_number' => 'nullable|string|max:100',
                'vendor_name' => 'nullable|string|max:255',
                'flock_id' => 'nullable|exists:flocks,id',
                'house_id' => 'nullable|exists:houses,id',
                'notes' => 'nullable|string'
            ]);
            
            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
            }
            
            $expense = Expense::findOrFail($id);
            $expense->update($request->all());
            
            return response()->json(['success' => true, 'message' => 'Expense updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}