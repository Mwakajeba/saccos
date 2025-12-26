@extends('layouts.main')
@section('title', 'Edit Journal Reference')

@section('content')
<div class="page-wrapper">
    <div class="page-content"> 
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Settings', 'url' => route('settings.index'), 'icon' => 'bx bx-cog'],
            ['label' => 'Journal References', 'url' => route('settings.journal-references.index'), 'icon' => 'bx bx-book'],
            ['label' => 'Edit Journal Reference', 'url' => '#', 'icon' => 'bx bx-edit']
        ]" />
        <h6 class="mb-0 text-uppercase">EDIT JOURNAL REFERENCE</h6>
        <hr/>
        <div class="card">
            <div class="card-body">
                <form action="{{ route('settings.journal-references.update', $journalReference->hash_id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bx bx-error-circle me-2"></i>
                        Please fix the following errors:
                        <ul class="mb-0 mt-2">
                            @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name', $journalReference->name) }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Reference <span class="text-danger">*</span></label>
                            <input type="text" name="reference" class="form-control @error('reference') is-invalid @enderror"
                                value="{{ old('reference', $journalReference->reference) }}" required>
                            @error('reference') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('settings.journal-references.index') }}" class="btn btn-secondary">
                            <i class="bx bx-x me-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-save me-1"></i> Update Journal Reference
                        </button>
                    </div>
                </form>
            </div>
        </div>       
    </div>
</div>
@endsection

