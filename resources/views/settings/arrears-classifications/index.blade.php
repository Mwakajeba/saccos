@extends('layouts.main')

@section('title', 'Arrears Classifications')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Settings', 'url' => route('settings.index'), 'icon' => 'bx bx-cog'],
            ['label' => 'Arrears Classifications', 'url' => '#', 'icon' => 'bx bx-category']
        ]" />
        
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 text-uppercase">Arrears Classifications / Aging Buckets</h6>
            <a href="{{ route('settings.index') }}" class="btn btn-secondary btn-sm">
                <i class="bx bx-arrow-back me-1"></i> Back to Settings
            </a>
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

        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h5 class="card-title mb-1">Loan Aging Buckets & Provision Rates</h5>
                        <p class="text-muted mb-0">Configure days past due ranges, classification statuses, and provision percentages</p>
                    </div>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addClassificationModal">
                        <i class="bx bx-plus me-1"></i> Add Classification
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 60px;">#</th>
                                <th>Bucket (Days)</th>
                                <th>Status</th>
                                <th>Provision %</th>
                                <th>Comments</th>
                                <th>Active</th>
                                <th style="width: 150px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($classifications as $classification)
                            <tr>
                                <td>{{ $classification->sort_order }}</td>
                                <td>
                                    <span class="badge bg-secondary">{{ $classification->bucket_label }}</span>
                                </td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'Current' => 'success',
                                            'Past Due' => 'info',
                                            'Substandard' => 'warning',
                                            'Doubtful' => 'danger',
                                            'Loss/NPL' => 'dark'
                                        ];
                                        $color = $statusColors[$classification->status] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $color }}">{{ $classification->status }}</span>
                                </td>
                                <td>
                                    <strong>{{ number_format($classification->provision_percentage, 2) }}%</strong>
                                </td>
                                <td>{{ $classification->comments ?? '-' }}</td>
                                <td>
                                    @if($classification->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="editClassification({{ json_encode($classification) }})">
                                        <i class="bx bx-edit"></i>
                                    </button>
                                    <form action="{{ route('settings.arrears-classifications.destroy', $classification->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this classification?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <i class="bx bx-info-circle fs-3 text-muted"></i>
                                    <p class="mb-0 text-muted">No classifications found. Click "Add Classification" to create one.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($classifications->isEmpty())
                <div class="mt-4">
                    <div class="alert alert-info">
                        <h6 class="alert-heading"><i class="bx bx-bulb me-2"></i>Suggested Default Classifications</h6>
                        <p class="mb-2">You can set up the following standard classifications:</p>
                        <ul class="mb-2">
                            <li><strong>0 days</strong> - Current (0% provision)</li>
                            <li><strong>1-30 days</strong> - Past Due (1% provision)</li>
                            <li><strong>31-90 days</strong> - Substandard (5% provision)</li>
                            <li><strong>91-180 days</strong> - Doubtful (25% or 50% provision)</li>
                            <li><strong>181+ days</strong> - Loss/NPL (100% provision)</li>
                        </ul>
                        <button type="button" class="btn btn-info btn-sm" onclick="seedDefaultClassifications()">
                            <i class="bx bx-magic-wand me-1"></i> Create Default Classifications
                        </button>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Add Classification Modal -->
<div class="modal fade" id="addClassificationModal" tabindex="-1" aria-labelledby="addClassificationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('settings.arrears-classifications.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addClassificationModalLabel">
                        <i class="bx bx-plus-circle me-2"></i>Add Arrears Classification
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="days_from" class="form-label">Days From <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="days_from" name="days_from" min="0" required>
                            <small class="text-muted">Starting day (e.g., 0, 1, 31, 91, 181)</small>
                        </div>
                        <div class="col-md-6">
                            <label for="days_to" class="form-label">Days To</label>
                            <input type="number" class="form-control" id="days_to" name="days_to" min="0">
                            <small class="text-muted">Leave empty for unlimited (e.g., 181+)</small>
                        </div>
                        <div class="col-md-12">
                            <label for="bucket_label" class="form-label">Bucket Label <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="bucket_label" name="bucket_label" placeholder="e.g., 1-30, 31-90, 181+" required>
                            <small class="text-muted">Display label for this bucket</small>
                        </div>
                        <div class="col-md-6">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="">-- Select Status --</option>
                                <option value="Current">Current</option>
                                <option value="Past Due">Past Due</option>
                                <option value="Substandard">Substandard</option>
                                <option value="Doubtful">Doubtful</option>
                                <option value="Loss/NPL">Loss/NPL</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="provision_percentage" class="form-label">Provision % <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="provision_percentage" name="provision_percentage" min="0" max="100" step="0.01" required>
                            <small class="text-muted">e.g., 0, 1, 5, 25, 50, 100</small>
                        </div>
                        <div class="col-md-6">
                            <label for="sort_order" class="form-label">Sort Order</label>
                            <input type="number" class="form-control" id="sort_order" name="sort_order" min="0" value="0">
                        </div>
                        <div class="col-md-6">
                            <label for="is_active" class="form-label">Status</label>
                            <select class="form-select" id="is_active" name="is_active">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label for="comments" class="form-label">Comments</label>
                            <textarea class="form-control" id="comments" name="comments" rows="3" placeholder="Enter any additional notes or comments..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bx bx-x me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-save me-1"></i>Save Classification
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Classification Modal -->
<div class="modal fade" id="editClassificationModal" tabindex="-1" aria-labelledby="editClassificationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editClassificationForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="editClassificationModalLabel">
                        <i class="bx bx-edit me-2"></i>Edit Arrears Classification
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="edit_days_from" class="form-label">Days From <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="edit_days_from" name="days_from" min="0" required>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_days_to" class="form-label">Days To</label>
                            <input type="number" class="form-control" id="edit_days_to" name="days_to" min="0">
                            <small class="text-muted">Leave empty for unlimited</small>
                        </div>
                        <div class="col-md-12">
                            <label for="edit_bucket_label" class="form-label">Bucket Label <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_bucket_label" name="bucket_label" required>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit_status" name="status" required>
                                <option value="">-- Select Status --</option>
                                <option value="Current">Current</option>
                                <option value="Past Due">Past Due</option>
                                <option value="Substandard">Substandard</option>
                                <option value="Doubtful">Doubtful</option>
                                <option value="Loss/NPL">Loss/NPL</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_provision_percentage" class="form-label">Provision % <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="edit_provision_percentage" name="provision_percentage" min="0" max="100" step="0.01" required>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_sort_order" class="form-label">Sort Order</label>
                            <input type="number" class="form-control" id="edit_sort_order" name="sort_order" min="0">
                        </div>
                        <div class="col-md-6">
                            <label for="edit_is_active" class="form-label">Status</label>
                            <select class="form-select" id="edit_is_active" name="is_active">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label for="edit_comments" class="form-label">Comments</label>
                            <textarea class="form-control" id="edit_comments" name="comments" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bx bx-x me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-save me-1"></i>Update Classification
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function editClassification(classification) {
        document.getElementById('editClassificationForm').action = '/settings/arrears-classifications/' + classification.id;
        document.getElementById('edit_days_from').value = classification.days_from;
        document.getElementById('edit_days_to').value = classification.days_to || '';
        document.getElementById('edit_bucket_label').value = classification.bucket_label;
        document.getElementById('edit_status').value = classification.status;
        document.getElementById('edit_provision_percentage').value = classification.provision_percentage;
        document.getElementById('edit_sort_order').value = classification.sort_order;
        document.getElementById('edit_is_active').value = classification.is_active ? '1' : '0';
        document.getElementById('edit_comments').value = classification.comments || '';
        
        var modal = new bootstrap.Modal(document.getElementById('editClassificationModal'));
        modal.show();
    }

    function seedDefaultClassifications() {
        Swal.fire({
            title: 'Create Default Classifications?',
            text: 'This will create the standard arrears classifications with default provision rates.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, create them!'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('{{ route("settings.arrears-classifications.seed-defaults") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: data.message,
                            confirmButtonColor: '#3085d6'
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: data.message,
                            confirmButtonColor: '#d33'
                        });
                    }
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'An error occurred. Please try again.',
                        confirmButtonColor: '#d33'
                    });
                });
            }
        });
    }
</script>
@endpush
