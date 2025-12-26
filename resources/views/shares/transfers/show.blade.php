@extends('layouts.main')

@section('title', 'View Share Transfer')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Shares Management', 'url' => route('shares.management'), 'icon' => 'bx bx-bar-chart-square'],
            ['label' => 'Share Transfers', 'url' => route('shares.transfers.index'), 'icon' => 'bx bx-transfer'],
            ['label' => 'View', 'url' => '#', 'icon' => 'bx bx-show']
        ]" />

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 text-uppercase">Share Transfer Details</h6>
            <div>
                <a href="{{ route('shares.transfers.edit', \Vinkla\Hashids\Facades\Hashids::encode($transfer->id)) }}" class="btn btn-warning">
                    <i class="bx bx-edit me-1"></i> Edit
                </a>
                <a href="{{ route('shares.transfers.index') }}" class="btn btn-success">
                    <i class="bx bx-list-ul me-1"></i> Back to List
                </a>
            </div>
        </div>
        <hr />

        <div class="row">
            <!-- Left Column - Transfer Details -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0"><i class="bx bx-info-circle me-2"></i>Transfer Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Transfer Date</label>
                                <p>{{ $transfer->transfer_date ? $transfer->transfer_date->format('Y-m-d') : 'N/A' }}</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Status</label>
                                <p>
                                    @if($transfer->status === 'approved')
                                        <span class="badge bg-success">Approved</span>
                                    @elseif($transfer->status === 'pending')
                                        <span class="badge bg-warning">Pending</span>
                                    @else
                                        <span class="badge bg-danger">Rejected</span>
                                    @endif
                                </p>
                            </div>
                        </div>

                        <hr>

                        <h6 class="text-primary mb-3">From Account (Source)</h6>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Account Number</label>
                                <p>{{ $transfer->fromAccount->account_number ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Customer Name</label>
                                <p>{{ $transfer->fromAccount->customer->name ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Customer Number</label>
                                <p>{{ $transfer->fromAccount->customer->customerNo ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Share Product</label>
                                <p>{{ $transfer->fromAccount->shareProduct->share_name ?? 'N/A' }}</p>
                            </div>
                        </div>

                        <hr>

                        <h6 class="text-success mb-3">To Account (Destination)</h6>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Account Number</label>
                                <p>{{ $transfer->toAccount->account_number ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Customer Name</label>
                                <p>{{ $transfer->toAccount->customer->name ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Customer Number</label>
                                <p>{{ $transfer->toAccount->customer->customerNo ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Share Product</label>
                                <p>{{ $transfer->toAccount->shareProduct->share_name ?? 'N/A' }}</p>
                            </div>
                        </div>

                        <hr>

                        <h6 class="text-info mb-3">Transfer Amounts</h6>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Number of Shares</label>
                                <p>{{ number_format($transfer->number_of_shares, 4) }}</p>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Transfer Amount</label>
                                <p>{{ number_format($transfer->transfer_amount, 2) }}</p>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Transfer Fee</label>
                                <p>{{ number_format($transfer->transfer_fee ?? 0, 2) }}</p>
                            </div>
                        </div>

                        @if($transfer->bankAccount)
                        <hr>
                        <h6 class="text-warning mb-3">Fee Payment</h6>
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="form-label fw-bold">Bank Account</label>
                                <p>{{ $transfer->bankAccount->name }} ({{ $transfer->bankAccount->account_number }})</p>
                            </div>
                        </div>
                        @endif

                        @if($transfer->transaction_reference)
                        <hr>
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="form-label fw-bold">Transaction Reference</label>
                                <p>{{ $transfer->transaction_reference }}</p>
                            </div>
                        </div>
                        @endif

                        @if($transfer->journalReference)
                        <hr>
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="form-label fw-bold">Journal Reference</label>
                                <p>{{ $transfer->journalReference->name }} ({{ $transfer->journalReference->reference }})</p>
                            </div>
                        </div>
                        @endif

                        @if($transfer->notes)
                        <hr>
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="form-label fw-bold">Notes</label>
                                <p>{{ $transfer->notes }}</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Right Column - Additional Info -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <h6 class="mb-0"><i class="bx bx-info-circle me-2"></i>Additional Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Branch</label>
                            <p>{{ $transfer->branch->name ?? 'N/A' }}</p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Company</label>
                            <p>{{ $transfer->company->name ?? 'N/A' }}</p>
                        </div>

                        <hr>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Created By</label>
                            <p>{{ $transfer->createdBy->name ?? 'N/A' }}</p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Created At</label>
                            <p>{{ $transfer->created_at ? $transfer->created_at->format('Y-m-d H:i:s') : 'N/A' }}</p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Updated By</label>
                            <p>{{ $transfer->updatedBy->name ?? 'N/A' }}</p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Updated At</label>
                            <p>{{ $transfer->updated_at ? $transfer->updated_at->format('Y-m-d H:i:s') : 'N/A' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Delete Button -->
                <div class="card mt-3">
                    <div class="card-body">
                        <button type="button" class="btn btn-danger w-100 delete-btn" 
                                data-id="{{ \Vinkla\Hashids\Facades\Hashids::encode($transfer->id) }}"
                                data-name="Transfer #{{ $transfer->id }}">
                            <i class="bx bx-trash me-1"></i> Delete Transfer
                        </button>
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
        // Handle delete button click
        $('.delete-btn').on('click', function(e) {
            e.preventDefault();
            
            var transferId = $(this).data('id');
            var transferName = $(this).data('name');

            Swal.fire({
                title: 'Are you sure?',
                text: `You want to delete ${transferName}? This action cannot be undone!`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ route("shares.transfers.destroy", ":id") }}'.replace(':id', transferId),
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            _method: 'DELETE'
                        },
                        beforeSend: function() {
                            Swal.fire({
                                title: 'Deleting...',
                                text: 'Please wait while we delete the share transfer.',
                                allowOutsideClick: false,
                                allowEscapeKey: false,
                                showConfirmButton: false,
                                didOpen: () => { 
                                    Swal.showLoading(); 
                                }
                            });
                        },
                        success: function(response) {
                            Swal.fire({
                                title: 'Deleted!',
                                text: response.message || 'Share transfer has been deleted successfully.',
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                window.location.href = '{{ route("shares.transfers.index") }}';
                            });
                        },
                        error: function(xhr) {
                            console.error('Delete Error:', xhr.responseText);
                            
                            let errorMessage = 'Failed to delete share transfer.';
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

