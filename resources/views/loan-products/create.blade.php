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