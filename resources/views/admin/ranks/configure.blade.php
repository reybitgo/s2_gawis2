@extends('layouts.admin')

@section('title', 'Configure Ranks')
@section('page-title', 'Configure Rank Requirements')

@push('styles')
<style>
    /* Ensure proper input widths in mobile mode for rank configuration table */
    .rank-config-table {
        min-width: 1000px; /* Ensures table scrolls horizontally on mobile */
        table-layout: auto; /* Allow columns to size based on content */
    }
    
    /* Column-specific sizing through CSS instead of HTML width attributes */
    .rank-config-table th:nth-child(1),
    .rank-config-table td:nth-child(1) {
        min-width: 150px; /* Package column */
    }
    
    .rank-config-table th:nth-child(2),
    .rank-config-table td:nth-child(2) {
        min-width: 200px; /* Rank Name column */
    }
    
    .rank-config-table th:nth-child(3),
    .rank-config-table td:nth-child(3) {
        min-width: 120px; /* Rank Order column */
    }
    
    .rank-config-table th:nth-child(4),
    .rank-config-table td:nth-child(4) {
        min-width: 150px; /* Required Sponsors column */
    }
    
    .rank-config-table th:nth-child(5),
    .rank-config-table td:nth-child(5) {
        min-width: 250px; /* Next Rank Package column */
    }
    
    .rank-config-table th:nth-child(6),
    .rank-config-table td:nth-child(6) {
        min-width: 120px; /* Price column */
    }

    .rank-config-table th:nth-child(7),
    .rank-config-table td:nth-child(7) {
        min-width: 130px; /* PV Required column */
    }

    .rank-config-table th:nth-child(8),
    .rank-config-table td:nth-child(8) {
        min-width: 130px; /* GPV Required column */
    }

    .rank-config-table th:nth-child(9),
    .rank-config-table td:nth-child(9) {
        min-width: 100px; /* PV Enabled column */
    }
    
    .rank-config-table td input.form-control,
    .rank-config-table td select.form-select {
        width: 100%;
    }
    
    /* Ensure table cells don't shrink below content size */
    .rank-config-table td,
    .rank-config-table th {
        white-space: nowrap;
    }
    
    .rank-config-table td small {
        white-space: normal; /* Allow small text to wrap */
        display: block;
        margin-top: 4px;
    }
</style>
@endpush

