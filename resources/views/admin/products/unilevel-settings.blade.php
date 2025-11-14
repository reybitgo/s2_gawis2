@extends('layouts.admin')

@section('title', 'Configure Unilevel Bonus - ' . $product->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-1">Unilevel Bonus Configuration</h5>
                            <p class="text-muted mb-0">{{ $product->name }} ({{ $product->sku }})</p>
                        </div>
                        <div>
                            <a href="{{ route('admin.products.show', $product) }}" class="btn btn-outline-info me-2">
                                <svg class="icon me-1">
                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-magnifying-glass') }}"></use>
                                </svg>
                                View Product
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
                    <!-- Product Info Summary -->
                    <div class="alert alert-info mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <strong>Product Price:</strong> {{ currency($product->price) }}
                            </div>
                            <div class="col-md-3">
                                <strong>Category:</strong> {{ $product->category }}
                            </div>
                            <div class="col-md-3">
                                <strong>Points Awarded:</strong> {{ $product->points_awarded }}
                            </div>
                            <div class="col-md-3">
                                <strong>Current Total Bonus:</strong> <span id="current-total" class="text-primary fw-bold">{{ currency($product->total_unilevel_bonus) }}</span>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('admin.products.unilevel-settings.update', $product) }}" id="unilevel-form">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-8">
                                <h6 class="mb-3">5-Level Unilevel Structure</h6>
                                <p class="text-muted mb-4">Configure the fixed bonus amount for each level. When a customer purchases this product, bonuses will be distributed to their upline network according to this structure.</p>

                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th width="15%">Level</th>
                                                <th width="40%">Bonus Amount ({{ currency_symbol() }})</th>
                                                <th width="25%">Percentage of Price</th>
                                                <th width="20%" class="text-center">Active</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($settings as $setting)
                                                <tr>
                                                    <td class="align-middle">
                                                        <div class="d-flex align-items-center">
                                                            <span class="badge bg-primary fs-6 me-2">{{ $setting->level }}</span>
                                                            <span class="text-muted small">
                                                                {{ $setting->level == 1 ? 'Direct Sponsor' : 'Level ' . $setting->level }}
                                                            </span>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="input-group">
                                                            <span class="input-group-text">{{ currency_symbol() }}</span>
                                                            <input type="number"
                                                                   class="form-control bonus-input @error('bonus_amounts.' . $setting->level) is-invalid @enderror"
                                                                   name="bonus_amounts[{{ $setting->level }}]"
                                                                   value="{{ old('bonus_amounts.' . $setting->level, $setting->bonus_amount) }}"
                                                                   step="0.01"
                                                                   min="0"
                                                                   data-level="{{ $setting->level }}"
                                                                   required>
                                                        </div>
                                                        @error('bonus_amounts.' . $setting->level)
                                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td class="align-middle">
                                                        <span class="percentage-display" data-level="{{ $setting->level }}">
                                                            {{ $product->price > 0 ? number_format(($setting->bonus_amount / $product->price) * 100, 2) : '0.00' }}%
                                                        </span>
                                                    </td>
                                                    <td class="text-center align-middle">
                                                        <div class="form-check form-switch d-inline-block">
                                                            <input class="form-check-input"
                                                                   type="checkbox"
                                                                   name="is_active[{{ $setting->level }}]"
                                                                   value="1"
                                                                   {{ old('is_active.' . $setting->level, $setting->is_active) ? 'checked' : '' }}>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="table-light">
                                            <tr>
                                                <td colspan="2"><strong>Total Unilevel Bonus</strong></td>
                                                <td colspan="2">
                                                    <strong id="total-bonus">{{ currency($product->total_unilevel_bonus) }}</strong>
                                                    <span class="text-muted ms-2">(<span id="total-percentage">{{ $product->price > 0 ? number_format(($product->total_unilevel_bonus / $product->price) * 100, 2) : '0.00' }}</span>% of price)</span>
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>

                                <div class="d-flex justify-content-end gap-2 mt-4">
                                    <a href="{{ route('admin.products.show', $product) }}" class="btn btn-secondary">Cancel</a>
                                    <button type="submit" class="btn btn-primary">
                                        <svg class="icon me-2">
                                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-save') }}"></use>
                                        </svg>
                                        Save Unilevel Structure
                                    </button>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <!-- Preview Card -->
                                <div class="card mb-3">
                                    <div class="card-header bg-primary text-white">
                                        <h6 class="card-title mb-0">
                                            <svg class="icon me-2">
                                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-calculator') }}"></use>
                                            </svg>
                                            Bonus Calculation Preview
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="text-center mb-3">
                                            <div class="text-muted small">When someone buys 1 unit</div>
                                            <div class="fs-4 fw-bold">{{ currency($product->price) }}</div>
                                        </div>

                                        <div class="border-top pt-3">
                                            <div class="d-flex justify-content-between mb-2">
                                                <span>Total Bonus Distributed:</span>
                                                <strong class="text-success" id="preview-total">{{ currency($product->total_unilevel_bonus) }}</strong>
                                            </div>
                                            <div class="d-flex justify-content-between mb-2">
                                                <span>Your Net Income:</span>
                                                <strong id="preview-net">{{ currency($product->price - $product->total_unilevel_bonus) }}</strong>
                                            </div>
                                            <div class="d-flex justify-content-between text-muted small">
                                                <span>Bonus Percentage:</span>
                                                <span id="preview-percentage">{{ $product->price > 0 ? number_format(($product->total_unilevel_bonus / $product->price) * 100, 2) : '0.00' }}%</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Help Card -->
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">
                                            <svg class="icon me-2">
                                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-info') }}"></use>
                                            </svg>
                                            How It Works
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <p class="small mb-2"><strong>Level 1 (Direct Sponsor):</strong> The person who directly referred the buyer.</p>
                                        <p class="small mb-2"><strong>Levels 2-5:</strong> The upline network above the direct sponsor.</p>
                                        <p class="small mb-3"><strong>Fixed Amounts:</strong> Unlike percentage-based commissions, unilevel uses fixed bonus amounts per level.</p>
                                        <p class="small text-muted mb-0">
                                            <svg class="icon me-1">
                                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-lightbulb') }}"></use>
                                            </svg>
                                            <strong>Tip:</strong> Level 1 typically receives a higher bonus since they directly recruited the customer.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bottom spacing -->
<div class="pb-5"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const productPrice = {{ $product->price }};
    const bonusInputs = document.querySelectorAll('.bonus-input');
    const totalBonusEl = document.getElementById('total-bonus');
    const totalPercentageEl = document.getElementById('total-percentage');
    const previewTotalEl = document.getElementById('preview-total');
    const previewNetEl = document.getElementById('preview-net');
    const previewPercentageEl = document.getElementById('preview-percentage');
    const currentTotalEl = document.getElementById('current-total');

    function formatCurrency(amount) {
        return '{{ currency_symbol() }}' + parseFloat(amount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }

    function calculateTotals() {
        let total = 0;

        bonusInputs.forEach(input => {
            const value = parseFloat(input.value) || 0;
            const level = input.dataset.level;
            total += value;

            // Update percentage display for this level
            const percentage = productPrice > 0 ? (value / productPrice * 100) : 0;
            const percentageDisplay = document.querySelector(`.percentage-display[data-level="${level}"]`);
            if (percentageDisplay) {
                percentageDisplay.textContent = percentage.toFixed(2) + '%';
            }
        });

        const totalPercentage = productPrice > 0 ? (total / productPrice * 100) : 0;
        const netIncome = productPrice - total;

        // Update all display elements
        totalBonusEl.textContent = formatCurrency(total);
        totalPercentageEl.textContent = totalPercentage.toFixed(2);
        previewTotalEl.textContent = formatCurrency(total);
        previewNetEl.textContent = formatCurrency(netIncome);
        previewPercentageEl.textContent = totalPercentage.toFixed(2) + '%';
        currentTotalEl.textContent = formatCurrency(total);
    }

    // Add event listeners to all bonus inputs
    bonusInputs.forEach(input => {
        input.addEventListener('input', calculateTotals);
    });

    // Initial calculation
    calculateTotals();
});
</script>
@endsection
