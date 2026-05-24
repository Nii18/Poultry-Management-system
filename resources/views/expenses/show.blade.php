{{-- resources/views/expenses/show.blade.php --}}
@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6 col-12">
                <h2>Expense Details</h2>
                <p class="mb-0 text-title-gray">{{ $expense->description }}</p>
            </div>
            <div class="col-sm-6 col-12">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="iconly-Home icli svg-color"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{ route('expenses.index') }}">Expenses</a></li>
                    <li class="breadcrumb-item active">Expense #{{ $expense->id }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3>Expense Information</h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr><th width="35%">Date</th><td>{{ $expense->expense_date->format('Y-m-d') }}</td></tr>
                        <tr><th>Category</th><td>{{ ucfirst($expense->category) }}</td></tr>
                        <tr><th>Description</th><td>{{ $expense->description }}</td></tr>
                        <tr><th>Amount</th><td><strong class="text-danger">${{ number_format($expense->amount, 2) }}</strong></td></tr>
                        <tr><th>Vendor</th><td>{{ $expense->vendor_name ?? 'N/A' }}</td></tr>
                        <tr><th>Payment Method</th><td>{{ $expense->payment_method ?? 'N/A' }}</td
                      </tr>
                      <tr><th>Receipt Number</th><td>{{ $expense->receipt_number ?? 'N/A' }}</td
                      </tr>
                      <tr><th>Recorded By</th><td>{{ $expense->creator->name ?? 'N/A' }}</td
                      </tr>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3>Associated Records</h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr><th width="35%">Associated Flock</th>
                            <td>
                                @if($expense->flock)
                                    <a href="{{ route('flocks.show', $expense->flock_id) }}" class="text-primary">
                                        {{ $expense->flock->flock_number }}
                                    </a>
                                @else
                                    None (General Expense)
                                @endif
                            </td
                        </tr>
                        <tr><th>Associated House</th>
                            <td>
                                @if($expense->house)
                                    <a href="{{ route('houses.show', $expense->house_id) }}" class="text-primary">
                                        {{ $expense->house->name }}
                                    </a>
                                @else
                                    None (General Expense)
                                @endif
                            </td
                        </tr>
                        <tr><th>Created At</th><td>{{ $expense->created_at->format('Y-m-d H:i:s') }}</td
                        </tr>
                        <tr><th>Last Updated</th><td>{{ $expense->updated_at->format('Y-m-d H:i:s') }}</td
                        </tr>
                    追赶
                    @if($expense->notes)
                        <tr>
                            <th>Notes</th>
                            <td>{{ $expense->notes }}</td>
                        </tr>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <a href="{{ route('expenses.edit', $expense->id) }}" class="btn btn-warning">
                        <i class="fa fa-edit"></i> Edit
                    </a>
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                        <i class="fa fa-trash"></i> Delete
                    </button>
                    <a href="{{ route('expenses.index') }}" class="btn btn-secondary">Back to List</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('expenses.destroy', $expense->id) }}">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h5 class="modal-title">Delete Expense</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this expense?</p>
                    <p class="text-danger">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection