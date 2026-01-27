@extends('layouts.main')
@section('title', 'Create Loan')

@section('content')
<div class="page-wrapper">
    <div class="page-content"> 
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Loans', 'url' => route('loans.list'), 'icon' => 'bx bx-money'],
            ['label' => 'Create Loan', 'url' => '#', 'icon' => 'bx bx-plus']
        ]" />
        
        <div class="row">
            <!-- Right Column: Guidelines -->
            <div class="col-md-4 col-lg-3 order-md-2 mb-3">
                @include('loans.guidelines')
            </div>

            <!-- Left Column: Form -->
            <div class="col-md-8 col-lg-9 order-md-1">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0"><i class="bx bx-plus me-2"></i>CREATE NEW LOAN</h6>
                    </div>
                    <div class="card-body">
                        @include('loans.form')
                    </div>
                </div>
            </div>
        </div>       
    </div>
</div>
@endsection