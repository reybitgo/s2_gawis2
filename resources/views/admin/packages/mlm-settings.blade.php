@extends('layouts.admin')

@section('title', 'MLM Settings - ' . $package->name)

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                <div>
                    <svg class="icon me-2">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-settings') }}"></use>
                    </svg>
                    <strong>MLM Settings: {{ $package->name }}</strong>
                    <small class="text-body-secondary ms-2 d-none d-md-inline">
                        Configure 5-level commission structure
                    </small>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('admin.packages.index') }}" class="btn btn-secondary">
                        <svg class="icon me-2">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-arrow-left') }}"></use>
                        </svg>
                        Back to Packages
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-8">
            <form action="{{ route('admin.packages.mlm.update', $package) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                            <div>
                                <strong>Commission Structure</strong>
                                <small class="text-white-50 ms-2 d-none d-md-inline">
                                    Package Price: ₱{{ number_format($package->price, 2) }}
                                </small>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th width="10%">Level</th>
                                        <th width="35%">Description</th>
                                        <th width="25%">Commission (₱)</th>
                                        <th width="15%">Percentage</th>
                                        <th width="15%" class="text-center">Active</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @for ($level = 1; $level <= 5; $level++)
                                        @php
                                            $commission = $mlmSettings[$level]->commission_amount ?? ($level == 1 ? 200 : 50);
                                            $percentage = $package->price > 0 ? ($commission / $package->price) * 100 : 0;
                                        @endphp
                                        <tr>
                                            <td class="align-middle">
                                                <strong class="text-primary">L{{ $level }}</strong>
                                                <input type="hidden" name="settings[{{ $level }}][level]" value="{{ $level }}">
                                            </td>
                                            <td class="align-middle">
                                                @if ($level == 1)
                                                    <span class="badge bg-success">Direct Referrals</span>
                                                    <small class="d-block text-muted mt-1">Your immediate recruits</small>
                                                @else
                                                    <span class="badge bg-info">Indirect L{{ $level }}</span>
                                                    <small class="d-block text-muted mt-1">{{ $level }}{{ $level == 2 ? 'nd' : ($level == 3 ? 'rd' : 'th') }} tier referrals</small>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="input-group" style="min-width: 160px;">
                                                    <span class="input-group-text">₱</span>
                                                    <input type="number"
                                                           class="form-control commission-input"
                                                           name="settings[{{ $level }}][commission_amount]"
                                                           value="{{ old('settings.'.$level.'.commission_amount', $commission) }}"
                                                           step="0.01"
                                                           min="0"
                                                           max="{{ $package->price }}"
                                                           required>
                                                </div>
                                            </td>
                                            <td class="align-middle">
                                                <span class="percentage-display" data-level="{{ $level }}">
                                                    {{ number_format($percentage, 2) }}%
                                                </span>
                                            </td>
                                            <td class="text-center align-middle">
                                                <div class="form-check form-switch d-flex justify-content-center">
                                                    <input class="form-check-input"
                                                           type="checkbox"
                                                           name="settings[{{ $level }}][is_active]"
                                                           value="1"
                                                           {{ old('settings.'.$level.'.is_active', $mlmSettings[$level]->is_active ?? true) ? 'checked' : '' }}>
                                                </div>
                                            </td>
                                        </tr>
                                    @endfor
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <th colspan="2" class="text-end">Total MLM Commission:</th>
                                        <th id="total-commission-amount">₱{{ number_format($totalCommission, 2) }}</th>
                                        <th id="total-commission-percentage">{{ number_format(($totalCommission / $package->price) * 100, 2) }}%</th>
                                        <th></th>
                                    </tr>
                                    <tr class="table-success">
                                        <th colspan="2" class="text-end">Company Profit (60% target):</th>
                                        <th id="company-profit-amount">₱{{ number_format($companyProfit, 2) }}</th>
                                        <th id="company-profit-percentage">{{ number_format($profitMargin, 2) }}%</th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="icon cil-save"></i> Save MLM Settings
                        </button>
                        <a href="{{ route('admin.packages.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </div>
            </form>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <strong>MLM Summary</strong>
                </div>
                <div class="card-body">
                    <h5>{{ $package->name }}</h5>
                    <hr>
                    <div class="mb-3">
                        <small class="text-muted">Package Price</small>
                        <h4 class="mb-0">₱{{ number_format($package->price, 2) }}</h4>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Total Commission</small>
                        <h4 class="mb-0 text-primary" id="sidebar-total-commission">₱{{ number_format($totalCommission, 2) }}</h4>
                        <small class="text-muted" id="sidebar-commission-percent">{{ number_format(($totalCommission / $package->price) * 100, 2) }}% of price</small>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Company Profit</small>
                        <h4 class="mb-0 text-success" id="sidebar-company-profit">₱{{ number_format($companyProfit, 2) }}</h4>
                        <small class="text-muted" id="sidebar-profit-percent">{{ number_format($profitMargin, 2) }}% margin</small>
                    </div>
                    <hr>
                    <small class="text-muted">MLM Structure</small>
                    <ul class="list-unstyled mt-2">
                        <li><strong>Max Levels:</strong> {{ $package->max_mlm_levels }}</li>
                        <li><strong>Status:</strong> <span class="badge bg-success">Active</span></li>
                    </ul>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <strong>Commission Guidelines</strong>
                </div>
                <div class="card-body">
                    <ul class="small">
                        <li>Level 1 typically earns highest commission (direct referrals)</li>
                        <li>Levels 2-5 usually have equal commissions</li>
                        <li>Changes apply to new purchases only</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bottom spacing for better visual layout -->
