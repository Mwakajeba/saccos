@extends('layouts.main')

@php
    use Vinkla\Hashids\Facades\Hashids;
@endphp

@section('title', 'View Contribution Product')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Contributions', 'url' => route('contributions.index'), 'icon' => 'bx bx-donate-heart'],
            ['label' => 'Contribution Products', 'url' => route('contributions.products.index'), 'icon' => 'bx bx-package'],
            ['label' => $product->product_name, 'url' => '#', 'icon' => 'bx bx-show']
        ]" />

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 text-uppercase text-info">{{ $product->product_name }}</h6>
            <div class="d-flex gap-2">
                <a href="{{ route('contributions.deposits.create', ['product_id' => Hashids::encode($product->id)]) }}" class="btn btn-success">
                    <i class="bx bx-down-arrow-circle me-1"></i> Deposit
                </a>
                <a href="{{ route('contributions.withdrawals.create', ['product_id' => Hashids::encode($product->id)]) }}" class="btn btn-warning">
                    <i class="bx bx-up-arrow-circle me-1"></i> Withdrawal
                </a>
                <a href="{{ route('contributions.transfers.index') }}" class="btn btn-info">
                    <i class="bx bx-transfer me-1"></i> Transfer
                </a>
                <a href="{{ route('contributions.products.index') }}" class="btn btn-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Back
                </a>
            </div>
        </div>
        <hr />

        <!-- Statistics Widgets -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-primary">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="text-muted mb-2">Total Customers</h6>
                                <h4 class="mb-0 text-primary">{{ number_format($totalCustomers) }}</h4>
                            </div>
                            <div class="widgets-icons bg-primary text-white">
                                <i class='bx bx-user-circle'></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-success">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="text-muted mb-2">Total Deposits</h6>
                                <h4 class="mb-0 text-success">{{ number_format($totalDeposits, 2) }} TZS</h4>
                            </div>
                            <div class="widgets-icons bg-success text-white">
                                <i class='bx bx-down-arrow-circle'></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-warning">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="text-muted mb-2">Total Withdrawals</h6>
                                <h4 class="mb-0 text-warning">{{ number_format($totalWithdrawals, 2) }} TZS</h4>
                            </div>
                            <div class="widgets-icons bg-warning text-white">
                                <i class='bx bx-up-arrow-circle'></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-info">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="text-muted mb-2">Balance</h6>
                                <h4 class="mb-0 {{ $balance >= 0 ? 'text-info' : 'text-danger' }}">
                                    {{ number_format($balance, 2) }} TZS
                                </h4>
                            </div>
                            <div class="widgets-icons bg-info text-white">
                                <i class='bx bx-wallet'></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Product Details -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0"><i class="bx bx-info-circle me-2"></i>Product Information</h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <th width="40%">Product Name:</th>
                                <td>{{ $product->product_name }}</td>
                            </tr>
                            <tr>
                                <th>Category:</th>
                                <td><span class="badge bg-{{ $product->category === 'Mandatory' ? 'warning' : 'info' }}">{{ $product->category }}</span></td>
                            </tr>
                            <tr>
                                <th>Interest Rate:</th>
                                <td>{{ number_format($product->interest, 2) }}%</td>
                            </tr>
                            <tr>
                                <th>Auto Create:</th>
                                <td>{{ $product->auto_create }}</td>
                            </tr>
                            <tr>
                                <th>Can Withdraw:</th>
                                <td>
                                    @if($product->can_withdraw)
                                        <span class="badge bg-success">Yes</span>
                                    @else
                                        <span class="badge bg-secondary">No</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td>
                                    @if($product->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">Inactive</span>
                                    @endif
                                </td>
                            </tr>
                            @if($product->description)
                            <tr>
                                <th>Description:</th>
                                <td>{{ $product->description }}</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0"><i class="bx bx-cog me-2"></i>Product Settings</h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <th width="40%">Compound Period:</th>
                                <td>{{ $product->compound_period }}</td>
                            </tr>
                            <tr>
                                <th>Interest Calculation:</th>
                                <td>{{ $product->interest_calculation_type }}</td>
                            </tr>
                            <tr>
                                <th>Interest Posting:</th>
                                <td>{{ $product->interest_posting_period ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Lockin Period:</th>
                                <td>{{ $product->lockin_period_frequency }} {{ $product->lockin_period_frequency_type }}</td>
                            </tr>
                            <tr>
                                <th>Opening Balance:</th>
                                <td>{{ number_format($product->automatic_opening_balance, 2) }} TZS</td>
                            </tr>
                            <tr>
                                <th>Min Balance for Interest:</th>
                                <td>{{ number_format($product->minimum_balance_for_interest_calculations, 2) }} TZS</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transactions Table -->
        <div class="card">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0"><i class="bx bx-list-ul me-2"></i>Transactions</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="productTransactionsTable" class="table table-striped table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>TrxId</th>
                                <th>Date</th>
                                <th>Customer</th>
                                <th>Type</th>
                                <th class="text-end">Credit</th>
                                <th class="text-end">Debit</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data will be loaded via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize DataTable with Ajax
        var table = $('#productTransactionsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("contributions.products.transactions.data", Hashids::encode($product->id)) }}',
                type: 'GET',
                error: function(xhr, error, code) {
                    console.error('DataTables Ajax Error:', error, code);
                    Swal.fire({
                        title: 'Error!',
                        text: 'Failed to load transactions data. Please refresh the page.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            },
            columns: [
                { data: 'trx_id', name: 'trx_id', title: 'TrxId' },
                { data: 'date_formatted', name: 'date', title: 'Date' },
                { data: 'customer_name', name: 'customer_name', title: 'Customer' },
                { data: 'type_badge', name: 'transaction_type', title: 'Type' },
                { 
                    data: 'credit', 
                    name: 'credit', 
                    title: 'Credit',
                    className: 'text-end text-success'
                },
                { 
                    data: 'debit', 
                    name: 'debit', 
                    title: 'Debit',
                    className: 'text-end text-danger'
                },
                { data: 'description_text', name: 'description', title: 'Description' }
            ],
            responsive: true,
            order: [[1, 'desc']], // Order by date descending (newest first)
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            language: {
                search: "",
                searchPlaceholder: "Search transactions...",
                processing: '<i class="bx bx-loader-alt bx-spin"></i> Loading transactions...'
            }
        });
    });
</script>
@endpush

