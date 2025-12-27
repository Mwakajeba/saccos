@extends('layouts.main')

@section('title', 'Create Contribution Account')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Contributions', 'url' => route('contributions.index'), 'icon' => 'bx bx-donate-heart'],
            ['label' => 'Contribution Accounts', 'url' => route('contributions.accounts.index'), 'icon' => 'bx bx-wallet'],
            ['label' => 'Create', 'url' => '#', 'icon' => 'bx bx-plus']
        ]" />

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 text-uppercase text-success">Add Contribution Account</h6>
            <a href="{{ route('contributions.accounts.index') }}" class="btn btn-success">
                <i class="bx bx-list-ul me-1"></i> Contribution Accounts List
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

                        <form action="{{ route('contributions.accounts.store') }}" method="POST" id="contributionAccountForm" data-has-custom-handler="true">
                            @csrf

                            <!-- Contribution Product Selection (at the top) -->
                            <div class="row mb-3">
                                <div class="col-12 mb-3">
                                    <label class="form-label">Contribution Product <span class="text-danger">*</span></label>
                                    <select name="contribution_product_id" 
                                            id="contribution_product_id"
                                            class="form-select @error('contribution_product_id') is-invalid @enderror" required>
                                        <option value="">Select Contribution Product</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}" 
                                                {{ old('contribution_product_id') == $product->id ? 'selected' : '' }}>
                                                {{ $product->product_name }} ({{ $product->category }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('contribution_product_id') 
                                        <div class="invalid-feedback">{{ $message }}</div> 
                                    @enderror
                                </div>
                            </div>

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
                            <div id="contributionAccountLines">
                                <!-- Default Line -->
                                <div class="contribution-account-line mb-3 border-bottom pb-3" data-line-index="0">
                                    <div class="row">
                                        <!-- Member name -->
                                        <div class="col-md-12 mb-3">
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

                                        <!-- Hidden contribution_product_id for this line -->
                                        <input type="hidden" name="lines[0][contribution_product_id]" class="line-product-id" value="{{ old('contribution_product_id') }}">

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
                                    Select a contribution product first
                                </li>
                                <li class="mb-2">
                                    <i class="bx bx-check-circle text-success me-2"></i>
                                    Select members from the dropdown
                                </li>
                                <li class="mb-2">
                                    <i class="bx bx-check-circle text-success me-2"></i>
                                    Set the opening date for all accounts
                                </li>
                                <li class="mb-2">
                                    <i class="bx bx-check-circle text-success me-2"></i>
                                    Add optional notes if needed
                                </li>
                                <li class="mb-2">
                                    <i class="bx bx-check-circle text-success me-2"></i>
                                    Click "Add Line" to add more members
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
                                <strong>{{ $products->count() }}</strong>
                            </div>
                        </div>

                        <hr>

                        <div class="alert alert-info mb-0">
                            <small>
                                <i class="bx bx-info-circle me-1"></i>
                                <strong>Note:</strong> Account numbers (16 characters) will be automatically generated when you save.
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

        // Initialize Select2 for customer dropdowns
        $('.customer-select').select2({
            placeholder: 'Select a member',
            allowClear: true,
            width: '100%',
            theme: 'bootstrap-5'
        });

        // Update all line product IDs when main product selection changes
        $('#contribution_product_id').on('change', function() {
            const productId = $(this).val();
            $('.line-product-id').val(productId);
            // Also update the hidden input for line 0
            $('input[name="lines[0][contribution_product_id]"]').val(productId);
        });
        
        // Initialize line 0 product ID on page load
        const initialProductId = $('#contribution_product_id').val();
        if (initialProductId) {
            $('input[name="lines[0][contribution_product_id]"]').val(initialProductId);
        }

        // Add Line Button Click Handler
        $('#addLineBtn').on('click', function() {
            const productId = $('#contribution_product_id').val();
            
            if (!productId) {
                Swal.fire({
                    title: 'Product Required',
                    text: 'Please select a Contribution Product first before adding lines.',
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
                return;
            }

            const template = `
                <div class="contribution-account-line mb-3 border-bottom pb-3" data-line-index="${lineIndex}">
                    <div class="row">
                        <!-- Member name -->
                        <div class="col-md-12 mb-3">
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

                        <!-- Hidden contribution_product_id for this line -->
                        <input type="hidden" name="lines[${lineIndex}][contribution_product_id]" class="line-product-id" value="${productId}">

                        <!-- Remove Line Button -->
                        <div class="col-12 text-end">
                            <button type="button" class="btn btn-sm btn-danger remove-line-btn">
                                <i class="bx bx-trash me-1"></i> Remove Line
                            </button>
                        </div>
                    </div>
                </div>
            `;

            $('#contributionAccountLines').append(template);
            
            // Initialize Select2 for new dropdown
            $('#contributionAccountLines .contribution-account-line').last().find('.customer-select').select2({
                placeholder: 'Select a member',
                allowClear: true,
                width: '100%',
                theme: 'bootstrap-5'
            });

            lineIndex++;

            // Show remove buttons if more than one line
            if ($('.contribution-account-line').length > 1) {
                $('.remove-line-btn').show();
            }
        });

        // Remove Line Button Click Handler
        $(document).on('click', '.remove-line-btn', function() {
            $(this).closest('.contribution-account-line').remove();
            
            // Re-index remaining lines
            $('#contributionAccountLines .contribution-account-line').each(function(index) {
                $(this).attr('data-line-index', index);
                $(this).find('select, input').each(function() {
                    const name = $(this).attr('name');
                    if (name) {
                        $(this).attr('name', name.replace(/lines\[\d+\]/, `lines[${index}]`));
                    }
                });
            });

            // Hide remove buttons if only one line remains
            if ($('.contribution-account-line').length <= 1) {
                $('.remove-line-btn').hide();
            }
        });

        // Form submission handler - copy opening_date, notes, and product_id to all lines
        $('#contributionAccountForm').on('submit', function(e) {
            const form = this;
            const submitBtn = $(form).find('button[type="submit"]');
            const originalHTML = submitBtn.html();
            
            // Prevent multiple submissions
            if (form.dataset.submitting === 'true') {
                e.preventDefault();
                return false;
            }
            
            // Validate product is selected
            const productId = $('#contribution_product_id').val();
            if (!productId) {
                e.preventDefault();
                Swal.fire({
                    title: 'Validation Error',
                    text: 'Please select a Contribution Product',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                return false;
            }

            // Sync Select2 values to actual select elements before submission
            $('.customer-select').each(function() {
                $(this).trigger('change.select2');
            });
            
            // Ensure line 0 has the product ID set before submission
            $('input[name="lines[0][contribution_product_id]"]').val(productId);
            
            // Get opening date and notes from first line
            const openingDate = $('input[name="lines[0][opening_date]"]').val();
            const notes = $('input[name="lines[0][notes]"]').val();

            // Copy opening date, notes, and product_id to all additional lines
            $('.contribution-account-line[data-line-index!="0"]').each(function() {
                const lineIndex = $(this).attr('data-line-index');
                // Remove existing hidden inputs if any
                $(this).find(`input[name="lines[${lineIndex}][opening_date]"]`).remove();
                $(this).find(`input[name="lines[${lineIndex}][notes]"]`).remove();
                // Add hidden inputs with values from first line
                $(this).append(`<input type="hidden" name="lines[${lineIndex}][opening_date]" value="${openingDate}">`);
                $(this).append(`<input type="hidden" name="lines[${lineIndex}][notes]" value="${notes || ''}">`);
                // Ensure product_id is set
                $(this).find('.line-product-id').val(productId);
            });

            // Validate at least one line is filled
            let hasValidLine = false;
            $('.contribution-account-line').each(function() {
                const customerId = $(this).find('.customer-select').val();
                if (customerId) {
                    hasValidLine = true;
                    return false; // break loop
                }
            });

            if (!hasValidLine) {
                e.preventDefault();
                Swal.fire({
                    title: 'Validation Error',
                    text: 'Please fill at least one complete line (Member name)',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                return false;
            }
            
            // Ensure CSRF token is present before submission
            let csrfToken = $(form).find('input[name="_token"]').val();
            if (!csrfToken) {
                csrfToken = $('meta[name="csrf-token"]').attr('content');
                if (csrfToken) {
                    $(form).append('<input type="hidden" name="_token" value="' + csrfToken + '">');
                }
            }
            
            // Mark form as submitting and show loading state
            form.dataset.submitting = 'true';
            submitBtn.prop('disabled', true);
            submitBtn.html('<i class="bx bx-loader-alt bx-spin me-1"></i> Processing...');
            
            // DO NOT disable form inputs - they need to be submitted with the form
            // Disabled inputs don't get sent in form submissions
            
            // Allow form to submit normally - don't prevent default
            // The form will submit with all data including CSRF token
            
            // Reset state on timeout (in case submission hangs)
            setTimeout(function() {
                if (form.dataset.submitting === 'true') {
                    form.dataset.submitting = 'false';
                    submitBtn.prop('disabled', false);
                    submitBtn.html(originalHTML);
                }
            }, 30000); // 30 second timeout
        });
    });
</script>
@endpush

