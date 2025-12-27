@extends('layouts.main')
@section('title', 'Create Chart Account')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Chart of Accounts', 'url' => route('accounting.chart-accounts.index'), 'icon' => 'bx bx-spreadsheet'],
            ['label' => 'Create Account', 'url' => '#', 'icon' => 'bx bx-plus']
             ]" />
            <h6 class="mb-0 text-uppercase">CREATE NEW CHART ACCOUNT</h6>
            <hr />
            <div class="row">
                <!-- Left Column: Form -->
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-body">
                            @include('chart-accounts.form')
                        </div>
                    </div>
                </div>
                
                <!-- Right Column: Information -->
                <div class="col-lg-4">
                    <div class="card border-info">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0"><i class="bx bx-info-circle me-2"></i>Information & Guidelines</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-4">
                                <h6 class="text-primary"><i class="bx bx-help-circle me-2"></i>How to Create a Chart Account</h6>
                                <ul class="list-unstyled mb-0">
                                    <li class="mb-2">
                                        <i class="bx bx-check-circle text-success me-2"></i>
                                        Select an Account Class Group from the dropdown
                                    </li>
                                    <li class="mb-2">
                                        <i class="bx bx-check-circle text-success me-2"></i>
                                        Enter an Account Code within the specified range
                                    </li>
                                    <li class="mb-2">
                                        <i class="bx bx-check-circle text-success me-2"></i>
                                        Provide a clear and descriptive Account Name
                                    </li>
                                    <li class="mb-2">
                                        <i class="bx bx-check-circle text-success me-2"></i>
                                        Check "Has Cash Flow Impact" if applicable
                                    </li>
                                    <li class="mb-2">
                                        <i class="bx bx-check-circle text-success me-2"></i>
                                        Check "Has Equity Impact" if applicable
                                    </li>
                                </ul>
                            </div>

                            <hr>

                            <div class="mb-4">
                                <h6 class="text-warning"><i class="bx bx-error-circle me-2"></i>Important Notes</h6>
                                <ul class="list-unstyled mb-0">
                                    <li class="mb-2">
                                        <i class="bx bx-info-circle text-info me-2"></i>
                                        Account codes must be unique across all accounts
                                    </li>
                                    <li class="mb-2">
                                        <i class="bx bx-info-circle text-info me-2"></i>
                                        The account code must fall within the range specified for the selected class
                                    </li>
                                    <li class="mb-2">
                                        <i class="bx bx-info-circle text-info me-2"></i>
                                        Account names should be clear and descriptive
                                    </li>
                                    <li class="mb-2">
                                        <i class="bx bx-info-circle text-info me-2"></i>
                                        Cash Flow and Equity categories are optional
                                    </li>
                                </ul>
                            </div>

                            <hr>

                            <div class="mb-3">
                                <h6 class="text-success"><i class="bx bx-bookmark me-2"></i>Account Class Groups</h6>
                                <p class="text-muted small mb-2">
                                    Account Class Groups organize accounts by their financial statement classification (Assets, Liabilities, Equity, Income, Expenses).
                                </p>
                                <p class="text-muted small mb-0">
                                    Each group belongs to an Account Class which defines the valid code range for accounts in that group.
                                </p>
                            </div>

                            <hr>

                            <div class="mb-3">
                                <h6 class="text-secondary"><i class="bx bx-calculator me-2"></i>Quick Tips</h6>
                                <ul class="list-unstyled mb-0 small">
                                    <li class="mb-2">
                                        <strong>Account Code:</strong> Use numeric codes that match your organization's chart of accounts structure
                                    </li>
                                    <li class="mb-2">
                                        <strong>Account Name:</strong> Use clear, descriptive names that will be easily understood by users
                                    </li>
                                    <li class="mb-0">
                                        <strong>Categories:</strong> Only select cash flow or equity categories if the account impacts those statements
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection