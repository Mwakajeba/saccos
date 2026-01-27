@extends('layouts.main')

@section('title', 'Shares Management')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Shares Management', 'url' => '#', 'icon' => 'bx bx-bar-chart-square']
             ]" />

        <h6 class="mb-0 text-uppercase">SHARES MANAGEMENT</h6>
        <hr />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <!-- Share Product -->
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-primary position-relative">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-package fs-1 text-primary"></i>
                                        </div>
                                        <h5 class="card-title">Share Product</h5>
                                        <p class="card-text">Manage share products and configurations.</p>
                                        <a href="{{ route('shares.products.index') }}" class="btn btn-primary position-relative">
                                            <i class="bx bx-package me-1"></i> View Products
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Share Account -->
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-success position-relative">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-user-circle fs-1 text-success"></i>
                                        </div>
                                        <h5 class="card-title">Share Account</h5>
                                        <p class="card-text">Manage customer share accounts.</p>
                                        <a href="{{ route('shares.accounts.index') }}" class="btn btn-success position-relative">
                                            <i class="bx bx-user-circle me-1"></i> View Accounts
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Share Deposit -->
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-info position-relative">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-down-arrow-circle fs-1 text-info"></i>
                                        </div>
                                        <h5 class="card-title">Share Deposit</h5>
                                        <p class="card-text">Process and manage share deposits.</p>
                                        <a href="{{ route('shares.deposits.index') }}" class="btn btn-info position-relative">
                                            <i class="bx bx-down-arrow-circle me-1"></i> View Deposits
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Share Withdrawal -->
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-warning position-relative">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-up-arrow-circle fs-1 text-warning"></i>
                                        </div>
                                        <h5 class="card-title">Share Withdrawal</h5>
                                        <p class="card-text">Process and manage share withdrawals.</p>
                                        <a href="{{ route('shares.withdrawals.index') }}" class="btn btn-warning position-relative">
                                            <i class="bx bx-up-arrow-circle me-1"></i> View Withdrawals
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Share Transfer -->
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-secondary position-relative">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-transfer fs-1 text-secondary"></i>
                                        </div>
                                        <h5 class="card-title">Share Transfer</h5>
                                        <p class="card-text">Transfer shares between accounts.</p>
                                        <a href="{{ route('shares.transfers.index') }}" class="btn btn-secondary position-relative">
                                            <i class="bx bx-transfer me-1"></i> View Transfers
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Opening Balance -->
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-dark position-relative">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-calendar-check fs-1 text-dark"></i>
                                        </div>
                                        <h5 class="card-title">Opening Balance</h5>
                                        <p class="card-text">Import and manage share opening balances.</p>
                                        <a href="{{ route('shares.opening-balance.index') }}" class="btn btn-dark position-relative">
                                            <i class="bx bx-calendar-check me-1"></i> Import Opening Balance
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Dividend Management -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

