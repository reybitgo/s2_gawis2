@extends('layouts.admin')

@section('title', 'Edit Product')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Edit Product: {{ $product->name }}</h5>
                        <div>
                            <a href="{{ route('admin.products.show', $product) }}" class="btn btn-outline-info me-2">
                                <svg class="icon me-1">
                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-magnifying-glass') }}"></use>
                                </svg>
                                View Details
                            </a>
                            <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">
                                <svg class="icon me-2">
                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-arrow-left') }}"></use>
                                </svg>
                                Back
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-8">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="mb-3">
                                            <label for="name" class="form-label">Product Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                                   id="name" name="name" value="{{ old('name', $product->name) }}" required>
                                            @error('name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="sku" class="form-label">SKU</label>
                                            <input type="text" class="form-control @error('sku') is-invalid @enderror"
                                                   id="sku" name="sku" value="{{ old('sku', $product->sku) }}">
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
                                                   id="price" name="price" value="{{ old('price', $product->price) }}" required>
                                            @error('price')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="points_awarded" class="form-label">Points Awarded <span class="text-danger">*</span></label>
                                            <input type="number" min="0" class="form-control @error('points_awarded') is-invalid @enderror"
                                                   id="points_awarded" name="points_awarded" value="{{ old('points_awarded', $product->points_awarded) }}" required>
                                            @error('points_awarded')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="quantity_available" class="form-label">Quantity Available</label>
                                            <input type="number" min="0" class="form-control @error('quantity_available') is-invalid @enderror"
                                                   id="quantity_available" name="quantity_available" value="{{ old('quantity_available', $product->quantity_available) }}">
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
                                                   id="category" name="category" value="{{ old('category', $product->category) }}"
                                                   list="categories-list" required>
                                            <datalist id="categories-list">
                                                @foreach($categories as $cat)
                                                    <option value="{{ $cat }}">
                                                @endforeach
                                            </datalist>
                                            @error('category')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="weight_grams" class="form-label">Weight (grams)</label>
                                            <input type="number" min="0" class="form-control @error('weight_grams') is-invalid @enderror"
                                                   id="weight_grams" name="weight_grams" value="{{ old('weight_grams', $product->weight_grams) }}">
                                            @error('weight_grams')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="short_description" class="form-label">Short Description <span class="text-danger">*</span></label>
                                    <textarea class="form-control @error('short_description') is-invalid @enderror"
                                              id="short_description" name="short_description" rows="2" maxlength="500" required>{{ old('short_description', $product->short_description) }}</textarea>
                                    <div class="form-text">Maximum 500 characters</div>
                                    @error('short_description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="long_description" class="form-label">Long Description <span class="text-danger">*</span></label>
                                    <textarea class="form-control @error('long_description') is-invalid @enderror"
                                              id="long_description" name="long_description" rows="6" required>{{ old('long_description', $product->long_description) }}</textarea>
                                    @error('long_description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="image" class="form-label">Product Image</label>
                                    @if($product->image_path)
                                        <div class="mb-2">
                                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="img-fluid rounded">
                                            <div class="form-text">Current image</div>
                                        </div>
                                    @endif
                                    <input type="file" class="form-control @error('image') is-invalid @enderror"
                                           id="image" name="image" accept="image/*">
                                    <div class="form-text">Upload new image to replace current</div>
                                    @error('image')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="sort_order" class="form-label">Sort Order</label>
                                    <input type="number" min="0" class="form-control @error('sort_order') is-invalid @enderror"
                                           id="sort_order" name="sort_order" value="{{ old('sort_order', $product->sort_order) }}">
                                    @error('sort_order')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                               {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            Active Product
                                        </label>
                                    </div>
                                </div>

                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title">Unilevel Bonus</h6>
                                        <p class="mb-2"><strong>Total: {{ currency($product->total_unilevel_bonus) }}</strong></p>
                                        <a href="{{ route('admin.products.unilevel-settings.edit', $product) }}" class="btn btn-sm btn-primary">
                                            <svg class="icon me-1">
                                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-settings') }}"></use>
                                            </svg>
                                            Configure Bonus Structure
                                        </a>
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
                                Update Product
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
    // Image preview for new upload
    const imageInput = document.getElementById('image');

    imageInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                // Update the current image preview
                const currentImg = document.querySelector('img[alt="{{ $product->name }}"]');
                if (currentImg) {
                    currentImg.src = e.target.result;
                }
            };
            reader.readAsDataURL(file);
        }
    });
});
</script>
@endsection
