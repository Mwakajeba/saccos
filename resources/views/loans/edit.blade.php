@extends('layouts.main')
@section('title', 'Edit Loan')

@section('content')
<div class="page-wrapper">
    <div class="page-content"> 
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Loans', 'url' => route('loans.list'), 'icon' => 'bx bx-money'],
            ['label' => 'Edit Loan', 'url' => '#', 'icon' => 'bx bx-edit']
        ]" />
        
        <div class="row">
            <!-- Right Column: Guidelines -->
            <div class="col-md-4 col-lg-3 order-md-2 mb-3">
                @include('loans.guidelines')
            </div>

            <!-- Left Column: Form -->
            <div class="col-md-8 col-lg-9 order-md-1">
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="mb-0"><i class="bx bx-edit me-2"></i>EDIT LOAN</h6>
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