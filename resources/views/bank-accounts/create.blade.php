@extends('layouts.main')

@section('title', 'Create Bank Account')
@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Bank Accounts', 'url' => route('accounting.bank-accounts'), 'icon' => 'bx bx-bank'],
            ['label' => 'Create Account', 'url' => '#', 'icon' => 'bx bx-plus-circle']
        ]" />

            <!-- Header Section -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0 text-dark fw-bold">
                                <i class="bx bx-bank me-2 text-primary"></i>
                                Create New Bank Account
                            </h4>
                        </div>
                        @can('view bank accounts')
                        <div>
                            <a href="{{ route('accounting.bank-accounts') }}" class="btn btn-outline-secondary">
                                <i class="bx bx-arrow-back me-1"></i> Back to List
                            </a>
                        </div>
                        @endcan
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="bx bx-plus-circle me-2"></i>
                                Bank Account Information
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            @include('bank-accounts.form')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!--end page wrapper -->
    <!--start overlay-->
    <div class="overlay toggle-icon"></div>
    <!--end overlay-->
    <!--Start Back To Top Button--> <a href="javaScript:;" class="back-to-top"><i class='bx bxs-up-arrow-alt'></i></a>
    <!--End Back To Top Button-->
    <footer class="page-footer">
        <p class="mb-0">Copyright © 2021. All right reserved.</p>
    </footer>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize Select2 for chart account
        $('#chart_account_id').select2({
            placeholder: 'Choose Chart Account',
            allowClear: true,
            width: '100%',
            theme: 'bootstrap-5'
        });

        // Form submission handler
        $('#bankAccountForm').on('submit', function(e) {
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
            if ($('#chart_account_id').length && $('#chart_account_id').data('select2')) {
                $('#chart_account_id').trigger('change');
            }

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

            // Ensure method field is present for PUT requests
            if ($form.find('input[name="_method"]').length === 0 && $form.attr('method').toUpperCase() === 'POST') {
                // Check if this is an update form
                const action = $form.attr('action');
                if (action && action.includes('/edit') || action.includes('update')) {
                    $form.append('<input type="hidden" name="_method" value="PUT">');
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