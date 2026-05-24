{{-- resources/views/notifications/show.blade.php --}}
@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6 col-12">
                <h2>Notification Details</h2>
                <p class="mb-0 text-title-gray">{{ $notification->title }}</p>
            </div>
            <div class="col-sm-6 col-12">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="iconly-Home icli svg-color"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{ route('notifications.index') }}">Notifications</a></li>
                    <li class="breadcrumb-item active">Details</li>
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
                    <h3>{{ $notification->title }}</h3>
                </div>
                <div class="card-body">
                    <div class="alert 
                        @if($notification->severity === 'critical') alert-danger
                        @elseif($notification->severity === 'warning') alert-warning
                        @else alert-info
                        @endif
                    ">
                        <h5>{{ ucfirst($notification->severity) }} Alert</h5>
                        <p>{{ $notification->message }}</p>
                    </div>

                    <table class="table table-bordered">
                        <tr><th width="25%">Type</th><td>{{ ucfirst($notification->type) }}</td
                        </tr>
                        <tr><th>Flock</th>
                            <td>
                                @if($notification->flock)
                                    <a href="{{ route('flocks.show', $notification->flock_id) }}" class="text-primary">
                                        {{ $notification->flock->flock_number }}
                                    </a>
                                @else
                                    N/A
                                @endif
                             </td
                         </tr
                        <tr><th>Created At</th><td>{{ $notification->created_at->format('Y-m-d H:i:s') }}</td
                        </tr>
                        <tr><th>Read At</th><td>{{ $notification->read_at ? $notification->read_at->format('Y-m-d H:i:s') : 'Not read yet' }}</td
                        </tr>
                        @if($notification->data)
                        <tr><th>Additional Data</th>
                            <td>
                                <pre class="bg-light p-2 rounded">{{ json_encode($notification->data, JSON_PRETTY_PRINT) }}</pre>
                             </td
                         </tr
                        @endif
                     </table
                </div>
                <div class="card-footer">
                    <a href="{{ route('notifications.index') }}" class="btn btn-secondary">Back to List</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection