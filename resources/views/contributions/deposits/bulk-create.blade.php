@extends('layouts.main')

@section('title', 'Bulk Contribution Deposits')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Contributions', 'url' => route('contributions.index'), 'icon' => 'bx bx-donate-heart'],
            ['label' => 'Contributions Deposits', 'url' => route('contributions.deposits.index'), 'icon' => 'bx bx-down-arrow-circle'],
            ['label' => 'Bulk Deposit', 'url' => '#', 'icon' => 'bx bx-upload']
        ]" />

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0 text-uppercase text-info">Bulk Contribution Deposits</h6>
                <a href="{{ route('contributions.deposits.index') }}" class="btn btn-info">
                    <i class="bx bx-list-ul me-1"></i> Deposits List
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

                            <form id="bulkDepositForm" enctype="multipart/form-data">
                                @csrf

                                <div class="row">
                                    <!-- Contribution Product -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Contribution Product <span
                                                class="text-danger">*</span></label>
                                        <select name="contribution_product_id" id="contribution_product_id"
                                            class="form-select @error('contribution_product_id') is-invalid @enderror"
                                            required>
                                            <option value="">Select Contribution Product</option>
                                            @foreach($products as $product)
                                                <option value="{{ $product->id }}" {{ old('contribution_product_id') == $product->id ? 'selected' : '' }}>
                                                    {{ $product->product_name }} ({{ $product->category }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('contribution_product_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Bank Account -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Bank Account <span class="text-danger">*</span></label>
                                        <select name="bank_account_id" id="bank_account_id"
                                            class="form-select @error('bank_account_id') is-invalid @enderror" required>
                                            <option value="">Select Bank Account</option>
                                            @foreach($bankAccounts as $bankAccount)
                                                <option value="{{ $bankAccount->id }}" {{ old('bank_account_id') == $bankAccount->id ? 'selected' : '' }}>
                                                    {{ $bankAccount->name }} ({{ $bankAccount->account_number }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('bank_account_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Download Template -->
                                    <div class="col-md-12 mb-3">
                                        <div class="alert alert-info">
                                            <i class="bx bx-info-circle me-2"></i>
                                            <strong>Instructions:</strong>
                                            <ol class="mb-0 mt-2">
                                                <li>Select a contribution product and bank account</li>
                                                <li>Download the Excel template below</li>
                                                <li>Fill in the template with customer_id, amount, date, and description
                                                </li>
                                                <li>Upload the completed file</li>
                                            </ol>
                                        </div>
                                        <button type="button" id="downloadTemplateBtn" class="btn btn-outline-primary">
                                            <i class="bx bx-download me-1"></i> Download Template
                                        </button>
                                    </div>

                                    <!-- File Upload -->
                                    <div class="col-md-12 mb-3">
                                        <label class="form-label">Upload Excel File <span
                                                class="text-danger">*</span></label>
                                        <input type="file" name="import_file" id="import_file"
                                            class="form-control @error('import_file') is-invalid @enderror"
                                            accept=".xlsx,.xls" required>
                                        <small class="form-text text-muted">Only .xlsx and .xls files are allowed</small>
                                        @error('import_file')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Submit Button -->
                                <div class="d-flex justify-content-end mt-4">
                                    <a href="{{ route('contributions.deposits.index') }}" class="btn btn-secondary me-2">
                                        <i class="bx bx-arrow-back me-1"></i> Cancel
                                    </a>
                                    <button type="submit" class="btn btn-info" id="submitBtn">
                                        <i class="bx bx-upload me-1"></i> Upload & Process
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
                                <h6 class="text-primary">Excel Template Format</h6>
                                <p class="small mb-2">The template should contain the following columns:</p>
                                <ul class="list-unstyled mb-0 small">
                                    <li><strong>customer_id:</strong> The ID of the customer (pre-filled from system)</li>
                                    <li><strong>customer_name:</strong> The name of the customer (pre-filled from system,
                                        for reference only)</li>
                                    <li><strong>amount:</strong> Deposit amount (must be > 0)</li>
                                    <li><strong>date:</strong> Transaction date (YYYY-MM-DD format)</li>
                                    <li><strong>description:</strong> Optional description</li>
                                </ul>
                            </div>

                            <hr>

                            <div class="alert alert-warning mb-0">
                                <small>
                                    <i class="bx bx-info-circle me-1"></i>
                                    <strong>Note:</strong> Large files (2000+ records) will be processed in the background.
                                    You can track progress on this page.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Progress Section (Hidden Initially) -->
            <div class="row mt-4" id="progressSection" style="display: none;">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0"><i class="bx bx-loader-alt bx-spin me-2"></i>Processing Bulk Deposits</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted" id="statusText">Initializing...</span>
                                    <span class="fw-bold" id="percentageText">0%</span>
                                </div>
                                <div class="progress" style="height: 25px;">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                                        id="progressBar" style="width: 0%" aria-valuenow="0" aria-valuemin="0"
                                        aria-valuemax="100">
                                        <span id="progressText">0%</span>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-4">
                                    <div class="stat-card border rounded p-3">
                                        <div class="d-flex align-items-center">
                                            <div class="stat-icon bg-success text-white me-3 rounded-circle d-flex align-items-center justify-content-center"
                                                style="width: 40px; height: 40px;">
                                                <i class="bx bx-check-circle"></i>
                                            </div>
                                            <div>
                                                <div class="text-muted small">Success</div>
                                                <div class="fs-4 fw-bold text-success" id="successCount">0</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="stat-card border rounded p-3">
                                        <div class="d-flex align-items-center">
                                            <div class="stat-icon bg-danger text-white me-3 rounded-circle d-flex align-items-center justify-content-center"
                                                style="width: 40px; height: 40px;">
                                                <i class="bx bx-x-circle"></i>
                                            </div>
                                            <div>
                                                <div class="text-muted small">Failed</div>
                                                <div class="fs-4 fw-bold text-danger" id="failedCount">0</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="stat-card border rounded p-3">
                                        <div class="d-flex align-items-center">
                                            <div class="stat-icon bg-info text-white me-3 rounded-circle d-flex align-items-center justify-content-center"
                                                style="width: 40px; height: 40px;">
                                                <i class="bx bx-file"></i>
                                            </div>
                                            <div>
                                                <div class="text-muted small">Total</div>
                                                <div class="fs-4 fw-bold text-info" id="totalCount">0</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div id="failedRecordsSection" style="display: none;" class="mt-4">
                                <div class="alert alert-warning">
                                    <i class="bx bx-error-circle me-2"></i>
                                    <strong>Some records failed to import.</strong> Click the button below to download the
                                    failed records with error reasons. You will be redirected to the deposits list after
                                    download.
                                </div>
                                <a href="#" id="downloadFailedBtn" class="btn btn-danger">
                                    <i class="bx bx-download me-1"></i> Download Failed Records
                                </a>
                                <a href="{{ route('contributions.deposits.index') }}" class="btn btn-info ms-2">
                                    <i class="bx bx-arrow-back me-1"></i> Back to Deposits
                                </a>
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
        $(document).ready(function () {
            let progressInterval = null;
            let currentJobId = null;

            // Initialize Select2 for dropdowns
            $('#contribution_product_id, #bank_account_id').select2({
                placeholder: 'Select an option',
                allowClear: true,
                width: '100%',
                theme: 'bootstrap-5'
            });

            // Download template button
            $('#downloadTemplateBtn').on('click', function () {
                const productId = $('#contribution_product_id').val();
                if (!productId) {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Please select a contribution product first.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                const url = '{{ route("contributions.deposits.download-template") }}?contribution_product_id=' + productId;
                window.location.href = url;
            });

            // Form submission
            $('#bulkDepositForm').on('submit', function (e) {
                e.preventDefault();

                const form = $(this)[0];
                const $form = $(this);
                const submitBtn = $('#submitBtn');
                const originalHTML = submitBtn.html();

                // Validate form
                const productId = $('#contribution_product_id').val();
                const bankAccountId = $('#bank_account_id').val();
                const fileInput = $('#import_file')[0];

                if (!productId) {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Please select a contribution product.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                if (!bankAccountId) {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Please select a bank account.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                if (!fileInput.files || fileInput.files.length === 0) {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Please select an Excel file to upload.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                // Create FormData
                const formData = new FormData();
                formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
                formData.append('contribution_product_id', productId);
                formData.append('bank_account_id', bankAccountId);
                formData.append('import_file', fileInput.files[0]);

                // Disable submit button
                submitBtn.prop('disabled', true);
                submitBtn.html('<i class="bx bx-loader-alt bx-spin me-1"></i> Uploading...');

                // Submit form via AJAX
                $.ajax({
                    url: '{{ route("contributions.deposits.bulk-store") }}',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        if (response.success) {
                            currentJobId = response.job_id;
                            $('#progressSection').show();
                            startProgressPolling(currentJobId);
                            Swal.fire({
                                title: 'Success!',
                                text: response.message,
                                icon: 'success',
                                confirmButtonText: 'OK'
                            });
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: response.error || 'Failed to start import',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                            submitBtn.prop('disabled', false);
                            submitBtn.html(originalHTML);
                        }
                    },
                    error: function (xhr) {
                        let errorMessage = 'Failed to upload file';
                        if (xhr.responseJSON && xhr.responseJSON.error) {
                            errorMessage = xhr.responseJSON.error;
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        Swal.fire({
                            title: 'Error!',
                            text: errorMessage,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                        submitBtn.prop('disabled', false);
                        submitBtn.html(originalHTML);
                    }
                });
            });

            // Start polling for progress
            function startProgressPolling(jobId) {
                // Clear any existing interval
                if (progressInterval) {
                    clearInterval(progressInterval);
                }

                // Poll immediately
                checkProgress(jobId);

                // Then poll every 2 seconds
                progressInterval = setInterval(function () {
                    checkProgress(jobId);
                }, 2000);
            }

            // Check progress
            function checkProgress(jobId) {
                $.ajax({
                    url: '{{ route("contributions.deposits.bulk-progress", ":jobId") }}'.replace(':jobId', jobId),
                    type: 'GET',
                    success: function (response) {
                        if (response.success && response.progress) {
                            const progress = response.progress;
                            updateProgressUI(progress);

                            // Stop polling if completed or failed
                            if (progress.status === 'completed' || progress.status === 'failed') {
                                if (progressInterval) {
                                    clearInterval(progressInterval);
                                    progressInterval = null;
                                }

                                // Update download failed button URL
                                if (progress.failed > 0) {
                                    const downloadUrl = '{{ route("contributions.deposits.download-failed", ":jobId") }}'.replace(':jobId', jobId);
                                    $('#downloadFailedBtn').attr('href', downloadUrl);
                                    $('#failedRecordsSection').show();

                                    // Handle download click to redirect after download
                                    $('#downloadFailedBtn').off('click').on('click', function (e) {
                                        e.preventDefault();
                                        // Show loading message
                                        const btn = $(this);
                                        const originalText = btn.html();
                                        btn.html('<i class="bx bx-loader-alt bx-spin me-1"></i> Downloading...');
                                        btn.prop('disabled', true);

                                        // Create a temporary form to trigger download
                                        const form = $('<form>', {
                                            'method': 'GET',
                                            'action': downloadUrl
                                        });
                                        $('body').append(form);
                                        form.submit();

                                        // Redirect after download starts (give it time to download)
                                        setTimeout(function () {
                                            window.location.href = '{{ route("contributions.deposits.index") }}';
                                        }, 2000);
                                    });
                                }

                                // Show completion message
                                if (progress.status === 'completed') {
                                    if (progress.failed > 0) {
                                        // Show message but don't redirect yet - let user download failed records first
                                        Swal.fire({
                                            title: 'Completed!',
                                            text: `Processing completed. Success: ${progress.success}, Failed: ${progress.failed}. You can download failed records below.`,
                                            icon: 'success',
                                            confirmButtonText: 'OK'
                                        });
                                    } else {
                                        // All succeeded - redirect immediately
                                        Swal.fire({
                                            title: 'Completed!',
                                            text: `All ${progress.success} deposits processed successfully!`,
                                            icon: 'success',
                                            confirmButtonText: 'OK'
                                        }).then((result) => {
                                            window.location.href = '{{ route("contributions.deposits.index") }}';
                                        });
                                    }
                                } else if (progress.status === 'failed') {
                                    Swal.fire({
                                        title: 'Failed!',
                                        text: 'Processing failed. Please try again.',
                                        icon: 'error',
                                        confirmButtonText: 'OK'
                                    }).then((result) => {
                                        window.location.href = '{{ route("contributions.deposits.index") }}';
                                    });
                                }
                            }
                        }
                    },
                    error: function (xhr) {
                        console.error('Error checking progress:', xhr);
                    }
                });
            }

            // Update progress UI
            function updateProgressUI(progress) {
                const percentage = progress.percentage || 0;
                const processed = progress.processed || 0;
                const total = progress.total || 0;
                const success = progress.success || 0;
                const failed = progress.failed || 0;
                const status = progress.status || 'processing';

                // Update progress bar
                $('#progressBar').css('width', percentage + '%').attr('aria-valuenow', percentage);
                $('#progressText').text(percentage.toFixed(1) + '%');
                $('#percentageText').text(percentage.toFixed(1) + '%');

                // Update status text
                if (status === 'processing') {
                    $('#statusText').text(`Processing ${processed} of ${total} records...`);
                } else if (status === 'completed') {
                    $('#statusText').text('Processing completed!');
                    $('#progressBar').removeClass('progress-bar-animated');
                } else if (status === 'failed') {
                    $('#statusText').text('Processing failed!');
                    $('#progressBar').removeClass('progress-bar-animated').addClass('bg-danger');
                }

                // Update counts
                $('#successCount').text(success);
                $('#failedCount').text(failed);
                $('#totalCount').text(total);
            }
        });
    </script>
@endpush