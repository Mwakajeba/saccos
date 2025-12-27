@extends('layouts.main')

@section('title', 'Accounting')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Accounting', 'url' => '#', 'icon' => 'bx bx-calculator']
        ]" />
        <h6 class="mb-0 text-uppercase">ACCOUNTING</h6>
        <hr />
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            @foreach ([
                                ['title' => 'Charts of Account - FSLI', 'icon' => 'bx bx-list-ul', 'color' => 'primary', 'description' => 'Manage Financial Statement Line Items and account class groups.', 'route' => 'accounting.account-class-groups.index', 'button_text' => 'View FSLI', 'permission' => null, 'count_key' => 'account_class_groups'],
                                ['title' => 'Charts of Account', 'icon' => 'bx bx-table', 'color' => 'info', 'description' => 'Manage chart of accounts and account classifications.', 'route' => 'accounting.chart-accounts.index', 'button_text' => 'View Accounts', 'permission' => 'view chart accounts', 'count_key' => 'chart_accounts'],
                                ['title' => 'Suppliers', 'icon' => 'bx bx-store', 'color' => 'success', 'description' => 'Manage suppliers and vendor information.', 'route' => 'accounting.suppliers.index', 'button_text' => 'View Suppliers', 'permission' => 'view suppliers', 'count_key' => 'suppliers'],
                                ['title' => 'Manual Journals', 'icon' => 'bx bx-book', 'color' => 'warning', 'description' => 'Create and manage manual journal entries.', 'route' => 'accounting.journals.index', 'button_text' => 'View Journals', 'permission' => 'view journals', 'count_key' => 'journals'],
                                ['title' => 'Payment Voucher', 'icon' => 'bx bx-book', 'color' => 'danger', 'description' => 'Process and manage payment vouchers.', 'route' => 'accounting.payment-vouchers.index', 'button_text' => 'View Payments', 'permission' => 'view payment vouchers', 'count_key' => 'payment_vouchers'],
                                ['title' => 'Receipt Voucher', 'icon' => 'bx bx-receipt', 'color' => 'success', 'description' => 'Process and manage receipt vouchers.', 'route' => 'accounting.receipt-vouchers.index', 'button_text' => 'View Receipts', 'permission' => 'view receipt vouchers', 'count_key' => 'receipt_vouchers'],
                                ['title' => 'Bank Accounts', 'icon' => 'bx bx-building', 'color' => 'primary', 'description' => 'Manage bank accounts and banking information.', 'route' => 'accounting.bank-accounts', 'button_text' => 'View Banks', 'permission' => 'view bank accounts', 'count_key' => 'bank_accounts'],
                                ['title' => 'Bank Reconciliation', 'icon' => 'bx bx-check-circle', 'color' => 'info', 'description' => 'Reconcile bank statements with accounting records.', 'route' => 'accounting.bank-reconciliation.index', 'button_text' => 'View Reconciliation', 'permission' => 'view bank reconciliation', 'count_key' => 'bank_reconciliations'],
                                ['title' => 'Bill Purchases', 'icon' => 'bx bx-purchase-tag', 'color' => 'secondary', 'description' => 'Manage bill purchases and vendor bills.', 'route' => 'accounting.bill-purchases', 'button_text' => 'View Bills', 'permission' => 'view bill purchases', 'count_key' => 'bill_purchases'],
                                ['title' => 'Budget', 'icon' => 'bx bx-bar-chart-alt-2', 'color' => 'dark', 'description' => 'Create and manage budgets for financial planning.', 'route' => 'accounting.budgets.index', 'button_text' => 'View Budgets', 'permission' => 'view budgets', 'count_key' => 'budgets'],
                            ] as $card)
                                @php
                                    $hasPermission = $card['permission'] ? auth()->user()->can($card['permission']) : true;
                                @endphp
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card border-{{ $card['color'] }} position-relative">
                                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-{{ $card['color'] }}">{{ $stats[$card['count_key']] ?? 0 }}</span>
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="{{ $card['icon'] }} fs-1 text-{{ $card['color'] }}"></i>
                                            </div>
                                            <h5 class="card-title">{{ $card['title'] }}</h5>
                                            <p class="card-text">{{ $card['description'] }}</p>
                                            @if($hasPermission && $card['route'])
                                                <a href="{{ route($card['route']) }}" class="btn btn-{{ $card['color'] }} position-relative">
                                                    <i class="{{ $card['icon'] }} me-1"></i> {{ $card['button_text'] }}
                                                </a>
                                            @else
                                                <button class="btn btn-{{ $card['color'] }} position-relative" disabled title="You don't have permission to access this feature">
                                                    <i class="{{ $card['icon'] }} me-1"></i> {{ $card['button_text'] }}
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

