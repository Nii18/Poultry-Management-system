{{-- resources/views/feed-types/show.blade.php --}}
@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6 col-12">
                <h2>Feed Type Details</h2>
                <p class="mb-0 text-title-gray">{{ $feedType->name }} ({{ $feedType->code }})</p>
            </div>
            <div class="col-sm-6 col-12">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="iconly-Home icli svg-color"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{ route('feed-types.index') }}">Feed Types</a></li>
                    <li class="breadcrumb-item active">{{ $feedType->name }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <!-- Stats Cards -->
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5>Total Deliveries</h5>
                    <h3>{{ $stats['total_deliveries'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5>Total Quantity</h5>
                    <h3>{{ number_format($stats['total_quantity']) }} kg</h3>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5>Current Stock</h5>
                    <h3>{{ number_format($stats['current_stock']) }} kg</h3>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5>Avg Cost/kg</h5>
                    <h3>${{ number_format($stats['avg_cost_per_kg'], 2) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Feed Type Details -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3>Feed Type Information</h3>
                        <div>
                            <a href="{{ route('feed-types.edit', $feedType->id) }}" class="btn btn-warning">
                                <i class="fa fa-edit"></i> Edit
                            </a>
                            @if($stats['total_deliveries'] == 0)
                                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                    <i class="fa fa-trash"></i> Delete
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr><th width="35%">Name</th><td>{{ $feedType->name }}</td
                        </tr>
                        <tr><th>Code</th><td>{{ $feedType->code }}</td
                        </tr>
                        <tr><th>Species</th><td>{{ $feedType->species->name ?? 'N/A' }}</td
                        </tr>
                        <tr><th>Category</th><td>{{ ucfirst($feedType->category) }}</td
                        </tr>
                        <tr><th>Protein %</th><td>{{ $feedType->protein_percentage ?? 'N/A' }}%</td
                        </tr>
                        <tr><th>Energy (MJ/kg)</th><td>{{ $feedType->energy_mj_kg ?? 'N/A' }}</td
                        </tr>
                        <tr><th>Status</th>
                            <td>
                                @if($feedType->is_active)
                                    <span class="badge badge-success">Active</span>
                                @else
                                    <span class="badge badge-secondary">Inactive</span>
                                @endif
                             </td
                        </tr>
                        @if($feedType->description)
                        <tr><th>Description</th><td>{{ $feedType->description }}</td
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3>Recent Deliveries</h3>
                </div>
                <div class="card-body">
                    @if($feedType->feedDeliveries->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Quantity</th>
                                        <th>Cost/kg</th>
                                        <th>Remaining</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($feedType->feedDeliveries->take(5) as $delivery)
                                        <tr>
                                            <td>{{ $delivery->delivery_date->format('Y-m-d') }}</td
                                            <td>{{ number_format($delivery->quantity_kg) }} kg</td
                                            <td>${{ number_format($delivery->cost_per_kg, 2) }}</td
                                            <td>{{ number_format($delivery->remaining_quantity_kg) }} kg</td
                                         </tr
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted text-center">No deliveries recorded for this feed type.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
@if($stats['total_deliveries'] == 0)
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('feed-types.destroy', $feedType->id) }}">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h5 class="modal-title">Delete Feed Type</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete <strong>{{ $feedType->name }}</strong>?</p>
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
@endif
@endsection