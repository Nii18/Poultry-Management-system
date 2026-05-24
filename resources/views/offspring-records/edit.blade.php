{{-- resources/views/offspring-records/edit.blade.php --}}
@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6 col-12">
                <h2>Edit Offspring Record</h2>
                <p class="mb-0 text-title-gray">Update offspring information</p>
            </div>
            <div class="col-sm-6 col-12">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="iconly-Home icli svg-color"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{ route('breeding-records.index') }}">Breeding</a></li>
                    <li class="breadcrumb-item active">Edit Offspring</li>
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
                    <h3>Edit Offspring Record</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('offspring-records.update', $offspring->id) }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Associated Flock</label>
                                <select name="new_flock_id" class="form-select @error('new_flock_id') is-invalid @enderror">
                                    <option value="">None - Not assigned</option>
                                    @foreach($activeFlocks as $flock)
                                        <option value="{{ $flock->id }}" {{ old('new_flock_id', $offspring->new_flock_id) == $flock->id ? 'selected' : '' }}>
                                            {{ $flock->flock_number }} ({{ $flock->breed_variety }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('new_flock_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Offspring Count <span class="text-danger">*</span></label>
                                <input type="number" name="count" class="form-control @error('count') is-invalid @enderror" value="{{ old('count', $offspring->count) }}" min="1" required>
                                @error('count') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Average Birth Weight (kg)</label>
                                <input type="number" name="average_birth_weight_kg" class="form-control @error('average_birth_weight_kg') is-invalid @enderror" value="{{ old('average_birth_weight_kg', $offspring->average_birth_weight_kg) }}" step="0.01" min="0">
                                @error('average_birth_weight_kg') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Ear Tag Prefix</label>
                                <input type="text" name="ear_tag_prefix" class="form-control @error('ear_tag_prefix') is-invalid @enderror" value="{{ old('ear_tag_prefix', $offspring->ear_tag_prefix) }}" maxlength="10">
                                @error('ear_tag_prefix') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Ear Tag Start Number</label>
                                <input type="number" name="ear_tag_start_number" class="form-control @error('ear_tag_start_number') is-invalid @enderror" value="{{ old('ear_tag_start_number', $offspring->ear_tag_start_number) }}" min="1">
                                @error('ear_tag_start_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-12 mb-3">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3">{{ old('notes', $offspring->notes) }}</textarea>
                                @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">Update Offspring Record</button>
                            <a href="{{ route('offspring-records.show', $offspring->id) }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection