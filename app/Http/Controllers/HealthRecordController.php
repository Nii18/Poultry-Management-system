<?php
// app/Http/Controllers/HealthRecordController.php

namespace App\Http\Controllers;

use App\Models\HealthRecord;
use App\Models\Treatment;
use App\Models\Vaccination;
use App\Models\Flock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HealthRecordController extends Controller
{
    /**
     * Display a listing of health records
     */
    public function index(Request $request)
    {
        $flockId = $request->get('flock_id');
        $recordType = $request->get('record_type');
        $severity = $request->get('severity');
        $startDate = $request->get('start_date', Carbon::now()->subDays(30));
        $endDate = $request->get('end_date', Carbon::now());
        
        $query = HealthRecord::with(['flock.species', 'recorder']);
        
        if ($flockId) {
            $query->where('flock_id', $flockId);
        }
        
        if ($recordType) {
            $query->where('record_type', $recordType);
        }
        
        if ($severity) {
            $query->where('severity', $severity);
        }
        
        $records = $query->whereBetween('record_date', [$startDate, $endDate])
            ->orderBy('record_date', 'desc')
            ->paginate(20);
        
        $flocks = Flock::where('status', 'active')->get();
        $recordTypes = HealthRecord::distinct()->pluck('record_type');
        $severities = ['info', 'warning', 'critical'];
        
        return view('health-records.index', compact('records', 'flocks', 'recordTypes', 'severities', 
            'flockId', 'recordType', 'severity', 'startDate', 'endDate'));
    }
    
    /**
     * Show the form for creating a new health record
     */
    public function create(Request $request)
    {
        $flockId = $request->get('flock_id');
        $flock = null;
        
        if ($flockId) {
            $flock = Flock::with('species')->findOrFail($flockId);
        }
        
        $flocks = Flock::where('status', 'active')->get();
        
        return view('health-records.create', compact('flocks', 'flock'));
    }
    
    /**
     * Store a newly created health record
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'flock_id' => 'required|exists:flocks,id',
            'record_type' => 'required|in:checkup,symptom,lab_result,post_mortem,consultation',
            'condition' => 'nullable|string|max:255',
            'symptoms' => 'nullable|array',
            'lab_results' => 'nullable|array',
            'veterinarian_notes' => 'nullable|string',
            'affected_count' => 'nullable|integer|min:0',
            'severity' => 'required|in:info,warning,critical',
            'record_date' => 'required|date|before_or_equal:today',
            'notes' => 'nullable|string'
        ]);
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        try {
            $record = HealthRecord::create([
                'flock_id' => $request->flock_id,
                'record_type' => $request->record_type,
                'condition' => $request->condition,
                'symptoms' => $request->symptoms,
                'lab_results' => $request->lab_results,
                'veterinarian_notes' => $request->veterinarian_notes,
                'affected_count' => $request->affected_count,
                'severity' => $request->severity,
                'record_date' => $request->record_date,
                'notes' => $request->notes,
                'recorded_by' => auth()->id()
            ]);
            
            return redirect()->route('health-records.show', $record->id)
                ->with('success', 'Health record created successfully');
                
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to create health record: ' . $e->getMessage());
        }
    }
    
    /**
     * Display the specified health record
     */
    public function show($id)
    {
        $record = HealthRecord::with(['flock.species', 'recorder'])->findOrFail($id);
        
        $affectedPercentage = $record->affected_percentage;
        
        return view('health-records.show', compact('record', 'affectedPercentage'));
    }
    
    /**
     * Show the form for editing the specified health record
     */
    public function edit($id)
    {
        $record = HealthRecord::findOrFail($id);
        $flocks = Flock::where('status', 'active')->get();
        
        return view('health-records.edit', compact('record', 'flocks'));
    }
    
    /**
     * Update the specified health record
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'flock_id' => 'required|exists:flocks,id',
            'record_type' => 'required|in:checkup,symptom,lab_result,post_mortem,consultation',
            'condition' => 'nullable|string|max:255',
            'symptoms' => 'nullable|array',
            'lab_results' => 'nullable|array',
            'veterinarian_notes' => 'nullable|string',
            'affected_count' => 'nullable|integer|min:0',
            'severity' => 'required|in:info,warning,critical',
            'record_date' => 'required|date',
            'notes' => 'nullable|string'
        ]);
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        $record = HealthRecord::findOrFail($id);
        
        try {
            $record->update([
                'flock_id' => $request->flock_id,
                'record_type' => $request->record_type,
                'condition' => $request->condition,
                'symptoms' => $request->symptoms,
                'lab_results' => $request->lab_results,
                'veterinarian_notes' => $request->veterinarian_notes,
                'affected_count' => $request->affected_count,
                'severity' => $request->severity,
                'record_date' => $request->record_date,
                'notes' => $request->notes
            ]);
            
            return redirect()->route('health-records.show', $record->id)
                ->with('success', 'Health record updated successfully');
                
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update health record: ' . $e->getMessage());
        }
    }
    
    /**
     * Remove the specified health record
     */
    public function destroy($id)
    {
        $record = HealthRecord::findOrFail($id);
        $record->delete();
        
        return redirect()->route('health-records.index')
            ->with('success', 'Health record deleted successfully');
    }
    
    /**
     * Display critical health alerts
     */
    public function criticalAlerts()
    {
        $criticalRecords = HealthRecord::with(['flock.species'])
            ->where('severity', 'critical')
            ->where('record_date', '>=', Carbon::now()->subDays(7))
            ->orderBy('record_date', 'desc')
            ->get();
        
        return view('health-records.critical-alerts', compact('criticalRecords'));
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
 * Get health record details for AJAX modal
 */
public function getHealthRecordDetails($id)
{
    try {
        $record = HealthRecord::with(['flock.species', 'recorder'])->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'record' => [
                'id' => $record->id,
                'flock_number' => $record->flock->flock_number ?? 'N/A',
                'breed_variety' => $record->flock->breed_variety ?? 'N/A',
                'species_name' => $record->flock->species->name ?? 'N/A',
                'record_date' => $record->record_date->format('Y-m-d'),
                'record_type' => $record->record_type,
                'condition' => $record->condition,
                'affected_count' => $record->affected_count,
                'affected_percentage' => $record->affected_percentage,
                'severity' => $record->severity,
                'symptoms' => $record->symptoms,
                'lab_results' => $record->lab_results,
                'veterinarian_notes' => $record->veterinarian_notes,
                'notes' => $record->notes,
                'recorded_by' => $record->recorder->name ?? 'N/A',
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()]);
    }
}

/**
 * Get health record edit data for AJAX modal
 */
public function getHealthRecordEditData($id)
{
    try {
        $record = HealthRecord::findOrFail($id);
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
            'record' => [
                'id' => $record->id,
                'flock_id' => $record->flock_id,
                'record_date' => $record->record_date->format('Y-m-d'),
                'record_type' => $record->record_type,
                'condition' => $record->condition,
                'affected_count' => $record->affected_count,
                'severity' => $record->severity,
                'symptoms' => $record->symptoms,
                'lab_results' => $record->lab_results,
                'veterinarian_notes' => $record->veterinarian_notes,
                'notes' => $record->notes,
            ],
            'flocks' => $flocksData
        ]);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()]);
    }
}

