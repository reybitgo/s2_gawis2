@extends('layouts.admin')

@section('title', 'Checkout')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                <div>
                    <h4 class="card-title mb-0">
                        <svg class="icon me-2">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-credit-card') }}"></use>
                        </svg>
                        Checkout
                    </h4>
                    <p class="text-body-secondary mb-0">Review your order and complete your purchase</p>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('cart.index') }}" class="btn btn-secondary">
                        <svg class="icon me-2">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-arrow-left') }}"></use>
                        </svg>
                        Back to Cart
                    </a>
                </div>
            </div>
        </div>
    </div>

    <form id="checkout-form" action="{{ route('checkout.process') }}" method="POST">
        @csrf

        <div class="row">
        <!-- Order Review -->
        <div class="col-lg-8">
            <!-- Order Items -->
            <div class="card mb-4 order-items">
                <div class="card-header">
                    <h5 class="mb-0">
                        <svg class="icon me-2">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-basket') }}"></use>
                        </svg>
                        Order Items ({{ $cartSummary['item_count'] }} items)
                    </h5>
                </div>
                <div class="card-body p-0">
                    @foreach($cartSummary['items'] as $item)
                        <div class="border-bottom p-3">
                            <div class="d-flex align-items-center">
                                <img src="{{ $item['image_url'] }}" alt="{{ $item['name'] }}" class="rounded me-3" style="width: 60px; height: 60px; object-fit: cover;">
                                <div class="grow">
                                    <h6 class="mb-1 item-name">{{ $item['name'] }}</h6>
                                    @if(isset($item['short_description']) && $item['short_description'])
                                        <p class="text-muted small mb-1">{{ Str::limit($item['short_description'], 80) }}</p>
                                    @endif
                                    <div class="d-flex flex-wrap text-sm">
                                        <span class="me-3 mb-1">Quantity: <strong>{{ $item['quantity'] }}</strong></span>
                                        <span class="me-3 mb-1">Unit Price: <strong>{{ currency($item['price']) }}</strong></span>
                                        <span class="text-primary mb-1">Points: <strong>{{ number_format(($item['points_awarded'] ?? $item['points'] ?? 0) * $item['quantity']) }}</strong></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Delivery Method -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <svg class="icon me-2">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-truck') }}"></use>
                        </svg>
                        Delivery Method
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="form-check border rounded p-3 h-100 delivery-option" data-method="office_pickup">
                                <input class="form-check-input" type="radio" name="delivery_method"
                                       id="office_pickup" value="office_pickup" checked>
                                <label class="form-check-label w-100" for="office_pickup">
                                    <div class="d-flex align-items-start">
                                        <div class="me-3">
                                            <svg class="icon icon-xl text-primary">
                                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-location-pin') }}"></use>
                                            </svg>
                                        </div>
                                        <div class="grow">
                                            <h6 class="mb-2">Office Pickup <span class="badge bg-success ms-2">Recommended</span></h6>
                                            <p class="text-muted small mb-2">Collect your order from our office or arranged meetup point</p>
                                            <div class="text-success small">
                                                <svg class="icon me-1">
                                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-check') }}"></use>
                                                </svg>
                                                No delivery charges
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check border rounded p-3 h-100 delivery-option" data-method="home_delivery">
                                <input class="form-check-input" type="radio" name="delivery_method"
                                       id="home_delivery" value="home_delivery">
                                <label class="form-check-label w-100" for="home_delivery">
                                    <div class="d-flex align-items-start">
                                        <div class="me-3">
                                            <svg class="icon icon-xl text-info">
                                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-home') }}"></use>
                                            </svg>
                                        </div>
                                        <div class="grow">
                                            <h6 class="mb-2">Home Delivery</h6>
                                            <p class="text-muted small mb-2">Standard delivery to your address</p>
                                            <div class="text-info small">
                                                <svg class="icon me-1">
                                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-check') }}"></use>
                                                </svg>
                                                Package tracking included
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Delivery Address Form (appears when Home Delivery is selected) -->
                    <div id="delivery-address-form" class="card mb-3" style="display: none;">
                        <!-- Address Source Notice -->
                        <div class="alert alert-info mb-3">
                            <div class="d-flex align-items-start">
                                <svg class="icon me-2 mt-1">
                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-info') }}"></use>
                                </svg>
                                <div>
                                    <strong>Pre-filled from your profile</strong><br>
                                    <small>Your delivery information is automatically loaded from your profile. You can update it here for this order or <a href="{{ route('profile.show') }}" target="_blank">edit your profile</a> to change the default address.</small>
                                </div>
                            </div>
                        </div>
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <svg class="icon me-2">
                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-location-pin') }}"></use>
                                </svg>
                                Delivery Address
                                <span class="text-danger">*</span>
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <!-- Full Name -->
                                <div class="col-md-6">
                                    <label for="delivery_full_name" class="form-label">
                                        Full Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control delivery-required" id="delivery_full_name"
                                           name="delivery_full_name" placeholder="Enter full name"
                                           value="{{ old('delivery_full_name', auth()->user()->fullname) }}">
                                    <div class="invalid-feedback"></div>
                                </div>

                                <!-- Phone Number -->
                                <div class="col-md-6">
                                    <label for="delivery_phone" class="form-label">
                                        Phone Number <span class="text-danger">*</span>
                                    </label>
                                    <input type="tel" class="form-control delivery-required" id="delivery_phone"
                                           name="delivery_phone" placeholder="+1 (555) 123-4567"
                                           value="{{ old('delivery_phone', auth()->user()->phone) }}">
                                    <div class="invalid-feedback"></div>
                                    <div class="form-text">Required for delivery coordination</div>
                                </div>

                                <!-- Street Address -->
                                <div class="col-12">
                                    <label for="delivery_address" class="form-label">
                                        Street Address <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control delivery-required" id="delivery_address"
                                           name="delivery_address" placeholder="1234 Main Street"
                                           value="{{ old('delivery_address', auth()->user()->address) }}">
                                    <div class="invalid-feedback"></div>
                                </div>

                                <!-- Address Line 2 -->
                                <div class="col-12">
                                    <label for="delivery_address_2" class="form-label">
                                        Address Line 2 <span class="text-muted">(Optional)</span>
                                    </label>
                                    <input type="text" class="form-control" id="delivery_address_2"
                                           name="delivery_address_2" placeholder="Apartment, suite, unit, floor, etc."
                                           value="{{ old('delivery_address_2', auth()->user()->address_2) }}">
                                </div>

                                <!-- City -->
                                <div class="col-md-6">
                                    <label for="delivery_city" class="form-label">
                                        City <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control delivery-required" id="delivery_city"
                                           name="delivery_city" placeholder="Enter city"
                                           value="{{ old('delivery_city', auth()->user()->city) }}">
                                    <div class="invalid-feedback"></div>
                                </div>

                                <!-- State/Province -->
                                <div class="col-md-3">
                                    <label for="delivery_state" class="form-label">
                                        State/Province <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control delivery-required" id="delivery_state"
                                           name="delivery_state" placeholder="State"
                                           value="{{ old('delivery_state', auth()->user()->state) }}">
                                    <div class="invalid-feedback"></div>
                                </div>

                                <!-- ZIP/Postal Code -->
                                <div class="col-md-3">
                                    <label for="delivery_zip" class="form-label">
                                        ZIP/Postal Code <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control delivery-required" id="delivery_zip"
                                           name="delivery_zip" placeholder="12345"
                                           value="{{ old('delivery_zip', auth()->user()->zip) }}">
                                    <div class="invalid-feedback"></div>
                                </div>

                                <!-- Special Instructions -->
                                <div class="col-12">
                                    <label for="delivery_instructions" class="form-label">
                                        Delivery Instructions <span class="text-muted">(Optional)</span>
                                    </label>
                                    <textarea class="form-control" id="delivery_instructions" name="delivery_instructions"
                                              rows="3" placeholder="Special delivery instructions (e.g., gate code, building entrance, safe place to leave package)">{{ old('delivery_instructions', auth()->user()->delivery_instructions) }}</textarea>
                                    <div class="form-text">Help our delivery team find you easily</div>
                                </div>

                                <!-- Preferred Delivery Time -->
                                <div class="col-12">
                                    <label class="form-label">Preferred Delivery Time <span class="text-muted">(Optional)</span></label>
                                    <div class="row g-2">
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="delivery_time_preference"
                                                       id="anytime" value="anytime"
                                                       {{ old('delivery_time_preference', auth()->user()->delivery_time_preference) === 'anytime' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="anytime">
                                                    Anytime (9 AM - 6 PM)
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="delivery_time_preference"
                                                       id="morning" value="morning"
                                                       {{ old('delivery_time_preference', auth()->user()->delivery_time_preference) === 'morning' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="morning">
                                                    Morning (9 AM - 12 PM)
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="delivery_time_preference"
                                                       id="afternoon" value="afternoon"
                                                       {{ old('delivery_time_preference', auth()->user()->delivery_time_preference) === 'afternoon' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="afternoon">
                                                    Afternoon (12 PM - 6 PM)
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="delivery_time_preference"
                                                       id="weekend" value="weekend"
                                                       {{ old('delivery_time_preference', auth()->user()->delivery_time_preference) === 'weekend' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="weekend">
                                                    Weekend preferred
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Address Validation Status -->
                            <div id="address-validation-status" class="mt-3" style="display: none;">
                                <div class="alert alert-success">
                                    <svg class="icon me-2">
                                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-check-circle') }}"></use>
                                    </svg>
                                    Address information is complete and valid
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pickup Information -->
                    <div id="pickup-info" class="alert alert-info delivery-info">
                        <h6 class="alert-heading">
                            <svg class="icon me-2">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-info') }}"></use>
                            </svg>
                            Pickup Information
                        </h6>
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Location:</strong><br>
                                {{ $officeAddress }}
                            </div>
                            <div class="col-md-6">
                                <strong>Hours:</strong><br>
                                Monday - Friday: 9:00 AM - 5:00 PM<br>
                                Saturday: 9:00 AM - 2:00 PM<br>
                                Sunday: Closed
                            </div>
                        </div>
                        <div class="mt-2">
                            <strong>Contact:</strong> +1 (555) 123-4567<br>
                            <strong>Note:</strong> Please bring a valid ID when collecting your order.
                        </div>
                    </div>

                    <!-- Delivery Information -->
                    <div id="delivery-info" class="alert alert-info delivery-info" style="display: none;">
                        <h6 class="alert-heading">
                            <svg class="icon me-2">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-info') }}"></use>
                            </svg>
                            Delivery Information
                        </h6>
                        <p class="mb-1"><strong>Delivery Time:</strong> 3-5 business days</p>
                        <p class="mb-1"><strong>Delivery Hours:</strong> Monday - Friday, 9:00 AM - 6:00 PM</p>
                        <p class="mb-1"><strong>Tracking:</strong> You'll receive tracking information once your order is shipped</p>
                        <p class="mb-0"><strong>Note:</strong> Delivery charges may apply depending on your location.</p>
                    </div>
                </div>
            </div>

            <!-- Payment Method -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <svg class="icon me-2">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-wallet') }}"></use>
                        </svg>
                        Payment Method
                    </h5>
                </div>
                <div class="card-body">
                        <!-- Wallet Payment Option -->
                        <div class="form-check border rounded p-3 mb-3 {{ $walletSummary['can_pay'] ? 'border-success' : 'border-danger' }}">
                            <input class="form-check-input @error('payment_method') is-invalid @enderror"
                                   type="radio"
                                   name="payment_method"
                                   id="wallet_payment"
                                   value="wallet"
                                   {{ $walletSummary['can_pay'] ? 'checked' : 'disabled' }}
                                   {{ old('payment_method') === 'wallet' ? 'checked' : '' }}>
                            <label class="form-check-label w-100" for="wallet_payment">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>Pay with E-Wallet</strong>
                                        <div class="text-muted small">Use your wallet balance to complete this purchase</div>
                                    </div>
                                    <div class="text-end">
                                        <div class="badge {{ $walletSummary['wallet_active'] ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $walletSummary['wallet_active'] ? 'Active' : 'Inactive' }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Wallet Balance Information -->
                                <div class="mt-2">
                                    <div class="row text-sm">
                                        <div class="col-md-6">
                                            <span class="text-muted">Current Balance:</span>
                                            <strong class="{{ $walletSummary['can_pay'] ? 'text-success' : 'text-danger' }}">
                                                {{ $walletSummary['formatted_balance'] }}
                                            </strong>
                                        </div>
                                        <div class="col-md-6">
                                            <span class="text-muted">Order Total:</span>
                                            <strong>{{ $walletSummary['formatted_order_amount'] }}</strong>
                                        </div>
                                    </div>
                                    @if($walletSummary['can_pay'])
                                        <div class="mt-2">
                                            <span class="text-muted">Remaining Balance:</span>
                                            <strong class="text-info">{{ $walletSummary['formatted_remaining_balance'] }}</strong>
                                        </div>
                                    @else
                                        <div class="mt-2">
                                            <div class="alert alert-danger small mb-0">
                                                <svg class="icon me-1">
                                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-warning') }}"></use>
                                                </svg>
                                                {{ $walletSummary['validation_message'] }}
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </label>
                            @error('payment_method')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        @if(!$walletSummary['can_pay'])
                            <div class="alert alert-warning d-flex align-items-start">
                                <svg class="icon me-2 shrink-0">
                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-wallet') }}"></use>
                                </svg>
                                <div>
                                    <strong>Insufficient Balance:</strong> Your wallet balance is too low to complete this purchase.
                                    <a href="{{ route('wallet.deposit') }}" class="btn btn-sm btn-warning ms-2">
                                        <svg class="icon me-1">
                                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-plus') }}"></use>
                                        </svg>
                                        Deposit Funds
                                    </a>
                                </div>
                            </div>
                        @endif

                        <!-- Customer Notes -->
                        <div class="mb-3">
                            <label for="customer_notes" class="form-label">Special Instructions or Notes (Optional)</label>
                            <textarea class="form-control @error('customer_notes') is-invalid @enderror"
                                      id="customer_notes"
                                      name="customer_notes"
                                      rows="3"
                                      placeholder="Any special instructions or notes for your order...">{{ old('customer_notes') }}</textarea>
                            @error('customer_notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Maximum 1000 characters</div>
                        </div>

                        <!-- Terms and Conditions -->
                        <div class="form-check mb-3">
                            <input class="form-check-input @error('terms_accepted') is-invalid @enderror"
                                   type="checkbox"
                                   id="terms_accepted"
                                   name="terms_accepted"
                                   value="1"
                                   {{ old('terms_accepted') ? 'checked' : '' }}>
                            <label class="form-check-label" for="terms_accepted">
                                I agree to the <a href="#" data-coreui-toggle="modal" data-coreui-target="#termsModal">Terms and Conditions</a> and <a href="#" data-coreui-toggle="modal" data-coreui-target="#privacyModal">Privacy Policy</a>
                            </label>
                            @error('terms_accepted')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                </div>
            </div>
        </div>

        <!-- Order Summary -->
        <div class="col-lg-4">
            <div class="card sticky-top sticky-order-summary">
                <div class="card-header">
                    <h5 class="mb-0">
                        <svg class="icon me-2">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-calculator') }}"></use>
                        </svg>
                        Order Summary
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal ({{ $cartSummary['item_count'] }} items)</span>
                        <span>{{ currency($cartSummary['subtotal']) }}</span>
                    </div>
                    @if($cartSummary['show_tax'])
                    <div class="d-flex justify-content-between mb-2">
                        <span>Tax ({{ number_format($cartSummary['tax_rate'] * 100, 1) }}%)</span>
                        <span>{{ currency($cartSummary['tax_amount']) }}</span>
                    </div>
                    @endif
                    <hr>
                    <div class="d-flex justify-content-between mb-3">
                        <strong>Total</strong>
                        <strong class="text-primary h5">{{ currency($cartSummary['total']) }}</strong>
                    </div>

                    <!-- Points Summary -->
                    <div class="alert alert-info">
                        <svg class="icon me-2">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-star') }}"></use>
                        </svg>
                        You will earn <strong>{{ number_format($cartSummary['total_points']) }} points</strong> from this order!
                    </div>

                    <!-- Wallet Payment Summary -->
                    @if($walletSummary['can_pay'])
                        <div class="alert alert-success">
                            <svg class="icon me-2">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-wallet') }}"></use>
                            </svg>
                            <strong>Payment Method:</strong> E-Wallet<br>
                            <small>Your order will be paid immediately using your wallet balance.</small>
                        </div>
                    @else
                        <div class="alert alert-warning">
                            <svg class="icon me-2">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-warning') }}"></use>
                            </svg>
                            <strong>Payment Required:</strong> Please ensure you have sufficient wallet balance to complete this purchase.
                        </div>
                    @endif

                    <!-- Place Order Button -->
                    <div class="d-grid">
                        <button type="submit"
                                form="checkout-form"
                                class="btn btn-primary btn-lg"
                                id="place-order-btn"
                                {{ !$walletSummary['can_pay'] ? 'disabled' : '' }}>
                            <svg class="icon me-2">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-credit-card') }}"></use>
                            </svg>
                            {{ $walletSummary['can_pay'] ? 'Pay Now' : 'Insufficient Balance' }}
                        </button>
                    </div>

                    <div class="text-center mt-3">
                        <small class="text-muted">
                            <svg class="icon me-1">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-shield-alt') }}"></use>
                            </svg>
                            Your order information is secure and protected
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </form>
</div>

<!-- Bottom spacing for better visual layout -->
<div class="pb-5"></div>

@push('styles')
<style>
.sticky-order-summary {
    z-index: 100 !important;
}
/* Prevent Order Items card content from overflowing (fix bleed) */
.order-items .card-body .d-flex.align-items-center {
    gap: 0.75rem;
}
.order-items .card-body .flex-grow-1 {
    min-width: 0; /* allow flex child to shrink and enable ellipsis */
}
.order-items .item-name {
    display: block;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.order-items img {
    flex-shrink: 0;
    width: 60px;
    height: 60px;
    object-fit: cover;
}
/* Responsive: stack item content on small screens */
@media (max-width: 576px) {
    .order-items .card-body .d-flex.align-items-center {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
    }
    .order-items img {
        width: 56px;
        height: 56px;
    }
    .order-items .text-end {
        width: 100%;
        text-align: right;
        align-self: stretch;
        margin-top: 0.25rem;
    }
    .order-items .item-name {
        white-space: normal;
        overflow: visible;
        text-overflow: unset;
    }
    .order-items .card-body .d-flex.align-items-center .d-flex.text-sm {
        flex-wrap: wrap;
        gap: 0.5rem;
    }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('checkout-form');
    const submitBtn = document.getElementById('place-order-btn');
    const termsCheckbox = document.getElementById('terms_accepted');
    const paymentMethodRadio = document.getElementById('wallet_payment');
    const canPay = {{ $walletSummary['can_pay'] ? 'true' : 'false' }};

    // Delivery method switching
    const deliveryMethodRadios = document.querySelectorAll('input[name="delivery_method"]');
    const pickupInfo = document.getElementById('pickup-info');
    const deliveryInfo = document.getElementById('delivery-info');
    const deliveryAddressForm = document.getElementById('delivery-address-form');
    const deliveryOptions = document.querySelectorAll('.delivery-option');
    const deliveryRequiredFields = document.querySelectorAll('.delivery-required');
    const addressValidationStatus = document.getElementById('address-validation-status');

    function updateDeliveryInfo() {
        const selectedMethod = document.querySelector('input[name="delivery_method"]:checked').value;

        // Update info sections
        if (selectedMethod === 'office_pickup') {
            pickupInfo.style.display = 'block';
            deliveryInfo.style.display = 'none';
            deliveryAddressForm.style.display = 'none';

            // Clear delivery form validation for office pickup
            clearDeliveryValidation();
        } else {
            pickupInfo.style.display = 'none';
            deliveryInfo.style.display = 'block';
            deliveryAddressForm.style.display = 'block';

            // Animate form appearance
            deliveryAddressForm.style.opacity = '0';
            deliveryAddressForm.style.transform = 'translateY(-10px)';
            setTimeout(() => {
                deliveryAddressForm.style.transition = 'all 0.3s ease';
                deliveryAddressForm.style.opacity = '1';
                deliveryAddressForm.style.transform = 'translateY(0)';
            }, 100);
        }

        // Update option styling
        deliveryOptions.forEach(option => {
            if (option.dataset.method === selectedMethod) {
                option.classList.add('border-primary');
                option.classList.remove('border-secondary');
            } else {
                option.classList.remove('border-primary');
                option.classList.add('border-secondary');
            }
        });

        // Update submit button validation
        updateSubmitButton();
    }

    // Clear delivery form validation
    function clearDeliveryValidation() {
        deliveryRequiredFields.forEach(field => {
            field.classList.remove('is-invalid', 'is-valid');
            const feedback = field.nextElementSibling;
            if (feedback && feedback.classList.contains('invalid-feedback')) {
                feedback.textContent = '';
            }
        });
        addressValidationStatus.style.display = 'none';
    }

    // Validate delivery address fields
    function validateDeliveryAddress() {
        const selectedMethod = document.querySelector('input[name="delivery_method"]:checked').value;

        // Only validate if home delivery is selected
        if (selectedMethod !== 'home_delivery') {
            return true;
        }

        let isValid = true;
        let allFieldsFilled = true;

        deliveryRequiredFields.forEach(field => {
            const value = field.value.trim();
            const feedback = field.nextElementSibling;

            // Clear previous validation
            field.classList.remove('is-invalid', 'is-valid');
            if (feedback && feedback.classList.contains('invalid-feedback')) {
                feedback.textContent = '';
            }

            // Validate required fields
            if (!value) {
                allFieldsFilled = false;
                if (field === document.activeElement || field.classList.contains('was-validated')) {
                    field.classList.add('is-invalid');
                    if (feedback && feedback.classList.contains('invalid-feedback')) {
                        feedback.textContent = 'This field is required';
                    }
                    isValid = false;
                }
            } else {
                // Field-specific validation
                if (field.id === 'delivery_phone' && !validatePhoneNumber(value)) {
                    field.classList.add('is-invalid');
                    if (feedback && feedback.classList.contains('invalid-feedback')) {
                        feedback.textContent = 'Please enter a valid phone number';
                    }
                    isValid = false;
                } else if (field.id === 'delivery_zip' && !validateZipCode(value)) {
                    field.classList.add('is-invalid');
                    if (feedback && feedback.classList.contains('invalid-feedback')) {
                        feedback.textContent = 'Please enter a valid ZIP/postal code';
                    }
                    isValid = false;
                } else {
                    field.classList.add('is-valid');
                }
            }
        });

        // Show validation status
        if (allFieldsFilled && isValid) {
            addressValidationStatus.style.display = 'block';
        } else {
            addressValidationStatus.style.display = 'none';
        }

        return isValid && allFieldsFilled;
    }

    // Phone number validation
    function validatePhoneNumber(phone) {
        const phoneRegex = /^[\+]?[1-9][\d]{0,15}$/;
        const cleanPhone = phone.replace(/[\s\-\(\)\.]/g, '');
        return phoneRegex.test(cleanPhone) && cleanPhone.length >= 10;
    }

    // ZIP code validation
    function validateZipCode(zip) {
        const zipRegex = /^[A-Za-z0-9\s\-]{3,10}$/;
        return zipRegex.test(zip);
    }

    // Initialize delivery method display
    updateDeliveryInfo();

    // Listen for delivery method changes
    deliveryMethodRadios.forEach(radio => {
        radio.addEventListener('change', updateDeliveryInfo);
    });

    // Enable/disable submit button based on validation
    function updateSubmitButton() {
        const termsAccepted = termsCheckbox.checked;
        const paymentSelected = paymentMethodRadio && paymentMethodRadio.checked;
        const hasValidPayment = canPay && paymentSelected;
        const deliveryAddressValid = validateDeliveryAddress();

        const allValid = termsAccepted && hasValidPayment && deliveryAddressValid;

        submitBtn.disabled = !allValid;

        // Update button text to provide feedback
        const buttonText = submitBtn.querySelector('.btn-text') || submitBtn.childNodes[2];
        if (!termsAccepted) {
            submitBtn.title = 'Please accept the terms and conditions';
        } else if (!hasValidPayment) {
            submitBtn.title = 'Payment method required';
        } else if (!deliveryAddressValid) {
            submitBtn.title = 'Please complete delivery address information';
        } else {
            submitBtn.title = 'Complete your order';
        }
    }

    // Initial check
    updateSubmitButton();

    // Listen for checkbox changes
    if (termsCheckbox) {
        termsCheckbox.addEventListener('change', updateSubmitButton);
    }

    if (paymentMethodRadio) {
        paymentMethodRadio.addEventListener('change', updateSubmitButton);
    }

    // Add event listeners for delivery address fields
    deliveryRequiredFields.forEach(field => {
        // Real-time validation on input
        field.addEventListener('input', function() {
            const selectedMethod = document.querySelector('input[name="delivery_method"]:checked').value;
            if (selectedMethod === 'home_delivery') {
                // Debounce validation to avoid excessive calls
                clearTimeout(field.validationTimeout);
                field.validationTimeout = setTimeout(() => {
                    updateSubmitButton();
                }, 300);
            }
        });

        // Validate on blur
        field.addEventListener('blur', function() {
            const selectedMethod = document.querySelector('input[name="delivery_method"]:checked').value;
            if (selectedMethod === 'home_delivery') {
                field.classList.add('was-validated');
                updateSubmitButton();
            }
        });
    });

    // Handle form submission
    form.addEventListener('submit', function(e) {
        if (!termsCheckbox.checked) {
            e.preventDefault();
            alert('Please accept the terms and conditions to continue.');
            return;
        }

        if (!paymentMethodRadio || !paymentMethodRadio.checked) {
            e.preventDefault();
            alert('Please select a payment method to continue.');
            return;
        }

        if (!canPay) {
            e.preventDefault();
            alert('Insufficient wallet balance. Please add funds to your wallet.');
            return;
        }

        // Show loading state
        submitBtn.disabled = true;
        submitBtn.innerHTML = `
            <span class="spinner-border spinner-border-sm me-2" role="status">
                <span class="visually-hidden">Loading...</span>
            </span>
            Processing Payment...
        `;
    });
});
</script>
@endpush
@include('legal.terms-of-service')
@include('legal.privacy-policy')
@endsection