<div class="pb-5"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const packagePrice = {{ $package->price }};
    const commissionInputs = document.querySelectorAll('.commission-input');
    const activeCheckboxes = document.querySelectorAll('input[name*="[is_active]"]');

    function updateTotals() {
        let total = 0;

        // Calculate total and update percentages
        commissionInputs.forEach(input => {
            const row = input.closest('tr');
            const checkbox = row.querySelector('input[name*="[is_active]"]');
            const isActive = checkbox.checked;
            const amount = parseFloat(input.value) || 0;

            // Only add to total if level is active
            if (isActive) {
                total += amount;
            }

            // Update percentage display for this level
            const level = row.querySelector('input[name*="[level]"]').value;
            const percentageDisplay = document.querySelector(`[data-level="${level}"]`);
            const percentage = packagePrice > 0 ? (amount / packagePrice) * 100 : 0;
            percentageDisplay.textContent = percentage.toFixed(2) + '%';
        });

        const companyProfit = packagePrice - total;
        const profitMargin = packagePrice > 0 ? (companyProfit / packagePrice) * 100 : 0;
        const commissionPercent = packagePrice > 0 ? (total / packagePrice) * 100 : 0;

        // Update table footer
        document.getElementById('total-commission-amount').textContent = '₱' + total.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
        document.getElementById('total-commission-percentage').textContent = commissionPercent.toFixed(2) + '%';
        document.getElementById('company-profit-amount').textContent = '₱' + companyProfit.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
        document.getElementById('company-profit-percentage').textContent = profitMargin.toFixed(2) + '%';

        // Update sidebar
        document.getElementById('sidebar-total-commission').textContent = '₱' + total.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
        document.getElementById('sidebar-commission-percent').textContent = commissionPercent.toFixed(2) + '% of price';
        document.getElementById('sidebar-company-profit').textContent = '₱' + companyProfit.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
        document.getElementById('sidebar-profit-percent').textContent = profitMargin.toFixed(2) + '% margin';
    }

    // Listen to input changes
    commissionInputs.forEach(input => {
        input.addEventListener('input', updateTotals);
    });

    // Listen to checkbox changes
    activeCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateTotals);
    });

    // Initial calculation
    updateTotals();
});
</script>
@endsection

@push('styles')
<style>
/* Mobile responsiveness */
@media (max-width: 767.98px) {
    .col-md-8 .card {
        margin-bottom: 2rem;
    }
}
</style>
@endpush
