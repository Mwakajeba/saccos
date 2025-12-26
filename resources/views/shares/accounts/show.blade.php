@extends('layouts.main')

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

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h6 class="mb-0 text-uppercase text-info">SHARE ACCOUNT DETAILS</h6>
            <div class="d-flex gap-2">
                <a href="{{ route('shares.accounts.edit', Vinkla\Hashids\Facades\Hashids::encode($shareAccount->id)) }}" class="btn btn-warning">
                    <i class="bx bx-edit me-1"></i> Edit
                </a>
                <button type="button" class="btn btn-danger delete-btn" 
                        data-id="{{ Vinkla\Hashids\Facades\Hashids::encode($shareAccount->id) }}" 
                        data-name="{{ $shareAccount->account_number }}">
                    <i class="bx bx-trash me-1"></i> Delete
                </button>
                <a href="{{ route('shares.accounts.index') }}" class="btn btn-success">
                    <i class="bx bx-list-ul me-1"></i> Back to List
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0"><i class="bx bx-wallet me-2"></i>Account Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Account Number:</strong></div>
                            <div class="col-sm-8"><span class="badge bg-dark">{{ $shareAccount->account_number }}</span></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Member Name:</strong></div>
                            <div class="col-sm-8">{{ $shareAccount->customer->name ?? 'N/A' }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Member Number:</strong></div>
                            <div class="col-sm-8">{{ $shareAccount->customer->customerNo ?? 'N/A' }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Share Product:</strong></div>
                            <div class="col-sm-8">{{ $shareAccount->shareProduct->share_name ?? 'N/A' }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Share Balance:</strong></div>
                            <div class="col-sm-8"><strong class="text-primary">{{ number_format($shareAccount->share_balance ?? 0, 2) }}</strong></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Nominal Value:</strong></div>
                            <div class="col-sm-8"><strong class="text-success">{{ number_format($shareAccount->nominal_value ?? 0, 2) }}</strong></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Opening Date:</strong></div>
                            <div class="col-sm-8">{{ $shareAccount->opening_date ? $shareAccount->opening_date->format('d M, Y') : 'N/A' }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Last Transaction Date:</strong></div>
                            <div class="col-sm-8">{{ $shareAccount->last_transaction_date ? $shareAccount->last_transaction_date->format('d M, Y') : 'N/A' }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Status:</strong></div>
                            <div class="col-sm-8">
                                @php
                                    $badgeClass = match ($shareAccount->status) {
                                        'active' => 'badge bg-success',
                                        'inactive' => 'badge bg-warning',
                                        'closed' => 'badge bg-danger',
                                        default => 'badge bg-secondary',
                                    };
                                @endphp
                                <span class="{{ $badgeClass }}">{{ ucfirst($shareAccount->status) }}</span>
                            </div>
                        </div>
                        @if($shareAccount->notes)
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Notes:</strong></div>
                            <div class="col-sm-8">{{ $shareAccount->notes }}</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0"><i class="bx bx-info-circle me-2"></i>Product Details</h6>
                    </div>
                    <div class="card-body">
                        @if($shareAccount->shareProduct)
                            <div class="mb-3">
                                <strong>Product Name:</strong><br>
                                <span class="text-muted">{{ $shareAccount->shareProduct->share_name }}</span>
                            </div>
                            <div class="mb-3">
                                <strong>Nominal Price:</strong><br>
                                <span class="text-muted">{{ number_format($shareAccount->shareProduct->nominal_price ?? 0, 2) }}</span>
                            </div>
                            <div class="mb-3">
                                <strong>Required Share:</strong><br>
                                <span class="text-muted">{{ number_format($shareAccount->shareProduct->required_share ?? 0, 2) }}</span>
                            </div>
                            @if($shareAccount->shareProduct->dividend_rate)
                            <div class="mb-3">
                                <strong>Dividend Rate:</strong><br>
                                <span class="text-muted">{{ number_format($shareAccount->shareProduct->dividend_rate * 100, 2) }}%</span>
                            </div>
                            @endif
                            <div class="mb-3">
                                <strong>Product Status:</strong><br>
                                @if($shareAccount->shareProduct->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </div>
                        @else
                            <p class="text-muted">No product information available.</p>
                        @endif
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
