@extends('layouts.main')
@section('title', 'Bulk Upload Customers')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Customers', 'url' => route('customers.index'), 'icon' => 'bx bx-group'],
            ['label' => 'Bulk Upload', 'url' => '#', 'icon' => 'bx bx-upload']
        ]" />

            <h6 class="mb-0 text-uppercase">BULK UPLOAD CUSTOMERS</h6>
            <hr />

            <div class="row">
                <div class="col-12">
                    <div class="card radius-10">
                        <div class="card-body">
                            <div class="row">
                                <!-- Sample Download Section -->
                                <div class="col-md-6 mb-4">
                                    <div class="card border-primary">
                                        <div class="card-header bg-primary text-white">
                                            <h6 class="mb-0"><i class="bx bx-download me-2"></i>Download Sample Template</h6>
                                        </div>
                                        <div class="card-body">
                                            <p class="text-muted mb-3">Download the sample Excel template with 100 sample customers to understand the
                                                required format for bulk upload.</p>
                                            <a href="{{ route('customers.download-sample') }}"
                                                class="btn btn-outline-primary">
                                                <i class="bx bx-download me-2"></i>Download Sample Template (Excel)
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Instructions Section -->
                                <div class="col-md-6 mb-4">
                                    <div class="card border-info">
                                        <div class="card-header bg-info text-white">
                                            <h6 class="mb-0"><i class="bx bx-info-circle me-2"></i>Instructions</h6>
                                        </div>
                                        <div class="card-body">
                                            <ul class="mb-0">
                                                <li>Download the sample Excel template first</li>
                                                <li>Fill in the customer data following the format</li>
                                                <li>Save as Excel (.xlsx) or CSV format</li>
                                                <li>Upload the file below</li>
                                                <li>Select shares or contributions options if needed</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if($errors->any())
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="bx bx-error-circle me-2"></i>
                                    <strong>Upload failed!</strong> Please fix the following errors:
                                    <ul class="mb-0 mt-2">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif

                            @if(session('upload_errors'))
                                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                                    <i class="bx bx-warning me-2"></i>
                                    <strong>Upload completed with warnings!</strong> Some rows had issues:
                                    <ul class="mb-0 mt-2">
                                        @foreach(session('upload_errors') as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif

                            @if(session('success'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="bx bx-check-circle me-2"></i>
                                    {{ session('success') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif

                            <!-- Upload Form -->
                            <form action="{{ route('customers.bulk-upload.store') }}" method="POST"
                                enctype="multipart/form-data" id="bulkUploadForm" data-has-custom-handler="true">
                                @csrf

                                <div class="row">
                                    <!-- CSV File Upload -->
                                    <div class="col-md-12 mb-4">
                                        <div class="card">
                                            <div class="card-header">
                                                <h6 class="mb-0"><i class="bx bx-file me-2"></i>Upload File</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <label for="csv_file" class="form-label">Select File (CSV or Excel) <span
                                                            class="text-danger">*</span></label>
                                                    <input type="file" name="csv_file" id="csv_file"
                                                        class="form-control @error('csv_file') is-invalid @enderror"
                                                        accept=".csv,.xlsx,.xls" required>
                                                    <div class="form-text">CSV and Excel files (.csv, .xlsx, .xls) are allowed. Maximum size: 10MB
                                                    </div>
                                                    @error('csv_file')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Shares Options -->
                                    <div class="col-md-12 mb-4">
                                        <div class="card">
                                            <div class="card-header">
                                                <h6 class="mb-0"><i class="bx bx-bar-chart-square me-2"></i>Shares Options
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <div class="form-check">
                                                            <input type="checkbox" class="form-check-input" value="1"
                                                                name="has_shares" id="has_shares">
                                                            <label class="form-check-label" for="has_shares">
                                                                Create Share Accounts for All Customers
                                                            </label>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6 mb-3" id="share-product-container" style="display: none;">
                                                        <label class="form-label">Share Product <span class="text-danger">*</span></label>
                                                        <select name="share_product_id" id="share_product_id" class="form-select @error('share_product_id') is-invalid @enderror">
                                                            <option value="">Select Share Product</option>
                                                            @foreach($shareProducts as $product)
                                                                <option value="{{ $product->id }}">{{ $product->share_name }}</option>
                                                            @endforeach
                                                        </select>
                                                        @error('share_product_id')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Contributions Options -->
                                    <div class="col-md-12 mb-4">
                                        <div class="card">
                                            <div class="card-header">
                                                <h6 class="mb-0"><i class="bx bx-donate-heart me-2"></i>Contributions Options
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <div class="form-check">
                                                            <input type="checkbox" class="form-check-input" value="1"
                                                                name="has_contributions" id="has_contributions">
                                                            <label class="form-check-label" for="has_contributions">
                                                                Create Contribution Accounts for All Customers
                                                            </label>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6 mb-3" id="contribution-product-container" style="display: none;">
                                                        <label class="form-label">Contribution Product <span class="text-danger">*</span></label>
                                                        <select name="contribution_product_id" id="contribution_product_id" class="form-select @error('contribution_product_id') is-invalid @enderror">
                                                            <option value="">Select Contribution Product</option>
                                                            @foreach($contributionProducts as $product)
                                                                <option value="{{ $product->id }}">{{ $product->product_name }} ({{ $product->category }})</option>
                                                            @endforeach
                                                        </select>
                                                        @error('contribution_product_id')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Submit Buttons -->
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('customers.index') }}" class="btn btn-secondary">
                                        <i class="bx bx-arrow-back me-1"></i> Back to Customers
                                    </a>
                                    <button type="submit" class="btn btn-primary" id="submitBtn">
                                        <i class="bx bx-upload me-1"></i>
                                        <span id="submitText">Upload Customers</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const sharesCheckbox = document.querySelector('#has_shares');
            const shareProductContainer = document.querySelector('#share-product-container');
            const contributionsCheckbox = document.querySelector('#has_contributions');
            const contributionProductContainer = document.querySelector('#contribution-product-container');
            const form = document.querySelector('#bulkUploadForm');
            const submitBtn = document.querySelector('#submitBtn');
            const submitText = document.querySelector('#submitText');

            // Show/hide share product
            function toggleShareProductField() {
                if (sharesCheckbox.checked) {
                    shareProductContainer.style.display = 'block';
                    document.querySelector('select[name="share_product_id"]').required = true;
                } else {
                    shareProductContainer.style.display = 'none';
                    document.querySelector('select[name="share_product_id"]').required = false;
                    document.querySelector('select[name="share_product_id"]').value = '';
                }
            }

            // Show/hide contribution product
            function toggleContributionProductField() {
                if (contributionsCheckbox.checked) {
                    contributionProductContainer.style.display = 'block';
                    document.querySelector('select[name="contribution_product_id"]').required = true;
                } else {
                    contributionProductContainer.style.display = 'none';
                    document.querySelector('select[name="contribution_product_id"]').required = false;
                    document.querySelector('select[name="contribution_product_id"]').value = '';
                }
            }

            // Event listeners
            sharesCheckbox.addEventListener('change', toggleShareProductField);
            contributionsCheckbox.addEventListener('change', toggleContributionProductField);

            // Initialize the state on page load
            toggleShareProductField();
            toggleContributionProductField();

            // Handle form submission
            form.addEventListener('submit', function (e) {
                // Validate shares checkbox
                if (sharesCheckbox.checked && !document.querySelector('select[name="share_product_id"]').value) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Validation Error',
                        text: 'Please select a Share Product when Shares option is checked.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                    return false;
                }

                // Validate contributions checkbox
                if (contributionsCheckbox.checked && !document.querySelector('select[name="contribution_product_id"]').value) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Validation Error',
                        text: 'Please select a Contribution Product when Contributions option is checked.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                    return false;
                }

                // Prevent multiple submissions
                if (form.dataset.submitting === 'true') {
                    e.preventDefault();
                    return false;
                }

                form.dataset.submitting = 'true';
                submitBtn.disabled = true;
                submitText.textContent = 'Uploading...';
                submitBtn.innerHTML = '<i class="bx bx-loader-alt bx-spin me-1"></i>Uploading...';
            });
        });
    </script>
@endpush