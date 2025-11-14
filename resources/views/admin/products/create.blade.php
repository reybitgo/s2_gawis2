@extends('layouts.admin')

@section('title', 'Create Product')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Create New Product</h5>
                        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">
                            <svg class="icon me-2">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-arrow-left') }}"></use>
                            </svg>
                            Back to Products
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="row">
                            <div class="col-md-8">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="mb-3">
                                            <label for="name" class="form-label">Product Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                                   id="name" name="name" value="{{ old('name') }}" required>
                                            @error('name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="sku" class="form-label">SKU</label>
                                            <input type="text" class="form-control @error('sku') is-invalid @enderror"
                                                   id="sku" name="sku" value="{{ old('sku') }}">
                                            <div class="form-text">Auto-generated if empty</div>
                                            @error('sku')
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
                                            <div class="form-text">Leave empty for unlimited</div>
                                            @error('quantity_available')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="mb-3">
                                            <label for="category" class="form-label">Category <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('category') is-invalid @enderror"
                                                   id="category" name="category" value="{{ old('category') }}"
                                                   list="categories-list" required>
                                            <datalist id="categories-list">
                                                @foreach($categories as $cat)
                                                    <option value="{{ $cat }}">
                                                @endforeach
                                            </datalist>
                                            <div class="form-text">Type to select existing or create new category</div>
                                            @error('category')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="weight_grams" class="form-label">Weight (grams)</label>
                                            <input type="number" min="0" class="form-control @error('weight_grams') is-invalid @enderror"
                                                   id="weight_grams" name="weight_grams" value="{{ old('weight_grams') }}">
                                            <div class="form-text">For shipping calculation</div>
                                            @error('weight_grams')
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

                                <div class="alert alert-info">
                                    <svg class="icon me-2">
                                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-info') }}"></use>
                                    </svg>
                                    <strong>Note:</strong> After creating this product, you'll be able to configure its Unilevel bonus structure (5 levels) from the product details page.
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="image" class="form-label">Product Image</label>
                                    <input type="file" class="form-control @error('image') is-invalid @enderror"
                                           id="image" name="image" accept="image/*">
                                    <div class="form-text">JPEG, PNG, JPG, GIF. Max: 2MB</div>
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
                                            Active Product
                                        </label>
                                    </div>
                                </div>

                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">Image Preview</h6>
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
                            <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <svg class="icon me-2">
                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-save') }}"></use>
                                </svg>
                                Create Product
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
});
</script>
@endsection
