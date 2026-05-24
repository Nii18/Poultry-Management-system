{{-- resources/views/offspring-records/create.blade.php --}}
@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6 col-12">
                <h2>Add Offspring Record</h2>
                <p class="mb-0 text-title-gray">Record offspring from a breeding event</p>
            </div>
            <div class="col-sm-6 col-12">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="iconly-Home icli svg-color"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{ route('breeding-records.index') }}">Breeding</a></li>
                    <li class="breadcrumb-item active">Add Offspring</li>
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
                    <h3>New Offspring Record</h3>
                </div>
                <div class="card-body">
                    @if($breedingRecord)
                        <div class="alert alert-info">
                            <i class="fa fa-info-circle"></i> 
                            Recording offspring for breeding: 
                            <strong>{{ $breedingRecord->female->flock_number ?? 'N/A' }}</strong> 
                            (Bred on {{ $breedingRecord->breeding_date->format('Y-m-d') }})
                        </div>
                    @endif

                    <form method="POST" action="{{ route('offspring-records.store') }}">
                        @csrf
                        
                        @if($breedingRecord)
                            <input type="hidden" name="breeding_record_id" value="{{ $breedingRecord->id }}">
                        @else
                            <div class="mb-3">
                                <label class="form-label">Breeding Record <span class="text-danger">*</span></label>
                                <select name="breeding_record_id" class="form-select @error('breeding_record_id') is-invalid @enderror" required>
                                    <option value="">Select Breeding Record</option>
                                    @foreach(\App\Models\BreedingRecord::with('female')->whereNull('actual_delivery_date')->get() as $record)
                                        <option value="{{ $record->id }}" {{ old('breeding_record_id') == $record->id ? 'selected' : '' }}>
                                            {{ $record->female->flock_number ?? 'N/A' }} - Bred on {{ $record->breeding_date->format('Y-m-d') }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('breeding_record_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        @endif
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">New Flock (Optional)</label>
                                <select name="new_flock_id" class="form-select @error('new_flock_id') is-invalid @enderror">
                                    <option value="">Create new flock later</option>
                                    @foreach($activeFlocks as $flock)
                                        <option value="{{ $flock->id }}" {{ old('new_flock_id') == $flock->id ? 'selected' : '' }}>
                                            {{ $flock->flock_number }} ({{ $flock->breed_variety }})
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">You can create a new flock for these offspring later</small>
                                @error('new_flock_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Offspring Count <span class="text-danger">*</span></label>
                                <input type="number" name="count" class="form-control @error('count') is-invalid @enderror" value="{{ old('count') }}" min="1" required>
                                @error('count') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Average Birth Weight (kg)</label>
                                <input type="number" name="average_birth_weight_kg" class="form-control @error('average_birth_weight_kg') is-invalid @enderror" value="{{ old('average_birth_weight_kg') }}" step="0.01" min="0">
                                @error('average_birth_weight_kg') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Ear Tag Prefix</label>
                                <input type="text" name="ear_tag_prefix" class="form-control @error('ear_tag_prefix') is-invalid @enderror" value="{{ old('ear_tag_prefix') }}" maxlength="10" placeholder="e.g., FLOCK-">
                                <small class="text-muted">Optional prefix for ear tags</small>
                                @error('ear_tag_prefix') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Ear Tag Start Number</label>
                                <input type="number" name="ear_tag_start_number" class="form-control @error('ear_tag_start_number') is-invalid @enderror" value="{{ old('ear_tag_start_number') }}" min="1">
                                <small class="text-muted">Starting number for sequential ear tags</small>
                                @error('ear_tag_start_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-12 mb-3">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3">{{ old('notes') }}</textarea>
                                @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">Save Offspring Record</button>
                            <a href="{{ route('breeding-records.show', $breedingRecord->id ?? '') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection