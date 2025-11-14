@if(auth()->check() && auth()->user()->wallet)
<div class="card shadow-sm border-success" id="network-earnings-widget">
    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0">
            <i class="cil-wallet"></i> Network Earnings
        </h6>
        <span class="badge bg-light text-success">Withdrawable</span>
    </div>
    <div class="card-body">
        <div class="row text-center">
            <div class="col-md-4 mb-3 border-end">
                <label class="text-muted small">MLM Balance</label>
                <h4 class="mb-0 text-success" id="mlm-balance-display">
                    {{ currency($wallet->lifetime_mlm_earnings ?? 0) }}
                </h4>
            </div>
            <div class="col-md-4 mb-3 border-end">
                <label class="text-muted small">Unilevel Balance</label>
                <h4 class="mb-0 text-success" id="unilevel-balance-display">
                    {{ currency($wallet->lifetime_unilevel_earnings ?? 0) }}
                </h4>
            </div>
            <div class="col-md-4 mb-3">
                <label class="text-muted small">Purchase Balance</label>
                <h4 class="mb-0" id="purchase-balance-display">
                    {{ currency($wallet->purchase_balance ?? 0) }}
                </h4>
            </div>
        </div>
        <hr>
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <label class="text-muted small">Total Balance</label>
                <h4 class="mb-0" id="total-balance-display">
                    {{ currency(auth()->user()->wallet->total_balance ?? 0) }}
                </h4>
            </div>
            @can('transfer_funds')
            <div>
                <a href="{{ route('wallet.convert') }}" class="btn btn-sm btn-success">
                    <svg class="icon me-1" style="width: 14px; height: 14px;">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-swap-vertical') }}"></use>
                    </svg>
                    Convert Balance
                </a>
            </div>
            @endcan
        </div>
    </div>
</div>
@endif

{{-- Real-time notification listener (requires Laravel Echo + Pusher/WebSocket) --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Check if Laravel Echo is available for real-time updates
    if (typeof window.Echo !== 'undefined') {
        // Listen for commission notifications on private user channel
        window.Echo.private('App.Models.User.{{ auth()->id() }}')
            .notification((notification) => {
                if (notification.type === 'mlm_commission') {
                    // Update MLM balance display
                    const mlmBalanceEl = document.getElementById('mlm-balance-display');
                    const totalBalanceEl = document.getElementById('total-balance-display');

                    if (mlmBalanceEl && totalBalanceEl) {
                        // Parse current balances (remove currency symbol and commas)
                        const currentMLM = parseFloat(mlmBalanceEl.textContent.replace(/[₱$€,]/g, ''));
                        const currentTotal = parseFloat(totalBalanceEl.textContent.replace(/[₱$€,]/g, ''));

                        // Add commission
                        const newMLM = currentMLM + parseFloat(notification.commission);
                        const newTotal = currentTotal + parseFloat(notification.commission);

                        // Update displays with animation (use currency symbol from page)
                        const currencySymbol = '{{ currency_symbol() }}';
                        mlmBalanceEl.textContent = currencySymbol + formatCurrency(newMLM);
                        totalBalanceEl.textContent = currencySymbol + formatCurrency(newTotal);

                        // Add pulse animation
                        mlmBalanceEl.classList.add('pulse-success');
                        totalBalanceEl.classList.add('pulse-success');

                        setTimeout(() => {
                            mlmBalanceEl.classList.remove('pulse-success');
                            totalBalanceEl.classList.remove('pulse-success');
                        }, 2000);
                    }

                    // Show toast notification
                    showMLMToast(notification.message, 'success');
                }
            });
    }
});

/**
 * Format number as currency
 */
function formatCurrency(amount) {
    return amount.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}

/**
 * Show toast notification for MLM commission
 */
function showMLMToast(message, type = 'success') {
    // Create toast element
    const toastHTML = `
        <div class="toast align-items-center text-white bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="cil-check-circle"></i> ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-coreui-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;

    // Get or create toast container
    let toastContainer = document.getElementById('mlm-toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'mlm-toast-container';
        toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
        toastContainer.style.zIndex = '9999';
        document.body.appendChild(toastContainer);
    }

    // Add toast to container
    toastContainer.insertAdjacentHTML('beforeend', toastHTML);
    const toastElement = toastContainer.lastElementChild;

    // Initialize and show toast (using CoreUI/Bootstrap toast API)
    if (typeof coreui !== 'undefined' && coreui.Toast) {
        const toast = new coreui.Toast(toastElement, {
            autohide: true,
            delay: 5000
        });
        toast.show();

        // Remove toast element after it's hidden
        toastElement.addEventListener('hidden.coreui.toast', function () {
            toastElement.remove();
        });
    } else if (typeof bootstrap !== 'undefined' && bootstrap.Toast) {
        const toast = new bootstrap.Toast(toastElement, {
            autohide: true,
            delay: 5000
        });
        toast.show();

        // Remove toast element after it's hidden
        toastElement.addEventListener('hidden.bs.toast', function () {
            toastElement.remove();
        });
    } else {
        // Fallback: auto-remove after 5 seconds
        setTimeout(() => toastElement.remove(), 5000);
    }
}
</script>

{{-- CSS for pulse animation --}}
<style>
@keyframes pulse-success {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); color: #28a745; }
    100% { transform: scale(1); }
}

.pulse-success {
    animation: pulse-success 0.6s ease-in-out 3;
}

#mlm-balance-widget .card-body h3,
#mlm-balance-widget .card-body h4 {
    transition: all 0.3s ease;
}
</style>
