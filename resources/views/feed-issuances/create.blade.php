{{-- resources/views/feed-issuances/create.blade.php --}}
@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6 col-12">
                <h2>Record Feed Issuance</h2>
                <p class="mb-0 text-title-gray">Record feed given to a flock</p>
            </div>
            <div class="col-sm-6 col-12">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="iconly-Home icli svg-color"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{ route('feed-issuances.index') }}">Feed Issuances</a></li>
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
                    <h3>New Feed Issuance</h3>
                </div>
                <div class="card-body">
                    @if($flock)
                        <div class="alert alert-info">
                            <i class="fa fa-info-circle"></i> Recording feed issuance for: <strong>{{ $flock->flock_number }}</strong>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('feed-issuances.store') }}">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Flock <span class="text-danger">*</span></label>
                                <select name="flock_id" class="form-select @error('flock_id') is-invalid @enderror" required>
                                    <option value="">Select Flock</option>
                                    @foreach($flocks as $flockOption)
                                        <option value="{{ $flockOption->id }}" {{ old('flock_id', $flock->id ?? '') == $flockOption->id ? 'selected' : '' }}>
                                            {{ $flockOption->flock_number }} ({{ $flockOption->breed_variety }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('flock_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Feed Stock <span class="text-danger">*</span></label>
                                <select name="feed_delivery_id" class="form-select @error('feed_delivery_id') is-invalid @enderror" required>
                                    <option value="">Select Feed Batch</option>
                                    @foreach($feedDeliveries as $delivery)
                                        <option value="{{ $delivery->id }}" {{ old('feed_delivery_id') == $delivery->id ? 'selected' : '' }}>
                                            {{ $delivery->feedType->name }} - {{ number_format($delivery->remaining_quantity_kg) }} kg remaining (Exp: {{ $delivery->expiry_date ? $delivery->expiry_date->format('Y-m-d') : 'N/A' }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('feed_delivery_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Quantity (kg) <span class="text-danger">*</span></label>
                                <input type="number" name="quantity_kg" class="form-control @error('quantity_kg') is-invalid @enderror" value="{{ old('quantity_kg') }}" step="0.01" min="0.01" required>
                                @error('quantity_kg') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Issuance Date <span class="text-danger">*</span></label>
                                <input type="date" name="issuance_date" class="form-control @error('issuance_date') is-invalid @enderror" value="{{ old('issuance_date', date('Y-m-d')) }}" required>
                                @error('issuance_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Issuance Time</label>
                                <input type="time" name="issuance_time" class="form-control @error('issuance_time') is-invalid @enderror" value="{{ old('issuance_time', date('H:i')) }}">
                                @error('issuance_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-12 mb-3">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3">{{ old('notes') }}</textarea>
                                @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">Record Issuance</button>
                            <a href="{{ route('feed-issuances.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection