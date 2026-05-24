{{-- resources/views/houses/show.blade.php --}}
@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6 col-12">
                <h2>House: {{ $house->name }}</h2>
                <p class="mb-0 text-title-gray">Code: {{ $house->house_code }}</p>
            </div>
            <div class="col-sm-6 col-12">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="iconly-Home icli svg-color"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{ route('houses.index') }}">Houses</a></li>
                    <li class="breadcrumb-item active">{{ $house->name }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <!-- Stats Cards -->
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5>Total Flocks</h5>
                    <h3>{{ $stats['total_flocks'] }}</h3>
                    <small>Completed: {{ $stats['completed_flocks'] }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5>Total Animals</h5>
                    <h3>{{ number_format($stats['total_animals']) }}</h3>
                    <small>Lifetime total</small>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5>Capacity</h5>
                    <h3>{{ number_format($house->capacity) }}</h3>
                    <small>Maximum animals</small>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5>Occupancy Rate</h5>
                    <h3 class="{{ $stats['occupancy_rate'] > 80 ? 'text-success' : 'text-warning' }}">{{ number_format($stats['occupancy_rate'], 1) }}%</h3>
                    <small>Current occupancy</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Current Flock -->
    @if($currentFlock)
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3>Current Flock</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <strong>Flock Number:</strong> {{ $currentFlock->flock_number }}
                        </div>
                        <div class="col-md-3">
                            <strong>Species:</strong> {{ $currentFlock->species->name ?? 'N/A' }}
                        </div>
                        <div class="col-md-3">
                            <strong>Breed:</strong> {{ $currentFlock->breed_variety }}
                        </div>
                        <div class="col-md-3">
                            <strong>Age:</strong> {{ $currentFlock->age_in_days }} days
                        </div>
                        <div class="col-md-3">
                            <strong>Count:</strong> {{ number_format($currentFlock->current_count) }}
                        </div>
                        <div class="col-md-3">
                            <strong>Mortality:</strong> {{ $currentFlock->mortality_rate }}%
                        </div>
                        <div class="col-md-3">
                            <strong>FCR:</strong> {{ number_format($currentFlock->feed_conversion_ratio, 2) }}
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('flocks.show', $currentFlock->id) }}" class="btn btn-primary btn-sm">View Flock</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- House Details -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3>House Details</h3>
                        <div>
                            <a href="{{ route('houses.edit', $house->id) }}" class="btn btn-warning">
                                <i class="fa fa-edit"></i> Edit
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr><th width="35%">Name</th><td>{{ $house->name }}</td></tr>
                                <tr><th>House Code</th><td>{{ $house->house_code }}</td></tr>
                                <tr><th>Species</th><td>{{ $house->species->name ?? 'Not Assigned' }}</td></tr>
                                <tr><th>Capacity</th><td>{{ number_format($house->capacity) }} animals</td></tr>
                                <tr><th>Status</th><td>
                                    @if($house->status === 'active')
                                        <span class="badge badge-success">Active</span>
                                    @elseif($house->status === 'maintenance')
                                        <span class="badge badge-warning">Maintenance</span>
                                    @elseif($house->status === 'cleaning')
                                        <span class="badge badge-info">Cleaning</span>
                                    @else
                                        <span class="badge badge-secondary">Inactive</span>
                                    @endif
                                </td></tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr><th width="35%">Dimensions</th><td>{{ $house->length_m }}m × {{ $house->width_m }}m × {{ $house->height_m }}m</td></tr>
                                <tr><th>Area</th><td>{{ number_format($house->length_m * $house->width_m, 2) }} m²</td></tr>
                                <tr><th>Feeders</th><td>{{ $house->feeders_count }}</td></tr>
                                <tr><th>Drinkers</th><td>{{ $house->drinkers_count }}</td></tr>
                                <tr><th>Fans / Heaters</th><td>{{ $house->fans_count }} / {{ $house->heaters_count }}</td></tr>
                            </table>
                        </div>
                        @if($house->notes)
                        <div class="col-12 mt-3">
                            <strong>Notes:</strong>
                            <p>{{ $house->notes }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection