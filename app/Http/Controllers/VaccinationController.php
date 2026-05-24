<?php
// app/Http/Controllers/VaccinationController.php

namespace App\Http\Controllers;

use App\Models\Vaccination;
use App\Models\Flock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class VaccinationController extends Controller
{
    /**
     * Display a listing of vaccinations
     */
    public function index(Request $request)
    {
        $flockId = $request->get('flock_id');
        $startDate = $request->get('start_date', Carbon::now()->subDays(30));
        $endDate = $request->get('end_date', Carbon::now());
        
        $query = Vaccination::with(['flock.species', 'administrator']);
        
        if ($flockId) {
            $query->where('flock_id', $flockId);
        }
        
        $vaccinations = $query->whereBetween('administration_date', [$startDate, $endDate])
            ->orderBy('administration_date', 'desc')
            ->paginate(20);
        
        $flocks = Flock::where('status', 'active')->get();
        
        return view('vaccinations.index', compact('vaccinations', 'flocks', 'flockId', 'startDate', 'endDate'));
    }
    
    /**
     * Show the form for creating a new vaccination
     */
    public function create(Request $request)
    {
        $flockId = $request->get('flock_id');
        $flock = null;
        
        if ($flockId) {
            $flock = Flock::with('species')->findOrFail($flockId);
        }
        
        $flocks = Flock::where('status', 'active')->get();
        
        return view('vaccinations.create', compact('flocks', 'flock'));
    }
    
    /**
     * Store a newly created vaccination
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'flock_id' => 'required|exists:flocks,id',
            'vaccine_name' => 'required|string|max:255',
            'disease_target' => 'required|string|max:255',
            'day_administered' => 'required|integer|min:0',
            'administration_date' => 'required|date|before_or_equal:today',
            'route' => 'required|in:subcutaneous,intramuscular,drinking_water,spray,eye_drop',
            'batch_number' => 'required|string|max:100',
            'expiry_date' => 'required|date|after:administration_date',
            'dosage_ml' => 'nullable|numeric|min:0',
            'birds_vaccinated' => 'nullable|integer|min:0',
            'notes' => 'nullable|string'
        ]);
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        try {
            $vaccination = Vaccination::create([
                'flock_id' => $request->flock_id,
                'vaccine_name' => $request->vaccine_name,
                'disease_target' => $request->disease_target,
                'day_administered' => $request->day_administered,
                'administration_date' => $request->administration_date,
                'route' => $request->route,
                'batch_number' => $request->batch_number,
                'expiry_date' => $request->expiry_date,
                'dosage_ml' => $request->dosage_ml,
                'birds_vaccinated' => $request->birds_vaccinated,
                'notes' => $request->notes,
                'administered_by' => auth()->id()
            ]);
            
            return redirect()->route('vaccinations.show', $vaccination->id)
                ->with('success', 'Vaccination recorded successfully');
                
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to record vaccination: ' . $e->getMessage());
        }
    }
    
    /**
     * Display the specified vaccination
     */
    public function show($id)
    {
        $vaccination = Vaccination::with(['flock.species', 'administrator'])->findOrFail($id);
        
        $coveragePercentage = $vaccination->coverage_percentage;
        $isExpired = $vaccination->is_expired;
        
        return view('vaccinations.show', compact('vaccination', 'coveragePercentage', 'isExpired'));
    }
    
    /**
     * Show the form for editing the specified vaccination
     */
    public function edit($id)
    {
        $vaccination = Vaccination::findOrFail($id);
        $flocks = Flock::where('status', 'active')->get();
        
        return view('vaccinations.edit', compact('vaccination', 'flocks'));
    }
    
    /**
     * Update the specified vaccination
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'flock_id' => 'required|exists:flocks,id',
            'vaccine_name' => 'required|string|max:255',
            'disease_target' => 'required|string|max:255',
            'day_administered' => 'required|integer|min:0',
            'administration_date' => 'required|date',
            'route' => 'required|in:subcutaneous,intramuscular,drinking_water,spray,eye_drop',
            'batch_number' => 'required|string|max:100',
            'expiry_date' => 'required|date|after:administration_date',
            'dosage_ml' => 'nullable|numeric|min:0',
            'birds_vaccinated' => 'nullable|integer|min:0',
            'notes' => 'nullable|string'
        ]);
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        $vaccination = Vaccination::findOrFail($id);
        
        try {
            $vaccination->update([
                'flock_id' => $request->flock_id,
                'vaccine_name' => $request->vaccine_name,
                'disease_target' => $request->disease_target,
                'day_administered' => $request->day_administered,
                'administration_date' => $request->administration_date,
                'route' => $request->route,
                'batch_number' => $request->batch_number,
                'expiry_date' => $request->expiry_date,
                'dosage_ml' => $request->dosage_ml,
                'birds_vaccinated' => $request->birds_vaccinated,
                'notes' => $request->notes
            ]);
            
            return redirect()->route('vaccinations.show', $vaccination->id)
                ->with('success', 'Vaccination updated successfully');
                
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update vaccination: ' . $e->getMessage());
        }
    }
    
    /**
     * Remove the specified vaccination
     */
    public function destroy($id)
    {
        $vaccination = Vaccination::findOrFail($id);
        $vaccination->delete();
        
        return redirect()->route('vaccinations.index')
            ->with('success', 'Vaccination deleted successfully');
    }
    
    /**
     * Display vaccination schedule
     */
    public function schedule(Request $request)
    {
        $flockId = $request->get('flock_id');
        
        $query = Vaccination::with(['flock.species']);
        
        if ($flockId) {
            $query->where('flock_id', $flockId);
        }
        
        $upcomingVaccinations = $query->where('administration_date', '>=', Carbon::today())
            ->orderBy('administration_date')
            ->get();
        
        $pastVaccinations = Vaccination::with(['flock.species'])
            ->where('administration_date', '<', Carbon::today())
            ->orderBy('administration_date', 'desc')
            ->limit(20)
            ->get();
        
        $flocks = Flock::where('status', 'active')->get();
        
        return view('vaccinations.schedule', compact('upcomingVaccinations', 'pastVaccinations', 'flocks', 'flockId'));
    }

    /**
 * Get create form data for AJAX modal
 */
public function getCreateForm()
{
    try {
        $flocks = Flock::where('status', 'active')
            ->with('species')
            ->get(['id', 'flock_number', 'breed_variety']);
        
        $flocksData = $flocks->map(function($flock) {
            return [
                'id' => $flock->id,
                'flock_number' => $flock->flock_number,
                'breed_variety' => $flock->breed_variety,
            ];
        });
        
        return response()->json([
            'success' => true,
            'flocks' => $flocksData
        ]);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()]);
    }
}

