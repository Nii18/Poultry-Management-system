{{-- resources/views/daily-logs/show.blade.php --}}
@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6 col-12">
                <h2>Daily Log Details</h2>
                <p class="mb-0 text-title-gray">{{ $dailyLog->log_date->format('Y-m-d') }} - {{ $dailyLog->flock->flock_number ?? 'N/A' }}</p>
            </div>
            <div class="col-sm-6 col-12">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="iconly-Home icli svg-color"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{ route('daily-logs.index') }}">Daily Logs</a></li>
                    <li class="breadcrumb-item active">Log #{{ $dailyLog->id }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3>Basic Information</h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr><th width="35%">Log Date</th><td>{{ $dailyLog->log_date->format('Y-m-d') }}</td></tr>
                        <tr><th>Flock</th>
                            <td>
                                <a href="{{ route('flocks.show', $dailyLog->flock_id) }}" class="text-primary">
                                    {{ $dailyLog->flock->flock_number ?? 'N/A' }}
                                </a>
                            </td>
                        </tr>
                        <tr><th>Species</th><td>{{ $dailyLog->flock->species->name ?? 'N/A' }}</td></tr>
                        <tr><th>Recorded By</th><td>{{ $dailyLog->creator->name ?? 'N/A' }}</td></tr>
                        <tr><th>Mortality Rate</th>
                            <td>
                                <span class="badge {{ $mortalityRate > 5 ? 'badge-danger' : 'badge-success' }}">
                                    {{ $mortalityRate }}%
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3>Performance Metrics</h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr><th width="35%">Mortality Count</th><td>{{ $dailyLog->mortality_count }}</td></tr>
                        <tr><th>Culling Count</th><td>{{ $dailyLog->culling_count }}</td></tr>
                        <tr><th>Total Loss</th><td>{{ $dailyLog->mortality_count + $dailyLog->culling_count }}</td></tr>
                        <tr><th>Feed Intake</th><td>{{ number_format($dailyLog->feed_intake_kg) }} kg</td></tr>
                        <tr><th>Water Consumption</th><td>{{ number_format($dailyLog->water_consumption_liters) }} liters</td></tr>
                        <tr><th>Average Weight</th><td>{{ $dailyLog->average_weight_kg ? number_format($dailyLog->average_weight_kg, 2) . ' kg' : 'N/A' }}</td></tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3>Environmental Data</h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr><th width="35%">Temperature Range</th><td>{{ $temperatureRange ?? 'N/A' }}</td></tr>
                        <tr><th>Min Temperature</th><td>{{ $dailyLog->min_temperature_c }}°C</td></tr>
                        <tr><th>Max Temperature</th><td>{{ $dailyLog->max_temperature_c }}°C</td></tr>
                        <tr><th>Min Humidity</th><td>{{ $dailyLog->min_humidity }}%</td></tr>
                        <tr><th>Max Humidity</th><td>{{ $dailyLog->max_humidity }}%</td></tr>
                        <tr><th>Ammonia Level</th>
                            <td>
                                {{ $dailyLog->ammonia_ppm }} ppm
                                @if($dailyLog->ammonia_ppm && $dailyLog->ammonia_ppm > 25)
                                    <span class="badge badge-danger">High</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3>Species-Specific Metrics</h3>
                </div>
                <div class="card-body">
                    @if($dailyLog->species_metrics && count($dailyLog->species_metrics) > 0)
                        <table class="table table-bordered">
                            @foreach($dailyLog->species_metrics as $key => $value)
                                <tr>
                                    <th width="40%">{{ ucwords(str_replace('_', ' ', $key)) }}</th>
                                    <td>{{ $value }}</td>
                                </tr>
                            @endforeach
                        </table>
                    @else
                        <p class="text-muted text-center">No species-specific metrics recorded.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($dailyLog->notes)
    <div class="row mt-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3>Notes</h3>
                </div>
                <div class="card-body">
                    <p>{{ $dailyLog->notes }}</p>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="row mt-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <a href="{{ route('daily-logs.edit', $dailyLog->id) }}" class="btn btn-warning">
                        <i class="fa fa-edit"></i> Edit
                    </a>
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                        <i class="fa fa-trash"></i> Delete
                    </button>
                    <a href="{{ route('daily-logs.index') }}" class="btn btn-secondary">Back to List</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('daily-logs.destroy', $dailyLog->id) }}">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h5 class="modal-title">Delete Daily Log</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this daily log?</p>
                    <p class="text-danger">This action will restore the mortality/culling counts to the flock's current count.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection