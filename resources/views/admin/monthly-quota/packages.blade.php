@extends('layouts.admin')

@section('title', 'Manage Package Monthly Quotas')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Manage Package Monthly Quotas</h1>
        <a href="{{ route('admin.monthly-quota.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h5>Package Monthly Quota Requirements</h5>
            <p class="text-muted mb-0">Configure monthly PV quota for each starter package. Users must meet this quota to earn Unilevel bonuses.</p>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th width="5%">ID</th>
                            <th width="20%">Package Name</th>
                            <th width="15%">Price</th>
                            <th width="10%">MLM Package</th>
                            <th width="15%">Current Quota</th>
                            <th width="15%">New Quota (PV)</th>
                            <th width="10%">Enforce</th>
                            <th width="10%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($packages as $package)
                        <tr>
                            <td>{{ $package->id }}</td>
                            <td>{{ $package->name }}</td>
                            <td>₱{{ number_format($package->price, 2) }}</td>
                            <td>
                                @if($package->is_mlm_package)
                                    <span class="badge bg-success">Yes</span>
                                @else
                                    <span class="badge bg-secondary">No</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $package->enforce_monthly_quota ? 'bg-warning' : 'bg-secondary' }}">
                                    {{ number_format($package->monthly_quota_points, 2) }} PV
                                    @if($package->enforce_monthly_quota)
                                        (Enforced)
                                    @else
                                        (Disabled)
                                    @endif
                                </span>
                            </td>
                            <td>
                                <form method="POST" action="{{ route('admin.monthly-quota.packages.update-quota', $package) }}" class="d-inline">
                                    @csrf
                                    <input type="number" name="monthly_quota_points" step="0.01" min="0" max="9999.99" 
                                           value="{{ $package->monthly_quota_points }}" class="form-control form-control-sm" 
                                           style="width: 100px; display: inline-block;" required>
                            </td>
                            <td>
                                    <select name="enforce_monthly_quota" class="form-select form-select-sm" required>
                                        <option value="0" {{ !$package->enforce_monthly_quota ? 'selected' : '' }}>No</option>
                                        <option value="1" {{ $package->enforce_monthly_quota ? 'selected' : '' }}>Yes</option>
                                    </select>
                            </td>
                            <td>
                                    <button type="submit" class="btn btn-sm btn-primary">
                                        <i class="fas fa-save"></i> Update
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Explanation -->
    <div class="card mt-4">
        <div class="card-header">
            <h5>How It Works</h5>
        </div>
        <div class="card-body">
            <ul>
                <li><strong>Monthly Quota Points:</strong> The PV amount users must accumulate each month</li>
                <li><strong>Enforce:</strong> When enabled, users MUST meet quota to earn Unilevel bonuses</li>
                <li><strong>When Disabled:</strong> Users only need to be "active" (purchased package) to earn bonuses</li>
                <li><strong>Flexibility:</strong> Different packages can have different requirements</li>
            </ul>
            
            <div class="alert alert-info mt-3">
                <strong>Example:</strong> If "Starter Package" has 100 PV monthly quota (enforced), 
                users who purchased this package must accumulate 100 PV through personal product purchases each month 
                to remain eligible for Unilevel bonuses from their downline.
            </div>
        </div>
    </div>
</div>
@endsection
