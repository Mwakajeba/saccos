@extends('layouts.main')

@section('title', 'Contribution Reports')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Reports', 'url' => route('reports.index'), 'icon' => 'bx bx-file'],
                ['label' => 'Contribution Reports', 'url' => '#', 'icon' => 'bx bx-bar-chart-square']
            ]" />
            <h6 class="mb-0 text-uppercase">CONTRIBUTION REPORTS</h6>
            <hr />

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title mb-4">Contribution Reports</h4>

                            <div class="row">
                                <!-- Contribution Register Report -->
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card border-primary">
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-list-ul fs-1 text-primary"></i>
                                            </div>
                                            <h5 class="card-title">Contribution Register Report</h5>
                                            <p class="card-text">View all contribution accounts with member details and contribution balances.</p>
                                            <a href="{{ route('reports.contributions.contribution-register') }}" class="btn btn-primary">
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
                                            <p class="card-text">View detailed transaction history for a member's contribution account including deposits and withdrawals.</p>
                                            <a href="{{ route('reports.contributions.member-ledger') }}" class="btn btn-success">
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