/**
 * Get vaccination details for AJAX modal
 */
public function getVaccinationDetails($id)
{
    try {
        $vaccination = Vaccination::with(['flock.species', 'administrator'])->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'vaccination' => [
                'id' => $vaccination->id,
                'flock_number' => $vaccination->flock->flock_number ?? 'N/A',
                'breed_variety' => $vaccination->flock->breed_variety ?? 'N/A',
                'vaccine_name' => $vaccination->vaccine_name,
                'disease_target' => $vaccination->disease_target,
                'day_administered' => $vaccination->day_administered,
                'administration_date' => $vaccination->administration_date->format('Y-m-d'),
                'route' => $vaccination->route,
                'batch_number' => $vaccination->batch_number,
                'expiry_date' => $vaccination->expiry_date->format('Y-m-d'),
                'dosage_ml' => $vaccination->dosage_ml,
                'birds_vaccinated' => $vaccination->birds_vaccinated,
                'coverage_percentage' => $vaccination->coverage_percentage ?? 0,
                'notes' => $vaccination->notes,
                'administered_by' => $vaccination->administrator->name ?? 'N/A',
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()]);
    }
}

/**
 * Get vaccination edit data for AJAX modal
 */
public function getVaccinationEditData($id)
{
    try {
        $vaccination = Vaccination::findOrFail($id);
        $flocks = Flock::where('status', 'active')->get(['id', 'flock_number', 'breed_variety']);
        
        $flocksData = $flocks->map(function($flock) {
            return [
                'id' => $flock->id,
                'flock_number' => $flock->flock_number,
                'breed_variety' => $flock->breed_variety,
            ];
        });
        
        return response()->json([
            'success' => true,
            'vaccination' => [
                'id' => $vaccination->id,
                'flock_id' => $vaccination->flock_id,
                'vaccine_name' => $vaccination->vaccine_name,
                'disease_target' => $vaccination->disease_target,
                'day_administered' => $vaccination->day_administered,
                'administration_date' => $vaccination->administration_date->format('Y-m-d'),
                'route' => $vaccination->route,
                'batch_number' => $vaccination->batch_number,
                'expiry_date' => $vaccination->expiry_date->format('Y-m-d'),
                'dosage_ml' => $vaccination->dosage_ml,
                'birds_vaccinated' => $vaccination->birds_vaccinated,
                'notes' => $vaccination->notes,
            ],
            'flocks' => $flocksData
        ]);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()]);
    }
}

