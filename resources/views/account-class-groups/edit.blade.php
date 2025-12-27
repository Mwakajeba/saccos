@extends('layouts.main')
@section('title', 'Edit Account Class Group')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Account Class Groups', 'url' => route('accounting.account-class-groups.index'), 'icon' => 'bx bx-category'],
            ['label' => 'Edit Group', 'url' => '#', 'icon' => 'bx bx-edit']
        ]" />

            <h6 class="mb-0 text-uppercase">EDIT ACCOUNT CLASS GROUP</h6>
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
                                <h6 class="text-primary"><i class="bx bx-help-circle me-2"></i>Editing Account Class Group</h6>
                                <ul class="list-unstyled mb-0">
                                    <li class="mb-2">
                                        <i class="bx bx-check-circle text-success me-2"></i>
                                        You can modify the Account Class if needed
                                    </li>
                                    <li class="mb-2">
                                        <i class="bx bx-check-circle text-success me-2"></i>
                                        Update the Group Code as necessary
                                    </li>
                                    <li class="mb-2">
                                        <i class="bx bx-check-circle text-success me-2"></i>
                                        Modify the Group Name to reflect changes
                                    </li>
                                    <li class="mb-2">
                                        <i class="bx bx-check-circle text-success me-2"></i>
                                        Review all changes before saving
                                    </li>
                                </ul>
                            </div>

                            <hr>

                            <div class="mb-4">
                                <h6 class="text-warning"><i class="bx bx-error-circle me-2"></i>Important Notes</h6>
                                <ul class="list-unstyled mb-0">
                                    <li class="mb-2">
                                        <i class="bx bx-info-circle text-info me-2"></i>
                                        Changing the Account Class may affect existing chart accounts
                                    </li>
                                    <li class="mb-2">
                                        <i class="bx bx-info-circle text-info me-2"></i>
                                        Group names must remain unique within the class
                                    </li>
                                    <li class="mb-2">
                                        <i class="bx bx-info-circle text-info me-2"></i>
                                        Changes will affect all accounts in this group
                                    </li>
                                    <li class="mb-2">
                                        <i class="bx bx-info-circle text-info me-2"></i>
                                        Review impact on financial reports before saving
                                    </li>
                                </ul>
                            </div>

                            <hr>

                            <div class="mb-3">
                                <h6 class="text-success"><i class="bx bx-bookmark me-2"></i>Account Class Groups (FSLI)</h6>
                                <p class="text-muted small mb-2">
                                    Account Class Groups are Financial Statement Line Items (FSLI) that organize chart accounts into meaningful categories for financial reporting.
                                </p>
                                <p class="text-muted small mb-0">
                                    They help structure your chart of accounts for accurate financial statement generation.
                                </p>
                            </div>

                            <hr>

                            <div class="mb-3">
                                <h6 class="text-secondary"><i class="bx bx-calculator me-2"></i>Quick Tips</h6>
                                <ul class="list-unstyled mb-0 small">
                                    <li class="mb-2">
                                        <strong>Group Code:</strong> Use consistent numbering for easy reference
                                    </li>
                                    <li class="mb-2">
                                        <strong>Group Name:</strong> Use standard accounting terminology
                                    </li>
                                    <li class="mb-0">
                                        <strong>Account Class:</strong> Determines the code range for accounts
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