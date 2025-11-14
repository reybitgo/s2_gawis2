@extends('layouts.admin')

@section('content')
<div class="container-fluid pb-5">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2>My Referral Link</h2>
            <p class="text-muted">Share your referral link and earn commissions from your network</p>
        </div>
    </div>

    <!-- Referral Link Card -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <strong>Share Your Referral Link</strong>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <label class="form-label">Your Unique Referral Code</label>
                    <div class="input-group mb-3">
                        <input type="text"
                               class="form-control form-control-lg"
                               id="referral-code"
                               value="{{ $user->referral_code }}"
                               readonly>
                        <button class="btn btn-outline-secondary"
                                type="button"
                                onclick="copyToClipboard('referral-code', 'Referral code copied!')">
                            <i class="cil-copy"></i> Copy Code
                        </button>
                    </div>

                    <label class="form-label">Your Referral Link</label>
                    <div class="input-group mb-3">
                        <input type="text"
                               class="form-control"
                               id="referral-link"
                               value="{{ $referralLink }}"
                               readonly>
                        <button class="btn btn-outline-secondary"
                                type="button"
                                onclick="copyToClipboard('referral-link', 'Referral link copied!')">
                            <i class="cil-copy"></i> Copy Link
                        </button>
                    </div>
                </div>
                <div class="col-md-4 d-flex flex-column align-items-center">
                    <label class="form-label">QR Code</label>
                    <div id="qr-code" class="mb-2 d-flex justify-content-center"></div>
                    <small class="text-muted text-center">Scan to register with your referral</small>
                </div>
            </div>

            <!-- Social Share Buttons -->
            <div class="mt-3">
                <label class="form-label">Share via Social Media</label>
                <div class="btn-group" role="group">
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($referralLink) }}"
                       target="_blank"
                       class="btn btn-primary">
                        <i class="cib-facebook"></i> Facebook
                    </a>
                    <a href="https://wa.me/?text={{ urlencode('Join using my referral: ' . $referralLink) }}"
                       target="_blank"
                       class="btn btn-success">
                        <i class="cib-whatsapp"></i> WhatsApp
                    </a>
                    <a href="https://www.messenger.com/t/?link={{ urlencode($referralLink) }}"
                       target="_blank"
                       class="btn btn-info text-white">
                        <i class="cib-messenger"></i> Messenger
                    </a>
                    <a href="https://twitter.com/intent/tweet?text={{ urlencode('Join using my referral: ' . $referralLink) }}"
                       target="_blank"
                       class="btn btn-dark">
                        <i class="cib-twitter"></i> Twitter
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Referral Statistics -->
    <div class="row">
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-primary mb-0">{{ $totalClicks }}</h3>
                    <p class="text-muted mb-0">Total Link Clicks</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-success mb-0">{{ $directReferrals }}</h3>
                    <p class="text-muted mb-0">Direct Referrals</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-info mb-0">{{ number_format(($totalClicks > 0 ? ($directReferrals / $totalClicks) * 100 : 0), 1) }}%</h3>
                    <p class="text-muted mb-0">Conversion Rate</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Toast Container for Notifications -->
<div id="toast-container" aria-live="polite" aria-atomic="true" style="position: fixed; top: 20px; right: 20px; z-index: 9999;"></div>

<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script>
// Generate QR Code
document.addEventListener('DOMContentLoaded', function() {
    new QRCode(document.getElementById("qr-code"), {
        text: "{{ $referralLink }}",
        width: 150,
        height: 150
    });
});

// Copy to clipboard function
function copyToClipboard(elementId, message) {
    const input = document.getElementById(elementId);
    input.select();
    input.setSelectionRange(0, 99999); // For mobile devices

    try {
        document.execCommand('copy');
        showToast(message || 'Copied to clipboard!', 'success');
    } catch (err) {
        showToast('Failed to copy', 'danger');
    }

    // Deselect
    window.getSelection().removeAllRanges();
}

// Show toast notification
function showToast(message, type = 'success') {
    const bgColor = type === 'success' ? 'bg-success' : 'bg-danger';
    const toast = `
        <div class="toast align-items-center text-white ${bgColor} border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-coreui-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;

    const container = document.getElementById('toast-container');
    container.insertAdjacentHTML('beforeend', toast);

    const toastElement = container.querySelector('.toast:last-child');
    const bsToast = new coreui.Toast(toastElement, {
        autohide: true,
        delay: 3000
    });
    bsToast.show();

    // Remove toast element after it's hidden
    toastElement.addEventListener('hidden.coreui.toast', function() {
        toastElement.remove();
    });
}
</script>
@endsection