/**
 * Store health record via AJAX
 */
public function storeHealthRecord(Request $request)
{
    $validator = Validator::make($request->all(), [
        'flock_id' => 'required|exists:flocks,id',
        'record_date' => 'required|date|before_or_equal:today',
        'record_type' => 'required|in:checkup,symptom,lab_result,post_mortem,consultation',
        'severity' => 'required|in:info,warning,critical',
    ]);
    
    if ($validator->fails()) {
        return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
    }
    
    try {
        $record = HealthRecord::create($request->all() + ['recorded_by' => auth()->id()]);
        return response()->json(['success' => true, 'message' => 'Health record created successfully']);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
}

/**
 * Update health record via AJAX
 */
public function updateHealthRecord(Request $request, $id)
{
    $validator = Validator::make($request->all(), [
        'flock_id' => 'required|exists:flocks,id',
        'record_date' => 'required|date',
        'record_type' => 'required|in:checkup,symptom,lab_result,post_mortem,consultation',
        'severity' => 'required|in:info,warning,critical',
    ]);
    
    if ($validator->fails()) {
        return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
    }
    
    $record = HealthRecord::findOrFail($id);
    
    try {
        $record->update($request->all());
        return response()->json(['success' => true, 'message' => 'Health record updated successfully']);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
}

// Add these methods at the end of your HealthRecordController class

/**
 * Display Drug Formulary page
 */
public function drugFormulary()
{
    return view('health-records.drug-formulary');
}

/**
 * Display Disease Guide page
 */
public function diseaseGuide()
{
    return view('health-records.disease-guide');
}

/**
 * Display Health Calendar page
 */
/**
 * Display Health Calendar page
 */
public function healthCalendar(Request $request)
{
    $year = $request->get('year', Carbon::now()->year);
    $month = $request->get('month', Carbon::now()->month);
    
    // Get vaccinations for the selected month
    $vaccinations = Vaccination::with(['flock'])
        ->whereYear('administration_date', $year)
        ->whereMonth('administration_date', $month)
        ->get()
        ->map(function($v) {
            return [
                'id' => $v->id,
                'type' => 'vaccination',
                'title' => '💉 ' . $v->vaccine_name,
                'flock' => $v->flock->flock_number ?? 'N/A',
                'date' => $v->administration_date->format('Y-m-d'),
                'details' => "Vaccine: {$v->vaccine_name}<br>Disease: {$v->disease_target}<br>Route: {$v->route}",
                'color' => 'success'
            ];
        });
    
    // Get treatments for the selected month
    $treatments = Treatment::with(['flock'])
        ->whereYear('start_date', $year)
        ->whereMonth('start_date', $month)
        ->get()
        ->map(function($t) {
            $withdrawalText = $t->withdrawal_days ? " (Withdrawal: {$t->withdrawal_days} days)" : '';
            return [
                'id' => $t->id,
                'type' => 'treatment',
                'title' => '💊 ' . $t->diagnosis,
                'flock' => $t->flock->flock_number ?? 'N/A',
                'date' => $t->start_date->format('Y-m-d'),
                'details' => "Product: {$t->product_name}<br>Dosage: {$t->dosage}<br>Route: {$t->administration_route}{$withdrawalText}",
                'color' => 'warning'
            ];
        });
    
    // Get health records for the selected month
    $healthRecords = HealthRecord::with(['flock'])
        ->whereYear('record_date', $year)
        ->whereMonth('record_date', $month)
        ->get()
        ->map(function($hr) {
            $severityIcon = $hr->severity === 'critical' ? '🔴' : ($hr->severity === 'warning' ? '🟡' : '🔵');
            return [
                'id' => $hr->id,
                'type' => 'health_record',
                'title' => $severityIcon . ' ' . ($hr->condition ?? 'Health Check'),
                'flock' => $hr->flock->flock_number ?? 'N/A',
                'date' => $hr->record_date->format('Y-m-d'),
                'details' => "Type: {$hr->record_type}<br>Severity: {$hr->severity}<br>Affected: {$hr->affected_count} birds",
                'color' => $hr->severity === 'critical' ? 'danger' : ($hr->severity === 'warning' ? 'warning' : 'info')
            ];
        });
    
    // Merge all events
    $events = $vaccinations->concat($treatments)->concat($healthRecords);
    
    // Group events by date
    $eventsByDate = [];
    foreach ($events as $event) {
        $eventsByDate[$event['date']][] = $event;
    }
    
    // Generate calendar data
    $calendarData = $this->generateCalendarData($year, $month, $eventsByDate);
    
    $prevMonth = Carbon::create($year, $month, 1)->subMonth();
    $nextMonth = Carbon::create($year, $month, 1)->addMonth();
    
    return view('health-records.calendar', compact(
        'calendarData', 
        'year', 
        'month', 
        'prevMonth', 
        'nextMonth'
    ));
}

/**
 * Generate calendar grid data
 */
private function generateCalendarData($year, $month, $eventsByDate)
{
    $firstDayOfMonth = Carbon::create($year, $month, 1);
    $lastDayOfMonth = $firstDayOfMonth->copy()->endOfMonth();
    
    // Get the starting day of the calendar (Sunday or Monday)
    $startDay = $firstDayOfMonth->copy()->startOfWeek(Carbon::SUNDAY);
    $endDay = $lastDayOfMonth->copy()->endOfWeek(Carbon::SATURDAY);
    
    $calendar = [];
    $currentDay = $startDay->copy();
    
    while ($currentDay <= $endDay) {
        $week = [];
        for ($i = 0; $i < 7; $i++) {
            $dateStr = $currentDay->format('Y-m-d');
            $isCurrentMonth = $currentDay->month == $month;
            $isToday = $currentDay->isToday();
            
            $week[] = [
                'date' => $dateStr,
                'day' => $currentDay->day,
                'isCurrentMonth' => $isCurrentMonth,
                'isToday' => $isToday,
                'events' => $eventsByDate[$dateStr] ?? []
            ];
            $currentDay->addDay();
        }
        $calendar[] = $week;
    }
    
    return $calendar;
}


/**
 * Drug Formulary Modal Content (for AJAX)
 */
public function drugFormularyModal()
{
    $html = '
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Drug Formulary Guide</strong> - Common poultry medications, dosages, and withdrawal periods.
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6 mb-3">
                <div class="card h-100">
                    <div class="card-header bg-success text-white">
                        <i class="fas fa-syringe me-2"></i> Vaccines
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr><th>Vaccine</th><th>Disease</th><th>Administration</th><th>Schedule</th></tr>
                            </thead>
                            <tbody>
                                <tr><td>Newcastle (LaSota)</td><td>Newcastle Disease</td><td>Drinking water/Spray</td><td>Day 7, 21</td></tr>
                                <tr><td>IB (H120)</td><td>Infectious Bronchitis</td><td>Drinking water/Spray</td><td>Day 1, 14</td></tr>
                                <tr><td>Gumboro (IBD)</td><td>Infectious Bursal Disease</td><td>Drinking water</td><td>Day 14, 24</td></tr>
                                <tr><td>Fowl Pox</td><td>Fowl Pox</td><td>Wing web stab</td><td>Week 8-10</td></tr>
                                <tr><td>Coccidiosis</td><td>Coccidiosis</td><td>Feed/Water</td><td>Day 1-35</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-3">
                <div class="card h-100">
                    <div class="card-header bg-warning text-dark">
                        <i class="fas fa-capsules me-2"></i> Antibiotics
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr><th>Drug</th><th>Use</th><th>Dosage</th><th>Withdrawal</th></tr>
                            </thead>
                            <tbody>
                                <tr><td>Amoxicillin</td><td>Respiratory/Gut infections</td><td>20mg/kg</td><td>5 days</td></tr>
                                <tr><td>Enrofloxacin</td><td>CRD/E.coli</td><td>10mg/kg</td><td>7 days</td></tr>
                                <tr><td>Tylosin</td><td>CRD/Chronic respiratory</td><td>500mg/L water</td><td>3 days</td></tr>
                                <tr><td>Doxycycline</td><td>Mycoplasma</td><td>200mg/L water</td><td>5 days</td></tr>
                                <tr><td>Sulfonamides</td><td>Coccidiosis/Colibacillosis</td><td>1g/L water</td><td>7 days</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-3">
                <div class="card h-100">
                    <div class="card-header bg-danger text-white">
                        <i class="fas fa-pills me-2"></i> Antiparasitics
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr><th>Drug</th><th>Target Parasite</th><th>Dosage</th><th>Withdrawal</th></tr>
                            </thead>
                            <tbody>
                                <tr><td>Piperazine</td><td>Roundworms</td><td>2g/L water</td><td>3 days</td></tr>
                                <tr><td>Fenbendazole</td><td>Roundworms/Tapeworms</td><td>30mg/kg</td><td>7 days</td></tr>
                                <tr><td>Ivermectin</td><td>Mites/Lice/Worms</td><td>0.2mg/kg</td><td>7 days</td></tr>
                                <tr><td>Levamisole</td><td>Roundworms</td><td>20mg/kg</td><td>5 days</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-3">
                <div class="card h-100">
                    <div class="card-header bg-info text-white">
                        <i class="fas fa-vitamin me-2"></i> Vitamins & Supplements
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr><th>Supplement</th><th>Purpose</th><th>Dosage</th><th>When to Use</th></tr>
                            </thead>
                            <tbody>
                                <tr><td>Vitamins ADE</td><td>Growth/Bone health</td><td>1ml/L water</td><td>Weekly</td></tr>
                                <tr><td>Electrolytes</td><td>Heat stress/Dehydration</td><td>2g/L water</td><td>During stress</td></tr>
                                <tr><td>Probiotics</td><td>Gut health</td><td>1g/L water</td><td>After antibiotics</td></tr>
                                <tr><td>Calcium</td><td>Eggshell quality</td><td>As directed</td><td>Laying period</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="alert alert-secondary mt-2">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Important:</strong> Always follow veterinarian prescriptions and observe withdrawal periods before slaughter.
        </div>
    </div>';
    
    return response()->json(['success' => true, 'html' => $html]);
}

/**
 * Disease Guide Modal Content (for AJAX)
 */
public function diseaseGuideModal()
{
    $html = '
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Disease Guide</strong> - Common poultry diseases, symptoms, and treatment recommendations.
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6 mb-3">
                <div class="card h-100">
                    <div class="card-header bg-danger text-white">
                        <i class="fas fa-virus me-2"></i> Viral Diseases
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr><th>Disease</th><th>Symptoms</th><th>Treatment</th><th>Prevention</th></tr>
                            </thead>
                            <tbody>
                                <tr><td><strong>Newcastle Disease</strong></td><td>Respiratory distress, greenish diarrhea, nervous signs</td><td>Supportive care, antibiotics for secondary infections</td><td>Vaccination at day 7, 21</td></tr>
                                <tr><td><strong>Infectious Bronchitis</strong></td><td>Coughing, sneezing, watery eyes, reduced egg production</td><td>Antibiotics, warm environment</td><td>Vaccination day 1, 14</td></tr>
                                <tr><td><strong>Gumboro (IBD)</strong></td><td>Depression, watery droppings, vent pecking</td><td>Immune boosters, electrolytes</td><td>Vaccination day 14, 24</td></tr>
                                <tr><td><strong>Marek\'s Disease</strong></td><td>Paralysis, grey iris, tumors</td><td>No treatment, cull affected</td><td>Vaccination day 1</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-3">
                <div class="card h-100">
                    <div class="card-header bg-warning text-dark">
                        <i class="fas fa-bacterium me-2"></i> Bacterial Diseases
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr><th>Disease</th><th>Symptoms</th><th>Treatment</th><th>Withdrawal</th></tr>
                            </thead>
                            <tbody>
                                <tr><td><strong>CRD (Mycoplasma)</strong></td><td>Sneezing, nasal discharge, swollen sinuses</td><td>Tylosin/Tiamulin</td><td>3-5 days</td></tr>
                                <tr><td><strong>Colibacillosis</strong></td><td>Lethargy, diarrhea, respiratory distress</td><td>Amoxicillin/Enrofloxacin</td><td>5-7 days</td></tr>
                                <tr><td><strong>Fowl Cholera</strong></td><td>Sudden death, fever, diarrhea</td><td>Sulfonamides</td><td>7 days</td></tr>
                                <tr><td><strong>Necrotic Enteritis</strong></td><td>Diarrhea, depression, sudden death</td><td>Bacitracin/Penicillin</td><td>5 days</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-3">
                <div class="card h-100">
                    <div class="card-header bg-success text-white">
                        <i class="fas fa-bug me-2"></i> Parasitic Diseases
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr><th>Parasite</th><th>Symptoms</th><th>Treatment</th><th>Prevention</th></tr>
                            </thead>
                            <tbody>
                                <tr><td><strong>Coccidiosis</strong></td><td>Bloody diarrhea, ruffled feathers, weight loss</td><td>Amprolium/Toltrazuril</td><td>Good litter management</td></tr>
                                <tr><td><strong>Roundworms</strong></td><td>Weight loss, diarrhea, poor growth</td><td>Piperazine/Fenbendazole</td><td>Regular deworming</td></tr>
                                <tr><td><strong>Red Mites</strong></td><td>Anemia, reduced egg production, restlessness</td><td>Ivermectin/Permethrin</td><td>Clean housing</td></tr>
                                <tr><td><strong>Scaly Leg Mites</strong></td><td>Raised scales on legs</td><td>Petroleum jelly/Ivermectin</td><td>Isolate infected</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-3">
                <div class="card h-100">
                    <div class="card-header bg-info text-white">
                        <i class="fas fa-apple-alt me-2"></i> Nutritional Disorders
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr><th>Condition</th><th>Symptoms</th><th>Cause</th><th>Solution</th></tr>
                            </thead>
                            <tbody>
                                <tr><td><strong>Rickets</strong></td><td>Soft bones, leg weakness</td><td>Calcium/Vitamin D deficiency</td><td>Supplement calcium</td></tr>
                                <tr><td><strong>Fatty Liver</strong></td><td>Sudden death, liver rupture</td><td>High energy diet</td><td>Balanced feed</td></tr>
                                <tr><td><strong>Cannibalism</strong></td><td>Feather pecking, vent picking</td><td>Overcrowding, nutrient deficiency</td><td>Beak trimming, increase space</td></tr>
                                <tr><td><strong>Egg Binding</strong></td><td>Straining, no egg production</td><td>Calcium deficiency</td><td>Calcium supplements</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="alert alert-warning mt-2">
            <i class="fas fa-phone-alt me-2"></i>
            <strong>Emergency:</strong> If you notice unusual mortality or severe symptoms, contact your veterinarian immediately.
        </div>
    </div>';
    
    return response()->json(['success' => true, 'html' => $html]);
}

/**
 * Health Calendar Modal Content (for AJAX)
 */
public function healthCalendarModal(Request $request)
{
    $year = $request->get('year', Carbon::now()->year);
    $month = $request->get('month', Carbon::now()->month);
    
    // Get vaccinations for the selected month
    $vaccinations = Vaccination::with(['flock'])
        ->whereYear('administration_date', $year)
        ->whereMonth('administration_date', $month)
        ->get()
        ->map(function($v) {
            return [
                'id' => $v->id,
                'type' => 'vaccination',
                'title' => '💉 ' . $v->vaccine_name,
                'flock' => $v->flock->flock_number ?? 'N/A',
                'date' => $v->administration_date->format('Y-m-d'),
                'color' => 'success'
            ];
        });
    
    // Get treatments for the selected month
    $treatments = Treatment::with(['flock'])
        ->whereYear('start_date', $year)
        ->whereMonth('start_date', $month)
        ->get()
        ->map(function($t) {
            return [
                'id' => $t->id,
                'type' => 'treatment',
                'title' => '💊 ' . $t->diagnosis,
                'flock' => $t->flock->flock_number ?? 'N/A',
                'date' => $t->start_date->format('Y-m-d'),
                'color' => 'warning'
            ];
        });
    
    // Get health records for the selected month
    $healthRecords = HealthRecord::with(['flock'])
        ->whereYear('record_date', $year)
        ->whereMonth('record_date', $month)
        ->get()
        ->map(function($hr) {
            $severityIcon = $hr->severity === 'critical' ? '🔴' : ($hr->severity === 'warning' ? '🟡' : '🔵');
            return [
                'id' => $hr->id,
                'type' => 'health_record',
                'title' => $severityIcon . ' ' . ($hr->condition ?? 'Health Check'),
                'flock' => $hr->flock->flock_number ?? 'N/A',
                'date' => $hr->record_date->format('Y-m-d'),
                'color' => $hr->severity === 'critical' ? 'danger' : ($hr->severity === 'warning' ? 'warning' : 'info')
            ];
        });
    
    // Merge all events
    $events = $vaccinations->concat($treatments)->concat($healthRecords);
    
    // Group events by date
    $eventsByDate = [];
    foreach ($events as $event) {
        $eventsByDate[$event['date']][] = $event;
    }
    
    // Generate calendar HTML
    $firstDayOfMonth = Carbon::create($year, $month, 1);
    $lastDayOfMonth = $firstDayOfMonth->copy()->endOfMonth();
    $startDay = $firstDayOfMonth->copy()->startOfWeek(Carbon::SUNDAY);
    $endDay = $lastDayOfMonth->copy()->endOfWeek(Carbon::SATURDAY);
    
    $calendarHtml = '
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <button class="btn btn-sm btn-outline-primary" id="calendarPrevMonth" data-year="' . ($firstDayOfMonth->copy()->subMonth()->year) . '" data-month="' . ($firstDayOfMonth->copy()->subMonth()->month) . '">
                <i class="fas fa-chevron-left"></i> ' . $firstDayOfMonth->copy()->subMonth()->format('M') . '
            </button>
            <h4 class="mb-0">' . $firstDayOfMonth->format('F Y') . '</h4>
            <button class="btn btn-sm btn-outline-primary" id="calendarNextMonth" data-year="' . ($firstDayOfMonth->copy()->addMonth()->year) . '" data-month="' . ($firstDayOfMonth->copy()->addMonth()->month) . '">
                ' . $firstDayOfMonth->copy()->addMonth()->format('M') . ' <i class="fas fa-chevron-right"></i>
            </button>
        </div>
        
        <div class="calendar-weekdays">
            <div class="calendar-weekday">Sun</div>
            <div class="calendar-weekday">Mon</div>
            <div class="calendar-weekday">Tue</div>
            <div class="calendar-weekday">Wed</div>
            <div class="calendar-weekday">Thu</div>
            <div class="calendar-weekday">Fri</div>
            <div class="calendar-weekday">Sat</div>
        </div>';
    
    $currentDay = $startDay->copy();
    while ($currentDay <= $endDay) {
        $calendarHtml .= '<div class="calendar-week">';
        for ($i = 0; $i < 7; $i++) {
            $dateStr = $currentDay->format('Y-m-d');
            $isCurrentMonth = $currentDay->month == $month;
            $isToday = $currentDay->isToday();
            $dayEvents = $eventsByDate[$dateStr] ?? [];
            
            $calendarHtml .= '<div class="calendar-day ' . (!$isCurrentMonth ? 'other-month' : '') . ($isToday ? ' today' : '') . '">
                <div class="calendar-day-header">
                    <span class="calendar-day-number">' . $currentDay->day . '</span>
                </div>
                <div class="calendar-events">';
            
            foreach ($dayEvents as $event) {
                $calendarHtml .= '<div class="calendar-event event-' . $event['color'] . '" onclick="showEventDetails(\'' . $event['type'] . '\', ' . $event['id'] . ')">
                    <div class="event-title">' . $event['title'] . '</div>
                    <div class="event-flock"><i class="fas fa-chicken"></i> ' . $event['flock'] . '</div>
                </div>';
            }
            
            $calendarHtml .= '</div></div>';
            $currentDay->addDay();
        }
        $calendarHtml .= '</div>';
    }
    
    $calendarHtml .= '</div>';
    
    return response()->json(['success' => true, 'html' => $calendarHtml]);
}
}