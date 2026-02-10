@extends('layouts.main')

@section('title', 'Contributions')


@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Contributions', 'url' => '#', 'icon' => 'bx bx-donate-heart']
        ]" />
        <h6 class="mb-0 text-uppercase">CONTRIBUTIONS</h6>
        <hr />
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            @foreach ([
                                ['title' => 'Contribution Products', 'icon' => 'bx bx-package', 'color' => 'primary', 'description' => 'Manage contribution products and their settings.', 'route' => 'contributions.products.index', 'button_text' => 'View Products', 'permission' => 'view contribution products', 'count_key' => 'products'],
                                ['title' => 'Contributions Account', 'icon' => 'bx bx-user-circle', 'color' => 'success', 'description' => 'Manage contribution accounts for members.', 'route' => 'contributions.accounts.index', 'button_text' => 'View Accounts', 'permission' => 'view contributions accounts', 'count_key' => 'accounts'],
                                ['title' => 'Contributions Deposits', 'icon' => 'bx bx-down-arrow-circle', 'color' => 'info', 'description' => 'Process and manage contribution deposits.', 'route' => 'contributions.deposits.index', 'button_text' => 'View Deposits', 'permission' => 'view contributions deposits', 'count_key' => 'deposits'],
                                ['title' => 'Contributions Withdrawals', 'icon' => 'bx bx-up-arrow-circle', 'color' => 'warning', 'description' => 'Process and manage contribution withdrawals.', 'route' => 'contributions.withdrawals.index', 'button_text' => 'View Withdrawals', 'permission' => 'view contributions withdrawal', 'count_key' => 'withdrawals'],
                                ['title' => 'Contributions Transfers', 'icon' => 'bx bx-transfer', 'color' => 'secondary', 'description' => 'Manage transfers between contribution accounts.', 'route' => 'contributions.transfers.index', 'button_text' => 'View Transfers', 'permission' => 'view contributions transfer', 'count_key' => 'transfers'],
                                ['title' => 'Opening Balance', 'icon' => 'bx bx-calendar-check', 'color' => 'dark', 'description' => 'Import and manage contribution opening balances.', 'route' => 'contributions.opening-balance.index', 'button_text' => 'Import Opening Balance', 'permission' => 'view contributions deposits', 'count_key' => 'opening_balances'],
                                ['title' => 'Interest on Saving', 'icon' => 'bx bx-calculator', 'color' => 'info', 'description' => 'View calculated interest on saving for all contribution accounts.', 'route' => 'contributions.interest-on-saving.index', 'button_text' => 'View Interest', 'permission' => 'view contributions deposits', 'count_key' => 'interest_on_saving'],
                            ] as $card)
                                @can($card['permission'])
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card border-{{ $card['color'] }} position-relative">
                                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-{{ $card['color'] }}">{{ $stats[$card['count_key']] ?? 0 }}</span>
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="{{ $card['icon'] }} fs-1 text-{{ $card['color'] }}"></i>
                                            </div>
                                            <h5 class="card-title">{{ $card['title'] }}</h5>
                                            <p class="card-text">{{ $card['description'] }}</p>
                                            <a href="{{ $card['route'] ? route($card['route']) : '#' }}" class="btn btn-{{ $card['color'] }} position-relative">
                                                <i class="{{ $card['icon'] }} me-1"></i> {{ $card['button_text'] }}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                @endcan
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <hr />
        <h6 class="mb-0 text-uppercase mt-4">CONTRIBUTIONS REPORTS</h6>
        <div class="row mt-3">
            @can('view contribution balance report')
            <div class="col-md-6 mb-3">
                <a href="{{ route('contributions.reports.balance') }}" class="text-decoration-none">
                    <div class="card border-info">
                        <div class="card-body d-flex align-items-center">
                            <i class="bx bx-bar-chart-alt-2 fs-2 text-info me-3"></i>
                            <span class="fs-5">Contributions Balance Report</span>
                        </div>
                    </div>
                </a>
            </div>
            @endcan
            @can('view contribution transactions report')
            <div class="col-md-6 mb-3">
                <a href="{{ route('contributions.reports.transactions') }}" class="text-decoration-none">
                    <div class="card border-secondary">
                        <div class="card-body d-flex align-items-center">
                            <i class="bx bx-transfer-alt fs-2 text-secondary me-3"></i>
                            <span class="fs-5">Contributions Transactions Report</span>
                        </div>
                    </div>
                </a>
            </div>
            @endcan
        </div>
    </div>
</div>
@endsection
