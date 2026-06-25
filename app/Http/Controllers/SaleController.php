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
    // ──────────────────────────────────────────────────────────────
    // PRODUCE TYPE → SALE TYPE MAPPING
    //
    // FarmProduce uses free-text types (e.g. "eggs", "live_bird")
    // Sale uses specific subtypes (e.g. "eggs_tray", "live_bird").
    // This map tells us which produce type covers which sale types,
    // so we can check available stock correctly.
    //
    // Key   = produce product_type (from FarmProduce records)
    // Value = array of sale product_types that consume that produce
    // ──────────────────────────────────────────────────────────────
    private const PRODUCE_TO_SALE_MAP = [
        'eggs'           => ['eggs_tray', 'eggs_crate', 'eggs_box', 'eggs'],
        'live_bird'      => ['live_bird'],
        'meat'           => ['meat_kg', 'meat'],
        'breeding_stock' => ['breeding_stock'],
        'manure'         => ['manure'],
    ];

    // Reverse: given a sale product_type, which produce type feeds it?
    private const SALE_TO_PRODUCE_MAP = [
        'eggs_tray'      => 'eggs',
        'eggs_crate'     => 'eggs',
        'eggs_box'       => 'eggs',
        'eggs'           => 'eggs',
        'live_bird'      => 'live_bird',
        'meat_kg'        => 'meat',
        'meat'           => 'meat',
        'breeding_stock' => 'breeding_stock',
        'manure'         => 'manure',
    ];

    // Egg unit conversions — how many individual eggs per sale unit
    private const EGG_UNIT_SIZES = [
        'eggs_tray'  => 30,
        'eggs_crate' => 360,  // 12 trays × 30
        'eggs_box'   => 360,
        'eggs'       => 1,
    ];

    // ──────────────────────────────────────────────────────────────
    // INDEX
    // ──────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $flockId     = $request->get('flock_id');
        $productType = $request->get('product_type');
        $startDate   = $request->get('start_date', Carbon::now()->startOfMonth());
        $endDate     = $request->get('end_date', Carbon::now());

        $query = Sale::with(['flock', 'creator']);

        if ($flockId)     $query->where('flock_id', $flockId);
        if ($productType) $query->where('product_type', $productType);

        $sales = $query->whereBetween('sale_date', [$startDate, $endDate])
            ->orderBy('sale_date', 'desc')
            ->paginate(20);

        $flocks       = Flock::where('status', 'active')->get();
        $productTypes = Sale::distinct()->pluck('product_type');

        $totalRevenue  = $sales->sum('total_amount');
        $totalQuantity = $sales->sum('quantity');

        // Real remaining-stock figures per SALE product_type (eggs_tray,
        // eggs_crate, live_bird, meat_kg, etc.), derived from the same
        // produce-based availability map used when validating new sales.
        // Replaces the old hardcoded/null capacity guesses in the view.
        $saleAvailability = $this->buildSaleTypeAvailability();

        return view('sales.index', compact(
            'sales', 'flocks', 'productTypes',
            'totalRevenue', 'totalQuantity', 'saleAvailability',
            'flockId', 'productType', 'startDate', 'endDate'
        ));
    }

    // ──────────────────────────────────────────────────────────────
    // CREATE FORM DATA (AJAX)
    //
    // Only surfaces product types that actually have produce stock.
    // Includes real-time available quantity for each type so the
    // frontend can show "X units available" in the form.
    // ──────────────────────────────────────────────────────────────
    public function getCreateForm()
    {
        try {
            $flocks = Flock::where('status', 'active')->get(['id', 'flock_number', 'breed_variety']);

            // Build availability map from FarmProduce
            $availability = $this->buildAvailabilityMap();

            // Only offer product types that have something to sell
            $productTypes = collect(self::PRODUCE_TO_SALE_MAP)
                ->flatMap(function ($saleTypes, $produceType) use ($availability) {
                    $available = $availability[$produceType]['remaining'] ?? 0;
                    if ($available <= 0) return [];

                    return collect($saleTypes)->map(fn($st) => [
                        'value'     => $st,
                        'label'     => $this->saleTypeLabel($st),
                        'available' => $this->convertProduceToSaleUnits($available, $st, $produceType),
                        'unit'      => $this->saleTypeUnit($st),
                    ]);
                })
                ->values();

            // Also include any sale types already recorded (for legacy/manual entries)
            $existingSaleTypes = Sale::distinct()->pluck('product_type');
            $existingSaleTypes->each(function ($type) use (&$productTypes, $availability) {
                if (!$productTypes->firstWhere('value', $type)) {
                    $productTypes->push([
                        'value'     => $type,
                        'label'     => $this->saleTypeLabel($type),
                        'available' => null, // unknown — no produce record
                        'unit'      => $this->saleTypeUnit($type),
                    ]);
                }
            });

            return response()->json([
                'success'      => true,
                'flocks'       => $flocks,
                'productTypes' => $productTypes,
                'availability' => $availability,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────────
    // STORE (AJAX)
    //
    // Validates stock availability before saving.
    // ──────────────────────────────────────────────────────────────
    public function storeSaleAjax(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'product_type'   => 'required|string|max:50',
                'quantity'       => 'required|numeric|min:0.01',
                'unit_price'     => 'required|numeric|min:0.01',
                'total_amount'   => 'required|numeric|min:0',
                'sale_date'      => 'required|date|before_or_equal:today',
                'flock_id'       => 'nullable|exists:flocks,id',
                'customer_name'  => 'nullable|string|max:255',
                'payment_method' => 'nullable|string|max:50',
                'receipt_number' => 'nullable|string|max:100',
                'description'    => 'nullable|string|max:255',
                'notes'          => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
            }

            // ── Stock availability check ───────────────────────────
            $stockCheck = $this->checkStockAvailability(
                $request->product_type,
                $request->quantity,
                $request->flock_id,
                null  // no sale to exclude (new record)
            );

            if (!$stockCheck['ok']) {
                return response()->json([
                    'success' => false,
                    'message' => $stockCheck['message'],
                ], 422);
            }

            $sale = Sale::create([
                'flock_id'       => $request->flock_id,
                'product_type'   => $request->product_type,
                'quantity'       => $request->quantity,
                'unit_price'     => $request->unit_price,
                'total_amount'   => $request->total_amount,
                'sale_date'      => $request->sale_date,
                'customer_name'  => $request->customer_name,
                'payment_method' => $request->payment_method,
                'receipt_number' => $request->receipt_number,
                'description'    => $request->description,
                'notes'          => $request->notes,
                'created_by'     => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Sale recorded successfully',
                'sale_id' => $sale->id,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────────
    // SALE DETAILS JSON (for view modal)
    // ──────────────────────────────────────────────────────────────
    public function getDetailsJson($id)
    {
        try {
            $sale = Sale::with(['flock', 'creator'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'sale'    => [
                    'id'             => $sale->id,
                    'sale_date'      => $sale->sale_date->format('d M Y'),
                    'product_type'   => $sale->product_type_label,
                    'product_type_key' => $sale->product_type,
                    'quantity'       => number_format($sale->quantity, 2),
                    'unit_price'     => number_format($sale->unit_price, 2),
                    'total_amount'   => number_format($sale->total_amount, 2),
                    'customer_name'  => $sale->customer_name ?? 'N/A',
                    'payment_method' => $sale->payment_method
                        ? ucfirst(str_replace('_', ' ', $sale->payment_method))
                        : 'N/A',
                    'receipt_number' => $sale->receipt_number ?? 'N/A',
                    'description'    => $sale->description ?? 'N/A',
                    'flock_number'   => $sale->flock->flock_number ?? null,
                    'flock_breed'    => $sale->flock->breed_variety ?? null,
                    'notes'          => $sale->notes,
                    'recorded_by'    => $sale->creator->name ?? 'N/A',
                    'created_at'     => $sale->created_at->format('d M Y H:i'),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────────
    // EDIT DATA (for edit modal)
    // ──────────────────────────────────────────────────────────────
    public function getEditData($id)
    {
        try {
            $sale   = Sale::findOrFail($id);
            $flocks = Flock::where('status', 'active')->get(['id', 'flock_number', 'breed_variety']);

            // Build availability for the edit form — include the current
            // sale's own quantity so it doesn't block editing
            $availability = $this->buildAvailabilityMap(excludeSaleId: $id);

            $productTypes = collect(self::PRODUCE_TO_SALE_MAP)
                ->flatMap(function ($saleTypes, $produceType) use ($availability) {
                    return collect($saleTypes)->map(fn($st) => [
                        'value'     => $st,
                        'label'     => $this->saleTypeLabel($st),
                        'available' => $this->convertProduceToSaleUnits(
                            $availability[$produceType]['remaining'] ?? 0, $st, $produceType
                        ),
                        'unit'      => $this->saleTypeUnit($st),
                    ]);
                })
                ->values();

            // Always include the current sale's type even if stock is 0
            if (!$productTypes->firstWhere('value', $sale->product_type)) {
                $productTypes->push([
                    'value'     => $sale->product_type,
                    'label'     => $this->saleTypeLabel($sale->product_type),
                    'available' => null,
                    'unit'      => $this->saleTypeUnit($sale->product_type),
                ]);
            }

            return response()->json([
                'success'      => true,
                'sale'         => [
                    'id'             => $sale->id,
                    'product_type'   => $sale->product_type,
                    'quantity'       => $sale->quantity,
                    'unit_price'     => $sale->unit_price,
                    'total_amount'   => $sale->total_amount,
                    'sale_date'      => $sale->sale_date->format('Y-m-d'),
                    'flock_id'       => $sale->flock_id,
                    'customer_name'  => $sale->customer_name,
                    'payment_method' => $sale->payment_method,
                    'receipt_number' => $sale->receipt_number,
                    'description'    => $sale->description,
                    'notes'          => $sale->notes,
                ],
                'flocks'       => $flocks,
                'productTypes' => $productTypes,
                'availability' => $availability,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────────
    // UPDATE (AJAX)
    //
    // Same stock check as store, but excludes the current sale's
    // own quantity so editing doesn't block itself.
    // ──────────────────────────────────────────────────────────────
    public function updateSaleAjax(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'product_type'   => 'required|string|max:50',
                'quantity'       => 'required|numeric|min:0.01',
                'unit_price'     => 'required|numeric|min:0.01',
                'total_amount'   => 'required|numeric|min:0',
                'sale_date'      => 'required|date',
                'flock_id'       => 'nullable|exists:flocks,id',
                'customer_name'  => 'nullable|string|max:255',
                'payment_method' => 'nullable|string|max:50',
                'receipt_number' => 'nullable|string|max:100',
                'description'    => 'nullable|string|max:255',
                'notes'          => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
            }

            // ── Stock check — exclude this sale's current quantity ──
            $stockCheck = $this->checkStockAvailability(
                $request->product_type,
                $request->quantity,
                $request->flock_id,
                $id  // exclude current sale from "already sold" count
            );

            if (!$stockCheck['ok']) {
                return response()->json([
                    'success' => false,
                    'message' => $stockCheck['message'],
                ], 422);
            }

            $sale = Sale::findOrFail($id);
            $sale->update($request->all());

            return response()->json(['success' => true, 'message' => 'Sale updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────────
    // DELETE
    // ──────────────────────────────────────────────────────────────
    public function destroy($id)
    {
        $sale = Sale::findOrFail($id);
        $sale->delete();

        return redirect()->route('sales.index')->with('success', 'Sale deleted successfully');
    }

    // ──────────────────────────────────────────────────────────────
    // BY PRODUCT TYPE VIEW
    // ──────────────────────────────────────────────────────────────
    public function byProductType(Request $request)
    {
        $year = $request->get('year', Carbon::now()->year);

        $salesByProduct = Sale::select(
                'product_type',
                DB::raw('SUM(quantity) as total_quantity'),
                DB::raw('SUM(total_amount) as total_revenue')
            )
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

        $totalRevenue  = $salesByProduct->sum('total_revenue');
        $totalExpenses = \App\Models\Expense::whereYear('expense_date', $year)->sum('amount');
        $netProfit     = $totalRevenue - $totalExpenses;

        return view('sales.by-product', compact(
            'salesByProduct', 'monthlyBreakdown',
            'totalRevenue', 'totalExpenses', 'netProfit', 'year'
        ));
    }

    // ══════════════════════════════════════════════════════════════
    // PRIVATE HELPERS
    // ══════════════════════════════════════════════════════════════

    /**
     * Build a complete availability map keyed by produce product_type.
     *
     * Returns:
     *   [
     *     'eggs' => [
     *       'produced'  => 1200.00,
     *       'damaged'   => 24.00,
     *       'available' => 1176.00,
     *       'sold'      => 540.00,   // in produce units (individual eggs)
     *       'remaining' => 636.00,
     *     ],
     *     ...
     *   ]
     *
     * @param int|null $excludeSaleId  Exclude a specific sale from the "sold"
     *                                 count (used when editing that sale).
     */
    private function buildAvailabilityMap(?int $excludeSaleId = null): array
    {
        // Total produced & damaged per produce type
        $produced = FarmProduce::select(
                'product_type',
                DB::raw('SUM(quantity) as total_produced'),
                DB::raw('SUM(COALESCE(quantity_damaged, 0)) as total_damaged')
            )
            ->groupBy('product_type')
            ->get()
            ->keyBy('product_type');

        $map = [];

        foreach (self::PRODUCE_TO_SALE_MAP as $produceType => $saleTypes) {
            $row       = $produced[$produceType] ?? null;
            $available = $row ? max(0, (float)$row->total_produced - (float)$row->total_damaged) : 0;

            // Sum all sales that consume this produce type, converting back
            // to produce units so the comparison is apples-to-apples
            $soldInProduceUnits = 0;
            foreach ($saleTypes as $saleType) {
                $saleQty = Sale::where('product_type', $saleType)
                    ->when($excludeSaleId, fn($q) => $q->where('id', '!=', $excludeSaleId))
                    ->sum('quantity');

                $soldInProduceUnits += $this->convertSaleToProduceUnits(
                    (float)$saleQty, $saleType, $produceType
                );
            }

            $map[$produceType] = [
                'produced'  => $row ? (float)$row->total_produced : 0,
                'damaged'   => $row ? (float)$row->total_damaged  : 0,
                'available' => $available,
                'sold'      => $soldInProduceUnits,
                'remaining' => max(0, $available - $soldInProduceUnits),
            ];
        }

        return $map;
    }

    /**
     * Check whether enough stock exists to record this sale.
     *
     * For egg-type sales we convert everything to individual eggs so that
     * selling 1 crate (360 eggs) correctly depletes against the egg produce
     * record regardless of whether eggs were recorded in pieces, trays, etc.
     *
     * For other types (live_bird, meat, manure, breeding_stock) quantities
     * are compared directly.
     */
    private function checkStockAvailability(
        string $saleType,
        float  $saleQty,
        ?int   $flockId,
        ?int   $excludeSaleId
    ): array {
        $produceType = self::SALE_TO_PRODUCE_MAP[$saleType] ?? null;

        // If we don't recognise the sale type, allow it through with a warning
        if (!$produceType) {
            return ['ok' => true, 'message' => ''];
        }

        $availability = $this->buildAvailabilityMap($excludeSaleId);
        $info         = $availability[$produceType] ?? null;

        if (!$info || $info['produced'] == 0) {
            return [
                'ok'      => false,
                'message' => "No produce records found for '{$produceType}'. "
                           . "Please record produce before making a sale.",
            ];
        }

        // Convert the sale quantity into produce units for comparison
        $saleInProduceUnits = $this->convertSaleToProduceUnits($saleQty, $saleType, $produceType);
        $remaining          = $info['remaining'];

        if ($saleInProduceUnits > $remaining) {
            $humanRemaining = $this->convertProduceToSaleUnits($remaining, $saleType, $produceType);
            $label          = $this->saleTypeLabel($saleType);
            $unit           = $this->saleTypeUnit($saleType);

            return [
                'ok'      => false,
                'message' => "Insufficient stock for {$label}. "
                           . "Available: " . number_format($humanRemaining, 2) . " {$unit}. "
                           . "You are trying to sell " . number_format($saleQty, 2) . " {$unit}.",
            ];
        }

        return ['ok' => true, 'message' => ''];
    }

    /**
     * Build a "remaining stock" figure for every known SALE product type
     * (not produce type), expressed in that sale type's own units
     * (e.g. trays, crates, birds, kg). Used by the Sales index stat-card
     * modals to show accurate "Stock Available" figures instead of guesses.
     *
     * Returns: [ 'eggs_tray' => 42.0, 'live_bird' => 180.0, ... ]
     */
    private function buildSaleTypeAvailability(): array
    {
        $availability = $this->buildAvailabilityMap();
        $result = [];

        foreach (self::SALE_TO_PRODUCE_MAP as $saleType => $produceType) {
            $remaining = $availability[$produceType]['remaining'] ?? 0;
            $result[$saleType] = $this->convertProduceToSaleUnits($remaining, $saleType, $produceType);
        }

        return $result;
    }

    /**
     * Convert a sale quantity into the equivalent produce quantity.
     *
     * Example: 2 trays → 60 individual eggs (2 × 30)
     * Example: 5 live birds → 5 birds
     */
    private function convertSaleToProduceUnits(float $saleQty, string $saleType, string $produceType): float
    {
        if ($produceType === 'eggs') {
            $unitSize = self::EGG_UNIT_SIZES[$saleType] ?? 1;
            return $saleQty * $unitSize;
        }

        // For all other types, quantities are directly comparable
        return $saleQty;
    }

    /**
     * Convert a produce quantity into the equivalent sale quantity.
     *
     * Example: 360 individual eggs → 12 trays
     * Example: 10 birds → 10 live_bird
     */
    private function convertProduceToSaleUnits(float $produceQty, string $saleType, string $produceType): float
    {
        if ($produceType === 'eggs') {
            $unitSize = self::EGG_UNIT_SIZES[$saleType] ?? 1;
            return $unitSize > 0 ? $produceQty / $unitSize : $produceQty;
        }

        return $produceQty;
    }

    /**
     * Human-readable label for a sale product type.
     */
    private function saleTypeLabel(string $type): string
    {
        $labels = [
            'eggs_tray'      => 'Eggs (Tray — 30 eggs)',
            'eggs_crate'     => 'Eggs (Crate — 360 eggs)',
            'eggs_box'       => 'Eggs (Box — 360 eggs)',
            'eggs'           => 'Eggs',
            'live_bird'      => 'Live Bird',
            'meat_kg'        => 'Meat (per kg)',
            'meat'           => 'Meat',
            'breeding_stock' => 'Breeding Stock',
            'manure'         => 'Manure',
            'other'          => 'Other',
        ];

        return $labels[$type] ?? ucfirst(str_replace('_', ' ', $type));
    }

    /**
     * Unit label for a sale product type.
     */
    private function saleTypeUnit(string $type): string
    {
        $units = [
            'eggs_tray'      => 'trays',
            'eggs_crate'     => 'crates',
            'eggs_box'       => 'boxes',
            'eggs'           => 'eggs',
            'live_bird'      => 'birds',
            'meat_kg'        => 'kg',
            'meat'           => 'kg',
            'breeding_stock' => 'animals',
            'manure'         => 'bags',
        ];

        return $units[$type] ?? 'units';
    }
}