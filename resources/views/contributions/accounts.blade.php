@extends('layouts.main')
@section('title', 'Contributions Accounts')
@section('content')
<div class="container py-4">
    <h2>Contribution Accounts</h2>
    <a href="{{ route('contributions.accounts.create') }}" class="btn btn-success mb-3">Add Accounts</a>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Customer</th>
                <th>Product</th>
                <th>Account Number</th>
            </tr>
        </thead>
        <tbody>
            @foreach($accounts as $account)
                <tr>
                    <td>{{ $account->customer->name ?? '-' }}</td>
                    <td>{{ $account->contributionProduct->product_name ?? '-' }}</td>
                    <td>{{ $account->account_number }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection