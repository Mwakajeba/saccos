@php
    use Vinkla\Hashids\Facades\Hashids;
@endphp

@extends('layouts.main')

@section('title', 'Share Product Details')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <x-breadcrumbs-with-icons :links="[
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Shares Management', 'url' => route('shares.management'), 'icon' => 'bx bx-bar-chart-square'],
                ['label' => 'Share Products', 'url' => route('shares.products.index'), 'icon' => 'bx bx-package'],
                ['label' => 'Product Details', 'url' => '#', 'icon' => 'bx bx-info-circle']
            ]" />
            <div class="d-flex gap-2">
                @if($shareProduct->is_active)
                    <a href="{{ route('shares.products.toggle-status', Hashids::encode($shareProduct->id)) }}" 
                       class="btn btn-warning" 
                       onclick="event.preventDefault(); toggleStatus('{{ Hashids::encode($shareProduct->id) }}', '{{ $shareProduct->share_name }}', 'active');">
                        <i class="bx bx-pause-circle me-1"></i> Deactivate
                    </a>
                @else
                    <a href="{{ route('shares.products.toggle-status', Hashids::encode($shareProduct->id)) }}" 
                       class="btn btn-success" 
                       onclick="event.preventDefault(); toggleStatus('{{ Hashids::encode($shareProduct->id) }}', '{{ $shareProduct->share_name }}', 'inactive');">
                        <i class="bx bx-play-circle me-1"></i> Activate
                    </a>
                @endif
                <a href="{{ route('shares.products.edit', Hashids::encode($shareProduct->id)) }}" class="btn btn-primary">
                    <i class="bx bx-edit me-1"></i> Edit Product
                </a>
                <a href="{{ route('shares.products.index') }}" class="btn btn-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Back to List
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Product Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-4">
                        <h2 class="text-primary mb-2">{{ $shareProduct->share_name }}</h2>
                        <p class="text-muted mb-0">
                            @if($shareProduct->is_active)
                                <span class="badge bg-success fs-6">Active</span>
                            @else
                                <span class="badge bg-danger fs-6">Inactive</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Product Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="bx bx-dollar-circle text-primary" style="font-size: 2rem;"></i>
                        </div>
                        <h5 class="card-title text-muted mb-1">Nominal Price</h5>
                        <h4 class="text-primary mb-0">{{ number_format($shareProduct->nominal_price, 2) }}</h4>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="bx bx-purchase-tag text-success" style="font-size: 2rem;"></i>
                        </div>
                        <h5 class="card-title text-muted mb-1">Required Share</h5>
                        <h4 class="text-success mb-0">{{ number_format($shareProduct->required_share, 2) }}</h4>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="bx bx-percentage text-warning" style="font-size: 2rem;"></i>
                        </div>
                        <h5 class="card-title text-muted mb-1">Dividend Rate</h5>
                        <h4 class="text-warning mb-0">
                            {{ $shareProduct->dividend_rate ? number_format($shareProduct->dividend_rate, 2) . '%' : 'N/A' }}
                        </h4>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="bx bx-time-five text-info" style="font-size: 2rem;"></i>
                        </div>
                        <h5 class="card-title text-muted mb-1">Lockin Period</h5>
                        <h4 class="text-info mb-0">
                            {{ $shareProduct->lockin_period_frequency }} {{ $shareProduct->lockin_period_frequency_type }}
                        </h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Information -->
        <div class="row">
            <!-- Basic Information -->
            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0"><i class="bx bx-info-circle me-2"></i>Basic Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-sm-5"><strong>Share Name:</strong></div>
                            <div class="col-sm-7">{{ $shareProduct->share_name }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-5"><strong>Status:</strong></div>
                            <div class="col-sm-7">
                                @if($shareProduct->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-5"><strong>Required Share:</strong></div>
                            <div class="col-sm-7">{{ number_format($shareProduct->required_share, 2) }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-5"><strong>Nominal Price:</strong></div>
                            <div class="col-sm-7">{{ number_format($shareProduct->nominal_price, 2) }}</div>
                        </div>
                        @if($shareProduct->description)
                        <div class="row mb-3">
                            <div class="col-sm-5"><strong>Description:</strong></div>
                            <div class="col-sm-7">{{ $shareProduct->description }}</div>
                        </div>
                        @endif
                        @if($shareProduct->hrms_code)
                        <div class="row mb-3">
                            <div class="col-sm-5"><strong>HRMS Code:</strong></div>
                            <div class="col-sm-7">{{ $shareProduct->hrms_code }}</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Purchase Limits -->
            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0"><i class="bx bx-purchase-tag me-2"></i>Purchase Limits</h6>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-sm-6"><strong>Minimum Purchase:</strong></div>
                            <div class="col-sm-6">{{ $shareProduct->minimum_purchase_amount ? number_format($shareProduct->minimum_purchase_amount, 2) : 'N/A' }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-6"><strong>Maximum Purchase:</strong></div>
                            <div class="col-sm-6">{{ $shareProduct->maximum_purchase_amount ? number_format($shareProduct->maximum_purchase_amount, 2) : 'N/A' }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-6"><strong>Max Shares Per Member:</strong></div>
                            <div class="col-sm-6">{{ $shareProduct->maximum_shares_per_member ? number_format($shareProduct->maximum_shares_per_member, 2) : 'N/A' }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-6"><strong>Min Shares for Membership:</strong></div>
                            <div class="col-sm-6">{{ $shareProduct->minimum_shares_for_membership ? number_format($shareProduct->minimum_shares_for_membership, 2) : 'N/A' }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-6"><strong>Purchase Increment:</strong></div>
                            <div class="col-sm-6">{{ $shareProduct->share_purchase_increment ? number_format($shareProduct->share_purchase_increment, 2) : 'N/A' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Period Settings -->
            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0"><i class="bx bx-time me-2"></i>Period Settings</h6>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-sm-6"><strong>Minimum Active Period:</strong></div>
                            <div class="col-sm-6">{{ $shareProduct->minimum_active_period }} {{ $shareProduct->minimum_active_period_type }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-6"><strong>Lockin Period:</strong></div>
                            <div class="col-sm-6">{{ $shareProduct->lockin_period_frequency }} {{ $shareProduct->lockin_period_frequency_type }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-6"><strong>Allow Dividends (Inactive):</strong></div>
                            <div class="col-sm-6">
                                @if($shareProduct->allow_dividends_for_inactive_member)
                                    <span class="badge bg-success">Yes</span>
                                @else
                                    <span class="badge bg-danger">No</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dividend Management -->
            @if($shareProduct->dividend_rate || $shareProduct->dividend_calculation_method)
            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-warning text-white">
                        <h6 class="mb-0"><i class="bx bx-money me-2"></i>Dividend Management</h6>
                    </div>
                    <div class="card-body">
                        @if($shareProduct->dividend_rate)
                        <div class="row mb-3">
                            <div class="col-sm-6"><strong>Dividend Rate:</strong></div>
                            <div class="col-sm-6">{{ number_format($shareProduct->dividend_rate, 2) }}%</div>
                        </div>
                        @endif
                        @if($shareProduct->dividend_calculation_method)
                        <div class="row mb-3">
                            <div class="col-sm-6"><strong>Calculation Method:</strong></div>
                            <div class="col-sm-6">{{ ucwords(str_replace('_', ' ', $shareProduct->dividend_calculation_method)) }}</div>
                        </div>
                        @endif
                        @if($shareProduct->dividend_payment_frequency)
                        <div class="row mb-3">
                            <div class="col-sm-6"><strong>Payment Frequency:</strong></div>
                            <div class="col-sm-6">{{ $shareProduct->dividend_payment_frequency }}</div>
                        </div>
                        @endif
                        @if($shareProduct->dividend_payment_month)
                        <div class="row mb-3">
                            <div class="col-sm-6"><strong>Payment Month:</strong></div>
                            <div class="col-sm-6">{{ date('F', mktime(0, 0, 0, $shareProduct->dividend_payment_month, 1)) }}</div>
                        </div>
                        @endif
                        @if($shareProduct->dividend_payment_day)
                        <div class="row mb-3">
                            <div class="col-sm-6"><strong>Payment Day:</strong></div>
                            <div class="col-sm-6">{{ $shareProduct->dividend_payment_day }}</div>
                        </div>
                        @endif
                        @if($shareProduct->minimum_balance_for_dividend)
                        <div class="row mb-3">
                            <div class="col-sm-6"><strong>Min Balance for Dividend:</strong></div>
                            <div class="col-sm-6">{{ number_format($shareProduct->minimum_balance_for_dividend, 2) }}</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <!-- Certificate Settings -->
            @if($shareProduct->certificate_number_prefix || $shareProduct->certificate_number_format)
            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-secondary text-white">
                        <h6 class="mb-0"><i class="bx bx-certificate me-2"></i>Certificate Settings</h6>
                    </div>
                    <div class="card-body">
                        @if($shareProduct->certificate_number_prefix)
                        <div class="row mb-3">
                            <div class="col-sm-6"><strong>Certificate Prefix:</strong></div>
                            <div class="col-sm-6">{{ $shareProduct->certificate_number_prefix }}</div>
                        </div>
                        @endif
                        @if($shareProduct->certificate_number_format)
                        <div class="row mb-3">
                            <div class="col-sm-6"><strong>Certificate Format:</strong></div>
                            <div class="col-sm-6">{{ $shareProduct->certificate_number_format }}</div>
                        </div>
                        @endif
                        <div class="row mb-3">
                            <div class="col-sm-6"><strong>Auto Generate:</strong></div>
                            <div class="col-sm-6">
                                @if($shareProduct->auto_generate_certificate)
                                    <span class="badge bg-success">Yes</span>
                                @else
                                    <span class="badge bg-danger">No</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Subscription & Availability -->
            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-dark text-white">
                        <h6 class="mb-0"><i class="bx bx-calendar me-2"></i>Subscription & Availability</h6>
                    </div>
                    <div class="card-body">
                        @if($shareProduct->opening_date)
                        <div class="row mb-3">
                            <div class="col-sm-6"><strong>Opening Date:</strong></div>
                            <div class="col-sm-6">{{ $shareProduct->opening_date->format('Y-m-d') }}</div>
                        </div>
                        @endif
                        @if($shareProduct->closing_date)
                        <div class="row mb-3">
                            <div class="col-sm-6"><strong>Closing Date:</strong></div>
                            <div class="col-sm-6">{{ $shareProduct->closing_date->format('Y-m-d') }}</div>
                        </div>
                        @endif
                        @if($shareProduct->maximum_total_shares)
                        <div class="row mb-3">
                            <div class="col-sm-6"><strong>Maximum Total Shares:</strong></div>
                            <div class="col-sm-6">{{ number_format($shareProduct->maximum_total_shares, 2) }}</div>
                        </div>
                        @endif
                        <div class="row mb-3">
                            <div class="col-sm-6"><strong>Allow New Subscriptions:</strong></div>
                            <div class="col-sm-6">
                                @if($shareProduct->allow_new_subscriptions)
                                    <span class="badge bg-success">Yes</span>
                                @else
                                    <span class="badge bg-danger">No</span>
                                @endif
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-6"><strong>Allow Additional Purchases:</strong></div>
                            <div class="col-sm-6">
                                @if($shareProduct->allow_additional_purchases)
                                    <span class="badge bg-success">Yes</span>
                                @else
                                    <span class="badge bg-danger">No</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charges -->
            @if($shareProduct->has_charges)
            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-danger text-white">
                        <h6 class="mb-0"><i class="bx bx-money me-2"></i>Charges</h6>
                    </div>
                    <div class="card-body">
                        @if($shareProduct->charge)
                        <div class="row mb-3">
                            <div class="col-sm-6"><strong>Charge:</strong></div>
                            <div class="col-sm-6">{{ $shareProduct->charge->name }}</div>
                        </div>
                        @endif
                        @if($shareProduct->charge_type)
                        <div class="row mb-3">
                            <div class="col-sm-6"><strong>Charge Type:</strong></div>
                            <div class="col-sm-6">{{ ucfirst($shareProduct->charge_type) }}</div>
                        </div>
                        @endif
                        @if($shareProduct->charge_amount)
                        <div class="row mb-3">
                            <div class="col-sm-6"><strong>Charge Amount:</strong></div>
                            <div class="col-sm-6">{{ number_format($shareProduct->charge_amount, 2) }}</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <!-- Transfer & Withdrawal Rules -->
            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-purple text-white">
                        <h6 class="mb-0"><i class="bx bx-transfer me-2"></i>Transfer & Withdrawal Rules</h6>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-sm-6"><strong>Allow Share Transfers:</strong></div>
                            <div class="col-sm-6">
                                @if($shareProduct->allow_share_transfers)
                                    <span class="badge bg-success">Yes</span>
                                    @if($shareProduct->transfer_fee)
                                        <br><small>Fee: {{ number_format($shareProduct->transfer_fee, 2) }} ({{ $shareProduct->transfer_fee_type }})</small>
                                    @endif
                                @else
                                    <span class="badge bg-danger">No</span>
                                @endif
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-6"><strong>Allow Share Withdrawals:</strong></div>
                            <div class="col-sm-6">
                                @if($shareProduct->allow_share_withdrawals)
                                    <span class="badge bg-success">Yes</span>
                                    @if($shareProduct->withdrawal_fee)
                                        <br><small>Fee: {{ number_format($shareProduct->withdrawal_fee, 2) }} ({{ $shareProduct->withdrawal_fee_type }})</small>
                                    @endif
                                    @if($shareProduct->withdrawal_notice_period)
                                        <br><small>Notice: {{ $shareProduct->withdrawal_notice_period }} {{ $shareProduct->withdrawal_notice_period_type }}</small>
                                    @endif
                                    @if($shareProduct->allow_partial_withdrawal)
                                        <br><small><span class="badge bg-info">Partial withdrawal allowed</span></small>
                                    @endif
                                @else
                                    <span class="badge bg-danger">No</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chart Accounts -->
            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0"><i class="bx bx-chart me-2"></i>Chart Accounts</h6>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-sm-6"><strong>Journal Reference:</strong></div>
                            <div class="col-sm-6">
                                {{ $shareProduct->journalReferenceAccount->account_code ?? 'N/A' }} - 
                                {{ $shareProduct->journalReferenceAccount->account_name ?? 'N/A' }}
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-6"><strong>Liability Account:</strong></div>
                            <div class="col-sm-6">
                                {{ $shareProduct->liabilityAccount->account_code ?? 'N/A' }} - 
                                {{ $shareProduct->liabilityAccount->account_name ?? 'N/A' }}
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-6"><strong>Income Account:</strong></div>
                            <div class="col-sm-6">
                                {{ $shareProduct->incomeAccount->account_code ?? 'N/A' }} - 
                                {{ $shareProduct->incomeAccount->account_name ?? 'N/A' }}
                            </div>
                        </div>
                        @if($shareProduct->shareCapitalAccount)
                        <div class="row mb-3">
                            <div class="col-sm-6"><strong>Share Capital Account:</strong></div>
                            <div class="col-sm-6">
                                {{ $shareProduct->shareCapitalAccount->account_code }} - 
                                {{ $shareProduct->shareCapitalAccount->account_name }}
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .bg-purple {
        background-color: #6f42c1 !important;
    }
    .card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1) !important;
    }
</style>
@endpush

@push('scripts')
<script>
    function toggleStatus(productId, productName, currentStatus) {
        var newStatus = currentStatus === 'active' ? 'inactive' : 'active';
        var actionText = currentStatus === 'active' ? 'deactivate' : 'activate';
        var confirmColor = currentStatus === 'active' ? '#ffc107' : '#28a745';
        
        Swal.fire({
            title: `${actionText.charAt(0).toUpperCase() + actionText.slice(1)} Share Product?`,
            text: `Are you sure you want to ${actionText} "${productName}"?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: confirmColor,
            cancelButtonColor: '#6c757d',
            confirmButtonText: `Yes, ${actionText} it!`,
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Create a form to submit the toggle status request
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("shares.products.toggle-status", ":id") }}'.replace(':id', productId);
                
                var csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';
                
                var methodField = document.createElement('input');
                methodField.type = 'hidden';
                methodField.name = '_method';
                methodField.value = 'PATCH';
                
                form.appendChild(csrfToken);
                form.appendChild(methodField);
                document.body.appendChild(form);
                
                form.submit();
            }
        });
    }
</script>
@endpush

