@extends('layouts.main')

@php
    use Vinkla\Hashids\Facades\Hashids;
@endphp

@section('title', 'Share Account Details')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Shares Management', 'url' => route('shares.management'), 'icon' => 'bx bx-bar-chart-square'],
            ['label' => 'Share Accounts', 'url' => route('shares.accounts.index'), 'icon' => 'bx bx-wallet'],
            ['label' => 'Account Details', 'url' => '#', 'icon' => 'bx bx-info-circle']
        ]" />

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 text-uppercase text-info">SHARE ACCOUNT DETAILS</h6>
            <div class="d-flex gap-2">
                <a href="{{ route('shares.accounts.edit', Hashids::encode($shareAccount->id)) }}" class="btn btn-warning">
                    <i class="bx bx-edit me-1"></i> Edit
                </a>
                <button type="button" class="btn btn-danger delete-btn" 
                        data-id="{{ Hashids::encode($shareAccount->id) }}" 
                        data-name="{{ $shareAccount->account_number }}">
                    <i class="bx bx-trash me-1"></i> Delete
                </button>
                <a href="{{ route('shares.accounts.index') }}" class="btn btn-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Back
                </a>
            </div>
        </div>
        <hr />

        <!-- Statistics Widgets -->
        <div class="row mb-4">
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
                                <h6 class="text-muted mb-2">Total Transfers</h6>
                                <h4 class="mb-0 text-info">{{ number_format($totalTransfers, 2) }} TZS</h4>
                            </div>
                            <div class="widgets-icons bg-info text-white">
                                <i class='bx bx-transfer'></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-primary">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="text-muted mb-2">Current Balance</h6>
                                <h4 class="mb-0 {{ $currentBalance >= 0 ? 'text-primary' : 'text-danger' }}">
                                    {{ number_format($currentBalance, 2) }} TZS
                                </h4>
                            </div>
                            <div class="widgets-icons bg-primary text-white">
                                <i class='bx bx-wallet'></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Account Information -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0"><i class="bx bx-wallet me-2"></i>Account Information</h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <th width="40%">Account Number:</th>
                                <td><span class="badge bg-dark">{{ $shareAccount->account_number }}</span></td>
                            </tr>
                            @if($shareAccount->certificate_number)
                            <tr>
                                <th>Certificate Number:</th>
                                <td><span class="badge bg-primary">{{ $shareAccount->certificate_number }}</span></td>
                            </tr>
                            @endif
                            <tr>
                                <th>Member Name:</th>
                                <td>{{ $shareAccount->customer->name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Member Number:</th>
                                <td>{{ $shareAccount->customer->customerNo ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Share Product:</th>
                                <td>{{ $product->share_name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Share Balance:</th>
                                <td><strong class="text-primary">{{ number_format($shareAccount->share_balance ?? 0, 4) }}</strong></td>
                            </tr>
                            <tr>
                                <th>Nominal Value:</th>
                                <td><strong class="text-success">{{ number_format($shareAccount->nominal_value ?? 0, 2) }} TZS</strong></td>
                            </tr>
                            <tr>
                                <th>Opening Date:</th>
                                <td>{{ $shareAccount->opening_date ? $shareAccount->opening_date->format('d M, Y') : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td>
                                    @php
                                        $badgeClass = match ($shareAccount->status) {
                                            'active' => 'badge bg-success',
                                            'inactive' => 'badge bg-warning',
                                            'closed' => 'badge bg-danger',
                                            default => 'badge bg-secondary',
                                        };
                                    @endphp
                                    <span class="{{ $badgeClass }}">{{ ucfirst($shareAccount->status) }}</span>
                                </td>
                            </tr>
                            @if($shareAccount->notes)
                            <tr>
                                <th>Notes:</th>
                                <td>{{ $shareAccount->notes }}</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0"><i class="bx bx-info-circle me-2"></i>Product Details</h6>
                    </div>
                    <div class="card-body">
                        @if($product)
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">Product Name:</th>
                                    <td>{{ $product->share_name }}</td>
                                </tr>
                                <tr>
                                    <th>Nominal Price:</th>
                                    <td>{{ number_format($product->nominal_price ?? 0, 2) }} TZS</td>
                                </tr>
                                <tr>
                                    <th>Required Share:</th>
                                    <td>{{ number_format($product->required_share ?? 0, 2) }}</td>
                                </tr>
                                @if($product->dividend_rate)
                                <tr>
                                    <th>Dividend Rate:</th>
                                    <td>{{ number_format($product->dividend_rate * 100, 2) }}%</td>
                                </tr>
                                @endif
                                <tr>
                                    <th>Product Status:</th>
                                    <td>
                                        @if($product->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-danger">Inactive</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        @else
                            <p class="text-muted">No product information available.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Transaction Statement -->
        <div class="card">
            <div class="card-header bg-secondary text-white">
                <h6 class="mb-0"><i class="bx bx-list-ul me-2"></i>Transaction Statement</h6>
            </div>
            <div class="card-body">
                <!-- Date Filter Form -->
                <form id="statementFilterForm" class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" 
                               value="{{ request('start_date', $shareAccount->opening_date->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-4">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" 
                               value="{{ request('end_date', now()->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-4 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-search me-1"></i> Filter
                        </button>
                        <button type="button" class="btn btn-success" id="exportPdfBtn">
                            <i class="bx bx-file me-1"></i> Export PDF
                        </button>
                    </div>
                </form>

                <!-- Summary Cards -->
                <div class="row mb-3" id="summaryCards" style="display: none;">
                    <div class="col-md-4">
                        <div class="card border-primary">
                            <div class="card-body text-center">
                                <h6 class="text-muted mb-1">Opening Balance</h6>
                                <h5 class="mb-0 text-primary" id="openingBalance">0.00</h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-info">
                            <div class="card-body text-center">
                                <h6 class="text-muted mb-1">Closing Balance</h6>
                                <h5 class="mb-0 text-info" id="closingBalance">0.00</h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-success">
                            <div class="card-body text-center">
                                <h6 class="text-muted mb-1">Net Movement</h6>
                                <h5 class="mb-0 text-success" id="netMovement">0.00</h5>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Transactions Table -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="transactionsTable">
                        <thead>
                            <tr>
                                <th>Trx ID</th>
                                <th>Date</th>
                                <th>Description</th>
                                <th>Type</th>
                                <th class="text-end">Credit</th>
                                <th class="text-end">Debit</th>
                                <th class="text-end">Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data will be loaded via Ajax -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .widgets-icons {
        width: 50px;
        height: 50px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        var accountId = '{{ Hashids::encode($shareAccount->id) }}';
        var table;

        // Initialize DataTable
        function initializeTable() {
            if ($.fn.DataTable.isDataTable('#transactionsTable')) {
                $('#transactionsTable').DataTable().destroy();
            }

            var startDate = $('#start_date').val();
            var endDate = $('#end_date').val();

            table = $('#transactionsTable').DataTable({
                processing: true,
                serverSide: false,
                ajax: {
                    url: '{{ route("shares.accounts.transactions.data", Hashids::encode($shareAccount->id)) }}',
                    type: 'GET',
                    data: function(d) {
                        d.start_date = startDate;
                        d.end_date = endDate;
                    },
                    dataSrc: function(json) {
                        // Update summary cards
                        $('#openingBalance').text(parseFloat(json.opening_balance).toLocaleString('en-US', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        }));
                        $('#closingBalance').text(parseFloat(json.closing_balance).toLocaleString('en-US', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        }));
                        
                        var netMovement = parseFloat(json.closing_balance) - parseFloat(json.opening_balance);
                        $('#netMovement').text(netMovement.toLocaleString('en-US', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        }));
                        $('#netMovement').removeClass('text-success text-danger').addClass(netMovement >= 0 ? 'text-success' : 'text-danger');
                        
                        $('#summaryCards').show();
                        return json.data;
                    }
                },
                columns: [
                    { data: 'trx_id', name: 'trx_id' },
                    { data: 'date', name: 'date' },
                    { data: 'description', name: 'description' },
                    { data: 'type', name: 'type' },
                    { data: 'credit', name: 'credit', className: 'text-end' },
                    { data: 'debit', name: 'debit', className: 'text-end' },
                    { data: 'balance', name: 'balance', className: 'text-end' }
                ],
                order: [[1, 'asc']], // Order by date ascending
                pageLength: 25,
                language: {
                    processing: "Loading transactions...",
                    emptyTable: "No transactions found for the selected period."
                }
            });
        }

        // Initial load
        initializeTable();

        // Filter form submission
        $('#statementFilterForm').on('submit', function(e) {
            e.preventDefault();
            initializeTable();
        });

        // Export PDF
        $('#exportPdfBtn').on('click', function() {
            var startDate = $('#start_date').val();
            var endDate = $('#end_date').val();
            var url = '{{ route("shares.accounts.statement.export", Hashids::encode($shareAccount->id)) }}' + 
                      '?start_date=' + startDate + '&end_date=' + endDate;
            window.open(url, '_blank');
        });

        // Handle delete button click
        $('.delete-btn').on('click', function(e) {
            e.preventDefault();
            
            var accountId = $(this).data('id');
            var accountNumber = $(this).data('name');
            var deleteBtn = $(this);
            
            Swal.fire({
                title: 'Are you sure?',
                text: `You want to delete share account "${accountNumber}"? This action cannot be undone!`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading
                    Swal.fire({
                        title: 'Deleting...',
                        text: 'Please wait while we delete the share account.',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    // Make AJAX delete request
                    $.ajax({
                        url: '{{ route("shares.accounts.destroy", ":id") }}'.replace(':id', accountId),
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            _method: 'DELETE'
                        },
                        success: function(response) {
                            Swal.fire({
                                title: 'Deleted!',
                                text: 'Share account has been deleted successfully.',
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                // Redirect to index page
                                window.location.href = '{{ route("shares.accounts.index") }}';
                            });
                        },
                        error: function(xhr) {
                            var errorMessage = 'Failed to delete share account.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            }
                            
                            Swal.fire({
                                title: 'Error!',
                                text: errorMessage,
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    });
                }
            });
        });
    });
</script>
@endpush
