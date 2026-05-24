{{-- resources/views/feed-types/create.blade.php --}}
@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6 col-12">
                <h2>Create Feed Type</h2>
                <p class="mb-0 text-title-gray">Add new feed formulation to the system</p>
            </div>
            <div class="col-sm-6 col-12">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="iconly-Home icli svg-color"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{ route('feed-types.index') }}">Feed Types</a></li>
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
                    <h3>New Feed Type</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('feed-types.store') }}">
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
                                <label class="form-label">Feed Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Feed Code <span class="text-danger">*</span></label>
                                <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code') }}" required>
                                <small class="text-muted">Unique identifier (e.g., CH-STARTER-01)</small>
                                @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Category <span class="text-danger">*</span></label>
                                <select name="category" class="form-select @error('category') is-invalid @enderror" required>
                                    <option value="starter" {{ old('category') == 'starter' ? 'selected' : '' }}>Starter</option>
                                    <option value="grower" {{ old('category') == 'grower' ? 'selected' : '' }}>Grower</option>
                                    <option value="finisher" {{ old('category') == 'finisher' ? 'selected' : '' }}>Finisher</option>
                                    <option value="layer" {{ old('category') == 'layer' ? 'selected' : '' }}>Layer</option>
                                    <option value="breeder" {{ old('category') == 'breeder' ? 'selected' : '' }}>Breeder</option>
                                    <option value="maintenance" {{ old('category') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                </select>
                                @error('category') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Protein Percentage (%)</label>
                                <input type="number" name="protein_percentage" class="form-control @error('protein_percentage') is-invalid @enderror" value="{{ old('protein_percentage') }}" step="0.1" min="0" max="100">
                                @error('protein_percentage') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Energy (MJ/kg)</label>
                                <input type="number" name="energy_mj_kg" class="form-control @error('energy_mj_kg') is-invalid @enderror" value="{{ old('energy_mj_kg') }}" step="0.01" min="0">
                                @error('energy_mj_kg') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-12 mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description') }}</textarea>
                                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-12 mb-3">
                                <div class="form-check">
                                    <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active" {{ old('is_active', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">Active (available for use)</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">Create Feed Type</button>
                            <a href="{{ route('feed-types.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection