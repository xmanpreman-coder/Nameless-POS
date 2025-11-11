@extends('layouts.app')

@section('title', 'Sales Report')

@section('third_party_stylesheets')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css">
@endsection

@section('breadcrumb')
    <ol class="breadcrumb border-0 m-0">
        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
        <li class="breadcrumb-item active">Sales Report</li>
    </ol>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <!-- Filter Form -->
                        <form method="GET" class="mb-3">
                            <div class="row">
                                <div class="col-md-3">
                                    <label for="start_date">Start Date</label>
                                    <input type="date" name="start_date" id="start_date" class="form-control" 
                                           value="{{ request('start_date', today()->subDays(30)->format('Y-m-d')) }}">
                                </div>
                                <div class="col-md-3">
                                    <label for="end_date">End Date</label>
                                    <input type="date" name="end_date" id="end_date" class="form-control" 
                                           value="{{ request('end_date', today()->format('Y-m-d')) }}">
                                </div>
                                <div class="col-md-3">
                                    <label for="customer_id">Customer</label>
                                    <select name="customer_id" id="customer_id" class="form-control">
                                        <option value="">All Customers</option>
                                        @foreach($customers as $customer)
                                            <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                                                {{ $customer->customer_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label>&nbsp;</label><br>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-search"></i> Filter
                                    </button>
                                    <a href="{{ route('sales-report.index') }}" class="btn btn-secondary">
                                        <i class="bi bi-x-circle"></i> Clear
                                    </a>
                                </div>
                            </div>
                        </form>

                        <hr>

                        <div class="table-responsive">
                            {!! $dataTable->table() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('page_scripts')
    {!! $dataTable->scripts() !!}
    <script>
        // Initialize print protection flag
        window.printInProgress = window.printInProgress || false;
    </script>
@endpush
