@extends('layouts.main')

@section('title', 'Contribution Opening Balance Import')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Contributions', 'url' => route('contributions.index'), 'icon' => 'bx bx-donate-heart'],
            ['label' => 'Opening Balance Import', 'url' => '#', 'icon' => 'bx bx-calendar-check']
        ]" />

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 text-uppercase text-dark">Contribution Opening Balance Import</h6>
        </div>
        <hr />

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

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('contributions.opening-balance.import') }}" method="POST" enctype="multipart/form-data" id="importForm">
                            @csrf

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="contribution_product_id" class="form-label">Contribution Product <span class="text-danger">*</span></label>
                                    <select name="contribution_product_id" id="contribution_product_id" 
                                            class="form-select @error('contribution_product_id') is-invalid @enderror" required>
                                        <option value="">Select Contribution Product</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}" {{ old('contribution_product_id') == $product->id ? 'selected' : '' }}>
                                                {{ $product->product_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('contribution_product_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="bank_account_id" class="form-label">Bank Account <span class="text-danger">*</span></label>
                                    <select name="bank_account_id" id="bank_account_id" 
                                            class="form-select @error('bank_account_id') is-invalid @enderror" required>
                                        <option value="">Select Bank Account</option>
                                        @foreach($bankAccounts as $bankAccount)
                                            <option value="{{ $bankAccount->id }}" {{ old('bank_account_id') == $bankAccount->id ? 'selected' : '' }}>
                                                {{ $bankAccount->name }} - {{ $bankAccount->account_number }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('bank_account_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="opening_balance_date" class="form-label">Opening Balance Date <span class="text-danger">*</span></label>
                                    <input type="date" name="opening_balance_date" id="opening_balance_date" 
                                           class="form-control @error('opening_balance_date') is-invalid @enderror"
                                           value="{{ old('opening_balance_date', date('Y-m-d')) }}" required>
                                    @error('opening_balance_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <div class="alert alert-info">
                                        <h6 class="text-primary">Instructions:</h6>
                                        <ul class="mb-0">
                                            <li>Select the contribution product</li>
                                            <li>Select the bank account for double entry</li>
                                            <li>Select the opening balance date</li>
                                            <li>Click "Download Template" to get the Excel template with all customers</li>
                                            <li>Fill in the Excel file with opening balance amounts (opening_balance_amount column)</li>
                                            <li>Upload the filled Excel file</li>
                                        </ul>
                                        <strong>Note:</strong> This will create accounts for customers who don't have one, create journals, journal items, GL transactions, and log the opening balance.
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <button type="button" class="btn btn-outline-primary" id="downloadTemplateBtn">
                                        <i class="bx bx-download me-1"></i> Download Template
                                    </button>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="import_file" class="form-label">Excel File <span class="text-danger">*</span></label>
                                    <input type="file" name="import_file" id="import_file" 
                                           class="form-control @error('import_file') is-invalid @enderror"
                                           accept=".xlsx,.xls" required>
                                    @error('import_file')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Only Excel files (.xlsx, .xls) are allowed. Maximum size: 10MB</small>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bx bx-upload me-1"></i> Import Opening Balance
                                    </button>
                                    <a href="{{ route('contributions.index') }}" class="btn btn-secondary">
                                        <i class="bx bx-arrow-back me-1"></i> Back
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        // Handle template download
        $('#downloadTemplateBtn').on('click', function(e) {
            e.preventDefault();

            const productId = $('#contribution_product_id').val();
            const openingBalanceDate = $('#opening_balance_date').val();

            if (!productId) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Validation Error',
                    text: 'Please select a contribution product',
                });
                return;
            }

            if (!openingBalanceDate) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Validation Error',
                    text: 'Please select an opening balance date',
                });
                return;
            }

            // Build URL with query parameters
            const url = '{{ route("contributions.opening-balance.download-template") }}?' +
                       'contribution_product_id=' + encodeURIComponent(productId) +
                       '&opening_balance_date=' + encodeURIComponent(openingBalanceDate);

            // Open in new window to trigger download
            window.location.href = url;
        });

        // Handle form submission
        $('#importForm').on('submit', function(e) {
            const form = $(this);
            const formData = new FormData(this);

            e.preventDefault();

            Swal.fire({
                title: 'Processing...',
                text: 'Please wait while we process your import.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Opening balance import has been queued. Processing will start shortly.',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        window.location.reload();
                    });
                },
                error: function(xhr) {
                    let errorMessage = 'An error occurred while processing the import.';
                    
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                        const errors = Object.values(xhr.responseJSON.errors).flat();
                        errorMessage = errors.join('\n');
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Import Failed',
                        text: errorMessage,
                        confirmButtonText: 'OK'
                    });
                }
            });
        });
    });
</script>
@endpush
@endsection

