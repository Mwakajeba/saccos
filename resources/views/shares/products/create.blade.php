@extends('layouts.main')

@section('title', 'Create Share Product')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Shares Management', 'url' => route('shares.management'), 'icon' => 'bx bx-bar-chart-square'],
            ['label' => 'Share Products', 'url' => route('shares.products.index'), 'icon' => 'bx bx-package'],
            ['label' => 'Create', 'url' => '#', 'icon' => 'bx bx-plus']
        ]" />

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 text-uppercase">ADD SHARE</h6>
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

