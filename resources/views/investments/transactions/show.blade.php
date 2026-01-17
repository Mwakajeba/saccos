@extends('layouts.main')

@section('title', 'Transaction Details')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Investment', 'url' => '#', 'icon' => 'bx bx-trending-up'],
            ['label' => 'Transactions', 'url' => route('investments.transactions.index'), 'icon' => 'bx bx-transfer'],
            ['label' => 'Transaction Details', 'url' => '#', 'icon' => 'bx bx-show']
        ]" />

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 text-uppercase">TRANSACTION DETAILS</h6>
            <a href="{{ route('investments.transactions.index') }}" class="btn btn-secondary">
                <i class="bx bx-arrow-back me-1"></i> Back
            </a>
        </div>
        <hr />

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Transaction Information</h5>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Reference Number:</strong>
                                <p>{{ $transaction->reference_number }}</p>
                            </div>
                            <div class="col-md-6">
                                <strong>Fund:</strong>
                                <p>{{ $transaction->uttFund->fund_name }} ({{ $transaction->uttFund->fund_code }})</p>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Transaction Type:</strong>
                                <p>
                                    <span class="badge bg-{{ $transaction->transaction_type == 'BUY' ? 'success' : ($transaction->transaction_type == 'SELL' ? 'danger' : 'info') }}">
                                        {{ $transaction->transaction_type }}
                                    </span>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <strong>Status:</strong>
                                <p>
                                    <span class="badge bg-{{ $transaction->status == 'SETTLED' ? 'success' : ($transaction->status == 'APPROVED' ? 'info' : ($transaction->status == 'PENDING' ? 'warning' : 'danger')) }}">
                                        {{ $transaction->status }}
                                    </span>
                                </p>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <strong>Trade Date:</strong>
                                <p>{{ $transaction->trade_date->format('M d, Y') }}</p>
                            </div>
                            <div class="col-md-4">
                                <strong>NAV Date:</strong>
                                <p>{{ $transaction->nav_date->format('M d, Y') }}</p>
                            </div>
                            <div class="col-md-4">
                                <strong>Settlement Date:</strong>
                                <p>{{ $transaction->settlement_date->format('M d, Y') }}</p>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <strong>Units:</strong>
                                <p class="h5">{{ number_format($transaction->units, 4) }}</p>
                            </div>
                            <div class="col-md-4">
                                <strong>NAV per Unit:</strong>
                                <p class="h5">{{ number_format($transaction->nav_per_unit, 4) }}</p>
                            </div>
                            <div class="col-md-4">
                                <strong>Total Cash Value:</strong>
                                <p class="h5 text-primary">{{ number_format($transaction->total_cash_value, 2) }}</p>
                            </div>
                        </div>
                        @if($transaction->description)
                        <div class="mb-3">
                            <strong>Description:</strong>
                            <p>{{ $transaction->description }}</p>
                        </div>
                        @endif
                        @if($transaction->rejection_reason)
                        <div class="mb-3">
                            <strong>Rejection Reason:</strong>
                            <p class="text-danger">{{ $transaction->rejection_reason }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Approval Information</h5>
                        <div class="mb-3">
                            <strong>Maker:</strong>
                            <p>{{ $transaction->maker->name ?? 'N/A' }}</p>
                        </div>
                        @if($transaction->checker)
                        <div class="mb-3">
                            <strong>Checker:</strong>
                            <p>{{ $transaction->checker->name }}</p>
                        </div>
                        @endif
                        @if($transaction->approved_at)
                        <div class="mb-3">
                            <strong>Approved At:</strong>
                            <p>{{ $transaction->approved_at->format('M d, Y H:i') }}</p>
                        </div>
                        @endif
                        @if($transaction->settled_at)
                        <div class="mb-3">
                            <strong>Settled At:</strong>
                            <p>{{ $transaction->settled_at->format('M d, Y H:i') }}</p>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="card mt-3">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Actions</h5>
                        <div class="d-grid gap-2">
                            @if($transaction->canBeApproved())
                                <button type="button" class="btn btn-success approve-btn" data-id="{{ $encodedId }}">
                                    <i class="bx bx-check me-1"></i> Approve Transaction
                                </button>
                            @endif

                            @if($transaction->canBeSettled())
                                <button type="button" class="btn btn-primary settle-btn" data-id="{{ $encodedId }}">
                                    <i class="bx bx-check-circle me-1"></i> Settle Transaction
                                </button>
                            @endif

                            @if($transaction->canBeCancelled())
                                <button type="button" class="btn btn-danger cancel-btn" data-id="{{ $encodedId }}">
                                    <i class="bx bx-x me-1"></i> Cancel Transaction
                                </button>
                            @endif

                            @if(!$transaction->canBeApproved() && !$transaction->canBeSettled() && !$transaction->canBeCancelled())
                                <p class="text-muted text-center mb-0">No actions available for this transaction</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Approve transaction
        $(document).on('click', '.approve-btn', function() {
            var transactionId = $(this).data('id');
            Swal.fire({
                title: 'Approve Transaction?',
                text: 'Are you sure you want to approve this transaction?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Approve',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ route("investments.transactions.approve", ":id") }}'.replace(':id', transactionId),
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            Swal.fire('Success!', response.message, 'success').then(() => {
                                location.reload();
                            });
                        },
                        error: function(xhr) {
                            Swal.fire('Error!', xhr.responseJSON?.error || 'Failed to approve transaction', 'error');
                        }
                    });
                }
            });
        });

        // Settle transaction
        $(document).on('click', '.settle-btn', function() {
            var transactionId = $(this).data('id');
            Swal.fire({
                title: 'Settle Transaction?',
                text: 'This will update the holdings. Are you sure?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#007bff',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Settle',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ route("investments.transactions.settle", ":id") }}'.replace(':id', transactionId),
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            Swal.fire('Success!', response.message, 'success').then(() => {
                                location.reload();
                            });
                        },
                        error: function(xhr) {
                            Swal.fire('Error!', xhr.responseJSON?.error || 'Failed to settle transaction', 'error');
                        }
                    });
                }
            });
        });

        // Cancel transaction
        $(document).on('click', '.cancel-btn', function() {
            var transactionId = $(this).data('id');
            Swal.fire({
                title: 'Cancel Transaction?',
                input: 'textarea',
                inputLabel: 'Reason for cancellation',
                inputPlaceholder: 'Enter cancellation reason...',
                inputAttributes: {
                    'aria-label': 'Enter cancellation reason'
                },
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Cancel',
                cancelButtonText: 'Cancel',
                inputValidator: (value) => {
                    if (!value) {
                        return 'Please provide a cancellation reason';
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ route("investments.transactions.cancel", ":id") }}'.replace(':id', transactionId),
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            rejection_reason: result.value
                        },
                        success: function(response) {
                            Swal.fire('Success!', response.message, 'success').then(() => {
                                location.reload();
                            });
                        },
                        error: function(xhr) {
                            Swal.fire('Error!', xhr.responseJSON?.error || 'Failed to cancel transaction', 'error');
                        }
                    });
                }
            });
        });
    });
</script>
@endpush
