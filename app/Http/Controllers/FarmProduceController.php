<?php

namespace App\Http\Controllers;

use App\Models\FarmProduce;
use App\Models\Flock;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FarmProduceController extends Controller
{
    // ── Index / listing ────────────────────────────────────────────

    public function index(Request $request)
    {
        $flockId     = $request->get('flock_id');
        $productType = $request->get('product_type');
        $startDate   = $request->get('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate     = $request->get('end_date',   Carbon::now()->toDateString());

        $query = FarmProduce::with(['flock', 'creator']);

        if ($flockId) {
            $query->where('flock_id', $flockId);
        }
        if ($productType) {
            $query->where('product_type', $productType);
        }

        $produces = $query
            ->whereBetween('produce_date', [$startDate, $endDate])
            ->orderBy('produce_date', 'desc')
            ->paginate(20)
            ->appends($request->query());

        $flocks       = Flock::where('status', 'active')->get();
        $productTypes = FarmProduce::productTypeLabels();
        $totalQty     = $produces->sum('quantity');

        return view('produces.index', compact(
            'produces', 'flocks', 'productTypes',
            'totalQty', 'flockId', 'productType', 'startDate', 'endDate'
        ));
    }

    // ── Inventory summary page ─────────────────────────────────────

    public function inventory(Request $request)
    {
        $flockId = $request->get('flock_id');
        $year    = $request->get('year', Carbon::now()->year);

        $productTypes = FarmProduce::productTypeLabels();

        // Build per-product totals
        $inventory = collect($productTypes)->map(function ($label, $type) use ($flockId, $year) {
            $produceQuery = FarmProduce::where('product_type', $type)
                ->whereYear('produce_date', $year);
            $saleQuery = Sale::where('product_type', $type)
                ->whereYear('sale_date', $year);

            if ($flockId) {
                $produceQuery->where('flock_id', $flockId);
                $saleQuery->where('flock_id', $flockId);
            }

            $produced  = (float) $produceQuery->sum('quantity');
            $sold      = (float) $saleQuery->sum('quantity');
            $remaining = max(0, $produced - $sold);

            // Unit for display
            $unit = FarmProduce::defaultUnit($type);

            // Monthly breakdown for sparkline (12 months)
            $monthlyProduce = $produceQuery->select(
                    DB::raw('MONTH(produce_date) as month'),
                    DB::raw('SUM(quantity) as total')
                )
                ->groupBy('month')
                ->pluck('total', 'month');

            $monthlySales = $saleQuery->select(
                    DB::raw('MONTH(sale_date) as month'),
                    DB::raw('SUM(quantity) as total')
                )
                ->groupBy('month')
                ->pluck('total', 'month');

            $monthlyData = [];
            for ($m = 1; $m <= 12; $m++) {
                $monthlyData[] = [
                    'month'    => Carbon::create()->month($m)->format('M'),
                    'produced' => (float) ($monthlyProduce[$m] ?? 0),
                    'sold'     => (float) ($monthlySales[$m] ?? 0),
                ];
            }

            return [
                'type'        => $type,
                'label'       => $label,
                'unit'        => $unit,
                'produced'    => $produced,
                'sold'        => $sold,
                'remaining'   => $remaining,
                'sell_through'=> $produced > 0 ? round(($sold / $produced) * 100, 1) : 0,
                'monthly'     => $monthlyData,
            ];
        });

        // Recent produce records
        $recentEntries = FarmProduce::with(['flock', 'creator'])
            ->when($flockId, fn($q) => $q->where('flock_id', $flockId))
            ->whereYear('produce_date', $year)
            ->orderBy('produce_date', 'desc')
            ->take(10)
            ->get();

        $flocks = Flock::where('status', 'active')->get();
        $years  = range(Carbon::now()->year, Carbon::now()->year - 3);

        return view('produces.inventory', compact(
            'inventory', 'recentEntries', 'flocks', 'flockId', 'year', 'years'
        ));
    }

    // ── AJAX: get create form data ─────────────────────────────────

    public function getCreateForm()
    {
        try {
            $flocks       = Flock::where('status', 'active')->get(['id', 'flock_number', 'breed_variety']);
            $productTypes = FarmProduce::productTypeLabels();
            $units        = ['pieces', 'birds', 'kg', 'bags', 'litres', 'trays'];

            return response()->json([
                'success'      => true,
                'flocks'       => $flocks,
                'productTypes' => $productTypes,
                'units'        => $units,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ── AJAX: store ────────────────────────────────────────────────

    public function storeProduceAjax(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'product_type'  => 'required|string|max:50',
                'quantity'      => 'required|numeric|min:0.01',
                'unit'          => 'required|string|max:30',
                'produce_date'  => 'required|date|before_or_equal:today',
                'flock_id'      => 'nullable|exists:flocks,id',
                'notes'         => 'nullable|string|max:500',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                ], 422);
            }

            $produce = FarmProduce::create([
                'flock_id'     => $request->flock_id,
                'product_type' => $request->product_type,
                'quantity'     => $request->quantity,
                'unit'         => $request->unit,
                'produce_date' => $request->produce_date,
                'notes'        => $request->notes,
                'created_by'   => auth()->id(),
            ]);

            return response()->json([
                'success'    => true,
                'message'    => 'Produce recorded successfully',
                'produce_id' => $produce->id,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ── AJAX: get details JSON ─────────────────────────────────────

    public function getDetailsJson($id)
    {
        try {
            $produce = FarmProduce::with(['flock', 'creator'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'produce' => [
                    'id'           => $produce->id,
                    'produce_date' => $produce->produce_date->format('d M Y'),
                    'product_type' => $produce->product_type_label,
                    'quantity'     => number_format($produce->quantity, 2),
                    'unit'         => $produce->unit,
                    'flock_number' => $produce->flock->flock_number ?? 'N/A',
                    'flock_breed'  => $produce->flock->breed_variety ?? null,
                    'notes'        => $produce->notes ?? '—',
                    'recorded_by'  => $produce->creator->name ?? 'N/A',
                    'created_at'   => $produce->created_at->format('d M Y H:i'),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ── AJAX: get edit data ────────────────────────────────────────

    public function getEditData($id)
    {
        try {
            $produce      = FarmProduce::findOrFail($id);
            $flocks       = Flock::where('status', 'active')->get(['id', 'flock_number', 'breed_variety']);
            $productTypes = FarmProduce::productTypeLabels();

            return response()->json([
                'success' => true,
                'produce' => [
                    'id'           => $produce->id,
                    'product_type' => $produce->product_type,
                    'quantity'     => $produce->quantity,
                    'unit'         => $produce->unit,
                    'produce_date' => $produce->produce_date->format('Y-m-d'),
                    'flock_id'     => $produce->flock_id,
                    'notes'        => $produce->notes,
                ],
                'flocks'       => $flocks,
                'productTypes' => $productTypes,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ── AJAX: update ───────────────────────────────────────────────

    public function updateProduceAjax(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'product_type' => 'required|string|max:50',
                'quantity'     => 'required|numeric|min:0.01',
                'unit'         => 'required|string|max:30',
                'produce_date' => 'required|date',
                'flock_id'     => 'nullable|exists:flocks,id',
                'notes'        => 'nullable|string|max:500',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                ], 422);
            }

            $produce = FarmProduce::findOrFail($id);
            $produce->update($request->only([
                'product_type', 'quantity', 'unit', 'produce_date', 'flock_id', 'notes',
            ]));

            return response()->json(['success' => true, 'message' => 'Produce record updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ── Delete ─────────────────────────────────────────────────────

    public function destroy($id)
    {
        try {
            $produce = FarmProduce::findOrFail($id);

            // Only admin or the creator within 24hrs
            $isAdmin   = auth()->user()->role === 'admin';
            $isCreator = $produce->created_by === auth()->id();
            $isRecent  = $produce->created_at->gt(now()->subHours(24));

            if (!$isAdmin && !($isCreator && $isRecent)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only delete your own records within 24 hours.',
                ], 403);
            }

            $produce->delete();

            return response()->json(['success' => true, 'message' => 'Record deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ── AJAX: get default unit for product type ────────────────────

    public function getDefaultUnit(string $type)
    {
        return response()->json([
            'unit' => FarmProduce::defaultUnit($type),
        ]);
    }
}