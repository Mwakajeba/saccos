@extends('layouts.main')

@section('title', 'Investment Management')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Investment Management', 'url' => '#', 'icon' => 'bx bx-trending-up']
        ]" />
        <h6 class="mb-0 text-uppercase">INVESTMENT MANAGEMENT</h6>
        <hr />

        <!-- Investment Statistics -->
        <div class="row">
            <div class="col-12 col-lg-8">
                <div class="card border-top border-0 border-4 border-primary">
                    <div class="card-body p-5">
                        <div class="card-title d-flex align-items-center">
                            <div><i class="bx bx-trending-up me-1 font-22 text-primary"></i></div>
                            <h5 class="mb-0 text-primary">Investment Statistics</h5>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="card radius-10 bg-primary">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="">
                                                <p class="mb-1 text-white">Total Funds</p>
                                                <h4 class="text-white">{{ \App\Models\UTTFund::where('company_id', auth()->user()->company_id)->count() }}</h4>
                                            </div>
                                            <div class="ms-auto fs-1 text-white"><i class="bx bx-package"></i></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card radius-10 bg-success">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="">
                                                <p class="mb-1 text-white">Active Funds</p>
                                                <h4 class="text-white">{{ \App\Models\UTTFund::where('company_id', auth()->user()->company_id)->where('status', 'Active')->count() }}</h4>
                                            </div>
                                            <div class="ms-auto fs-1 text-white"><i class="bx bx-check-circle"></i></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card radius-10 bg-info">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="">
                                                <p class="mb-1 text-white">Transactions</p>
                                                <h4 class="text-white">{{ \App\Models\UTTTransaction::where('company_id', auth()->user()->company_id)->count() }}</h4>
                                            </div>
                                            <div class="ms-auto fs-1 text-white"><i class="bx bx-transfer"></i></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card radius-10 bg-warning">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="">
                                                <p class="mb-1 text-white">NAV Prices</p>
                                                <h4 class="text-white">{{ \App\Models\UTTNavPrice::where('company_id', auth()->user()->company_id)->count() }}</h4>
                                            </div>
                                            <div class="ms-auto fs-1 text-white"><i class="bx bx-line-chart"></i></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-4">
                <div class="card border-top border-0 border-4 border-success">
                    <div class="card-body p-5">
                        <div class="card-title d-flex align-items-center">
                            <div><i class="bx bx-pie-chart me-1 font-22 text-success"></i></div>
                            <h5 class="mb-0 text-success">Quick Links</h5>
                        </div>
                        <hr>
                        <div class="d-grid gap-2">
                            <a href="{{ route('investments.valuation') }}" class="btn btn-primary">
                                <i class="bx bx-bar-chart me-1"></i> Portfolio Valuation
                            </a>
                            <a href="{{ route('investments.member-view') }}" class="btn btn-outline-primary">
                                <i class="bx bx-user me-1"></i> Member View
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Investment Flow Modules -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center">
                            <div><i class="bx bx-grid me-1 font-22 text-primary"></i></div>
                            <h5 class="mb-0 text-primary">Investment Management</h5>
                        </div>
                        <hr>
                        <div class="row">
                            <!-- 1. UTT Funds -->
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-primary position-relative">
                                    <div class="card-body text-center">
                                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary">
                                            {{ \App\Models\UTTFund::where('company_id', auth()->user()->company_id)->count() }}
                                            <span class="visually-hidden">funds count</span>
                                        </span>
                                        <div class="mb-3">
                                            <i class="bx bx-package fs-1 text-primary"></i>
                                        </div>
                                        <h5 class="card-title">UTT Funds</h5>
                                        <p class="card-text">Manage UTT investment funds, asset allocation, and fund settings.</p>
                                        <a href="{{ route('investments.funds.index') }}" class="btn btn-primary">
                                            <i class="bx bx-list-ul me-1"></i> Manage Funds
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- 2. Holdings Register -->
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-success position-relative">
                                    <div class="card-body text-center">
                                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-success">
                                            {{ \App\Models\SaccoUTTHolding::where('company_id', auth()->user()->company_id)->count() }}
                                            <span class="visually-hidden">holdings count</span>
                                        </span>
                                        <div class="mb-3">
                                            <i class="bx bx-book fs-1 text-success"></i>
                                        </div>
                                        <h5 class="card-title">Holdings Register</h5>
                                        <p class="card-text">View member holdings across UTT funds and track unit balances.</p>
                                        <a href="{{ route('investments.holdings.index') }}" class="btn btn-success">
                                            <i class="bx bx-list-ul me-1"></i> View Holdings
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- 3. Transactions -->
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-info position-relative">
                                    <div class="card-body text-center">
                                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-info">
                                            {{ \App\Models\UTTTransaction::where('company_id', auth()->user()->company_id)->count() }}
                                            <span class="visually-hidden">transactions count</span>
                                        </span>
                                        <div class="mb-3">
                                            <i class="bx bx-transfer fs-1 text-info"></i>
                                        </div>
                                        <h5 class="card-title">Transactions</h5>
                                        <p class="card-text">Process subscriptions, redemptions, and transfers with approval workflow.</p>
                                        <a href="{{ route('investments.transactions.index') }}" class="btn btn-info">
                                            <i class="bx bx-transfer me-1"></i> Manage Transactions
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- 4. NAV Prices -->
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-warning position-relative">
                                    <div class="card-body text-center">
                                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning text-dark">
                                            {{ \App\Models\UTTNavPrice::where('company_id', auth()->user()->company_id)->count() }}
                                            <span class="visually-hidden">nav prices count</span>
                                        </span>
                                        <div class="mb-3">
                                            <i class="bx bx-line-chart fs-1 text-warning"></i>
                                        </div>
                                        <h5 class="card-title">NAV Prices</h5>
                                        <p class="card-text">Record and manage Net Asset Value prices for fund valuation.</p>
                                        <a href="{{ route('investments.nav-prices.index') }}" class="btn btn-warning">
                                            <i class="bx bx-line-chart me-1"></i> Manage NAV Prices
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- 5. Cash Flows -->
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-secondary position-relative">
                                    <div class="card-body text-center">
                                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-secondary">
                                            {{ \App\Models\UTTCashFlow::where('company_id', auth()->user()->company_id)->count() }}
                                            <span class="visually-hidden">cash flows count</span>
                                        </span>
                                        <div class="mb-3">
                                            <i class="bx bx-dollar-circle fs-1 text-secondary"></i>
                                        </div>
                                        <h5 class="card-title">Cash Flows</h5>
                                        <p class="card-text">Track investment cash flows, dividends, and distributions.</p>
                                        <a href="{{ route('investments.cash-flows.index') }}" class="btn btn-secondary">
                                            <i class="bx bx-dollar-circle me-1"></i> View Cash Flows
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- 6. Reconciliations -->
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-danger position-relative">
                                    <div class="card-body text-center">
                                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                            {{ \App\Models\UTTReconciliation::where('company_id', auth()->user()->company_id)->count() }}
                                            <span class="visually-hidden">reconciliations count</span>
                                        </span>
                                        <div class="mb-3">
                                            <i class="bx bx-check-square fs-1 text-danger"></i>
                                        </div>
                                        <h5 class="card-title">Reconciliations</h5>
                                        <p class="card-text">Reconcile holdings with custodian and fund administrator records.</p>
                                        <a href="{{ route('investments.reconciliations.index') }}" class="btn btn-danger">
                                            <i class="bx bx-check-square me-1"></i> Manage Reconciliations
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

@push('styles')
<style>
    .card {
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }

    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .badge {
        font-size: 0.75rem;
        padding: 0.5em 0.75em;
    }

    .fs-1 {
        font-size: 3rem !important;
    }

    .position-relative .badge {
        z-index: 10;
        font-size: 0.7rem;
        min-width: 1.5rem;
        height: 1.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px solid white;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    }

    .border-primary { border-color: #0d6efd !important; }
    .border-success { border-color: #198754 !important; }
    .border-info { border-color: #0dcaf0 !important; }
    .border-warning { border-color: #ffc107 !important; }
    .border-secondary { border-color: #6c757d !important; }
    .border-danger { border-color: #dc3545 !important; }
</style>
@endpush
