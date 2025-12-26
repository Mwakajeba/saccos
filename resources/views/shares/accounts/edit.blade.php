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
            <h6 class="mb-0 text-uppercase">EDIT SHARE ACCOUNT</h6>
            <a href="{{ route('shares.accounts.index') }}" class="btn btn-success">
                <i class="bx bx-list-ul me-1"></i> Share accounts list
            </a>
        </div>
        <hr />

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

                    <div class="row">
                        <!-- Member name -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Member name <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-dark text-white">
                                    <i class="bx bx-user"></i>
                                </span>
                                <select name="customer_id" 
                                        class="form-select select2-single @error('customer_id') is-invalid @enderror" required>
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
                        </div>

                        <!-- Share product -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Share product <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-dark text-white">
                                    <i class="bx bx-box"></i>
                                </span>
                                <select name="share_product_id" 
                                        class="form-select select2-single @error('share_product_id') is-invalid @enderror" required>
                                    <option value="">Select account</option>
                                    @foreach($shareProducts as $product)
                                        <option value="{{ $product->id }}" 
                                            {{ old('share_product_id', $shareAccount->share_product_id) == $product->id ? 'selected' : '' }}>
                                            {{ $product->share_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('share_product_id') 
                                    <div class="invalid-feedback">{{ $message }}</div> 
                                @enderror
                            </div>
                        </div>

                        <!-- Account Number (readonly) -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Account Number</label>
                            <input type="text" class="form-control" value="{{ $shareAccount->account_number }}" readonly>
                        </div>

                        <!-- Opening date -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Opening date <span class="text-danger">*</span></label>
                            <input type="date" name="opening_date" 
                                   class="form-control @error('opening_date') is-invalid @enderror"
                                   value="{{ old('opening_date', $shareAccount->opening_date ? $shareAccount->opening_date->format('Y-m-d') : '') }}" required>
                            @error('opening_date') 
                                <div class="invalid-feedback">{{ $message }}</div> 
                            @enderror
                        </div>

                        <!-- Status -->
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

                        <!-- Notes -->
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

                    <!-- Submit Button -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-warning">
                                    <i class="bx bx-save me-1"></i> Save
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize Select2
        $('.select2-single').select2({
            placeholder: 'Select an option',
            allowClear: true,
            width: '100%',
            theme: 'bootstrap-5'
        });
    });
</script>
@endpush

