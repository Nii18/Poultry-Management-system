<?php
// app/Http/Controllers/FarmProduceController.php

namespace App\Http\Controllers;

use App\Models\FarmProduce;
use App\Models\Flock;
use App\Models\Sale;
use App\Models\DailyLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FarmProduceController extends Controller
{
    // ── Index ──────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $flockId     = $request->get('flock_id');
        $productType = $request->get('product_type');
        $startDate   = $request->get('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate     = $request->get('end_date', Carbon::now()->toDateString());

        $query = FarmProduce::with(['flock', 'creator']);

        if ($flockId)     $query->where('flock_id', $flockId);
        if ($productType) $query->where('product_type', $productType);

        $produces = $query
            ->whereBetween('produce_date', [$startDate, $endDate])
            ->orderBy('produce_date', 'desc')
            ->paginate(20)
            ->appends($request->query());

        $monthlyStats       = FarmProduce::getMonthlyStats();
        $allTimeStats       = FarmProduce::getAllTimeStats();
        $activeProductTypes = FarmProduce::getActiveProductTypes();

        $flocks   = Flock::where('status', 'active')->get();
        $totalQty = $produces->sum('quantity');

        return view('produces.index', compact(
            'produces', 'flocks', 'activeProductTypes',
            'monthlyStats', 'allTimeStats',
            'totalQty', 'flockId', 'productType', 'startDate', 'endDate'
        ));
    }

    // ── Inventory analytics ────────────────────────────────────────

    public function inventory(Request $request)
    {
        $flockId = $request->get('flock_id');
        $year    = (int) $request->get('year', Carbon::now()->year);

        $productTypes = FarmProduce::getActiveProductTypes();

        $inventory = $productTypes->map(function ($type) use ($flockId, $year) {
            $produceQ = FarmProduce::where('product_type', $type)
                ->whereYear('produce_date', $year)
                ->when($flockId, fn($q) => $q->where('flock_id', $flockId));

            $saleQ = Sale::where('product_type', $type)
                ->whereYear('sale_date', $year)
                ->when($flockId, fn($q) => $q->where('flock_id', $flockId));

            $produced  = (float) $produceQ->sum('quantity');
            $damaged   = (float) $produceQ->sum('quantity_damaged');
            $available = max(0, $produced - $damaged);
            $sold      = (float) $saleQ->sum('quantity');
            $remaining = max(0, $available - $sold);

            $monthlyProduce = (clone $produceQ)
                ->select(DB::raw('MONTH(produce_date) as month'), DB::raw('SUM(quantity) as total'))
                ->groupBy('month')->pluck('total', 'month');

            $monthlySales = (clone $saleQ)
                ->select(DB::raw('MONTH(sale_date) as month'), DB::raw('SUM(quantity) as total'))
                ->groupBy('month')->pluck('total', 'month');

            $monthly = [];
            for ($m = 1; $m <= 12; $m++) {
                $monthly[] = [
                    'month'    => Carbon::create()->month($m)->format('M'),
                    'produced' => (float) ($monthlyProduce[$m] ?? 0),
                    'sold'     => (float) ($monthlySales[$m] ?? 0),
                ];
            }

            return [
                'type'         => $type,
                'label'        => ucwords(str_replace('_', ' ', $type)),
                'icon'         => FarmProduce::productIcon($type),
                'unit'         => FarmProduce::defaultUnit($type),
                'produced'     => $produced,
                'damaged'      => $damaged,
                'available'    => $available,
                'sold'         => $sold,
                'remaining'    => $remaining,
                'sell_through' => $available > 0 ? round(($sold / $available) * 100, 1) : 0,
                'damage_rate'  => $produced > 0 ? round(($damaged / $produced) * 100, 1) : 0,
                'monthly'      => $monthly,
            ];
        });

        $recentEntries = FarmProduce::with(['flock', 'creator'])
            ->when($flockId, fn($q) => $q->where('flock_id', $flockId))
            ->whereYear('produce_date', $year)
            ->orderBy('produce_date', 'desc')
            ->take(15)
            ->get();

        $flocks = Flock::where('status', 'active')->get();
        $years  = range(Carbon::now()->year, max(Carbon::now()->year - 3, 2020));

        return view('produces.inventory', compact(
            'inventory', 'recentEntries', 'flocks',
            'flockId', 'year', 'years'
        ));
    }

    // ── AJAX: create form data ─────────────────────────────────────

    public function getCreateForm()
    {
        try {
            $flocks = Flock::where('status', 'active')
                ->get(['id', 'flock_number', 'breed_variety']);

            $existingTypes = FarmProduce::getActiveProductTypes();
            $suggestions   = ['eggs', 'milk', 'meat', 'live_bird', 'manure',
                              'breeding_stock', 'wool', 'honey'];
            $units         = ['pieces', 'trays', 'crates', 'litres', 'kg', 'bags', 'birds', 'units'];

            return response()->json([
                'success'       => true,
                'flocks'        => $flocks,
                'existingTypes' => $existingTypes,
                'suggestions'   => $suggestions,
                'units'         => $units,
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
                'product_type'     => 'required|string|max:50',
                'quantity'         => 'required|numeric|min:0.01',
                'quantity_damaged' => 'nullable|numeric|min:0',
                'unit'             => 'required|string|max:30',
                'produce_date'     => 'required|date|before_or_equal:today',
                'flock_id'         => 'nullable|exists:flocks,id',
                'notes'            => 'nullable|string|max:500',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                ], 422);
            }

            $productType = strtolower(str_replace(' ', '_', trim($request->product_type)));
            $damaged     = (float)($request->quantity_damaged ?? 0);

            if ($damaged > (float)$request->quantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Damaged quantity cannot exceed total quantity.',
                ], 422);
            }

            $produce = FarmProduce::create([
                'flock_id'         => $request->flock_id ?: null,
                'product_type'     => $productType,
                'quantity'         => $request->quantity,
                'quantity_damaged' => $damaged,
                'unit'             => $request->unit,
                'produce_date'     => $request->produce_date,
                'notes'            => $request->notes,
                'created_by'       => auth()->id(),
            ]);

            $this->syncDailyLogFromProduce($produce);

            return response()->json([
                'success'    => true,
                'message'    => 'Produce recorded successfully',
                'produce_id' => $produce->id,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ── AJAX: details JSON ────────────────────────────────────────

    public function getDetailsJson($id)
    {
        try {
            $produce = FarmProduce::with(['flock', 'creator'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'produce' => [
                    'id'                 => $produce->id,
                    'produce_date'       => $produce->produce_date->format('d M Y'),
                    'product_type'       => $produce->product_type,
                    'product_type_label' => $produce->product_type_label,
                    'quantity'           => number_format($produce->quantity, 2),
                    'quantity_damaged'   => number_format($produce->quantity_damaged, 2),
                    'net_quantity'       => number_format($produce->net_quantity, 2),
                    'unit'               => $produce->unit,
                    'flock_number'       => $produce->flock->flock_number ?? 'N/A',
                    'flock_breed'        => $produce->flock->breed_variety ?? null,
                    'notes'              => $produce->notes ?? '—',
                    'recorded_by'        => $produce->creator->name ?? 'N/A',
                    'created_at'         => $produce->created_at->format('d M Y H:i'),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ── AJAX: edit data ────────────────────────────────────────────

    public function getEditData($id)
    {
        try {
            $produce       = FarmProduce::findOrFail($id);
            $flocks        = Flock::where('status', 'active')->get(['id', 'flock_number', 'breed_variety']);
            $existingTypes = FarmProduce::getActiveProductTypes();
            $units         = ['pieces', 'trays', 'crates', 'litres', 'kg', 'bags', 'birds', 'units'];

            return response()->json([
                'success' => true,
                'produce' => [
                    'id'               => $produce->id,
                    'product_type'     => $produce->product_type,
                    'quantity'         => $produce->quantity,
                    'quantity_damaged' => $produce->quantity_damaged,
                    'unit'             => $produce->unit,
                    'produce_date'     => $produce->produce_date->format('Y-m-d'),
                    'flock_id'         => $produce->flock_id,
                    'notes'            => $produce->notes,
                ],
                'flocks'        => $flocks,
                'existingTypes' => $existingTypes,
                'units'         => $units,
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
                'product_type'     => 'required|string|max:50',
                'quantity'         => 'required|numeric|min:0.01',
                'quantity_damaged' => 'nullable|numeric|min:0',
                'unit'             => 'required|string|max:30',
                'produce_date'     => 'required|date',
                'flock_id'         => 'nullable|exists:flocks,id',
                'notes'            => 'nullable|string|max:500',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
            }

            $produce = FarmProduce::findOrFail($id);

            $damaged = (float)($request->quantity_damaged ?? 0);
            if ($damaged > (float)$request->quantity) {
                return response()->json(['success' => false, 'message' => 'Damaged qty cannot exceed total.'], 422);
            }

            $oldProductType = $produce->product_type;
            $oldFlockId     = $produce->flock_id;
            $oldDate        = $produce->produce_date->toDateString();

            $newProductType = strtolower(str_replace(' ', '_', trim($request->product_type)));
            $newFlockId     = $request->flock_id ?: null;
            $newDate        = $request->produce_date;

            $produce->update([
                'product_type'     => $newProductType,
                'quantity'         => $request->quantity,
                'quantity_damaged' => $damaged,
                'unit'             => $request->unit,
                'produce_date'     => $newDate,
                'flock_id'         => $newFlockId,
                'notes'            => $request->notes,
            ]);

            if ($this->isSyncedType($oldProductType) && $oldFlockId) {
                $locationChanged = ($oldFlockId != $newFlockId) || ($oldDate !== $newDate);
                if ($locationChanged) {
                    $this->clearDailyLogProduction($oldProductType, $oldFlockId, $oldDate);
                }
            }

            $produce->refresh();
            $this->syncDailyLogFromProduce($produce);

            return response()->json(['success' => true, 'message' => 'Produce record updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ── Delete ─────────────────────────────────────────────────────

    public function destroy($id)
    {
        try {
            $produce   = FarmProduce::findOrFail($id);
            $isAdmin   = in_array(auth()->user()->role, ['admin', 'manager']);
            $isCreator = $produce->created_by === auth()->id();
            $isRecent  = $produce->created_at->gt(now()->subHours(24));

            if (!$isAdmin && !($isCreator && $isRecent)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only delete your own records within 24 hours.',
                ], 403);
            }

            if ($this->isSyncedType($produce->product_type) && $produce->flock_id) {
                $this->clearDailyLogProduction(
                    $produce->product_type,
                    $produce->flock_id,
                    $produce->produce_date->toDateString()
                );
            }

            $produce->delete();

            return response()->json(['success' => true, 'message' => 'Record deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ── AJAX: default unit ─────────────────────────────────────────

    public function getDefaultUnit(string $type)
    {
        return response()->json(['unit' => FarmProduce::defaultUnit($type)]);
    }

    // ── AJAX: stat card detail ─────────────────────────────────────

    public function getStatCardDetail(Request $request, string $productType)
    {
        try {
            // ── Base queries ─────────────────────────────────────────────
            $allTimeQ  = FarmProduce::where('product_type', $productType);
            $monthQ    = (clone $allTimeQ)
                ->whereMonth('produce_date', now()->month)
                ->whereYear('produce_date',  now()->year);

            // ── Sales figures ────────────────────────────────────────────
            $allTimeSold   = (float) Sale::where('product_type', $productType)->sum('quantity');
            $monthSold     = (float) Sale::where('product_type', $productType)
                ->whereMonth('sale_date', now()->month)
                ->whereYear('sale_date',  now()->year)
                ->sum('quantity');
            $allTimeRevenue = (float) Sale::where('product_type', $productType)->sum('total_amount');
            $monthRevenue   = (float) Sale::where('product_type', $productType)
                ->whereMonth('sale_date', now()->month)
                ->whereYear('sale_date',  now()->year)
                ->sum('total_amount');

            // ── Aggregate totals ─────────────────────────────────────────
            $totalProduced  = (float) $allTimeQ->sum('quantity');
            $totalDamaged   = (float) $allTimeQ->sum('quantity_damaged');
            $totalAvailable = max(0, $totalProduced - $totalDamaged);
            $totalRemaining = max(0, $totalAvailable - $allTimeSold);

            $monthProduced  = (float) $monthQ->sum('quantity');
            $monthDamaged   = (float) $monthQ->sum('quantity_damaged');
            $monthAvailable = max(0, $monthProduced - $monthDamaged);
            $monthRemaining = max(0, $monthAvailable - $monthSold);

            // ── Species breakdown ────────────────────────────────────────
            // Only relevant for product types that can originate from multiple
            // species (e.g. 'meat' can be rabbit, pig, etc.).
            // We join farm_produces → flocks → species to group by species.
            $speciesBreakdown = [];

            $typesWithSpeciesBreakdown = ['meat', 'milk', 'eggs', 'honey'];

            if (in_array($productType, $typesWithSpeciesBreakdown)) {
                $rows = DB::table('farm_produces')
                    ->join('flocks',   'farm_produces.flock_id',   '=', 'flocks.id')
                    ->join('species',  'flocks.species_id',        '=', 'species.id')
                    ->where('farm_produces.product_type', $productType)
                    ->whereNotNull('farm_produces.flock_id')
                    ->select(
                        'species.id   as species_id',
                        'species.name as species_name',
                        'species.code as species_code',
                        DB::raw('SUM(farm_produces.quantity)         as total_produced'),
                        DB::raw('SUM(farm_produces.quantity_damaged) as total_damaged'),
                        DB::raw('COUNT(DISTINCT farm_produces.flock_id) as flock_count'),
                        DB::raw('COUNT(farm_produces.id)             as record_count')
                    )
                    ->groupBy('species.id', 'species.name', 'species.code')
                    ->orderByDesc('total_produced')
                    ->get();

                // Monthly figures per species
                $monthRows = DB::table('farm_produces')
                    ->join('flocks',  'farm_produces.flock_id',  '=', 'flocks.id')
                    ->join('species', 'flocks.species_id',       '=', 'species.id')
                    ->where('farm_produces.product_type', $productType)
                    ->whereNotNull('farm_produces.flock_id')
                    ->whereMonth('farm_produces.produce_date', now()->month)
                    ->whereYear('farm_produces.produce_date',  now()->year)
                    ->select(
                        'species.id as species_id',
                        DB::raw('SUM(farm_produces.quantity)         as month_produced'),
                        DB::raw('SUM(farm_produces.quantity_damaged) as month_damaged')
                    )
                    ->groupBy('species.id')
                    ->get()
                    ->keyBy('species_id');

                foreach ($rows as $row) {
                    $produced  = (float) $row->total_produced;
                    $damaged   = (float) $row->total_damaged;
                    $available = max(0, $produced - $damaged);

                    $mp = (float) ($monthRows[$row->species_id]->month_produced ?? 0);
                    $md = (float) ($monthRows[$row->species_id]->month_damaged  ?? 0);

                    $speciesBreakdown[] = [
                        'species_id'    => $row->species_id,
                        'species_name'  => $row->species_name,
                        'species_code'  => $row->species_code,
                        'flock_count'   => (int) $row->flock_count,
                        'record_count'  => (int) $row->record_count,
                        'all_time' => [
                            'produced'   => number_format($produced, 2),
                            'damaged'    => number_format($damaged, 2),
                            'available'  => number_format($available, 2),
                            'damage_pct' => $produced > 0
                                ? round(($damaged / $produced) * 100, 1) : 0,
                        ],
                        'this_month' => [
                            'produced'   => number_format($mp, 2),
                            'damaged'    => number_format($md, 2),
                            'available'  => number_format(max(0, $mp - $md), 2),
                            'damage_pct' => $mp > 0
                                ? round(($md / $mp) * 100, 1) : 0,
                        ],
                    ];
                }
            }

            // ── Flock-level breakdown (top contributors) ─────────────────
            $flockBreakdown = DB::table('farm_produces')
                ->join('flocks', 'farm_produces.flock_id', '=', 'flocks.id')
                ->leftJoin('species', 'flocks.species_id', '=', 'species.id')
                ->where('farm_produces.product_type', $productType)
                ->whereNotNull('farm_produces.flock_id')
                ->select(
                    'flocks.id          as flock_id',
                    'flocks.flock_number',
                    'flocks.breed_variety',
                    'species.name       as species_name',
                    'species.code       as species_code',
                    DB::raw('SUM(farm_produces.quantity)         as total_produced'),
                    DB::raw('SUM(farm_produces.quantity_damaged) as total_damaged'),
                    DB::raw('COUNT(farm_produces.id)             as record_count')
                )
                ->groupBy(
                    'flocks.id', 'flocks.flock_number', 'flocks.breed_variety',
                    'species.name', 'species.code'
                )
                ->orderByDesc('total_produced')
                ->limit(8)
                ->get()
                ->map(function ($row) {
                    $produced  = (float) $row->total_produced;
                    $damaged   = (float) $row->total_damaged;
                    $available = max(0, $produced - $damaged);
                    return [
                        'flock_number'  => $row->flock_number,
                        'breed_variety' => $row->breed_variety ?? '—',
                        'species_name'  => $row->species_name  ?? '—',
                        'species_code'  => $row->species_code  ?? '—',
                        'produced'      => number_format($produced, 2),
                        'damaged'       => number_format($damaged, 2),
                        'available'     => number_format($available, 2),
                        'record_count'  => (int) $row->record_count,
                        'damage_pct'    => $produced > 0
                            ? round(($damaged / $produced) * 100, 1) : 0,
                        'share_pct'     => 0, // calculated client-side from total_produced
                        'raw_produced'  => $produced,
                    ];
                })
                ->toArray();

            // ── Recent records ────────────────────────────────────────────
            $recentRecords = FarmProduce::with(['flock.species', 'creator'])
                ->where('product_type', $productType)
                ->orderBy('produce_date', 'desc')
                ->take(5)
                ->get()
                ->map(fn($r) => [
                    'date'         => $r->produce_date->format('d M Y'),
                    'quantity'     => number_format($r->quantity, 2),
                    'damaged'      => number_format($r->quantity_damaged, 2),
                    'net'          => number_format($r->net_quantity, 2),
                    'unit'         => $r->unit,
                    'flock'        => $r->flock->flock_number   ?? 'General',
                    'species_name' => $r->flock->species->name  ?? '—',
                    'species_code' => $r->flock->species->code  ?? '—',
                ]);

            return response()->json([
                'success'           => true,
                'product_type'      => $productType,
                'label'             => ucwords(str_replace('_', ' ', $productType)),
                'icon'              => FarmProduce::productIcon($productType),
                'has_species_breakdown' => count($speciesBreakdown) > 1,
                'species_breakdown' => $speciesBreakdown,
                'flock_breakdown'   => $flockBreakdown,
                'this_month'        => [
                    'produced'   => number_format($monthProduced, 2),
                    'damaged'    => number_format($monthDamaged, 2),
                    'available'  => number_format($monthAvailable, 2),
                    'sold'       => number_format($monthSold, 2),
                    'remaining'  => number_format($monthRemaining, 2),
                    'revenue'    => number_format($monthRevenue, 2),
                    'damage_pct' => $monthProduced > 0
                        ? round(($monthDamaged / $monthProduced) * 100, 1) : 0,
                ],
                'all_time'          => [
                    'produced'   => number_format($totalProduced, 2),
                    'damaged'    => number_format($totalDamaged, 2),
                    'available'  => number_format($totalAvailable, 2),
                    'sold'       => number_format($allTimeSold, 2),
                    'remaining'  => number_format($totalRemaining, 2),
                    'revenue'    => number_format($allTimeRevenue, 2),
                    'damage_pct' => $totalProduced > 0
                        ? round(($totalDamaged / $totalProduced) * 100, 1) : 0,
                ],
                'recent_records'    => $recentRecords,
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ═══════════════════════════════════════════════════════════════
    // SYNC HELPERS
    // ═══════════════════════════════════════════════════════════════

    private function isSyncedType(string $productType): bool
    {
        return in_array($productType, ['eggs', 'milk']);
    }

    public function syncDailyLogFromProduce(FarmProduce $produce): void
    {
        if (!$this->isSyncedType($produce->product_type) || !$produce->flock_id) {
            return;
        }

        $dailyLog = DailyLog::where('flock_id', $produce->flock_id)
            ->whereDate('log_date', $produce->produce_date)
            ->first();

        $note = '[Updated via Produce Module: ' . now()->format('d M H:i') . ']';

        if ($produce->product_type === 'eggs') {
            if ($dailyLog) {
                $dailyLog->update([
                    'eggs_collected' => $produce->quantity,
                    'eggs_damaged'   => $produce->quantity_damaged,
                    'notes'          => $dailyLog->notes
                        ? $dailyLog->notes . "\n" . $note
                        : $note,
                ]);
            } elseif ($produce->quantity > 0) {
                DailyLog::create([
                    'flock_id'        => $produce->flock_id,
                    'log_date'        => $produce->produce_date,
                    'eggs_collected'  => $produce->quantity,
                    'eggs_damaged'    => $produce->quantity_damaged,
                    'mortality_count' => 0,
                    'culling_count'   => 0,
                    'notes'           => 'Auto-created from Produce Module: ' . ($produce->notes ?? ''),
                    'created_by'      => auth()->id(),
                ]);
            }
        }

        if ($produce->product_type === 'milk') {
            if ($dailyLog) {
                $metrics = $dailyLog->species_metrics ?? [];
                if (is_object($metrics)) $metrics = (array) $metrics;
                $metrics['milk_litres']         = $produce->quantity;
                $metrics['milk_litres_damaged'] = $produce->quantity_damaged;
                $dailyLog->update([
                    'species_metrics' => $metrics,
                    'notes'           => $dailyLog->notes
                        ? $dailyLog->notes . "\n" . $note
                        : $note,
                ]);
            } elseif ($produce->quantity > 0) {
                DailyLog::create([
                    'flock_id'        => $produce->flock_id,
                    'log_date'        => $produce->produce_date,
                    'mortality_count' => 0,
                    'culling_count'   => 0,
                    'species_metrics' => [
                        'milk_litres'         => $produce->quantity,
                        'milk_litres_damaged' => $produce->quantity_damaged,
                    ],
                    'notes'      => 'Auto-created from Produce Module: ' . ($produce->notes ?? ''),
                    'created_by' => auth()->id(),
                ]);
            }
        }
    }

    private function clearDailyLogProduction(string $productType, int $flockId, string $date): void
    {
        $dailyLog = DailyLog::where('flock_id', $flockId)
            ->whereDate('log_date', $date)
            ->first();

        if (!$dailyLog) return;

        $note = '[Production record removed via Produce Module: ' . now()->format('d M H:i') . ']';

        if ($productType === 'eggs') {
            $dailyLog->update([
                'eggs_collected' => 0,
                'eggs_damaged'   => 0,
                'notes'          => $dailyLog->notes ? $dailyLog->notes . "\n" . $note : $note,
            ]);
        }

        if ($productType === 'milk') {
            $metrics = $dailyLog->species_metrics ?? [];
            if (is_object($metrics)) $metrics = (array) $metrics;
            unset($metrics['milk_litres'], $metrics['milk_litres_damaged']);
            $dailyLog->update([
                'species_metrics' => $metrics,
                'notes'           => $dailyLog->notes ? $dailyLog->notes . "\n" . $note : $note,
            ]);
        }
    }

    // ── Legacy alias ───────────────────────────────────────────────

    public function syncDailyLogFromEggs(FarmProduce $produce, Request $request): void
    {
        $this->syncDailyLogFromProduce($produce);
    }
}