/**
 * Get schedule data for AJAX modal
 */
public function getScheduleData()
{
    try {
        $upcoming = Vaccination::with(['flock.species'])
            ->where('administration_date', '>=', Carbon::today())
            ->orderBy('administration_date')
            ->get()
            ->map(function($v) {
                return [
                    'id' => $v->id,
                    'administration_date' => $v->administration_date->format('Y-m-d'),
                    'flock_number' => $v->flock->flock_number ?? 'N/A',
                    'vaccine_name' => $v->vaccine_name,
                    'disease_target' => $v->disease_target,
                ];
            });
        
        $past = Vaccination::with(['flock.species'])
            ->where('administration_date', '<', Carbon::today())
            ->orderBy('administration_date', 'desc')
            ->limit(20)
            ->get()
            ->map(function($v) {
                return [
                    'id' => $v->id,
                    'administration_date' => $v->administration_date->format('Y-m-d'),
                    'flock_number' => $v->flock->flock_number ?? 'N/A',
                    'vaccine_name' => $v->vaccine_name,
                    'disease_target' => $v->disease_target,
                ];
            });
        
        return response()->json([
            'success' => true,
            'upcoming' => $upcoming,
            'past' => $past
        ]);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()]);
    }
}

/**
 * Update vaccination via AJAX
 */
public function updateVaccination(Request $request, $id)
{
    $validator = Validator::make($request->all(), [
        'flock_id' => 'required|exists:flocks,id',
        'vaccine_name' => 'required|string|max:255',
        'disease_target' => 'required|string|max:255',
        'day_administered' => 'required|integer|min:0',
        'administration_date' => 'required|date',
        'route' => 'required|in:subcutaneous,intramuscular,drinking_water,spray,eye_drop',
        'batch_number' => 'required|string|max:100',
        'expiry_date' => 'required|date|after:administration_date',
    ]);
    
    if ($validator->fails()) {
        return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
    }
    
    $vaccination = Vaccination::findOrFail($id);
    
    try {
        $vaccination->update($request->all());
        return response()->json(['success' => true, 'message' => 'Vaccination updated successfully']);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
}

/**
 * Store vaccination via AJAX
 */
public function storeVaccination(Request $request)
{
    $validator = Validator::make($request->all(), [
        'flock_id' => 'required|exists:flocks,id',
        'vaccine_name' => 'required|string|max:255',
        'disease_target' => 'required|string|max:255',
        'day_administered' => 'required|integer|min:0',
        'administration_date' => 'required|date|before_or_equal:today',
        'route' => 'required|in:subcutaneous,intramuscular,drinking_water,spray,eye_drop',
        'batch_number' => 'required|string|max:100',
        'expiry_date' => 'required|date|after:administration_date',
    ]);
    
    if ($validator->fails()) {
        return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
    }
    
    try {
        $vaccination = Vaccination::create($request->all() + ['administered_by' => auth()->id()]);
        return response()->json(['success' => true, 'message' => 'Vaccination created successfully']);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
}

}