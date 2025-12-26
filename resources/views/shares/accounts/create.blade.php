@extends('layouts.main')

@section('title', 'Create Share Account')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Shares Management', 'url' => route('shares.management'), 'icon' => 'bx bx-bar-chart-square'],
            ['label' => 'Share Accounts', 'url' => route('shares.accounts.index'), 'icon' => 'bx bx-wallet'],
            ['label' => 'Create', 'url' => '#', 'icon' => 'bx bx-plus']
        ]" />

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 text-uppercase text-success">Add share account</h6>
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

                        <form action="{{ route('shares.accounts.store') }}" method="POST" id="shareAccountForm">
                            @csrf

                            <!-- Opening date and Notes (only for first line) -->
                            <div class="row mb-3">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Opening date <span class="text-danger">*</span></label>
                                    <input type="date" name="lines[0][opening_date]" 
                                           class="form-control @error('lines.0.opening_date') is-invalid @enderror"
                                           value="{{ old('lines.0.opening_date', date('Y-m-d')) }}" required>
                                    @error('lines.0.opening_date') 
                                        <div class="invalid-feedback">{{ $message }}</div> 
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Notes</label>
                                    <input type="text" name="lines[0][notes]" 
                                           class="form-control @error('lines.0.notes') is-invalid @enderror"
                                           value="{{ old('lines.0.notes') }}" 
                                           placeholder="Optional notes">
                                    @error('lines.0.notes') 
                                        <div class="invalid-feedback">{{ $message }}</div> 
                                    @enderror
                                </div>
                            </div>

                            <!-- Form Lines Container -->
                            <div id="shareAccountLines">
                                <!-- Default Line -->
                                <div class="share-account-line mb-3 border-bottom pb-3" data-line-index="0">
                                    <div class="row">
                                        <!-- Member name -->
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Member name <span class="text-danger">*</span></label>
                                            <select name="lines[0][customer_id]" 
                                                    class="form-select customer-select @error('lines.0.customer_id') is-invalid @enderror" required>
                                                <option value="">Select member</option>
                                                @foreach($customers as $customer)
                                                    <option value="{{ $customer->id }}" 
                                                        {{ old('lines.0.customer_id') == $customer->id ? 'selected' : '' }}>
                                                        {{ $customer->name }} ({{ $customer->customerNo }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('lines.0.customer_id') 
                                                <div class="invalid-feedback">{{ $message }}</div> 
                                            @enderror
                                        </div>

                                        <!-- Share product -->
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Share product <span class="text-danger">*</span></label>
                                            <select name="lines[0][share_product_id]" 
                                                    class="form-select share-product-select @error('lines.0.share_product_id') is-invalid @enderror" required>
                                                <option value="">Select account</option>
                                                @foreach($shareProducts as $product)
                                                    <option value="{{ $product->id }}" 
                                                        {{ old('lines.0.share_product_id') == $product->id ? 'selected' : '' }}
                                                        data-nominal-price="{{ $product->nominal_price }}">
                                                        {{ $product->share_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('lines.0.share_product_id') 
                                                <div class="invalid-feedback">{{ $message }}</div> 
                                            @enderror
                                        </div>

                                        <!-- Remove Line Button (hidden for first line) -->
                                        <div class="col-12 text-end">
                                            <button type="button" class="btn btn-sm btn-danger remove-line-btn" style="display: none;">
                                                <i class="bx bx-trash me-1"></i> Remove Line
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Add Line Button -->
                            <div class="row mt-3">
                                <div class="col-12">
                                    <button type="button" class="btn btn-primary" id="addLineBtn">
                                        <i class="bx bx-plus me-1"></i> Add Line
                                    </button>
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

            <!-- Right Column - Information -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0"><i class="bx bx-info-circle me-2"></i>Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <h6 class="text-primary">Instructions</h6>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">
                                    <i class="bx bx-check-circle text-success me-2"></i>
                                    Select a member from the dropdown
                                </li>
                                <li class="mb-2">
                                    <i class="bx bx-check-circle text-success me-2"></i>
                                    Choose a share product for the account
                                </li>
                                <li class="mb-2">
                                    <i class="bx bx-check-circle text-success me-2"></i>
                                    Set the opening date for the account
                                </li>
                                <li class="mb-2">
                                    <i class="bx bx-check-circle text-success me-2"></i>
                                    Add optional notes if needed
                                </li>
                                <li class="mb-2">
                                    <i class="bx bx-check-circle text-success me-2"></i>
                                    Click "Add Line" to add multiple accounts
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
                                <strong>Note:</strong> Account numbers will be automatically generated when you save.
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
        let lineIndex = 1;

        // Initialize Select2 for customer and share product dropdowns
        $('.customer-select, .share-product-select').select2({
            placeholder: 'Select an option',
            allowClear: true,
            width: '100%',
            theme: 'bootstrap-5'
        });

        // Add Line Button Click Handler
        $('#addLineBtn').on('click', function() {
            const template = `
                <div class="share-account-line mb-3 border-bottom pb-3" data-line-index="${lineIndex}">
                    <div class="row">
                        <!-- Member name -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Member name <span class="text-danger">*</span></label>
                            <select name="lines[${lineIndex}][customer_id]" 
                                    class="form-select customer-select" required>
                                <option value="">Select member</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}">
                                        {{ $customer->name }} ({{ $customer->customerNo }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Share product -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Share product <span class="text-danger">*</span></label>
                            <select name="lines[${lineIndex}][share_product_id]" 
                                    class="form-select share-product-select" required>
                                <option value="">Select account</option>
                                @foreach($shareProducts as $product)
                                    <option value="{{ $product->id }}" data-nominal-price="{{ $product->nominal_price }}">
                                        {{ $product->share_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Remove Line Button -->
                        <div class="col-12 text-end">
                            <button type="button" class="btn btn-sm btn-danger remove-line-btn">
                                <i class="bx bx-trash me-1"></i> Remove Line
                            </button>
                        </div>
                    </div>
                </div>
            `;

            $('#shareAccountLines').append(template);
            
            // Initialize Select2 for new dropdowns
            $('#shareAccountLines .share-account-line').last().find('.customer-select, .share-product-select').select2({
                placeholder: 'Select an option',
                allowClear: true,
                width: '100%',
                theme: 'bootstrap-5'
            });

            lineIndex++;

            // Show remove buttons if more than one line
            if ($('.share-account-line').length > 1) {
                $('.remove-line-btn').show();
            }
        });

        // Remove Line Button Click Handler
        $(document).on('click', '.remove-line-btn', function() {
            $(this).closest('.share-account-line').remove();
            
            // Re-index remaining lines
            $('#shareAccountLines .share-account-line').each(function(index) {
                $(this).attr('data-line-index', index);
                $(this).find('select, input').each(function() {
                    const name = $(this).attr('name');
                    if (name) {
                        $(this).attr('name', name.replace(/lines\[\d+\]/, `lines[${index}]`));
                    }
                });
            });

            // Hide remove buttons if only one line remains
            if ($('.share-account-line').length <= 1) {
                $('.remove-line-btn').hide();
            }
        });

        // Form submission handler - copy opening_date and notes to all lines
        $('#shareAccountForm').on('submit', function(e) {
            // Get opening date and notes from first line (the ones at the top)
            const openingDate = $('input[name="lines[0][opening_date]"]').val();
            const notes = $('input[name="lines[0][notes]"]').val();

            // Copy opening date and notes to all additional lines as hidden inputs
            $('.share-account-line[data-line-index!="0"]').each(function() {
                const lineIndex = $(this).attr('data-line-index');
                // Remove existing hidden inputs if any
                $(this).find(`input[name="lines[${lineIndex}][opening_date]"]`).remove();
                $(this).find(`input[name="lines[${lineIndex}][notes]"]`).remove();
                // Add hidden inputs with values from first line
                $(this).append(`<input type="hidden" name="lines[${lineIndex}][opening_date]" value="${openingDate}">`);
                $(this).append(`<input type="hidden" name="lines[${lineIndex}][notes]" value="${notes || ''}">`);
            });

            // Validate at least one line is filled
            let hasValidLine = false;
            $('.share-account-line').each(function() {
                const customerId = $(this).find('.customer-select').val();
                const shareProductId = $(this).find('.share-product-select').val();
                if (customerId && shareProductId) {
                    hasValidLine = true;
                    return false; // break loop
                }
            });

            if (!hasValidLine) {
                e.preventDefault();
                Swal.fire({
                    title: 'Validation Error',
                    text: 'Please fill at least one complete line (Member name and Share product)',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                return false;
            }
        });
    });
</script>
@endpush

