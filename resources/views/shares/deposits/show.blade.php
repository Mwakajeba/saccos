@extends('layouts.main')

@section('title', 'Share Deposit Details')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Shares Management', 'url' => route('shares.management'), 'icon' => 'bx bx-bar-chart-square'],
            ['label' => 'Share Deposits', 'url' => route('shares.deposits.index'), 'icon' => 'bx bx-right-arrow-alt'],
            ['label' => 'Deposit Details', 'url' => '#', 'icon' => 'bx bx-info-circle']
        ]" />

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h6 class="mb-0 text-uppercase">SHARE DEPOSIT DETAILS</h6>
            <div class="d-flex gap-2">
                <a href="{{ route('shares.deposits.edit', Vinkla\Hashids\Facades\Hashids::encode($deposit->id)) }}" class="btn btn-primary">
                    <i class="bx bx-edit me-1"></i> Edit
                </a>
                <button class="btn btn-danger delete-btn" 
                        data-id="{{ Vinkla\Hashids\Facades\Hashids::encode($deposit->id) }}" 
                        data-name="Deposit #{{ $deposit->id }}">
                    <i class="bx bx-trash me-1"></i> Delete
                </button>
                <a href="{{ route('shares.deposits.index') }}" class="btn btn-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Back to List
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0"><i class="bx bx-right-arrow-alt me-2"></i>Deposit Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Deposit ID:</strong></div>
                            <div class="col-sm-8"><span class="badge bg-dark">#{{ $deposit->id }}</span></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Account Number:</strong></div>
                            <div class="col-sm-8">{{ $deposit->shareAccount->account_number ?? 'N/A' }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Member Name:</strong></div>
                            <div class="col-sm-8">{{ $deposit->shareAccount->customer->name ?? 'N/A' }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Member Number:</strong></div>
                            <div class="col-sm-8">{{ $deposit->shareAccount->customer->customerNo ?? 'N/A' }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Share Product:</strong></div>
                            <div class="col-sm-8">{{ $deposit->shareAccount->shareProduct->share_name ?? 'N/A' }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Deposit Date:</strong></div>
                            <div class="col-sm-8">{{ $deposit->deposit_date ? $deposit->deposit_date->format('d M, Y') : 'N/A' }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Deposit Amount:</strong></div>
                            <div class="col-sm-8"><span class="text-success fw-bold">{{ number_format($deposit->deposit_amount, 2) }}</span></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Number of Shares:</strong></div>
                            <div class="col-sm-8"><span class="text-info fw-bold">{{ number_format($deposit->number_of_shares, 4) }}</span></div>
                        </div>
                        @if($deposit->charge_amount > 0)
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Charge Amount:</strong></div>
                            <div class="col-sm-8"><span class="text-warning fw-bold">{{ number_format($deposit->charge_amount, 2) }}</span></div>
                        </div>
                        @endif
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Total Amount:</strong></div>
                            <div class="col-sm-8"><span class="text-primary fw-bold">{{ number_format($deposit->total_amount, 2) }}</span></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Bank Account:</strong></div>
                            <div class="col-sm-8">{{ $deposit->bankAccount->name ?? 'N/A' }} {{ $deposit->bankAccount ? '(' . $deposit->bankAccount->account_number . ')' : '' }}</div>
                        </div>
                        @if($deposit->cheque_number)
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Cheque Number:</strong></div>
                            <div class="col-sm-8">{{ $deposit->cheque_number }}</div>
                        </div>
                        @endif
                        @if($deposit->transaction_reference)
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Transaction Reference:</strong></div>
                            <div class="col-sm-8">{{ $deposit->transaction_reference }}</div>
                        </div>
                        @endif
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Status:</strong></div>
                            <div class="col-sm-8">
                                @if($deposit->status == 'pending')
                                    <span class="badge bg-warning">Pending</span>
                                @elseif($deposit->status == 'approved')
                                    <span class="badge bg-success">Approved</span>
                                @else
                                    <span class="badge bg-danger">Rejected</span>
                                @endif
                            </div>
                        </div>
                        @if($deposit->notes)
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Notes:</strong></div>
                            <div class="col-sm-8">{{ $deposit->notes }}</div>
                        </div>
                        @endif
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Created By:</strong></div>
                            <div class="col-sm-8">{{ $deposit->createdBy->name ?? 'N/A' }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Created At:</strong></div>
                            <div class="col-sm-8">{{ $deposit->created_at ? $deposit->created_at->format('d M, Y H:i') : 'N/A' }}</div>
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
                                <strong>Account Number:</strong> <span class="float-end">{{ $deposit->shareAccount->account_number ?? 'N/A' }}</span>
                            </li>
                            <li class="list-group-item">
                                <strong>Current Balance:</strong> <span class="float-end">{{ number_format($deposit->shareAccount->share_balance ?? 0, 4) }} shares</span>
                            </li>
                            <li class="list-group-item">
                                <strong>Nominal Value:</strong> <span class="float-end">{{ number_format($deposit->shareAccount->shareProduct->nominal_price ?? 0, 2) }}</span>
                            </li>
                            <li class="list-group-item">
                                <strong>Product Name:</strong> <span class="float-end">{{ $deposit->shareAccount->shareProduct->share_name ?? 'N/A' }}</span>
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
        // Handle delete button clicks
        $('.delete-btn').on('click', function(e) {
            e.preventDefault();
            
            var depositId = $(this).data('id');
            var depositName = $(this).data('name');
            
            Swal.fire({
                title: 'Are you sure?',
                text: `You want to delete ${depositName}? This action cannot be undone!`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ route("shares.deposits.destroy", ":id") }}'.replace(':id', depositId),
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            _method: 'DELETE'
                        },
                        beforeSend: function() {
                            Swal.fire({
                                title: 'Deleting...',
                                text: 'Please wait while we delete the share deposit.',
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
                                window.location.href = '{{ route("shares.deposits.index") }}';
                            });
                        },
                        error: function(xhr) {
                            console.error('Delete Error:', xhr.responseText);
                            Swal.fire({
                                title: 'Error!',
                                text: 'Failed to delete share deposit. ' + (xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : ''),
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

