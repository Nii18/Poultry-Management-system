@extends('layouts.master')

@section('title', 'Switch User - Medicine Expiry System')

@section('content')
<!-- Start Content-->
<div class="container-fluid">
    
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Switch User Account</h4>
            </div>
        </div>
    </div>     
    <!-- end page title --> 

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <!-- Session Messages -->


                    @if(session('info'))
                        <div class="alert alert-info alert-dismissible bg-info text-white border-0 fade show" role="alert">
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
                            {{ session('info') }}
                        </div>
                    @endif

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible bg-success text-white border-0 fade show" role="alert">
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible bg-danger text-white border-0 fade show" role="alert">
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
                            {{ session('error') }}
                        </div>
                    @endif

                    @if($users->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-centered mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>User Details</th>
                                        <th>Email</th>
                                        <th>User ID</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($users as $user)
                                        <tr>
                                            <td><strong>{{ $user->name }}</strong></td>
                                            <td>{{ $user->email }}</td>
                                            <td>#{{ $user->id }}</td>
                                            <td>
                                                {{-- Make sure this form is correct --}}
                                                <form action="{{ route('user.switch.to', $user->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-primary btn-sm">
                                                        <i class="mdi mdi-account-switch me-1"></i>Switch to User
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                     @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info" role="alert">
                            <i class="mdi mdi-information-outline me-2"></i> No other users found in the system.
                        </div>
                    @endif

                    <!-- Switch Back Button (if currently switched) -->
                    @if(Session::has('impersonator_id'))
                        <div class="mt-4 pt-2 border-top">
                            <form action="{{ route('user.switch.back') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-warning">
                                    <i class="mdi mdi-account-arrow-left me-1"></i>Switch Back to Original User
                                </button>
                            </form>
                        </div>
                    @endif

                    <!-- Back to Dashboard -->
                    <div class="mt-3">
                        <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                            <i class="mdi mdi-arrow-left me-1"></i>Back to Dashboard
                        </a>
                    </div>
                </div> <!-- end card-body -->
            </div> <!-- end card -->
        </div> <!-- end col -->
    </div> <!-- end row -->
    
</div> <!-- container -->
@endsection