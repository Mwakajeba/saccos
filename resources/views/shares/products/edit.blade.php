@extends('layouts.main')

@section('title', 'Edit Share Product')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Shares Management', 'url' => route('shares.management'), 'icon' => 'bx bx-bar-chart-square'],
            ['label' => 'Share Products', 'url' => route('shares.products.index'), 'icon' => 'bx bx-package'],
            ['label' => 'Edit', 'url' => '#', 'icon' => 'bx bx-edit']
        ]" />

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 text-uppercase">EDIT SHARE PRODUCT</h6>
            <a href="{{ route('shares.products.index') }}" class="btn btn-success">
                <i class="bx bx-list-ul me-1"></i> Shares list
            </a>
        </div>
        <hr />

        <div class="card">
            <div class="card-body">
                @include('shares.products.form')
            </div>
        </div>
    </div>
</div>
@endsection

