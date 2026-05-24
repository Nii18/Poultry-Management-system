{{-- resources/views/breeding-records/show.blade.php --}}
@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6 col-12">
                <h2>Breeding Record Details</h2>
                <p class="mb-0 text-title-gray">
                    {{ $record->female->flock_number ?? 'N/A' }} × 
                    {{ $record->male->flock_number ?? 'External/AI' }}
                </p>
            </div>
            <div class="col-sm-6 col-12">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="iconly-Home icli svg-color"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{ route('breeding-records.index') }}">Breeding</a></li>
                    <li class="breadcrumb-item active">Record #{{ $record->id }}</li>
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
                    <h5>Conception Rate</h5>
                    <h3 class="text-success">{{ $conceptionRate }}%</h3>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5>Live Birth Rate</h5>
                    <h3 class="text-primary">{{ $liveBirthRate }}%</h3>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5>Weaning Rate</h5>
                    <h3 class="text-info">{{ $weaningRate }}%</h3>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5>Status</h5>
                    @if($record->is_successful)
                        <h3 class="text-success">Successful</h3>
                    @elseif($record->actual_delivery_date && !$record->is_successful)
                        <h3 class="text-danger">Failed</h3>
                    @else
                        <h3 class="text-warning">Pending</h3>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Breeding Details -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3>Breeding Information</h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th width="40%">Breeding Date</th>
                            <td>{{ $record->breeding_date->format('Y-m-d') }}</td>
                        </tr>
                        <tr>
                            <th>Expected Delivery</th>
                            <td>{{ $record->expected_delivery_date->format('Y-m-d') }}
                                @if($record->expected_delivery_date > now())
                                    <br><small>({{ now()->diffInDays($record->expected_delivery_date) }} days from now)</small>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Actual Delivery</th>
                            <td>{{ $record->actual_delivery_date ? $record->actual_delivery_date->format('Y-m-d') : 'Not yet recorded' }}</td>
                        </tr>
                        <tr>
                            <th>Breeding Method</th>
                            <td>{{ ucfirst(str_replace('_', ' ', $record->breeding_method)) }}</td>
                        </tr>
                        <tr>
                            <th>Recorded By</th>
                            <td>{{ $record->recorder->name ?? 'N/A' }}</td>
                        </tr>
                        @if($record->notes)
                        <tr>
                            <th>Notes</th>
                            <td>{{ $record->notes }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3>Offspring Information</h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th width="40%">Total Offspring</th>
                            <td>{{ $record->offspring_count ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Stillborn</th>
                            <td>{{ $record->stillborn_count ?? 0 }}</td>
                        </tr>
                        <tr>
                            <th>Live Births</th>
                            <td>{{ ($record->offspring_count ?? 0) - ($record->stillborn_count ?? 0) }}</td>
                        </tr>
                        <tr>
                            <th>Weaned Count</th>
                            <td>{{ $record->weaned_count ?? 'N/A' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Offspring Records -->
    @if($record->offspringRecords->count() > 0)
    <div class="row mt-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3>Offspring Flocks</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Flock Number</th>
                                    <th>Count</th>
                                    <th>Avg Birth Weight</th>
                                    <th>Ear Tag Range</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($record->offspringRecords as $offspring)
                                    <tr>
                                        <td>
                                            <a href="{{ route('flocks.show', $offspring->new_flock_id) }}" class="text-primary">
                                                {{ $offspring->newFlock->flock_number ?? 'N/A' }}
                                            </a>
                                        </td>
                                        <td>{{ $offspring->count }}</td>
                                        <td>{{ $offspring->average_birth_weight_kg ? $offspring->average_birth_weight_kg . ' kg' : 'N/A' }}</td>
                                        <td>
                                            @if($offspring->ear_tag_prefix)
                                                {{ $offspring->ear_tag_prefix }}{{ $offspring->ear_tag_start_number }}+
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('offspring-records.show', $offspring->id) }}" class="btn btn-sm btn-primary">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                        </td>
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

    <!-- Action Buttons -->
    @if(!$record->actual_delivery_date)
    <div class="row mt-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#recordDeliveryModal">
                        <i class="fa fa-baby"></i> Record Delivery
                    </button>
                    <a href="{{ route('offspring-records.create', ['breeding_record_id' => $record->id]) }}" class="btn btn-info">
                        <i class="fa fa-plus"></i> Add Offspring Flock
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Record Delivery Modal -->
<div class="modal fade" id="recordDeliveryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('breeding-records.record-delivery', $record->id) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Record Delivery</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Actual Delivery Date</label>
                        <input type="date" name="actual_delivery_date" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Offspring Count</label>
                        <input type="number" name="offspring_count" class="form-control" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Stillborn Count</label>
                        <input type="number" name="stillborn_count" class="form-control" min="0" value="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Weaned Count</label>
                        <input type="number" name="weaned_count" class="form-control" min="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-check">
                            <input type="checkbox" name="is_successful" value="1" class="form-check-input">
                            <label class="form-check-label">Breeding Successful</label>
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Delivery</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection