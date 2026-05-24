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
    /**
     * Display a listing of daily logs
     */
    public function index(Request $request)
    {
        $flockId   = $request->get('flock_id');
        $startDate = $request->get('start_date', Carbon::now()->subDays(30));
        $endDate   = $request->get('end_date',   Carbon::now());

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
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();

        try {
            $flock = Flock::find($request->flock_id);

            // Prevent duplicate log for the same flock/date
            $existingLog = DailyLog::where('flock_id', $request->flock_id)
                ->where('log_date', $request->log_date)
                ->first();

            if ($existingLog) {
                return back()->with('error', 'A log already exists for this date. Please edit the existing log.');
            }

            $totalLoss = ($request->mortality_count ?? 0) + ($request->culling_count ?? 0);

            // Create the log
            $log = DailyLog::create([
                'flock_id'                 => $request->flock_id,
                'log_date'                 => $request->log_date,
                'mortality_count'          => $request->mortality_count          ?? 0,
                'culling_count'            => $request->culling_count            ?? 0,
                'eggs_collected'           => $request->eggs_collected           ?? 0,
                'eggs_damaged'             => $request->eggs_damaged             ?? 0,
                'feed_intake_kg'           => $request->feed_intake_kg,
                'water_consumption_liters' => $request->water_consumption_liters,
                'average_weight_kg'        => $request->average_weight_kg,
                'species_metrics'          => $request->species_metrics,
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

            // Auto-record egg produce if eggs were collected
            if (($request->eggs_collected ?? 0) > 0) {
                FarmProduce::create([
                    'flock_id'     => $flock->id,
                    'product_type' => 'eggs',
                    'quantity'     => $request->eggs_collected,
                    'unit'         => 'pieces',
                    'produce_date' => $request->log_date,
                    'notes'        => ($request->eggs_damaged ?? 0) > 0
                                        ? "Auto-recorded from daily log. Damaged: {$request->eggs_damaged}"
                                        : 'Auto-recorded from daily log',
                    'created_by'   => auth()->id(),
                ]);
            }

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

            return redirect()->route('daily-logs.show', $log)
                ->with('success', 'Daily log saved successfully');

        } catch (\Exception $e) {
            DB::rollBack();
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
            return back()->withErrors($validator)->withInput();
        }

        $dailyLog = DailyLog::findOrFail($id);

        DB::beginTransaction();

        try {
            $oldValues    = $dailyLog->toArray();
            $oldTotalLoss = $dailyLog->mortality_count + $dailyLog->culling_count;
            $newTotalLoss = ($request->mortality_count ?? 0) + ($request->culling_count ?? 0);
            $lossDiff     = $newTotalLoss - $oldTotalLoss;

            // Update the log
            $dailyLog->update([
                'mortality_count'          => $request->mortality_count          ?? 0,
                'culling_count'            => $request->culling_count            ?? 0,
                'eggs_collected'           => $request->eggs_collected           ?? 0,
                'eggs_damaged'             => $request->eggs_damaged             ?? 0,
                'feed_intake_kg'           => $request->feed_intake_kg,
                'water_consumption_liters' => $request->water_consumption_liters,
                'average_weight_kg'        => $request->average_weight_kg,
                'species_metrics'          => $request->species_metrics,
                'min_temperature_c'        => $request->min_temperature_c,
                'max_temperature_c'        => $request->max_temperature_c,
                'min_humidity'             => $request->min_humidity,
                'max_humidity'             => $request->max_humidity,
                'ammonia_ppm'              => $request->ammonia_ppm,
                'notes'                    => $request->notes,
            ]);

            // Adjust flock count if mortality/culling changed
            if ($lossDiff !== 0) {
                $flock = $dailyLog->flock;
                $flock->update([
                    'current_count' => $flock->current_count - $lossDiff,
                ]);
            }

            // Sync egg produce record with the updated log
            $this->syncEggProduce($dailyLog, $request);

            // Audit trail
            AuditHelper::log(
                'update',
                "Updated daily log for flock #{$dailyLog->flock->flock_number} on {$dailyLog->log_date}",
                'daily_log',
                $dailyLog->id,
                $oldValues,
                $dailyLog->toArray()
            );

            DB::commit();

            return redirect()->route('daily-logs.index')
                ->with('success', 'Daily log updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update daily log: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified daily log
     */
    public function destroy($id)
    {
        $dailyLog = DailyLog::findOrFail($id);

        DB::beginTransaction();

        try {
            $flockNumber = $dailyLog->flock->flock_number ?? 'Unknown';
            $logDate     = $dailyLog->log_date;

            // Restore flock count
            $totalLoss = $dailyLog->mortality_count + $dailyLog->culling_count;
            $flock     = $dailyLog->flock;
            $flock->update([
                'current_count' => $flock->current_count + $totalLoss,
            ]);

            // Remove the auto-created egg produce record if it exists
            FarmProduce::where('flock_id', $dailyLog->flock_id)
                ->where('product_type', 'eggs')
                ->whereDate('produce_date', $dailyLog->log_date)
                ->where('notes', 'like', '%Auto-recorded from daily log%')
                ->delete();

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

            return redirect()->route('daily-logs.index')
                ->with('success', 'Daily log deleted successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete daily log: ' . $e->getMessage());
        }
    }

    /**
     * Get log details as JSON for modal
     */
    public function getLogJson($id)
    {
        try {
            $log = DailyLog::with(['flock.species', 'creator'])->findOrFail($id);

            $totalLoss    = $log->mortality_count + $log->culling_count;
            $mortalityRate = $log->flock->current_count > 0
                ? round(($totalLoss / $log->flock->current_count) * 100, 2)
                : 0;

            return response()->json([
                'success' => true,
                'log'     => [
                    'id'                       => $log->id,
                    'log_date'                 => $log->log_date->format('d M Y'),
                    'flock_number'             => $log->flock->flock_number ?? 'N/A',
                    'species_name'             => $log->flock->species->name ?? 'N/A',
                    'recorded_by'              => $log->creator->name ?? 'N/A',
                    'mortality_count'          => $log->mortality_count,
                    'culling_count'            => $log->culling_count,
                    'total_loss'               => $totalLoss,
                    'mortality_rate'           => $mortalityRate,
                    'eggs_collected'           => $log->eggs_collected ?? 0,
                    'eggs_damaged'             => $log->eggs_damaged   ?? 0,
                    'feed_intake_kg'           => number_format($log->feed_intake_kg, 1),
                    'water_consumption_liters' => number_format($log->water_consumption_liters, 1),
                    'average_weight_kg'        => $log->average_weight_kg
                                                    ? number_format($log->average_weight_kg, 2)
                                                    : 'N/A',
                    'min_temp'                 => $log->min_temperature_c ?? 'N/A',
                    'max_temp'                 => $log->max_temperature_c ?? 'N/A',
                    'min_humidity'             => $log->min_humidity      ?? 'N/A',
                    'max_humidity'             => $log->max_humidity      ?? 'N/A',
                    'ammonia_ppm'              => $log->ammonia_ppm       ?? 'N/A',
                    'notes'                    => $log->notes,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ── Private helpers ────────────────────────────────────────────

    /**
     * Sync the egg FarmProduce record when a daily log is updated.
     * Creates, updates, or deletes the produce record to match the log.
     */
    private function syncEggProduce(DailyLog $log, Request $request): void
    {
        $eggsCollected = $request->eggs_collected ?? 0;
        $eggsDamaged   = $request->eggs_damaged   ?? 0;

        // Find any existing auto-created produce record for this flock/date
        $existing = FarmProduce::where('flock_id', $log->flock_id)
            ->where('product_type', 'eggs')
            ->whereDate('produce_date', $log->log_date)
            ->where('notes', 'like', '%Auto-recorded from daily log%')
            ->first();

        if ($eggsCollected > 0) {
            $notes = $eggsDamaged > 0
                ? "Auto-recorded from daily log. Damaged: {$eggsDamaged}"
                : 'Auto-recorded from daily log';

            if ($existing) {
                // Update the existing record
                $existing->update([
                    'quantity' => $eggsCollected,
                    'notes'    => $notes,
                ]);
            } else {
                // Create a new one (worker may have added eggs on edit)
                FarmProduce::create([
                    'flock_id'     => $log->flock_id,
                    'product_type' => 'eggs',
                    'quantity'     => $eggsCollected,
                    'unit'         => 'pieces',
                    'produce_date' => $log->log_date,
                    'notes'        => $notes,
                    'created_by'   => auth()->id(),
                ]);
            }
        } else {
            // Eggs set to 0 on edit — remove the produce record if it exists
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