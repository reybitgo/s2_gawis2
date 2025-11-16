@extends('layouts.auth')

@section('title', 'System Upgrade in Progress')

@section('content')
<div class="min-vh-100 d-flex flex-row align-items-center py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-7">
                <div class="text-center mb-4">
                    <div class="text-primary mb-3" style="font-size: 5rem; line-height: 1;">
                        <svg class="icon" style="width: 80px; height: 80px;">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-settings') }}"></use>
                        </svg>
                    </div>
                    <h2 class="mb-3">We're Making Things Better!</h2>
                    <p class="text-body-secondary lead mb-0">Our system is currently undergoing scheduled maintenance and improvements.</p>
                </div>

                <div class="alert alert-primary d-flex align-items-start mb-3" role="alert">
                    <svg class="icon me-3 mt-1 flex-shrink-0" style="width: 24px; height: 24px;">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-lightbulb') }}"></use>
                    </svg>
                    <div>
                        <strong>What's happening?</strong><br>
                        We're enhancing our platform with new features and improvements to provide you with a better experience. This brief maintenance ensures everything runs smoothly and securely.
                    </div>
                </div>

                <div class="alert alert-success d-flex align-items-start mb-3" role="alert">
                    <svg class="icon me-3 mt-1 flex-shrink-0" style="width: 24px; height: 24px;">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-shield-alt') }}"></use>
                    </svg>
                    <div>
                        <strong>Your data is completely safe</strong><br>
                        All your account information, wallet balance, and transaction history are secure. Nothing is being deleted or modified during this upgrade.
                    </div>
                </div>

                <div class="alert alert-info d-flex align-items-start mb-4" role="alert">
                    <svg class="icon me-3 mt-1 flex-shrink-0" style="width: 24px; height: 24px;">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-clock') }}"></use>
                    </svg>
                    <div>
                        <strong>When will we be back?</strong><br>
                        We expect to complete this upgrade within a few minutes. The page will automatically check for availability.
                        <div class="mt-2">
                            <span class="badge bg-info-gradient">Auto-retry in <span id="countdown">60</span> seconds</span>
                        </div>
                    </div>
                </div>

                <div class="card bg-light border-0 mb-4">
                    <div class="card-body">
                        <h6 class="card-title mb-3">
                            <svg class="icon me-2">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-star') }}"></use>
                            </svg>
                            What's New?
                        </h6>
                        <ul class="mb-0 small text-body-secondary">
                            <li>Enhanced system performance and reliability</li>
                            <li>Improved security features</li>
                            <li>New functionality coming soon</li>
                            <li>Better user experience optimizations</li>
                        </ul>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button onclick="window.location.reload()" class="btn btn-primary btn-lg">
                        <svg class="icon me-2">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-reload') }}"></use>
                        </svg>
                        Check if We're Back
                    </button>

                    <button onclick="checkStatus()" class="btn btn-outline-primary" id="statusBtn">
                        <svg class="icon me-2">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-task') }}"></use>
                        </svg>
                        <span id="statusText">Check Status</span>
                    </button>
                </div>

                <div class="mt-4 text-center">
                    <small class="text-body-secondary">
                        <svg class="icon me-1">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-calendar') }}"></use>
                        </svg>
                        Maintenance started: <strong>{{ now()->format('g:i A') }}</strong>
                    </small>
                    <div class="mt-2">
                        <small class="text-body-secondary">
                            Need immediate assistance? Our support team is still available via email.
                        </small>
                    </div>
                </div>

                <div class="mt-4 text-center">
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" 
                             role="progressbar" 
                             style="width: 100%">
                        </div>
                    </div>
                    <small class="text-body-secondary mt-2 d-block">
                        <svg class="icon me-1">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-sync') }}"></use>
                        </svg>
                        Upgrade in progress...
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Countdown timer
let countdownSeconds = {{ request()->has('retry') ? request()->retry : 60 }};
const countdownElement = document.getElementById('countdown');

function updateCountdown() {
    if (countdownSeconds > 0) {
        countdownElement.textContent = countdownSeconds;
        countdownSeconds--;
    } else {
        // Auto-reload when countdown reaches 0
        window.location.reload();
    }
}

// Update countdown every second
setInterval(updateCountdown, 1000);

// Check status function
let checkingStatus = false;
function checkStatus() {
    if (checkingStatus) return;
    
    checkingStatus = true;
    const statusBtn = document.getElementById('statusBtn');
    const statusText = document.getElementById('statusText');
    
    statusBtn.disabled = true;
    statusText.textContent = 'Checking...';
    
    // Try to fetch a lightweight endpoint
    fetch('/', { method: 'HEAD' })
        .then(response => {
            if (response.ok || response.status !== 503) {
                // Site is back up
                statusText.textContent = 'Site is back! Redirecting...';
                setTimeout(() => {
                    window.location.href = '/';
                }, 500);
            } else {
                // Still in maintenance
                statusText.textContent = 'Still upgrading...';
                setTimeout(() => {
                    statusText.textContent = 'Check Status';
                    statusBtn.disabled = false;
                    checkingStatus = false;
                }, 2000);
            }
        })
        .catch(() => {
            statusText.textContent = 'Still upgrading...';
            setTimeout(() => {
                statusText.textContent = 'Check Status';
                statusBtn.disabled = false;
                checkingStatus = false;
            }, 2000);
        });
}

// Keyboard shortcut: Press 'R' to reload
document.addEventListener('keypress', function(e) {
    if (e.key === 'r' || e.key === 'R') {
        window.location.reload();
    }
});

// Favicon animation (optional - shows activity)
let faviconToggle = false;
setInterval(() => {
    faviconToggle = !faviconToggle;
    const link = document.querySelector("link[rel*='icon']") || document.createElement('link');
    link.type = 'image/x-icon';
    link.rel = 'shortcut icon';
    // Alternates to show activity (optional, can be removed if not desired)
}, 1000);
</script>
@endpush

@push('styles')
<style>
/* Smooth animations */
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
}

.alert {
    animation: fadeInUp 0.5s ease-out;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.progress-bar-animated {
    animation: progress-bar-stripes 1s linear infinite, pulse 2s ease-in-out infinite;
}

/* Smooth icon rotation for settings icon */
.float-start svg {
    animation: rotate 3s linear infinite;
}

@keyframes rotate {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

/* Badge pulse effect */
.badge {
    animation: badgePulse 2s ease-in-out infinite;
}

@keyframes badgePulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}
</style>
@endpush
@endsection
