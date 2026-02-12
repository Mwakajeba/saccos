@extends('layouts.main')

@section('title', 'Job Log Details')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Settings', 'url' => route('settings.index'), 'icon' => 'bx bx-cog'],
            ['label' => 'Job Logs', 'url' => route('settings.job-logs.index'), 'icon' => 'bx bx-list-ul'],
            ['label' => 'Job Details', 'url' => '#', 'icon' => 'bx bx-detail']
        ]" />

        <!-- Job Summary Card -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bx bx-info-circle me-2"></i>Job Summary</h5>
                <div class="btn-group">
                    <a href="{{ route('settings.job-logs.export', ['jobLog' => $jobLog->id, 'format' => 'excel']) }}" 
                       class="btn btn-sm btn-success">
                        <i class="bx bx-file me-1"></i>Export Excel
                    </a>
                    <a href="{{ route('settings.job-logs.export', ['jobLog' => $jobLog->id, 'format' => 'pdf']) }}" 
                       class="btn btn-sm btn-danger">
                        <i class="bx bx-file-blank me-1"></i>Export PDF
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td class="fw-bold" style="width: 40%;">Job Name:</td>
                                <td>{{ $jobLog->job_name }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Status:</td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'pending' => 'secondary',
                                            'running' => 'info',
                                            'completed' => 'success',
                                            'failed' => 'danger',
                                        ];
                                        $color = $statusColors[$jobLog->status] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $color }}">{{ ucfirst($jobLog->status) }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Started At:</td>
                                <td>{{ $jobLog->started_at ? $jobLog->started_at->format('d-m-Y H:i:s') : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Completed At:</td>
                                <td>{{ $jobLog->completed_at ? $jobLog->completed_at->format('d-m-Y H:i:s') : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Duration:</td>
                                <td>{{ $jobLog->formatted_duration }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td class="fw-bold" style="width: 40%;">Total Processed:</td>
                                <td><span class="badge bg-primary">{{ $jobLog->processed }}</span></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Successful:</td>
                                <td><span class="badge bg-success">{{ $jobLog->successful }}</span></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Failed:</td>
                                <td><span class="badge bg-danger">{{ $jobLog->failed }}</span></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Total Amount:</td>
                                <td class="text-success fw-bold">
                                    {{ $jobLog->total_amount ? 'TZS ' . number_format($jobLog->total_amount, 2) : 'N/A' }}
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Summary:</td>
                                <td>{{ $jobLog->summary ?? 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                @if($jobLog->error_message)
                    <div class="alert alert-danger mt-3">
                        <strong><i class="bx bx-error-circle me-2"></i>Error:</strong>
                        {{ $jobLog->error_message }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Loan Details Card (for interest calculation jobs) -->
        @if($jobLog->job_name === 'CalculateDailyInterestJob' && !empty($details))
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bx bx-trending-up me-2"></i>Daily Interest Accrued Details</h5>
                </div>
                <div class="card-body">
                    <!-- Summary Stats -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card bg-light-primary border-0">
                                <div class="card-body text-center">
                                    <h6 class="text-muted mb-1">Total Loans Processed</h6>
                                    <h4 class="mb-0 text-primary">{{ count($details) }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light-success border-0">
                                <div class="card-body text-center">
                                    <h6 class="text-muted mb-1">Total Interest Accrued</h6>
                                    <h4 class="mb-0 text-success">TZS {{ number_format(collect($details)->sum('interest_accrued'), 2) }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light-info border-0">
                                <div class="card-body text-center">
                                    <h6 class="text-muted mb-1">Accrual Date</h6>
                                    <h4 class="mb-0 text-info">{{ $details[0]['accrual_date'] ?? 'N/A' }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Details Table -->
                    <div class="table-responsive">
                        <table id="jobDetailsTable" class="table table-bordered table-striped w-100">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Loan No</th>
                                    <th>Customer Name</th>
                                    <th class="text-end">Principal Balance</th>
                                    <th class="text-end">Interest Accrued</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($details as $index => $detail)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            @if(isset($detail['loan_id']))
                                                <a href="{{ route('loans.show', \Vinkla\Hashids\Facades\Hashids::encode($detail['loan_id'])) }}" target="_blank">
                                                    {{ $detail['loan_no'] ?? 'N/A' }}
                                                </a>
                                            @else
                                                {{ $detail['loan_no'] ?? 'N/A' }}
                                            @endif
                                        </td>
                                        <td>{{ $detail['customer_name'] ?? 'N/A' }}</td>
                                        <td class="text-end">
                                            {{ isset($detail['principal_balance']) ? 'TZS ' . number_format($detail['principal_balance'], 2) : '-' }}
                                        </td>
                                        <td class="text-end">
                                            <strong class="text-success">
                                                {{ isset($detail['interest_accrued']) ? 'TZS ' . number_format($detail['interest_accrued'], 2) : '-' }}
                                            </strong>
                                        </td>
                                        <td>
                                            @if(isset($detail['error']))
                                                <span class="badge bg-danger" title="{{ $detail['error'] }}">Failed</span>
                                            @else
                                                <span class="badge bg-success">Success</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-secondary">
                                <tr>
                                    <th colspan="4" class="text-end">TOTAL:</th>
                                    <th class="text-end text-success">TZS {{ number_format(collect($details)->sum('interest_accrued'), 2) }}</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        @elseif(empty($details))
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="bx bx-info-circle fs-1 text-muted mb-3"></i>
                    <h6 class="text-muted">No detailed information available</h6>
                    <p class="text-muted">Detailed job information may have expired or was not captured for this job.</p>
                </div>
            </div>
        @endif

        <div class="mt-4">
            <a href="{{ route('settings.job-logs.index') }}" class="btn btn-secondary">
                <i class="bx bx-arrow-back me-1"></i>Back to Job Logs
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        @if($jobLog->job_name === 'CalculateDailyInterestJob' && !empty($details))
            $('#jobDetailsTable').DataTable({
                pageLength: 25,
                order: [[4, 'desc']], // Sort by interest accrued descending
                language: {
                    search: "Search loans:"
                }
            });
        @endif
    });
</script>
@endpush
