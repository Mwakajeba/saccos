@extends('layouts.main')

@section('title', 'Share Withdrawal Details')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Shares Management', 'url' => route('shares.management'), 'icon' => 'bx bx-bar-chart-square'],
            ['label' => 'Share Withdrawals', 'url' => route('shares.withdrawals.index'), 'icon' => 'bx bx-up-arrow-circle'],
            ['label' => 'Withdrawal Details', 'url' => '#', 'icon' => 'bx bx-info-circle']
        ]" />

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h6 class="mb-0 text-uppercase">SHARE WITHDRAWAL DETAILS</h6>
            <div class="d-flex gap-2">
                <a href="{{ route('shares.withdrawals.edit', Vinkla\Hashids\Facades\Hashids::encode($withdrawal->id)) }}" class="btn btn-primary">
                    <i class="bx bx-edit me-1"></i> Edit
                </a>
                <button class="btn btn-danger delete-btn" 
                        data-id="{{ Vinkla\Hashids\Facades\Hashids::encode($withdrawal->id) }}" 
                        data-name="Withdrawal #{{ $withdrawal->id }}">
                    <i class="bx bx-trash me-1"></i> Delete
                </button>
                <a href="{{ route('shares.withdrawals.index') }}" class="btn btn-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Back to List
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-warning text-white">
                        <h6 class="mb-0"><i class="bx bx-up-arrow-circle me-2"></i>Withdrawal Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Withdrawal ID:</strong></div>
                            <div class="col-sm-8"><span class="badge bg-dark">#{{ $withdrawal->id }}</span></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Account Number:</strong></div>
                            <div class="col-sm-8">{{ $withdrawal->shareAccount->account_number ?? 'N/A' }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Member Name:</strong></div>
                            <div class="col-sm-8">{{ $withdrawal->shareAccount->customer->name ?? 'N/A' }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Member Number:</strong></div>
                            <div class="col-sm-8">{{ $withdrawal->shareAccount->customer->customerNo ?? 'N/A' }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Share Product:</strong></div>
                            <div class="col-sm-8">{{ $withdrawal->shareAccount->shareProduct->share_name ?? 'N/A' }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Withdrawal Date:</strong></div>
                            <div class="col-sm-8">{{ $withdrawal->withdrawal_date ? $withdrawal->withdrawal_date->format('d M, Y') : 'N/A' }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Number of Shares:</strong></div>
                            <div class="col-sm-8"><span class="text-info fw-bold">{{ number_format($withdrawal->number_of_shares, 4) }}</span></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Withdrawal Amount:</strong></div>
                            <div class="col-sm-8"><span class="text-warning fw-bold">{{ number_format($withdrawal->withdrawal_amount, 2) }}</span></div>
                        </div>
                        @if($withdrawal->withdrawal_fee > 0)
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Withdrawal Fee:</strong></div>
                            <div class="col-sm-8"><span class="text-danger fw-bold">{{ number_format($withdrawal->withdrawal_fee, 2) }}</span></div>
                        </div>
                        @endif
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Net Amount (After Fee):</strong></div>
                            <div class="col-sm-8"><span class="text-success fw-bold">{{ number_format($withdrawal->total_amount, 2) }}</span></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Bank Account:</strong></div>
                            <div class="col-sm-8">{{ $withdrawal->bankAccount->name ?? 'N/A' }} {{ $withdrawal->bankAccount ? '(' . $withdrawal->bankAccount->account_number . ')' : '' }}</div>
                        </div>
                        @if($withdrawal->cheque_number)
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Cheque Number:</strong></div>
                            <div class="col-sm-8">{{ $withdrawal->cheque_number }}</div>
                        </div>
                        @endif
                        @if($withdrawal->transaction_reference)
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Transaction Reference:</strong></div>
                            <div class="col-sm-8">{{ $withdrawal->transaction_reference }}</div>
                        </div>
                        @endif
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Status:</strong></div>
                            <div class="col-sm-8">
                                @if($withdrawal->status == 'pending')
                                    <span class="badge bg-warning">Pending</span>
                                @elseif($withdrawal->status == 'approved')
                                    <span class="badge bg-success">Approved</span>
                                @else
                                    <span class="badge bg-danger">Rejected</span>
                                @endif
                            </div>
                        </div>
                        @if($withdrawal->notes)
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Notes:</strong></div>
                            <div class="col-sm-8">{{ $withdrawal->notes }}</div>
                        </div>
                        @endif
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Created By:</strong></div>
                            <div class="col-sm-8">{{ $withdrawal->createdBy->name ?? 'N/A' }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Created At:</strong></div>
                            <div class="col-sm-8">{{ $withdrawal->created_at ? $withdrawal->created_at->format('d M, Y H:i') : 'N/A' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0"><i class="bx bx-wallet me-2"></i>Account Summary</h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">
                                <strong>Account Number:</strong> <span class="float-end">{{ $withdrawal->shareAccount->account_number ?? 'N/A' }}</span>
                            </li>
                            <li class="list-group-item">
                                <strong>Current Balance:</strong> <span class="float-end">{{ number_format($withdrawal->shareAccount->share_balance ?? 0, 4) }} shares</span>
                            </li>
                            <li class="list-group-item">
                                <strong>Nominal Value:</strong> <span class="float-end">{{ number_format($withdrawal->shareAccount->shareProduct->nominal_price ?? 0, 2) }}</span>
                            </li>
                            <li class="list-group-item">
                                <strong>Product Name:</strong> <span class="float-end">{{ $withdrawal->shareAccount->shareProduct->share_name ?? 'N/A' }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        $('.delete-btn').on('click', function(e) {
            e.preventDefault();
            var withdrawalId = $(this).data('id');
            var withdrawalName = $(this).data('name');
            
            Swal.fire({
                title: 'Are you sure?',
                text: `You want to delete ${withdrawalName}? This action cannot be undone!`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ route("shares.withdrawals.destroy", ":id") }}'.replace(':id', withdrawalId),
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            _method: 'DELETE'
                        },
                        beforeSend: function() {
                            Swal.fire({
                                title: 'Deleting...',
                                text: 'Please wait while we delete the share withdrawal.',
                                allowOutsideClick: false,
                                didOpen: () => { Swal.showLoading(); }
                            });
                        },
                        success: function(response) {
                            Swal.fire({
                                title: 'Deleted!',
                                text: response.message,
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                window.location.href = '{{ route("shares.withdrawals.index") }}';
                            });
                        },
                        error: function(xhr) {
                            console.error('Delete Error:', xhr.responseText);
                            Swal.fire({
                                title: 'Error!',
                                text: 'Failed to delete share withdrawal. ' + (xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : ''),
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
@endsection

