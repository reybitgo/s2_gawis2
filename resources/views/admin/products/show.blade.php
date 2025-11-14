@extends('layouts.admin')

@section('title', 'Product Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">{{ $product->name }}</h5>
                        <div>
                            <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-primary me-2">
                                <svg class="icon me-1">
                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-pencil') }}"></use>
                                </svg>
                                Edit Product
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
                    <div class="row">
                        <div class="col-md-4">
                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="img-fluid rounded mb-3">

                            <div class="card mb-3">
                                <div class="card-body">
                                    <h6 class="card-title">Status</h6>
                                    <span class="badge bg-{{ $product->is_active ? 'success' : 'warning' }} fs-6">
                                        {{ $product->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-body">
                                    <h6 class="card-title">Quick Actions</h6>
                                    <div class="d-grid gap-2">
                                        <a href="{{ route('admin.products.unilevel-settings.edit', $product) }}" class="btn btn-outline-primary">
                                            <svg class="icon me-1">
                                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-settings') }}"></use>
                                            </svg>
                                            Configure Unilevel Bonus
                                        </a>
                                        <form method="POST" action="{{ route('admin.products.toggle-status', $product) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-{{ $product->is_active ? 'warning' : 'success' }} w-100">
                                                <svg class="icon me-1">
                                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-' . ($product->is_active ? 'ban' : 'check') . '') }}"></use>
                                                </svg>
                                                {{ $product->is_active ? 'Deactivate' : 'Activate' }}
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-8">
                            <div class="row mb-4">
                                <div class="col-md-3">
                                    <div class="card text-center">
                                        <div class="card-body">
                                            <div class="fs-4 fw-bold">{{ $product->formatted_price }}</div>
                                            <div class="text-muted small">Price</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card text-center">
                                        <div class="card-body">
                                            <div class="fs-4 fw-bold">{{ $product->points_awarded }}</div>
                                            <div class="text-muted small">Points</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card text-center">
                                        <div class="card-body">
                                            <div class="fs-4 fw-bold">{{ $product->quantity_available ?? '∞' }}</div>
                                            <div class="text-muted small">Available</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card text-center">
                                        <div class="card-body">
                                            <div class="fs-4 fw-bold">{{ currency($product->total_unilevel_bonus) }}</div>
                                            <div class="text-muted small">Total Bonus</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card mb-3">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">Product Information</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-2">
                                        <div class="col-md-4"><strong>SKU:</strong></div>
                                        <div class="col-md-8"><code>{{ $product->sku }}</code></div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-4"><strong>Category:</strong></div>
                                        <div class="col-md-8"><span class="badge bg-info">{{ $product->category }}</span></div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-4"><strong>Weight:</strong></div>
                                        <div class="col-md-8">{{ $product->weight_grams ? $product->weight_grams . ' grams' : 'Not specified' }}</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-4"><strong>Sort Order:</strong></div>
                                        <div class="col-md-8">{{ $product->sort_order }}</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-4"><strong>Slug:</strong></div>
                                        <div class="col-md-8"><code>{{ $product->slug }}</code></div>
                                    </div>
                                </div>
                            </div>

                            <div class="card mb-3">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">Description</h6>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted mb-3">{{ $product->short_description }}</p>
                                    <div>{{ $product->long_description }}</div>
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">Unilevel Bonus Structure</h6>
                                </div>
                                <div class="card-body">
                                    @if($product->unilevelSettings->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Level</th>
                                                        <th>Bonus Amount</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($product->unilevelSettings as $setting)
                                                        <tr>
                                                            <td><strong>Level {{ $setting->level }}</strong></td>
                                                            <td>{{ currency($setting->bonus_amount) }}</td>
                                                            <td>
                                                                <span class="badge bg-{{ $setting->is_active ? 'success' : 'secondary' }}">
                                                                    {{ $setting->is_active ? 'Active' : 'Inactive' }}
                                                                </span>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                    <tr class="table-active">
                                                        <td colspan="2"><strong>Total Unilevel Bonus</strong></td>
                                                        <td><strong>{{ currency($product->total_unilevel_bonus) }}</strong></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <a href="{{ route('admin.products.unilevel-settings.edit', $product) }}" class="btn btn-sm btn-primary">
                                            Edit Unilevel Structure
                                        </a>
                                    @else
                                        <p class="text-muted mb-3">No unilevel bonus structure configured yet.</p>
                                        <a href="{{ route('admin.products.unilevel-settings.edit', $product) }}" class="btn btn-primary">
                                            Configure Unilevel Bonus
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">Metadata</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="mb-1"><strong>Created:</strong> {{ $product->created_at->format('M d, Y h:i A') }}</p>
                                            <p class="mb-1"><strong>Last Updated:</strong> {{ $product->updated_at->format('M d, Y h:i A') }}</p>
                                        </div>
                                        <div class="col-md-6">
                                            @if($product->meta_data)
                                                <p class="mb-1"><strong>Additional Data:</strong></p>
                                                <pre class="bg-light p-2 rounded"><code>{{ json_encode($product->meta_data, JSON_PRETTY_PRINT) }}</code></pre>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bottom spacing -->
<div class="pb-5"></div>

@endsection
