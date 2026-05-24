{{-- resources/views/expenses/edit.blade.php --}}
@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6 col-12">
                <h2>Edit Expense</h2>
                <p class="mb-0 text-title-gray">{{ $expense->description }}</p>
            </div>
            <div class="col-sm-6 col-12">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="iconly-Home icli svg-color"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{ route('expenses.index') }}">Expenses</a></li>
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
                    <h3>Edit Expense</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('expenses.update', $expense->id) }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Category <span class="text-danger">*</span></label>
                                <select name="category" class="form-select @error('category') is-invalid @enderror" required>
                                    <option value="feed" {{ old('category', $expense->category) == 'feed' ? 'selected' : '' }}>Feed</option>
                                    <option value="veterinary" {{ old('category', $expense->category) == 'veterinary' ? 'selected' : '' }}>Veterinary</option>
                                    <option value="medication" {{ old('category', $expense->category) == 'medication' ? 'selected' : '' }}>Medication</option>
                                    <option value="labor" {{ old('category', $expense->category) == 'labor' ? 'selected' : '' }}>Labor</option>
                                    <option value="equipment" {{ old('category', $expense->category) == 'equipment' ? 'selected' : '' }}>Equipment</option>
                                    <option value="utilities" {{ old('category', $expense->category) == 'utilities' ? 'selected' : '' }}>Utilities</option>
                                    <option value="maintenance" {{ old('category', $expense->category) == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                    <option value="transport" {{ old('category', $expense->category) == 'transport' ? 'selected' : '' }}>Transport</option>
                                    <option value="other" {{ old('category', $expense->category) == 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('category') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Expense Date <span class="text-danger">*</span></label>
                                <input type="date" name="expense_date" class="form-control @error('expense_date') is-invalid @enderror" value="{{ old('expense_date', $expense->expense_date->format('Y-m-d')) }}" required>
                                @error('expense_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Description <span class="text-danger">*</span></label>
                                <input type="text" name="description" class="form-control @error('description') is-invalid @enderror" value="{{ old('description', $expense->description) }}" required>
                                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Amount <span class="text-danger">*</span></label>
                                <input type="number" name="amount" class="form-control @error('amount') is-invalid @enderror" value="{{ old('amount', $expense->amount) }}" step="0.01" min="0.01" required>
                                @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Vendor Name</label>
                                <input type="text" name="vendor_name" class="form-control" value="{{ old('vendor_name', $expense->vendor_name) }}">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Payment Method</label>
                                <select name="payment_method" class="form-select">
                                    <option value="">Select Payment Method</option>
                                    <option value="cash" {{ old('payment_method', $expense->payment_method) == 'cash' ? 'selected' : '' }}>Cash</option>
                                    <option value="bank_transfer" {{ old('payment_method', $expense->payment_method) == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                    <option value="credit_card" {{ old('payment_method', $expense->payment_method) == 'credit_card' ? 'selected' : '' }}>Credit Card</option>
                                    <option value="check" {{ old('payment_method', $expense->payment_method) == 'check' ? 'selected' : '' }}>Check</option>
                                    <option value="mobile_money" {{ old('payment_method', $expense->payment_method) == 'mobile_money' ? 'selected' : '' }}>Mobile Money</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Receipt Number</label>
                                <input type="text" name="receipt_number" class="form-control" value="{{ old('receipt_number', $expense->receipt_number) }}">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Associated Flock</label>
                                <select name="flock_id" class="form-select">
                                    <option value="">None - General Expense</option>
                                    @foreach($flocks as $flock)
                                        <option value="{{ $flock->id }}" {{ old('flock_id', $expense->flock_id) == $flock->id ? 'selected' : '' }}>
                                            {{ $flock->flock_number }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Associated House</label>
                                <select name="house_id" class="form-select">
                                    <option value="">None - General Expense</option>
                                    @foreach($houses as $house)
                                        <option value="{{ $house->id }}" {{ old('house_id', $expense->house_id) == $house->id ? 'selected' : '' }}>
                                            {{ $house->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="col-12 mb-3">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" class="form-control" rows="3">{{ old('notes', $expense->notes) }}</textarea>
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">Update Expense</button>
                            <a href="{{ route('expenses.show', $expense->id) }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection