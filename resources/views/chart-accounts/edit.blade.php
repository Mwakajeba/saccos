@extends('layouts.main')
@section('title', 'Edit Chart Account')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Chart of Accounts', 'url' => route('accounting.chart-accounts.index'), 'icon' => 'bx bx-spreadsheet'],
            ['label' => 'Edit Account', 'url' => '#', 'icon' => 'bx bx-edit']
             ]" />
            <h6 class="mb-0 text-uppercase">EDIT CHART ACCOUNT</h6>
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
                                <h6 class="text-primary"><i class="bx bx-help-circle me-2"></i>Editing Chart Account</h6>
                                <ul class="list-unstyled mb-0">
                                    <li class="mb-2">
                                        <i class="bx bx-check-circle text-success me-2"></i>
                                        You can modify the Account Class Group if needed
                                    </li>
                                    <li class="mb-2">
                                        <i class="bx bx-check-circle text-success me-2"></i>
                                        Account Code must remain within the valid range
                                    </li>
                                    <li class="mb-2">
                                        <i class="bx bx-check-circle text-success me-2"></i>
                                        Update the Account Name to reflect changes
                                    </li>
                                    <li class="mb-2">
                                        <i class="bx bx-check-circle text-success me-2"></i>
                                        Adjust Cash Flow and Equity settings as needed
                                    </li>
                                </ul>
                            </div>

                            <hr>

                            <div class="mb-4">
                                <h6 class="text-warning"><i class="bx bx-error-circle me-2"></i>Important Notes</h6>
                                <ul class="list-unstyled mb-0">
                                    <li class="mb-2">
                                        <i class="bx bx-info-circle text-info me-2"></i>
                                        Changing the account code may affect existing transactions
                                    </li>
                                    <li class="mb-2">
                                        <i class="bx bx-info-circle text-info me-2"></i>
                                        Ensure the new account code is unique
                                    </li>
                                    <li class="mb-2">
                                        <i class="bx bx-info-circle text-info me-2"></i>
                                        Review all changes before saving
                                    </li>
                                    <li class="mb-2">
                                        <i class="bx bx-info-circle text-info me-2"></i>
                                        Changes will affect all future transactions
                                    </li>
                                </ul>
                            </div>

                            <hr>

                            <div class="mb-3">
                                <h6 class="text-success"><i class="bx bx-bookmark me-2"></i>Account Structure</h6>
                                <p class="text-muted small mb-2">
                                    Chart accounts are organized hierarchically: Account Class → Account Class Group → Chart Account.
                                </p>
                                <p class="text-muted small mb-0">
                                    Each level provides more specific categorization for financial reporting.
                                </p>
                            </div>

                            <hr>

                            <div class="mb-3">
                                <h6 class="text-secondary"><i class="bx bx-calculator me-2"></i>Quick Tips</h6>
                                <ul class="list-unstyled mb-0 small">
                                    <li class="mb-2">
                                        <strong>Account Code:</strong> Must be unique and within the class range
                                    </li>
                                    <li class="mb-2">
                                        <strong>Account Name:</strong> Should clearly describe the account's purpose
                                    </li>
                                    <li class="mb-0">
                                        <strong>Categories:</strong> Only enable if the account impacts those statements
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