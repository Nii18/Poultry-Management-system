{{-- resources/views/flocks/show.blade.php --}}
@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6 col-12">
                <h2>Flock: {{ $flock->flock_number }}</h2>
                <p class="mb-0 text-title-gray">{{ $flock->breed_variety }} - {{ $flock->species->name }}</p>
            </div>
            <div class="col-sm-6 col-12">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="iconly-Home icli svg-color"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{ route('flocks.index') }}">Flocks</a></li>
                    <li class="breadcrumb-item active">{{ $flock->flock_number }}</li>
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
                    <h5>Age</h5>
                    <h3>{{ $summary['age_days'] }} days</h3>
                    <small>({{ $summary['age_weeks'] }} weeks)</small>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5>Current Count</h5>
                    <h3>{{ number_format($summary['current_count']) }}</h3>
                    <small>Started: {{ number_format($flock->initial_count) }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5>Mortality Rate</h5>
                    <h3 class="{{ $summary['mortality_rate'] > 5 ? 'text-danger' : 'text-success' }}">{{ $summary['mortality_rate'] }}%</h3>
                    <small>Survival: {{ $summary['survival_rate'] }}%</small>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5>Feed Conversion Ratio</h5>
                    <h3>{{ number_format($summary['fcr'], 2) }}</h3>
                    <small>Total Feed: {{ number_format($summary['total_feed']) }} kg</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex gap-2">
                        <a href="{{ route('daily-logs.create', ['flock_id' => $flock->id]) }}" class="btn btn-primary">
                            <i class="fa fa-clipboard-list"></i> Add Daily Log
                        </a>
                        <a href="{{ route('treatments.create', ['flock_id' => $flock->id]) }}" class="btn btn-info">
                            <i class="fa fa-stethoscope"></i> Add Treatment
                        </a>
                        @if($flock->status === 'active')
                            <a href="{{ route('flocks.edit', $flock->id) }}" class="btn btn-warning">
                                <i class="fa fa-edit"></i> Edit
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#details">Details</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#daily-logs">Daily Logs</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#treatments">Treatments</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#vaccinations">Vaccinations</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#feed">Feed Issuances</a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <!-- Details Tab -->
                        <div class="tab-pane fade show active" id="details">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-bordered">
                                        <tr><th width="40%">Flock Number</th><td>{{ $flock->flock_number }}</td></tr>
                                        <tr><th>Species</th><td>{{ $flock->species->name }} ({{ $flock->species->code }})</td></tr>
                                        <tr><th>House</th><td>{{ $flock->house->name }} ({{ $flock->house->house_code }})</td></tr>
                                        <tr><th>Breed/Variety</th><td>{{ $flock->breed_variety }}</td></tr>
                                        <tr><th>Start Date</th><td>{{ $flock->start_date->format('Y-m-d') }}</td></tr>
                                        <tr><th>Source</th><td>{{ $flock->source ?? 'N/A' }}</td></tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-bordered">
                                        <tr><th width="40%">Production Type</th><td>{{ ucfirst($flock->production_type) }}</td></tr>
                                        <tr><th>Breeding Stock</th><td>{{ $flock->is_breeding_stock ? 'Yes' : 'No' }}</td></tr>
                                        <tr><th>Parity Number</th><td>{{ $flock->parity_number ?? 'N/A' }}</td></tr>
                                        <tr><th>Status</th><td><span class="badge badge-success">{{ ucfirst($flock->status) }}</span></td></tr>
                                        <tr><th>Notes</th><td>{{ $flock->notes ?? 'N/A' }}</td></tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Daily Logs Tab -->
                        <div class="tab-pane fade" id="daily-logs">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Mortality</th>
                                            <th>Culling</th>
                                            <th>Feed (kg)</th>
                                            <th>Avg Weight (kg)</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($flock->dailyLogs as $log)
                                            <tr>
                                                <td>{{ $log->log_date->format('Y-m-d') }}</td>
                                                <td>{{ $log->mortality_count }}</td>
                                                <td>{{ $log->culling_count }}</td>
                                                <td>{{ number_format($log->feed_intake_kg) }}</td>
                                                <td>{{ $log->average_weight_kg ? number_format($log->average_weight_kg, 2) : 'N/A' }}</td>
                                                <td>
                                                    <a href="{{ route('daily-logs.show', $log->id) }}" class="btn btn-sm btn-primary">View</a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center">No daily logs recorded yet.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Treatments Tab -->
                        <div class="tab-pane fade" id="treatments">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Diagnosis</th>
                                            <th>Product</th>
                                            <th>Withdrawal End</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($flock->treatments as $treatment)
                                            <tr>
                                                <td>{{ $treatment->start_date->format('Y-m-d') }}</td>
                                                <td>{{ $treatment->diagnosis }}</td>
                                                <td>{{ $treatment->product_name }}</td>
                                                <td>{{ $treatment->withdrawal_end_date ? $treatment->withdrawal_end_date->format('Y-m-d') : 'N/A' }}</td>
                                                <td>
                                                    <a href="{{ route('treatments.show', $treatment->id) }}" class="btn btn-sm btn-primary">View</a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center">No treatments recorded yet.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Vaccinations Tab -->
                        <div class="tab-pane fade" id="vaccinations">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Vaccine</th>
                                            <th>Disease</th>
                                            <th>Route</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($flock->vaccinations as $vaccination)
                                            <tr>
                                                <td>{{ $vaccination->administration_date->format('Y-m-d') }}</td>
                                                <td>{{ $vaccination->vaccine_name }}</td>
                                                <td>{{ $vaccination->disease_target }}</td>
                                                <td>{{ ucfirst($vaccination->route) }}</td>
                                                <td>
                                                    <a href="{{ route('vaccinations.show', $vaccination->id) }}" class="btn btn-sm btn-primary">View</a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center">No vaccinations recorded yet.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Feed Issuances Tab -->
                        <div class="tab-pane fade" id="feed">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Feed Type</th>
                                            <th>Quantity (kg)</th>
                                            <th>Batch</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($flock->feedIssuances as $issuance)
                                            <tr>
                                                <td>{{ $issuance->issuance_date->format('Y-m-d') }}</td>
                                                <td>{{ $issuance->feedDelivery->feedType->name ?? 'N/A' }}</td>
                                                <td>{{ number_format($issuance->quantity_kg, 2) }}</td>
                                                <td>{{ $issuance->feedDelivery->batch_number ?? 'N/A' }}</td>
                                                <td>
                                                    <a href="{{ route('feed-issuances.show', $issuance->id) }}" class="btn btn-sm btn-primary">View</a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center">No feed issuances recorded yet.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection