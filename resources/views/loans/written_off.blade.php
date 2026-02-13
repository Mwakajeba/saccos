@extends('layouts.main')

@section('title', 'Written Off Loans')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Loans', 'url' => route('loans.index'), 'icon' => 'bx bx-credit-card'],
            ['label' => 'Written Off Loans', 'url' => '#', 'icon' => 'bx bx-x-circle'],
        ]" />
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h6 class="mb-0 text-uppercase">Written Off Loans</h6>
            @can('view writeoff loans')
            <a href="{{ route('loans.writeoffs.index') }}" class="btn btn-outline-danger">
                <i class="bx bx-list-ul me-1"></i> Write-off History
            </a>
            @endcan
        </div>
        <hr />
        <div class="card">
            <div class="card-body">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Product</th>
                            <th>Amount</th>
                            <th>Total</th>
                            <th>Branch</th>
                            <th>Date Applied</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($loans as $loan)
                            <tr>
                                <td>{{ optional($loan->customer)->name ?? 'N/A' }}</td>
                                <td>{{ optional($loan->product)->name ?? 'N/A' }}</td>
                                <td>{{ number_format($loan->amount, 2) }}</td>
                                <td>{{ number_format($loan->amount_total, 2) }}</td>
                                <td>{{ optional($loan->branch)->name ?? 'N/A' }}</td>
                                <td>{{ $loan->date_applied }}</td>
                                <td>
                                    <button type="button" class="btn btn-primary btn-sm" onclick="openRepaymentModal({{ $loan->id }}, '{{ $loan->loanNo ?? $loan->id }}', '{{ optional($loan->customer)->name ?? 'N/A' }}')">
                                        Repayment Receipt
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">No written-off loans found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

