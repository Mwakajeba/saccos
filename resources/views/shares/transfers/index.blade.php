@extends('layouts.main')

@section('title', 'Share Transfers')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Shares Management', 'url' => route('shares.management'), 'icon' => 'bx bx-bar-chart-square'],
            ['label' => 'Share Transfers', 'url' => '#', 'icon' => 'bx bx-right-arrow-alt']
             ]" />
        <h6 class="mb-0 text-uppercase">SHARE TRANSFERS</h6>
        <hr />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Share Transfers</h5>
                        <p class="card-text">Share Transfers module coming soon.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

