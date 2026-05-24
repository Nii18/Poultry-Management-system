{{-- resources/views/species/edit.blade.php --}}
@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6 col-12">
                <h2>Edit Species</h2>
                <p class="mb-0 text-title-gray">Update {{ $species->name }} details</p>
            </div>
            <div class="col-sm-6 col-12">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="iconly-Home icli svg-color"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{ route('species.index') }}">Species</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('species.show', $species->id) }}">{{ $species->name }}</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3>Edit Species: {{ $species->name }}</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('species.update', $species->id) }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                    value="{{ old('name', $species->name) }}" required>
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Code <span class="text-danger">*</span></label>
                                <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" 
                                    value="{{ old('code', $species->code) }}" maxlength="5" required>
                                @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Icon Class</label>
                                <input type="text" name="icon" class="form-control @error('icon') is-invalid @enderror" 
                                    value="{{ old('icon', $species->icon) }}" placeholder="e.g., fas fa-drumstick">
                                <small class="text-muted">FontAwesome icon class</small>
                                @error('icon') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Color (Hex)</label>
                                <input type="color" name="color_hex" class="form-control @error('color_hex') is-invalid @enderror" 
                                    value="{{ old('color_hex', $species->color_hex) }}">
                                <small class="text-muted">Pick a color for this species</small>
                                @error('color_hex') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                                    rows="2">{{ old('description', $species->description) }}</textarea>
                                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-12 mb-3">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">Lifecycle Parameters</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Gestation Days</label>
                                                <input type="number" name="gestation_days" class="form-control @error('gestation_days') is-invalid @enderror" 
                                                    value="{{ old('gestation_days', $species->gestation_days) }}" min="0">
                                                @error('gestation_days') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                            </div>
                                            
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Weaning Days</label>
                                                <input type="number" name="weaning_days" class="form-control @error('weaning_days') is-invalid @enderror" 
                                                    value="{{ old('weaning_days', $species->weaning_days) }}" min="0">
                                                @error('weaning_days') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                            </div>
                                            
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Sexual Maturity (Days)</label>
                                                <input type="number" name="sexual_maturity_days" class="form-control @error('sexual_maturity_days') is-invalid @enderror" 
                                                    value="{{ old('sexual_maturity_days', $species->sexual_maturity_days) }}" min="0">
                                                @error('sexual_maturity_days') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                            </div>
                                            
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Market Age (Days)</label>
                                                <input type="number" name="market_age_days" class="form-control @error('market_age_days') is-invalid @enderror" 
                                                    value="{{ old('market_age_days', $species->market_age_days) }}" min="0">
                                                @error('market_age_days') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                            </div>
                                            
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Market Weight (kg)</label>
                                                <input type="number" name="market_weight_kg" class="form-control @error('market_weight_kg') is-invalid @enderror" 
                                                    value="{{ old('market_weight_kg', $species->market_weight_kg) }}" step="0.01" min="0">
                                                @error('market_weight_kg') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                            </div>
                                            
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Lifespan (Years)</label>
                                                <input type="number" name="lifespan_years" class="form-control @error('lifespan_years') is-invalid @enderror" 
                                                    value="{{ old('lifespan_years', $species->lifespan_years) }}" step="0.5" min="0">
                                                @error('lifespan_years') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-12 mb-3">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">Default Performance Metrics (JSON)</h5>
                                    </div>
                                    <div class="card-body">
                                        <textarea name="default_metrics" class="form-control @error('default_metrics') is-invalid @enderror" 
                                            rows="4" placeholder='{"fcr_target": 1.8, "mortality_target": 5, "egg_production_target": 85}'>{{ old('default_metrics', json_encode($species->default_metrics, JSON_PRETTY_PRINT)) }}</textarea>
                                        <small class="text-muted">Enter default metrics as JSON format</small>
                                        @error('default_metrics') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-12 mb-3">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">Growth Standards (JSON)</h5>
                                    </div>
                                    <div class="card-body">
                                        <textarea name="growth_standards" class="form-control @error('growth_standards') is-invalid @enderror" 
                                            rows="4" placeholder='{"week1": 0.18, "week2": 0.45, "week3": 0.85, "week4": 1.35, "week5": 1.95, "week6": 2.5}'>{{ old('growth_standards', json_encode($species->growth_standards, JSON_PRETTY_PRINT)) }}</textarea>
                                        <small class="text-muted">Enter growth standards as JSON format (period => weight)</small>
                                        @error('growth_standards') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-12 mb-3">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">Health Indicators (JSON)</h5>
                                    </div>
                                    <div class="card-body">
                                        <textarea name="health_indicators" class="form-control @error('health_indicators') is-invalid @enderror" 
                                            rows="4" placeholder='{"normal_temperature": 41.5, "normal_heart_rate": 250}'>{{ old('health_indicators', json_encode($species->health_indicators, JSON_PRETTY_PRINT)) }}</textarea>
                                        <small class="text-muted">Enter health indicators as JSON format</small>
                                        @error('health_indicators') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="form-check">
                                    <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active" 
                                        {{ old('is_active', $species->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">Active (available for selection)</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">Update Species</button>
                            <a href="{{ route('species.show', $species->id) }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Helper function to validate JSON format before submission
    document.querySelector('form').addEventListener('submit', function(e) {
        const jsonFields = ['default_metrics', 'growth_standards', 'health_indicators'];
        
        for (let field of jsonFields) {
            const textarea = document.querySelector(`textarea[name="${field}"]`);
            if (textarea && textarea.value.trim()) {
                try {
                    JSON.parse(textarea.value);
                } catch (e) {
                    alert(`Invalid JSON format in ${field.replace('_', ' ')}. Please fix before saving.`);
                    textarea.focus();
                    e.preventDefault();
                    return false;
                }
            }
        }
    });
    
    // Format JSON nicely when leaving the field
    const jsonFields = ['default_metrics', 'growth_standards', 'health_indicators'];
    jsonFields.forEach(field => {
        const textarea = document.querySelector(`textarea[name="${field}"]`);
        if (textarea) {
            textarea.addEventListener('blur', function() {
                if (this.value.trim()) {
                    try {
                        const parsed = JSON.parse(this.value);
                        this.value = JSON.stringify(parsed, null, 2);
                    } catch (e) {
                        // Invalid JSON, leave as is
                    }
                }
            });
        }
    });
</script>
@endpush
@endsection