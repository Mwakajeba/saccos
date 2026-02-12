@extends('layouts.main')
@section('title', 'Create Loan Product')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Loan Products', 'url' => route('loan-products.index')],
            ['label' => 'Create Loan Product']
        ]" />
            
            <div class="row">
                <!-- Right Column: Guidelines -->
                <div class="col-md-4 col-lg-3 order-md-2 mb-3">
                    @include('loan-products.guidelines')
                </div>

                <!-- Left Column: Form -->
                <div class="col-md-8 col-lg-9 order-md-1">
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bx bx-error-circle me-2"></i>
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bx bx-check-circle me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0"><i class="bx bx-plus me-2"></i>CREATE NEW LOAN PRODUCT</h6>
                        </div>
                        <div class="card-body">
                            @include('loan-products.form')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection