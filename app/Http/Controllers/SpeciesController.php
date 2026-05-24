<?php
// app/Http/Controllers/SpeciesController.php

namespace App\Http\Controllers;

use App\Models\Species;
use App\Models\Flock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class SpeciesController extends Controller
{
    /**
     * Display a listing of species
     */
    public function index(Request $request)
    {
        $isActive = $request->get('is_active');
        
        $query = Species::query();
        
        if ($isActive !== null) {
            $query->where('is_active', $isActive);
        }
        
        $species = $query->orderBy('name')->paginate(5);
        
        return view('species.index', compact('species'));
    }
    
    /**
     * Get create form HTML for AJAX modal
     */
    public function getCreateForm()
    {
        try {
            $html = '
                <form id="createSpeciesForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="e.g., Chicken, Cattle, Goat" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Code <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control" maxlength="5" placeholder="e.g., CH, CT, GT" required>
                            <small class="text-muted">Unique 3-5 character code</small>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Icon Class</label>
                            <input type="text" name="icon" class="form-control" value="fas fa-drumstick" placeholder="e.g., fas fa-drumstick">
                            <small class="text-muted">FontAwesome icon class</small>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Color (Hex)</label>
                            <input type="color" name="color_hex" class="form-control" value="#3B82F6">
                        </div>
                        
                        <div class="col-12 mb-3">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="Brief description of the species..."></textarea>
                        </div>
                        
                        <div class="col-md-12 mb-3">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Lifecycle Parameters</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Gestation Days</label>
                                            <input type="number" name="gestation_days" class="form-control" placeholder="Days until birth" min="0">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Weaning Days</label>
                                            <input type="number" name="weaning_days" class="form-control" placeholder="Days until weaning" min="0">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Sexual Maturity (Days)</label>
                                            <input type="number" name="sexual_maturity_days" class="form-control" placeholder="Days until breeding age" min="0">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Market Age (Days)</label>
                                            <input type="number" name="market_age_days" class="form-control" placeholder="Days until ready for market" min="0">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Market Weight (kg)</label>
                                            <input type="number" name="market_weight_kg" class="form-control" step="0.01" placeholder="Target weight at market" min="0">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Lifespan (Years)</label>
                                            <input type="number" name="lifespan_years" class="form-control" placeholder="Average lifespan" min="0">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-12 mb-3">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Default Performance Metrics (JSON)</h6>
                                </div>
                                <div class="card-body">
                                    <textarea name="default_metrics" class="form-control" rows="3" placeholder=\'{"fcr_target": 1.8, "mortality_target": 5, "egg_production_target": 85}\'></textarea>
                                    <small class="text-muted">Enter as valid JSON format. Example: {"fcr_target": 1.8, "mortality_target": 5}</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-12 mb-3">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Growth Standards (JSON)</h6>
                                </div>
                                <div class="card-body">
                                    <textarea name="growth_standards" class="form-control" rows="3" placeholder=\'{"week1": 0.18, "week2": 0.45, "week3": 0.85, "week4": 1.35, "week5": 1.95, "week6": 2.5}\'></textarea>
                                    <small class="text-muted">Enter as valid JSON format. Example: {"week1": 0.18, "week2": 0.45}</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-12 mb-3">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Health Indicators (JSON)</h6>
                                </div>
                                <div class="card-body">
                                    <textarea name="health_indicators" class="form-control" rows="3" placeholder=\'{"normal_temperature": 41.5, "normal_heart_rate": 250, "normal_respiration": 20}\'></textarea>
                                    <small class="text-muted">Enter as valid JSON format. Example: {"normal_temperature": 41.5, "normal_heart_rate": 250}</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active" checked>
                                <label class="form-check-label" for="is_active">Active (available for selection)</label>
                            </div>
                        </div>
                    </div>
                </form>
            ';
            
            return response()->json([
                'success' => true,
                'html' => $html
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Store species via AJAX
     */
    public function storeSpeciesAjax(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:species',
                'code' => 'required|string|max:5|unique:species',
                'icon' => 'nullable|string|max:100',
                'color_hex' => 'nullable|string|max:7',
                'description' => 'nullable|string',
                'gestation_days' => 'nullable|integer|min:0',
                'weaning_days' => 'nullable|integer|min:0',
                'market_age_days' => 'nullable|integer|min:0',
                'market_weight_kg' => 'nullable|numeric|min:0',
                'lifespan_years' => 'nullable|integer|min:0',
                'sexual_maturity_days' => 'nullable|integer|min:0',
                'is_active' => 'nullable|boolean'
            ]);
            
            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
            }
            
            // Parse JSON fields if provided
            $defaultMetrics = null;
            if ($request->default_metrics && trim($request->default_metrics)) {
                $defaultMetrics = json_decode($request->default_metrics, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return response()->json(['success' => false, 'message' => 'Invalid JSON in Default Metrics'], 422);
                }
            }
            
            $growthStandards = null;
            if ($request->growth_standards && trim($request->growth_standards)) {
                $growthStandards = json_decode($request->growth_standards, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return response()->json(['success' => false, 'message' => 'Invalid JSON in Growth Standards'], 422);
                }
            }
            
            $healthIndicators = null;
            if ($request->health_indicators && trim($request->health_indicators)) {
                $healthIndicators = json_decode($request->health_indicators, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return response()->json(['success' => false, 'message' => 'Invalid JSON in Health Indicators'], 422);
                }
            }
            
            $species = Species::create([
                'name' => $request->name,
                'code' => strtoupper($request->code),
                'icon' => $request->icon,
                'color_hex' => $request->color_hex ?? '#3B82F6',
                'description' => $request->description,
                'default_metrics' => $defaultMetrics,
                'growth_standards' => $growthStandards,
                'health_indicators' => $healthIndicators,
                'gestation_days' => $request->gestation_days,
                'weaning_days' => $request->weaning_days,
                'market_age_days' => $request->market_age_days,
                'market_weight_kg' => $request->market_weight_kg,
                'lifespan_years' => $request->lifespan_years,
                'sexual_maturity_days' => $request->sexual_maturity_days,
                'is_active' => $request->is_active ?? true
            ]);
            
            return response()->json(['success' => true, 'message' => 'Species created successfully', 'species_id' => $species->id]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Get species data for AJAX modal
     */
    public function getDetailsJson($id)
    {
        try {
            $species = Species::findOrFail($id);
            
            // Get statistics for this species
            $flockCount = Flock::where('species_id', $id)->count();
            $activeFlockCount = Flock::where('species_id', $id)->where('status', 'active')->count();
            $totalAnimals = Flock::where('species_id', $id)->sum('current_count');
            
            $stats = [
                'flock_count' => $flockCount,
                'active_flocks' => $activeFlockCount,
                'total_animals' => number_format($totalAnimals)
            ];
            
            return response()->json([
                'success' => true,
                'species' => [
                    'id' => $species->id,
                    'name' => $species->name,
                    'code' => $species->code,
                    'icon' => $species->icon,
                    'color_hex' => $species->color_hex,
                    'description' => $species->description,
                    'is_active' => $species->is_active,
                    'gestation_days' => $species->gestation_days ?? 'N/A',
                    'weaning_days' => $species->weaning_days ?? 'N/A',
                    'market_age_days' => $species->market_age_days ? $species->market_age_days . ' days' : 'N/A',
                    'market_weight_kg' => $species->market_weight_kg ? $species->market_weight_kg . ' kg' : 'N/A',
                    'lifespan_years' => $species->lifespan_years ? $species->lifespan_years . ' years' : 'N/A',
                    'sexual_maturity_days' => $species->sexual_maturity_days ? $species->sexual_maturity_days . ' days' : 'N/A',
                    'default_metrics' => $species->default_metrics,
                    'growth_standards' => $species->growth_standards,
                    'health_indicators' => $species->health_indicators,
                    'stats' => $stats
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Get species edit data for AJAX modal
     */
    public function getEditData($id)
    {
        try {
            $species = Species::findOrFail($id);
            
            return response()->json([
                'success' => true,
                'species' => [
                    'id' => $species->id,
                    'name' => $species->name,
                    'code' => $species->code,
                    'icon' => $species->icon,
                    'color_hex' => $species->color_hex,
                    'description' => $species->description,
                    'is_active' => $species->is_active,
                    'gestation_days' => $species->gestation_days,
                    'weaning_days' => $species->weaning_days,
                    'market_age_days' => $species->market_age_days,
                    'market_weight_kg' => $species->market_weight_kg,
                    'lifespan_years' => $species->lifespan_years,
                    'sexual_maturity_days' => $species->sexual_maturity_days,
                    'default_metrics' => $species->default_metrics ? json_encode($species->default_metrics, JSON_PRETTY_PRINT) : '',
                    'growth_standards' => $species->growth_standards ? json_encode($species->growth_standards, JSON_PRETTY_PRINT) : '',
                    'health_indicators' => $species->health_indicators ? json_encode($species->health_indicators, JSON_PRETTY_PRINT) : '',
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Update species via AJAX
     */
    public function updateSpeciesAjax(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:species,name,' . $id,
                'code' => 'required|string|max:5|unique:species,code,' . $id,
                'icon' => 'nullable|string|max:100',
                'color_hex' => 'nullable|string|max:7',
                'description' => 'nullable|string',
                'gestation_days' => 'nullable|integer|min:0',
                'weaning_days' => 'nullable|integer|min:0',
                'market_age_days' => 'nullable|integer|min:0',
                'market_weight_kg' => 'nullable|numeric|min:0',
                'lifespan_years' => 'nullable|integer|min:0',
                'sexual_maturity_days' => 'nullable|integer|min:0',
                'is_active' => 'nullable|boolean'
            ]);
            
            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
            }
            
            $species = Species::findOrFail($id);
            
            // Parse JSON fields if provided
            $defaultMetrics = null;
            if ($request->default_metrics && trim($request->default_metrics)) {
                $defaultMetrics = json_decode($request->default_metrics, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return response()->json(['success' => false, 'message' => 'Invalid JSON in Default Metrics'], 422);
                }
            }
            
            $growthStandards = null;
            if ($request->growth_standards && trim($request->growth_standards)) {
                $growthStandards = json_decode($request->growth_standards, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return response()->json(['success' => false, 'message' => 'Invalid JSON in Growth Standards'], 422);
                }
            }
            
            $healthIndicators = null;
            if ($request->health_indicators && trim($request->health_indicators)) {
                $healthIndicators = json_decode($request->health_indicators, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return response()->json(['success' => false, 'message' => 'Invalid JSON in Health Indicators'], 422);
                }
            }
            
            $species->update([
                'name' => $request->name,
                'code' => strtoupper($request->code),
                'icon' => $request->icon,
                'color_hex' => $request->color_hex,
                'description' => $request->description,
                'default_metrics' => $defaultMetrics,
                'growth_standards' => $growthStandards,
                'health_indicators' => $healthIndicators,
                'gestation_days' => $request->gestation_days,
                'weaning_days' => $request->weaning_days,
                'market_age_days' => $request->market_age_days,
                'market_weight_kg' => $request->market_weight_kg,
                'lifespan_years' => $request->lifespan_years,
                'sexual_maturity_days' => $request->sexual_maturity_days,
                'is_active' => $request->is_active ?? true
            ]);
            
            return response()->json(['success' => true, 'message' => 'Species updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Toggle species status via AJAX
     */
    public function toggleStatusAjax(Request $request, $id)
    {
        try {
            $species = Species::findOrFail($id);
            $newStatus = !$species->is_active;
            $species->update(['is_active' => $newStatus]);
            
            return response()->json([
                'success' => true, 
                'message' => "Species " . ($newStatus ? 'activated' : 'deactivated') . " successfully",
                'is_active' => $newStatus
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Delete species via AJAX (admin only)
     */
    public function destroyAjax($id)
    {
        try {
            $species = Species::findOrFail($id);
            
            // Check if species has associated flocks
            $flockCount = Flock::where('species_id', $id)->count();
            
            if ($flockCount > 0) {
                return response()->json(['success' => false, 'message' => 'Cannot delete species with associated flocks. Archive it instead.'], 422);
            }
            
            $species->delete();
            
            return response()->json(['success' => true, 'message' => 'Species deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Show the form for creating a new species
     */
    public function create()
    {
        return view('species.create');
    }
    
    /**
     * Store a newly created species (regular form submission)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:species',
            'code' => 'required|string|max:5|unique:species',
            'icon' => 'nullable|string|max:100',
            'color_hex' => 'nullable|string|max:7',
            'description' => 'nullable|string',
            'default_metrics' => 'nullable|array',
            'growth_standards' => 'nullable|array',
            'health_indicators' => 'nullable|array',
            'gestation_days' => 'nullable|integer|min:0',
            'weaning_days' => 'nullable|integer|min:0',
            'market_age_days' => 'nullable|integer|min:0',
            'market_weight_kg' => 'nullable|numeric|min:0',
            'lifespan_years' => 'nullable|integer|min:0',
            'sexual_maturity_days' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean'
        ]);
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        try {
            $species = Species::create([
                'name' => $request->name,
                'code' => strtoupper($request->code),
                'icon' => $request->icon,
                'color_hex' => $request->color_hex ?? '#3B82F6',
                'description' => $request->description,
                'default_metrics' => $request->default_metrics,
                'growth_standards' => $request->growth_standards,
                'health_indicators' => $request->health_indicators,
                'gestation_days' => $request->gestation_days,
                'weaning_days' => $request->weaning_days,
                'market_age_days' => $request->market_age_days,
                'market_weight_kg' => $request->market_weight_kg,
                'lifespan_years' => $request->lifespan_years,
                'sexual_maturity_days' => $request->sexual_maturity_days,
                'is_active' => $request->is_active ?? true
            ]);
            
            return redirect()->route('species.show', $species->id)
                ->with('success', 'Species created successfully');
                
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to create species: ' . $e->getMessage());
        }
    }
    
    /**
     * Display the specified species
     */
    public function show($id)
    {
        $species = Species::findOrFail($id);
        
        // Get statistics for this species
        $flockCount = Flock::where('species_id', $id)->count();
        $activeFlockCount = Flock::where('species_id', $id)->where('status', 'active')->count();
        $totalAnimals = Flock::where('species_id', $id)->sum('current_count');
        
        $stats = [
            'flock_count' => $flockCount,
            'active_flocks' => $activeFlockCount,
            'total_animals' => $totalAnimals
        ];
        
        return view('species.show', compact('species', 'stats'));
    }
    
    /**
     * Show the form for editing the specified species
     */
    public function edit($id)
    {
        $species = Species::findOrFail($id);
        
        return view('species.edit', compact('species'));
    }
    
    /**
     * Update the specified species (regular form submission)
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:species,name,' . $id,
            'code' => 'required|string|max:5|unique:species,code,' . $id,
            'icon' => 'nullable|string|max:100',
            'color_hex' => 'nullable|string|max:7',
            'description' => 'nullable|string',
            'default_metrics' => 'nullable|array',
            'growth_standards' => 'nullable|array',
            'health_indicators' => 'nullable|array',
            'gestation_days' => 'nullable|integer|min:0',
            'weaning_days' => 'nullable|integer|min:0',
            'market_age_days' => 'nullable|integer|min:0',
            'market_weight_kg' => 'nullable|numeric|min:0',
            'lifespan_years' => 'nullable|integer|min:0',
            'sexual_maturity_days' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean'
        ]);
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        $species = Species::findOrFail($id);
        
        try {
            $species->update([
                'name' => $request->name,
                'code' => strtoupper($request->code),
                'icon' => $request->icon,
                'color_hex' => $request->color_hex,
                'description' => $request->description,
                'default_metrics' => $request->default_metrics,
                'growth_standards' => $request->growth_standards,
                'health_indicators' => $request->health_indicators,
                'gestation_days' => $request->gestation_days,
                'weaning_days' => $request->weaning_days,
                'market_age_days' => $request->market_age_days,
                'market_weight_kg' => $request->market_weight_kg,
                'lifespan_years' => $request->lifespan_years,
                'sexual_maturity_days' => $request->sexual_maturity_days,
                'is_active' => $request->is_active ?? true
            ]);
            
            return redirect()->route('species.show', $species->id)
                ->with('success', 'Species updated successfully');
                
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update species: ' . $e->getMessage());
        }
    }
    
    /**
     * Remove the specified species (regular form submission)
     */
    public function destroy($id)
    {
        $species = Species::findOrFail($id);
        
        // Check if species has associated flocks
        $flockCount = Flock::where('species_id', $id)->count();
        
        if ($flockCount > 0) {
            return back()->with('error', 'Cannot delete species with associated flocks. Archive it instead.');
        }
        
        $species->delete();
        
        return redirect()->route('species.index')
            ->with('success', 'Species deleted successfully');
    }
    
    /**
     * Toggle species active status (regular form submission)
     */
    public function toggleStatus($id)
    {
        $species = Species::findOrFail($id);
        $species->update(['is_active' => !$species->is_active]);
        
        $status = $species->is_active ? 'activated' : 'deactivated';
        
        return redirect()->route('species.show', $species->id)
            ->with('success', "Species {$status} successfully");
    }
}