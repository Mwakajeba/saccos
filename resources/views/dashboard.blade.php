@extends('layouts.main')

@section('title', __('app.dashboard'))

@php
use Vinkla\Hashids\Facades\Hashids;
@endphp

<style>
    .financial-section {
        margin-bottom: 20px;
    }

    .section-header {
        border-radius: 8px 8px 0 0 !important;
    }

    .section-content {
        border-radius: 0 0 8px 8px !important;
        border-top: none !important;
    }

    .account-row:hover {
        background-color: #f8f9fa;
        transition: background-color 0.2s ease;
    }

    .account-row a:hover {
        color: #007bff !important;
        text-decoration: underline !important;
    }

    .table-sm td {
        padding: 0.5rem;
        vertical-align: middle;
    }

    .section-title {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    }



    @media print {

        .btn,
        .overlay,
        .back-to-top,
        footer {
            display: none !important;
        }

        .card {
            border: none !important;
            box-shadow: none !important;
        }

        .section-header {
            background: #333 !important;
            color: white !important;
        }
    }
</style>

@section('content')
@can('view dashboard')
<div class="page-wrapper">
    <div class="page-content">
        <!-- Welcome Section -->
        <div class="row">
            <div class="col-12">
                <div class="card border-top border-0 border-4 border-primary">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <div class="card-title d-flex align-items-center">
                                    <div><i class="bx bx-home me-1 font-22 text-primary"></i></div>
                                    <h5 class="mb-0 text-primary">Dashboard</h5>
                                </div>
                                <p class="mb-0 text-muted">Here's what's happening with your financial data today</p>
                            </div>
                            <div class="col-md-4 text-end">
                                <div class="d-flex gap-2 justify-content-end">
                                    <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#bulkSmsModal">
                                        <i class="bx bx-envelope"></i> SMS
                                    </button>
                                    <a href="{{ route('customers.create') }}" class="btn btn-sm btn-primary">
                                        <i class="bx bx-user-plus"></i> Create Customer
                                    </a>
                                    <a href="{{ route('loans.create') }}" class="btn btn-sm btn-success">
                                        <i class="bx bx-money"></i> Create Loan
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Branch Filter -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form method="GET" action="{{ route('dashboard') }}" class="d-flex align-items-center">
                            <div class="me-3">
                                <label for="branch_id" class="form-label mb-0"><strong>Filter by Branch:</strong></label>
                            </div>
                            <div class="me-3">
                                <select name="branch_id" id="branch_id" class="form-select" onchange="this.form.submit()">
                                    <option value="" {{ !$selectedBranchId ? 'selected' : '' }}>All Branches</option>
                                    @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ $selectedBranchId == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="me-3">
                                <span class="badge bg-primary">
                                    Showing: {{ $selectedBranchId ? (collect($branches)->where('id', $selectedBranchId)->first()['name'] ?? 'Selected Branch') : 'All Branches' }}
                                </span>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="row row-cols-1 row-cols-lg-4">
            @can('view charges')
            <div class="col">
                <a href="{{ route('customers.penalty') }}" class="text-decoration-none">
                    <div class="card radius-10">
                        <div class="card-body position-relative">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <p class="mb-0 text-muted">Total Penalty</p>
                                    <h4 class="font-weight-bold text-dark">
                                        TZS {{ number_format($penaltyBalance, 2) }}
                                    </h4>
                                    <p class="text-success mb-0 font-13">Penalty balance</p>
                                </div>
                                <div class="widgets-icons bg-gradient-cosmic text-white">
                                    <i class='bx bx-error'></i>
                                </div>
                            </div>
                            <span class="stretched-link"></span>
                        </div>
                    </div>
                </a>
            </div>
            @endcan

            @can('view payments')
            <div class="col">
                <div class="card radius-10">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="mb-0">Total Payments</p>
                                <h4 class="font-weight-bold">TZS {{ number_format($recentPayments->sum('amount') ?? 0, 2) }}</h4>
                                <p class="text-success mb-0 font-13">This year ({{ now()->format('Y') }})</p>
                            </div>
                            <div class="widgets-icons bg-gradient-burning text-white"><i class='bx bx-money'></i></div>
                        </div>
                    </div>
                </div>
            </div>
            @endcan

            @can('view receipts')
            <div class="col">
                <div class="card radius-10">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="mb-0">Total Receipts</p>
                                <h4 class="font-weight-bold">TZS {{ number_format($receiptsThisYear->sum('amount') ?? 0, 2) }}</h4>
                                <p class="text-success mb-0 font-13">This year ({{ now()->format('Y') }})</p>
                            </div>
                            <div class="widgets-icons bg-gradient-lush text-white"><i class='bx bx-receipt'></i></div>
                        </div>
                    </div>
                </div>
            </div>
            @endcan
            <!-- Loan Stats Cards -->
            @can('view loans')
            <div class="col">
                <div class="card radius-10">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="mb-0">Total Loan Amount</p>
                                <h4 class="font-weight-bold">TZS {{ number_format($totalLoanAmount ?? 0, 2) }}</h4>
                            </div>
                            <div class="widgets-icons bg-gradient-blues text-white"><i class='bx bx-wallet'></i></div>
                        </div>
                    </div>
                </div>
            </div>
            @endcan
            @can('view loans')
            <div class="col">
                <div class="card radius-10">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="mb-0">Total Principal</p>
                                <h4 class="font-weight-bold">TZS {{ number_format($totalPrincipal ?? 0, 2) }}</h4>
                                <p class="mb-0">Total Interest: TZS {{ number_format($totalInterest ?? 0, 2) }}</p>
                            </div>
                            <div class="widgets-icons bg-gradient-burning text-white"><i class='bx bx-money'></i></div>
                        </div>
                    </div>
                </div>
            </div>
            @endcan
            @can('view loans')
            <div class="col">
                <div class="card radius-10">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="mb-0">Repaid Principal</p>
                                <h4 class="font-weight-bold">TZS {{ number_format($repaidPrincipal ?? 0, 2) }}</h4>
                                <p class="mb-0">Repaid Interest: TZS {{ number_format($repaidInterest ?? 0, 2) }}</p>
                            </div>
                            <div class="widgets-icons bg-gradient-success text-white"><i class='bx bx-check-circle'></i></div>
                        </div>
                    </div>
                </div>
            </div>
            @endcan
            @can('view loans')
            <div class="col">
                <div class="card radius-10">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="mb-0">Expected Interest This Year</p>
                                <h4 class="font-weight-bold text-info">TZS {{ number_format($expectedInterestThisYear ?? 0, 0) }}</h4>
                                <p class="text-muted mb-0 font-13">{{ now()->format('Y') }}</p>
                            </div>
                            <div class="widgets-icons bg-gradient-ohhappiness text-white"><i class='bx bx-trending-up'></i></div>
                        </div>
                    </div>
                </div>
            </div>
            @endcan
            @can('view loans')
            <div class="col">
                <div class="card radius-10">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="mb-0">Accrued Interest This Year</p>
                                <h4 class="font-weight-bold text-primary">TZS {{ number_format($accruedInterestThisYear ?? 0, 0) }}</h4>
                                <p class="text-muted mb-0 font-13">{{ now()->format('Y') }}</p>
                            </div>
                            <div class="widgets-icons bg-gradient-blues text-white"><i class='bx bx-calendar'></i></div>
                        </div>
                    </div>
                </div>
            </div>
            @endcan
            @can('view loans')
            <div class="col">
                <div class="card radius-10">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="mb-0">Accrued Interest This Month</p>
                                <h4 class="font-weight-bold text-success">TZS {{ number_format($accruedInterestThisMonth ?? 0, 0) }}</h4>
                                <p class="text-muted mb-0 font-13">{{ now()->format('F Y') }}</p>
                            </div>
                            <div class="widgets-icons bg-gradient-lush text-white"><i class='bx bx-calendar-check'></i></div>
                        </div>
                    </div>
                </div>
            </div>
            @endcan
            @can('view loans')
            <div class="col">
                <div class="card radius-10">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="mb-0">Collected Principal This Year</p>
                                <h4 class="font-weight-bold text-success">TZS {{ number_format($collectedPrincipalThisYear ?? 0, 0) }}</h4>
                                <p class="text-muted mb-0 font-13">{{ now()->format('Y') }}</p>
                            </div>
                            <div class="widgets-icons bg-gradient-success text-white"><i class='bx bx-check-circle'></i></div>
                        </div>
                    </div>
                </div>
            </div>
            @endcan
            @can('view loans')
            <div class="col">
                <div class="card radius-10">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="mb-0">Collected Interest This Year</p>
                                <h4 class="font-weight-bold text-primary">TZS {{ number_format($collectedInterestThisYear ?? 0, 0) }}</h4>
                                <p class="text-muted mb-0 font-13">{{ now()->format('Y') }}</p>
                            </div>
                            <div class="widgets-icons bg-gradient-blues text-white"><i class='bx bx-dollar-circle'></i></div>
                        </div>
                    </div>
                </div>
            </div>
            @endcan
            @can('view loans')
            <div class="col">
                <div class="card radius-10">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="mb-0">Fee Collected This Year</p>
                                <h4 class="font-weight-bold text-warning">TZS {{ number_format($collectedFeeThisYear ?? 0, 0) }}</h4>
                                <p class="text-muted mb-0 font-13">{{ now()->format('Y') }}</p>
                            </div>
                            <div class="widgets-icons bg-gradient-burning text-white"><i class='bx bx-receipt'></i></div>
                        </div>
                    </div>
                </div>
            </div>
            @endcan
            @can('view loans')
            <div class="col">
                <div class="card radius-10">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="mb-0">Penalty Collected This Year</p>
                                <h4 class="font-weight-bold text-danger">TZS {{ number_format($collectedPenaltyThisYear ?? 0, 0) }}</h4>
                                <p class="text-muted mb-0 font-13">{{ now()->format('Y') }}</p>
                            </div>
                            <div class="widgets-icons bg-gradient-cosmic text-white"><i class='bx bx-error'></i></div>
                        </div>
                    </div>
                </div>
            </div>
            @endcan
        </div>
        <!--end row-->

        <!-- Arrears Classification Buckets -->
        @can('view loans')
        @if(isset($arrearsClassifications) && $arrearsClassifications->count() > 0)
        <div class="row mt-3">
            <div class="col-12">
                <h5 class="mb-3"><i class="bx bx-pie-chart-alt me-2"></i>Loan Portfolio by Arrears Classification</h5>
            </div>
        </div>
        <div class="row row-cols-1 row-cols-lg-5">
            @foreach($arrearsClassifications as $classification)
            <div class="col">
                <div class="card radius-10">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="mb-0">{{ $classification['status'] }}</p>
                                <h4 class="font-weight-bold 
                                    @if($classification['status'] == 'Current') text-success
                                    @elseif($classification['status'] == 'Past Due') text-info
                                    @elseif($classification['status'] == 'Substandard') text-warning
                                    @elseif($classification['status'] == 'Doubtful') text-danger
                                    @elseif($classification['status'] == 'Loss/NPL') text-dark
                                    @else text-secondary
                                    @endif">TZS {{ number_format($classification['total_amount'], 0) }}</h4>
                                <p class="text-muted mb-0 font-13">{{ $classification['bucket_label'] }} ({{ $classification['loan_count'] }} Loans)</p>
                            </div>
                            <div class="widgets-icons 
                                @if($classification['status'] == 'Current') bg-gradient-success
                                @elseif($classification['status'] == 'Past Due') bg-gradient-blues
                                @elseif($classification['status'] == 'Substandard') bg-gradient-burning
                                @elseif($classification['status'] == 'Doubtful') bg-gradient-bloody
                                @elseif($classification['status'] == 'Loss/NPL') bg-gradient-cosmic
                                @else bg-gradient-ohhappiness
                                @endif text-white">
                                <i class='bx 
                                    @if($classification['status'] == 'Current') bx-check-circle
                                    @elseif($classification['status'] == 'Past Due') bx-time
                                    @elseif($classification['status'] == 'Substandard') bx-error
                                    @elseif($classification['status'] == 'Doubtful') bx-error-circle
                                    @elseif($classification['status'] == 'Loss/NPL') bx-x-circle
                                    @else bx-info-circle
                                    @endif'></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
        @endcan

        @can('view graphs')
        <!-- Loan Product Disbursement Chart -->
        <div class="row">
            <div class="col-5">
                <div class="card radius-10">
                    <div class="card-body">
                        <h5 class="mb-3">Delinquency Loan Buckets (This Year)</h5>
                        <canvas id="delinquencyLoanChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-7">
                <div class="card radius-10">
                    <div class="card-body">
                        <h5 class="mb-3">Loan Product Disbursement (This Year)</h5>
                        <canvas id="loanProductChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <!-- Daily Accrued Interest Line Chart -->
        <div class="row mt-3">
            <div class="col-12">
                <div class="card radius-10">
                    <div class="card-body">
                        <h5 class="mb-3"><i class="bx bx-line-chart me-2"></i>Daily Accrued Interest (Past 7 Days)</h5>
                        <canvas id="dailyAccruedInterestChart" height="80"></canvas>
                    </div>
                </div>
            </div>
        </div>
        @endcan
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Daily Accrued Interest Line Chart
                const dailyAccruedData = @json($dailyAccruedInterest ?? []);
                const dailyAccruedCtx = document.getElementById('dailyAccruedInterestChart');
                if (dailyAccruedCtx && dailyAccruedData.length > 0) {
                    new Chart(dailyAccruedCtx, {
                        type: 'line',
                        data: {
                            labels: dailyAccruedData.map(item => item.date),
                            datasets: [{
                                label: 'Accrued Interest (TZS)',
                                data: dailyAccruedData.map(item => item.amount),
                                borderColor: '#0d6efd',
                                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                                borderWidth: 3,
                                fill: true,
                                tension: 0.4,
                                pointBackgroundColor: '#0d6efd',
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2,
                                pointRadius: 5,
                                pointHoverRadius: 7
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    display: true,
                                    position: 'top'
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            return 'TZS ' + context.raw.toLocaleString();
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function(value) {
                                            return 'TZS ' + value.toLocaleString();
                                        }
                                    }
                                }
                            }
                        }
                    });
                }

                // Get current branch filter from URL or form
                const urlParams = new URLSearchParams(window.location.search);
                const branchId = urlParams.get('branch_id') || '';

                // Loan Product Disbursement Chart
                fetch('/dashboard/loan-product-disbursement' + (branchId ? '?branch_id=' + branchId : ''))
                    .then(response => response.json())
                    .then(data => {
                        const ctx = document.getElementById('loanProductChart').getContext('2d');
                        if (!data.products.length || !data.amounts.length || data.amounts.every(a => a == 0)) {
                            document.getElementById('loanProductChart').style.display = 'none';
                            const fallback = document.createElement('div');
                            fallback.style.textAlign = 'center';
                            fallback.style.padding = '40px 0';
                            fallback.style.color = '#888';
                            fallback.innerHTML = '<b>No loan product disbursement data available for this year.</b>';
                            ctx.canvas.parentNode.appendChild(fallback);
                            return;
                        }
                        new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: data.products,
                                datasets: [{
                                    label: 'Amount Disbursed (TZS)',
                                    data: data.amounts,
                                    backgroundColor: [
                                        '#8e44ad', '#e74c3c', '#f1c40f', '#27ae60', '#34495e', '#00bfff'
                                    ],
                                }]
                            },
                            options: {
                                responsive: true,
                                plugins: {
                                    legend: {
                                        display: false
                                    },
                                    title: {
                                        display: true,
                                        text: 'Loan By Product Disbursement (This Year)'
                                    }
                                },
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        title: {
                                            display: true,
                                            text: 'Amount (TZS)'
                                        }
                                    },
                                    x: {
                                        title: {
                                            display: true,
                                            text: 'Loan Product'
                                        }
                                    }
                                }
                            }
                        });
                    });

                // Delinquency Loan Pie Chart
                fetch('/dashboard/delinquency-loan-buckets' + (branchId ? '?branch_id=' + branchId : ''))
                    .then(response => response.json())
                    .then(data => {
                        const ctx = document.getElementById('delinquencyLoanChart').getContext('2d');
                        if (!data.labels.length || !data.values.length || data.values.every(v => v == 0)) {
                            document.getElementById('delinquencyLoanChart').style.display = 'none';
                            const fallback = document.createElement('div');
                            fallback.style.textAlign = 'center';
                            fallback.style.padding = '40px 0';
                            fallback.style.color = '#888';
                            fallback.innerHTML = '<b>No delinquency loan data available for this year.</b>';
                            ctx.canvas.parentNode.appendChild(fallback);
                            return;
                        }
                        new Chart(ctx, {
                            type: 'pie',
                            data: {
                                labels: data.labels,
                                datasets: [{
                                    label: 'Delinquency Loans',
                                    data: data.values,
                                    backgroundColor: [
                                        '#e74c3c', '#f1c40f', '#27ae60', '#34495e', '#00bfff', '#8e44ad'
                                    ],
                                }]
                            },
                            options: {
                                responsive: true,
                                plugins: {
                                    legend: {
                                        display: true
                                    },
                                    title: {
                                        display: true,
                                        text: 'Delinquency Loan Buckets (Percent)'
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(context) {
                                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                                const value = context.parsed;
                                                const percent = total ? ((value / total) * 100).toFixed(1) : 0;
                                                return `${context.label}: ${value} (${percent}%)`;
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    });
            });

            // Monthly Collections Grouped Bar Chart
            fetch('/dashboard/monthly-collections')
                .then(response => response.json())
                .then(data => {
                    console.log('Monthly Collections Chart Data:', data);
                    console.log('Expected:', data.expected);
                    console.log('Collected:', data.collected);
                    console.log('Arrears:', data.arrears);
                    const ctx = document.getElementById('monthlyCollectionsChart').getContext('2d');
                    const isEmpty = !data.months || !data.expected || !data.collected || !data.arrears ||
                        data.months.length === 0 ||
                        (data.expected.every(v => v == 0) && data.collected.every(v => v == 0) && data.arrears.every(v => v == 0));
                    if (isEmpty) {
                        document.getElementById('monthlyCollectionsChart').style.display = 'none';
                        const fallback = document.createElement('div');
                        fallback.style.textAlign = 'center';
                        fallback.style.padding = '40px 0';
                        fallback.style.color = '#888';
                        fallback.innerHTML = '<b>No monthly collections data available for this year.</b>';
                        ctx.canvas.parentNode.appendChild(fallback);
                        return;
                    }
                    // Highlight months with no repayments by changing the collected bar color to gray
                    const collectedColors = data.collected.map(v => v == 0 ? '#cccccc' : '#27ae60');
                    new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: data.months,
                            datasets: [{
                                    label: 'Expected',
                                    data: data.expected,
                                    backgroundColor: '#f1c40f',
                                    barPercentage: 0.6,
                                    categoryPercentage: 0.8
                                },
                                {
                                    label: 'Collected',
                                    data: data.collected,
                                    backgroundColor: collectedColors,
                                    barPercentage: 0.6,
                                    categoryPercentage: 0.8
                                },
                                {
                                    label: 'Arrears',
                                    data: data.arrears,
                                    backgroundColor: '#e74c3c',
                                    barPercentage: 0.6,
                                    categoryPercentage: 0.8
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    display: true
                                },
                                title: {
                                    display: true,
                                    text: 'Monthly Expected vs Collected vs Arrears'
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            let label = context.dataset.label || '';
                                            let value = context.parsed;
                                            if (label === 'Collected' && value === 0) {
                                                return `${context.label}: No repayments`;
                                            }
                                            return `${context.label}: ${value.toLocaleString()}`;
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    stacked: false,
                                    title: {
                                        display: true,
                                        text: 'Month'
                                    }
                                },
                                y: {
                                    stacked: false,
                                    beginAtZero: true,
                                    title: {
                                        display: true,
                                        text: 'Amount (TZS)'
                                    },
                                    ticks: {
                                        callback: function(value) {
                                            return value.toLocaleString();
                                        }
                                    }
                                }
                            },
                            barThickness: 12
                        }
                    });
                });
        </script>
        <!--end row-->
        @can('view graphs')
        <!-- Monthly Collections Overview -->
        <div class="row">
            <div class="col-12">
                <div class="card radius-10 w-100">
                    <div class="card-body">
                        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                            <div class="card-header bg-white border-bottom-0 d-flex align-items-center">
                                <i class="bx bx-bar-chart-alt-2 text-primary me-2 font-20"></i>
                                <h6 class="mb-0 text-dark">Monthly Collections Overview (This Year)</h6>
                            </div>
                            <div class="card-body pt-3 pb-2">
                                <canvas id="monthlyCollectionsChart" height="80"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--end row-->
        @endcan
        <!-- Recent Activities -->
        @can('view recent activities')
        <div class="row row-cols-1 row-cols-lg-3">
            <div class="col">
                <div class="card radius-10">
                    <div class="card-header bg-transparent">
                        <h6 class="mb-0"><i class="bx bx-book-open me-2"></i>Recent Journals</h6>
                    </div>
                    <div class="card-body">
                        @forelse($recentJournals as $journal)
                        <div class="d-flex align-items-center mb-3">
                            <div class="widgets-icons bg-light-primary text-primary me-3">
                                <i class="bx bx-book"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">{{ $journal->reference }}</h6>
                                <p class="mb-0 text-muted">{{ Str::limit($journal->description, 30) }}</p>
                                <small class="text-muted">{{ $journal->date ? $journal->date->format('M d, Y') : 'N/A' }}</small>
                            </div>
                        </div>
                        @empty
                        <p class="text-muted text-center">No recent journals</p>
                        @endforelse
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-10">
                    <div class="card-header bg-transparent">
                        <h6 class="mb-0"><i class="bx bx-money me-2"></i>Recent Payments</h6>
                    </div>
                    <div class="card-body">
                        @forelse($recentPayments as $payment)
                        <div class="d-flex align-items-center mb-3">
                            <div class="widgets-icons bg-light-success text-success me-3">
                                <i class="bx bx-money"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">{{ $payment->reference }}</h6>
                                <p class="mb-0 text-muted">{{ Str::limit($payment->description, 30) }}</p>
                                <small class="text-muted">{{ $payment->date ? $payment->date->format('M d, Y') : 'N/A' }}</small>
                            </div>
                        </div>
                        @empty
                        <p class="text-muted text-center">No recent payments</p>
                        @endforelse
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-10">
                    <div class="card-header bg-transparent">
                        <h6 class="mb-0"><i class="bx bx-receipt me-2"></i>Recent Receipts</h6>
                    </div>
                    <div class="card-body">
                        @forelse($recentReceipts as $receipt)
                        <div class="d-flex align-items-center mb-3">
                            <div class="widgets-icons bg-light-success text-success me-3">
                                <i class="bx bx-receipt"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">{{ $receipt->reference }}</h6>
                                <p class="mb-0 text-muted">{{ $receipt->description ?? 'N/A' }}</p>
                                <small class="text-muted">{{ $receipt->date ? $receipt->date->format('M d, Y') : 'N/A' }}</small>
                            </div>
                        </div>
                        @empty
                        <p class="text-muted text-center">No recent receipts</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
        @endcan
        <!-- Financial Report Summary -->
        @can('view financial reports')
        <div class="row">
            <div class="col-12">
                <div class="card radius-10 border-0 shadow-sm">
                    <div class="card-header border-0" style="background: linear-gradient(135deg, #6c757d 0%, #ffc107 25%, #fd7e14 50%, #0d6efd 100%);">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h5 class="mb-0 text-white"><i class="bx bx-bar-chart me-2"></i>FINANCIAL REPORT SUMMARY</h5>
                                <small class="text-white-50">
                                    Comprehensive financial overview as of {{ date('d-m-Y') }}
                                    @php
                                    $currentBranchName = null;
                                    if (!empty($selectedBranchId)) {
                                    $currentBranchName = optional($branches->firstWhere('id', $selectedBranchId))->name;
                                    }
                                    @endphp
                                    — {{ $currentBranchName ? ('Branch: ' . $currentBranchName) : 'All Branches' }}
                                </small>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="row">
                            <!-- Balance Sheet Section -->
                            <div class="col-md-6">
                                <div class="financial-section">
                                    <div class="section-header p-3 rounded-top" style="background: linear-gradient(135deg, #0d6efd 0%, #6c757d 100%);">
                                        <h4 class="mb-0 text-white"><i class="bx bx-balance me-2"></i>BALANCE SHEET</h4>
                                        <small class="text-white-50">As of {{ date('d-m-Y') }} vs {{ $previousYearData['year'] }}</small>
                                    </div>

                                    <!-- Assets Section -->
                                    <div class="section-content border rounded-bottom">
                                        <div class="section-title p-2 border-bottom" style="background: linear-gradient(135deg, #10b981 0%, #34d399 50%, #6ee7b7 100%);">
                                            <h6 class="mb-0 text-white fw-bold"><i class="bx bx-trending-up me-1"></i>ASSETS</h6>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-striped table-hover mb-0" id="assets-table">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Account</th>
                                                        <th class="text-end">Current Year</th>
                                                        <th class="text-end">Previous Year</th>
                                                        <th class="text-end">Change</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php $sumAsset = 0; $sumAssetPrev = 0; @endphp
                                                    @foreach($financialReportData['chartAccountsAssets'] as $mainGroupName => $mainGroup)
                                                    @php
                                                    $prevMainGroup = $previousYearData['chartAccountsAssets'][$mainGroupName] ?? null;
                                                    $prevMainGroupTotal = $prevMainGroup['total'] ?? 0;
                                                    $currentMainGroupTotal = $mainGroup['total'] ?? 0;
                                                    @endphp
                                                    @if($currentMainGroupTotal != 0 || $prevMainGroupTotal != 0)
                                                    @php
                                                    $mainGroupId = 'asset-' . Str::slug($mainGroupName);
                                                    $mainGroupChange = $currentMainGroupTotal - $prevMainGroupTotal;
                                                    @endphp
                                                    <tr class="table-primary parent-row clickable" data-bs-toggle="collapse" data-bs-target=".{{ $mainGroupId }}" aria-expanded="true">
                                                        <td class="fw-bold text-dark">
                                                            <i class="bx bx-chevron-down me-1 transition-icon"></i>
                                                            <i class="bx bx-folder me-1"></i>{{ $mainGroupName }}
                                                        </td>
                                                        <td class="text-end fw-bold text-dark">{{ number_format($currentMainGroupTotal, 2) }}</td>
                                                        <td class="text-end fw-bold text-dark">{{ number_format($prevMainGroupTotal, 2) }}</td>
                                                        <td class="text-end fw-bold text-dark">
                                                            {{ $mainGroupChange >= 0 ? '+' : '' }}{{ number_format($mainGroupChange, 2) }}
                                                        </td>
                                                    </tr>
                                                    @if(isset($mainGroup['fslis']))
                                                    @foreach($mainGroup['fslis'] as $fsliName => $fsli)
                                                    @php
                                                    $prevFsli = $prevMainGroup['fslis'][$fsliName] ?? null;
                                                    $prevFsliTotal = $prevFsli['total'] ?? 0;
                                                    $currentFsliTotal = $fsli['total'] ?? 0;
                                                    @endphp
                                                    @if($currentFsliTotal != 0 || $prevFsliTotal != 0)
                                                    @php
                                                    $fsliId = 'fsli-asset-' . Str::slug($fsliName);
                                                    $fsliChange = $currentFsliTotal - $prevFsliTotal;
                                                    @endphp
                                                    <tr class="table-light collapse show {{ $mainGroupId }} fsli-row clickable" data-bs-toggle="collapse" data-bs-target=".{{ $fsliId }}" aria-expanded="false">
                                                        <td class="ps-4 fw-medium text-dark">
                                                            <i class="bx bx-chevron-right me-1 transition-icon"></i>
                                                            {{ $fsliName }}
                                                        </td>
                                                        <td class="text-end fw-medium text-dark">{{ number_format($currentFsliTotal, 2) }}</td>
                                                        <td class="text-end fw-medium text-dark">{{ number_format($prevFsliTotal, 2) }}</td>
                                                        <td class="text-end fw-medium text-dark">
                                                            {{ $fsliChange >= 0 ? '+' : '' }}{{ number_format($fsliChange, 2) }}
                                                        </td>
                                                    </tr>
                                                    @if(isset($fsli['accounts']))
                                                    @foreach($fsli['accounts'] as $chartAccountAsset)
                                                    @include('partials.dashboard-account-row', [
                                                    'account' => $chartAccountAsset,
                                                    'mainGroupName' => $mainGroupName,
                                                    'fsliName' => $fsliName,
                                                    'fsliId' => $fsliId,
                                                    'previousYearData' => $previousYearData['chartAccountsAssets'],
                                                    'depth' => 0
                                                    ])
                                                    @endforeach
                                                    @endif
                                                    @endif
                                                    @endforeach
                                                    @endif
                                                    @endif
                                                    @endforeach
                                                    @php
                                                    $sumAsset = collect($financialReportData['chartAccountsAssets'])->sum('total');
                                                    $sumAssetPrev = collect($previousYearData['chartAccountsAssets'])->sum('total');
                                                    @endphp
                                                    <tr class="fw-bold" style="background: rgba(16, 185, 129, 0.25);">
                                                        <td class="text-dark">TOTAL ASSETS</td>
                                                        <td class="text-end text-dark">{{ number_format($sumAsset,2) }}</td>
                                                        <td class="text-end text-dark">{{ number_format($sumAssetPrev,2) }}</td>
                                                        <td class="text-end text-dark">
                                                            @php $assetChange = $sumAsset - $sumAssetPrev; @endphp
                                                            {{ $assetChange >= 0 ? '+' : '' }}{{ number_format($assetChange,2) }}
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>

                                        <!-- Equity Section -->
                                        <div class="section-title p-2 border-bottom mt-3" style="background: linear-gradient(135deg, #3b82f6 0%, #60a5fa 50%, #93c5fd 100%);">
                                            <h6 class="mb-0 text-white fw-bold"><i class="bx bx-user me-1"></i>EQUITY</h6>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-sm mb-0 table-hover" id="equity-table">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Account</th>
                                                        <th class="text-end">Current Year</th>
                                                        <th class="text-end">Previous Year</th>
                                                        <th class="text-end">Change</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php $sumEquity = 0; $sumEquityPrev = 0; @endphp
                                                    @foreach($financialReportData['chartAccountsEquitys'] as $mainGroupName => $mainGroup)
                                                    @php
                                                    $prevMainGroup = $previousYearData['chartAccountsEquitys'][$mainGroupName] ?? null;
                                                    $prevMainGroupTotal = $prevMainGroup['total'] ?? 0;
                                                    $currentMainGroupTotal = $mainGroup['total'] ?? 0;
                                                    @endphp
                                                    @if($currentMainGroupTotal != 0 || $prevMainGroupTotal != 0)
                                                    @php
                                                    $mainGroupId = 'equity-' . Str::slug($mainGroupName);
                                                    $mainGroupChange = $currentMainGroupTotal - $prevMainGroupTotal;
                                                    @endphp
                                                    <tr class="table-primary parent-row clickable" data-bs-toggle="collapse" data-bs-target=".{{ $mainGroupId }}" aria-expanded="true">
                                                        <td class="fw-bold text-dark">
                                                            <i class="bx bx-chevron-down me-1 transition-icon"></i>
                                                            <i class="bx bx-folder me-1"></i>{{ $mainGroupName }}
                                                        </td>
                                                        <td class="text-end fw-bold text-dark">{{ number_format($currentMainGroupTotal, 2) }}</td>
                                                        <td class="text-end fw-bold text-dark">{{ number_format($prevMainGroupTotal, 2) }}</td>
                                                        <td class="text-end fw-bold text-dark">
                                                            {{ $mainGroupChange >= 0 ? '+' : '' }}{{ number_format($mainGroupChange, 2) }}
                                                        </td>
                                                    </tr>
                                                    @if(isset($mainGroup['fslis']))
                                                    @foreach($mainGroup['fslis'] as $fsliName => $fsli)
                                                    @php
                                                    $prevFsli = $prevMainGroup['fslis'][$fsliName] ?? null;
                                                    $prevFsliTotal = $prevFsli['total'] ?? 0;
                                                    $currentFsliTotal = $fsli['total'] ?? 0;
                                                    @endphp
                                                    @if($currentFsliTotal != 0 || $prevFsliTotal != 0)
                                                    @php
                                                    $fsliId = 'fsli-equity-' . Str::slug($fsliName);
                                                    $fsliChange = $currentFsliTotal - $prevFsliTotal;
                                                    @endphp
                                                    <tr class="table-light collapse show {{ $mainGroupId }} fsli-row clickable" data-bs-toggle="collapse" data-bs-target=".{{ $fsliId }}" aria-expanded="false">
                                                        <td class="ps-4 fw-medium text-dark">
                                                            <i class="bx bx-chevron-right me-1 transition-icon"></i>
                                                            {{ $fsliName }}
                                                        </td>
                                                        <td class="text-end fw-medium text-dark">{{ number_format($currentFsliTotal, 2) }}</td>
                                                        <td class="text-end fw-medium text-dark">{{ number_format($prevFsliTotal, 2) }}</td>
                                                        <td class="text-end fw-medium text-dark">
                                                            {{ $fsliChange >= 0 ? '+' : '' }}{{ number_format($fsliChange, 2) }}
                                                        </td>
                                                    </tr>
                                                    @if(isset($fsli['accounts']))
                                                    @foreach($fsli['accounts'] as $chartAccountEquity)
                                                    @include('partials.dashboard-account-row', [
                                                    'account' => $chartAccountEquity,
                                                    'mainGroupName' => $mainGroupName,
                                                    'fsliName' => $fsliName,
                                                    'fsliId' => $fsliId,
                                                    'previousYearData' => $previousYearData['chartAccountsEquitys'],
                                                    'depth' => 0
                                                    ])
                                                    @endforeach
                                                    @endif
                                                    @endif
                                                    @endforeach
                                                    @endif
                                                    @endif
                                                    @endforeach
                                                    @php
                                                    $sumEquity = collect($financialReportData['chartAccountsEquitys'])->sum('total');
                                                    $sumEquityPrev = collect($previousYearData['chartAccountsEquitys'])->sum('total');
                                                    @endphp
                                                    <tr class="table-info">
                                                        <td>Profit And Loss (YTD)</td>
                                                        <td class="text-end fw-bold">{{ number_format($cumulativeProfitLoss ?? 0,2) }}</td>
                                                        <td class="text-end text-dark">{{ number_format($previousYearData['profitLoss'],2) }}</td>
                                                        <td class="text-end">
                                                            @php $profitChange = ($cumulativeProfitLoss ?? 0) - $previousYearData['profitLoss']; @endphp
                                                            {{ $profitChange >= 0 ? '+' : '' }}{{ number_format($profitChange,2) }}
                                                        </td>
                                                    </tr>
                                                    <tr class="fw-bold" style="background: rgba(59, 130, 246, 0.25);">
                                                        <td class="text-dark">TOTAL EQUITY</td>
                                                        <td class="text-end text-dark">{{ number_format($sumEquity + ($cumulativeProfitLoss ?? 0),2) }}</td>
                                                        <td class="text-end text-dark">{{ number_format($sumEquityPrev + $previousYearData['profitLoss'],2) }}</td>
                                                        <td class="text-end text-dark">
                                                            @php $equityChange = ($sumEquity + ($cumulativeProfitLoss ?? 0)) - ($sumEquityPrev + $previousYearData['profitLoss']); @endphp
                                                            {{ $equityChange >= 0 ? '+' : '' }}{{ number_format($equityChange,2) }}
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>

                                        <!-- Liabilities Section -->
                                        <div class="section-title p-2 border-bottom mt-3" style="background: linear-gradient(135deg, #ef4444 0%, #f87171 50%, #fca5a5 100%);">
                                            <h6 class="mb-0 text-white fw-bold"><i class="bx bx-trending-down me-1"></i>LIABILITIES</h6>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-sm mb-0 table-hover" id="liabilities-table">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Account</th>
                                                        <th class="text-end">Current Year</th>
                                                        <th class="text-end">Previous Year</th>
                                                        <th class="text-end">Change</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($financialReportData['chartAccountsLiabilities'] as $mainGroupName => $mainGroup)
                                                    @php
                                                    $prevMainGroup = $previousYearData['chartAccountsLiabilities'][$mainGroupName] ?? null;
                                                    $prevMainGroupTotal = $prevMainGroup['total'] ?? 0;
                                                    $currentMainGroupTotal = $mainGroup['total'] ?? 0;
                                                    @endphp
                                                    @if($currentMainGroupTotal != 0 || $prevMainGroupTotal != 0)
                                                    @php
                                                    $mainGroupId = 'liability-' . Str::slug($mainGroupName);
                                                    $mainGroupChange = $currentMainGroupTotal - $prevMainGroupTotal;
                                                    @endphp
                                                    <tr class="table-primary parent-row clickable" data-bs-toggle="collapse" data-bs-target=".{{ $mainGroupId }}" aria-expanded="true">
                                                        <td class="fw-bold text-dark">
                                                            <i class="bx bx-chevron-down me-1 transition-icon"></i>
                                                            <i class="bx bx-folder me-1"></i>{{ $mainGroupName }}
                                                        </td>
                                                        <td class="text-end fw-bold text-dark">{{ number_format($currentMainGroupTotal, 2) }}</td>
                                                        <td class="text-end fw-bold text-dark">{{ number_format($prevMainGroupTotal, 2) }}</td>
                                                        <td class="text-end fw-bold text-dark">
                                                            {{ $mainGroupChange >= 0 ? '+' : '' }}{{ number_format($mainGroupChange, 2) }}
                                                        </td>
                                                    </tr>
                                                    @if(isset($mainGroup['fslis']))
                                                    @foreach($mainGroup['fslis'] as $fsliName => $fsli)
                                                    @php
                                                    $prevFsli = $prevMainGroup['fslis'][$fsliName] ?? null;
                                                    $prevFsliTotal = $prevFsli['total'] ?? 0;
                                                    $currentFsliTotal = $fsli['total'] ?? 0;
                                                    @endphp
                                                    @if($currentFsliTotal != 0 || $prevFsliTotal != 0)
                                                    @php
                                                    $fsliId = 'fsli-liability-' . Str::slug($fsliName);
                                                    $fsliChange = $currentFsliTotal - $prevFsliTotal;
                                                    @endphp
                                                    <tr class="table-light collapse show {{ $mainGroupId }} fsli-row clickable" data-bs-toggle="collapse" data-bs-target=".{{ $fsliId }}" aria-expanded="false">
                                                        <td class="ps-4 fw-medium text-dark">
                                                            <i class="bx bx-chevron-right me-1 transition-icon"></i>
                                                            {{ $fsliName }}
                                                        </td>
                                                        <td class="text-end fw-medium text-dark">{{ number_format($currentFsliTotal, 2) }}</td>
                                                        <td class="text-end fw-medium text-dark">{{ number_format($prevFsliTotal, 2) }}</td>
                                                        <td class="text-end fw-medium text-dark">
                                                            {{ $fsliChange >= 0 ? '+' : '' }}{{ number_format($fsliChange, 2) }}
                                                        </td>
                                                    </tr>
                                                    @if(isset($fsli['accounts']))
                                                    @foreach($fsli['accounts'] as $chartAccountLiability)
                                                    @include('partials.dashboard-account-row', [
                                                    'account' => $chartAccountLiability,
                                                    'mainGroupName' => $mainGroupName,
                                                    'fsliName' => $fsliName,
                                                    'fsliId' => $fsliId,
                                                    'previousYearData' => $previousYearData['chartAccountsLiabilities'],
                                                    'depth' => 0
                                                    ])
                                                    @endforeach
                                                    @endif
                                                    @endif
                                                    @endforeach
                                                    @endif
                                                    @endif
                                                    @endforeach
                                                    @php
                                                    $sumLiability = collect($financialReportData['chartAccountsLiabilities'])->sum('total');
                                                    $sumLiabilityPrev = collect($previousYearData['chartAccountsLiabilities'])->sum('total');
                                                    @endphp
                                                    <tr class="fw-bold" style="background: rgba(239, 68, 68, 0.25);">
                                                        <td class="text-dark">TOTAL LIABILITIES</td>
                                                        <td class="text-end text-dark">{{ number_format($sumLiability,2) }}</td>
                                                        <td class="text-end text-dark">{{ number_format($sumLiabilityPrev, 2) }}</td>
                                                        <td class="text-end text-dark">
                                                            @php $liabilityChange = $sumLiability - $sumLiabilityPrev; @endphp
                                                            {{ $liabilityChange >= 0 ? '+' : '' }}{{ number_format(abs($liabilityChange),2) }}
                                                        </td>
                                                    </tr>
                                                    <tr class="fw-bold" style="background: rgba(99, 102, 241, 0.25);">
                                                        <td class="text-dark">TOTAL EQUITY & LIABILITY</td>
                                                        <td class="text-end text-dark">{{ number_format($sumLiability + $sumEquity + ($cumulativeProfitLoss ?? 0),2) }}</td>
                                                        <td class="text-end text-dark">{{ number_format($sumLiabilityPrev + $sumEquityPrev + $previousYearData['profitLoss'],2) }}</td>
                                                        <td class="text-end text-dark">
                                                            @php $totalChange = ($sumLiability + $sumEquity + ($cumulativeProfitLoss ?? 0)) - ($sumLiabilityPrev + $sumEquityPrev + $previousYearData['profitLoss']); @endphp
                                                            {{ $totalChange >= 0 ? '+' : '' }}{{ number_format($totalChange,2) }}

                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Profit & Loss Section -->
                            <div class="col-md-6">
                                <div class="financial-section">
                                    <div class="section-header p-3 rounded-top" style="background: linear-gradient(135deg, #fd7e14 0%, #ffc107 100%);">
                                        <h4 class="mb-0 text-white"><i class="bx bx-line-chart me-2"></i>PROFIT & LOSS STATEMENT</h4>
                                        <small class="text-white-50">From 01-01-{{date('Y')}} to {{ date('d-m-Y') }} vs {{ $previousYearData['year'] }}</small>
                                    </div>

                                    <div class="section-content border rounded-bottom">
                                        <!-- Revenue Section -->
                                        <div class="section-title p-2 border-bottom" style="background: linear-gradient(135deg, #059669 0%, #10b981 50%, #34d399 100%);">
                                            <h6 class="mb-0 text-white fw-bold"><i class="bx bx-trending-up me-1"></i>INCOME</h6>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-striped">
                                                <thead class="table-secondary">
                                                    <tr>
                                                        <th>Account</th>
                                                        <th class="text-end">Current Year</th>
                                                        <th class="text-end">Previous Year</th>
                                                        <th class="text-end">Change</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php
                                                    $sumRevenue = collect($financialReportData['chartAccountsRevenues'])->sum('total');
                                                    $sumRevenuePrev = collect($previousYearData['chartAccountsRevenues'])->sum('total');
                                                    @endphp
                                                    @foreach($financialReportData['chartAccountsRevenues'] as $mainGroupName => $mainGroup)
                                                    @php
                                                    $prevMainGroup = $previousYearData['chartAccountsRevenues'][$mainGroupName] ?? null;
                                                    $prevMainGroupTotal = $prevMainGroup['total'] ?? 0;
                                                    $currentMainGroupTotal = $mainGroup['total'] ?? 0;
                                                    @endphp
                                                    @if($currentMainGroupTotal != 0 || $prevMainGroupTotal != 0)
                                                    @php
                                                    $mainGroupId = 'income-' . Str::slug($mainGroupName);
                                                    $mainGroupChange = $currentMainGroupTotal - $prevMainGroupTotal;
                                                    @endphp
                                                    <tr class="table-primary parent-row clickable" data-bs-toggle="collapse" data-bs-target=".{{ $mainGroupId }}" aria-expanded="true">
                                                        <td class="fw-bold text-dark">
                                                            <i class="bx bx-chevron-down me-1 transition-icon"></i>
                                                            <i class="bx bx-folder me-1"></i>{{ $mainGroupName }}
                                                        </td>
                                                        <td class="text-end fw-bold text-dark">{{ number_format($currentMainGroupTotal, 2) }}</td>
                                                        <td class="text-end fw-bold text-dark">{{ number_format($prevMainGroupTotal, 2) }}</td>
                                                        <td class="text-end fw-bold text-dark">
                                                            {{ $mainGroupChange >= 0 ? '+' : '' }}{{ number_format($mainGroupChange, 2) }}
                                                        </td>
                                                    </tr>
                                                    @foreach($mainGroup['fslis'] as $fsliName => $fsli)
                                                    @php
                                                    $prevFsli = $prevMainGroup['fslis'][$fsliName] ?? null;
                                                    $prevFsliTotal = $prevFsli['total'] ?? 0;
                                                    $currentFsliTotal = $fsli['total'] ?? 0;
                                                    @endphp
                                                    @if($currentFsliTotal != 0 || $prevFsliTotal != 0)
                                                    @php
                                                    $fsliId = 'fsli-income-' . Str::slug($fsliName);
                                                    $fsliChange = $currentFsliTotal - $prevFsliTotal;
                                                    @endphp
                                                    <tr class="table-light collapse show {{ $mainGroupId }} fsli-row clickable" data-bs-toggle="collapse" data-bs-target=".{{ $fsliId }}" aria-expanded="false">
                                                        <td class="ps-4 fw-medium text-dark">
                                                            <i class="bx bx-chevron-right me-1 transition-icon"></i>
                                                            {{ $fsliName }}
                                                        </td>
                                                        <td class="text-end fw-medium text-dark">{{ number_format($currentFsliTotal, 2) }}</td>
                                                        <td class="text-end fw-medium text-dark">{{ number_format($prevFsliTotal, 2) }}</td>
                                                        <td class="text-end fw-medium text-dark">
                                                            {{ $fsliChange >= 0 ? '+' : '' }}{{ number_format($fsliChange, 2) }}
                                                        </td>
                                                    </tr>
                                                    @if(isset($fsli['accounts']))
                                                    @foreach($fsli['accounts'] as $chartAccountRevenue)
                                                    @include('partials.dashboard-account-row', [
                                                    'account' => $chartAccountRevenue,
                                                    'mainGroupName' => $mainGroupName,
                                                    'fsliName' => $fsliName,
                                                    'fsliId' => $fsliId,
                                                    'previousYearData' => $previousYearData['chartAccountsRevenues'],
                                                    'depth' => 0
                                                    ])
                                                    @endforeach
                                                    @endif
                                                    @endif
                                                    @endforeach
                                                    @endif
                                                    @endforeach
                                                    <tr class="fw-bold" style="background: rgba(5, 150, 105, 0.25);">
                                                        <td class="text-dark">TOTAL INCOME</td>
                                                        <td class="text-end text-dark">{{ number_format($sumRevenue,2) }}</td>
                                                        <td class="text-end text-dark">{{ number_format($sumRevenuePrev,2) }}</td>
                                                        <td class="text-end text-dark">
                                                            @php $revenueChange = $sumRevenue - $sumRevenuePrev; @endphp

                                                            {{ $revenueChange >= 0 ? '+' : '' }}{{ number_format($revenueChange,2) }}

                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>

                                        <!-- Expenses Section -->
                                        <div class="section-title p-2 border-bottom mt-3" style="background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 50%, #fcd34d 100%);">
                                            <h6 class="mb-0 text-white fw-bold"><i class="bx bx-trending-down me-1"></i>EXPENSES</h6>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Account</th>
                                                        <th class="text-end">Current Year</th>
                                                        <th class="text-end">Previous Year</th>
                                                        <th class="text-end">Change</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php
                                                    $sumExpense = collect($financialReportData['chartAccountsExpense'])->sum('total');
                                                    $sumExpensePrev = collect($previousYearData['chartAccountsExpense'])->sum('total');
                                                    @endphp
                                                    @foreach($financialReportData['chartAccountsExpense'] as $mainGroupName => $mainGroup)
                                                    @php
                                                    $prevMainGroup = $previousYearData['chartAccountsExpense'][$mainGroupName] ?? null;
                                                    $prevMainGroupTotal = $prevMainGroup['total'] ?? 0;
                                                    $currentMainGroupTotal = $mainGroup['total'] ?? 0;
                                                    @endphp
                                                    @if($currentMainGroupTotal != 0 || $prevMainGroupTotal != 0)
                                                    @php
                                                    $mainGroupId = 'expense-' . Str::slug($mainGroupName);
                                                    $mainGroupChange = $currentMainGroupTotal - $prevMainGroupTotal;
                                                    @endphp
                                                    <tr class="table-primary parent-row clickable" data-bs-toggle="collapse" data-bs-target=".{{ $mainGroupId }}" aria-expanded="true">
                                                        <td class="fw-bold text-dark">
                                                            <i class="bx bx-chevron-down me-1 transition-icon"></i>
                                                            <i class="bx bx-folder me-1"></i>{{ $mainGroupName }}
                                                        </td>
                                                        <td class="text-end fw-bold text-dark">{{ number_format($currentMainGroupTotal, 2) }}</td>
                                                        <td class="text-end fw-bold text-dark">{{ number_format($prevMainGroupTotal, 2) }}</td>
                                                        <td class="text-end fw-bold text-dark">
                                                            {{ $mainGroupChange >= 0 ? '+' : '' }}{{ number_format($mainGroupChange, 2) }}
                                                        </td>
                                                    </tr>
                                                    @foreach($mainGroup['fslis'] as $fsliName => $fsli)
                                                    @php
                                                    $prevFsli = $prevMainGroup['fslis'][$fsliName] ?? null;
                                                    $prevFsliTotal = $prevFsli['total'] ?? 0;
                                                    $currentFsliTotal = $fsli['total'] ?? 0;
                                                    @endphp
                                                    @if($currentFsliTotal != 0 || $prevFsliTotal != 0)
                                                    @php
                                                    $fsliId = 'fsli-expense-' . Str::slug($fsliName);
                                                    $fsliChange = $currentFsliTotal - $prevFsliTotal;
                                                    @endphp
                                                    <tr class="table-light collapse show {{ $mainGroupId }} fsli-row clickable" data-bs-toggle="collapse" data-bs-target=".{{ $fsliId }}" aria-expanded="false">
                                                        <td class="ps-4 fw-medium text-dark">
                                                            <i class="bx bx-chevron-right me-1 transition-icon"></i>
                                                            {{ $fsliName }}
                                                        </td>
                                                        <td class="text-end fw-medium text-dark">{{ number_format($currentFsliTotal, 2) }}</td>
                                                        <td class="text-end fw-medium text-dark">{{ number_format($prevFsliTotal, 2) }}</td>
                                                        <td class="text-end fw-medium text-dark">
                                                            {{ $fsliChange >= 0 ? '+' : '' }}{{ number_format($fsliChange, 2) }}
                                                        </td>
                                                    </tr>
                                                    @if(isset($fsli['accounts']))
                                                    @foreach($fsli['accounts'] as $chartAccountExpense)
                                                    @include('partials.dashboard-account-row', [
                                                    'account' => $chartAccountExpense,
                                                    'mainGroupName' => $mainGroupName,
                                                    'fsliName' => $fsliName,
                                                    'fsliId' => $fsliId,
                                                    'parentClasses' => $mainGroupId,
                                                    'previousYearData' => $previousYearData['chartAccountsExpense'],
                                                    'depth' => 0
                                                    ])
                                                    @endforeach
                                                    @endif
                                                    @endif
                                                    @endforeach
                                                    @endif
                                                    @endforeach
                                                    <tr class="fw-bold" style="background: rgba(245, 158, 11, 0.25);">
                                                        <td class="text-dark">TOTAL EXPENSES</td>
                                                        <td class="text-end text-dark">{{ number_format($sumExpense,2) }}</td>
                                                        <td class="text-end text-dark">{{ number_format($sumExpensePrev,2) }}</td>
                                                        <td class="text-end text-dark">
                                                            @php $expenseChange = $sumExpense - $sumExpensePrev; @endphp

                                                            {{ $expenseChange >= 0 ? '+' : '' }}{{ number_format($expenseChange,2) }}

                                                        </td>
                                                    </tr>
                                                    <tr class="fw-bold" style="background: rgba(139, 92, 246, 0.25);">
                                                        <td class="text-dark">NET PROFIT/LOSS</td>
                                                        <td class="text-end text-dark">{{ number_format($sumRevenue - $sumExpense,2) }}</td>
                                                        <td class="text-end text-dark">{{ number_format($sumRevenuePrev - $sumExpensePrev,2) }}</td>
                                                        <td class="text-end text-dark">
                                                            @php $netProfitChange = ($sumRevenue - $sumExpense) - ($sumRevenuePrev - $sumExpensePrev); @endphp

                                                            {{ $netProfitChange >= 0 ? '+' : '' }}{{ number_format($netProfitChange,2) }}

                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endcan

        <!-- Contributions and Shares Section -->
        <div class="row">
            <!-- Contributions Section -->
            <div class="col-md-6">
                <div class="card radius-10">
                    <div class="card-header bg-transparent">
                        <h5 class="mb-0"><i class="bx bx-donate-heart me-2 text-info"></i>Contributions</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Product Name</th>
                                        <th class="text-end">Balance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($contributions as $contribution)
                                    <tr>
                                        <td>
                                            <strong>{{ $contribution['product_name'] }}</strong>
                                        </td>
                                        <td class="text-end">
                                            <span class="badge bg-info">
                                                TZS {{ number_format($contribution['balance'], 2) }}
                                            </span>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="2" class="text-center text-muted">No contributions available</td>
                                    </tr>
                                    @endforelse
                                    @if($contributions->isNotEmpty())
                                    <tr class="table-secondary fw-bold">
                                        <td>Total Contributions</td>
                                        <td class="text-end">
                                            <span class="badge bg-primary">
                                                TZS {{ number_format($contributions->sum('balance'), 2) }}
                                            </span>
                                        </td>
                                    </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Shares Section -->
            <div class="col-md-6">
                <div class="card radius-10">
                    <div class="card-header bg-transparent">
                        <h5 class="mb-0"><i class="bx bx-pie-chart-alt-2 me-2 text-success"></i>Shares</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Product Name</th>
                                        <th class="text-end">Balance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($shares as $share)
                                    <tr>
                                        <td>
                                            <strong>{{ $share['share_name'] }}</strong>
                                        </td>
                                        <td class="text-end">
                                            <span class="badge bg-success">
                                                TZS {{ number_format($share['balance'], 2) }}
                                            </span>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="2" class="text-center text-muted">No shares available</td>
                                    </tr>
                                    @endforelse
                                    @if($shares->isNotEmpty())
                                    <tr class="table-secondary fw-bold">
                                        <td>Total Shares</td>
                                        <td class="text-end">
                                            <span class="badge bg-primary">
                                                TZS {{ number_format($shares->sum('balance'), 2) }}
                                            </span>
                                        </td>
                                    </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--end row-->

        <!-- Send Bulk SMS Button
        <div class="d-flex justify-content-end mb-3">
            <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#bulkSmsModal">
                <i class="bx bx-envelope"></i> Send Bulk SMS
            </button>
        </div> -->

        <!-- Bulk SMS Modal -->
        <div class="modal fade" id="bulkSmsModal" tabindex="-1" aria-labelledby="bulkSmsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="bulkSmsModalLabel">
                            <i class="bx bx-envelope me-2"></i>Send Bulk SMS
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="bulkSmsForm" action="{{ route('sms.bulk') }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="branch_id" class="form-label">Select Branch</label>
                                <select class="form-select" id="branch_id" name="branch_id" required>
                                    <option value="all">All Branches</option>
                                    @foreach(App\Models\Branch::all() as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="message_title" class="form-label fw-bold">Message Title</label>
                                <select class="form-select" id="message_title" name="message_title" required>
                                    <option value="">Select a title...</option>
                                    <option value="Payment Reminder">Payment Reminder</option>
                                    <option value="Loan Approved">Loan Approved</option>
                                    <option value="Loan Disbursed">Loan Disbursed</option>
                                    <option value="Custom">Custom Title</option>
                                </select>
                                <div class="form-text">Choose a title for this SMS batch or select Custom to enter your own.</div>
                            </div>
                            <div class="mb-3">
                                <label for="bulk_message_content" class="form-label">Message Content</label>
                                <textarea class="form-control" id="bulk_message_content" name="bulk_message_content" rows="4" maxlength="500" required></textarea>
                                <div class="form-text"><span id="bulk_character_count">0</span>/500 characters</div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="bx bx-x me-1"></i>Cancel
                            </button>
                            <button type="submit" class="btn btn-primary" id="sendBulkSmsBtn">
                                <i class="bx bx-send me-1"></i>Send Bulk SMS
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            // Character counter for bulk SMS
            function updateBulkCharacterCount() {
                const bulkMessageContent = document.getElementById('bulk_message_content');
                const bulkCharacterCount = document.getElementById('bulk_character_count');
                const count = bulkMessageContent.value.length;
                bulkCharacterCount.textContent = count;
                if (count > 500) {
                    bulkCharacterCount.style.color = 'red';
                } else if (count > 450) {
                    bulkCharacterCount.style.color = 'orange';
                } else {
                    bulkCharacterCount.style.color = 'green';
                }
            }
            document.getElementById('bulk_message_content').addEventListener('input', updateBulkCharacterCount);

            // Bulk SMS form submission
            const bulkSmsForm = document.getElementById('bulkSmsForm');
            bulkSmsForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const sendBtn = document.getElementById('sendBulkSmsBtn');
                const originalText = sendBtn.innerHTML;
                const modal = document.getElementById('bulkSmsModal');
                const formElements = modal.querySelectorAll('input, textarea, select, button');
                const closeBtn = modal.querySelector('.btn-close');
                // Show loading state and disable all form elements
                sendBtn.innerHTML = '<i class="bx bx-loader-alt bx-spin me-1"></i>Sending...';
                sendBtn.disabled = true;
                formElements.forEach(element => {
                    element.disabled = true;
                });
                if (closeBtn) closeBtn.disabled = true;
                const modalBody = modal.querySelector('.modal-body');
                modalBody.style.opacity = '0.7';
                // Submit the form via AJAX
                fetch(this.action, {
                        method: 'POST',
                        body: new FormData(this),
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        let responseMsg = '';
                        if (typeof data.response === 'string') {
                            try {
                                const parsed = JSON.parse(data.response);
                                responseMsg = parsed.message || data.message || '';
                            } catch (e) {
                                responseMsg = data.response || data.message || '';
                            }
                        } else if (typeof data.response === 'object' && data.response !== null) {
                            responseMsg = data.response.message || data.message || '';
                        } else {
                            responseMsg = data.message || '';
                        }
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Bulk SMS Sent!',
                                html: `<div><b>${responseMsg}</b></div>`,
                                confirmButtonColor: '#28a745',
                                timer: 3000,
                                timerProgressBar: true,
                                showConfirmButton: true
                            });
                            bulkSmsForm.reset();
                            updateBulkCharacterCount();
                            const modalInstance = bootstrap.Modal.getInstance(modal);
                            modalInstance.hide();
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Failed to Send Bulk SMS',
                                text: responseMsg || 'Unknown error occurred',
                                confirmButtonColor: '#dc3545',
                                footer: 'Please try again or contact support if the problem persists.'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Connection Error',
                            text: 'Failed to send bulk SMS due to connection issues.',
                            confirmButtonColor: '#dc3545',
                            footer: 'Please check your internet connection and try again.'
                        });
                    })
                    .finally(() => {
                        sendBtn.innerHTML = originalText;
                        sendBtn.disabled = false;
                        formElements.forEach(element => {
                            element.disabled = false;
                        });
                        if (closeBtn) closeBtn.disabled = false;
                        modalBody.style.opacity = '1';
                    });
            });
        </script>
        @endpush
    </div>
</div>
@endcan
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