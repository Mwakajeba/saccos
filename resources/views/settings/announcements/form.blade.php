@php
    use Vinkla\Hashids\Facades\Hashids;
@endphp

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ isset($announcement) ? route('settings.announcements.update', Hashids::encode($announcement->id)) : route('settings.announcements.store') }}"
      method="POST" enctype="multipart/form-data">
    @csrf
    @if(isset($announcement))
        @method('PUT')
    @endif

    <div class="mb-3">
        <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
        <input type="text" name="title" id="title"
               class="form-control @error('title') is-invalid @enderror"
               value="{{ old('title', isset($announcement) ? $announcement->title : '') }}"
               placeholder="e.g. Habari za SACCOS, Mkopo Mpya">
        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="mb-3">
        <label for="message" class="form-label">Message <span class="text-danger">*</span></label>
        <textarea name="message" id="message" rows="4"
                  class="form-control @error('message') is-invalid @enderror"
                  placeholder="Enter the announcement message...">{{ old('message', isset($announcement) ? $announcement->message : '') }}</textarea>
        @error('message') <div class="invalid-feedback">{{ $message }}</div> @enderror
        <small class="form-text text-muted">
            <i class="bx bx-info-circle"></i> Keep the message concise (recommended: 100-150 characters for best display).
        </small>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label for="icon" class="form-label">Icon Name</label>
                <input type="text" name="icon" id="icon"
                       class="form-control @error('icon') is-invalid @enderror"
                       value="{{ old('icon', isset($announcement) ? $announcement->icon : '') }}"
                       placeholder="e.g. info_outline, credit_card, feedback">
                @error('icon') <div class="invalid-feedback">{{ $message }}</div> @enderror
                <small class="form-text text-muted">
                    <i class="bx bx-info-circle"></i> Material Icons name (optional).
                </small>
            </div>
        </div>
        <div class="col-md-6">
            <div class="mb-3">
                <label for="color" class="form-label">Color <span class="text-danger">*</span></label>
                <select name="color" id="color"
                        class="form-select @error('color') is-invalid @enderror">
                    <option value="">Select Color</option>
                    <option value="blue" {{ old('color', isset($announcement) ? $announcement->color : '') == 'blue' ? 'selected' : '' }}>Blue</option>
                    <option value="green" {{ old('color', isset($announcement) ? $announcement->color : '') == 'green' ? 'selected' : '' }}>Green</option>
                    <option value="orange" {{ old('color', isset($announcement) ? $announcement->color : '') == 'orange' ? 'selected' : '' }}>Orange</option>
                    <option value="red" {{ old('color', isset($announcement) ? $announcement->color : '') == 'red' ? 'selected' : '' }}>Red</option>
                    <option value="purple" {{ old('color', isset($announcement) ? $announcement->color : '') == 'purple' ? 'selected' : '' }}>Purple</option>
                    <option value="yellow" {{ old('color', isset($announcement) ? $announcement->color : '') == 'yellow' ? 'selected' : '' }}>Yellow</option>
                </select>
                @error('color') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>
    </div>

    <div class="mb-3">
        <label for="image" class="form-label">Image</label>
        <input type="file" name="image" id="image"
               class="form-control @error('image') is-invalid @enderror"
               accept="image/jpeg,image/png,image/jpg,image/gif">
        @error('image') <div class="invalid-feedback">{{ $message }}</div> @enderror
        @if(isset($announcement) && $announcement->image_path)
            <div class="mt-2">
                <img src="{{ asset('storage/' . $announcement->image_path) }}" 
                     alt="Current image" 
                     style="max-width: 200px; max-height: 120px; border-radius: 8px;">
                <p class="text-muted small mt-1">Current image</p>
            </div>
        @endif
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="mb-3">
                <label for="order" class="form-label">Display Order</label>
                <input type="number" name="order" id="order"
                       class="form-control @error('order') is-invalid @enderror"
                       value="{{ old('order', isset($announcement) ? $announcement->order : 0) }}"
                       min="0">
                @error('order') <div class="invalid-feedback">{{ $message }}</div> @enderror
                <small class="form-text text-muted">
                    Lower numbers appear first.
                </small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="mb-3">
                <label for="start_date" class="form-label">Start Date</label>
                <input type="datetime-local" name="start_date" id="start_date"
                       class="form-control @error('start_date') is-invalid @enderror"
                       value="{{ old('start_date', isset($announcement) && $announcement->start_date ? $announcement->start_date->format('Y-m-d\TH:i') : '') }}">
                @error('start_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>
        <div class="col-md-4">
            <div class="mb-3">
                <label for="end_date" class="form-label">End Date</label>
                <input type="datetime-local" name="end_date" id="end_date"
                       class="form-control @error('end_date') is-invalid @enderror"
                       value="{{ old('end_date', isset($announcement) && $announcement->end_date ? $announcement->end_date->format('Y-m-d\TH:i') : '') }}">
                @error('end_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>
    </div>

    <div class="mb-3">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" 
                   value="1" {{ old('is_active', isset($announcement) ? $announcement->is_active : true) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_active">
                Active (Show in mobile app)
            </label>
        </div>
    </div>

    <div class="d-flex justify-content-between">
        <a href="{{ route('settings.announcements.index') }}" class="btn btn-secondary">
            <i class="bx bx-arrow-back me-1"></i> Back
        </a>
        <button type="submit" class="btn btn-primary">
            <i class="bx bx-save me-1"></i> {{ isset($announcement) ? 'Update' : 'Save' }}
        </button>
    </div>
</form>
