@php
    use Vinkla\Hashids\Facades\Hashids;
@endphp

<!doctype html>
<html lang="{{ app()->getLocale() }}" class="color-sidebar sidebarcolor3">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="user-id" content="{{ auth()->id() }}">

    <title>@yield('title', 'Connect – Dashboard')</title>

    <!--favicon-->
    <link rel="icon" href="{{ asset('assets/images/favicon-32x32.png') }}" type="image/png" />

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

    <!-- DataTables Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <!-- DataTables Responsive CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    <!-- DataTables Buttons CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">

    <!-- Plugins CSS -->
    <link href="{{ asset('assets/plugins/simplebar/css/simplebar.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/plugins/perfect-scrollbar/css/perfect-scrollbar.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/plugins/metismenu/css/metisMenu.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/plugins/highcharts/css/highcharts.css') }}" rel="stylesheet" />

    <!-- Loader CSS -->
    <link href="{{ asset('assets/css/pace.min.css') }}" rel="stylesheet" />

    <!-- Bootstrap & Theme CSS -->
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/bootstrap-extended.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/icons.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/dark-theme.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/semi-dark.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/header-colors.css') }}" rel="stylesheet" />

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    
    @stack('styles')
</head>

<body>
    <div class="wrapper">
        {{-- Include Navigation and Header --}}
        @include('incs.sideMenu')
        @include('incs.navBar')

        {{-- Main Content --}}
        @yield('content')
    </div>

    <!-- Scripts -->

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Bootstrap Bundle -->
    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>

    <!-- Plugins -->
    <script src="{{ asset('assets/plugins/simplebar/js/simplebar.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/metismenu/js/metisMenu.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/perfect-scrollbar/js/perfect-scrollbar.js') }}"></script>

    <!-- Highcharts -->
    <script src="{{ asset('assets/plugins/highcharts/js/highcharts.js') }}"></script>
    <script src="{{ asset('assets/plugins/highcharts/js/highcharts-more.js') }}"></script>
    <script src="{{ asset('assets/plugins/highcharts/js/variable-pie.js') }}"></script>
    <script src="{{ asset('assets/plugins/highcharts/js/solid-gauge.js') }}"></script>
    <script src="{{ asset('assets/plugins/highcharts/js/highcharts-3d.js') }}"></script>
    <script src="{{ asset('assets/plugins/highcharts/js/cylinder.js') }}"></script>
    <script src="{{ asset('assets/plugins/highcharts/js/funnel3d.js') }}"></script>
    <script src="{{ asset('assets/plugins/highcharts/js/exporting.js') }}"></script>
    <script src="{{ asset('assets/plugins/highcharts/js/export-data.js') }}"></script>
    <script src="{{ asset('assets/plugins/highcharts/js/accessibility.js') }}"></script>

    <!-- Global Error Handlers -->
    <script>
        // Fix Highcharts error #13 globally
        window.addEventListener('load', function() {
            if (typeof Highcharts !== 'undefined') {
                Highcharts.error = function(code, stop) {
                    if (code === 13) {
                        console.warn('Highcharts error #13: Container not found, skipping chart rendering');
                        return;
                    }
                    console.error('Highcharts error #' + code);
                };
            }
        });
        
        // Fix DataTables column count issues globally
        $(document).ready(function() {
            // Override DataTables initialization to handle column count errors
            $.fn.dataTable.ext.errMode = 'throw';
            
            // Add error handler for DataTables
            $(document).on('error.dt', function(e, settings, techNote, message) {
                if (message && message.includes('column count')) {
                    console.warn('DataTables column count warning suppressed for table:', settings.nTable.id);
                    return false; // Prevent the error from being thrown
                }
            });
        });
    </script>

    <script src="{{ asset('assets/js/index4.js') }}"></script>

    <!-- DataTables Core -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    
    <!-- DataTables Responsive -->
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

    <!-- DataTables Buttons -->
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>

    <!-- JSZip and PDFMake for export -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

    <!-- Export Buttons -->
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function () {
            // Initialize first table without buttons
            $('#example').DataTable();

            // Initialize second table with buttons
            var table2 = $('#example2').DataTable({
                lengthChange: false,
                buttons: ['copy', 'excel', 'pdf', 'print']
            });

            // Place buttons container in the DOM
            table2.buttons().container()
                .appendTo('#example2_wrapper .col-md-6:eq(0)');

            // Select2 init
            $('.select2-single').select2({
                placeholder: 'Select',
                allowClear: true,
                width: '100%',
                theme: 'bootstrap-5'
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.delete-form').forEach(form => {
                form.addEventListener('submit', function (e) {
                    e.preventDefault();

                    const name = this.querySelector('button').getAttribute('data-name');

                    Swal.fire({
                        title: `Delete "${name}"?`,
                        text: "This action cannot be undone!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Yes, delete it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            this.submit();
                        }
                    });
                });
            });
        });
    </script>

    @if(session('success'))
        <script>
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: '{{ session('success') }}',
                showConfirmButton: false,
                timer: 3000
            });
        </script>
    @endif

    @if(session('error'))
        <script>
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'error',
                title: '{{ session('error') }}',
                showConfirmButton: false,
                timer: 4000
            });
        </script>
    @endif

    <script>
        function previewImage(event) {
            const reader = new FileReader();
            reader.onload = function () {
                const output = document.getElementById('preview');
                output.innerHTML = `<img src="${reader.result}" width="100">`;
            }
            reader.readAsDataURL(event.target.files[0]);
        }

        document.getElementById('region')?.addEventListener('change', function () {
            const region = this.value;
            fetch(`/get-districts/${region}`)
                .then(res => res.json())
                .then(data => {
                    let options = `<option value="">Select District</option>`;
                    data.forEach(district => {
                        options += `<option value="${district.name}">${district.name}</option>`;
                    });
                    document.getElementById('district').innerHTML = options;
                });
        });
    </script>

    <!-- App JS -->
    <script src="{{ asset('assets/js/app.js') }}"></script>

    <!-- Global Form Submit Handler with Loading Spinner -->
    <script>
        (function() {
            'use strict';
            
            /**
             * Initialize form submit button loading states
             * Prevents double-clicking and shows loading spinner
             */
            function initFormSubmitHandlers() {
                // Find all forms with submit buttons
                document.querySelectorAll('form').forEach(function(form) {
                    // Skip if form already has custom submit handler (has data attribute)
                    if (form.dataset.hasCustomHandler === 'true' || form.hasAttribute('data-has-custom-handler')) {
                        return;
                    }
                    
                    // Find submit buttons in this form
                    const submitButtons = form.querySelectorAll('button[type="submit"], input[type="submit"]');
                    
                    if (submitButtons.length === 0) {
                        return;
                    }
                    
                    // Track if form is submitting
                    let isSubmitting = false;
                    
                    // Store original button states
                    const buttonStates = new Map();
                    submitButtons.forEach(function(btn) {
                        buttonStates.set(btn, {
                            originalHTML: btn.innerHTML || btn.value,
                            originalDisabled: btn.disabled,
                            originalText: btn.textContent || btn.value
                        });
                    });
                    
                    form.addEventListener('submit', function(e) {
                        // Prevent multiple submissions
                        if (isSubmitting) {
                            e.preventDefault();
                            e.stopPropagation();
                            return false;
                        }
                        
                        // Mark as submitting
                        isSubmitting = true;
                        
                        // Disable all submit buttons and show loading state
                        submitButtons.forEach(function(btn) {
                            const state = buttonStates.get(btn);
                            
                            // Disable button
                            btn.disabled = true;
                            btn.setAttribute('aria-disabled', 'true');
                            btn.classList.add('opacity-75', 'cursor-not-allowed');
                            
                            // Add loading spinner
                            if (btn.tagName === 'BUTTON') {
                                const originalHTML = state.originalHTML;
                                // Check if button already has spinner
                                if (!originalHTML.includes('bx-loader-alt') && !originalHTML.includes('bx-spin')) {
                                    // Preserve button structure if it has btn-text span
                                    if (originalHTML.includes('btn-text')) {
                                        const btnTextSpan = btn.querySelector('.btn-text');
                                        if (btnTextSpan) {
                                            btnTextSpan.textContent = 'Processing...';
                                            // Add spinner before the span if not already present
                                            if (!btn.querySelector('.bx-loader-alt')) {
                                                const spinner = document.createElement('i');
                                                spinner.className = 'bx bx-loader-alt bx-spin me-1';
                                                btn.insertBefore(spinner, btnTextSpan);
                                            }
                                        } else {
                                            btn.innerHTML = '<i class="bx bx-loader-alt bx-spin me-1"></i> <span class="btn-text">Processing...</span>';
                                        }
                                    } else {
                                        btn.innerHTML = '<i class="bx bx-loader-alt bx-spin me-1"></i> Processing...';
                                    }
                                }
                            } else if (btn.tagName === 'INPUT') {
                                btn.value = 'Processing...';
                            }
                        });
                        
                        // Use readonly for text inputs and textareas instead of disabled
                        // This prevents changes but still allows values to be submitted
                        const textInputs = form.querySelectorAll('input[type="text"], input[type="email"], input[type="number"], input[type="date"], input[type="tel"], textarea');
                        textInputs.forEach(function(input) {
                            if (!input.readOnly) {
                                input.setAttribute('data-original-readonly', input.readOnly);
                                input.readOnly = true;
                                input.classList.add('bg-light');
                            }
                        });
                        
                        // For selects, use a visual indicator but don't disable (disabled selects don't submit values)
                        // Instead, add a visual overlay or pointer-events-none
                        const selects = form.querySelectorAll('select');
                        selects.forEach(function(select) {
                            if (!select.disabled) {
                                select.setAttribute('data-original-disabled', select.disabled);
                                select.style.pointerEvents = 'none';
                                select.style.opacity = '0.6';
                                select.classList.add('bg-light');
                            }
                        });
                        
                        // Disable checkboxes and radio buttons (these can be disabled as they have boolean values)
                        const checkboxesRadios = form.querySelectorAll('input[type="checkbox"], input[type="radio"]');
                        checkboxesRadios.forEach(function(input) {
                            if (!input.disabled) {
                                input.setAttribute('data-original-disabled', input.disabled);
                                input.disabled = true;
                            }
                        });
                        
                        // For regular form submissions, reset on page reload/redirect
                        // For AJAX forms, they should call form._resetSubmitState() on error
                        
                        // Timeout protection - re-enable after 30 seconds if still submitting
                        const timeoutId = setTimeout(function() {
                            if (isSubmitting) {
                                console.warn('Form submission timeout - re-enabling form');
                                resetFormState();
                            }
                        }, 30000);
                        
                        // Store timeout ID for cleanup
                        form._submitTimeoutId = timeoutId;
                    });
                    
                    // Reset form state function
                    function resetFormState() {
                        if (!isSubmitting) return; // Already reset
                        
                        isSubmitting = false;
                        
                        // Clear timeout if exists
                        if (form._submitTimeoutId) {
                            clearTimeout(form._submitTimeoutId);
                            form._submitTimeoutId = null;
                        }
                        
                        submitButtons.forEach(function(btn) {
                            const state = buttonStates.get(btn);
                            
                            btn.disabled = state.originalDisabled;
                            btn.removeAttribute('aria-disabled');
                            btn.classList.remove('opacity-75', 'cursor-not-allowed');
                            
                            if (btn.tagName === 'BUTTON') {
                                btn.innerHTML = state.originalHTML;
                            } else if (btn.tagName === 'INPUT') {
                                btn.value = state.originalText;
                            }
                        });
                        
                        // Re-enable form inputs
                        // Restore readonly state for text inputs
                        form.querySelectorAll('input[type="text"], input[type="email"], input[type="number"], input[type="date"], input[type="tel"], textarea').forEach(function(input) {
                            if (input.hasAttribute('data-original-readonly')) {
                                input.readOnly = input.getAttribute('data-original-readonly') === 'true';
                                input.removeAttribute('data-original-readonly');
                                input.classList.remove('bg-light');
                            }
                        });
                        
                        // Restore select state
                        form.querySelectorAll('select').forEach(function(select) {
                            if (select.hasAttribute('data-original-disabled')) {
                                select.style.pointerEvents = '';
                                select.style.opacity = '';
                                select.classList.remove('bg-light');
                                select.removeAttribute('data-original-disabled');
                            }
                        });
                        
                        // Restore disabled state for checkboxes and radio buttons
                        form.querySelectorAll('input[type="checkbox"], input[type="radio"]').forEach(function(input) {
                            if (input.hasAttribute('data-original-disabled')) {
                                input.disabled = input.getAttribute('data-original-disabled') === 'true';
                                input.removeAttribute('data-original-disabled');
                            }
                        });
                    }
                    
                    // Store reset function for external access if needed
                    form._resetSubmitState = resetFormState;
                });
            }
            
            // Initialize on DOM ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initFormSubmitHandlers);
            } else {
                initFormSubmitHandlers();
            }
            
            // Re-initialize for dynamically added forms
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    mutation.addedNodes.forEach(function(node) {
                        if (node.nodeType === 1 && (node.tagName === 'FORM' || node.querySelector('form'))) {
                            initFormSubmitHandlers();
                        }
                    });
                });
            });
            
            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
        })();
    </script>

    @stack('scripts')
</body>

</html>
