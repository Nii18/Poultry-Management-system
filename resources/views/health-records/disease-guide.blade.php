@extends('layouts.master')

@section('title', 'Disease Guide')

@section('content')
<div class="container-fluid px-4">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <div class="d-flex align-items-center gap-3">
                    <div class="page-icon bg-danger-soft">
                        <i class="fas fa-book-medical fs-1 text-danger"></i>
                    </div>
                    <div>
                        <h1 class="page-title mb-1">Disease Guide</h1>
                        <p class="page-description text-muted mb-0">Common poultry diseases, symptoms, and treatment recommendations</p>
                    </div>
                </div>
            </div>
            <div class="col-auto">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('health-records.index') }}">Health Records</a></li>
                        <li class="breadcrumb-item active">Disease Guide</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Disease Guide</strong> - Common poultry diseases, symptoms, and treatment recommendations. Early detection is key to successful treatment.
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-header bg-danger text-white">
                    <i class="fas fa-virus me-2"></i> Viral Diseases
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead class="table-light">
                                <tr><th>Disease</th><th>Symptoms</th><th>Treatment</th><th>Prevention</th></tr>
                            </thead>
                            <tbody>
                                <tr><td><strong>Newcastle Disease</strong></td><td>Respiratory distress, greenish diarrhea, nervous signs</td><td>Supportive care, antibiotics for secondary infections</td><td>Vaccination at day 7, 21</td></tr>
                                <tr><td><strong>Infectious Bronchitis</strong></td><td>Coughing, sneezing, watery eyes, reduced egg production</td><td>Antibiotics, warm environment</td><td>Vaccination day 1, 14</td></tr>
                                <tr><td><strong>Gumboro (IBD)</strong></td><td>Depression, watery droppings, vent pecking</td><td>Immune boosters, electrolytes</td><td>Vaccination day 14, 24</td></tr>
                                <tr><td><strong>Marek's Disease</strong></td><td>Paralysis, grey iris, tumors</td><td>No treatment, cull affected</td><td>Vaccination day 1</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-header bg-warning text-dark">
                    <i class="fas fa-bacterium me-2"></i> Bacterial Diseases
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead class="table-light">
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
        </div>

        <div class="col-md-6 mb-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-header bg-success text-white">
                    <i class="fas fa-bug me-2"></i> Parasitic Diseases
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead class="table-light">
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
        </div>

        <div class="col-md-6 mb-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-header bg-info text-white">
                    <i class="fas fa-apple-alt me-2"></i> Nutritional Disorders
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead class="table-light">
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
    </div>

    <div class="alert alert-warning mt-2">
        <i class="fas fa-phone-alt me-2"></i>
        <strong>Emergency:</strong> If you notice unusual mortality or severe symptoms, contact your veterinarian immediately.
    </div>
</div>
@endsection