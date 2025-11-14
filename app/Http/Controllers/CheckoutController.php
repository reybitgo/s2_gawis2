<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Services\CartService;
use App\Services\WalletPaymentService;
use App\Services\InputSanitizationService;
use App\Services\FraudDetectionService;
use App\Jobs\ProcessMLMCommissions;
use App\Jobs\ProcessUnilevelBonusesJob;
use App\Models\Product;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CheckoutController extends Controller
{
    protected CartService $cartService;
    protected WalletPaymentService $walletPaymentService;
    protected InputSanitizationService $sanitizationService;
    protected FraudDetectionService $fraudDetectionService;

    public function __construct(
        CartService $cartService,
        WalletPaymentService $walletPaymentService,
        InputSanitizationService $sanitizationService,
        FraudDetectionService $fraudDetectionService
    ) {
        $this->cartService = $cartService;
        $this->walletPaymentService = $walletPaymentService;
        $this->sanitizationService = $sanitizationService;
        $this->fraudDetectionService = $fraudDetectionService;
    }

    /**
     * Show the checkout page
     */
    public function index()
    {
        $cartSummary = $this->cartService->getSummary();

        // Redirect if cart is empty
        if ($cartSummary['is_empty']) {
            return redirect()->route('packages.index')
                ->with('error', 'Your cart is empty. Please add some packages before checkout.');
        }

        // Validate cart items are still available
        $validationErrors = $this->cartService->validateCart();
        if (!empty($validationErrors)) {
            return redirect()->route('cart.index')
                ->with('error', 'Some items in your cart are no longer available. Please review your cart.');
        }

        // Get wallet payment summary
        $walletSummary = $this->walletPaymentService->getPaymentSummary(
            Auth::user(),
            $cartSummary['total']
        );

        // Get admin's delivery address for office pickup
        $adminUser = \App\Models\User::role('admin')->first();
        $officeAddress = null;
        if ($adminUser) {
            $addressParts = array_filter([
                $adminUser->address,
                $adminUser->address_2,
                $adminUser->city,
                $adminUser->state,
                $adminUser->zip,
            ]);
            $officeAddress = !empty($addressParts) ? implode(', ', $addressParts) : 'Main Office';
        } else {
            $officeAddress = 'Main Office';
        }

        return view('checkout.index', compact('cartSummary', 'walletSummary', 'officeAddress'));
    }

    /**
     * Process the checkout and create an order
     */
    public function process(Request $request)
    {
        // Base validation rules
        $validationRules = [
            'delivery_method' => 'required|in:office_pickup,home_delivery',
            'customer_notes' => 'nullable|string|max:1000',
            'terms_accepted' => 'required|accepted',
            'payment_method' => 'required|in:wallet',
        ];

        // Add delivery address validation rules if home delivery is selected
        if ($request->delivery_method === 'home_delivery') {
            $validationRules = array_merge($validationRules, [
                'delivery_full_name' => 'required|string|max:255',
                'delivery_phone' => 'required|string|max:20',
                'delivery_address' => 'required|string|max:255',
                'delivery_address_2' => 'nullable|string|max:255',
                'delivery_city' => 'required|string|max:100',
                'delivery_state' => 'required|string|max:100',
                'delivery_zip' => 'required|string|max:20',
                'delivery_instructions' => 'nullable|string|max:1000',
                'delivery_time_preference' => 'nullable|in:anytime,morning,afternoon,weekend',
            ]);
        }

        $validated = $request->validate($validationRules);

        // Sanitize all user inputs to prevent XSS
        if (isset($validated['customer_notes'])) {
            // Check for suspicious patterns first
            if ($this->sanitizationService->containsSuspiciousPatterns($validated['customer_notes'])) {
                Log::warning('Suspicious input detected in customer notes', [
                    'user_id' => Auth::id(),
                    'ip' => $request->ip(),
                    'input' => $validated['customer_notes']
                ]);
            }
            $validated['customer_notes'] = $this->sanitizationService->sanitizeNotes($validated['customer_notes']);
        }

        // Sanitize delivery address fields if present
        if ($request->delivery_method === 'home_delivery') {
            $validated['delivery_full_name'] = $this->sanitizationService->sanitizeAddress($validated['delivery_full_name']);
            $validated['delivery_phone'] = $this->sanitizationService->sanitizeAddress($validated['delivery_phone']);
            $validated['delivery_address'] = $this->sanitizationService->sanitizeAddress($validated['delivery_address']);
            $validated['delivery_address_2'] = $this->sanitizationService->sanitizeAddress($validated['delivery_address_2'] ?? null);
            $validated['delivery_city'] = $this->sanitizationService->sanitizeAddress($validated['delivery_city']);
            $validated['delivery_state'] = $this->sanitizationService->sanitizeAddress($validated['delivery_state']);
            $validated['delivery_zip'] = $this->sanitizationService->sanitizeAddress($validated['delivery_zip']);

            if (isset($validated['delivery_instructions'])) {
                $validated['delivery_instructions'] = $this->sanitizationService->sanitizeNotes($validated['delivery_instructions']);
            }
        }

        // Fraud Detection: Check if order should be blocked
        if (SystemSetting::get('fraud_protection_enabled', true)) {
            if ($this->fraudDetectionService->shouldBlockOrder(Auth::user(), $request->ip())) {
                Log::critical('Blocked order attempt from suspicious user/IP', [
                    'user_id' => Auth::id(),
                    'ip' => $request->ip()
                ]);
                return redirect()->route('dashboard')
                    ->with('error', 'Your order cannot be processed at this time. Please contact support.');
            }
        }

        $cartSummary = $this->cartService->getSummary();

        // Check if cart is empty
        if ($cartSummary['is_empty']) {
            return redirect()->route('packages.index')
                ->with('error', 'Your cart is empty. Please add some packages before checkout.');
        }

        // Fraud Detection: Check velocity and patterns
        if (SystemSetting::get('fraud_protection_enabled', true)) {
            $velocityCheck = $this->fraudDetectionService->checkVelocity(Auth::user(), $request->ip());
            $ipCheck = $this->fraudDetectionService->checkIpReputation($request->ip());
            $patternCheck = $this->fraudDetectionService->checkOrderPattern([
                'total_amount' => $cartSummary['total']
            ], Auth::user());

            // Calculate combined risk score
            $totalRiskScore = $velocityCheck['risk_score'] +
                             ($ipCheck['is_suspicious'] ? 20 : 0) +
                             ($patternCheck['is_suspicious'] ? 15 : 0);

            // Block if risk score is very high
            if ($totalRiskScore >= 70) {
                $this->fraudDetectionService->logSuspiciousActivity(
                    Auth::user(),
                    'high_risk_checkout_attempt',
                    [
                        'risk_score' => $totalRiskScore,
                        'velocity_issues' => $velocityCheck['issues'],
                        'ip_issues' => $ipCheck['issues'],
                        'pattern_issues' => $patternCheck['issues'],
                        'cart_total' => $cartSummary['total']
                    ]
                );

                return redirect()->route('dashboard')
                    ->with('error', 'Your order has been flagged for review. Our team will contact you shortly.');
            }

            // Log suspicious but not blocking activity
            if ($totalRiskScore >= 40) {
                $this->fraudDetectionService->logSuspiciousActivity(
                    Auth::user(),
                    'moderate_risk_checkout',
                    [
                        'risk_score' => $totalRiskScore,
                        'velocity_issues' => $velocityCheck['issues'],
                        'ip_issues' => $ipCheck['issues']
                    ]
                );
            }
        }

        // Validate cart items one more time
        $validationErrors = $this->cartService->validateCart();
        if (!empty($validationErrors)) {
            return redirect()->route('cart.index')
                ->with('error', 'Some items in your cart are no longer available. Please review your cart.');
        }

        // Validate wallet payment
        $paymentValidation = $this->walletPaymentService->validatePayment(
            Auth::user(),
            $cartSummary['total']
        );

        if (!$paymentValidation['valid']) {
            return redirect()->route('checkout.index')
                ->with('error', $paymentValidation['message']);
        }

        try {
            DB::beginTransaction();

            // Create the order
            $order = Order::createFromCart(
                Auth::user(),
                $cartSummary,
                [
                    'checkout_timestamp' => now(),
                    'user_ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'payment_method' => $request->payment_method,
                ]
            );

            // Prepare order update data (use sanitized values)
            $orderData = [
                'delivery_method' => $validated['delivery_method'],
                'customer_notes' => $validated['customer_notes'] ?? null,
            ];

            // Add delivery address information for home delivery
            if ($validated['delivery_method'] === 'home_delivery') {
                $orderData = array_merge($orderData, [
                    'delivery_address' => json_encode([
                        'full_name' => $validated['delivery_full_name'],
                        'phone' => $validated['delivery_phone'],
                        'address' => $validated['delivery_address'],
                        'address_2' => $validated['delivery_address_2'] ?? null,
                        'city' => $validated['delivery_city'],
                        'state' => $validated['delivery_state'],
                        'zip' => $validated['delivery_zip'],
                        'instructions' => $validated['delivery_instructions'] ?? null,
                        'time_preference' => $validated['delivery_time_preference'] ?? null,
                    ])
                ]);

                // Update user's profile with delivery information for future use
                Auth::user()->update([
                    'fullname' => $request->delivery_full_name,
                    'phone' => $request->delivery_phone,
                    'address' => $request->delivery_address,
                    'address_2' => $request->delivery_address_2,
                    'city' => $request->delivery_city,
                    'state' => $request->delivery_state,
                    'zip' => $request->delivery_zip,
                    'delivery_instructions' => $request->delivery_instructions,
                    'delivery_time_preference' => $request->delivery_time_preference ?? 'anytime',
                ]);
            }

            $order->update($orderData);

            // Create order items from cart
            foreach ($cartSummary['items'] as $cartItem) {
                OrderItem::createFromCartItem($order, $cartItem);
            }

            // Reduce package and product quantities
            foreach ($cartSummary['items'] as $cartItem) {
                // Determine item type for backward compatibility
                $itemType = $cartItem['type'] ?? (isset($cartItem['package_id']) ? 'package' : 'product');

                if ($itemType === 'package') {
                    $itemId = $cartItem['item_id'] ?? $cartItem['package_id'];
                    $package = \App\Models\Package::find($itemId);
                    if ($package && $package->quantity_available !== null) {
                        $package->reduceQuantity($cartItem['quantity']);
                    }
                } else if ($itemType === 'product') {
                    $itemId = $cartItem['item_id'];
                    $product = Product::find($itemId);
                    if ($product && $product->quantity_available !== null) {
                        $product->reduceQuantity($cartItem['quantity']);
                    }
                }
            }

            DB::commit();

            // Process wallet payment
            $paymentResult = $this->walletPaymentService->processPayment($order);

            if (!$paymentResult['success']) {
                // Rollback quantity changes if payment fails
                DB::beginTransaction();
                foreach ($cartSummary['items'] as $cartItem) {
                    if ($cartItem['type'] === 'package') {
                        $package = \App\Models\Package::find($cartItem['item_id']);
                        if ($package && $package->quantity_available !== null) {
                            $package->quantity_available += $cartItem['quantity'];
                            $package->save();
                        }
                    } else if ($cartItem['type'] === 'product') {
                        $product = Product::find($cartItem['item_id']);
                        if ($product && $product->quantity_available !== null) {
                            $product->quantity_available += $cartItem['quantity'];
                            $product->save();
                        }
                    }
                }
                DB::commit();

                return redirect()->route('checkout.index')
                    ->with('error', 'Payment failed: ' . $paymentResult['message']);
            }

            // Clear the cart
            $this->cartService->clear();

            // Process MLM commissions immediately (sync) if order contains MLM packages
            // Refresh order to load order items relationship
            $order->load('orderItems.package');

            // Check if any order items contain MLM packages
            $hasMlmPackage = $order->orderItems->contains(function($orderItem) {
                return $orderItem->package && $orderItem->package->is_mlm_package;
            });

            if ($hasMlmPackage) {
                // Activate the user's network status
                $order->user->activateNetwork();

                ProcessMLMCommissions::dispatchSync($order);

                $mlmPackageNames = $order->orderItems
                    ->filter(function($item) {
                        return $item->package && $item->package->is_mlm_package;
                    })
                    ->pluck('package.name')
                    ->join(', ');

                Log::info('MLM Commission Processing Initiated', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'buyer_id' => $order->user_id,
                    'buyer_username' => $order->user->username ?? 'unknown',
                    'sponsor_id' => $order->user->sponsor_id ?? null,
                    'mlm_packages' => $mlmPackageNames
                ]);
            }

            // Process Unilevel bonuses immediately (sync) if order contains products
            $hasProduct = $order->orderItems->contains(function($orderItem) {
                return $orderItem->isProduct();
            });

            if ($hasProduct) {
                ProcessUnilevelBonusesJob::dispatchSync($order);

                $productNames = $order->orderItems
                    ->filter(fn($item) => $item->isProduct())
                    ->pluck('product.name')
                    ->join(', ');

                Log::info('Unilevel Bonus Processing Initiated', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'buyer_id' => $order->user_id,
                    'products' => $productNames
                ]);
            }


            // Redirect to order confirmation
            return redirect()->route('checkout.confirmation', $order)
                ->with('success', 'Your order has been placed and paid successfully!');

        } catch (\Exception $e) {
            DB::rollBack();

            // Log the error
            \Log::error('Checkout failed: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'cart_summary' => $cartSummary,
                'exception' => $e,
            ]);

            return redirect()->route('cart.index')
                ->with('error', 'There was an error processing your order. Please try again.');
        }
    }

    /**
     * Show order confirmation page
     */
    public function confirmation(Order $order)
    {
        // Ensure the order belongs to the current user
        if ($order->user_id !== Auth::id()) {
            abort(403, 'This order does not belong to you.');
        }

        // Load order items with package and product data
        $order->load(['orderItems.package', 'orderItems.product']);

        return view('checkout.confirmation', compact('order'));
    }

    /**
     * Show order details page
     */
    public function orderDetails(Order $order)
    {
        // Ensure the order belongs to the current user
        if ($order->user_id !== Auth::id()) {
            abort(403, 'This order does not belong to you.');
        }

        // Load order items with package and product data
        $order->load(['orderItems.package', 'orderItems.product']);

        return view('checkout.order-details', compact('order'));
    }

    /**
     * Cancel an order (if allowed)
     */
    public function cancelOrder(Request $request, Order $order)
    {
        // Ensure the order belongs to the current user
        if ($order->user_id !== Auth::id()) {
            abort(403, 'This order does not belong to you.');
        }

        // Check if order can be cancelled
        if (!$order->canBeCancelled()) {
            return redirect()->route('checkout.order-details', $order)
                ->with('error', 'This order cannot be cancelled at this time.');
        }

        $request->validate([
            'cancellation_reason' => 'required|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            // Process refund if order was paid
            $refundMessage = '';
            if ($order->isPaid()) {
                $refundResult = $this->walletPaymentService->refundPayment($order);
                if ($refundResult['success']) {
                    $refundMessage = ' Your wallet has been refunded.';
                } else {
                    throw new \Exception('Refund failed: ' . $refundResult['message']);
                }
            }

            // Cancel the order
            $order->cancel($request->cancellation_reason);

            // Restore package and product quantities
            foreach ($order->orderItems as $orderItem) {
                if ($orderItem->isPackage()) {
                    $package = $orderItem->package;
                    if ($package && $package->quantity_available !== null) {
                        $package->quantity_available += $orderItem->quantity;
                        $package->save();
                    }
                } elseif ($orderItem->isProduct()) {
                    $product = $orderItem->product;
                    if ($product && $product->quantity_available !== null) {
                        $product->quantity_available += $orderItem->quantity;
                        $product->save();
                    }
                }
            }

            DB::commit();

            return redirect()->route('checkout.order-details', $order)
                ->with('success', 'Your order has been cancelled successfully.' . $refundMessage);

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Order cancellation failed: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'order_id' => $order->id,
                'exception' => $e,
            ]);

            return redirect()->route('checkout.order-details', $order)
                ->with('error', 'There was an error cancelling your order. Please contact support.');
        }
    }

    /**
     * Get checkout summary for AJAX requests
     */
    public function getSummary()
    {
        $cartSummary = $this->cartService->getSummary();

        return response()->json([
            'success' => true,
            'summary' => $cartSummary,
            'validation_errors' => $this->cartService->validateCart(),
        ]);
    }
}