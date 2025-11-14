@extends('layouts.admin')

@section('title', 'System Reports')

@section('content')
<!-- Success/Error Messages -->
<div id="alert-container" class="position-fixed top-0 end-0 p-3" style="z-index: 1060;"></div>

<!-- Page Header -->
<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
            <div>
                <h4 class="card-title mb-0">
                    <svg class="icon me-2">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-chart-pie') }}"></use>
                    </svg>
                    System Reports
                </h4>
                <p class="text-body-secondary mb-0">Generate comprehensive system and business reports</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <button onclick="refreshStats()" class="btn btn-primary">
                    <svg class="icon me-2">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-reload') }}"></use>
                    </svg>
                    Refresh Stats
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

<!-- Report Statistics Overview -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card text-white bg-primary-gradient">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold">{{ number_format($stats['total_users']) }}</div>
                    <div>Total Users</div>
                    <div class="small mt-1">+{{ $stats['new_users_this_month'] }} this month</div>
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
                    <div class="fs-4 fw-semibold">{{ number_format($stats['total_transactions']) }}</div>
                    <div>Total Transactions</div>
                    <div class="small mt-1">{{ $stats['approved_transactions'] }} approved, {{ $stats['pending_transactions'] }} pending</div>
                </div>
                <svg class="icon icon-3xl">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-swap-horizontal') }}"></use>
                </svg>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card text-white bg-warning-gradient">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold">${{ number_format($stats['total_volume'], 2) }}</div>
                    <div>Transaction Volume</div>
                    <div class="small mt-1">+12.5% from last month</div>
                </div>
                <svg class="icon icon-3xl">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-dollar') }}"></use>
                </svg>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card text-white bg-danger-gradient">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold">{{ $stats['rejected_transactions'] }}</div>
                    <div>Rejected Transactions</div>
                    <div class="small mt-1">{{ $stats['total_transactions'] > 0 ? number_format(($stats['rejected_transactions']/$stats['total_transactions'])*100, 1) : 0 }}% rejection rate</div>
                </div>
                <svg class="icon icon-3xl">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-x') }}"></use>
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- Report Generation and Templates -->
<div class="row g-3">
    <div class="col-lg-6">
        <!-- Report Generation Form -->
        <div class="card">
            <div class="card-header">
                <svg class="icon me-2">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-file') }}"></use>
                </svg>
                <strong>Generate New Report</strong>
            </div>
            <div class="card-body">
                <form id="report-form">
                    @csrf
                    <div class="mb-3">
                        <label for="report_type" class="form-label">Report Type</label>
                        <select id="report_type" name="report_type" class="form-select" required>
                            <option value="">Select Report Type</option>
                            <option value="users">User Activity Report</option>
                            <option value="transactions">Transaction Report</option>
                            <option value="financial">Financial Summary Report</option>
                            <option value="security">Security Audit Report</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="date_range" class="form-label">Date Range</label>
                        <select id="date_range" name="date_range" class="form-select" required onchange="toggleCustomDates()">
                            <option value="">Select Date Range</option>
                            <option value="today">Today</option>
                            <option value="week">Last 7 Days</option>
                            <option value="month">Last 30 Days</option>
                            <option value="quarter">Last Quarter</option>
                            <option value="year">Last Year</option>
                            <option value="custom">Custom Date Range</option>
                        </select>
                    </div>

                    <div id="custom-dates" class="d-none">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="date_from" class="form-label">From Date</label>
                                <input type="date" id="date_from" name="date_from" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="date_to" class="form-label">To Date</label>
                                <input type="date" id="date_to" name="date_to" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="format" class="form-label">Export Format</label>
                        <select id="format" name="format" class="form-select" required>
                            <option value="">Select Format</option>
                            <option value="pdf">PDF Document</option>
                            <option value="csv">CSV Spreadsheet</option>
                            <option value="excel">Excel Workbook</option>
                        </select>
                    </div>

                    <button type="submit" id="generate-btn" class="btn btn-primary w-100">
                        <svg class="icon me-2">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-file-plus') }}"></use>
                        </svg>
                        Generate Report
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="row g-3">

            <!-- Quick Report Templates -->
            <div class="col-12 mb-3">
                <div class="card">
                    <div class="card-header">
                        <svg class="icon me-2">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-speedometer') }}"></use>
                        </svg>
                        <strong>Quick Report Templates</strong>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-3">
                            <button onclick="generateQuickReport('daily-summary')" class="btn btn-outline-primary text-start p-3">
                                <div class="d-flex align-items-center">
                                    <svg class="icon icon-lg text-primary me-3">
                                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-chart-line') }}"></use>
                                    </svg>
                                    <div>
                                        <div class="fw-semibold">Daily Summary Report</div>
                                        <small class="text-body-secondary">Today's transactions and user activity</small>
                                    </div>
                                </div>
                            </button>

                            <button onclick="generateQuickReport('weekly-financial')" class="btn btn-outline-success text-start p-3">
                                <div class="d-flex align-items-center">
                                    <svg class="icon icon-lg text-success me-3">
                                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-dollar') }}"></use>
                                    </svg>
                                    <div>
                                        <div class="fw-semibold">Weekly Financial Report</div>
                                        <small class="text-body-secondary">7-day financial performance summary</small>
                                    </div>
                                </div>
                            </button>

                            <button onclick="generateQuickReport('security-audit')" class="btn btn-outline-danger text-start p-3">
                                <div class="d-flex align-items-center">
                                    <svg class="icon icon-lg text-danger me-3">
                                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-shield-alt') }}"></use>
                                    </svg>
                                    <div>
                                        <div class="fw-semibold">Security Audit Report</div>
                                        <small class="text-body-secondary">Monthly security events and threats</small>
                                    </div>
                                </div>
                            </button>

                            <button onclick="generateQuickReport('compliance')" class="btn btn-outline-warning text-start p-3">
                                <div class="d-flex align-items-center">
                                    <svg class="icon icon-lg text-warning me-3">
                                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-check') }}"></use>
                                    </svg>
                                    <div>
                                        <div class="fw-semibold">Compliance Report</div>
                                        <small class="text-body-secondary">Regulatory compliance summary</small>
                                    </div>
                                </div>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Reports -->
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <svg class="icon me-2">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-history') }}"></use>
                        </svg>
                        <strong>Recent Reports</strong>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-semibold">Financial Summary - December 2024</div>
                                    <small class="text-body-secondary">Generated 2 hours ago • 45.2 KB</small>
                                </div>
                                <button class="btn btn-sm btn-outline-primary">
                                    <svg class="icon me-1">
                                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-cloud-download') }}"></use>
                                    </svg>
                                    Download
                                </button>
                            </div>

                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-semibold">User Activity Report - Q4 2024</div>
                                    <small class="text-body-secondary">Generated yesterday • 127.8 KB</small>
                                </div>
                                <button class="btn btn-sm btn-outline-primary">
                                    <svg class="icon me-1">
                                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-cloud-download') }}"></use>
                                    </svg>
                                    Download
                                </button>
                            </div>

                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-semibold">Security Audit - November 2024</div>
                                    <small class="text-body-secondary">Generated 3 days ago • 89.4 KB</small>
                                </div>
                                <button class="btn btn-sm btn-outline-primary">
                                    <svg class="icon me-1">
                                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-cloud-download') }}"></use>
                                    </svg>
                                    Download
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// CSRF Token
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                  document.querySelector('input[name="_token"]')?.value;

function toggleCustomDates() {
    const dateRange = document.getElementById('date_range').value;
    const customDates = document.getElementById('custom-dates');

    if (dateRange === 'custom') {
        customDates.classList.remove('d-none');
        document.getElementById('date_from').required = true;
        document.getElementById('date_to').required = true;
    } else {
        customDates.classList.add('d-none');
        document.getElementById('date_from').required = false;
        document.getElementById('date_to').required = false;
    }
}

function refreshStats() {
    showAlert('Statistics refreshed successfully', 'success');
    window.location.reload();
}

// Handle report generation form
document.getElementById('report-form').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const generateBtn = document.getElementById('generate-btn');
    const originalText = generateBtn.innerHTML;

    // Show loading state
    generateBtn.disabled = true;
    generateBtn.innerHTML = `
        <div class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></div>
        Generating...
    `;

    const data = {
        report_type: formData.get('report_type'),
        date_range: formData.get('date_range'),
        format: formData.get('format'),
        date_from: formData.get('date_from'),
        date_to: formData.get('date_to')
    };

    fetch('/admin/reports/generate', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(`Report generated successfully! File: ${data.report.filename} (${data.report.size})`, 'success');
            // Reset form
            document.getElementById('report-form').reset();
            toggleCustomDates();
        } else {
            showAlert(data.message || 'Failed to generate report', 'error');
        }
    })
    .catch(error => {
        showAlert('Error generating report: ' + error.message, 'error');
    })
    .finally(() => {
        // Reset button state
        generateBtn.disabled = false;
        generateBtn.innerHTML = originalText;
    });
});

function generateQuickReport(template) {
    const templates = {
        'daily-summary': {
            report_type: 'transactions',
            date_range: 'today',
            format: 'pdf'
        },
        'weekly-financial': {
            report_type: 'financial',
            date_range: 'week',
            format: 'excel'
        },
        'security-audit': {
            report_type: 'security',
            date_range: 'month',
            format: 'pdf'
        },
        'compliance': {
            report_type: 'financial',
            date_range: 'quarter',
            format: 'csv'
        }
    };

    const config = templates[template];
    if (!config) return;

    fetch('/admin/reports/generate', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify(config)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(`Quick report generated! File: ${data.report.filename} (${data.report.records} records)`, 'success');
        } else {
            showAlert(data.message || 'Failed to generate quick report', 'error');
        }
    })
    .catch(error => {
        showAlert('Error generating quick report: ' + error.message, 'error');
    });
}

function showAlert(message, type = 'success') {
    const alertContainer = document.getElementById('alert-container');
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';

    const alert = document.createElement('div');
    alert.className = `alert ${alertClass} alert-dismissible fade show shadow`;
    alert.innerHTML = `
        <div class="d-flex align-items-center">
            <svg class="icon me-2">
                <use xlink:href="${window.location.origin}/coreui-template/vendors/@coreui/icons/svg/free.svg#cil-${type === 'success' ? 'check' : 'x'}"></use>
            </svg>
            ${message}
        </div>
        <button type="button" class="btn-close" aria-label="Close" onclick="this.parentElement.remove()"></button>
    `;

    alertContainer.appendChild(alert);

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