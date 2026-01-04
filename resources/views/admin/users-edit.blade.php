@extends('layouts.admin')

@section('title', 'Edit User')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                <div>
                    <h1 class="h2 mb-2">Edit User</h1>
                    <p class="text-muted">Update user information and role</p>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('admin.users') }}" class="btn btn-outline-secondary">
                        <svg class="icon me-2">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-arrow-left') }}"></use>
                        </svg>
                        Back to Users
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-coreui-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-coreui-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">User Information</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.users.update', $user) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control @error('username') is-invalid @enderror"
                                   id="username"
                                   name="username"
                                   value="{{ old('username', $user->username) }}"
                                   required>
                            @error('username')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   id="email"
                                   name="email"
                                   value="{{ old('email', $user->email) }}"
                                   required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="fullname" class="form-label">Full Name</label>
                            <input type="text"
                                   class="form-control @error('fullname') is-invalid @enderror"
                                   id="fullname"
                                   name="fullname"
                                   value="{{ old('fullname', $user->fullname) }}">
                            @error('fullname')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                            <select class="form-select @error('role') is-invalid @enderror"
                                    id="role"
                                    name="role"
                                    required>
                                @foreach(\Spatie\Permission\Models\Role::all() as $role)
                                    <option value="{{ $role->name }}"
                                            {{ $user->hasRole($role->name) ? 'selected' : '' }}>
                                        {{ ucfirst($role->name) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('role')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                Changing the role will affect the user's permissions and access.
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <svg class="icon me-2">
                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-save') }}"></use>
                                </svg>
                                Update User
                            </button>
                            <a href="{{ route('admin.users') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- User Details Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">User Details</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="text-muted small">User ID</div>
                        <div class="fw-semibold">#{{ $user->id }}</div>
                    </div>

                    <div class="mb-3">
                        <div class="text-muted small">Current Role</div>
                        <div>
                            @foreach($user->roles as $role)
                                <span class="badge {{ $role->name === 'admin' ? 'bg-danger' : 'bg-primary' }}">
                                    {{ ucfirst($role->name) }}
                                </span>
                            @endforeach
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="text-muted small">Email Status</div>
                        <div>
                            @if($user->email_verified_at)
                                <span class="badge bg-success">Verified</span>
                                <div class="text-muted small mt-1">{{ $user->email_verified_at->diffForHumans() }}</div>
                            @else
                                <span class="badge bg-warning">Not Verified</span>
                            @endif
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="text-muted small">Member Since</div>
                        <div class="fw-semibold">{{ $user->created_at->format('M d, Y') }}</div>
                        <div class="text-muted small">{{ $user->created_at->diffForHumans() }}</div>
                    </div>

                    @if($user->wallet)
                        <div class="mb-3">
                            <div class="text-muted small">Wallet Status</div>
                            <div>
                                @if($user->wallet->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Suspended</span>
                                @endif
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="text-muted small">Wallet Balance</div>
                            <div class="fw-semibold">{{ currency($user->wallet->balance) }}</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Action Cards Row -->
    @unless($user->hasRole('admin'))
        <div class="row">
            <!-- Rank Management -->
            <div class="col-lg-6 mb-4">
                <div class="card border-primary h-100">
                    <div class="card-header bg-primary bg-opacity-10">
                        <h5 class="mb-0 text-primary">
                            <svg class="icon me-2">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-star') }}"></use>
                            </svg>
                            Rank Management
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="text-muted small">Current Rank</div>
                            <div>
                                @if($user->current_rank)
                                    <span class="badge bg-success fs-6">{{ $user->current_rank }}</span>
                                    @if($user->rankPackage)
                                        <div class="text-muted small mt-1">{{ $user->rankPackage->name }} (₱{{ number_format($user->rankPackage->price, 2) }})</div>
                                    @endif
                                @else
                                    <span class="badge bg-secondary">No Rank</span>
                                    <div class="text-muted small mt-1">User has not purchased a rank package yet</div>
                                @endif
                            </div>
                        </div>

                        @if($user->rank_updated_at)
                            <div class="mb-3">
                                <div class="text-muted small">Last Rank Update</div>
                                <div class="text-muted small">{{ $user->rank_updated_at->diffForHumans() }}</div>
                            </div>
                        @endif

                        <button type="button" class="btn btn-primary w-100" data-coreui-toggle="modal" data-coreui-target="#manualAdvanceModal">
                            <svg class="icon me-2">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-arrow-circle-top') }}"></use>
                            </svg>
                            Manual Rank Advance
                        </button>
                        <p class="text-muted small mt-2 mb-0">Manually advance this user to a higher rank. System will create the order automatically.</p>
                    </div>
                </div>
            </div>

            <!-- Account Actions -->
            <div class="col-lg-6 mb-4">
                <div class="card border-warning h-100">
                    <div class="card-header bg-warning bg-opacity-10">
                        <h5 class="mb-0 text-warning">
                            <svg class="icon me-2">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-warning') }}"></use>
                            </svg>
                            Account Actions
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small">Suspend or activate this user's account and wallet.</p>

                        @if($user->wallet && $user->wallet->is_active)
                            <button onclick="suspendUser({{ $user->id }})" class="btn btn-warning w-100">
                                <svg class="icon me-2">
                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-lock-locked') }}"></use>
                                </svg>
                                Suspend User Account
                            </button>
                        @else
                            <button onclick="activateUser({{ $user->id }})" class="btn btn-success w-100">
                                <svg class="icon me-2">
                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-lock-unlocked') }}"></use>
                                </svg>
                                Activate User Account
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endunless
</div>

<!-- Suspend Confirmation Modal -->
<div class="modal fade" id="suspendModal" tabindex="-1" aria-labelledby="suspendModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning bg-opacity-10">
                <h5 class="modal-title text-warning" id="suspendModalLabel">
                    <svg class="icon me-2">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-warning') }}"></use>
                    </svg>
                    Suspend User Account
                </h5>
                <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">Are you sure you want to suspend this user? They will not be able to access their account.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="confirmSuspendBtn">Suspend User</button>
            </div>
        </div>
    </div>
</div>

<!-- Activate Confirmation Modal -->
<div class="modal fade" id="activateModal" tabindex="-1" aria-labelledby="activateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success bg-opacity-10">
                <h5 class="modal-title text-success" id="activateModalLabel">
                    <svg class="icon me-2">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-check-circle') }}"></use>
                    </svg>
                    Activate User Account
                </h5>
                <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">Are you sure you want to activate this user? Their account will be reactivated.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="confirmActivateBtn">Activate User</button>
            </div>
        </div>
    </div>
</div>

<!-- Manual Rank Advance Modal -->
<div class="modal fade" id="manualAdvanceModal" tabindex="-1" aria-labelledby="manualAdvanceModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary bg-opacity-10">
                <h5 class="modal-title text-primary" id="manualAdvanceModalLabel">
                    <svg class="icon me-2">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-arrow-circle-top') }}"></use>
                    </svg>
                    Manual Rank Advance
                </h5>
                <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="manualAdvanceForm" action="{{ route('admin.ranks.manual-advance', $user) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <svg class="icon me-2">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-info') }}"></use>
                        </svg>
                        <small>This will create a system-funded order for the selected package and update the user's rank automatically.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Current Rank</label>
                        <div>
                            @if($user->current_rank)
                                <span class="badge bg-success">{{ $user->current_rank }}</span>
                            @else
                                <span class="badge bg-secondary">No Rank</span>
                            @endif
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="to_package_id" class="form-label">Advance to Package <span class="text-danger">*</span></label>
                        <select name="to_package_id" id="to_package_id" class="form-select" required>
                            <option value="">Select a package...</option>
                            @php
                                $rankablePackages = \App\Models\Package::rankable()->orderedByRank()->get();
                            @endphp
                            @foreach($rankablePackages as $package)
                                @if(!$user->current_rank || $package->rank_order > ($user->rankPackage->rank_order ?? 0))
                                    <option value="{{ $package->id }}">
                                        {{ $package->name }} - {{ $package->rank_name }} (₱{{ number_format($package->price, 2) }})
                                    </option>
                                @endif
                            @endforeach
                        </select>
                        <small class="text-muted">Only shows packages with higher rank than current</small>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes (Optional)</label>
                        <textarea name="notes" id="notes" class="form-control" rows="3" placeholder="Reason for manual advancement..."></textarea>
                        <small class="text-muted">This will be recorded in the advancement history</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <svg class="icon me-2">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-check') }}"></use>
                        </svg>
                        Advance Rank
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Success/Error Toast -->
<div class="toast-container position-fixed top-0 end-0 p-3">
    <div id="actionToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
            <svg class="icon me-2" id="toastIcon">
                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-check-circle') }}"></use>
            </svg>
            <strong class="me-auto" id="toastTitle">Success</strong>
            <button type="button" class="btn-close" data-coreui-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body" id="toastMessage"></div>
    </div>
</div>

@push('scripts')
<script>
let currentUserId = {{ $user->id }};

function suspendUser(userId) {
    currentUserId = userId;
    const modal = new coreui.Modal(document.getElementById('suspendModal'));
    modal.show();
}

function activateUser(userId) {
    currentUserId = userId;
    const modal = new coreui.Modal(document.getElementById('activateModal'));
    modal.show();
}

document.getElementById('confirmSuspendBtn').addEventListener('click', function() {
    performAction('suspend');
});

document.getElementById('confirmActivateBtn').addEventListener('click', function() {
    performAction('activate');
});

function performAction(action) {
    const modal = coreui.Modal.getInstance(document.getElementById(action === 'suspend' ? 'suspendModal' : 'activateModal'));
    modal.hide();

    fetch(`/admin/users/${currentUserId}/${action}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Success', data.message, 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast('Error', data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error', `An error occurred while ${action === 'suspend' ? 'suspending' : 'activating'} the user`, 'error');
    });
}

function showToast(title, message, type) {
    const toast = document.getElementById('actionToast');
    const toastTitle = document.getElementById('toastTitle');
    const toastMessage = document.getElementById('toastMessage');
    const toastIcon = document.getElementById('toastIcon').querySelector('use');

    toastTitle.textContent = title;
    toastMessage.textContent = message;

    const toastHeader = toast.querySelector('.toast-header');
    toastHeader.className = 'toast-header';

    if (type === 'success') {
        toastHeader.classList.add('bg-success', 'text-white');
        toastIcon.setAttribute('xlink:href', "{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-check-circle') }}");
    } else {
        toastHeader.classList.add('bg-danger', 'text-white');
        toastIcon.setAttribute('xlink:href', "{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-x-circle') }}");
    }

    const bsToast = new coreui.Toast(toast);
    bsToast.show();
}
</script>
@endpush
@endsection
