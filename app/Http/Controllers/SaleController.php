<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Flock;
use App\Models\FarmProduce;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SaleController extends Controller
{
    // ──────────────────────────────────────────────────────────────
    // PRODUCE TYPE → SALE TYPE MAPPING (stock accounting — do not prune)
    //
    // Kept intentionally comprehensive, including legacy egg subtypes
    // (eggs_tray/eggs_crate/eggs_box), so that stock math for OLD sales
    // stays correct even though new sales no longer offer those types.
    // See NEW_SALE_OPTIONS_PER_PRODUCE below for what's actually offered
    // when creating a sale today.
    // ──────────────────────────────────────────────────────────────
    private const PRODUCE_TO_SALE_MAP = [
        'eggs'           => ['eggs_tray', 'eggs_crate', 'eggs_box', 'eggs'],
        'milk'           => ['milk'],
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
        'milk'           => 'milk',
        'live_bird'      => 'live_bird',
        'meat_kg'        => 'meat',
        'meat'           => 'meat',
        'breeding_stock' => 'breeding_stock',
        'manure'         => 'manure',
    ];

    // ──────────────────────────────────────────────────────────────
    // NEW SALE OPTIONS — what's offered in the "Record Sale" dropdown
    // going forward. Separate from PRODUCE_TO_SALE_MAP above so we can
    // simplify eggs down to a single "Eggs" choice for new sales without
    // breaking stock accounting for historical eggs_tray/crate/box sales.
    // ──────────────────────────────────────────────────────────────
    private const NEW_SALE_OPTIONS_PER_PRODUCE = [
        'eggs'           => ['eggs'],
        'milk'           => ['milk'],
        'live_bird'      => ['live_bird'],
        'meat'           => ['meat_kg'],
        'breeding_stock' => ['breeding_stock'],
        'manure'         => ['manure'],
    ];

    // Egg unit conversions — how many individual eggs per sale unit.
    // Still needed for editing/displaying OLD eggs_tray/crate/box sales.
    private const EGG_UNIT_SIZES = [
        'eggs_tray'  => 30,
        'eggs_crate' => 360,  // 12 trays × 30
        'eggs_box'   => 360,
        'eggs'       => 1,
    ];

    public function __construct(protected NotificationService $notifications) {}

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

        $saleAvailability = $this->buildSaleTypeAvailability($productTypes);

        return view('sales.index', compact(
            'sales', 'flocks', 'productTypes',
            'totalRevenue', 'totalQuantity', 'saleAvailability',
            'flockId', 'productType', 'startDate', 'endDate'
        ));
    }

    // ──────────────────────────────────────────────────────────────
    // CREATE FORM DATA (AJAX)
    //
    // Uses NEW_SALE_OPTIONS_PER_PRODUCE (not the full PRODUCE_TO_SALE_MAP)
    // so eggs shows up as a single "Eggs" choice instead of tray/crate/box
    // subtypes, while stock math underneath still accounts for all
    // historical variants correctly.
    // ──────────────────────────────────────────────────────────────
    public function getCreateForm()
    {
        try {
            $flocks = Flock::where('status', 'active')->get(['id', 'flock_number', 'breed_variety']);

            $availability = $this->buildAvailabilityMap();

            $productTypes = collect(self::NEW_SALE_OPTIONS_PER_PRODUCE)
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

            // Also include any OTHER sale types already recorded that aren't
            // in NEW_SALE_OPTIONS_PER_PRODUCE — e.g. a genuinely custom/
            // one-off product_type someone typed in manually. This is for
            // legacy/manual entries only, still validated correctly via
            // checkStockAvailability()'s fallback.
            //
            // IMPORTANT: legacy egg subtypes (eggs_tray/eggs_crate/eggs_box)
            // are deliberately excluded here. Even if old sales exist with
            // those types, the "Record Sale" dropdown must only ever offer
            // the single simplified "Eggs" option going forward — those
            // subtypes should only surface when editing the specific old
            // sale record that used them (handled separately in
            // getEditData()), never here.
            $legacyEggSubtypes = ['eggs_tray', 'eggs_crate', 'eggs_box'];

            $existingSaleTypes = Sale::distinct()->pluck('product_type');
            $existingSaleTypes->each(function ($type) use (&$productTypes, $availability, $legacyEggSubtypes) {
                if (in_array($type, $legacyEggSubtypes, true)) {
                    return;
                }
                if (!$productTypes->firstWhere('value', $type)) {
                    $productTypes->push([
                        'value'     => $type,
                        'label'     => $this->saleTypeLabel($type),
                        'available' => null,
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
    // REAL-TIME STOCK AVAILABILITY CHECK (AJAX)
    //
    // Powers the "X litres available" live indicator on the create/edit
    // sale forms as the user picks a flock + product type. This is a
    // *display* aid only — the authoritative, enforced check happens
    // server-side in checkStockAvailability() inside storeSaleAjax() /
    // updateSaleAjax(), so even if the client-side JS is bypassed, an
    // over-sell is still rejected on save.
    //
    // GET /sales/availability?flock_id=&product_type=&exclude_sale_id=
    // ──────────────────────────────────────────────────────────────
    public function getAvailability(Request $request)
    {
        try {
            $productType = $request->get('product_type');

            if (!$productType) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product type is required',
                ], 422);
            }

            $flockId       = $request->get('flock_id');
            $excludeSaleId = $request->get('exclude_sale_id');
            $produceType   = $this->resolveProduceType($productType);

            $availability = $this->buildAvailabilityMap($excludeSaleId, $flockId, [$produceType]);
            $info         = $availability[$produceType] ?? null;
            $remaining    = $info['remaining'] ?? 0;

            $availableInSaleUnits = $this->convertProduceToSaleUnits($remaining, $productType, $produceType);

            // Distinguish "no produce recorded at all" from "produced but sold out",
            // so the UI can nudge the user to record produce first when relevant.
            $hasProduceRecord = $info && $info['produced'] > 0;

            return response()->json([
                'success'           => true,
                'available'         => round($availableInSaleUnits, 2),
                'unit'              => $this->saleTypeUnit($productType),
                'label'             => $this->saleTypeLabel($productType),
                'has_stock'         => $availableInSaleUnits > 0,
                'has_produce_record'=> $hasProduceRecord,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────────
    // STORE (AJAX)
    //
    // receipt_number is always auto-generated from the new sale's own
    // id, ignoring any client-supplied value — guarantees a clean,
    // sequential, unique series (e.g. SALE-000123).
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
                'description'    => 'nullable|string|max:255',
                'notes'          => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
            }

            $stockCheck = $this->checkStockAvailability(
                $request->product_type,
                $request->quantity,
                $request->flock_id,
                null
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
                'receipt_number' => null, // set below, once we have a real id
                'description'    => $request->description,
                'notes'          => $request->notes,
                'created_by'     => auth()->id(),
            ]);

            $sale->receipt_number = $this->generateReceiptNumber($sale->id);
            $sale->save();

            $this->notifications->notifySaleRecorded(
                $sale->product_type,
                $sale->quantity,
                $sale->total_amount,
                auth()->user()->name
            );

            return response()->json([
                'success'        => true,
                'message'        => 'Sale recorded successfully',
                'sale_id'        => $sale->id,
                'receipt_number' => $sale->receipt_number,
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

            $availability = $this->buildAvailabilityMap(
                excludeSaleId: $id,
                flockId: $sale->flock_id
            );

            $productTypes = collect(self::NEW_SALE_OPTIONS_PER_PRODUCE)
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

            // Always include the current sale's own type, even if it's a
            // legacy variant (eggs_tray, etc.) no longer offered for new
            // sales, so editing an old record doesn't silently reassign it.
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
    // receipt_number is excluded from mass update — it's permanent once
    // assigned at creation and can never be changed via the edit form.
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
                'description'    => 'nullable|string|max:255',
                'notes'          => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
            }

            $stockCheck = $this->checkStockAvailability(
                $request->product_type,
                $request->quantity,
                $request->flock_id,
                $id
            );

            if (!$stockCheck['ok']) {
                return response()->json([
                    'success' => false,
                    'message' => $stockCheck['message'],
                ], 422);
            }

            $sale = Sale::findOrFail($id);
            $sale->update($request->except('receipt_number'));

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
     * Generate a sequential, human-readable sale/receipt number based on
     * the sale's own database id (e.g. SALE-000123). Using the id
     * guarantees global uniqueness with zero race-condition risk — MySQL's
     * auto-increment already serializes that for us — and the numbers stay
     * strictly increasing over time, so a lower number always means an
     * earlier sale, making it useful as a lookup reference ("Sale #123").
     */
    private function generateReceiptNumber(int $saleId): string
    {
        return 'SALE-' . str_pad((string) $saleId, 6, '0', STR_PAD_LEFT);
    }

    private function resolveProduceType(string $saleType): string
    {
        return self::SALE_TO_PRODUCE_MAP[$saleType] ?? $saleType;
    }

    private function buildAvailabilityMap(
        ?int $excludeSaleId = null,
        ?int $flockId = null,
        array $extraProduceTypes = []
    ): array {
        $produced = FarmProduce::select(
                'product_type',
                DB::raw('SUM(quantity) as total_produced'),
                DB::raw('SUM(COALESCE(quantity_damaged, 0)) as total_damaged')
            )
            ->when($flockId, fn($q) => $q->where('flock_id', $flockId))
            ->groupBy('product_type')
            ->get()
            ->keyBy('product_type');

        $produceTypesToBuild = array_unique(array_merge(
            array_keys(self::PRODUCE_TO_SALE_MAP),
            $extraProduceTypes
        ));

        $map = [];

        foreach ($produceTypesToBuild as $produceType) {
            $row       = $produced[$produceType] ?? null;
            $available = $row ? max(0, (float)$row->total_produced - (float)$row->total_damaged) : 0;

            $saleTypes = self::PRODUCE_TO_SALE_MAP[$produceType] ?? [$produceType];

            $soldInProduceUnits = 0;
            foreach ($saleTypes as $saleType) {
                $saleQty = Sale::where('product_type', $saleType)
                    ->when($flockId, fn($q) => $q->where('flock_id', $flockId))
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

    private function checkStockAvailability(
        string $saleType,
        float  $saleQty,
        ?int   $flockId,
        ?int   $excludeSaleId
    ): array {
        $produceType = $this->resolveProduceType($saleType);

        $availability = $this->buildAvailabilityMap($excludeSaleId, $flockId, [$produceType]);
        $info         = $availability[$produceType] ?? null;

        if (!$info || $info['produced'] == 0) {
            $scope = $flockId ? ' for this flock' : '';
            return [
                'ok'      => false,
                'message' => "No produce records found{$scope} for '{$produceType}'. "
                           . "Please record produce before making a sale.",
            ];
        }

        $saleInProduceUnits = $this->convertSaleToProduceUnits($saleQty, $saleType, $produceType);
        $remaining          = $info['remaining'];

        if ($saleInProduceUnits > $remaining) {
            $humanRemaining = $this->convertProduceToSaleUnits($remaining, $saleType, $produceType);
            $label          = $this->saleTypeLabel($saleType);
            $unit           = $this->saleTypeUnit($saleType);
            $scope          = $flockId ? ' for this flock' : '';

            return [
                'ok'      => false,
                'message' => "Insufficient stock{$scope} for {$label}. "
                           . "Available: " . number_format($humanRemaining, 2) . " {$unit}. "
                           . "You are trying to sell " . number_format($saleQty, 2) . " {$unit}.",
            ];
        }

        return ['ok' => true, 'message' => ''];
    }

    private function buildSaleTypeAvailability($extraSaleTypes = []): array
    {
        $allSaleTypes = array_unique(array_merge(
            array_keys(self::SALE_TO_PRODUCE_MAP),
            is_array($extraSaleTypes) ? $extraSaleTypes : $extraSaleTypes->toArray()
        ));

        $produceTypesNeeded = array_unique(array_map(
            fn($st) => $this->resolveProduceType($st),
            $allSaleTypes
        ));

        $availability = $this->buildAvailabilityMap(null, null, $produceTypesNeeded);
        $result = [];

        foreach ($allSaleTypes as $saleType) {
            $produceType = $this->resolveProduceType($saleType);
            $remaining   = $availability[$produceType]['remaining'] ?? 0;
            $result[$saleType] = $this->convertProduceToSaleUnits($remaining, $saleType, $produceType);
        }

        return $result;
    }

    private function convertSaleToProduceUnits(float $saleQty, string $saleType, string $produceType): float
    {
        if ($produceType === 'eggs') {
            $unitSize = self::EGG_UNIT_SIZES[$saleType] ?? 1;
            return $saleQty * $unitSize;
        }

        return $saleQty;
    }

    private function convertProduceToSaleUnits(float $produceQty, string $saleType, string $produceType): float
    {
        if ($produceType === 'eggs') {
            $unitSize = self::EGG_UNIT_SIZES[$saleType] ?? 1;
            return $unitSize > 0 ? $produceQty / $unitSize : $produceQty;
        }

        return $produceQty;
    }

    private function saleTypeLabel(string $type): string
    {
        $labels = [
            'eggs_tray'      => 'Eggs (Tray — 30 eggs)', // legacy display only
            'eggs_crate'     => 'Eggs (Crate — 360 eggs)', // legacy display only
            'eggs_box'       => 'Eggs (Box — 360 eggs)', // legacy display only
            'eggs'           => 'Eggs',
            'milk'           => 'Milk',
            'live_bird'      => 'Live Bird',
            'meat_kg'        => 'Meat (per kg)',
            'meat'           => 'Meat',
            'breeding_stock' => 'Breeding Stock',
            'manure'         => 'Manure',
            'other'          => 'Other',
        ];

        return $labels[$type] ?? ucfirst(str_replace('_', ' ', $type));
    }

    private function saleTypeUnit(string $type): string
    {
        $units = [
            'eggs_tray'      => 'trays',
            'eggs_crate'     => 'crates',
            'eggs_box'       => 'boxes',
            'eggs'           => 'pieces',
            'milk'           => 'litres',
            'live_bird'      => 'birds',
            'meat_kg'        => 'kg',
            'meat'           => 'kg',
            'breeding_stock' => 'animals',
            'manure'         => 'bags',
        ];

        return $units[$type] ?? 'units';
    }
}