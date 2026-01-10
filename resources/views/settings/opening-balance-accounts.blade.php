@extends('layouts.main')

@section('title', 'Opening Balance Accounts Settings')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Settings', 'url' => route('settings.index'), 'icon' => 'bx bx-cog'],
            ['label' => 'Opening Balance Accounts', 'url' => '#', 'icon' => 'bx bx-bar-chart-alt-2']
        ]" />
        
        <h6 class="mb-0 text-uppercase">OPENING BALANCE ACCOUNTS SETTINGS</h6>
        <hr />

        <div class="row">
            <!-- Left Column: Form -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bx bx-bar-chart-alt-2 me-2"></i>Opening Balances Account Settings</h5>
                    </div>
                    <div class="card-body">
                        @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bx bx-check-circle me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        @endif

                        @if(isset($errors) && $errors->any())
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

                        <form action="{{ route('settings.opening-balance-accounts.update') }}" method="POST" id="openingBalanceForm">
                            @csrf
                            @method('PUT')

                            <!-- Existing Settings Lines -->
                            <div id="settingsLines">
                                <!-- Shares -->
                                <div class="settings-line mb-3 border-bottom pb-3" data-line-index="0">
                                    <div class="row align-items-end">
                                        <div class="col-md-5 mb-3">
                                            <label class="form-label">Category <span class="text-danger">*</span></label>
                                            <select name="lines[0][category]" class="form-select category-select" required>
                                                <option value="Shares" selected>Shares</option>
                                                <option value="Contributions">Contributions</option>
                                                <option value="Loans">Loans</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Chart Account <span class="text-danger">*</span></label>
                                            <select name="lines[0][chart_account_id]" class="form-select chart-account-select" required>
                                                <option value="">Select Chart Account</option>
                                                @foreach($chartAccounts as $account)
                                                <option value="{{ $account->id }}" 
                                                    {{ old('lines.0.chart_account_id', $sharesOpeningBalanceAccountId) == $account->id ? 'selected' : '' }}>
                                                    {{ $account->account_name }} ({{ $account->account_code }})
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-1 mb-3 text-end">
                                            <button type="button" class="btn btn-danger btn-sm remove-line-btn" style="display: none;">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Savings/Contributions -->
                                <div class="settings-line mb-3 border-bottom pb-3" data-line-index="1">
                                    <div class="row align-items-end">
                                        <div class="col-md-5 mb-3">
                                            <label class="form-label">Category <span class="text-danger">*</span></label>
                                            <select name="lines[1][category]" class="form-select category-select" required>
                                                <option value="Shares">Shares</option>
                                                <option value="Contributions" selected>Contributions</option>
                                                <option value="Loans">Loans</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Chart Account <span class="text-danger">*</span></label>
                                            <select name="lines[1][chart_account_id]" class="form-select chart-account-select" required>
                                                <option value="">Select Chart Account</option>
                                                @foreach($chartAccounts as $account)
                                                <option value="{{ $account->id }}" 
                                                    {{ old('lines.1.chart_account_id', $savingsOpeningBalanceAccountId) == $account->id ? 'selected' : '' }}>
                                                    {{ $account->account_name }} ({{ $account->account_code }})
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-1 mb-3 text-end">
                                            <button type="button" class="btn btn-danger btn-sm remove-line-btn" style="display: none;">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Deposits -->
                                <div class="settings-line mb-3 border-bottom pb-3" data-line-index="2">
                                    <div class="row align-items-end">
                                        <div class="col-md-5 mb-3">
                                            <label class="form-label">Category <span class="text-danger">*</span></label>
                                            <select name="lines[2][category]" class="form-select category-select" required>
                                                <option value="Shares">Shares</option>
                                                <option value="Contributions">Contributions</option>
                                                <option value="Loans" selected>Loans</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Chart Account <span class="text-danger">*</span></label>
                                            <select name="lines[2][chart_account_id]" class="form-select chart-account-select" required>
                                                <option value="">Select Chart Account</option>
                                                @foreach($chartAccounts as $account)
                                                <option value="{{ $account->id }}" 
                                                    {{ old('lines.2.chart_account_id', $depositsOpeningBalanceAccountId) == $account->id ? 'selected' : '' }}>
                                                    {{ $account->account_name }} ({{ $account->account_code }})
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-1 mb-3 text-end">
                                            <button type="button" class="btn btn-danger btn-sm remove-line-btn" style="display: none;">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Add Line Button -->
                            <div class="mb-3">
                                <button type="button" class="btn btn-success btn-sm" id="addLineBtn">
                                    <i class="bx bx-plus me-1"></i> Add Line
                                </button>
                            </div>

                            <!-- Form Actions -->
                            <div class="d-flex justify-content-end gap-2 mt-4">
                                <a href="{{ route('settings.index') }}" class="btn btn-secondary">
                                    <i class="bx bx-arrow-back me-1"></i> Cancel
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-save me-1"></i> Save Settings
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Right Column: Information & Guidelines -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="bx bx-info-circle me-2"></i>Information & Guidelines</h5>
                    </div>
                    <div class="card-body">
                        <h6 class="fw-bold mb-3">About Opening Balance Accounts</h6>
                        <p class="text-muted mb-3">
                            Opening balance accounts are chart accounts used to record initial balances when importing historical data for shares, contributions, and loans.
                        </p>

                        <h6 class="fw-bold mb-3">Categories</h6>
                        <ul class="list-unstyled mb-3">
                            <li class="mb-2">
                                <strong class="text-primary">Shares:</strong>
                                <span class="text-muted">Chart account for share opening balances</span>
                            </li>
                            <li class="mb-2">
                                <strong class="text-success">Contributions:</strong>
                                <span class="text-muted">Chart account for contribution opening balances</span>
                            </li>
                            <li class="mb-2">
                                <strong class="text-warning">Loans:</strong>
                                <span class="text-muted">Chart account for loan opening balances</span>
                            </li>
                        </ul>

                        <h6 class="fw-bold mb-3">Guidelines</h6>
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <i class="bx bx-check-circle text-success me-2"></i>
                                Select appropriate chart accounts for each category
                            </li>
                            <li class="mb-2">
                                <i class="bx bx-check-circle text-success me-2"></i>
                                You can add multiple lines for the same category if needed
                            </li>
                            <li class="mb-2">
                                <i class="bx bx-check-circle text-success me-2"></i>
                                Chart accounts should be liability accounts for member balances
                            </li>
                            <li class="mb-2">
                                <i class="bx bx-check-circle text-success me-2"></i>
                                Changes take effect immediately after saving
                            </li>
                        </ul>

                        <div class="alert alert-warning mt-3 mb-0">
                            <i class="bx bx-info-circle me-2"></i>
                            <small><strong>Note:</strong> Make sure the selected chart accounts are properly configured in your chart of accounts.</small>
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
        let lineIndex = 3; // Start from 3 since we have 3 default lines

        // Initialize Select2 for chart account dropdowns
        $('.chart-account-select').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: 'Search and select chart account...',
            allowClear: true
        });

        // Show remove button if more than one line
        function updateRemoveButtons() {
            const lines = $('.settings-line');
            if (lines.length > 1) {
                $('.remove-line-btn').show();
            } else {
                $('.remove-line-btn').hide();
            }
        }

        // Add Line Button Click Handler
        $('#addLineBtn').on('click', function() {
            const template = `
                <div class="settings-line mb-3 border-bottom pb-3" data-line-index="${lineIndex}">
                    <div class="row align-items-end">
                        <div class="col-md-5 mb-3">
                            <label class="form-label">Category <span class="text-danger">*</span></label>
                            <select name="lines[${lineIndex}][category]" class="form-select category-select" required>
                                <option value="">Select Category</option>
                                <option value="Shares">Shares</option>
                                <option value="Contributions">Contributions</option>
                                <option value="Loans">Loans</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Chart Account <span class="text-danger">*</span></label>
                            <select name="lines[${lineIndex}][chart_account_id]" class="form-select chart-account-select" required>
                                <option value="">Select Chart Account</option>
                                @foreach($chartAccounts as $account)
                                <option value="{{ $account->id }}">
                                    {{ $account->account_name }} ({{ $account->account_code }})
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-1 mb-3 text-end">
                            <button type="button" class="btn btn-danger btn-sm remove-line-btn">
                                <i class="bx bx-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;

            $('#settingsLines').append(template);
            
            // Initialize Select2 for the new chart account dropdown
            $('#settingsLines .settings-line:last .chart-account-select').select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: 'Search and select chart account...',
                allowClear: true
            });

            lineIndex++;
            updateRemoveButtons();
        });

        // Remove Line Button Click Handler
        $(document).on('click', '.remove-line-btn', function() {
            $(this).closest('.settings-line').remove();
            updateRemoveButtons();
            
            // Re-index remaining lines
            $('#settingsLines .settings-line').each(function(index) {
                $(this).attr('data-line-index', index);
                $(this).find('select, input').each(function() {
                    const name = $(this).attr('name');
                    if (name) {
                        $(this).attr('name', name.replace(/lines\[\d+\]/, `lines[${index}]`));
                    }
                });
            });
        });

        // Initial update of remove buttons
        updateRemoveButtons();
    });
</script>
@endpush
