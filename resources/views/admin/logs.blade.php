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
            <div class="col-md-4">
                <label for="search" class="form-label">Search Logs</label>
                <input type="text" name="search" id="search" value="{{ $search }}"
                    placeholder="Search by message or IP address..." class="form-control">
            </div>
            <div class="col-md-3">
                <label for="type" class="form-label">Log Type</label>
                <select name="type" id="type" class="form-select">
                    <option value="all" {{ $logType == 'all' ? 'selected' : '' }}>All Types</option>
                    <option value="security" {{ $logType == 'security' ? 'selected' : '' }}>Security</option>
                    <option value="transaction" {{ $logType == 'transaction' ? 'selected' : '' }}>Transaction</option>
                    <option value="mlm_commission" {{ $logType == 'mlm_commission' ? 'selected' : '' }}>MLM Commission</option>
                    <option value="wallet" {{ $logType == 'wallet' ? 'selected' : '' }}>Wallet</option>
                    <option value="order" {{ $logType == 'order' ? 'selected' : '' }}>Order</option>
                    <option value="system" {{ $logType == 'system' ? 'selected' : '' }}>System</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="level" class="form-label">Log Level</label>
                <select name="level" id="level" class="form-select">
                    <option value="all" {{ $level == 'all' ? 'selected' : '' }}>All Levels</option>
                    <option value="DEBUG" {{ $level == 'DEBUG' ? 'selected' : '' }}>Debug</option>
                    <option value="INFO" {{ $level == 'INFO' ? 'selected' : '' }}>Info</option>
                    <option value="WARNING" {{ $level == 'WARNING' ? 'selected' : '' }}>Warning</option>
                    <option value="ERROR" {{ $level == 'ERROR' ? 'selected' : '' }}>Error</option>
                    <option value="CRITICAL" {{ $level == 'CRITICAL' ? 'selected' : '' }}>Critical</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
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
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <svg class="icon me-2">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-list') }}"></use>
                </svg>
                <strong>System Activity Log</strong>
                <small class="text-body-secondary ms-2">
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
                    <div class="list-group-item {{ $log['level'] == 'CRITICAL' ? 'bg-danger-subtle' : ($log['level'] == 'ERROR' ? 'bg-warning-subtle' : '') }}">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="d-flex flex-grow-1">
                                <!-- Log Level Badge -->
                                <div class="me-3">
                                    @php
                                        $levelColors = [
                                            'DEBUG' => 'bg-secondary',
                                            'INFO' => 'bg-primary',
                                            'WARNING' => 'bg-warning',
                                            'ERROR' => 'bg-danger',
                                            'CRITICAL' => 'bg-dark'
                                        ];
                                    @endphp
                                    <span class="badge {{ $levelColors[$log['level']] ?? 'bg-secondary' }}">
                                        {{ $log['level'] }}
                                    </span>
                                </div>

                                <!-- Log Content -->
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center mb-1">
                                        <h6 class="mb-0 me-2">{{ $log['message'] }}</h6>
                                        @php
                                            $typeColors = [
                                                'security' => 'bg-danger',
                                                'transaction' => 'bg-success',
                                                'mlm_commission' => 'bg-primary',
                                                'wallet' => 'bg-warning',
                                                'order' => 'bg-info',
                                                'system' => 'bg-secondary'
                                            ];
                                        @endphp
                                        <span class="badge {{ $typeColors[$log['type']] ?? 'bg-secondary' }} badge-sm">
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
                                    @if(strlen($log['user_agent']) > 50)
                                        <div class="mt-1 small text-body-secondary">
                                            <strong>User Agent:</strong> {{ Str::limit($log['user_agent'], 100) }}
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="d-flex gap-2">
                                <button onclick="viewLogDetails({{ json_encode($log) }})" class="btn btn-sm btn-outline-primary">
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
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-body-secondary small">
                        Showing {{ $activityLogs->firstItem() }} to {{ $activityLogs->lastItem() }} of {{ $activityLogs->total() }} results
                    </div>
                    <div>
                        {{ $activityLogs->appends(request()->query())->links() }}
                    </div>
                </div>
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