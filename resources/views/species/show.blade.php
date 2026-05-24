{{-- resources/views/species/show.blade.php --}}
@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6 col-12">
                <h2>{{ $species->name }}</h2>
                <p class="mb-0 text-title-gray">Code: {{ $species->code }}</p>
            </div>
            <div class="col-sm-6 col-12">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="iconly-Home icli svg-color"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{ route('species.index') }}">Species</a></li>
                    <li class="breadcrumb-item active">{{ $species->name }}</li>
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
                    <i class="{{ $species->icon }} fs-1" style="color: {{ $species->color_hex }}"></i>
                    <h5 class="mt-2">Total Flocks</h5>
                    <h3>{{ $stats['flock_count'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5>Active Flocks</h5>
                    <h3>{{ $stats['active_flocks'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5>Total Animals</h5>
                    <h3>{{ number_format($stats['total_animals']) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5>Avg FCR</h5>
                    <h3>{{ number_format($stats['avg_fcr'], 2) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Species Details -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3>Species Details</h3>
                        <div>
                            <a href="{{ route('species.edit', $species->id) }}" class="btn btn-warning">
                                <i class="fa fa-edit"></i> Edit
                            </a>
                            @if($stats['flock_count'] == 0)
                                <form method="POST" action="{{ route('species.destroy', $species->id) }}" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this species?')">
                                        <i class="fa fa-trash"></i> Delete
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th width="35%">Name</th>
                                    <td>{{ $species->name }}</td>
                                </tr>
                                <tr>
                                    <th>Code</th>
                                    <td>{{ $species->code }}</td>
                                </tr>
                                <tr>
                                    <th>Icon</th>
                                    <td>
                                        <i class="{{ $species->icon }}"></i> {{ $species->icon }}
                                    </td>
                                </tr>
                                <tr>
                                    <th>Color</th>
                                    <td>
                                        <span style="background: {{ $species->color_hex }}; padding: 5px 15px; border-radius: 5px; color: #fff;">
                                            {{ $species->color_hex }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>
                                        @if($species->is_active)
                                            <span class="badge badge-success">Active</span>
                                        @else
                                            <span class="badge badge-secondary">Inactive</span>
                                        @endif
                                        <button type="button" class="btn btn-sm btn-info ms-2" data-bs-toggle="modal" data-bs-target="#toggleStatusModal">
                                            <i class="fa fa-exchange-alt"></i> Toggle
                                        </button>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th width="35%">Gestation Days</th>
                                    <td>{{ $species->gestation_days ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Weaning Days</th>
                                    <td>{{ $species->weaning_days ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Market Age</th>
                                    <td>{{ $species->market_age_days ? $species->market_age_days . ' days' : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Market Weight</th>
                                    <td>{{ $species->market_weight_kg ? $species->market_weight_kg . ' kg' : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Lifespan</th>
                                    <td>{{ $species->lifespan_years ? $species->lifespan_years . ' years' : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Sexual Maturity</th>
                                    <td>{{ $species->sexual_maturity_days ? $species->sexual_maturity_days . ' days' : 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                        @if($species->description)
                        <div class="col-12 mt-3">
                            <table class="table table-bordered">
                                <tr>
                                    <th width="15%">Description</th>
                                    <td>{{ $species->description }}</td>
                                </tr>
                            </table>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Default Metrics Section -->
    @if($species->default_metrics)
    <div class="row mt-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3>Default Performance Metrics</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($species->default_metrics as $key => $value)
                            <div class="col-md-3 mb-2">
                                <strong>{{ ucwords(str_replace('_', ' ', $key)) }}:</strong>
                                <span>{{ $value }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Growth Standards Section -->
    @if($species->growth_standards)
    <div class="row mt-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3>Growth Standards</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Period</th>
                                    <th>Target Weight (kg)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($species->growth_standards as $period => $weight)
                                    <tr>
                                        <td>{{ ucwords(str_replace('_', ' ', $period)) }}</td>
                                        <td>{{ $weight }} kg</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Toggle Status Modal -->
<div class="modal fade" id="toggleStatusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('species.toggle-status', $species->id) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Toggle Species Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to {{ $species->is_active ? 'deactivate' : 'activate' }} this species?</p>
                    @if($species->is_active)
                        <p class="text-warning">Deactivating will hide this species from selection in new flocks.</p>
                    @else
                        <p class="text-success">Activating will make this species available for selection.</p>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-{{ $species->is_active ? 'warning' : 'success' }}">
                        {{ $species->is_active ? 'Deactivate' : 'Activate' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection