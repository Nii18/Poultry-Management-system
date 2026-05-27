<?php
// app/Http/Controllers/DailyLogController.php

namespace App\Http\Controllers;

use App\Models\DailyLog;
use App\Models\Flock;
use App\Models\FarmProduce;
use App\Models\Notification;
use App\Helpers\AuditHelper;
use App\Events\HighMortalityAlert;
use App\Events\AbnormalTemperatureAlert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DailyLogController extends Controller
{
    // ── Species group constants ──────────────────────────────────────────────
    const POULTRY = ['CH', 'QU', 'DK', 'GS', 'TK'];
    const DAIRY   = ['CT', 'GT', 'SH', 'BF'];
    const MEAT    = ['RB', 'PG'];

    /**
     * Display a listing of daily logs
     */
    public function index(Request $request)
    {
        $flockId   = $request->get('flock_id');
        $startDate = $request->get('start_date');
        $endDate   = $request->get('end_date');

        if ($startDate) {
            $startDate = Carbon::parse($startDate);
        } else {
            $startDate = Carbon::now()->subDays(30);
        }

        if ($endDate) {
            $endDate = Carbon::parse($endDate);
        } else {
            $endDate = Carbon::now();
        }

        $query = DailyLog::with(['flock.species', 'creator']);

        if ($flockId) {
            $query->where('flock_id', $flockId);
        }

        $logs = $query->whereBetween('log_date', [$startDate, $endDate])
            ->orderBy('log_date', 'desc')
            ->paginate(20);

        $flocks = Flock::where('status', 'active')->get();

        return view('daily-logs.index', compact(
            'logs', 'flocks', 'flockId', 'startDate', 'endDate'
        ));
    }

    /**
     * Show the form for creating a new daily log
     */
    public function create(Request $request)
    {
        $flockId = $request->get('flock_id');
        $flock   = null;

        if ($flockId) {
            $flock = Flock::with('species')->findOrFail($flockId);
        }

        $activeFlocks = Flock::where('status', 'active')
            ->with('species')
            ->orderBy('start_date', 'desc')
            ->get();

        return view('daily-logs.create', compact('activeFlocks', 'flock'));
    }

    /**
     * Store a newly created daily log
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'flock_id'                  => 'required|exists:flocks,id',
            'log_date'                  => 'required|date|before_or_equal:today',
            'mortality_count'           => 'nullable|integer|min:0',
            'culling_count'             => 'nullable|integer|min:0',
            'eggs_collected'            => 'nullable|numeric|min:0',
            'eggs_damaged'              => 'nullable|numeric|min:0',
            'feed_intake_kg'            => 'nullable|numeric|min:0',
            'water_consumption_liters'  => 'nullable|numeric|min:0',
            'average_weight_kg'         => 'nullable|numeric|min:0',
            'species_metrics'           => 'nullable|array',
            'min_temperature_c'         => 'nullable|numeric',
            'max_temperature_c'         => 'nullable|numeric',
            'min_humidity'              => 'nullable|numeric|between:0,100',
            'max_humidity'              => 'nullable|numeric|between:0,100',
            'ammonia_ppm'               => 'nullable|numeric|min:0',
            'notes'                     => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $flock       = Flock::with('species')->find($request->flock_id);
            $speciesCode = $flock->species->code ?? '';

            // Prevent duplicate log for the same flock/date
            $existingLog = DailyLog::where('flock_id', $request->flock_id)
                ->where('log_date', $request->log_date)
                ->first();

            if ($existingLog) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'A log already exists for this date. Please edit the existing log.'
                ], 422);
            }

            // ── Map the generic production fields into the right columns ──
            [$eggsCollected, $eggsDamaged, $speciesMetrics] =
                $this->resolveProductionFields($request, $speciesCode);

            $totalLoss = ($request->mortality_count ?? 0) + ($request->culling_count ?? 0);

            $log = DailyLog::create([
                'flock_id'                 => $request->flock_id,
                'log_date'                 => $request->log_date,
                'mortality_count'          => $request->mortality_count          ?? 0,
                'culling_count'            => $request->culling_count            ?? 0,
                'eggs_collected'           => $eggsCollected,
                'eggs_damaged'             => $eggsDamaged,
                'feed_intake_kg'           => $request->feed_intake_kg,
                'water_consumption_liters' => $request->water_consumption_liters,
                'average_weight_kg'        => $request->average_weight_kg,
                'species_metrics'          => $speciesMetrics ?: null,
                'min_temperature_c'        => $request->min_temperature_c,
                'max_temperature_c'        => $request->max_temperature_c,
                'min_humidity'             => $request->min_humidity,
                'max_humidity'             => $request->max_humidity,
                'ammonia_ppm'              => $request->ammonia_ppm,
                'notes'                    => $request->notes,
                'created_by'               => auth()->id(),
            ]);

            // Update flock current count
            $flock->update([
                'current_count' => $flock->current_count - $totalLoss,
            ]);

            // Sync with FarmProduce
            $this->syncFarmProduceFromDailyLog($log, $flock);

            // Audit trail
            AuditHelper::log(
                'create',
                "Added daily log for flock #{$flock->flock_number} on {$log->log_date}",
                'daily_log',
                $log->id,
                null,
                $log->toArray()
            );

            // Trigger alert checks
            $this->checkForAlerts($log, $flock);

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success'  => true,
                    'message'  => 'Daily log saved successfully',
                    'redirect' => route('daily-logs.index')
                ]);
            }

            return redirect()->route('daily-logs.index')
                ->with('success', 'Daily log saved successfully');

        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to save daily log: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Failed to save daily log: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified daily log
     */
    public function show($id)
    {
        $dailyLog = DailyLog::with(['flock.species', 'creator'])->findOrFail($id);

        $mortalityRate    = $dailyLog->mortality_rate;
        $temperatureRange = $dailyLog->temperature_range;

        return view('daily-logs.show', compact('dailyLog', 'mortalityRate', 'temperatureRange'));
    }

    /**
     * Show the form for editing the specified daily log
     */
    public function edit($id)
    {
        $dailyLog     = DailyLog::findOrFail($id);
        $activeFlocks = Flock::where('status', 'active')->get();

        return view('daily-logs.edit', compact('dailyLog', 'activeFlocks'));
    }

    /**
     * Update the specified daily log
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'mortality_count'          => 'nullable|integer|min:0',
            'culling_count'            => 'nullable|integer|min:0',
            'eggs_collected'           => 'nullable|numeric|min:0',
            'eggs_damaged'             => 'nullable|numeric|min:0',
            'feed_intake_kg'           => 'nullable|numeric|min:0',
            'water_consumption_liters' => 'nullable|numeric|min:0',
            'average_weight_kg'        => 'nullable|numeric|min:0',
            'species_metrics'          => 'nullable|array',
            'min_temperature_c'        => 'nullable|numeric',
            'max_temperature_c'        => 'nullable|numeric',
            'min_humidity'             => 'nullable|numeric|between:0,100',
            'max_humidity'             => 'nullable|numeric|between:0,100',
            'ammonia_ppm'              => 'nullable|numeric|min:0',
            'notes'                    => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $dailyLog = DailyLog::with('flock.species')->findOrFail($id);

        DB::beginTransaction();

        try {
            $oldValues    = $dailyLog->toArray();
            $oldTotalLoss = $dailyLog->mortality_count + $dailyLog->culling_count;
            $newTotalLoss = ($request->mortality_count ?? 0) + ($request->culling_count ?? 0);
            $lossDiff     = $newTotalLoss - $oldTotalLoss;

            $flock       = $dailyLog->flock;
            $speciesCode = $flock->species->code ?? '';

            // ── Map the generic production fields into the right columns ──
            [$eggsCollected, $eggsDamaged, $speciesMetrics] =
                $this->resolveProductionFields($request, $speciesCode);

            $dailyLog->update([
                'mortality_count'          => $request->mortality_count          ?? 0,
                'culling_count'            => $request->culling_count            ?? 0,
                'eggs_collected'           => $eggsCollected,
                'eggs_damaged'             => $eggsDamaged,
                'feed_intake_kg'           => $request->feed_intake_kg,
                'water_consumption_liters' => $request->water_consumption_liters,
                'average_weight_kg'        => $request->average_weight_kg,
                'species_metrics'          => $speciesMetrics ?: null,
                'min_temperature_c'        => $request->min_temperature_c,
                'max_temperature_c'        => $request->max_temperature_c,
                'min_humidity'             => $request->min_humidity,
                'max_humidity'             => $request->max_humidity,
                'ammonia_ppm'              => $request->ammonia_ppm,
                'notes'                    => $request->notes,
            ]);

            // Adjust flock count if mortality/culling changed
            if ($lossDiff !== 0) {
                $flock->update([
                    'current_count' => $flock->current_count - $lossDiff,
                ]);
            }

            // Reload to get updated attributes for sync
            $dailyLog->refresh();

            // Sync with FarmProduce
            $this->syncFarmProduceFromDailyLog($dailyLog, $flock);

            // Audit trail
            AuditHelper::log(
                'update',
                "Updated daily log for flock #{$flock->flock_number} on {$dailyLog->log_date}",
                'daily_log',
                $dailyLog->id,
                $oldValues,
                $dailyLog->toArray()
            );

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success'  => true,
                    'message'  => 'Daily log updated successfully',
                    'redirect' => route('daily-logs.index')
                ]);
            }

            return redirect()->route('daily-logs.index')
                ->with('success', 'Daily log updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update daily log: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Failed to update daily log: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified daily log
     */
    public function destroy($id)
    {
        try {
            $dailyLog = DailyLog::with('flock.species')->findOrFail($id);

            DB::beginTransaction();

            $flock       = $dailyLog->flock;
            $flockNumber = $flock->flock_number ?? 'Unknown';
            $logDate     = $dailyLog->log_date;
            $speciesCode = $flock->species->code ?? '';

            // Restore flock count
            $totalLoss = $dailyLog->mortality_count + $dailyLog->culling_count;
            $flock->update([
                'current_count' => $flock->current_count + $totalLoss,
            ]);

            // Delete the matching FarmProduce record for this species' product type
            $productType = $this->getProductTypeForSpecies($speciesCode);
            if ($productType) {
                FarmProduce::where('flock_id', $dailyLog->flock_id)
                    ->where('product_type', $productType)
                    ->whereDate('produce_date', $dailyLog->log_date)
                    ->delete();
            }

            // Audit trail
            AuditHelper::log(
                'delete',
                "Deleted daily log for flock #{$flockNumber} on {$logDate}",
                'daily_log',
                $dailyLog->id,
                $dailyLog->toArray(),
                null
            );

            $dailyLog->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Daily log deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete daily log: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get log details as JSON for modal (view + edit).
     * Returns species_code and a normalised production block.
     * Raw numeric values are returned so the edit form inputs are pre-filled correctly.
     */
    public function getLogJson($id)
    {
        try {
            $log = DailyLog::with(['flock.species', 'creator'])->findOrFail($id);

            $totalLoss     = $log->mortality_count + $log->culling_count;
            $mortalityRate = $log->flock->current_count > 0
                ? round(($totalLoss / $log->flock->current_count) * 100, 2)
                : 0;

            $speciesCode = $log->flock->species->code ?? '';
            $metrics     = $log->species_metrics ?? [];
            if (is_object($metrics)) {
                $metrics = (array) $metrics;
            }

            // Build a normalised production block based on species
            $production = null;

            if (in_array($speciesCode, self::POULTRY)) {
                $qty        = (float) ($log->eggs_collected ?? 0);
                $damaged    = (float) ($log->eggs_damaged   ?? 0);
                $production = [
                    'type'    => 'eggs',
                    'qty'     => $qty,
                    'damaged' => $damaged,
                    'net'     => max(0, $qty - $damaged),
                    'unit'    => 'pieces',
                ];
            } elseif (in_array($speciesCode, self::DAIRY)) {
                $qty        = (float) ($metrics['milk_litres']         ?? 0);
                $damaged    = (float) ($metrics['milk_litres_damaged']  ?? 0);
                $production = [
                    'type'    => 'milk',
                    'qty'     => $qty,
                    'damaged' => $damaged,
                    'net'     => max(0, $qty - $damaged),
                    'unit'    => 'litres',
                ];
            } elseif (in_array($speciesCode, self::MEAT)) {
                $qty        = (float) ($metrics['meat_kg'] ?? 0);
                $production = [
                    'type'    => 'meat',
                    'qty'     => $qty,
                    'damaged' => 0,
                    'net'     => $qty,
                    'unit'    => 'kg',
                ];
            } elseif ($speciesCode === 'BE') {
                $qty        = (float) ($metrics['honey_kg'] ?? 0);
                $production = [
                    'type'    => 'honey',
                    'qty'     => $qty,
                    'damaged' => 0,
                    'net'     => $qty,
                    'unit'    => 'kg',
                ];
            }

            return response()->json([
                'success'      => true,
                'species_code' => $speciesCode,
                'production'   => $production,
                'log'          => [
                    'id'                       => $log->id,
                    'log_date'                 => $log->log_date->format('d M Y'),
                    'flock_number'             => $log->flock->flock_number ?? 'N/A',
                    'species_name'             => $log->flock->species->name ?? 'N/A',
                    'recorded_by'              => $log->creator->name ?? 'N/A',
                    'mortality_count'          => $log->mortality_count,
                    'culling_count'            => $log->culling_count,
                    'total_loss'               => $totalLoss,
                    'mortality_rate'           => $mortalityRate,
                    // Raw numeric values so edit form number inputs pre-fill correctly
                    'feed_intake_kg'           => $log->feed_intake_kg,
                    'water_consumption_liters' => $log->water_consumption_liters,
                    'average_weight_kg'        => $log->average_weight_kg,
                    'min_temp'                 => $log->min_temperature_c,
                    'max_temp'                 => $log->max_temperature_c,
                    'min_humidity'             => $log->min_humidity,
                    'max_humidity'             => $log->max_humidity,
                    'ammonia_ppm'              => $log->ammonia_ppm,
                    'notes'                    => $log->notes,
                    'production'               => $production,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ── PRIVATE HELPERS ──────────────────────────────────────────────────────

    /**
     * Resolve the generic production form fields (eggs_collected / eggs_damaged)
     * into the correct database columns based on species code.
     *
     * The create/edit form always sends production quantity as `eggs_collected`
     * and damage as `eggs_damaged` regardless of species — this method maps them
     * to the right place so non-poultry data lands in species_metrics JSON.
     *
     * Returns [$eggsCollected, $eggsDamaged, $speciesMetrics]
     */
    private function resolveProductionFields(Request $request, string $speciesCode): array
    {
        $rawQty      = (float) ($request->eggs_collected ?? 0);
        $rawDamaged  = (float) ($request->eggs_damaged   ?? 0);
        $baseMetrics = is_array($request->species_metrics) ? $request->species_metrics : [];

        if (in_array($speciesCode, self::POULTRY)) {
            // Eggs live in dedicated columns
            return [$rawQty, $rawDamaged, $baseMetrics ?: null];
        }

        if (in_array($speciesCode, self::DAIRY)) {
            // Milk goes into species_metrics
            $baseMetrics['milk_litres']         = $rawQty;
            $baseMetrics['milk_litres_damaged'] = $rawDamaged;
            return [0, 0, $baseMetrics];
        }

        if (in_array($speciesCode, self::MEAT)) {
            // Meat / live weight goes into species_metrics
            $baseMetrics['meat_kg'] = $rawQty;
            return [0, 0, $baseMetrics];
        }

        if ($speciesCode === 'BE') {
            // Honey goes into species_metrics
            $baseMetrics['honey_kg'] = $rawQty;
            return [0, 0, $baseMetrics];
        }

        // No production for this species — pass through unchanged
        return [0, 0, $baseMetrics ?: null];
    }

    /**
     * Return the FarmProduce product_type string for a given species code,
     * or null if the species has no tracked production.
     */
    private function getProductTypeForSpecies(string $speciesCode): ?string
    {
        if (in_array($speciesCode, self::POULTRY)) return 'eggs';
        if (in_array($speciesCode, self::DAIRY))   return 'milk';
        if (in_array($speciesCode, self::MEAT))     return 'meat';
        if ($speciesCode === 'BE')                  return 'honey';
        return null;
    }

    /**
     * Sync the FarmProduce record when a daily log is created or updated.
     * Handles eggs, milk, meat, and honey — creates, updates, or deletes
     * the produce record to stay in sync with the log.
     */
    public function syncFarmProduceFromDailyLog(DailyLog $log, Flock $flock): void
    {
        $speciesCode = $flock->species->code ?? '';
        $metrics     = $log->species_metrics ?? [];
        if (is_object($metrics)) {
            $metrics = (array) $metrics;
        }

        $productType = $this->getProductTypeForSpecies($speciesCode);

        // No known production type for this species
        if (!$productType) {
            return;
        }

        // Determine qty, damaged, and unit based on product type
        switch ($productType) {
            case 'eggs':
                $qty     = (float) ($log->eggs_collected ?? 0);
                $damaged = (float) ($log->eggs_damaged   ?? 0);
                $unit    = 'pieces';
                break;

            case 'milk':
                $qty     = (float) ($metrics['milk_litres']         ?? 0);
                $damaged = (float) ($metrics['milk_litres_damaged']  ?? 0);
                $unit    = 'litres';
                break;

            case 'meat':
                $qty     = (float) ($metrics['meat_kg'] ?? 0);
                $damaged = 0;
                $unit    = 'kg';
                break;

            case 'honey':
                $qty     = (float) ($metrics['honey_kg'] ?? 0);
                $damaged = 0;
                $unit    = 'kg';
                break;

            default:
                return;
        }

        // Find any existing auto-created produce record for this flock/date/type
        $existing = FarmProduce::where('flock_id', $log->flock_id)
            ->where('product_type', $productType)
            ->whereDate('produce_date', $log->log_date)
            ->first();

        if ($qty > 0) {
            $notes = $damaged > 0
                ? "Auto-recorded from daily log. Damaged: {$damaged}"
                : 'Auto-recorded from daily log';

            if ($existing) {
                $existing->update([
                    'quantity'         => $qty,
                    'quantity_damaged' => $damaged,
                    'notes'            => $notes,
                ]);
            } else {
                FarmProduce::create([
                    'flock_id'         => $log->flock_id,
                    'product_type'     => $productType,
                    'quantity'         => $qty,
                    'quantity_damaged' => $damaged,
                    'unit'             => $unit,
                    'produce_date'     => $log->log_date,
                    'notes'            => $notes,
                    'created_by'       => auth()->id(),
                ]);
            }
        } else {
            // Quantity is zero — remove the produce record if it exists
            if ($existing) {
                $existing->delete();
            }
        }
    }

    /**
     * Check for alerts based on daily log data
     */
    private function checkForAlerts(DailyLog $log, Flock $flock): void
    {
        // High mortality check
        $dailyMortalityRate = ($log->mortality_count + $log->culling_count)
            / max($flock->current_count, 1) * 100;

        if ($dailyMortalityRate > 3) {
            Notification::create([
                'user_id'  => auth()->id(),
                'flock_id' => $flock->id,
                'type'     => 'high_mortality',
                'title'    => 'High Mortality Detected',
                'message'  => "High mortality rate of " . round($dailyMortalityRate, 2)
                              . "% detected in flock {$flock->flock_number}",
                'severity' => 'critical',
                'data'     => json_encode([
                    'flock_id'        => $flock->id,
                    'log_id'          => $log->id,
                    'mortality_rate'  => $dailyMortalityRate,
                    'mortality_count' => $log->mortality_count,
                    'culling_count'   => $log->culling_count,
                ]),
            ]);

            event(new HighMortalityAlert($flock, $log, $dailyMortalityRate));
        }

        // Temperature check
        $optimalTemp = $this->getOptimalTemperature(
            $flock->age_in_days,
            $flock->species->code
        );

        if ($log->max_temperature_c && $log->max_temperature_c > ($optimalTemp + 3)) {
            Notification::create([
                'user_id'  => auth()->id(),
                'flock_id' => $flock->id,
                'type'     => 'high_temperature',
                'title'    => 'Temperature Alert',
                'message'  => "High temperature detected: {$log->max_temperature_c}°C"
                              . " in house {$flock->house->name}",
                'severity' => 'warning',
                'data'     => json_encode([
                    'flock_id'    => $flock->id,
                    'temperature' => $log->max_temperature_c,
                    'optimal'     => $optimalTemp,
                ]),
            ]);

            event(new AbnormalTemperatureAlert($flock, $log, 'high'));
        }

        // Ammonia check
        if ($log->ammonia_ppm && $log->ammonia_ppm > 25) {
            Notification::create([
                'user_id'  => auth()->id(),
                'flock_id' => $flock->id,
                'type'     => 'high_ammonia',
                'title'    => 'High Ammonia Levels',
                'message'  => "High ammonia level of {$log->ammonia_ppm}ppm detected."
                              . " Risk of respiratory issues.",
                'severity' => 'warning',
                'data'     => json_encode([
                    'flock_id'    => $flock->id,
                    'ammonia_ppm' => $log->ammonia_ppm,
                ]),
            ]);
        }
    }

    /**
     * Get optimal temperature based on species and age
     */
    private function getOptimalTemperature(int $ageInDays, string $speciesCode): int
    {
        if ($speciesCode === 'CH') {
            if ($ageInDays <= 7)  return 32;
            if ($ageInDays <= 14) return 29;
            if ($ageInDays <= 21) return 26;
            if ($ageInDays <= 28) return 23;
            return 21;
        }

        return match ($speciesCode) {
            'PG'    => 22,
            'CT'    => 18,
            default => 22,
        };
    }
}