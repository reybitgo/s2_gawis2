@extends('layouts.admin')

@section('title', 'User Management')
@section('page-title', 'User Management')

@section('content')
<!-- Page Header -->
<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
            <div>
                <h4 class="card-title mb-0">
                    <svg class="icon me-2">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-people') }}"></use>
                    </svg>
                    User Management
                </h4>
                <p class="text-body-secondary mb-0">Manage system users and their roles</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
                    <svg class="icon me-2">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-arrow-left') }}"></use>
                    </svg>
                    Back to Dashboard
                </a>
            </div>
        </div>
    </div>
</div>

<!-- User Statistics -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card text-white bg-primary-gradient">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold">{{ $users->total() }}</div>
                    <div>Total Users</div>
                </div>
                <svg class="icon icon-3xl">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-people') }}"></use>
                </svg>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card text-white bg-success-gradient">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold">{{ $users->where('email_verified_at', '!=', null)->count() }}</div>
                    <div>Verified Users</div>
                </div>
                <svg class="icon icon-3xl">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-check') }}"></use>
                </svg>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card text-white bg-warning-gradient">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold">{{ $users->where('two_factor_secret', '!=', null)->count() }}</div>
                    <div>2FA Enabled</div>
                </div>
                <svg class="icon icon-3xl">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-lock-locked') }}"></use>
                </svg>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card text-white bg-info-gradient">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold">{{ $users->filter(function($user) { return $user->hasRole('admin'); })->count() }}</div>
                    <div>Administrators</div>
                </div>
                <svg class="icon icon-3xl">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-shield-alt') }}"></use>
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- Users List -->
<div class="card">
    <div class="card-header">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
            <div>
                <svg class="icon me-2">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-list') }}"></use>
                </svg>
                <strong>All Users</strong>
                <small class="text-body-secondary ms-2 d-none d-md-inline">
                    @if($users->count() > 0)
                        Showing {{ $users->firstItem() }} to {{ $users->lastItem() }} of {{ $users->total() }} users
                    @else
                        No users found
                    @endif
                </small>
            </div>
            <x-per-page-selector :perPage="$perPage" />
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th scope="col">User</th>
                        <th scope="col">Income</th>
                        <th scope="col">Rank</th>
                        <th scope="col">Roles</th>
                        <th scope="col">Status</th>
                        <th scope="col">Created</th>
                        <th scope="col" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar me-3 bg-primary">
                                        <span class="text-white">{{ strtoupper(substr($user->username, 0, 2)) }}</span>
                                    </div>
                                    <div>
                                        <div class="fw-semibold">{{ $user->username }}</div>
                                        <div class="text-body-secondary">ID: {{ $user->id }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @php
                                    $withdrawableBalance = $user->wallet?->withdrawable_balance ?? 0;
                                @endphp
                                @if($withdrawableBalance > 0)
                                    <div class="fw-semibold text-success">
                                        ₱{{ number_format($withdrawableBalance, 2) }}
                                    </div>
                                @else
                                    <div class="text-muted">₱0.00</div>
                                @endif
                            </td>
                            <td>
                                @if($user->rankPackage)
                                    <span class="badge bg-info" title="Rank Order: {{ $user->rankPackage->rank_order }}">
                                        {{ $user->current_rank }}
                                    </span>
                                    <div class="text-muted small mt-1">{{ $user->rankPackage->name }}</div>
                                @else
                                    <span class="badge bg-secondary">Unranked</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-1">
                                    @forelse($user->roles as $role)
                                        <span class="badge {{ $role->name === 'admin' ? 'bg-danger' : 'bg-primary' }}">
                                            {{ ucfirst($role->name) }}
                                        </span>
                                    @empty
                                        <span class="text-body-secondary">No roles assigned</span>
                                    @endforelse
                                </div>
                            </td>
                            <td>
                                @if($user->network_status === 'active')
                                    <span class="badge bg-success">
                                        <svg class="icon">
                                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-check-alt') }}"></use>
                                        </svg>
                                        Active
                                    </span>
                                @else
                                    <span class="badge bg-secondary">
                                        <svg class="icon">
                                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-x') }}"></use>
                                        </svg>
                                        Inactive
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $user->created_at->format('M d, Y') }}</div>
                                <div class="text-body-secondary small">{{ $user->created_at->format('g:i A') }}</div>
                            </td>
                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-primary">
                                        <svg class="icon me-1">
                                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-pencil') }}"></use>
                                        </svg>
                                        Edit
                                    </a>
                                    @unless($user->hasRole('admin'))
                                        @if($user->wallet && $user->wallet->is_active)
                                            <button onclick="suspendUser({{ $user->id }})" class="btn btn-sm btn-outline-warning">
                                                <svg class="icon me-1">
                                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-lock-locked') }}"></use>
                                                </svg>
                                                Suspend
                                            </button>
                                        @else
                                            <button onclick="activateUser({{ $user->id }})" class="btn btn-sm btn-outline-success">
                                                <svg class="icon me-1">
                                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-lock-unlocked') }}"></use>
                                                </svg>
                                                Activate
                                            </button>
                                        @endif
                                    @endunless
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @if($users->hasPages())
        <div class="card-footer">
            {{ $users->appends(request()->query())->links() }}
        </div>
    @endif
