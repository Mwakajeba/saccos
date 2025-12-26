@extends('layouts.main')

@section('title', 'Edit Share Account')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Shares Management', 'url' => route('shares.management'), 'icon' => 'bx bx-bar-chart-square'],
            ['label' => 'Share Accounts', 'url' => route('shares.accounts.index'), 'icon' => 'bx bx-wallet'],
            ['label' => 'Edit', 'url' => '#', 'icon' => 'bx bx-edit']
        ]" />

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 text-uppercase text-warning">EDIT SHARE ACCOUNT</h6>
            <a href="{{ route('shares.accounts.index') }}" class="btn btn-success">
                <i class="bx bx-list-ul me-1"></i> Share accounts list
            </a>
        </div>
        <hr />

        <div class="row">
            <!-- Left Column - Form -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        @if($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bx bx-error-circle me-2"></i>
                                Please fix the following errors:
                                <ul class="mb-0 mt-2">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <form action="{{ route('shares.accounts.update', Vinkla\Hashids\Facades\Hashids::encode($shareAccount->id)) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <!-- Opening date and Notes -->
                            <div class="row mb-3">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Opening date <span class="text-danger">*</span></label>
                                    <input type="date" name="opening_date" 
                                           class="form-control @error('opening_date') is-invalid @enderror"
                                           value="{{ old('opening_date', $shareAccount->opening_date ? $shareAccount->opening_date->format('Y-m-d') : '') }}" required>
                                    @error('opening_date') 
                                        <div class="invalid-feedback">{{ $message }}</div> 
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Notes</label>
                                    <input type="text" name="notes" 
                                           class="form-control @error('notes') is-invalid @enderror"
                                           value="{{ old('notes', $shareAccount->notes) }}" 
                                           placeholder="Optional notes">
                                    @error('notes') 
                                        <div class="invalid-feedback">{{ $message }}</div> 
                                    @enderror
                                </div>
                            </div>

                            <!-- Member name and Share product -->
                            <div class="row mb-3">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Member name <span class="text-danger">*</span></label>
                                    <select name="customer_id" 
                                            class="form-select customer-select @error('customer_id') is-invalid @enderror" required>
                                        <option value="">Select member</option>
                                        @foreach($customers as $customer)
                                            <option value="{{ $customer->id }}" 
                                                {{ old('customer_id', $shareAccount->customer_id) == $customer->id ? 'selected' : '' }}>
                                                {{ $customer->name }} ({{ $customer->customerNo }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('customer_id') 
                                        <div class="invalid-feedback">{{ $message }}</div> 
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Share product <span class="text-danger">*</span></label>
                                    <select name="share_product_id" 
                                            class="form-select share-product-select @error('share_product_id') is-invalid @enderror" required>
                                        <option value="">Select account</option>
                                        @foreach($shareProducts as $product)
                                            <option value="{{ $product->id }}" 
                                                {{ old('share_product_id', $shareAccount->share_product_id) == $product->id ? 'selected' : '' }}
                                                data-nominal-price="{{ $product->nominal_price }}">
                                                {{ $product->share_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('share_product_id') 
                                        <div class="invalid-feedback">{{ $message }}</div> 
                                    @enderror
                                </div>
                            </div>

                            <!-- Status -->
                            <div class="row mb-3">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Status <span class="text-danger">*</span></label>
                                    <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                        <option value="active" {{ old('status', $shareAccount->status) == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ old('status', $shareAccount->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                        <option value="closed" {{ old('status', $shareAccount->status) == 'closed' ? 'selected' : '' }}>Closed</option>
                                    </select>
                                    @error('status') 
                                        <div class="invalid-feedback">{{ $message }}</div> 
                                    @enderror
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="d-flex justify-content-end">
                                        <button type="submit" class="btn btn-warning">
                                            <i class="bx bx-save me-1"></i> Update
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Right Column - Information -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0"><i class="bx bx-info-circle me-2"></i>Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <h6 class="text-primary">Account Details</h6>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">
                                    <strong>Account Number:</strong><br>
                                    <span class="text-muted">{{ $shareAccount->account_number }}</span>
                                </li>
                                <li class="mb-2">
                                    <strong>Current Balance:</strong><br>
                                    <span class="text-muted">{{ number_format($shareAccount->share_balance ?? 0, 2) }}</span>
                                </li>
                                <li class="mb-2">
                                    <strong>Nominal Value:</strong><br>
                                    <span class="text-muted">{{ number_format($shareAccount->nominal_value ?? 0, 2) }}</span>
                                </li>
                                <li class="mb-2">
                                    <strong>Opening Date:</strong><br>
                                    <span class="text-muted">{{ $shareAccount->opening_date ? $shareAccount->opening_date->format('d M, Y') : 'N/A' }}</span>
                                </li>
                                <li class="mb-2">
                                    <strong>Status:</strong><br>
                                    @php
                                        $badgeClass = match ($shareAccount->status) {
                                            'active' => 'badge bg-success',
                                            'inactive' => 'badge bg-secondary',
                                            'closed' => 'badge bg-danger',
                                            default => 'badge bg-info',
                                        };
                                    @endphp
                                    <span class="{{ $badgeClass }}">{{ ucfirst($shareAccount->status) }}</span>
                                </li>
                            </ul>
                        </div>

                        <hr>

                        <div class="mb-3">
                            <h6 class="text-primary">Quick Stats</h6>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Total Members:</span>
                                <strong>{{ $customers->count() }}</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Active Products:</span>
                                <strong>{{ $shareProducts->count() }}</strong>
                            </div>
                        </div>

                        <hr>

                        <div class="alert alert-info mb-0">
                            <small>
                                <i class="bx bx-info-circle me-1"></i>
                                <strong>Note:</strong> Changing the share product will update the nominal value automatically.
                            </small>
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
        // Initialize Select2
        $('.customer-select, .share-product-select').select2({
            placeholder: 'Select an option',
            allowClear: true,
            width: '100%',
            theme: 'bootstrap-5'
        });
    });
</script>
@endpush
