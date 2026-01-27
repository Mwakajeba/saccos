@extends('layouts.main')

@section('title', 'Asset Categories')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Asset Management', 'url' => route('assets.index'), 'icon' => 'bx bx-cabinet'],
            ['label' => 'Categories', 'url' => route('assets.categories.index'), 'icon' => 'bx bx-category']
        ]" />

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="mb-1 text-primary">Asset Categories</h5>
                <div class="text-muted">Manage standardized fixed asset categories and defaults</div>
            </div>
            <a href="{{ route('assets.categories.create') }}" class="btn btn-primary"><i class="bx bx-plus me-1"></i> New Category</a>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="categoriesTable" class="table table-striped table-hover align-middle" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th>Code</th>
                                <th>Name</th>
                                <th>Method</th>
                                <th>Useful Life</th>
                                <th>Rate (%)</th>
                                <th>Convention</th>
                                <th>Assets</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function(){
  console.log('Initializing Asset Categories DataTable...');
  console.log('jQuery version:', $.fn.jquery);
  console.log('DataTables available:', typeof $.fn.DataTable !== 'undefined');
  
  var table = $('#categoriesTable').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: '{{ route('assets.categories.data') }}',
      type: 'GET',
      dataSrc: function(json) {
        console.log('DataTable received data:', json);
        return json.data;
      },
      error: function(xhr, error, code) {
        console.error('DataTable AJAX Error:', error, code);
        console.error('Status:', xhr.status);
        console.error('Response:', xhr.responseText);
        if (xhr.status === 500) {
          try {
            var response = JSON.parse(xhr.responseText);
            Swal.fire('Server Error', response.message || 'An error occurred while loading data.', 'error');
          } catch(e) {
            Swal.fire('Error', 'Failed to load asset categories. Please refresh the page.', 'error');
          }
        } else if (xhr.status === 401) {
          Swal.fire('Unauthorized', 'Please login again.', 'warning').then(() => {
            window.location.href = '{{ route('login') }}';
          });
        } else {
          Swal.fire('Error', 'Failed to load asset categories. Status: ' + xhr.status, 'error');
        }
      }
    },
    columns: [
      { data: 'code', name: 'code', render: function(d){ return d ? `<span class="badge bg-light text-dark">${d}</span>` : ''; } },
      { data: 'name', name: 'name' },
      { data: 'default_depreciation_method', name: 'default_depreciation_method', render: function(d){ d = d||''; d = d.replace(/_/g,' '); return d ? `<span class="badge bg-info text-dark">${d.charAt(0).toUpperCase()+d.slice(1)}</span>` : ''; } },
      { data: 'default_useful_life_months', name: 'default_useful_life_months', render: function(d){ return d ? `${d} m` : '0 m'; } },
      { data: 'default_depreciation_rate', name: 'default_depreciation_rate', render: function(d){ d = d||0; return `${parseFloat(d).toFixed(2)}%`; } },
      { data: 'depreciation_convention', name: 'depreciation_convention', render: function(d){ d = d||''; d = d.replace(/_/g,' '); return d ? `<span class="badge bg-light text-dark">${d.charAt(0).toUpperCase()+d.slice(1)}</span>` : ''; } },
      { data: 'assets_count', name: 'assets_count', className: 'text-end', render: function(d){ return `<span class="badge bg-secondary">${d||0}</span>`; } },
      { data: 'actions', name: 'actions', orderable: false, searchable: false }
    ],
    order: [[1,'asc']],
    lengthMenu: [[10,25,50,100,500,-1],[10,25,50,100,500,'All']],
    pageLength: 25,
    dom: 'lfrtip'
  });
  
  console.log('DataTable initialized:', table);

  // SweetAlert delete confirmation
  $(document).on('click', '.btn-delete-category', function(){
    const $form = $(this).closest('form.category-delete-form');
    Swal.fire({
      title: 'Delete this category?',
      text: 'This action cannot be undone.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, delete',
      cancelButtonText: 'Cancel'
    }).then((result) => {
      if (result.isConfirmed) {
        $form.trigger('submit');
      }
    });
  });
});
</script>
@endpush


