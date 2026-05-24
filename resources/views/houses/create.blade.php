{{-- resources/views/houses/create.blade.php --}}
@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6 col-12">
                <h2>Create New House</h2>
                <p class="mb-0 text-title-gray">Add a new house or facility to your farm</p>
            </div>
            <div class="col-sm-6 col-12">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="iconly-Home icli svg-color"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{ route('houses.index') }}">Houses</a></li>
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
                    <h3>New House Details</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('houses.store') }}">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">House Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">House Code <span class="text-danger">*</span></label>
                                <input type="text" name="house_code" class="form-control @error('house_code') is-invalid @enderror" value="{{ old('house_code') }}" placeholder="e.g., H01, BARN-A" required>
                                @error('house_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Species</label>
                                <select name="species_id" class="form-select @error('species_id') is-invalid @enderror">
                                    <option value="">Not Assigned</option>
                                    @foreach($species as $spec)
                                        <option value="{{ $spec->id }}" {{ old('species_id') == $spec->id ? 'selected' : '' }}>
                                            {{ $spec->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('species_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Capacity</label>
                                <input type="number" name="capacity" class="form-control @error('capacity') is-invalid @enderror" value="{{ old('capacity', 0) }}" min="0">
                                @error('capacity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Length (m)</label>
                                <input type="number" name="length_m" class="form-control @error('length_m') is-invalid @enderror" value="{{ old('length_m') }}" step="0.01" min="0">
                                @error('length_m') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Width (m)</label>
                                <input type="number" name="width_m" class="form-control @error('width_m') is-invalid @enderror" value="{{ old('width_m') }}" step="0.01" min="0">
                                @error('width_m') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Height (m)</label>
                                <input type="number" name="height_m" class="form-control @error('height_m') is-invalid @enderror" value="{{ old('height_m') }}" step="0.01" min="0">
                                @error('height_m') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Feeders Count</label>
                                <input type="number" name="feeders_count" class="form-control @error('feeders_count') is-invalid @enderror" value="{{ old('feeders_count', 0) }}" min="0">
                                @error('feeders_count') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Drinkers Count</label>
                                <input type="number" name="drinkers_count" class="form-control @error('drinkers_count') is-invalid @enderror" value="{{ old('drinkers_count', 0) }}" min="0">
                                @error('drinkers_count') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Fans Count</label>
                                <input type="number" name="fans_count" class="form-control @error('fans_count') is-invalid @enderror" value="{{ old('fans_count', 0) }}" min="0">
                                @error('fans_count') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Heaters Count</label>
                                <input type="number" name="heaters_count" class="form-control @error('heaters_count') is-invalid @enderror" value="{{ old('heaters_count', 0) }}" min="0">
                                @error('heaters_count') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                    <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="maintenance" {{ old('status') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                    <option value="cleaning" {{ old('status') == 'cleaning' ? 'selected' : '' }}>Cleaning</option>
                                    <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                                @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-12 mb-3">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3">{{ old('notes') }}</textarea>
                                @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">Create House</button>
                            <a href="{{ route('houses.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection