@extends('layouts.admin')

@section('title', 'Return Requests Management')
@section('page-title', 'Return Requests')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Return Requests</h2>
            <div class="text-muted">Manage customer return requests and refunds</div>
        </div>
        <div>
            <span class="badge bg-warning fs-6">
                {{ $pendingCount }} Pending
            </span>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('admin.returns.index') }}" method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending Review</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control"
                           placeholder="Order number, customer name or email..."
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <svg class="icon me-1">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-magnifying-glass') }}"></use>
                        </svg>
                        Filter
                    </button>
                    <a href="{{ route('admin.returns.index') }}" class="btn btn-outline-secondary">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Return Requests Table -->
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Return Requests</h5>
                <x-per-page-selector :perPage="$perPage" />
            </div>
        </div>
        <div class="card-body">
            @if($returnRequests->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Return ID</th>
                            <th>Order</th>
                            <th>Customer</th>
                            <th>Reason</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Submitted</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($returnRequests as $return)
                        <tr>
                            <td>
                                <strong>#{{ $return->id }}</strong>
                            </td>
                            <td>
                                <a href="{{ route('admin.orders.show', $return->order) }}" class="text-decoration-none">
                                    {{ $return->order->order_number }}
                                </a>
                                <div class="small text-muted">
                                    {{ $return->order->orderItems->count() }} item(s)
                                </div>
                            </td>
                            <td>
                                <div>{{ $return->user->name }}</div>
                                <div class="small text-muted">{{ $return->user->email }}</div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">{{ $return->reason_label }}</span>
                            </td>
                            <td>
                                <strong>${{ number_format($return->order->total_amount, 2) }}</strong>
                            </td>
                            <td>
                                <span class="{{ $return->status_badge_class }}">{{ $return->status_label }}</span>
                            </td>
                            <td>
                                <div>{{ $return->created_at->format('M d, Y') }}</div>
                                <div class="small text-muted">{{ $return->created_at->format('g:i A') }}</div>
                            </td>
                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    @if($return->isPending())
                                        <!-- Approve Button -->
                                        <button type="button" class="btn btn-sm btn-success"
                                                data-coreui-toggle="modal"
                                                data-coreui-target="#approveModal{{ $return->id }}"
                                                title="Approve Return">
                                            <svg class="icon">
                                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-check') }}"></use>
                                            </svg>
                                        </button>
                                        <!-- Reject Button -->
                                        <button type="button" class="btn btn-sm btn-danger"
                                                data-coreui-toggle="modal"
                                                data-coreui-target="#rejectModal{{ $return->id }}"
                                                title="Reject Return">
                                            <svg class="icon">
                                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-x') }}"></use>
                                            </svg>
                                        </button>
                                    @elseif($return->isApproved() && $return->return_tracking_number)
                                        <!-- Confirm Received & Refund Button -->
                                        <button type="button" class="btn btn-sm btn-primary"
                                                data-coreui-toggle="modal"
                                                data-coreui-target="#refundModal{{ $return->id }}"
                                                title="Process Refund">
                                            <svg class="icon me-1">
                                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-dollar') }}"></use>
                                            </svg>
                                            Refund
                                        </button>
                                    @endif
                                    <!-- View Details Button -->
                                    <button type="button" class="btn btn-sm btn-outline-secondary"
                                            data-coreui-toggle="modal"
                                            data-coreui-target="#detailsModal{{ $return->id }}"
                                            title="View Details">
                                        <svg class="icon">
                                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-search') }}"></use>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="card-footer">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted">
                        Showing {{ $returnRequests->firstItem() ?? 0 }} to {{ $returnRequests->lastItem() ?? 0 }} of {{ $returnRequests->total() }} returns
                    </div>
                    {{ $returnRequests->appends(request()->query())->links() }}
                </div>
            </div>

            <!-- Modals (outside table structure) -->
            @foreach($returnRequests as $return)
                <!-- Details Modal -->
                        <div class="modal fade" id="detailsModal{{ $return->id }}" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Return Request Details - #{{ $return->id }}</h5>
                                        <button type="button" class="btn-close" data-coreui-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <h6 class="text-muted mb-3">Order Information</h6>
                                                <div class="p-3 bg-light border rounded">
                                                    <table class="table table-sm table-borderless mb-0">
                                                        <tr>
                                                            <td class="fw-semibold ps-0" style="width: 140px;">Order Number:</td>
                                                            <td class="pe-0">{{ $return->order->order_number }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td class="fw-semibold ps-0">Order Total:</td>
                                                            <td class="pe-0">${{ number_format($return->order->total_amount, 2) }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td class="fw-semibold ps-0">Delivered:</td>
                                                            <td class="pe-0">{{ $return->order->delivered_at ? $return->order->delivered_at->format('M d, Y g:i A') : 'N/A' }}</td>
                                                        </tr>
                                                    </table>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <h6 class="text-muted mb-3">Customer Information</h6>
                                                <div class="p-3 bg-light border rounded">
                                                    <table class="table table-sm table-borderless mb-0">
                                                        <tr>
                                                            <td class="fw-semibold ps-0" style="width: 140px;">Name:</td>
                                                            <td class="pe-0">{{ $return->user->name }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td class="fw-semibold ps-0">Email:</td>
                                                            <td class="pe-0">{{ $return->user->email }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td class="fw-semibold ps-0">Phone:</td>
                                                            <td class="pe-0">{{ $return->user->phone ?? 'N/A' }}</td>
                                                        </tr>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>

                                        <hr>

                                        <div class="mb-4">
                                            <h6 class="text-muted mb-3">Return Details</h6>
                                            <div class="p-3 bg-light border rounded">
                                                <table class="table table-sm table-borderless mb-0">
                                                    <tr>
                                                        <td class="fw-semibold ps-0" style="width: 150px;">Status:</td>
                                                        <td class="pe-0"><span class="{{ $return->status_badge_class }}">{{ $return->status_label }}</span></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="fw-semibold ps-0">Reason:</td>
                                                        <td class="pe-0">{{ $return->reason_label }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="fw-semibold ps-0">Submitted:</td>
                                                        <td class="pe-0">{{ $return->created_at->format('M d, Y g:i A') }}</td>
                                                    </tr>
                                                    @if($return->approved_at)
                                                    <tr>
                                                        <td class="fw-semibold ps-0">Approved:</td>
                                                        <td class="pe-0">{{ $return->approved_at->format('M d, Y g:i A') }}</td>
                                                    </tr>
                                                    @endif
                                                    @if($return->rejected_at)
                                                    <tr>
                                                        <td class="fw-semibold ps-0">Rejected:</td>
                                                        <td class="pe-0">{{ $return->rejected_at->format('M d, Y g:i A') }}</td>
                                                    </tr>
                                                    @endif
                                                    @if($return->return_tracking_number)
                                                    <tr>
                                                        <td class="fw-semibold ps-0">Return Tracking:</td>
                                                        <td class="pe-0"><code>{{ $return->return_tracking_number }}</code></td>
                                                    </tr>
                                                    @endif
                                                </table>
                                            </div>
                                        </div>

                                        <div class="mb-4">
                                            <h6 class="text-muted mb-3">Customer Description</h6>
                                            <div class="p-3 bg-light border rounded">
                                                <p class="mb-0" style="white-space: pre-line;">{{ $return->description }}</p>
                                            </div>
                                        </div>

                                        @if($return->images && count($return->images) > 0)
                                        <div class="mb-4">
                                            <h6 class="text-muted mb-3">Proof Images ({{ count($return->images) }})</h6>
                                            <div class="row g-3">
                                                @foreach($return->images as $image)
                                                <div class="col-6 col-md-4">
                                                    <a href="{{ asset('storage/' . $image) }}" target="_blank" class="d-block">
                                                        <img src="{{ asset('storage/' . $image) }}"
                                                             alt="Return proof"
                                                             class="img-thumbnail w-100"
                                                             style="height: 150px; object-fit: cover; cursor: pointer;">
                                                    </a>
                                                </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        @endif

                                        @if($return->admin_response)
                                        <div class="mb-4">
                                            <h6 class="text-muted mb-3">Admin Response</h6>
                                            <div class="p-3 bg-info-subtle border border-info rounded">
                                                <p class="mb-0 text-dark" style="white-space: pre-line;">{{ $return->admin_response }}</p>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Close</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Approve Modal -->
                        @if($return->isPending())
                        <div class="modal fade" id="approveModal{{ $return->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Approve Return Request</h5>
                                        <button type="button" class="btn-close" data-coreui-dismiss="modal"></button>
                                    </div>
                                    <form action="{{ route('admin.returns.approve', $return) }}" method="POST">
                                        @csrf
                                        <div class="modal-body">
                                            <div class="alert alert-success">
                                                <strong>Approving return for Order #{{ $return->order->order_number }}</strong>
                                                <div class="mt-2">Refund Amount: <strong>${{ number_format($return->order->total_amount, 2) }}</strong></div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Response to Customer <span class="text-danger">*</span></label>
                                                <textarea name="admin_response" class="form-control" rows="4" required placeholder="Provide return instructions and shipping address...">We've reviewed your return request and approved it. Please ship the item back to:

[Your Company Address]
[City, State ZIP]

Use a trackable shipping method and provide the tracking number. We'll process your refund once we receive and verify the returned item.</textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-success">Approve Return</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Reject Modal -->
                        <div class="modal fade" id="rejectModal{{ $return->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Reject Return Request</h5>
                                        <button type="button" class="btn-close" data-coreui-dismiss="modal"></button>
                                    </div>
                                    <form action="{{ route('admin.returns.reject', $return) }}" method="POST">
                                        @csrf
                                        <div class="modal-body">
                                            <div class="alert alert-warning">
                                                <strong>Rejecting return for Order #{{ $return->order->order_number }}</strong>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                                                <textarea name="admin_response" class="form-control" rows="4" required placeholder="Explain why the return request is being rejected..."></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-danger">Reject Return</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Refund Modal -->
                        @if($return->isApproved() && $return->return_tracking_number)
                        <div class="modal fade" id="refundModal{{ $return->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Process Refund</h5>
                                        <button type="button" class="btn-close" data-coreui-dismiss="modal"></button>
                                    </div>
                                    <form action="{{ route('admin.returns.confirm-received', $return) }}" method="POST">
                                        @csrf
                                        <div class="modal-body">
                                            <div class="alert alert-info">
                                                <h6 class="alert-heading">Confirm Return Receipt & Process Refund</h6>
                                                <ul class="mb-0">
                                                    <li>Order: <strong>{{ $return->order->order_number }}</strong></li>
                                                    <li>Tracking: <code>{{ $return->return_tracking_number }}</code></li>
                                                    <li>Refund Amount: <strong>${{ number_format($return->order->total_amount, 2) }}</strong></li>
                                                </ul>
                                            </div>
                                            <p>Are you sure you've received the returned item and verified its condition?</p>
                                            <p class="text-muted small">This will:</p>
                                            <ul class="small text-muted">
                                                <li>Mark the return as completed</li>
                                                <li>Credit ${{ number_format($return->order->total_amount, 2) }} to customer's e-wallet</li>
                                                <li>Update order status to "Refunded"</li>
                                            </ul>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary">Confirm & Process Refund</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endif
            @endforeach
            @else
            <div class="text-center py-5">
                <svg class="icon icon-4xl text-muted mb-3">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-inbox') }}"></use>
                </svg>
                <h5 class="text-muted">No Return Requests Found</h5>
                <p class="text-muted">
                    @if(request('status') || request('search'))
                        Try adjusting your filters.
                    @else
                        Return requests will appear here when customers submit them.
                    @endif
                </p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Modal styling improvements */
.modal-content {
    border-radius: 0.5rem;
}

.modal-header {
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid #e0e0e0;
}

.modal-header .btn-close {
    padding: 0.75rem;
    margin: -0.75rem -0.75rem -0.75rem auto;
}

.modal-body {
    padding: 1.5rem;
}

.modal-footer {
    padding: 1rem 1.5rem;
    border-top: 1px solid #e0e0e0;
}

/* Table improvements */
.table-sm td {
    padding: 0.5rem 0.75rem;
}

.table-borderless td {
    padding: 0.375rem 0.5rem;
}

/* Card body padding */
.card-body {
    padding: 1.5rem;
}

/* Form group spacing in modals */
.modal-body .mb-3 {
    margin-bottom: 1.25rem;
}

/* Image grid improvements */
.modal-body .row.g-2 {
    margin: -0.5rem;
}

.modal-body .row.g-2 > [class*='col-'] {
    padding: 0.5rem;
}

/* Alert spacing in modals */
.modal-body .alert {
    padding: 1rem;
    margin-bottom: 1.25rem;
}

/* Badge styling */
.badge {
    padding: 0.35em 0.65em;
    font-weight: 500;
}

/* Button group spacing */
.btn-group .btn-sm {
    padding: 0.375rem 0.75rem;
}

/* Details modal specific */
.modal-lg .modal-body hr {
    margin: 1.5rem 0;
}

/* Image container in modals */
.modal-body img {
    border-radius: 0.375rem;
    border: 1px solid #e0e0e0;
}

/* Form labels in modals */
.modal-body .form-label {
    margin-bottom: 0.5rem;
    font-weight: 500;
    font-size: 0.875rem;
}

/* Textarea in modals */
.modal-body textarea.form-control {
    min-height: 100px;
}

/* Section headers in modal */
.modal-body h6 {
    margin-bottom: 1rem;
    font-weight: 600;
    color: #6c757d;
}

/* Container fluid padding */
.container-fluid {
    padding-left: 1.5rem;
    padding-right: 1.5rem;
}

/* Table responsive padding fix */
.table-responsive {
    margin: -0.5rem;
    padding: 0.5rem;
}
</style>
@endpush
