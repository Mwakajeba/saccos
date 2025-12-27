@extends('layouts.main')
@section('title', 'Create Account Class Group')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Account Class Groups', 'url' => route('accounting.account-class-groups.index'), 'icon' => 'bx bx-category'],
            ['label' => 'Create Group', 'url' => '#', 'icon' => 'bx bx-plus-circle']
        ]" />

            <h6 class="mb-0 text-uppercase">CREATE NEW ACCOUNT CLASS GROUP</h6>
            <hr />
            <div class="row">
                <!-- Left Column: Form -->
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-body">
                            @include('account-class-groups.form')
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
                                <h6 class="text-primary"><i class="bx bx-help-circle me-2"></i>How to Create an Account Class Group</h6>
                                <ul class="list-unstyled mb-0">
                                    <li class="mb-2">
                                        <i class="bx bx-check-circle text-success me-2"></i>
                                        Select an Account Class from the dropdown
                                    </li>
                                    <li class="mb-2">
                                        <i class="bx bx-check-circle text-success me-2"></i>
                                        Enter a Group Code (optional but recommended)
                                    </li>
                                    <li class="mb-2">
                                        <i class="bx bx-check-circle text-success me-2"></i>
                                        Provide a clear and descriptive Group Name
                                    </li>
                                    <li class="mb-2">
                                        <i class="bx bx-check-circle text-success me-2"></i>
                                        Click "Create Group" to save
                                    </li>
                                </ul>
                            </div>

                            <hr>

                            <div class="mb-4">
                                <h6 class="text-warning"><i class="bx bx-error-circle me-2"></i>Important Notes</h6>
                                <ul class="list-unstyled mb-0">
                                    <li class="mb-2">
                                        <i class="bx bx-info-circle text-info me-2"></i>
                                        Group names must be unique within the same class
                                    </li>
                                    <li class="mb-2">
                                        <i class="bx bx-info-circle text-info me-2"></i>
                                        Group codes are optional but help with organization
                                    </li>
                                    <li class="mb-2">
                                        <i class="bx bx-info-circle text-info me-2"></i>
                                        Each group belongs to one Account Class
                                    </li>
                                    <li class="mb-2">
                                        <i class="bx bx-info-circle text-info me-2"></i>
                                        Groups organize accounts for financial reporting
                                    </li>
                                </ul>
                            </div>

                            <hr>

                            <div class="mb-3">
                                <h6 class="text-success"><i class="bx bx-bookmark me-2"></i>Account Class Groups (FSLI)</h6>
                                <p class="text-muted small mb-2">
                                    Account Class Groups are Financial Statement Line Items (FSLI) that organize chart accounts into meaningful categories for financial reporting.
                                </p>
                                <p class="text-muted small mb-2">
                                    Examples include:
                                </p>
                                <ul class="list-unstyled mb-0 small">
                                    <li class="mb-1">• Current Assets</li>
                                    <li class="mb-1">• Long-term Liabilities</li>
                                    <li class="mb-1">• Operating Expenses</li>
                                    <li class="mb-0">• Revenue</li>
                                </ul>
                            </div>

                            <hr>

                            <div class="mb-3">
                                <h6 class="text-secondary"><i class="bx bx-calculator me-2"></i>Quick Tips</h6>
                                <ul class="list-unstyled mb-0 small">
                                    <li class="mb-2">
                                        <strong>Group Code:</strong> Use numeric codes like 1000, 2000 for easy reference
                                    </li>
                                    <li class="mb-2">
                                        <strong>Group Name:</strong> Use clear, standard accounting terminology
                                    </li>
                                    <li class="mb-0">
                                        <strong>Account Class:</strong> Determines the code range for accounts in this group
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