@section('content')
<!-- Page Header -->
<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
            <div>
                <h4 class="card-title mb-0">
                    <svg class="icon me-2">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-settings') }}"></use>
                    </svg>
                    Configure Rank Requirements
                </h4>
                <p class="text-body-secondary mb-0">Set rank names, order, and advancement requirements</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('admin.ranks.index') }}" class="btn btn-secondary">
                    <svg class="icon me-1">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-arrow-left') }}"></use>
                    </svg>
                    Back to Dashboard
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Configuration Form -->
<form method="POST" action="{{ route('admin.ranks.update-configuration') }}">
    @csrf
    
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Rank Package Configuration</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered rank-config-table">
                    <thead class="table-light">
                        <tr>
                            <th>Package</th>
                            <th>Rank Name <span class="text-danger">*</span></th>
                             <th>Rank Order <span class="text-danger">*</span></th>
                             <th>
                                 Required Sponsors (PV)
                                 <svg class="icon ms-1 text-muted" data-coreui-toggle="tooltip" title="Sponsors for PV-based advancement" style="vertical-align: middle;">
                                     <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-info') }}"></use>
                                 </svg>
                             </th>
                            <th>
                                Next Rank Package
                                <svg class="icon ms-1 text-muted" data-coreui-toggle="tooltip" title="Auto-purchase on advance" style="vertical-align: middle;">
                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-info') }}"></use>
                                </svg>
                            </th>
                             <th>
                                 Reward
                                 <svg class="icon ms-1 text-muted" data-coreui-toggle="tooltip" title="Reward given to user on reaching this rank" style="vertical-align: middle;">
                                     <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-info') }}"></use>
                                 </svg>
                             </th>
                             <th>
                                 PV Required
                                 <svg class="icon ms-1 text-muted" data-coreui-toggle="tooltip" title="Personal Points Volume needed" style="vertical-align: middle;">
                                     <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-info') }}"></use>
                                 </svg>
                             </th>
                             <th>
                                 GPV Required
                                 <svg class="icon ms-1 text-muted" data-coreui-toggle="tooltip" title="Group Points Volume needed" style="vertical-align: middle;">
                                     <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-info') }}"></use>
                                 </svg>
                             </th>
                             <th>
                                 PV Enabled
                                 <svg class="icon ms-1 text-muted" data-coreui-toggle="tooltip" title="Enable PV-based advancement" style="vertical-align: middle;">
                                     <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-info') }}"></use>
                                 </svg>
                             </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($packages as $package)
                            <tr>
                                <td class="align-middle">
                                    <strong>{{ $package->name }}</strong>
                                    <br>
                                    <small class="text-muted">ID: {{ $package->id }}</small>
                                </td>
                                <td>
                                    <input type="text" 
                                           name="packages[{{ $package->id }}][rank_name]" 
                                           value="{{ old('packages.'.$package->id.'.rank_name', $package->rank_name) }}" 
                                           class="form-control @error('packages.'.$package->id.'.rank_name') is-invalid @enderror" 
                                           placeholder="e.g., Starter, Bronze" 
                                           required>
                                    @error('packages.'.$package->id.'.rank_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </td>
                                <td>
                                    <input type="number" 
                                           name="packages[{{ $package->id }}][rank_order]" 
                                           value="{{ old('packages.'.$package->id.'.rank_order', $package->rank_order) }}" 
                                           class="form-control @error('packages.'.$package->id.'.rank_order') is-invalid @enderror" 
                                           min="1" 
                                           placeholder="1, 2, 3..." 
                                           required>
                                    @error('packages.'.$package->id.'.rank_order')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    
                                </td>
                                <td>
                                     <input type="number"
                                            name="packages[{{ $package->id }}][required_direct_sponsors]"
                                            value="{{ old('packages.'.$package->id.'.required_direct_sponsors', $package->required_direct_sponsors) }}"
                                            class="form-control @error('packages.'.$package->id.'.required_direct_sponsors') is-invalid @enderror"
                                            min="0"
                                            placeholder="e.g., 5"
                                            required>
                                     <small class="text-muted">Path A: Recruitment</small>
                                     @error('packages.'.$package->id.'.required_direct_sponsors')
                                         <div class="invalid-feedback">{{ $message }}</div>
                                     @enderror
                                </td>
                                <td>
                                     <input type="number"
                                            name="packages[{{ $package->id }}][required_sponsors_ppv_gpv]"
                                            value="{{ old('packages.'.$package->id.'.required_sponsors_ppv_gpv', $package->required_sponsors_ppv_gpv) }}"
                                            class="form-control @error('packages.'.$package->id.'.required_sponsors_ppv_gpv') is-invalid @enderror"
                                            min="0"
                                            placeholder="e.g., 4"
                                            required>
                                     <small class="text-muted">Path B: PV</small>
                                     @error('packages.'.$package->id.'.required_sponsors_ppv_gpv')
                                         <div class="invalid-feedback">{{ $message }}</div>
                                     @enderror
                                </td>
                                <td>
                                    @php
                                        // Get currently selected next rank packages to exclude them
                                        $selectedNextRanks = $packages->pluck('next_rank_package_id')->filter()->toArray();
                                        // Get the lowest rank order (typically 1)
                                        $lowestRankOrder = $packages->min('rank_order');
                                    @endphp
                                    <select name="packages[{{ $package->id }}][next_rank_package_id]" 
                                            class="form-select @error('packages.'.$package->id.'.next_rank_package_id') is-invalid @enderror">
                                        <option value="">None (Top Rank)</option>
                                        @foreach($packages as $nextPackage)
                                            @php
                                                // Exclude if:
                                                // 1. Same as current package
                                                // 2. Is the lowest rank (can never be a next rank)
                                                // 3. Already selected by another package (unless it's this package's current selection)
                                                $isCurrentPackage = $nextPackage->id === $package->id;
                                                $isLowestRank = $nextPackage->rank_order == $lowestRankOrder;
                                                $isAlreadySelected = in_array($nextPackage->id, $selectedNextRanks) && $nextPackage->id != $package->next_rank_package_id;
                                                
                                                $shouldExclude = $isCurrentPackage || $isLowestRank || $isAlreadySelected;
                                            @endphp
                                            
                                            @if(!$shouldExclude)
                                                <option value="{{ $nextPackage->id }}" 
                                                        {{ old('packages.'.$package->id.'.next_rank_package_id', $package->next_rank_package_id) == $nextPackage->id ? 'selected' : '' }}>
                                                    {{ $nextPackage->name }} ({{ $nextPackage->rank_name }})
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>
                                    @error('packages.'.$package->id.'.next_rank_package_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    
                                </td>
                                 <td class="align-middle">
                                     <input type="number"
                                            step="0.01"
                                            min="0"
                                            name="packages[{{ $package->id }}][rank_reward]"
                                            value="{{ old('packages.'.$package->id.'.rank_reward', $package->rank_reward) }}"
                                            class="form-control @error('packages.'.$package->id.'.rank_reward') is-invalid @enderror">
                                     @error('packages.'.$package->id.'.rank_reward')
                                         <div class="invalid-feedback">{{ $message }}</div>
                                     @enderror
                                </td>
                                <td class="align-middle">
                                     <input type="number"
                                            step="0.01"
                                            min="0"
                                            name="packages[{{ $package->id }}][ppv_required]"
                                            value="{{ old('packages.'.$package->id.'.ppv_required', $package->ppv_required) }}"
                                            class="form-control @error('packages.'.$package->id.'.ppv_required') is-invalid @enderror"
                                            placeholder="0.00">
                                     <small class="text-muted">PPV needed</small>
                                     @error('packages.'.$package->id.'.ppv_required')
                                         <div class="invalid-feedback">{{ $message }}</div>
                                     @enderror
                                </td>
                                <td class="align-middle">
                                     <input type="number"
                                            step="0.01"
                                            min="0"
                                            name="packages[{{ $package->id }}][gpv_required]"
                                            value="{{ old('packages.'.$package->id.'.gpv_required', $package->gpv_required) }}"
                                            class="form-control @error('packages.'.$package->id.'.gpv_required') is-invalid @enderror"
                                            placeholder="0.00">
                                     <small class="text-muted">GPV needed</small>
                                     @error('packages.'.$package->id.'.gpv_required')
                                         <div class="invalid-feedback">{{ $message }}</div>
                                     @enderror
                                </td>
                                <td class="align-middle text-center">
                                     <div class="form-check">
                                         <input class="form-check-input" type="checkbox" id="rank_pv_enabled_{{ $package->id }}"
                                                name="packages[{{ $package->id }}][rank_pv_enabled]"
                                                value="1"
                                                {{ old('packages.'.$package->id.'.rank_pv_enabled', $package->rank_pv_enabled) ? 'checked' : '' }}>
                                         <label class="form-check-label" for="rank_pv_enabled_{{ $package->id }}">
                                             {{ $package->rank_pv_enabled ? 'Yes' : 'No' }}
                                         </label>
                                     </div>
                                     @error('packages.'.$package->id.'.rank_pv_enabled')
                                         <div class="invalid-feedback">{{ $message }}</div>
                                     @enderror
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">
                                    <svg class="icon me-2">
                                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-info') }}"></use>
                                    </svg>
                                    No rankable packages found. Please create packages with is_rankable = true.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($packages->count() > 0)
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <svg class="icon me-1">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-save') }}"></use>
                    </svg>
                    Save Configuration
                </button>
                <button type="reset" class="btn btn-secondary">
                    <svg class="icon me-1">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-reload') }}"></use>
                    </svg>
                    Reset
                </button>
            </div>
        @endif
    </div>
</form>

<!-- Explanation Card -->
<div class="card mb-5">
    <div class="card-header bg-info text-white">
        <h5 class="mb-0">
            <svg class="icon me-2">
                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-lightbulb') }}"></use>
            </svg>
            How It Works
        </h5>
    </div>
    <div class="card-body">
        <h6>Configuration Fields Explained:</h6>
        <ul class="mb-3">
            <li><strong>Rank Name:</strong> Display name for this rank tier (e.g., "Starter", "Newbie", "Bronze")</li>
            <li><strong>Rank Order:</strong> Numeric order where 1 = lowest rank, higher numbers = better ranks. Used for rank comparisons in commission calculations.</li>
            <li><strong>Required Sponsors (Path A):</strong> Number of same-rank direct sponsors needed to advance to next rank tier via recruitment path.</li>
            <li><strong>Required Sponsors (Path B):</strong> Minimum same-rank sponsors needed for PV-based rank advancement (separate from Path A).</li>
            <li><strong>PV Required:</strong> Personal Points Volume needed for PV-based advancement.</li>
            <li><strong>GPV Required:</strong> Group Points Volume needed for PV-based advancement (user + all downlines).</li>
            <li><strong>PV Enabled:</strong> Enable or disable PV-based advancement for this rank.</li>
            <li><strong>Next Rank Package:</strong> The package that will be automatically purchased (system-funded) when advancement criteria is met.</li>
            <li><strong>Reward:</strong> Cash reward given to user on reaching this rank.</li>
        </ul>
        
        <h6>Important Notes:</h6>
        <div class="alert alert-warning mb-3">
            <svg class="icon me-2">
                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-warning') }}"></use>
            </svg>
            <strong>Warning:</strong> Make sure rank order is sequential and the next rank package has a higher rank order than the current one.
        </div>
        
        <div class="alert alert-info mb-3">
            <svg class="icon me-2">
                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-info') }}"></use>
            </svg>
            <strong>Note:</strong> The "Next Rank Package" dropdown automatically excludes:
            <ul class="mb-0 mt-2">
                <li>The lowest rank (Rank Order = 1) - it can never be a next rank</li>
                <li>Packages already selected by other ranks - prevents conflicts</li>
                <li>The current package itself - prevents circular references</li>
            </ul>
        </div>
        
        <h6>Example Configuration:</h6>
        <div class="table-responsive">
            <table class="table table-sm table-bordered">
                 <thead class="table-light">
                        <tr>
                            <th>Package</th>
                            <th>Rank Name</th>
                            <th>Rank Order</th>
                            <th>Sponsors A</th>
                            <th>Sponsors B</th>
                            <th>PV Req</th>
                            <th>GPV Req</th>
                            <th>PV</th>
                        </tr>
                    </thead>
                 <tbody>
                        <tr>
                            <td>Starter Package (Reward: ₱{{ number_format(1000, 2) }})</td>
                            <td>Starter</td>
                            <td>1</td>
                            <td>2</td>
                            <td>2</td>
                            <td>0</td>
                            <td>0</td>
                            <td>Yes</td>
                            <td>Newbie Package</td>
                        </tr>
                        <tr>
                            <td>Newbie Package (Reward: ₱{{ number_format(2500, 2) }})</td>
                            <td>Newbie</td>
                            <td>2</td>
                            <td>5</td>
                            <td>4</td>
                            <td>100</td>
                            <td>1000</td>
                            <td>Yes</td>
                            <td>Bronze Package</td>
                        </tr>
                        <tr>
                            <td>Bronze Package (Reward: ₱{{ number_format(5000, 2) }})</td>
                            <td>Bronze</td>
                            <td>3</td>
                            <td>10</td>
                            <td>4</td>
                            <td>300</td>
                            <td>5000</td>
                            <td>Yes</td>
                            <td>Silver Package</td>
                        </tr>
                </tbody>
            </table>
        </div>
	        
        <p class="mb-0"><strong>Result:</strong> Users can advance ranks via two paths:
            <ul class="mb-0">
                <li><strong>Path A (Recruitment):</strong> When a user meets recruitment sponsor requirement, they automatically receive next rank package!</li>
                <li><strong>Path B (PV-Based):</strong> When a user meets PPV/GPV sponsor requirement + PPV threshold + GPV threshold, they advance!</li>
            </ul>
        </p>
	    
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var selector = '[data-coreui-toggle="tooltip"], [data-bs-toggle="tooltip"], [data-toggle="tooltip"]';
            var tooltipElements = [].slice.call(document.querySelectorAll(selector));

            tooltipElements.forEach(function (el) {
                try {
                    if (window.coreui) {
                        new coreui.Tooltip(el);
                    } else if (window.CoreUI) {
                        new CoreUI.Tooltip(el);
                    } else if (window.bootstrap) {
                        new bootstrap.Tooltip(el);
                    } else if (typeof $ !== 'undefined' && $.fn && $.fn.tooltip) {
                        $(el).tooltip();
                    }
                } catch (e) {
                    // ignore init errors
                    console.warn('Tooltip init error', e);
                }
            });
        });
    </script>
    @endpush
