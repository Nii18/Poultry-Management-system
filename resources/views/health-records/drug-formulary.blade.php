@extends('layouts.master')

@section('title', 'Drug Formulary')

@section('content')
<div class="container-fluid px-4">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <div class="d-flex align-items-center gap-3">
                    <div class="page-icon bg-info-soft">
                        <i class="fas fa-capsules fs-1 text-info"></i>
                    </div>
                    <div>
                        <h1 class="page-title mb-1">Drug Formulary</h1>
                        <p class="page-description text-muted mb-0">Poultry medications and treatment reference</p>
                    </div>
                </div>
            </div>
            <div class="col-auto">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('health-records.index') }}">Health Records</a></li>
                        <li class="breadcrumb-item active">Drug Formulary</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Drug Formulary Guide</strong> - Common poultry medications, dosages, and withdrawal periods. Always consult with a veterinarian before administering any medication.
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-header bg-success text-white">
                    <i class="fas fa-syringe me-2"></i> Vaccines
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead class="table-light">
                                <tr><th>Vaccine</th><th>Disease</th><th>Administration</th><th>Schedule</th></tr>
                            </thead>
                            <tbody>
                                <tr><td>Newcastle (LaSoda)</td><td>Newcastle Disease</td><td>Drinking water/Spray</td><td>Day 7, 21</td></tr>
                                <tr><td>IB (H120)</td><td>Infectious Bronchitis</td><td>Drinking water/Spray</td><td>Day 1, 14</td></tr>
                                <tr><td>Gumboro (IBD)</td><td>Infectious Bursal Disease</td><td>Drinking water</td><td>Day 14, 24</td></tr>
                                <tr><td>Fowl Pox</td><td>Fowl Pox</td><td>Wing web stab</td><td>Week 8-10</td></tr>
                                <tr><td>Coccidiosis Vaccine</td><td>Coccidiosis</td><td>Feed/Water</td><td>Day 1-35</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-header bg-warning text-dark">
                    <i class="fas fa-capsules me-2"></i> Antibiotics
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead class="table-light">
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
        </div>

        <div class="col-md-6 mb-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-header bg-danger text-white">
                    <i class="fas fa-pills me-2"></i> Antiparasitics
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead class="table-light">
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
        </div>

        <div class="col-md-6 mb-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-header bg-info text-white">
                    <i class="fas fa-vitamin me-2"></i> Vitamins & Supplements
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead class="table-light">
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
    </div>

    <div class="alert alert-secondary mt-2">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <strong>Important:</strong> Always follow veterinarian prescriptions and observe withdrawal periods before slaughter. Dosages may vary based on bird weight and condition.
    </div>
</div>
@endsection