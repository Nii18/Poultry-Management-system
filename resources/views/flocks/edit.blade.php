{{-- resources/views/flocks/edit.blade.php --}}
@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6 col-12">
                <h2>Edit Flock</h2>
                <p class="mb-0 text-title-gray">{{ $flock->flock_number }} - {{ $flock->breed_variety }}</p>
            </div>
            <div class="col-sm-6 col-12">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="iconly-Home icli svg-color"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{ route('flocks.index') }}">Flocks</a></li>
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
                    <h3>Edit Flock Details</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('flocks.update', $flock->id) }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Breed/Variety <span class="text-danger">*</span></label>
                                <input type="text" name="breed_variety" class="form-control @error('breed_variety') is-invalid @enderror" value="{{ old('breed_variety', $flock->breed_variety) }}" required>
                                @error('breed_variety') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">House <span class="text-danger">*</span></label>
                                <select name="house_id" class="form-select @error('house_id') is-invalid @enderror" required>
                                    <option value="">Select House</option>
                                    @foreach($houses as $house)
                                        <option value="{{ $house->id }}" {{ old('house_id', $flock->house_id) == $house->id ? 'selected' : '' }}>
                                            {{ $house->name }} (Capacity: {{ number_format($house->capacity) }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('house_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Production Type</label>
                                <select name="production_type" class="form-select @error('production_type') is-invalid @enderror">
                                    <option value="meat" {{ old('production_type', $flock->production_type) == 'meat' ? 'selected' : '' }}>Meat</option>
                                    <option value="eggs" {{ old('production_type', $flock->production_type) == 'eggs' ? 'selected' : '' }}>Eggs</option>
                                    <option value="milk" {{ old('production_type', $flock->production_type) == 'milk' ? 'selected' : '' }}>Milk</option>
                                    <option value="breeding" {{ old('production_type', $flock->production_type) == 'breeding' ? 'selected' : '' }}>Breeding</option>
                                    <option value="dual_purpose" {{ old('production_type', $flock->production_type) == 'dual_purpose' ? 'selected' : '' }}>Dual Purpose</option>
                                </select>
                                @error('production_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-12 mb-3">
                                <div class="form-check">
                                    <input type="checkbox" name="is_breeding_stock" value="1" class="form-check-input" id="is_breeding_stock" {{ old('is_breeding_stock', $flock->is_breeding_stock) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_breeding_stock">This flock is for breeding stock</label>
                                </div>
                            </div>
                            
                            <div class="col-12 mb-3">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3">{{ old('notes', $flock->notes) }}</textarea>
                                @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">Update Flock</button>
                            <a href="{{ route('flocks.show', $flock->id) }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection