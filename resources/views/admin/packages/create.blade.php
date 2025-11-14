@extends('layouts.admin')

@section('title', 'Create Package')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Create New Package</h5>
                        <a href="{{ route('admin.packages.index') }}" class="btn btn-outline-secondary">
                            <svg class="icon me-2">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-arrow-left') }}"></use>
                            </svg>
                            Back to Packages
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.packages.store') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="row">
                            <div class="col-md-8">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="mb-3">
                                            <label for="name" class="form-label">Package Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                                   id="name" name="name" value="{{ old('name') }}" required>
                                            @error('name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="slug" class="form-label">Slug (URL)</label>
                                            <input type="text" class="form-control @error('slug') is-invalid @enderror"
                                                   id="slug" name="slug" value="{{ old('slug') }}">
                                            <div class="form-text">Leave empty to auto-generate from name</div>
                                            @error('slug')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="price" class="form-label">Price ({{ currency_symbol() }}) <span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" min="0.01" class="form-control @error('price') is-invalid @enderror"
                                                   id="price" name="price" value="{{ old('price') }}" required>
                                            @error('price')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="points_awarded" class="form-label">Points Awarded <span class="text-danger">*</span></label>
                                            <input type="number" min="0" class="form-control @error('points_awarded') is-invalid @enderror"
                                                   id="points_awarded" name="points_awarded" value="{{ old('points_awarded') }}" required>
                                            @error('points_awarded')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="quantity_available" class="form-label">Quantity Available</label>
                                            <input type="number" min="0" class="form-control @error('quantity_available') is-invalid @enderror"
                                                   id="quantity_available" name="quantity_available" value="{{ old('quantity_available') }}">
                                            <div class="form-text">Leave empty for unlimited quantity</div>
                                            @error('quantity_available')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="short_description" class="form-label">Short Description <span class="text-danger">*</span></label>
                                    <textarea class="form-control @error('short_description') is-invalid @enderror"
                                              id="short_description" name="short_description" rows="2" maxlength="500" required>{{ old('short_description') }}</textarea>
                                    <div class="form-text">Maximum 500 characters</div>
                                    @error('short_description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="long_description" class="form-label">Long Description <span class="text-danger">*</span></label>
                                    <textarea class="form-control @error('long_description') is-invalid @enderror"
                                              id="long_description" name="long_description" rows="6" required>{{ old('long_description') }}</textarea>
                                    @error('long_description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Package Features -->
                                <div class="mb-4">
                                    <label class="form-label">Package Features</label>
                                    <div id="features-container">
                                        <div class="input-group mb-2 feature-input">
                                            <input type="text" class="form-control" name="features[]" placeholder="Enter a feature" value="{{ old('features.0') }}">
                                            <button type="button" class="btn btn-outline-danger remove-feature" style="display: none;">
                                                <svg class="icon">
                                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-x') }}"></use>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                    <button type="button" id="add-feature" class="btn btn-outline-primary btn-sm">
                                        <svg class="icon me-1">
                                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-plus') }}"></use>
                                        </svg>
                                        Add Feature
                                    </button>
                                    <div class="form-text">Add features that highlight what this package includes</div>
                                    @error('features')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Package Metadata -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="duration" class="form-label">Duration <small class="text-muted">(Optional)</small></label>
                                            <input type="text" class="form-control @error('duration') is-invalid @enderror"
                                                   id="duration" name="duration" value="{{ old('duration') }}" placeholder="e.g., 30 days, 1 year, lifetime">
                                            <div class="form-text">For digital/subscription packages only. Leave empty for physical products.</div>
                                            @error('duration')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="category" class="form-label">Category</label>
                                            <select class="form-select @error('category') is-invalid @enderror" id="category" name="category">
                                                <option value="">Select a category</option>
                                                <option value="physical" {{ old('category') == 'physical' ? 'selected' : '' }}>Physical Product</option>
                                                <option value="digital" {{ old('category') == 'digital' ? 'selected' : '' }}>Digital Product</option>
                                                <option value="subscription" {{ old('category') == 'subscription' ? 'selected' : '' }}>Subscription Service</option>
                                                <option value="bundle" {{ old('category') == 'bundle' ? 'selected' : '' }}>Product Bundle</option>
                                                <option value="limited" {{ old('category') == 'limited' ? 'selected' : '' }}>Limited Edition</option>
                                                <option value="premium" {{ old('category') == 'premium' ? 'selected' : '' }}>Premium</option>
                                                <option value="basic" {{ old('category') == 'basic' ? 'selected' : '' }}>Basic</option>
                                                <option value="custom" {{ old('category') == 'custom' ? 'selected' : '' }}>Custom</option>
                                            </select>
                                            @error('category')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="image" class="form-label">Package Image</label>
                                    <input type="file" class="form-control @error('image') is-invalid @enderror"
                                           id="image" name="image" accept="image/*">
                                    <div class="form-text">Accepted formats: JPEG, PNG, JPG, GIF. Max size: 2MB</div>
                                    @error('image')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="sort_order" class="form-label">Sort Order</label>
                                    <input type="number" min="0" class="form-control @error('sort_order') is-invalid @enderror"
                                           id="sort_order" name="sort_order" value="{{ old('sort_order', 0) }}">
                                    @error('sort_order')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                               {{ old('is_active', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            Active Package
                                        </label>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_mlm_package" name="is_mlm_package" value="1"
                                               {{ old('is_mlm_package', false) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_mlm_package">
                                            MLM Package (Commission-based)
                                        </label>
                                    </div>
                                    <div class="form-text">Enable multi-level marketing commission structure for this package</div>
                                </div>

                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">Package Preview</h6>
                                    </div>
                                    <div class="card-body">
                                        <div id="image-preview" class="mb-3" style="display: none;">
                                            <img id="preview-img" src="" alt="Preview" class="img-fluid rounded">
                                        </div>
                                        <div class="text-muted">
                                            <small>Upload an image to see preview</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('admin.packages.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <svg class="icon me-2">
                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-save') }}"></use>
                                </svg>
                                Create Package
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-generate slug from name
    const nameInput = document.getElementById('name');
    const slugInput = document.getElementById('slug');

    nameInput.addEventListener('input', function() {
        if (!slugInput.value || slugInput.dataset.manuallySet !== 'true') {
            slugInput.value = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
        }
    });

    slugInput.addEventListener('input', function() {
        this.dataset.manuallySet = 'true';
    });

    // Image preview
    const imageInput = document.getElementById('image');
    const imagePreview = document.getElementById('image-preview');
    const previewImg = document.getElementById('preview-img');

    imageInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                imagePreview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            imagePreview.style.display = 'none';
        }
    });

    // Features management
    const featuresContainer = document.getElementById('features-container');
    const addFeatureBtn = document.getElementById('add-feature');
    let featureIndex = 1;

    // Load existing features from old input (for form validation errors)
    @if(old('features'))
        @foreach(old('features') as $index => $feature)
            @if($index > 0)
                addFeatureInput('{{ $feature }}');
            @endif
        @endforeach
    @endif

    function addFeatureInput(value = '') {
        const featureDiv = document.createElement('div');
        featureDiv.className = 'input-group mb-2 feature-input';
        featureDiv.innerHTML = `
            <input type="text" class="form-control" name="features[]" placeholder="Enter a feature" value="${value}">
            <button type="button" class="btn btn-outline-danger remove-feature">
                <svg class="icon">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-x') }}"></use>
                </svg>
            </button>
        `;
        featuresContainer.appendChild(featureDiv);
        updateRemoveButtons();
        featureIndex++;
    }

    function updateRemoveButtons() {
        const features = featuresContainer.querySelectorAll('.feature-input');
        features.forEach((feature, index) => {
            const removeBtn = feature.querySelector('.remove-feature');
            if (features.length > 1) {
                removeBtn.style.display = 'block';
            } else {
                removeBtn.style.display = 'none';
            }
        });
    }

    addFeatureBtn.addEventListener('click', function() {
        addFeatureInput();
    });

    featuresContainer.addEventListener('click', function(e) {
        if (e.target.closest('.remove-feature')) {
            e.target.closest('.feature-input').remove();
            updateRemoveButtons();
        }
    });

    // Initial setup
    updateRemoveButtons();
});
</script>
@endsection