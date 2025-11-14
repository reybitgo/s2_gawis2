<?php

namespace App\Services;

use App\Models\Package;
use App\Models\Product;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class CartService
{
    const CART_SESSION_KEY = 'shopping_cart';

    /**
     * Generate cart item ID
     */
    private function generateItemId(string $type, int $itemId): string
    {
        return "{$type}_{$itemId}";
    }

    /**
     * Parse cart item ID
     */
    private function parseItemId(string $id): array
    {
        $parts = explode('_', $id);
        return [
            'type' => $parts[0] ?? 'package', // Default to package for backward compatibility
            'item_id' => (int)($parts[1] ?? $id)
        ];
    }

    /**
     * Get the current tax rate from system settings
     */
    private function getTaxRate(): float
    {
        return SystemSetting::get('tax_rate', 0.07); // Default to 7% if not set
    }

    /**
     * Get all cart items
     */
    public function getItems(): array
    {
        $items = Session::get(self::CART_SESSION_KEY, []);

        $needsUpdate = false;
        foreach ($items as $itemId => &$item) {
            // Check for inconsistent image URL key and migrate it
            if (!isset($item['image_url']) && isset($item['image'])) {
                $item['image_url'] = $item['image'];
                unset($item['image']);
                $needsUpdate = true;
            }

            // If image URL is still missing, fetch it from the database
            if (!isset($item['image_url'])) {
                $parsedId = $this->parseItemId($itemId);
                if ($parsedId['type'] === 'product') {
                    $product = Product::find($parsedId['item_id']);
                    if ($product) {
                        $item['image_url'] = $product->image_url;
                        $needsUpdate = true;
                    }
                } else {
                    $package = Package::find($parsedId['item_id']);
                    if ($package) {
                        $item['image_url'] = $package->image_url;
                        $needsUpdate = true;
                    }
                }
            }
        }

        if ($needsUpdate) {
            Session::put(self::CART_SESSION_KEY, $items);
        }

        unset($item); // Destroy the lingering reference

        return $items;
    }

    /**
     * Add item to cart
     */
    public function addItem(Package $package, int $quantity = 1): bool
    {
        if (!$package->isAvailable()) {
            return false;
        }

        $cart = $this->getItems();
        $packageId = $package->id;

        if (isset($cart[$packageId])) {
            $newQuantity = $cart[$packageId]['quantity'] + $quantity;

            // Check if new quantity exceeds available stock
            if ($package->quantity_available !== null && $newQuantity > $package->quantity_available) {
                return false;
            }

            $cart[$packageId]['quantity'] = $newQuantity;
        } else {
            // Check if quantity exceeds available stock
            if ($package->quantity_available !== null && $quantity > $package->quantity_available) {
                return false;
            }

            $cart[$packageId] = [
                'package_id' => $package->id,
                'name' => $package->name,
                'slug' => $package->slug,
                'price' => $package->price,
                'points_awarded' => $package->points_awarded,
                'image_url' => $package->image_url,
                'short_description' => $package->short_description,
                'quantity' => $quantity,
                'added_at' => now()->toISOString()
            ];
        }

        Session::put(self::CART_SESSION_KEY, $cart);
        return true;
    }

    /**
     * Add product to cart
     */
    public function addProduct(Product $product, int $quantity = 1): bool
    {
        if (!$product->isAvailable()) {
            return false;
        }

        $cart = $this->getItems();
        $itemId = $this->generateItemId('product', $product->id);

        if (isset($cart[$itemId])) {
            $newQuantity = $cart[$itemId]['quantity'] + $quantity;

            // Check if new quantity exceeds available stock
            if ($product->quantity_available !== null && $newQuantity > $product->quantity_available) {
                return false;
            }

            $cart[$itemId]['quantity'] = $newQuantity;
        } else {
            // Check if quantity exceeds available stock
            if ($product->quantity_available !== null && $quantity > $product->quantity_available) {
                return false;
            }

            $cart[$itemId] = [
                'id' => $itemId,
                'type' => 'product',
                'item_id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'sku' => $product->sku,
                'price' => $product->price,
                'points' => $product->points_awarded,
                'image_url' => $product->image_url,
                'short_description' => $product->short_description,
                'quantity' => $quantity,
                'added_at' => now()->toISOString()
            ];
        }

        Session::put(self::CART_SESSION_KEY, $cart);
        return true;
    }

    /**
     * Check if a package is in cart
     */
    public function hasPackage(int $packageId): bool
    {
        $cart = $this->getItems();
        return isset($cart[$packageId]) || isset($cart[$this->generateItemId('package', $packageId)]);
    }

    /**
     * Check if a product is in cart
     */
    public function hasProduct(int $productId): bool
    {
        $cart = $this->getItems();
        return isset($cart[$this->generateItemId('product', $productId)]);
    }

    /**
     * Update item quantity in cart (supports both legacy packageId and new composite ID)
     */
    public function updateQuantity($itemId, int $quantity): array
    {
        Log::info('Updating quantity', ['itemId' => $itemId, 'cart' => Session::get(self::CART_SESSION_KEY)]);
        if ($quantity <= 0) {
            $this->removeItem($itemId);
            return ['success' => true, 'message' => 'Item removed from cart.'];
        }

        $cart = $this->getItems();

        if (!isset($cart[$itemId])) {
            throw new \Exception("Item with ID '{$itemId}' not found in cart. Cart contents: " . json_encode($cart));
        }

        $item = $cart[$itemId];
        $type = $item['type'] ?? 'package';
        $actualItemId = $item['item_id'] ?? $itemId;

        if ($type === 'package') {
            $package = Package::find($actualItemId);
            if (!$package || !$package->isAvailable()) {
                return ['success' => false, 'message' => "'{$item['name']}' is no longer available."];
            }

            if ($package->quantity_available !== null && $quantity > $package->quantity_available) {
                return ['success' => false, 'message' => "Only {$package->quantity_available} units of '{$item['name']}' are available."];
            }
        } else {
            $product = Product::find($actualItemId);
            if (!$product || !$product->isAvailable()) {
                return ['success' => false, 'message' => "'{$item['name']}' is no longer available."];
            }

            if ($product->quantity_available !== null && $quantity > $product->quantity_available) {
                return ['success' => false, 'message' => "Only {$product->quantity_available} units of '{$item['name']}' are available."];
            }
        }

        $cart[$itemId]['quantity'] = $quantity;
        Session::put(self::CART_SESSION_KEY, $cart);

        return ['success' => true, 'message' => 'Cart updated successfully.'];
    }

    /**
     * Remove item from cart (supports both legacy packageId and new composite ID)
     */
    public function removeItem($itemId): bool
    {
        $cart = $this->getItems();

        if (isset($cart[$itemId])) {
            unset($cart[$itemId]);
            Session::put(self::CART_SESSION_KEY, $cart);
            return true;
        }

        return false;
    }

    /**
     * Clear entire cart
     */
    public function clear(): void
    {
        Session::forget(self::CART_SESSION_KEY);
    }

    /**
     * Get cart item count
     */
    public function getItemCount(): int
    {
        $cart = $this->getItems();
        return array_sum(array_column($cart, 'quantity'));
    }

    /**
     * Get cart subtotal
     */
    public function getSubtotal(): float
    {
        $cart = $this->getItems();
        $subtotal = 0;

        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }

        return round($subtotal, 2);
    }

    /**
     * Get cart tax amount
     */
    public function getTaxAmount(): float
    {
        return round($this->getSubtotal() * $this->getTaxRate(), 2);
    }

    /**
     * Get cart total
     */
    public function getTotal(): float
    {
        return round($this->getSubtotal() + $this->getTaxAmount(), 2);
    }

    /**
     * Get total points that will be awarded
     */
    public function getTotalPoints(): int
    {
        $cart = $this->getItems();
        $totalPoints = 0;

        foreach ($cart as $item) {
            $points = $item['points'] ?? $item['points_awarded'] ?? 0;
            $totalPoints += $points * $item['quantity'];
        }

        return $totalPoints;
    }

    /**
     * Get items grouped by type (packages and products)
     */
    public function getItemsByType(): array
    {
        $cart = $this->getItems();
        $packages = [];
        $products = [];

        foreach ($cart as $itemId => $item) {
            $type = $item['type'] ?? 'package'; // Default to package for backward compatibility

            if ($type === 'product') {
                $products[$itemId] = $item;
            } else {
                $packages[$itemId] = $item;
            }
        }

        return [
            'packages' => $packages,
            'products' => $products,
        ];
    }

    /**
     * Get subtotals by type
     */
    public function getSubtotalsByType(): array
    {
        $grouped = $this->getItemsByType();

        $packageSubtotal = 0;
        foreach ($grouped['packages'] as $item) {
            $packageSubtotal += $item['price'] * $item['quantity'];
        }

        $productSubtotal = 0;
        foreach ($grouped['products'] as $item) {
            $productSubtotal += $item['price'] * $item['quantity'];
        }

        return [
            'packages' => round($packageSubtotal, 2),
            'products' => round($productSubtotal, 2),
        ];
    }

    /**
     * Check if cart is empty
     */
    public function isEmpty(): bool
    {
        return empty($this->getItems());
    }

    /**
     * Get cart summary for display
     */
    public function getSummary(): array
    {
        $taxAmount = $this->getTaxAmount();
        $taxRate = $this->getTaxRate();
        $grouped = $this->getItemsByType();
        $subtotalsByType = $this->getSubtotalsByType();

        return [
            'items' => $this->getItems(),
            'items_by_type' => $grouped,
            'subtotals_by_type' => $subtotalsByType,
            'item_count' => $this->getItemCount(),
            'subtotal' => $this->getSubtotal(),
            'tax_amount' => $taxAmount,
            'tax_rate' => $taxRate,
            'show_tax' => $taxRate > 0, // Only show tax if rate is greater than 0
            'total' => $this->getTotal(),
            'total_points' => $this->getTotalPoints(),
            'is_empty' => $this->isEmpty(),
            'has_packages' => !empty($grouped['packages']),
            'has_products' => !empty($grouped['products']),
        ];
    }

    /**
     * Validate cart items against current package and product availability
     */
    public function validateCart(): array
    {
        $cart = $this->getItems();
        $issues = [];
        $updatedCart = [];

        foreach ($cart as $itemId => $item) {
            $type = $item['type'] ?? 'package'; // Default to package for backward compatibility
            $actualItemId = $item['item_id'] ?? $itemId;

            if ($type === 'package') {
                $package = Package::find($actualItemId);

                if (!$package || !$package->isAvailable()) {
                    $issues[] = "'{$item['name']}' is no longer available and has been removed from your cart.";
                    continue;
                }

                // Check quantity availability
                if ($package->quantity_available !== null && $item['quantity'] > $package->quantity_available) {
                    $issues[] = "Only {$package->quantity_available} units of '{$package->name}' are available. Cart quantity has been adjusted.";
                    $item['quantity'] = $package->quantity_available;
                }

                // Update item data in case package details changed
                $item['name'] = $package->name;
                $item['price'] = $package->price;
                $item['points_awarded'] = $package->points_awarded;
                $item['image_url'] = $package->image_url;
            } else {
                                        // Product validation
                                        $product = Product::find($actualItemId);
                            
                                        if (!$product || !$product->isAvailable()) {                    $issues[] = "'{$item['name']}' is no longer available and has been removed from your cart.";
                    continue;
                }

                // Check quantity availability
                if ($product->quantity_available !== null && $item['quantity'] > $product->quantity_available) {
                    $issues[] = "Only {$product->quantity_available} units of '{$product->name}' are available. Cart quantity has been adjusted.";
                    $item['quantity'] = $product->quantity_available;
                }

                // Update item data in case product details changed
                $item['name'] = $product->name;
                $item['price'] = $product->price;
                $item['points'] = $product->points_awarded;
                $item['image_url'] = $product->image_url;
            }

            $updatedCart[$itemId] = $item;
        }

        // Update cart with validated items
        Session::put(self::CART_SESSION_KEY, $updatedCart);

        return $issues;
    }

    /**
     * Get cart for checkout (validates and returns clean data)
     */
    public function getCartForCheckout(): array
    {
        $this->validateCart();
        return $this->getSummary();
    }
}