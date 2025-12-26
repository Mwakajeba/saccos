@extends('layouts.main')

@section('title', 'Contribution Products')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Contributions', 'url' => route('contributions.index'), 'icon' => 'bx bx-donate-heart'],
            ['label' => 'Contribution Products', 'url' => '#', 'icon' => 'bx bx-package']
        ]" />
        
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 text-uppercase">CONTRIBUTION PRODUCTS</h6>
            <a href="{{ route('contributions.products.create') }}" class="btn btn-primary">
                <i class="bx bx-plus"></i> Add New Product
            </a>
        </div>
        <hr />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered dt-responsive nowrap w-100" id="productsTable">
                                <thead>
                                    <tr>
                                        <th>Product Name</th>
                                        <th>Category</th>
                                        <th>Interest</th>
                                        <th>Auto Create</th>
                                        <th>Compound Period</th>
                                        <th>Status</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($products as $product)
                                    <tr>
                                        <td>
                                            <strong>{{ $product->product_name }}</strong>
                                            @if($product->description)
                                            <br><small class="text-muted">{{ Str::limit($product->description, 50) }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $product->category === 'Mandatory' ? 'warning' : 'info' }}">
                                                {{ $product->category }}
                                            </span>
                                        </td>
                                        <td>{{ number_format($product->interest, 2) }}%</td>
                                        <td>{{ $product->auto_create }}</td>
                                        <td>{{ $product->compound_period }}</td>
                                        <td>
                                            @if($product->is_active)
                                            <span class="badge bg-success">Active</span>
                                            @else
                                            <span class="badge bg-danger">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group" role="group">
                                                <a href="#" class="btn btn-sm btn-outline-primary" title="Edit">
                                                    <i class="bx bx-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-outline-danger" title="Delete">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            <i class="bx bx-package fs-1 d-block mb-2"></i>
                                            No contribution products found. 
                                            <a href="{{ route('contributions.products.create') }}">Create your first product</a>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#productsTable').DataTable({
            responsive: true,
            order: [[0, 'asc']],
            pageLength: 25,
        });
    });
</script>
@endpush
