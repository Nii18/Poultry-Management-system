{{-- resources/views/flocks/create.blade.php --}}
@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6 col-12">
                <h2>Create New Flock</h2>
                <p class="mb-0 text-title-gray">Add a new flock or herd to your farm</p>
            </div>
            <div class="col-sm-6 col-12">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="iconly-Home icli svg-color"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{ route('flocks.index') }}">Flocks</a></li>
                    <li class="breadcrumb-item active">Create</li>
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
                    <h3>New Flock Details</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('flocks.store') }}">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Species <span class="text-danger">*</span></label>
                                <select name="species_id" class="form-select @error('species_id') is-invalid @enderror" required>
                                    <option value="">Select Species</option>
                                    @foreach($species as $spec)
                                        <option value="{{ $spec->id }}" {{ old('species_id') == $spec->id ? 'selected' : '' }}>
                                            {{ $spec->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('species_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">House <span class="text-danger">*</span></label>
                                <select name="house_id" class="form-select @error('house_id') is-invalid @enderror" required>
                                    <option value="">Select House</option>
                                    @foreach($houses as $house)
                                        <option value="{{ $house->id }}" {{ old('house_id') == $house->id ? 'selected' : '' }}>
                                            {{ $house->name }} (Capacity: {{ number_format($house->capacity) }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('house_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Breed/Variety <span class="text-danger">*</span></label>
                                <input type="text" name="breed_variety" class="form-control @error('breed_variety') is-invalid @enderror" value="{{ old('breed_variety') }}" required>
                                @error('breed_variety') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Start Date <span class="text-danger">*</span></label>
                                <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror" value="{{ old('start_date', date('Y-m-d')) }}" required>
                                @error('start_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Initial Count <span class="text-danger">*</span></label>
                                <input type="number" name="initial_count" class="form-control @error('initial_count') is-invalid @enderror" value="{{ old('initial_count') }}" min="1" required>
                                @error('initial_count') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Source</label>
                                <input type="text" name="source" class="form-control @error('source') is-invalid @enderror" value="{{ old('source') }}" placeholder="Hatchery, Breeder farm, etc.">
                                @error('source') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Production Type</label>
                                <select name="production_type" class="form-select @error('production_type') is-invalid @enderror">
                                    <option value="meat" {{ old('production_type', 'meat') == 'meat' ? 'selected' : '' }}>Meat</option>
                                    <option value="eggs" {{ old('production_type') == 'eggs' ? 'selected' : '' }}>Eggs</option>
                                    <option value="milk" {{ old('production_type') == 'milk' ? 'selected' : '' }}>Milk</option>
                                    <option value="breeding" {{ old('production_type') == 'breeding' ? 'selected' : '' }}>Breeding</option>
                                    <option value="dual_purpose" {{ old('production_type') == 'dual_purpose' ? 'selected' : '' }}>Dual Purpose</option>
                                </select>
                                @error('production_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Parity Number</label>
                                <input type="number" name="parity_number" class="form-control @error('parity_number') is-invalid @enderror" value="{{ old('parity_number') }}" min="0" placeholder="Number of previous births">
                                @error('parity_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-12 mb-3">
                                <div class="form-check">
                                    <input type="checkbox" name="is_breeding_stock" value="1" class="form-check-input" id="is_breeding_stock" {{ old('is_breeding_stock') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_breeding_stock">This flock is for breeding stock</label>
                                </div>
                            </div>
                            
                            <div class="col-12 mb-3">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3">{{ old('notes') }}</textarea>
                                @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">Create Flock</button>
                            <a href="{{ route('flocks.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection