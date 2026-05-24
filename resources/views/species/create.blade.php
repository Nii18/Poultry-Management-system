{{-- resources/views/species/create.blade.php --}}
@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6 col-12">
                <h2>Create New Species</h2>
                <p class="mb-0 text-title-gray">Add a new animal species to your farm</p>
            </div>
            <div class="col-sm-6 col-12">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="iconly-Home icli svg-color"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{ route('species.index') }}">Species</a></li>
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
                    <h3>New Species Details</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('species.store') }}">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Code <span class="text-danger">*</span></label>
                                <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code') }}" maxlength="5" placeholder="e.g., CH, PG, CT" required>
                                @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Icon Class</label>
                                <input type="text" name="icon" class="form-control @error('icon') is-invalid @enderror" value="{{ old('icon', 'fas fa-drumstick') }}" placeholder="e.g., fas fa-drumstick">
                                @error('icon') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Color (Hex)</label>
                                <input type="color" name="color_hex" class="form-control @error('color_hex') is-invalid @enderror" value="{{ old('color_hex', '#3B82F6') }}">
                                @error('color_hex') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="2">{{ old('description') }}</textarea>
                                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Gestation Days</label>
                                <input type="number" name="gestation_days" class="form-control @error('gestation_days') is-invalid @enderror" value="{{ old('gestation_days') }}" min="0">
                                @error('gestation_days') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Weaning Days</label>
                                <input type="number" name="weaning_days" class="form-control @error('weaning_days') is-invalid @enderror" value="{{ old('weaning_days') }}" min="0">
                                @error('weaning_days') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Market Age (Days)</label>
                                <input type="number" name="market_age_days" class="form-control @error('market_age_days') is-invalid @enderror" value="{{ old('market_age_days') }}" min="0">
                                @error('market_age_days') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Market Weight (kg)</label>
                                <input type="number" name="market_weight_kg" class="form-control @error('market_weight_kg') is-invalid @enderror" value="{{ old('market_weight_kg') }}" step="0.01" min="0">
                                @error('market_weight_kg') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Lifespan (Years)</label>
                                <input type="number" name="lifespan_years" class="form-control @error('lifespan_years') is-invalid @enderror" value="{{ old('lifespan_years') }}" min="0">
                                @error('lifespan_years') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Sexual Maturity (Days)</label>
                                <input type="number" name="sexual_maturity_days" class="form-control @error('sexual_maturity_days') is-invalid @enderror" value="{{ old('sexual_maturity_days') }}" min="0">
                                @error('sexual_maturity_days') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">Create Species</button>
                            <a href="{{ route('species.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection