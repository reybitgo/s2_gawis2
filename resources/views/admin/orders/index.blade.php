@extends('layouts.admin')

@section('title', 'Order Management')
@section('page-title', 'Order Management')

@push('styles')
<style>
/* Ensure dropdowns in tables appear on top */
.table .btn-group {
    position: static;
}

.table .dropdown-menu {
    z-index: 1050;
    position: absolute;
}

/* Ensure table doesn't create stacking context issues */
.table-responsive {
    overflow-x: auto;
    overflow-y: visible;
}

.table {
    position: relative;
}

/* Fix for last column dropdowns that might get cut off */
.table td:last-child .dropdown-menu {
    right: 0;
    left: auto;
}

/* Mobile responsiveness improvements */
@media (max-width: 767.98px) {
    .card-header {
        padding: 1rem;
    }
    
    .card-header h5 {
        font-size: 1.1rem;
    }
    
    /* Stack buttons vertically on very small screens */
    .btn-toolbar .btn-group {
        width: 100%;
    }
    
    .btn-toolbar .btn-group .btn {
        flex: 1;
    }
    
    /* Improve filter section on mobile */
    .card-body.border-bottom {
        padding: 0.75rem;
    }
    
    /* Make table more mobile-friendly */
    .table-responsive {
        font-size: 0.875rem;
    }
    
    .table th, .table td {
        padding: 0.5rem 0.25rem;
        white-space: nowrap;
    }
    
    /* Adjust statistics cards on mobile */
    .row.g-3 {
        gap: 0.5rem !important;
    }
    
    .card-body.py-3 {
        padding: 0.75rem !important;
    }
    
    .text-value-lg {
        font-size: 1.25rem !important;
    }
}

@media (max-width: 575.98px) {
    /* Extra small screens - hide button text, show only icons */
    .btn-toolbar .btn-group .btn svg.icon {
        margin-right: 0 !important;
    }
    
    /* Ensure per-page selector doesn't overflow */
    .per-page-selector {
        font-size: 0.875rem;
    }
}

/* Prevent card header from overflowing */
.card-header {
    overflow: hidden;
}

