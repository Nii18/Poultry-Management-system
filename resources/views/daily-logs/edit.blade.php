{{-- resources/views/daily-logs/edit.blade.php --}}
@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6 col-12">
                <h2>Edit Daily Log</h2>
                <p class="mb-0 text-title-gray">{{ $dailyLog->log_date->format('Y-m-d') }} - {{ $dailyLog->flock->flock_number ?? 'N/A' }}</p>
            </div>
            <div class="col-sm-6 col-12">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="iconly-Home icli svg-color"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{ route('daily-logs.index') }}">Daily Logs</a></li>
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
                    <h3>Edit Daily Log</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('daily-logs.update', $dailyLog->id) }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Mortality Count</label>
                                <input type="number" name="mortality_count" class="form-control @error('mortality_count') is-invalid @enderror" value="{{ old('mortality_count', $dailyLog->mortality_count) }}" min="0">
                                @error('mortality_count') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Culling Count</label>
                                <input type="number" name="culling_count" class="form-control @error('culling_count') is-invalid @enderror" value="{{ old('culling_count', $dailyLog->culling_count) }}" min="0">
                                @error('culling_count') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Feed Intake (kg)</label>
                                <input type="number" name="feed_intake_kg" class="form-control @error('feed_intake_kg') is-invalid @enderror" value="{{ old('feed_intake_kg', $dailyLog->feed_intake_kg) }}" step="0.1" min="0">
                                @error('feed_intake_kg') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Water Consumption (liters)</label>
                                <input type="number" name="water_consumption_liters" class="form-control @error('water_consumption_liters') is-invalid @enderror" value="{{ old('water_consumption_liters', $dailyLog->water_consumption_liters) }}" step="0.1" min="0">
                                @error('water_consumption_liters') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Average Weight (kg)</label>
                                <input type="number" name="average_weight_kg" class="form-control @error('average_weight_kg') is-invalid @enderror" value="{{ old('average_weight_kg', $dailyLog->average_weight_kg) }}" step="0.01" min="0">
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
                                        <input type="number" name="min_temperature_c" class="form-control" value="{{ old('min_temperature_c', $dailyLog->min_temperature_c) }}" step="0.1">
                                    </div>
                                    
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Max Temperature (°C)</label>
                                        <input type="number" name="max_temperature_c" class="form-control" value="{{ old('max_temperature_c', $dailyLog->max_temperature_c) }}" step="0.1">
                                    </div>
                                    
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Min Humidity (%)</label>
                                        <input type="number" name="min_humidity" class="form-control" value="{{ old('min_humidity', $dailyLog->min_humidity) }}" step="0.1" min="0" max="100">
                                    </div>
                                    
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Max Humidity (%)</label>
                                        <input type="number" name="max_humidity" class="form-control" value="{{ old('max_humidity', $dailyLog->max_humidity) }}" step="0.1" min="0" max="100">
                                    </div>
                                    
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Ammonia (ppm)</label>
                                        <input type="number" name="ammonia_ppm" class="form-control" value="{{ old('ammonia_ppm', $dailyLog->ammonia_ppm) }}" step="0.1" min="0">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card mt-3">
                            <div class="card-header">
                                <h5 class="mb-0">Species-Specific Metrics (JSON)</h5>
                            </div>
                            <div class="card-body">
                                <textarea name="species_metrics" class="form-control" rows="4">{{ old('species_metrics', json_encode($dailyLog->species_metrics, JSON_PRETTY_PRINT)) }}</textarea>
                                <small class="text-muted">Enter species-specific metrics as JSON format</small>
                            </div>
                        </div>
                        
                        <div class="mb-3 mt-3">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="3">{{ old('notes', $dailyLog->notes) }}</textarea>
                        </div>
                        
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">Update Daily Log</button>
                            <a href="{{ route('daily-logs.show', $dailyLog->id) }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection