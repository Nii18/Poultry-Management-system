{{-- resources/views/feed-deliveries/create.blade.php --}}
@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6 col-12">
                <h2>Record Feed Delivery</h2>
                <p class="mb-0 text-title-gray">Add new feed purchase to inventory</p>
            </div>
            <div class="col-sm-6 col-12">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="iconly-Home icli svg-color"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{ route('feed-deliveries.index') }}">Feed Deliveries</a></li>
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
                    <h3>New Feed Delivery</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('feed-deliveries.store') }}">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Feed Type <span class="text-danger">*</span></label>
                                <select name="feed_type_id" class="form-select @error('feed_type_id') is-invalid @enderror" required>
                                    <option value="">Select Feed Type</option>
                                    @foreach($feedTypes as $type)
                                        <option value="{{ $type->id }}" {{ old('feed_type_id') == $type->id ? 'selected' : '' }}>
                                            {{ $type->name }} ({{ ucfirst($type->category) }}) - {{ $type->species->name ?? 'N/A' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('feed_type_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Delivery Date <span class="text-danger">*</span></label>
                                <input type="date" name="delivery_date" class="form-control @error('delivery_date') is-invalid @enderror" value="{{ old('delivery_date', date('Y-m-d')) }}" required>
                                @error('delivery_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Supplier Name <span class="text-danger">*</span></label>
                                <input type="text" name="supplier_name" class="form-control @error('supplier_name') is-invalid @enderror" value="{{ old('supplier_name') }}" required>
                                @error('supplier_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Invoice Number</label>
                                <input type="text" name="invoice_number" class="form-control @error('invoice_number') is-invalid @enderror" value="{{ old('invoice_number') }}">
                                @error('invoice_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Quantity (kg) <span class="text-danger">*</span></label>
                                <input type="number" name="quantity_kg" class="form-control @error('quantity_kg') is-invalid @enderror" value="{{ old('quantity_kg') }}" step="0.01" min="0.01" required>
                                @error('quantity_kg') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Cost per kg ($) <span class="text-danger">*</span></label>
                                <input type="number" name="cost_per_kg" class="form-control @error('cost_per_kg') is-invalid @enderror" value="{{ old('cost_per_kg') }}" step="0.01" min="0" required>
                                @error('cost_per_kg') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Expiry Date</label>
                                <input type="date" name="expiry_date" class="form-control @error('expiry_date') is-invalid @enderror" value="{{ old('expiry_date') }}">
                                @error('expiry_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Batch Number</label>
                                <input type="text" name="batch_number" class="form-control @error('batch_number') is-invalid @enderror" value="{{ old('batch_number') }}">
                                @error('batch_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-12 mb-3">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3">{{ old('notes') }}</textarea>
                                @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">Record Delivery</button>
                            <a href="{{ route('feed-deliveries.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection