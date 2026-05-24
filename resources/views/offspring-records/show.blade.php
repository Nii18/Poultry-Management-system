{{-- resources/views/offspring-records/show.blade.php --}}
@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6 col-12">
                <h2>Offspring Record Details</h2>
                <p class="mb-0 text-title-gray">Offspring from breeding event</p>
            </div>
            <div class="col-sm-6 col-12">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="iconly-Home icli svg-color"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{ route('breeding-records.index') }}">Breeding</a></li>
                    <li class="breadcrumb-item active">Offspring #{{ $offspring->id }}</li>
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
                    <h3>Offspring Information</h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr><th width="35%">Breeding Record</th>
                            <td>
                                <a href="{{ route('breeding-records.show', $offspring->breeding_record_id) }}" class="text-primary">
                                    View Breeding Record
                                </a>
                             </td
                         </tr
                        <tr><th>Female (Dam)</th><td>{{ $offspring->breedingRecord->female->flock_number ?? 'N/A' }}</td</tr>
                        <tr><th>Offspring Count</th><td>{{ $offspring->count }}</td</tr>
                        <tr><th>Average Birth Weight</th><td>{{ $offspring->average_birth_weight_kg ? number_format($offspring->average_birth_weight_kg, 2) . ' kg' : 'N/A' }}</td</tr>
                        <tr><th>Ear Tag Prefix</th><td>{{ $offspring->ear_tag_prefix ?? 'N/A' }}</td</tr>
                        <tr><th>Ear Tag Range</th>
                            <td>
                                @if($offspring->ear_tag_prefix && $offspring->ear_tag_start_number)
                                    {{ $offspring->ear_tag_prefix }}{{ $offspring->ear_tag_start_number }} - 
                                    {{ $offspring->ear_tag_prefix }}{{ $offspring->ear_tag_start_number + $offspring->count - 1 }}
                                @else
                                    N/A
                                @endif
                             </td
                         </tr
                        <tr><th>Associated Flock</th>
                            <td>
                                @if($offspring->new_flock_id)
                                    <a href="{{ route('flocks.show', $offspring->new_flock_id) }}" class="text-primary">
                                        {{ $offspring->newFlock->flock_number ?? 'N/A' }}
                                    </a>
                                @else
                                    <span class="text-muted">Not yet assigned</span>
                                @endif
                             </td
                         </tr
                        @if($offspring->notes)
                        <tr><th>Notes</th><td>{{ $offspring->notes }}</td</tr>
                        @endif
                     </table
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3>Actions</h3>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('offspring-records.edit', $offspring->id) }}" class="btn btn-warning">
                            <i class="fa fa-edit"></i> Edit Record
                        </a>
                        @if(!$offspring->new_flock_id)
                            <a href="{{ route('flocks.create', ['offspring_id' => $offspring->id]) }}" class="btn btn-success">
                                <i class="fa fa-plus"></i> Create New Flock from Offspring
                            </a>
                        @endif
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                            <i class="fa fa-trash"></i> Delete Record
                        </button>
                        <a href="{{ route('breeding-records.show', $offspring->breeding_record_id) }}" class="btn btn-secondary">
                            <i class="fa fa-arrow-left"></i> Back to Breeding Record
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('offspring-records.destroy', $offspring->id) }}">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h5 class="modal-title">Delete Offspring Record</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this offspring record?</p>
                    <p class="text-danger">This action cannot be undone.</p>
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