</div>

<!-- Bottom spacing for better visual layout -->
<div class="pb-5"></div>

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

@push('styles')
<style>
/* Pagination improvements - prevent overflow */
.card-footer {
    overflow-x: auto;
    overflow-y: hidden;
}

.card-footer nav {
    min-width: fit-content;
}

.pagination {
    flex-wrap: wrap;
    margin-bottom: 0;
}

.pagination .page-item {
    margin: 2px;
}

.pagination .page-link {
    min-width: 32px;
    text-align: center;
}

/* Mobile responsiveness improvements */
@media (max-width: 767.98px) {
    .card-header {
        padding: 1rem;
    }
    
    .card-header h4, .card-header h5 {
        font-size: 1.1rem;
    }
    
    /* Make list items more mobile-friendly */
    .card-body {
        padding: 0.75rem;
    }
    
    /* Adjust statistics cards on mobile */
    .row.g-3 {
        gap: 0.5rem !important;
    }
    
    .card-body.pb-0 {
        padding: 0.75rem !important;
    }
    
    .fs-4 {
        font-size: 1.25rem !important;
    }
    
    /* Button improvements */
    .btn-sm {
        padding: 0.375rem 0.5rem;
        font-size: 0.875rem;
    }
    
    /* Pagination on mobile */
    .card-footer {
        padding: 0.75rem;
    }
    
    .pagination .page-item .page-link {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
        min-width: 28px;
    }
    
    /* Hide some pagination numbers on mobile */
    .pagination .page-item:not(.active):not(:first-child):not(:last-child):not(:nth-child(2)):not(:nth-last-child(2)) {
        display: none;
    }
}

@media (max-width: 575.98px) {
    /* Extra small screens */
    /* Hide verbose text on mobile */
    .d-none-xs {
        display: none !important;
    }
    
    /* More aggressive pagination hiding on very small screens */
    .pagination .page-item:not(.active):not(:first-child):not(:last-child):not(:nth-child(2)):not(:nth-last-child(2)) {
        display: none;
    }
    
    /* Make pagination info smaller */
    .card-footer small {
        font-size: 0.75rem;
    }
}

/* Prevent card header from overflowing */
.card-header {
    overflow: hidden;
}

.card-header > div {
    min-width: 0;
}

/* Prevent card footer from overflowing */
.card-footer {
    overflow-x: auto;
}

/* Improve badge visibility */
.badge {
    font-size: 0.75rem;
    padding: 0.35em 0.65em;
}

/* Gradient backgrounds for stats cards */
.bg-primary-gradient {
    background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
}

.bg-success-gradient {
    background: linear-gradient(135deg, #198754 0%, #157347 100%);
}

.bg-danger-gradient {
    background: linear-gradient(135deg, #dc3545 0%, #b02a37 100%);
}

.bg-warning-gradient {
    background: linear-gradient(135deg, #ffc107 0%, #ffb300 100%);
}

.bg-info-gradient {
    background: linear-gradient(135deg, #0dcaf0 0%, #0baccc 100%);
}

/* Scrollbar styling for pagination overflow */
.card-footer::-webkit-scrollbar {
    height: 6px;
}

.card-footer::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.card-footer::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 3px;
}

.card-footer::-webkit-scrollbar-thumb:hover {
    background: #555;
}
</style>
@endpush

@push('scripts')
<script>
let currentUserId = null;

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