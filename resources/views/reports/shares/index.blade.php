@extends('layouts.main')

@section('title', 'Share Reports')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Reports', 'url' => route('reports.index'), 'icon' => 'bx bx-file'],
                ['label' => 'Share Reports', 'url' => '#', 'icon' => 'bx bx-bar-chart-square']
            ]" />
            <h6 class="mb-0 text-uppercase">SHARE REPORTS</h6>
            <hr />

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title mb-4">Share Reports</h4>

                            <div class="row">
                                <!-- Share Register Report -->
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card border-primary">
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-list-ul fs-1 text-primary"></i>
                                            </div>
                                            <h5 class="card-title">Share Register Report</h5>
                                            <p class="card-text">View all share accounts with certificate numbers, member details, and share balances.</p>
                                            <a href="{{ route('reports.shares.share-register') }}" class="btn btn-primary">
                                                <i class="bx bx-file me-1"></i> Generate Report
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Member Ledger Report -->
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card border-success">
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-book fs-1 text-success"></i>
                                            </div>
                                            <h5 class="card-title">Member Ledger Report</h5>
                                            <p class="card-text">View detailed transaction history for a member's share account including deposits, withdrawals, and transfers.</p>
                                            <a href="{{ route('reports.shares.member-ledger') }}" class="btn btn-success">
                                                <i class="bx bx-file me-1"></i> Generate Report
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

