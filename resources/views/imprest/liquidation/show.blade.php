@extends('layouts.main')

@section('title', 'Liquidation Details')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Imprest Management', 'url' => route('imprest.index'), 'icon' => 'bx bx-money'],
            ['label' => 'Liquidation Details', 'url' => '#', 'icon' => 'bx bx-receipt']
        ]" />

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="mb-0 text-primary">Liquidation {{ $liquidation->liquidation_number }}</h5>
            <span class="{{ $liquidation->getStatusBadgeClass() }}">{{ $liquidation->getStatusLabel() }}</span>
        </div>

        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Liquidation Details</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <strong>Liquidation Number:</strong><br>
                        {{ $liquidation->liquidation_number }}
                    </div>
                    <div class="col-md-3 mb-3">
                        <strong>Imprest Request:</strong><br>
                        <a href="{{ route('imprest.requests.show', $liquidation->imprest_request_id) }}">
                            {{ $liquidation->imprestRequest->request_number }}
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <strong>Liquidation Date:</strong><br>
                        {{ $liquidation->liquidation_date->format('Y-m-d') }}
                    </div>
                    <div class="col-md-3 mb-3">
                        <strong>Submitted By:</strong><br>
                        {{ $liquidation->submitter->name ?? 'N/A' }}
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3 mb-3">
                        <strong>Total Spent:</strong><br>
                        {{ number_format($liquidation->total_spent, 2) }}
                    </div>
                    <div class="col-md-3 mb-3">
                        <strong>Balance Returned:</strong><br>
                        {{ number_format($liquidation->balance_returned, 2) }}
                    </div>
                    @if($liquidation->verified_by)
                    <div class="col-md-3 mb-3">
                        <strong>Verified By:</strong><br>
                        {{ $liquidation->verifier->name ?? 'N/A' }} ({{ $liquidation->verified_at->format('Y-m-d H:i') }})
                    </div>
                    @endif
                    @if($liquidation->approved_by)
                    <div class="col-md-3 mb-3">
                        <strong>Approved By:</strong><br>
                        {{ $liquidation->approver->name ?? 'N/A' }} ({{ $liquidation->approved_at->format('Y-m-d H:i') }})
                    </div>
                    @endif
                </div>

                @if($liquidation->liquidation_notes)
                <div class="row">
                    <div class="col-12">
                        <strong>Notes:</strong><br>
                        {{ $liquidation->liquidation_notes }}
                    </div>
                </div>
                @endif
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h6 class="mb-0">Expense Items</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Description</th>
                                <th>Account</th>
                                <th>Date</th>
                                <th>Receipt #</th>
                                <th>Supplier</th>
                                <th class="text-end">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($liquidation->liquidationItems as $item)
                            <tr>
                                <td>{{ $item->expense_category }}</td>
                                <td>{{ $item->description }}</td>
                                <td>{{ $item->chartAccount->account_code ?? '' }} - {{ $item->chartAccount->account_name ?? '' }}</td>
                                <td>{{ $item->expense_date->format('Y-m-d') }}</td>
                                <td>{{ $item->receipt_number ?? '-' }}</td>
                                <td>{{ $item->supplier_name ?? '-' }}</td>
                                <td class="text-end">{{ number_format($item->amount, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="6" class="text-end"><strong>Total:</strong></td>
                                <td class="text-end"><strong>{{ number_format($liquidation->total_spent, 2) }}</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        @if($liquidation->canBeVerified() || $liquidation->canBeApproved())
        <div class="card mt-4">
            <div class="card-header">
                <h6 class="mb-0">Actions</h6>
            </div>
            <div class="card-body">
                @if($liquidation->canBeVerified())
                <button type="button" class="btn btn-success me-2" onclick="verifyLiquidation('verify')">
                    <i class="bx bx-check me-1"></i> Verify
                </button>
                <button type="button" class="btn btn-danger" onclick="verifyLiquidation('reject')">
                    <i class="bx bx-x me-1"></i> Reject
                </button>
                @endif

                @if($liquidation->canBeApproved())
                <button type="button" class="btn btn-success me-2" onclick="approveLiquidation('approve')">
                    <i class="bx bx-check me-1"></i> Approve
                </button>
                <button type="button" class="btn btn-danger" onclick="approveLiquidation('reject')">
                    <i class="bx bx-x me-1"></i> Reject
                </button>
                @endif
            </div>
        </div>
        @endif

        <div class="mt-4">
            <a href="{{ route('imprest.requests.show', $liquidation->imprest_request_id) }}" class="btn btn-secondary">
                <i class="bx bx-arrow-back me-1"></i> Back to Request
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function verifyLiquidation(action) {
    Swal.fire({
        title: action === 'verify' ? 'Verify Liquidation' : 'Reject Liquidation',
        input: 'textarea',
        inputLabel: 'Comments',
        inputPlaceholder: 'Enter your comments...',
        showCancelButton: true,
        confirmButtonText: action === 'verify' ? 'Verify' : 'Reject',
        confirmButtonColor: action === 'verify' ? '#28a745' : '#dc3545',
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '{{ route("imprest.liquidation.verify", $liquidation->id) }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    action: action,
                    verification_notes: result.value
                },
                success: function(response) {
                    Swal.fire('Success', response.success, 'success').then(() => {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    Swal.fire('Error', xhr.responseJSON?.error || 'An error occurred', 'error');
                }
            });
        }
    });
}

function approveLiquidation(action) {
    Swal.fire({
        title: action === 'approve' ? 'Approve Liquidation' : 'Reject Liquidation',
        input: 'textarea',
        inputLabel: 'Comments',
        inputPlaceholder: 'Enter your comments...',
        showCancelButton: true,
        confirmButtonText: action === 'approve' ? 'Approve' : 'Reject',
        confirmButtonColor: action === 'approve' ? '#28a745' : '#dc3545',
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '{{ route("imprest.liquidation.approve", $liquidation->id) }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    action: action,
                    approval_notes: result.value
                },
                success: function(response) {
                    Swal.fire('Success', response.success, 'success').then(() => {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    Swal.fire('Error', xhr.responseJSON?.error || 'An error occurred', 'error');
                }
            });
        }
    });
}
</script>
@endpush
