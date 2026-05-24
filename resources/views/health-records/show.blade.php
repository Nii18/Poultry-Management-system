{{-- resources/views/health-records/show.blade.php --}}
@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6 col-12">
                <h2>Health Record Details</h2>
                <p class="mb-0 text-title-gray">
                    {{ ucfirst($record->record_type) }} - {{ $record->flock->flock_number ?? 'N/A' }}
                </p>
            </div>
            <div class="col-sm-6 col-12">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="iconly-Home icli svg-color"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{ route('health-records.index') }}">Health</a></li>
                    <li class="breadcrumb-item active">Record #{{ $record->id }}</li>
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
                    <h3>Record Information</h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th width="35%">Record Date</th>
                            <td>{{ $record->record_date->format('Y-m-d') }}</td>
                        </tr>
                        <tr>
                            <th>Flock</th>
                            <td>
                                <a href="{{ route('flocks.show', $record->flock_id) }}" class="text-primary">
                                    {{ $record->flock->flock_number ?? 'N/A' }}
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <th>Record Type</th>
                            <td>{{ ucfirst($record->record_type) }}</td>
                        </tr>
                        <tr>
                            <th>Severity</th>
                            <td>
                                @if($record->severity === 'critical')
                                    <span class="badge badge-danger">Critical</span>
                                @elseif($record->severity === 'warning')
                                    <span class="badge badge-warning">Warning</span>
                                @else
                                    <span class="badge badge-info">Info</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Condition</th>
                            <td>{{ $record->condition ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Affected Animals</th>
                            <td>
                                @if($record->affected_count)
                                    {{ number_format($record->affected_count) }} 
                                    ({{ $affectedPercentage }}% of flock)
                                @else
                                    N/A
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Recorded By</th>
                            <td>{{ $record->recorder->name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Created At</th>
                            <td>{{ $record->created_at->format('Y-m-d H:i:s') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3>Clinical Details</h3>
                </div>
                <div class="card-body">
                    @if($record->symptoms)
                        <h5>Symptoms</h5>
                        <table class="table table-bordered mb-3">
                            @foreach($record->symptoms as $key => $value)
                                <tr>
                                    <th width="40%">{{ ucwords(str_replace('_', ' ', $key)) }}</th>
                                    <td>{{ is_bool($value) ? ($value ? 'Yes' : 'No') : $value }}</td>
                                </tr>
                            @endforeach
                        </table>
                    @endif
                    
                    @if($record->lab_results)
                        <h5>Lab Results</h5>
                        <table class="table table-bordered">
                            @foreach($record->lab_results as $key => $value)
                                <tr>
                                    <th width="40%">{{ ucwords(str_replace('_', ' ', $key)) }}</th>
                                    <td>{{ $value }}</td>
                                </tr>
                            @endforeach
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($record->veterinarian_notes)
    <div class="row mt-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3>Veterinarian Notes</h3>
                </div>
                <div class="card-body">
                    <p>{{ $record->veterinarian_notes }}</p>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($record->notes)
    <div class="row mt-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3>Additional Notes</h3>
                </div>
                <div class="card-body">
                    <p>{{ $record->notes }}</p>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Action Buttons -->
    <div class="row mt-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <a href="{{ route('health-records.edit', $record->id) }}" class="btn btn-warning">
                        <i class="fa fa-edit"></i> Edit
                    </a>
                    @if($record->severity === 'critical')
                        <a href="{{ route('treatments.create', ['flock_id' => $record->flock_id]) }}" class="btn btn-danger">
                            <i class="fa fa-stethoscope"></i> Create Treatment
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection