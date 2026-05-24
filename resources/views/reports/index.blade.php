{{-- resources/views/reports/index.blade.php --}}
@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6 col-12">
                <h2>Reports</h2>
                <p class="mb-0 text-title-gray">Generate farm performance and financial reports</p>
            </div>
            <div class="col-sm-6 col-12">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="iconly-Home icli svg-color"></i></a></li>
                    <li class="breadcrumb-item">Reports</li>
                    <li class="breadcrumb-item active">Dashboard</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <!-- Performance Report Card -->
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="fa fa-chart-line fa-3x text-primary mb-3"></i>
                    <h3>Performance Report</h3>
                    <p>Generate flock performance reports including FCR, mortality rates, weight gain, and more.</p>
                    <a href="{{ route('reports.performance') }}" class="btn btn-primary">Generate Report</a>
                </div>
            </div>
        </div>
        
        <!-- Financial Report Card -->
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="fa fa-chart-pie fa-3x text-success mb-3"></i>
                    <h3>Financial Report</h3>
                    <p>View expenses by category, revenue breakdown, and profit analysis.</p>
                    <a href="{{ route('reports.financial') }}" class="btn btn-success">Generate Report</a>
                </div>
            </div>
        </div>
        
        <!-- Health Report Card -->
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="fa fa-stethoscope fa-3x text-danger mb-3"></i>
                    <h3>Health Report</h3>
                    <p>Analyze mortality trends, disease outbreaks, and vaccination coverage.</p>
                    <a href="{{ route('reports.health') }}" class="btn btn-danger">Generate Report</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection