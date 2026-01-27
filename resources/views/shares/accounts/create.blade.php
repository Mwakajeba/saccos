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

                        <form action="{{ route('shares.accounts.store') }}" method="POST" id="shareAccountForm" data-has-custom-handler="true">
                            @csrf

                            <!-- Share Product Selection (Global) -->
                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <div class="alert alert-info">
                                        <strong><i class="bx bx-info-circle me-2"></i>Select Share Product:</strong>
                                        Choose the share product, then add members below.
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Share product <span class="text-danger">*</span></label>
                                    <select name="share_product_id" id="globalShareProduct"
                                            class="form-select @error('share_product_id') is-invalid @enderror" required>
                                        <option value="">Select share product</option>
                                        @foreach($shareProducts as $product)
                                            <option value="{{ $product->id }}" 
                                                {{ old('share_product_id') == $product->id ? 'selected' : '' }}
                                                data-nominal-price="{{ $product->nominal_price }}">
                                                {{ $product->share_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('share_product_id') 
                                        <div class="invalid-feedback">{{ $message }}</div> 
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Opening date <span class="text-danger">*</span></label>
                                    <input type="date" name="opening_date" id="globalOpeningDate"
                                           class="form-control @error('opening_date') is-invalid @enderror"
                                           value="{{ old('opening_date', date('Y-m-d')) }}" required>
                                    @error('opening_date') 
                                        <div class="invalid-feedback">{{ $message }}</div> 
                                    @enderror
                                </div>

                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Notes</label>
                                    <textarea name="notes" id="globalNotes" rows="2"
                                           class="form-control @error('notes') is-invalid @enderror"
                                           placeholder="Optional notes">{{ old('notes') }}</textarea>
                                    @error('notes') 
                                        <div class="invalid-feedback">{{ $message }}</div> 
                                    @enderror
                                </div>
                            </div>

                            <hr class="my-4">

                            <!-- Members Section -->
                            <div class="row mb-3">
                                <div class="col-12">
                                    <h6 class="text-primary mb-3">
                                        <i class="bx bx-user-plus me-2"></i>Add Members
                                    </h6>
                                </div>
                            </div>

                            <!-- Form Lines Container -->
                            <div id="shareAccountLines">
                                <!-- Default Line -->
                                <div class="share-account-line mb-3 border p-3 rounded" data-line-index="0">
                                    <div class="row align-items-center">
                                        <!-- Member name -->
                                        <div class="col-md-10 mb-2">
                                            <label class="form-label">Member name <span class="text-danger">*</span></label>
                                            <select name="members[0][customer_id]" 
                                                    class="form-select customer-select @error('members.0.customer_id') is-invalid @enderror" required>
                                                <option value="">Select member</option>
                                                @foreach($customers as $customer)
                                                    <option value="{{ $customer->id }}" 
                                                        {{ old('members.0.customer_id') == $customer->id ? 'selected' : '' }}>
                                                        {{ $customer->name }} ({{ $customer->customerNo }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('members.0.customer_id') 
                                                <div class="invalid-feedback">{{ $message }}</div> 
                                            @enderror
                                        </div>

                                        <!-- Remove Line Button (hidden for first line) -->
                                        <div class="col-md-2 mb-2 text-end">
                                            <label class="form-label d-block">&nbsp;</label>
                                            <button type="button" class="btn btn-sm btn-danger remove-line-btn" style="display: none;">
                                                <i class="bx bx-trash"></i> Remove
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Add Line Button -->
                            <div class="row mt-3">
                                <div class="col-12">
                                    <button type="button" class="btn btn-primary" id="addLineBtn">
                                        <i class="bx bx-plus me-1"></i> Add Another Member
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
                                    Select the share product first
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
                                    Select members who will have this share product
                                </li>
                                <li class="mb-2">
                                    <i class="bx bx-check-circle text-success me-2"></i>
                                    Click "Add Another Member" to add more members
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

        // Periodically refresh CSRF token to prevent session expiration issues
        // This fetches a fresh token every 5 minutes
        function refreshCsrfToken() {
            $.ajax({
                url: '{{ route("dashboard") }}',
                method: 'GET',
                success: function(response) {
                    // Extract CSRF token from the response HTML
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(response, 'text/html');
                    const newToken = doc.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    
                    if (newToken) {
                        // Update meta tag
                        $('meta[name="csrf-token"]').attr('content', newToken);
                        // Update form token
                        $('#shareAccountForm input[name="_token"]').val(newToken);
                        console.log('CSRF token refreshed successfully');
                    }
                },
                error: function() {
                    console.log('Failed to refresh CSRF token');
                }
            });
        }

        // Refresh token every 5 minutes (300000 ms)
        setInterval(refreshCsrfToken, 300000);

        // Initialize Select2 for dropdowns
        $('#globalShareProduct, .customer-select').select2({
            placeholder: 'Select an option',
            allowClear: true,
            width: '100%',
            theme: 'bootstrap-5'
        });

        // Add Line Button Click Handler
        $('#addLineBtn').on('click', function() {
            const template = `
                <div class="share-account-line mb-3 border p-3 rounded" data-line-index="${lineIndex}">
                    <div class="row align-items-center">
                        <!-- Member name -->
                        <div class="col-md-10 mb-2">
                            <label class="form-label">Member name <span class="text-danger">*</span></label>
                            <select name="members[${lineIndex}][customer_id]" 
                                    class="form-select customer-select" required>
                                <option value="">Select member</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}">
                                        {{ $customer->name }} ({{ $customer->customerNo }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Remove Line Button -->
                        <div class="col-md-2 mb-2 text-end">
                            <label class="form-label d-block">&nbsp;</label>
                            <button type="button" class="btn btn-sm btn-danger remove-line-btn">
                                <i class="bx bx-trash"></i> Remove
                            </button>
                        </div>
                    </div>
                </div>
            `;

            $('#shareAccountLines').append(template);
            
            // Initialize Select2 for new dropdown
            $('#shareAccountLines .share-account-line').last().find('.customer-select').select2({
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
                $(this).find('select').each(function() {
                    const name = $(this).attr('name');
                    if (name) {
                        $(this).attr('name', name.replace(/members\[\d+\]/, `members[${index}]`));
                    }
                });
            });

            // Hide remove buttons if only one line remains
            if ($('.share-account-line').length <= 1) {
                $('.remove-line-btn').hide();
            }
        });

        // Form submission handler
        $('#shareAccountForm').on('submit', function(e) {
            const form = this;
            const submitBtn = $(form).find('button[type="submit"]');
            const originalHTML = submitBtn.html();
            
            // Prevent multiple submissions
            if (form.dataset.submitting === 'true') {
                e.preventDefault();
                return false;
            }

            // Refresh CSRF token from meta tag before submission
            const metaCsrfToken = $('meta[name="csrf-token"]').attr('content');
            if (metaCsrfToken) {
                $(form).find('input[name="_token"]').val(metaCsrfToken);
            }

            // Validate share product is selected
            const shareProductId = $('#globalShareProduct').val();
            if (!shareProductId) {
                e.preventDefault();
                Swal.fire({
                    title: 'Validation Error',
                    text: 'Please select a share product',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                return false;
            }

            // Validate at least one member is selected
            let hasValidMember = false;
            $('.share-account-line').each(function() {
                const customerId = $(this).find('.customer-select').val();
                if (customerId) {
                    hasValidMember = true;
                    return false; // break loop
                }
            });

            if (!hasValidMember) {
                e.preventDefault();
                Swal.fire({
                    title: 'Validation Error',
                    text: 'Please select at least one member',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                return false;
            }
            
            // Mark form as submitting and show loading state
            form.dataset.submitting = 'true';
            submitBtn.prop('disabled', true);
            submitBtn.html('<i class="bx bx-loader-alt bx-spin me-1"></i> Processing...');
            
            // Disable all form inputs
            $(form).find('input, select, textarea, button').not('button[type="submit"]').prop('disabled', true);
            
            // Reset state on form error (validation errors will reload page, so this is for AJAX errors if any)
            setTimeout(function() {
                if (form.dataset.submitting === 'true') {
                    form.dataset.submitting = 'false';
                    submitBtn.prop('disabled', false);
                    submitBtn.html(originalHTML);
                    $(form).find('input, select, textarea, button').not('button[type="submit"]').prop('disabled', false);
                }
            }, 30000); // 30 second timeout
        });
    });
</script>
@endpush

