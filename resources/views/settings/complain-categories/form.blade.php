@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ isset($complainCategory) ? route('settings.complain-categories.update', $complainCategory) : route('settings.complain-categories.store') }}"
      method="POST">
    @csrf
    @if(isset($complainCategory))
        @method('PUT')
    @endif

    <div class="mb-3">
        <label for="name" class="form-label">Complain Category <span class="text-danger">*</span></label>
        <input type="text" name="name" id="name"
               class="form-control @error('name') is-invalid @enderror"
               value="{{ old('name', $complainCategory->name ?? '') }}"
               placeholder="e.g. Service Quality, Payment Issues, Account Problems">
        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="mb-3">
        <label for="description" class="form-label">Description</label>
        <textarea name="description" id="description" rows="4"
                  class="form-control @error('description') is-invalid @enderror"
                  placeholder="Enter a description for this category...">{{ old('description', $complainCategory->description ?? '') }}</textarea>
        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="mb-3">
        <label for="priority" class="form-label">Priority <span class="text-danger">*</span></label>
        <select name="priority" id="priority"
                class="form-select @error('priority') is-invalid @enderror">
            <option value="">Select Priority</option>
            <option value="low" {{ old('priority', $complainCategory->priority ?? '') == 'low' ? 'selected' : '' }}>Low</option>
            <option value="medium" {{ old('priority', $complainCategory->priority ?? '') == 'medium' ? 'selected' : '' }}>Medium</option>
            <option value="high" {{ old('priority', $complainCategory->priority ?? '') == 'high' ? 'selected' : '' }}>High</option>
        </select>
        @error('priority') <div class="invalid-feedback">{{ $message }}</div> @enderror
        <small class="form-text text-muted">
            <i class="bx bx-info-circle"></i> Priority helps categorize the urgency of complaints.
        </small>
    </div>

    <div class="d-flex justify-content-between">
        <a href="{{ route('settings.complain-categories.index') }}" class="btn btn-secondary">
            <i class="bx bx-arrow-back me-1"></i> Back
        </a>
        <button type="submit" class="btn btn-primary">
            <i class="bx bx-save me-1"></i> {{ isset($complainCategory) ? 'Update' : 'Save' }}
        </button>
    </div>
</form>