.card-header > div {
    min-width: 0;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Order Statistics Cards -->
    <div class="row g-3 mb-4">
        @foreach($statusStats as $groupName => $stats)
        <div class="col-md-2 col-sm-4 col-6">
            <div class="card text-center">
                <div class="card-body py-3">
                    <div class="text-value-lg text-primary">{{ $stats['count'] }}</div>
                    <div class="text-muted text-uppercase font-weight-bold small">{{ $stats['label'] }}</div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    @if(count($ordersRequiringAttention) > 0)
    <!-- Attention Required Alert -->
    <div class="alert alert-warning" role="alert">
        <div class="d-flex">
            <div>
                <svg class="icon me-2">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-warning') }}"></use>
                </svg>
            </div>
            <div>
                <h4 class="alert-heading">Orders Requiring Attention</h4>
                <p class="mb-0">{{ count($ordersRequiringAttention) }} orders require immediate attention (stuck in problematic status for over 24 hours).</p>
            </div>
        </div>
    </div>
    @endif

    <!-- Main Orders Card -->
    <div class="card">
        <div class="card-header">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                <h5 class="card-title mb-0">Orders</h5>
                <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center gap-2 w-100 w-md-auto">
                    <div class="btn-toolbar" role="toolbar">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-outline-primary btn-sm" id="exportBtn">
                                <svg class="icon me-1">
                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-cloud-download') }}"></use>
                                </svg>
                                <span class="d-none d-sm-inline">Export</span>
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="refreshBtn">
                                <svg class="icon me-1">
                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-reload') }}"></use>
                                </svg>
                                <span class="d-none d-sm-inline">Refresh</span>
                            </button>
                        </div>
                    </div>
                    <x-per-page-selector :perPage="$perPage" />
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card-body border-bottom">
            <form method="GET" id="filtersForm">
                <div class="row g-3">
                    <div class="col-md-2 col-sm-6 col-12">
                        <label class="form-label small">Status Group</label>
                        <select name="status_group" class="form-select form-select-sm">
                            <option value="">All Groups</option>
                            <option value="pre_fulfillment" {{ request('status_group') == 'pre_fulfillment' ? 'selected' : '' }}>Pre-fulfillment</option>
                            <option value="fulfillment" {{ request('status_group') == 'fulfillment' ? 'selected' : '' }}>Fulfillment</option>
                            <option value="delivery" {{ request('status_group') == 'delivery' ? 'selected' : '' }}>Delivery</option>
                            <option value="issues" {{ request('status_group') == 'issues' ? 'selected' : '' }}>Issues</option>
                            <option value="completed" {{ request('status_group') == 'completed' ? 'selected' : '' }}>Completed</option>
                        </select>
                    </div>
                    <div class="col-md-2 col-sm-6 col-12">
                        <label class="form-label small">Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">All Statuses</option>
                            @foreach(\App\Models\Order::getStatusLabels() as $status => $label)
                                <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 col-sm-6 col-12">
                        <label class="form-label small">Delivery Method</label>
                        <select name="delivery_method" class="form-select form-select-sm">
                            <option value="">All Methods</option>
                            <option value="office_pickup" {{ request('delivery_method') == 'office_pickup' ? 'selected' : '' }}>Office Pickup</option>
                            <option value="home_delivery" {{ request('delivery_method') == 'home_delivery' ? 'selected' : '' }}>Home Delivery</option>
                        </select>
                    </div>
                    <div class="col-md-2 col-sm-6 col-12">
                        <label class="form-label small">Payment Status</label>
                        <select name="payment_status" class="form-select form-select-sm">
                            <option value="">All Payment</option>
                            <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="failed" {{ request('payment_status') == 'failed' ? 'selected' : '' }}>Failed</option>
                            <option value="refunded" {{ request('payment_status') == 'refunded' ? 'selected' : '' }}>Refunded</option>
                        </select>
                    </div>
                    <div class="col-md-2 col-sm-6 col-12">
                        <label class="form-label small">Date From</label>
                        <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2 col-sm-6 col-12">
                        <label class="form-label small">Date To</label>
                        <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                    </div>
                </div>
                <div class="row g-3 mt-2">
                    <div class="col-md-8 col-12">
                        <label class="form-label small">Search</label>
                        <input type="text" name="search" class="form-control form-control-sm"
                               placeholder="Order number, customer name, email, notes..."
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-4 col-12 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                            <svg class="icon me-1">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-filter') }}"></use>
                            </svg>
                            Filter
                        </button>
                        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary btn-sm flex-grow-1">
                            <svg class="icon me-1">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-reload') }}"></use>
                            </svg>
                            Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Orders Table -->
        <div class="card-body p-0">
            @if($orders->count() > 0)
                <!-- Bulk Actions -->
                <div class="p-3 border-bottom bg-light">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="selectAll">
                                <label class="form-check-label" for="selectAll">
                                    Select All
                                </label>
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="btn-group" id="bulkActions" style="display: none;">
                                <select class="form-select form-select-sm me-2" id="bulkStatus">
                                    <option value="">Select Status</option>
                                    @foreach($adminAllowedStatusLabels as $status => $label)
                                        <option value="{{ $status }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-sm btn-primary" id="bulkUpdateBtn">Update Selected</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="40">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="selectAllTable">
                                    </div>
                                </th>
                                <th>Order</th>
                                <th>Customer</th>
                                <th>Status</th>
                                <th>Delivery</th>
                                <th>Payment</th>
                                <th>Total</th>
                                <th>Date</th>
                                <th width="120">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $order)
                                <tr>
                                    <td>
                                        <div class="form-check">
                                            <input class="form-check-input order-checkbox" type="checkbox" value="{{ $order->id }}">
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ $order->order_number }}</strong>
                                            @if($order->admin_notes)
                                                <div class="text-muted small">{{ Str::limit($order->admin_notes, 50) }}</div>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ $order->user->name }}</strong>
                                            <div class="text-muted small">{{ $order->user->email }}</div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ \App\Models\Order::getStatusBadgeColor($order->status) }}">
                                            {{ $order->status_label }}
                                        </span>
                                        @if(in_array($order->status, ['on_hold', 'delivery_failed', 'payment_failed']))
                                            <svg class="icon text-warning ms-1" title="Requires Attention">
                                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-warning') }}"></use>
                                            </svg>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $order->delivery_method_label }}</span>
                                        @if($order->tracking_number && $order->isHomeDelivery())
                                            <div class="text-muted small">{{ $order->tracking_number }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $order->payment_status === 'paid' ? 'success' : ($order->payment_status === 'failed' ? 'danger' : 'warning') }}">
                                            {{ ucfirst($order->payment_status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <strong>{{ $order->formatted_total }}</strong>
                                        <div class="text-muted small">{{ $order->getTotalItemsCount() }} items</div>
                                    </td>
                                    <td>
                                        <div>{{ $order->created_at->format('M d, Y') }}</div>
                                        <div class="text-muted small">{{ $order->created_at->format('H:i') }}</div>
                                    </td>
                                    <td>
                                        <div class="btn-group dropdown" role="group">
                                            <a href="{{ route('admin.orders.show', $order) }}"
                                               class="btn btn-sm btn-outline-primary" title="View Details">
                                                <svg class="icon">
                                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-magnifying-glass') }}"></use>
                                                </svg>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                                    data-coreui-toggle="dropdown"
                                                    aria-expanded="false">
                                                <svg class="icon">
                                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-options') }}"></use>
                                                </svg>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li><h6 class="dropdown-header">Quick Actions</h6></li>
                                                @if($order->adminAllowedStatuses && count($order->adminAllowedStatuses) > 0)
                                                    @foreach($order->adminAllowedStatuses as $statusInfo)
                                                        <li>
                                                            <a class="dropdown-item quick-status-change"
                                                               href="#"
                                                               data-order-id="{{ $order->id }}"
                                                               data-status="{{ $statusInfo['status'] }}">
                                                                {{ $statusInfo['label'] }}
                                                            </a>
                                                        </li>
                                                    @endforeach
                                                @else
                                                    <li><span class="dropdown-item-text text-muted">No actions available</span></li>
                                                @endif
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="card-footer">
                    {{ $orders->appends(request()->query())->links('vendor.pagination.coreui') }}
                </div>
            @else
                <div class="card-body text-center py-5">
                    <svg class="icon icon-4xl text-muted mb-3">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-inbox') }}"></use>
                    </svg>
                    <h5 class="text-muted">No orders found</h5>
                    <p class="text-muted">Try adjusting your search criteria or filters.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Bulk Status Update Modal -->
<div class="modal fade" id="bulkStatusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Multiple Orders</h5>
                <button type="button" class="btn-close" data-coreui-dismiss="modal"></button>
            </div>
            <form id="bulkStatusForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">New Status</label>
                        <select name="status" class="form-select" required>
                            <option value="">Select Status</option>
                            @foreach($adminAllowedStatusLabels as $status => $label)
                                <option value="{{ $status }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes (Optional)</label>
                        <textarea name="notes" class="form-control" rows="3"
                                  placeholder="Add notes about this status change..."></textarea>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="notify_customer" id="bulkNotifyCustomer">
                        <label class="form-check-label" for="bulkNotifyCustomer">
                            Notify customers about this status change
                        </label>
                    </div>
                    <div class="alert alert-info">
                        <strong id="selectedCount">0</strong> orders will be updated.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Orders</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Quick Status Confirmation Modal -->
<div class="modal fade" id="quickStatusConfirmModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Status Change</h5>
                <button type="button" class="btn-close" data-coreui-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex align-items-start">
                    <div class="me-3">
                        <svg class="icon icon-2xl text-warning">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-warning') }}"></use>
                        </svg>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-2">Are you sure you want to change the order status?</h6>
                        <p class="mb-2">
                            <strong>Order:</strong> <span id="quickConfirmOrderNumber"></span><br>
                            <strong>New Status:</strong> <span id="quickConfirmNewStatus" class="badge bg-primary"></span>
                        </p>
                        <div class="text-muted small">
                            <svg class="icon me-1">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-info') }}"></use>
                            </svg>
                            This action will update the order status immediately.
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmQuickStatusUpdate">
                    <svg class="icon me-2">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-check') }}"></use>
                    </svg>
                    Confirm Change
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Bottom spacing for better visual layout -->
<div class="pb-5"></div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-submit filters on change
    document.querySelectorAll('#filtersForm select').forEach(select => {
        select.addEventListener('change', function() {
            document.getElementById('filtersForm').submit();
        });
    });

    // Select all functionality
    const selectAll = document.getElementById('selectAll');
    const selectAllTable = document.getElementById('selectAllTable');
    const orderCheckboxes = document.querySelectorAll('.order-checkbox');
    const bulkActions = document.getElementById('bulkActions');

    function updateBulkActions() {
        const checkedBoxes = document.querySelectorAll('.order-checkbox:checked');
        if (checkedBoxes.length > 0) {
            bulkActions.style.display = 'block';
            document.getElementById('selectedCount').textContent = checkedBoxes.length;
        } else {
            bulkActions.style.display = 'none';
        }
    }

    [selectAll, selectAllTable].forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            orderCheckboxes.forEach(cb => cb.checked = this.checked);
            updateBulkActions();
        });
    });

    orderCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkActions);
    });

    // Quick status change - show confirmation modal
    document.querySelectorAll('.quick-status-change').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const orderId = this.dataset.orderId;
            const status = this.dataset.status;
            const orderNumber = this.dataset.orderNumber || `Order #${orderId}`;
            const statusLabel = this.textContent.trim();

            // Update confirmation modal with details
            document.getElementById('quickConfirmOrderNumber').textContent = orderNumber;
            document.getElementById('quickConfirmNewStatus').textContent = statusLabel;

            // Store data for confirmation
            window.pendingQuickUpdate = {
                orderId: orderId,
                status: status
            };

            // Show confirmation modal
            const confirmModal = new coreui.Modal(document.getElementById('quickStatusConfirmModal'));
            confirmModal.show();
        });
    });

    // Quick status confirmation handler
    document.getElementById('confirmQuickStatusUpdate').addEventListener('click', function() {
        if (window.pendingQuickUpdate) {
            const { orderId, status } = window.pendingQuickUpdate;

            // Hide confirmation modal
            const confirmModal = new coreui.Modal(document.getElementById('quickStatusConfirmModal'));
            confirmModal.hide();

            // Perform the actual status update
            updateOrderStatus(orderId, status);

            // Clear pending update
            window.pendingQuickUpdate = null;
        }
    });

    // Bulk status update
    document.getElementById('bulkUpdateBtn').addEventListener('click', function() {
        const checkedBoxes = document.querySelectorAll('.order-checkbox:checked');
        if (checkedBoxes.length === 0) {
            alert('Please select orders to update.');
            return;
        }

        const modal = new coreui.Modal(document.getElementById('bulkStatusModal'));
        modal.show();
    });

    document.getElementById('bulkStatusForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const checkedBoxes = document.querySelectorAll('.order-checkbox:checked');
        const orderIds = Array.from(checkedBoxes).map(cb => cb.value);
        const formData = new FormData(this);

        fetch('{{ route("admin.orders.bulk-update-status") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                order_ids: orderIds,
                status: formData.get('status'),
                notes: formData.get('notes'),
                notify_customer: formData.get('notify_customer') === 'on'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating orders.');
        });
    });

    // Export functionality
    document.getElementById('exportBtn').addEventListener('click', function() {
        const params = new URLSearchParams(window.location.search);
        window.location.href = '{{ route("admin.orders.export") }}?' + params.toString();
    });

    // Refresh functionality
    document.getElementById('refreshBtn').addEventListener('click', function() {
        location.reload();
    });

    function updateOrderStatus(orderId, status, notes = null) {
        fetch(`/admin/orders/${orderId}/update-status`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                status: status,
                notes: notes
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating the order status.');
        });
    }
});

// Helper function for status badge colors
function getStatusBadgeColor(status) {
    const colors = {
        'pending': 'warning',
        'paid': 'info',
        'processing': 'primary',
        'confirmed': 'primary',
        'packing': 'primary',
        'ready_for_pickup': 'info',
        'pickup_notified': 'info',
        'ready_to_ship': 'info',
        'shipped': 'info',
        'in_transit': 'info',
        'out_for_delivery': 'info',
        'delivered': 'success',
        'received_in_office': 'success',
        'completed': 'success',
        'on_hold': 'warning',
        'cancelled': 'secondary',
        'refunded': 'secondary',
        'returned': 'secondary',
        'delivery_failed': 'danger',
        'payment_failed': 'danger',
        'failed': 'danger'
    };
    return colors[status] || 'secondary';
}
</script>
@endpush
