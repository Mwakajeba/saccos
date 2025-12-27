@extends('layouts.main')

@php
    use Vinkla\Hashids\Facades\Hashids;
@endphp

@section('title', 'Create Contribution Transfer')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Contributions', 'url' => route('contributions.index'), 'icon' => 'bx bx-donate-heart'],
            ['label' => 'Contributions Transfers', 'url' => route('contributions.transfers.index'), 'icon' => 'bx bx-transfer'],
            ['label' => 'Create', 'url' => '#', 'icon' => 'bx bx-plus']
        ]" />

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 text-uppercase text-info">Add Contribution Transfer</h6>
            <a href="{{ route('contributions.transfers.index') }}" class="btn btn-info">
                <i class="bx bx-list-ul me-1"></i> Transfers List
            </a>
        </div>
        <hr />

        <div class="row">
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

                        <form action="{{ route('contributions.transfers.store') }}" method="POST" id="transferForm" data-has-custom-handler="true">
                            @csrf

                            <!-- Source Section -->
                            <div class="card border-primary mb-3">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0"><i class="bx bx-arrow-from-left me-2"></i>Source (From)</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Source Customer <span class="text-danger">*</span></label>
                                            <select name="source_customer_id" id="source_customer_id"
                                                    class="form-select @error('source_customer_id') is-invalid @enderror" required>
                                                <option value="">Select Source Customer</option>
                                                @foreach($customers as $customer)
                                                    <option value="{{ $customer->id }}" 
                                                        {{ old('source_customer_id') == $customer->id ? 'selected' : '' }}>
                                                        {{ $customer->name }} ({{ $customer->customerNo }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('source_customer_id') 
                                                <div class="invalid-feedback">{{ $message }}</div> 
                                            @enderror
                                            <small class="text-muted" id="source-balance-info" style="display: none;">
                                                Available Balance: <span id="source-balance" class="fw-bold">0.00</span> TZS
                                            </small>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Source Product <span class="text-danger">*</span></label>
                                            <select name="source_product_id" id="source_product_id"
                                                    class="form-select @error('source_product_id') is-invalid @enderror" required>
                                                <option value="">Select Source Product</option>
                                                @foreach($products as $product)
                                                    <option value="{{ $product->id }}" 
                                                        {{ old('source_product_id') == $product->id ? 'selected' : '' }}>
                                                        {{ $product->product_name }} ({{ $product->category }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('source_product_id') 
                                                <div class="invalid-feedback">{{ $message }}</div> 
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Destination Section -->
                            <div class="card border-success mb-3">
                                <div class="card-header bg-success text-white">
                                    <h6 class="mb-0"><i class="bx bx-arrow-to-right me-2"></i>Destination (To)</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Destination Customer <span class="text-danger">*</span></label>
                                            <select name="destination_customer_id" id="destination_customer_id"
                                                    class="form-select @error('destination_customer_id') is-invalid @enderror" required>
                                                <option value="">Select Destination Customer</option>
                                                @foreach($customers as $customer)
                                                    <option value="{{ $customer->id }}" 
                                                        {{ old('destination_customer_id') == $customer->id ? 'selected' : '' }}>
                                                        {{ $customer->name }} ({{ $customer->customerNo }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('destination_customer_id') 
                                                <div class="invalid-feedback">{{ $message }}</div> 
                                            @enderror
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Destination Product <span class="text-danger">*</span></label>
                                            <select name="destination_product_id" id="destination_product_id"
                                                    class="form-select @error('destination_product_id') is-invalid @enderror" required>
                                                <option value="">Select Destination Product</option>
                                                @foreach($products as $product)
                                                    <option value="{{ $product->id }}" 
                                                        {{ old('destination_product_id') == $product->id ? 'selected' : '' }}>
                                                        {{ $product->product_name }} ({{ $product->category }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('destination_product_id') 
                                                <div class="invalid-feedback">{{ $message }}</div> 
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Transfer Details -->
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Transfer Amount <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">TZS</span>
                                        <input type="number" name="amount" id="amount" step="0.01" min="0.01"
                                               class="form-control @error('amount') is-invalid @enderror"
                                               value="{{ old('amount') }}" placeholder="0.00" required>
                                    </div>
                                    @error('amount') 
                                        <div class="invalid-feedback">{{ $message }}</div> 
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Date <span class="text-danger">*</span></label>
                                    <input type="date" name="date" id="date"
                                           class="form-control @error('date') is-invalid @enderror"
                                           value="{{ old('date', date('Y-m-d')) }}" required>
                                    @error('date') 
                                        <div class="invalid-feedback">{{ $message }}</div> 
                                    @enderror
                                </div>

                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea name="description" id="description" rows="3"
                                              class="form-control @error('description') is-invalid @enderror"
                                              placeholder="Enter description (optional)">{{ old('description') }}</textarea>
                                    @error('description') 
                                        <div class="invalid-feedback">{{ $message }}</div> 
                                    @enderror
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="d-flex justify-content-end mt-4">
                                <a href="{{ route('contributions.transfers.index') }}" class="btn btn-secondary me-2">
                                    <i class="bx bx-arrow-back me-1"></i> Cancel
                                </a>
                                <button type="submit" class="btn btn-info" id="submitBtn">
                                    <i class="bx bx-save me-1"></i> Process Transfer
                                </button>
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
                            <h6 class="text-primary">Transfer Types</h6>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">
                                    <i class="bx bx-check-circle text-success me-2"></i>
                                    <strong>Internal:</strong> Same customer, different products
                                </li>
                                <li class="mb-2">
                                    <i class="bx bx-check-circle text-success me-2"></i>
                                    <strong>External:</strong> Different customers, same or different products
                                </li>
                            </ul>
                        </div>

                        <hr>

                        <div class="mb-3">
                            <h6 class="text-primary">Instructions</h6>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">
                                    <i class="bx bx-check-circle text-success me-2"></i>
                                    Select source customer and product
                                </li>
                                <li class="mb-2">
                                    <i class="bx bx-check-circle text-success me-2"></i>
                                    Select destination customer and product
                                </li>
                                <li class="mb-2">
                                    <i class="bx bx-check-circle text-success me-2"></i>
                                    Enter transfer amount
                                </li>
                                <li class="mb-2">
                                    <i class="bx bx-check-circle text-success me-2"></i>
                                    Set transaction date
                                </li>
                            </ul>
                        </div>

                        <hr>

                        <div class="alert alert-info mb-0">
                            <small>
                                <i class="bx bx-info-circle me-1"></i>
                                <strong>Note:</strong> This is a direct transfer between liability accounts. The source account will be debited and the destination account will be credited. Both transactions will be recorded in GL transactions.
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
        // Initialize Select2 for dropdowns
        $('#source_customer_id, #source_product_id, #destination_customer_id, #destination_product_id').select2({
            placeholder: 'Select an option',
            allowClear: true,
            width: '100%',
            theme: 'bootstrap-5'
        });

        // Update source products when source customer changes
        $('#source_customer_id').on('change', function() {
            const customerId = $(this).val();
            if (customerId) {
                // Fetch customer's products via AJAX
                fetch(`/api/customers/${customerId}/contribution-products`)
                    .then(response => response.json())
                    .then(data => {
                        const $select = $('#source_product_id');
                        $select.empty().append('<option value="">Select Source Product</option>');
                        data.products.forEach(product => {
                            $select.append(`<option value="${product.id}">${product.product_name} (${product.category})</option>`);
                        });
                        $select.trigger('change');
                    })
                    .catch(error => {
                        console.error('Error fetching products:', error);
                    });
            }
        });

        // Update destination products when destination customer changes
        $('#destination_customer_id').on('change', function() {
            const customerId = $(this).val();
            if (customerId) {
                // Fetch customer's products via AJAX
                fetch(`/api/customers/${customerId}/contribution-products`)
                    .then(response => response.json())
                    .then(data => {
                        const $select = $('#destination_product_id');
                        $select.empty().append('<option value="">Select Destination Product</option>');
                        data.products.forEach(product => {
                            $select.append(`<option value="${product.id}">${product.product_name} (${product.category})</option>`);
                        });
                        $select.trigger('change');
                    })
                    .catch(error => {
                        console.error('Error fetching products:', error);
                    });
        });

        // Check source balance when customer and product are selected
        $('#source_customer_id, #source_product_id').on('change', function() {
            const customerId = $('#source_customer_id').val();
            const productId = $('#source_product_id').val();
            
            if (customerId && productId) {
                fetch(`/api/contribution-accounts/balance?customer_id=${customerId}&product_id=${productId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.balance !== undefined) {
                            $('#source-balance').text(data.balance.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                            $('#source-balance-info').show();
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching balance:', error);
                    });
            } else {
                $('#source-balance-info').hide();
            }
        });

        // Form submission handler
        $('#transferForm').on('submit', function(e) {
            const form = $(this)[0];
            const $form = $(this);
            const submitBtn = $('#submitBtn');
            const originalHTML = submitBtn.html();

            // Prevent multiple submissions
            if (form.dataset.submitting === 'true') {
                e.preventDefault();
                return false;
            }

            // Sync Select2 values before submission
            $('#source_customer_id, #source_product_id, #destination_customer_id, #destination_product_id, #bank_account_id').each(function() {
                if ($(this).data('select2')) {
                    $(this).trigger('change');
                }
            });

            // Ensure CSRF token is present
            let csrfToken = $form.find('input[name="_token"]').val();
            if (!csrfToken) {
                csrfToken = $('meta[name="csrf-token"]').attr('content');
                if (csrfToken) {
                    // Remove any existing duplicate token
                    $form.find('input[name="_token"]').remove();
                    // Add the token
                    $form.prepend('<input type="hidden" name="_token" value="' + csrfToken + '">');
                }
            }

            // Mark form as submitting and show loading state
            form.dataset.submitting = 'true';
            submitBtn.prop('disabled', true);
            submitBtn.html('<i class="bx bx-loader-alt bx-spin me-1"></i> Processing...');

            // Allow form to submit normally - don't prevent default
            // The form will submit with all the data including CSRF token

            // Reset state on timeout (in case submission fails silently)
            setTimeout(function() {
                if (form.dataset.submitting === 'true') {
                    form.dataset.submitting = 'false';
                    submitBtn.prop('disabled', false);
                    submitBtn.html(originalHTML);
                }
            }, 30000);
        });
    });
</script>
@endpush

