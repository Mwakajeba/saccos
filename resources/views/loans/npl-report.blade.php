@extends('layouts.main')

@section('title', 'NPL Report (Loss/NPL)')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Loans', 'url' => route('loans.index'), 'icon' => 'bx bx-credit-card'],
            ['label' => 'NPL Report', 'url' => '#', 'icon' => 'bx bx-file']
        ]" />

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 text-uppercase">NON-PERFORMING LOANS (NPL) REPORT</h6>
            <div>
                @if($loans->isNotEmpty())
                <a href="{{ route('loans.npl-report.export-excel') }}" class="btn btn-success btn-sm me-2">
                    <i class="bx bx-file me-1"></i> Export Excel
                </a>
                <a href="{{ route('loans.npl-report.export-pdf') }}" class="btn btn-danger btn-sm me-2">
                    <i class="bx bx-file-pdf me-1"></i> Export PDF
                </a>
                @endif
                <a href="{{ route('loans.index') }}" class="btn btn-secondary btn-sm">
                    <i class="bx bx-arrow-back me-1"></i> Back to Loans
                </a>
            </div>
        </div>
        <hr />

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bx bx-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bx bx-error-circle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        @if($message)
        <div class="alert alert-warning">
            <i class="bx bx-info-circle me-2"></i>{{ $message }}
            <a href="{{ route('settings.arrears-classifications.index') }}" class="btn btn-warning btn-sm ms-3">
                <i class="bx bx-cog me-1"></i> Configure Now
            </a>
        </div>
        @endif

        @if($nplClassification)
        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-dark text-white">
                    <div class="card-body text-center">
                        <h6 class="text-white-50">Classification</h6>
                        <h4 class="mb-0">{{ $nplClassification->status }}</h4>
                        <small>{{ $nplClassification->bucket_label }} days</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-danger text-white">
                    <div class="card-body text-center">
                        <h6 class="text-white-50">Total NPL Loans</h6>
                        <h4 class="mb-0">{{ $loans->count() }}</h4>
                        <small>Loans in Loss/NPL</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-dark">
                    <div class="card-body text-center">
                        <h6 class="text-dark-50">Total Outstanding</h6>
                        <h4 class="mb-0">TSHS {{ number_format($totalOutstanding, 2) }}</h4>
                        <small>Outstanding Balance</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body text-center">
                        <h6 class="text-white-50">Total Provision</h6>
                        <h4 class="mb-0">TSHS {{ number_format($totalProvision, 2) }}</h4>
                        <small>{{ $nplClassification->provision_percentage }}% Provision</small>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-error-alt text-danger me-2"></i>
                        NPL Loans List (181+ Days in Arrears)
                    </h5>
                    <span class="badge bg-danger fs-6">{{ $loans->count() }} Loans</span>
                </div>

                @if($loans->isEmpty())
                <div class="text-center py-5">
                    <i class="bx bx-check-circle fs-1 text-success"></i>
                    <h5 class="mt-3">No NPL Loans Found</h5>
                    <p class="text-muted">There are no loans currently classified as Loss/NPL.</p>
                </div>
                @else
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped" id="nplLoansTable">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Loan No.</th>
                                <th>Customer</th>
                                <th>Product</th>
                                <th>Loan Amount</th>
                                <th>Outstanding Balance</th>
                                <th>Days in Arrears</th>
                                <th>Provision %</th>
                                <th>Provision Amount</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($loans as $index => $loan)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <a href="{{ route('loans.show', Vinkla\Hashids\Facades\Hashids::encode($loan->id)) }}" class="fw-bold text-primary">
                                        {{ $loan->loan_number ?? 'N/A' }}
                                    </a>
                                </td>
                                <td>{{ $loan->customer->name ?? 'N/A' }}</td>
                                <td>{{ $loan->product->name ?? 'N/A' }}</td>
                                <td class="text-end">TSHS {{ number_format($loan->amount, 2) }}</td>
                                <td class="text-end text-danger fw-bold">TSHS {{ number_format($loan->outstanding_balance, 2) }}</td>
                                <td class="text-center">
                                    <span class="badge bg-danger">{{ $loan->days_in_arrears }} days</span>
                                </td>
                                <td class="text-center">{{ number_format($loan->provision_percentage, 2) }}%</td>
                                <td class="text-end text-warning fw-bold">TSHS {{ number_format($loan->provision_amount, 2) }}</td>
                                <td>
                                    <span class="badge bg-{{ $loan->status === 'defaulted' ? 'danger' : 'primary' }}">
                                        {{ ucfirst($loan->status) }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('loans.show', Vinkla\Hashids\Facades\Hashids::encode($loan->id)) }}" class="btn btn-sm btn-outline-primary" title="View Loan">
                                        <i class="bx bx-show"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-secondary">
                            <tr>
                                <th colspan="5" class="text-end">Totals:</th>
                                <th class="text-end text-danger fw-bold">TSHS {{ number_format($totalOutstanding, 2) }}</th>
                                <th colspan="2"></th>
                                <th class="text-end text-warning fw-bold">TSHS {{ number_format($totalProvision, 2) }}</th>
                                <th colspan="2"></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        @if($loans->isNotEmpty())
        $('#nplLoansTable').DataTable({
            order: [[6, 'desc']], // Sort by days in arrears descending
            pageLength: 25,
            dom: 'Bfrtip',
            buttons: [
                'copy', 'csv'
            ]
        });
        @endif
    });
</script>
@endpush
