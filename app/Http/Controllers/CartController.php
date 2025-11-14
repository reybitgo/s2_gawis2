<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CartController extends Controller
{
    protected CartService $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    /**
     * Display the cart page
     */
    public function index()
    {
        $validationIssues = $this->cartService->validateCart();
        $cartSummary = $this->cartService->getSummary();

        return view('cart.index', compact('cartSummary', 'validationIssues'));
    }

    /**
     * Add item to cart
     */
    public function add(Request $request, int $packageId): JsonResponse
    {
        $request->validate([
            'quantity' => 'nullable|integer|min:1|max:100'
        ]);

        $quantity = $request->input('quantity', 1);

        // Find the package
        $package = Package::find($packageId);
        if (!$package) {
            return response()->json([
                'success' => false,
                'message' => 'Package not found.'
            ], 404);
        }

        if (!$package->isAvailable()) {
            return response()->json([
                'success' => false,
                'message' => 'This package is no longer available.'
            ], 422);
        }

        $success = $this->cartService->addItem($package, $quantity);

        if (!$success) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to add item to cart. Please check availability.'
            ], 422);
        }

        $cartSummary = $this->cartService->getSummary();

        return response()->json([
            'success' => true,
            'message' => "'{$package->name}' has been added to your cart.",
            'cart_count' => $cartSummary['item_count'],
            'cart_total' => number_format($cartSummary['total'], 2)
        ]);
    }

    /**
     * Update item quantity in cart
     */
    public function update(Request $request, string $itemId): JsonResponse
    {
        $request->validate([
            'quantity' => 'required|integer|min:0|max:100'
        ]);

        $quantity = $request->input('quantity');
        $result = $this->cartService->updateQuantity($itemId, $quantity);

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message']
            ], 422);
        }

        $cartSummary = $this->cartService->getSummary();

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'cart_count' => $cartSummary['item_count'],
            'cart_summary' => $cartSummary
        ]);
    }

    /**
     * Remove item from cart
     */
    public function remove(string $itemId): JsonResponse
    {
        $success = $this->cartService->removeItem($itemId);

        if (!$success) {
            return response()->json([
                'success' => false,
                'message' => 'Item not found in cart.'
            ], 404);
        }

        $cartSummary = $this->cartService->getSummary();

        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart.',
            'cart_count' => $cartSummary['item_count'],
            'cart_summary' => $cartSummary
        ]);
    }

    /**
     * Clear entire cart
     */
    public function clear(): JsonResponse
    {
        $this->cartService->clear();

        return response()->json([
            'success' => true,
            'message' => 'Cart cleared successfully.',
            'cart_count' => 0
        ]);
    }

    /**
     * Get cart item count (for AJAX requests)
     */
    public function getCount(): JsonResponse
    {
        return response()->json([
            'count' => $this->cartService->getItemCount()
        ]);
    }

    /**
     * Get cart summary (for AJAX requests)
     */
    public function getSummary(): JsonResponse
    {
        $cartSummary = $this->cartService->getSummary();
        $validationIssues = $this->cartService->validateCart();

        return response()->json([
            'summary' => $cartSummary,
            'validation_issues' => $validationIssues
        ]);
    }
}