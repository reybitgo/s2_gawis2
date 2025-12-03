@extends('layouts.admin')

@section('title', 'Configure Ranks')
@section('page-title', 'Configure Rank Requirements')

@section('content')
<!-- Page Header -->
<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="card-title mb-0">
                    <svg class="icon me-2">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-settings') }}"></use>
                    </svg>
                    Configure Rank Requirements
                </h4>
                <p class="text-body-secondary mb-0">Set rank names, order, and advancement requirements</p>
            </div>
            <div>
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
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th width="15%">Package</th>
                            <th width="15%">Rank Name <span class="text-danger">*</span></th>
                            <th width="10%">Rank Order <span class="text-danger">*</span></th>
                            <th width="15%">Required Sponsors <span class="text-danger">*</span></th>
                            <th width="20%">Next Rank Package</th>
                            <th width="10%">Price</th>
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
                                    <small class="text-muted">1 = lowest</small>
                                </td>
                                <td>
                                    <input type="number" 
                                           name="packages[{{ $package->id }}][required_direct_sponsors]" 
                                           value="{{ old('packages.'.$package->id.'.required_direct_sponsors', $package->required_direct_sponsors) }}" 
                                           class="form-control @error('packages.'.$package->id.'.required_direct_sponsors') is-invalid @enderror" 
                                           min="0" 
                                           placeholder="e.g., 5" 
                                           required>
                                    @error('packages.'.$package->id.'.required_direct_sponsors')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Same-rank sponsors</small>
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
                                    <small class="text-muted">Auto-purchase on advance</small>
                                </td>
                                <td class="align-middle">
                                    <strong class="text-success">₱{{ number_format($package->price, 2) }}</strong>
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
            <li><strong>Required Sponsors:</strong> Number of same-rank direct sponsors needed to advance to the next rank tier</li>
            <li><strong>Next Rank Package:</strong> The package that will be automatically purchased (system-funded) when advancement criteria is met</li>
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
                        <th>Required Sponsors</th>
                        <th>Next Rank</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Starter Package (₱1,000)</td>
                        <td>Starter</td>
                        <td>1</td>
                        <td>5</td>
                        <td>Newbie Package</td>
                    </tr>
                    <tr>
                        <td>Newbie Package (₱2,500)</td>
                        <td>Newbie</td>
                        <td>2</td>
                        <td>8</td>
                        <td>Bronze Package</td>
                    </tr>
                    <tr>
                        <td>Bronze Package (₱5,000)</td>
                        <td>Bronze</td>
                        <td>3</td>
                        <td>10</td>
                        <td>None (Top Rank)</td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <p class="mb-0"><strong>Result:</strong> When a Starter user sponsors 5 other Starter-rank users, they automatically receive the Newbie Package for free!</p>
    </div>
</div>
@endsection
