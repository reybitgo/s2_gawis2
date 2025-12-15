@extends('layouts.admin')

@section('title', 'System Logs')

@section('content')
<!-- Page Header -->
<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
            <div>
                <h4 class="card-title mb-0">
                    <svg class="icon me-2">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-list') }}"></use>
                    </svg>
                    System Logs
                </h4>
                <p class="text-body-secondary mb-0">Monitor system activity and security events</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <button onclick="refreshLogs()" class="btn btn-primary">
                    <svg class="icon me-2">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-reload') }}"></use>
                    </svg>
                    Refresh
                </button>
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

<!-- Filters -->
<div class="card mb-4">
    <div class="card-header">
        <svg class="icon me-2">
            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-filter') }}"></use>
        </svg>
        <strong>Filter Logs</strong>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.logs') }}" class="row g-3">
            <div class="col-md-5 col-12">
                <label for="search" class="form-label small">Search Logs</label>
                <input type="text" name="search" id="search" value="{{ $search }}"
                    placeholder="Search by message or IP address..." class="form-control form-control-sm">
            </div>
            <div class="col-md-3 col-sm-6 col-12">
                <label for="type" class="form-label small">Log Type</label>
                <select name="type" id="type" class="form-select form-select-sm">
                    <option value="all" {{ $logType == 'all' ? 'selected' : '' }}>All Types</option>
                    <option value="security" {{ $logType == 'security' ? 'selected' : '' }}>Security</option>
                    <option value="transaction" {{ $logType == 'transaction' ? 'selected' : '' }}>Transaction</option>
                    <option value="mlm_commission" {{ $logType == 'mlm_commission' ? 'selected' : '' }}>MLM Commission</option>
                    <option value="wallet" {{ $logType == 'wallet' ? 'selected' : '' }}>Wallet</option>
                    <option value="order" {{ $logType == 'order' ? 'selected' : '' }}>Order</option>
                    <option value="system" {{ $logType == 'system' ? 'selected' : '' }}>System</option>
                </select>
            </div>
            <div class="col-md-2 col-sm-6 col-12">
                <label for="level" class="form-label small">Log Level</label>
                <select name="level" id="level" class="form-select form-select-sm">
                    <option value="all" {{ $level == 'all' ? 'selected' : '' }}>All Levels</option>
                    <option value="DEBUG" {{ $level == 'DEBUG' ? 'selected' : '' }}>Debug</option>
                    <option value="INFO" {{ $level == 'INFO' ? 'selected' : '' }}>Info</option>
                    <option value="WARNING" {{ $level == 'WARNING' ? 'selected' : '' }}>Warning</option>
                    <option value="ERROR" {{ $level == 'ERROR' ? 'selected' : '' }}>Error</option>
                    <option value="CRITICAL" {{ $level == 'CRITICAL' ? 'selected' : '' }}>Critical</option>
                </select>
            </div>
            <div class="col-md-2 col-12 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                    <svg class="icon me-1">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-filter') }}"></use>
                    </svg>
                    Filter
                </button>
                <a href="{{ route('admin.logs') }}" class="btn btn-outline-secondary btn-sm">
                    <svg class="icon">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-reload') }}"></use>
                    </svg>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Log Statistics -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card text-white bg-primary-gradient">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold">{{ collect($activityLogs->items())->where('level', 'INFO')->count() }}</div>
                    <div>Info Events (Page)</div>
                </div>
                <svg class="icon icon-3xl">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-info') }}"></use>
                </svg>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card text-white bg-warning-gradient">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold">{{ collect($activityLogs->items())->where('level', 'WARNING')->count() }}</div>
                    <div>Warnings (Page)</div>
                </div>
                <svg class="icon icon-3xl">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-warning') }}"></use>
                </svg>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card text-white bg-danger-gradient">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold">{{ collect($activityLogs->items())->whereIn('level', ['ERROR', 'CRITICAL'])->count() }}</div>
                    <div>Errors (Page)</div>
                </div>
                <svg class="icon icon-3xl">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-x') }}"></use>
                </svg>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card text-white bg-success-gradient">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold">{{ $activityLogs->total() }}</div>
                    <div>Total Entries</div>
                </div>
                <svg class="icon icon-3xl">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-chart-line') }}"></use>
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- Logs Display -->
<div class="card">
    <div class="card-header">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
            <div>
                <svg class="icon me-2">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-list') }}"></use>
                </svg>
                <strong>System Activity Log</strong>
                <small class="text-body-secondary ms-2 d-none d-md-inline">
                    @if($activityLogs->count() > 0)
                        Showing {{ $activityLogs->firstItem() }} to {{ $activityLogs->lastItem() }} of {{ $activityLogs->total() }} log entries
                    @else
                        No log entries found matching the current filters
                    @endif
                </small>
            </div>
            <x-per-page-selector :perPage="$perPage" />
        </div>
    </div>

    @if($activityLogs->count() > 0)
        <div class="card-body p-0">
            <div class="list-group list-group-flush">
                @foreach($activityLogs as $log)
                    @php
                        $levelColors = [
                            'DEBUG' => 'secondary',
                            'INFO' => 'primary',
                            'WARNING' => 'warning',
                            'ERROR' => 'danger',
                            'CRITICAL' => 'dark'
                        ];
                        
                        $typeColors = [
                            'security' => 'danger',
                            'transaction' => 'success',
                            'mlm_commission' => 'primary',
                            'wallet' => 'warning',
                            'order' => 'info',
                            'system' => 'secondary'
                        ];
                        
                        $levelColor = $levelColors[$log['level']] ?? 'secondary';
                        $typeColor = $typeColors[$log['type']] ?? 'secondary';
                    @endphp
                    <div class="list-group-item {{ $log['level'] == 'CRITICAL' ? 'bg-danger-subtle' : ($log['level'] == 'ERROR' ? 'bg-warning-subtle' : '') }}">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="d-flex flex-grow-1">
                                <!-- Log Level Badge -->
                                <div class="me-3">
                                    <span class="badge bg-{{ $levelColor }}">
                                        {{ strtoupper($log['level']) }}
                                    </span>
                                </div>

                                <!-- Log Content -->
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center mb-1">
                                        <h6 class="mb-0 me-2">{{ $log['message'] }}</h6>
                                        <span class="badge bg-{{ $typeColor }} badge-sm">
                                            {{ ucfirst($log['type']) }}
                                        </span>
                                    </div>
                                    <div class="d-flex flex-wrap text-body-secondary small gap-3">
                                        <div class="d-flex align-items-center">
                                            <svg class="icon me-1">
                                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-clock') }}"></use>
                                            </svg>
                                            @if($log['timestamp'])
                                                {{ $log['timestamp']->format('M d, Y g:i A') }}
                                            @else
                                                N/A
                                            @endif
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <svg class="icon me-1">
                                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-location-pin') }}"></use>
                                            </svg>
                                            {{ $log['ip_address'] }}
                                        </div>
                                        @if($log['user_id'])
                                            <div class="d-flex align-items-center">
                                                <svg class="icon me-1">
                                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-user') }}"></use>
                                                </svg>
                                                User ID: {{ $log['user_id'] }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="d-flex gap-2">
                                <button onclick="viewLogDetails({{ json_encode($log) }})" class="btn btn-sm btn-outline-primary">
                                    <svg class="icon me-1">
                                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-info') }}"></use>
                                    </svg>
                                    Details
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Pagination -->
        @if($activityLogs->hasPages())
            <div class="card-footer">
                {{ $activityLogs->appends(request()->query())->links('vendor.pagination.coreui') }}
            </div>
        @endif
    @else
        <div class="card-body text-center py-5">
            <svg class="icon icon-xxl text-body-secondary mb-3">
                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-file') }}"></use>
            </svg>
            <h5 class="text-body-secondary">No logs found</h5>
            <p class="text-body-secondary">Try adjusting your filters or check back later.</p>
        </div>
    @endif
</div>

<!-- Export Options -->
<div class="card mt-4">
    <div class="card-header">
        <svg class="icon me-2">
            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-data-transfer-down') }}"></use>
        </svg>
        <strong>Export Options</strong>
    </div>
    <div class="card-body">
        <div class="d-flex flex-wrap gap-3">
            <button onclick="exportLogs('csv')" class="btn btn-success">
                <svg class="icon me-2">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-spreadsheet') }}"></use>
                </svg>
                Export as CSV
            </button>
            <button onclick="exportLogs('json')" class="btn btn-primary">
                <svg class="icon me-2">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-code') }}"></use>
                </svg>
                Export as JSON
            </button>
            <button onclick="clearOldLogs()" class="btn btn-danger">
                <svg class="icon me-2">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-trash') }}"></use>
                </svg>
                Clear Old Logs (30+ days)
            </button>
        </div>
    </div>
</div>

<!-- Log Details Modal -->
<div class="modal fade" id="log-details-modal" tabindex="-1" aria-labelledby="logDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="logDetailsModalLabel">Log Entry Details</h5>
                <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="log-details-content">
                    <!-- Content will be populated by JavaScript -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function refreshLogs() {
    window.location.reload();
}

function viewLogDetails(log) {
    const content = document.getElementById('log-details-content');

    content.innerHTML = `
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold">Timestamp</label>
                <p class="text-body-secondary">${new Date(log.timestamp).toLocaleString()}</p>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Level</label>
                <p class="text-body-secondary">${log.level}</p>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Type</label>
                <p class="text-body-secondary">${log.type}</p>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">IP Address</label>
                <p class="text-body-secondary">${log.ip_address}</p>
            </div>
            ${log.user_id ? `
            <div class="col-md-6">
                <label class="form-label fw-semibold">User ID</label>
                <p class="text-body-secondary">${log.user_id}</p>
            </div>
            ` : ''}
            <div class="col-12">
                <label class="form-label fw-semibold">Message</label>
                <p class="text-body-secondary">${log.message}</p>
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">User Agent</label>
                <p class="text-body-secondary small">${log.user_agent}</p>
            </div>
        </div>
    `;

    const modal = new coreui.Modal(document.getElementById('log-details-modal'));
    modal.show();
}


function exportLogs(format) {
    // Get current filter values
    const urlParams = new URLSearchParams(window.location.search);
    const type = urlParams.get('type') || 'all';
    const level = urlParams.get('level') || 'all';
    const search = urlParams.get('search') || '';

    // Show loading state
    showAlert(`Preparing ${format.toUpperCase()} export...`, 'info');

    // Create form and submit
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("admin.logs.export") }}';
    form.style.display = 'none';

    // Add CSRF token
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = '{{ csrf_token() }}';
    form.appendChild(csrfInput);

    // Add format
    const formatInput = document.createElement('input');
    formatInput.type = 'hidden';
    formatInput.name = 'format';
    formatInput.value = format;
    form.appendChild(formatInput);

    // Add filters
    const typeInput = document.createElement('input');
    typeInput.type = 'hidden';
    typeInput.name = 'type';
    typeInput.value = type;
    form.appendChild(typeInput);

    const levelInput = document.createElement('input');
    levelInput.type = 'hidden';
    levelInput.name = 'level';
    levelInput.value = level;
    form.appendChild(levelInput);

    const searchInput = document.createElement('input');
    searchInput.type = 'hidden';
    searchInput.name = 'search';
    searchInput.value = search;
    form.appendChild(searchInput);

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);

    // Show success message after a delay
    setTimeout(() => {
        showAlert(`${format.toUpperCase()} export completed and download started.`, 'success');
    }, 1000);
}

function clearOldLogs() {
    // Create a more sophisticated confirmation modal
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.innerHTML = `
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Clear Old Logs</h5>
                    <button type="button" class="btn-close" data-coreui-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="clearDays" class="form-label">Clear logs older than:</label>
                        <select id="clearDays" class="form-select">
                            <option value="7">7 days</option>
                            <option value="30" selected>30 days</option>
                            <option value="60">60 days</option>
                            <option value="90">90 days</option>
                            <option value="180">6 months</option>
                            <option value="365">1 year</option>
                        </select>
                    </div>
                    <div class="alert alert-warning">
                        <strong>Warning:</strong> This action cannot be undone. All log entries older than the selected period will be permanently deleted.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" onclick="confirmClearLogs()">Clear Logs</button>
                </div>
            </div>
        </div>
    `;

    document.body.appendChild(modal);
    const modalInstance = new coreui.Modal(modal);
    modalInstance.show();

    // Clean up modal after it's hidden
    modal.addEventListener('hidden.coreui.modal', () => {
        document.body.removeChild(modal);
    });
}

function confirmClearLogs() {
    const days = document.getElementById('clearDays').value;

    fetch('{{ route("admin.logs.clear") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ days: parseInt(days) })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
            // Close the modal
            const modal = coreui.Modal.getInstance(document.querySelector('.modal'));
            if (modal) {
                modal.hide();
            }
            // Refresh the page after a short delay
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        } else {
            showAlert('Failed to clear logs. Please try again.', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('An error occurred while clearing logs.', 'error');
    });
}

function showAlert(message, type = 'success') {
    let alertClass, iconName;

    switch(type) {
        case 'success':
            alertClass = 'alert-success';
            iconName = 'check';
            break;
        case 'error':
        case 'danger':
            alertClass = 'alert-danger';
            iconName = 'x';
            break;
        case 'info':
            alertClass = 'alert-info';
            iconName = 'info';
            break;
        case 'warning':
            alertClass = 'alert-warning';
            iconName = 'warning';
            break;
        default:
            alertClass = 'alert-success';
            iconName = 'check';
    }

    const alert = document.createElement('div');
    alert.className = `alert ${alertClass} alert-dismissible fade show shadow position-fixed top-0 end-0 m-3`;
    alert.style.zIndex = '1060';
    alert.innerHTML = `
        <div class="d-flex align-items-center">
            <svg class="icon me-2">
                <use xlink:href="${window.location.origin}/coreui-template/vendors/@coreui/icons/svg/free.svg#cil-${iconName}"></use>
            </svg>
            ${message}
        </div>
        <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
    `;

    document.body.appendChild(alert);

    setTimeout(() => {
        if (alert.parentElement) {
            alert.remove();
        }
    }, 5000);
}
</script>

<!-- Bottom spacing for better visual layout -->
<div class="pb-5"></div>
@endsection

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
    
    /* Improve filter section on mobile */
    .card-body {
        padding: 0.75rem;
    }
    
    /* Make list items more mobile-friendly */
    .list-group-item {
        padding: 0.75rem;
    }
    
    .list-group-item h6 {
        font-size: 0.95rem;
    }
    
    .list-group-item .small {
        font-size: 0.8rem;
    }
    
    /* Prevent text overflow in activity items */
    .list-group-item .d-flex.flex-grow-1 {
        overflow: hidden;
    }
    
    .list-group-item .flex-grow-1 {
        min-width: 0;
    }
    
    .list-group-item h6 {
        word-wrap: break-word;
        word-break: break-word;
        overflow-wrap: break-word;
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
    /* Stack main container vertically on very small screens */
    .list-group-item > .d-flex.justify-content-between {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    /* Keep metadata items horizontal with flex-wrap */
    .list-group-item .flex-wrap.gap-3 {
        gap: 0.5rem !important;
    }
    
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
    
    /* Stack action buttons */
    .list-group-item .d-flex.gap-2 {
        align-self: flex-start;
        width: 100%;
    }
    
    .list-group-item .btn-sm {
        width: 100%;
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