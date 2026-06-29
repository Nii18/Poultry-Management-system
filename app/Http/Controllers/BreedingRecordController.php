<?php

namespace App\Http\Controllers;

use App\Models\BreedingRecord;
use App\Models\Flock;
use App\Models\OffspringRecord;
use App\Services\NotificationService;
use App\Helpers\AuditHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BreedingRecordController extends Controller
{
    public function __construct(protected NotificationService $notifications) {}

    // ═══════════════════════════════════════════════════════════════════════════
    // INDEX
    // ═══════════════════════════════════════════════════════════════════════════

    public function index(Request $request)
    {
        $flockId = $request->get('flock_id');
        $status  = $request->get('status', 'all');

        $query = BreedingRecord::with(['female.species', 'male', 'recorder', 'offspringRecords']);

        if ($flockId) {
            $query->where('flock_id', $flockId);
        }

        if ($status === 'pending') {
            $query->where('expected_delivery_date', '>', now())
                  ->whereNull('actual_delivery_date');
        } elseif ($status === 'successful') {
            $query->where('is_successful', true);
        } elseif ($status === 'unsuccessful') {
            $query->where('is_successful', false);
        }

        $records = $query->orderBy('breeding_date', 'desc')->paginate(20);

        // Stats across ALL records matching the filter, not just the current page.
        $statsQuery = BreedingRecord::query();
        if ($flockId) $statsQuery->where('flock_id', $flockId);

        $totalBreedings  = $statsQuery->count();
        $pendingCount    = (clone $statsQuery)->where('expected_delivery_date', '>', now())
                                              ->whereNull('actual_delivery_date')->count();
        $successfulCount = (clone $statsQuery)->where('is_successful', true)->count();
        $totalOffspring  = (clone $statsQuery)->sum('offspring_count');

        // Filter dropdown: only dam (female) flocks marked as breeding stock.
        $flocks = Flock::where('is_breeding_stock', true)
            ->where('sex', 'female')
            ->get();

        return view('breeding-records.index', compact(
            'records', 'flocks', 'flockId', 'status',
            'totalBreedings', 'pendingCount', 'successfulCount', 'totalOffspring'
        ));
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // CREATE (traditional form page)
    // ═══════════════════════════════════════════════════════════════════════════

    public function create(Request $request)
    {
        $flockId = $request->get('flock_id');
        $flock   = null;

        if ($flockId) {
            $flock = Flock::with('species')->findOrFail($flockId);
        }

        $femaleFlocks = Flock::where('is_breeding_stock', true)
            ->where('sex', 'female')
            ->where('status', 'active')
            ->when($flock, fn ($q) => $q->where('species_id', $flock->species_id))
            ->get();

        $maleFlocks = Flock::where('is_breeding_stock', true)
            ->where('sex', 'male')
            ->where('status', 'active')
            ->when($flock, fn ($q) => $q->where('species_id', $flock->species_id))
            ->get();

        return view('breeding-records.create', compact('femaleFlocks', 'maleFlocks', 'flock'));
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // STORE (traditional form)
    // ═══════════════════════════════════════════════════════════════════════════

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'flock_id'               => 'required|exists:flocks,id',
            'mate_id'                => 'nullable|exists:flocks,id',
            'breeding_date'          => 'required|date|before_or_equal:today',
            'expected_delivery_date' => 'required|date|after:breeding_date',
            'breeding_method'        => 'required|in:natural,artificial_insemination',
            'notes'                  => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();

        try {
            $femaleFlock = Flock::findOrFail($request->flock_id);
            $maleFlock   = $request->mate_id ? Flock::find($request->mate_id) : null;

            // Resolve effective breeder counts using the hybrid logic.
            $femaleResolved = BreedingRecord::resolveEffectiveBreeders($femaleFlock);
            $maleResolved   = $maleFlock
                ? BreedingRecord::resolveEffectiveBreeders($maleFlock)
                : null;

            $record = BreedingRecord::create([
                'flock_id'               => $request->flock_id,
                'mate_id'                => $request->mate_id,
                'female_breeder_count'   => $femaleResolved['effective_count'],
                'male_breeder_count'     => $maleResolved ? $maleResolved['effective_count'] : null,
                'breeding_date'          => $request->breeding_date,
                'expected_delivery_date' => $request->expected_delivery_date,
                'breeding_method'        => $request->breeding_method,
                'is_successful'          => false,
                'notes'                  => $request->notes,
                'recorded_by'            => auth()->id(),
            ]);

            AuditHelper::log(
                'create',
                "Created breeding record for flock #{$record->female->flock_number} with "
                    . ($record->male ? "flock #{$record->male->flock_number}" : "AI")
                    . " | Female breeders: {$record->female_breeder_count}"
                    . ($record->male_breeder_count ? " | Male breeders: {$record->male_breeder_count}" : ''),
                'breeding_record',
                $record->id,
                null,
                $record->toArray()
            );

            $femaleFlock->update([
                'last_breeding_date'     => $request->breeding_date,
                'expected_delivery_date' => $request->expected_delivery_date,
            ]);

            DB::commit();

            return redirect()->route('breeding-records.show', $record->id)
                ->with('success', 'Breeding record created successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create breeding record: ' . $e->getMessage());
        }
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // SHOW
    // ═══════════════════════════════════════════════════════════════════════════

    public function show($id)
    {
        $record = BreedingRecord::with([
            'female.species', 'male', 'recorder', 'offspringRecords.newFlock',
        ])->findOrFail($id);

        $conceptionRate        = $record->conception_rate;
        $liveBirthRate         = $record->live_birth_rate;
        $weaningRate           = $record->weaning_rate;
        $offspringPerFemale    = $record->offspring_per_female;
        $offspringPerMale      = $record->offspring_per_male;
        $maleToFemaleRatio     = $record->male_to_female_ratio;

        return view('breeding-records.show', compact(
            'record', 'conceptionRate', 'liveBirthRate', 'weaningRate',
            'offspringPerFemale', 'offspringPerMale', 'maleToFemaleRatio'
        ));
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // EDIT
    // ═══════════════════════════════════════════════════════════════════════════

    public function edit($id)
    {
        $record = BreedingRecord::findOrFail($id);

        $femaleFlocks = Flock::where('is_breeding_stock', true)
            ->where('sex', 'female')
            ->where('status', 'active')
            ->get();

        $maleFlocks = Flock::where('is_breeding_stock', true)
            ->where('sex', 'male')
            ->where('status', 'active')
            ->get();

        return view('breeding-records.edit', compact('record', 'femaleFlocks', 'maleFlocks'));
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // UPDATE
    // ═══════════════════════════════════════════════════════════════════════════

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'flock_id'               => 'required|exists:flocks,id',
            'mate_id'                => 'nullable|exists:flocks,id',
            'breeding_date'          => 'required|date',
            'expected_delivery_date' => 'required|date|after:breeding_date',
            'actual_delivery_date'   => 'nullable|date|after_or_equal:breeding_date',
            'is_successful'          => 'nullable|boolean',
            'offspring_count'        => 'nullable|integer|min:0',
            'stillborn_count'        => 'nullable|integer|min:0',
            'weaned_count'           => 'nullable|integer|min:0',
            'breeding_method'        => 'required|in:natural,artificial_insemination',
            'notes'                  => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $record    = BreedingRecord::findOrFail($id);
        $oldValues = $record->toArray();

        DB::beginTransaction();

        try {
            // Re-resolve breeder counts only if the flock selection changed.
            $femaleBreederCount = $record->female_breeder_count;
            $maleBreederCount   = $record->male_breeder_count;

            if ($request->flock_id != $record->flock_id) {
                $femaleFlock        = Flock::findOrFail($request->flock_id);
                $resolved           = BreedingRecord::resolveEffectiveBreeders($femaleFlock);
                $femaleBreederCount = $resolved['effective_count'];
            }

            if ($request->mate_id != $record->mate_id) {
                if ($request->mate_id) {
                    $maleFlock          = Flock::findOrFail($request->mate_id);
                    $resolved           = BreedingRecord::resolveEffectiveBreeders($maleFlock);
                    $maleBreederCount   = $resolved['effective_count'];
                } else {
                    $maleBreederCount = null; // switched to AI
                }
            }

            $record->update([
                'flock_id'               => $request->flock_id,
                'mate_id'                => $request->mate_id,
                'female_breeder_count'   => $femaleBreederCount,
                'male_breeder_count'     => $maleBreederCount,
                'breeding_date'          => $request->breeding_date,
                'expected_delivery_date' => $request->expected_delivery_date,
                'actual_delivery_date'   => $request->actual_delivery_date,
                'is_successful'          => $request->is_successful ?? false,
                'offspring_count'        => $request->offspring_count,
                'stillborn_count'        => $request->stillborn_count ?? 0,
                'weaned_count'           => $request->weaned_count,
                'breeding_method'        => $request->breeding_method,
                'notes'                  => $request->notes,
            ]);

            AuditHelper::log(
                'update',
                "Updated breeding record for flock #{$record->female->flock_number}",
                'breeding_record',
                $record->id,
                $oldValues,
                $record->toArray()
            );

            $flock = Flock::find($request->flock_id);
            if ($flock) {
                $flock->update([
                    'last_breeding_date'     => $request->breeding_date,
                    'expected_delivery_date' => $request->expected_delivery_date,
                ]);
            }

            DB::commit();

            return redirect()->route('breeding-records.show', $record->id)
                ->with('success', 'Breeding record updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update breeding record: ' . $e->getMessage());
        }
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // DESTROY
    // ═══════════════════════════════════════════════════════════════════════════

    public function destroy($id)
    {
        $record = BreedingRecord::findOrFail($id);

        try {
            AuditHelper::log(
                'delete',
                "Deleted breeding record for flock #{$record->female->flock_number}",
                'breeding_record',
                $record->id,
                $record->toArray(),
                null
            );

            $record->delete();

            return redirect()->route('breeding-records.index')
                ->with('success', 'Breeding record deleted successfully');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete breeding record: ' . $e->getMessage());
        }
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // RECORD DELIVERY (traditional form)
    // ═══════════════════════════════════════════════════════════════════════════

    public function recordDelivery(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'actual_delivery_date' => 'required|date|before_or_equal:today',
            'offspring_count'      => 'required|integer|min:0',
            'stillborn_count'      => 'required|integer|min:0',
            'weaned_count'         => 'nullable|integer|min:0',
            'is_successful'        => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $record    = BreedingRecord::findOrFail($id);
        $oldValues = $record->toArray();

        DB::beginTransaction();

        try {
            $record->update([
                'actual_delivery_date' => $request->actual_delivery_date,
                'offspring_count'      => $request->offspring_count,
                'stillborn_count'      => $request->stillborn_count,
                'weaned_count'         => $request->weaned_count,
                'is_successful'        => $request->is_successful,
            ]);

            AuditHelper::log(
                'delivery',
                "Recorded delivery for flock #{$record->female->flock_number} — "
                    . "{$record->offspring_count} offspring | "
                    . "Female breeders: {$record->female_breeder_count} | "
                    . ($record->male_breeder_count ? "Male breeders: {$record->male_breeder_count}" : "AI"),
                'breeding_record',
                $record->id,
                $oldValues,
                $record->toArray()
            );

            DB::commit();

            $flock = Flock::find($record->flock_id);
            $this->notifications->notifyBreedingDelivery(
                $record->flock_id,
                $flock->flock_number ?? 'Unknown',
                $record->offspring_count ?? 0,
                $record->stillborn_count ?? 0
            );

            return redirect()->route('breeding-records.show', $record->id)
                ->with('success', 'Delivery recorded successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to record delivery: ' . $e->getMessage());
        }
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // PENDING
    // ═══════════════════════════════════════════════════════════════════════════

    public function pending()
    {
        $pendingBreedings = BreedingRecord::with(['female.species', 'male'])
            ->where('expected_delivery_date', '>', now())
            ->whereNull('actual_delivery_date')
            ->orderBy('expected_delivery_date')
            ->get();

        return view('breeding-records.pending', compact('pendingBreedings'));
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // AJAX — GET DETAILS JSON
    // ═══════════════════════════════════════════════════════════════════════════

    public function getDetailsJson($id)
    {
        try {
            if (!auth()->check()) {
                return response()->json(['success' => false, 'message' => 'Please login'], 401);
            }

            $record = BreedingRecord::with([
                'female',
                'male',
                'recorder',
                'offspringRecords.newFlock',
            ])->findOrFail($id);

            $offspringData = $record->offspringRecords->map(fn ($o) => [
                'flock_number'     => $o->newFlock->flock_number ?? 'N/A',
                'count'            => $o->count ?? 0,
                'ear_tag_range'    => $o->ear_tag_range          ?? 'N/A',
                'avg_birth_weight' => $o->avg_birth_weight        ?? null,
            ]);

            return response()->json([
                'success' => true,
                'record'  => [
                    // Identifiers
                    'id'                     => $record->id,
                    'female_flock_number'    => $record->female->flock_number  ?? 'N/A',
                    'female_breed'           => $record->female->breed_variety ?? 'N/A',
                    'male_flock_number'      => $record->male->flock_number    ?? 'External / AI',
                    'male_breed'             => $record->male->breed_variety   ?? null,

                    // Breeder population snapshots
                    'female_breeder_count'   => $record->female_breeder_count,
                    'male_breeder_count'     => $record->male_breeder_count,

                    // Dates
                    'breeding_date'          => $record->breeding_date->format('d M Y'),
                    'expected_delivery_date' => $record->expected_delivery_date->format('d M Y'),
                    'actual_delivery_date'   => $record->actual_delivery_date?->format('d M Y'),

                    // Details
                    'breeding_method'        => ucfirst(str_replace('_', ' ', $record->breeding_method)),
                    'is_successful'          => $record->is_successful,
                    'offspring_count'        => $record->offspring_count,
                    'stillborn_count'        => $record->stillborn_count,
                    'weaned_count'           => $record->weaned_count,
                    'notes'                  => $record->notes,
                    'recorded_by'            => $record->recorder->name ?? 'N/A',

                    // Analytics
                    'conception_rate'        => $record->conception_rate,
                    'live_birth_rate'        => $record->live_birth_rate,
                    'weaning_rate'           => $record->weaning_rate,
                    'offspring_per_female'   => $record->offspring_per_female,
                    'offspring_per_male'     => $record->offspring_per_male,
                    'male_to_female_ratio'   => $record->male_to_female_ratio,

                    'offspring_records'      => $offspringData,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // AJAX — RECORD DELIVERY
    // ═══════════════════════════════════════════════════════════════════════════

    public function recordDeliveryAjax(Request $request, $id)
    {
        try {
            if (!auth()->check()) {
                return response()->json(['success' => false, 'message' => 'Please login'], 401);
            }

            $validator = Validator::make($request->all(), [
                'actual_delivery_date' => 'required|date|before_or_equal:today',
                'offspring_count'      => 'required|integer|min:0',
                'stillborn_count'      => 'required|integer|min:0',
                'weaned_count'         => 'nullable|integer|min:0',
                'is_successful'        => 'required|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                ], 422);
            }

            $record    = BreedingRecord::findOrFail($id);
            $oldValues = $record->toArray();

            DB::beginTransaction();

            try {
                $record->update([
                    'actual_delivery_date' => $request->actual_delivery_date,
                    'offspring_count'      => $request->offspring_count,
                    'stillborn_count'      => $request->stillborn_count,
                    'weaned_count'         => $request->weaned_count ?? 0,
                    'is_successful'        => $request->is_successful,
                ]);

                AuditHelper::log(
                    'delivery',
                    "Recorded delivery for flock #{$record->female->flock_number} — "
                        . "{$record->offspring_count} offspring | "
                        . "Female breeders: {$record->female_breeder_count} | "
                        . ($record->male_breeder_count ? "Male breeders: {$record->male_breeder_count}" : "AI"),
                    'breeding_record',
                    $record->id,
                    $oldValues,
                    $record->toArray()
                );

                DB::commit();

                $flock = Flock::find($record->flock_id);
                $this->notifications->notifyBreedingDelivery(
                    $record->flock_id,
                    $flock->flock_number ?? 'Unknown',
                    $record->offspring_count ?? 0,
                    $record->stillborn_count ?? 0
                );

                return response()->json(['success' => true, 'message' => 'Delivery recorded successfully']);

            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // AJAX — GET CREATE FORM DATA
    // ═══════════════════════════════════════════════════════════════════════════

    public function getCreateForm()
    {
        $femaleFlocks = Flock::with('species')
    ->where('status', 'active')
    ->where('is_breeding_stock', true)
    ->where('sex', 'female')
    ->get()
    ->map(function (Flock $f) {          // ← add the type hint here
        $resolved = BreedingRecord::resolveEffectiveBreeders($f);
        return [
            'id'               => $f->id,
            'flock_number'     => $f->flock_number,
            'breed_variety'    => $f->breed_variety,
            'species_name'     => $f->species->name           ?? 'N/A',
            'gestation_days'   => $f->species->gestation_days ?? 0,
            'current_count'    => $f->current_count,
            'breeder_count'    => $f->breeder_count,
            'effective_count'  => $resolved['effective_count'],
            'breeder_mode'     => $resolved['mode'],
        ];
    });

        $maleFlocks = Flock::with('species')
            ->where('status', 'active')
            ->where('is_breeding_stock', true)
            ->where('sex', 'male')
            ->get()
            ->map(function (Flock $f) { 
                $resolved = BreedingRecord::resolveEffectiveBreeders($f);
                return [
                    'id'              => $f->id,
                    'flock_number'    => $f->flock_number,
                    'breed_variety'   => $f->breed_variety,
                    'current_count'   => $f->current_count,
                    'breeder_count'   => $f->breeder_count,
                    'effective_count' => $resolved['effective_count'],
                    'breeder_mode'    => $resolved['mode'],
                ];
            });

        return response()->json([
            'success'       => true,
            'female_flocks' => $femaleFlocks,
            'male_flocks'   => $maleFlocks,
        ]);
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // AJAX — STORE BREEDING RECORD
    // ═══════════════════════════════════════════════════════════════════════════

    public function storeBreedingRecord(Request $request)
    {
        try {
            if (!auth()->check()) {
                return response()->json(['success' => false, 'message' => 'Please login'], 401);
            }

            $validator = Validator::make($request->all(), [
                'flock_id'               => 'required|exists:flocks,id',
                'mate_id'                => 'nullable|exists:flocks,id',
                'breeding_date'          => 'required|date|before_or_equal:today',
                'expected_delivery_date' => 'required|date|after:breeding_date',
                'breeding_method'        => 'required|in:natural,artificial_insemination',
                'notes'                  => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                ], 422);
            }

            DB::beginTransaction();

            try {
                $femaleFlock = Flock::findOrFail($request->flock_id);
                $maleFlock   = $request->mate_id ? Flock::find($request->mate_id) : null;

                // Resolve effective breeder counts using hybrid logic.
                $femaleResolved = BreedingRecord::resolveEffectiveBreeders($femaleFlock);
                $maleResolved   = $maleFlock
                    ? BreedingRecord::resolveEffectiveBreeders($maleFlock)
                    : null;

                $record = BreedingRecord::create([
                    'flock_id'               => $request->flock_id,
                    'mate_id'                => $request->mate_id,
                    'female_breeder_count'   => $femaleResolved['effective_count'],
                    'male_breeder_count'     => $maleResolved ? $maleResolved['effective_count'] : null,
                    'breeding_date'          => $request->breeding_date,
                    'expected_delivery_date' => $request->expected_delivery_date,
                    'breeding_method'        => $request->breeding_method,
                    'is_successful'          => false,
                    'notes'                  => $request->notes,
                    'recorded_by'            => auth()->id(),
                ]);

                AuditHelper::log(
                    'create',
                    "Created breeding record for flock #{$record->female->flock_number} with "
                        . ($record->male ? "flock #{$record->male->flock_number}" : "AI")
                        . " | Female breeders: {$record->female_breeder_count}"
                        . ($record->male_breeder_count ? " | Male breeders: {$record->male_breeder_count}" : ''),
                    'breeding_record',
                    $record->id,
                    null,
                    $record->toArray()
                );

                // Sync parent flock breeding dates.
                $femaleFlock->update([
                    'last_breeding_date'     => $request->breeding_date,
                    'expected_delivery_date' => $request->expected_delivery_date,
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Breeding record created successfully',
                    'record'  => [
                        'id'                   => $record->id,
                        'female_breeder_count' => $record->female_breeder_count,
                        'male_breeder_count'   => $record->male_breeder_count,
                        'female_breeder_mode'  => $femaleResolved['mode'],
                        'male_breeder_mode'    => $maleResolved ? $maleResolved['mode'] : null,
                    ],
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}