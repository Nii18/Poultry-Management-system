{{-- resources/views/daily-logs/create.blade.php --}}
@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6 col-12">
                <h2>Add Daily Log</h2>
                <p class="mb-0 text-title-gray">Record daily observations and metrics</p>
            </div>
            <div class="col-sm-6 col-12">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="iconly-Home icli svg-color"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{ route('daily-logs.index') }}">Daily Logs</a></li>
                    <li class="breadcrumb-item active">Create</li>
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
                    <h3>New Daily Log</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('daily-logs.store') }}">
                        @csrf
                        
                        @if($flock)
                            <input type="hidden" name="flock_id" value="{{ $flock->id }}">
                            <div class="alert alert-info">
                                <i class="fa fa-info-circle"></i> Recording log for: <strong>{{ $flock->flock_number }}</strong> ({{ $flock->species->name }} - {{ $flock->breed_variety }})
                            </div>
                        @else
                            <div class="mb-3">
                                <label class="form-label">Select Flock <span class="text-danger">*</span></label>
                                <select name="flock_id" class="form-select @error('flock_id') is-invalid @enderror" required>
                                    <option value="">Choose a flock</option>
                                    @foreach($activeFlocks as $flockOption)
                                        <option value="{{ $flockOption->id }}" {{ old('flock_id') == $flockOption->id ? 'selected' : '' }}>
                                            {{ $flockOption->flock_number }} - {{ $flockOption->breed_variety }} (Day {{ $flockOption->age_in_days }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('flock_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        @endif
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Log Date <span class="text-danger">*</span></label>
                                <input type="date" name="log_date" class="form-control @error('log_date') is-invalid @enderror" value="{{ old('log_date', date('Y-m-d')) }}" required>
                                @error('log_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Mortality Count</label>
                                <input type="number" name="mortality_count" class="form-control @error('mortality_count') is-invalid @enderror" value="{{ old('mortality_count', 0) }}" min="0">
                                @error('mortality_count') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Culling Count</label>
                                <input type="number" name="culling_count" class="form-control @error('culling_count') is-invalid @enderror" value="{{ old('culling_count', 0) }}" min="0">
                                @error('culling_count') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Feed Intake (kg)</label>
                                <input type="number" name="feed_intake_kg" class="form-control @error('feed_intake_kg') is-invalid @enderror" value="{{ old('feed_intake_kg') }}" step="0.1" min="0">
                                @error('feed_intake_kg') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Water Consumption (liters)</label>
                                <input type="number" name="water_consumption_liters" class="form-control @error('water_consumption_liters') is-invalid @enderror" value="{{ old('water_consumption_liters') }}" step="0.1" min="0">
                                @error('water_consumption_liters') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Average Weight (kg)</label>
                                <input type="number" name="average_weight_kg" class="form-control @error('average_weight_kg') is-invalid @enderror" value="{{ old('average_weight_kg') }}" step="0.01" min="0">
                                @error('average_weight_kg') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        
                        <div class="card mt-3">
                            <div class="card-header">
                                <h5 class="mb-0">Environmental Data</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Min Temperature (°C)</label>
                                        <input type="number" name="min_temperature_c" class="form-control @error('min_temperature_c') is-invalid @enderror" value="{{ old('min_temperature_c') }}" step="0.1">
                                        @error('min_temperature_c') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Max Temperature (°C)</label>
                                        <input type="number" name="max_temperature_c" class="form-control @error('max_temperature_c') is-invalid @enderror" value="{{ old('max_temperature_c') }}" step="0.1">
                                        @error('max_temperature_c') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Min Humidity (%)</label>
                                        <input type="number" name="min_humidity" class="form-control @error('min_humidity') is-invalid @enderror" value="{{ old('min_humidity') }}" step="0.1" min="0" max="100">
                                        @error('min_humidity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Max Humidity (%)</label>
                                        <input type="number" name="max_humidity" class="form-control @error('max_humidity') is-invalid @enderror" value="{{ old('max_humidity') }}" step="0.1" min="0" max="100">
                                        @error('max_humidity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Ammonia (ppm)</label>
                                        <input type="number" name="ammonia_ppm" class="form-control @error('ammonia_ppm') is-invalid @enderror" value="{{ old('ammonia_ppm') }}" step="0.1" min="0">
                                        <small class="text-muted">Above 25ppm requires attention</small>
                                        @error('ammonia_ppm') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card mt-3">
                            <div class="card-header">
                                <h5 class="mb-0">Species-Specific Metrics (JSON)</h5>
                            </div>
                            <div class="card-body">
                                <textarea name="species_metrics" class="form-control @error('species_metrics') is-invalid @enderror" rows="4" placeholder='{"egg_production": 85, "egg_weight": 65}'></textarea>
                                <small class="text-muted">Enter species-specific metrics as JSON format</small>
                                @error('species_metrics') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        
                        <div class="mb-3 mt-3">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3">{{ old('notes') }}</textarea>
                            @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">Save Daily Log</button>
                            <a href="{{ route('daily-logs.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection