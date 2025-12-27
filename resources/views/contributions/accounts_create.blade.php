@extends('layouts.main')
@section('title', 'Create Contribution Accounts')
@section('content')
<div class="container py-4">
    <h2>Create Contribution Accounts</h2>
    <form method="POST" action="{{ route('contributions.accounts.store') }}">
        @csrf
        <div class="mb-3">
            <label for="contribution_product_id" class="form-label">Contribution Product</label>
            <select name="contribution_product_id" id="contribution_product_id" class="form-control" required>
                <option value="">Select Product</option>
                @foreach($products as $product)
                    <option value="{{ $product->id }}">{{ $product->product_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="customer_ids" class="form-label">Customers</label>
            <select name="customer_ids[]" id="customer_ids" class="form-control" multiple required>
                @foreach($customers as $customer)
                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                @endforeach
            </select>
            <small class="form-text text-muted">Hold Ctrl (Cmd on Mac) to select multiple customers.</small>
        </div>
        <button type="submit" class="btn btn-primary">Create Accounts</button>
    </form>
</div>
@endsection
