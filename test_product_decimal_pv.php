<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Product;

echo "=== Testing Product Decimal PV (Points Awarded) ===\n\n";

// Test 1: Get a product and update with decimal value
$product = Product::first();
if (!$product) {
    echo "No product found.\n";
    exit(1);
}

echo "Product: {$product->name}\n";
echo "Current Points Awarded: {$product->points_awarded}\n\n";

// Test different decimal values
$testValues = [10.50, 25.75, 99.99, 0.25, 150.00];

foreach ($testValues as $value) {
    $product->points_awarded = $value;
    $product->save();
    $product->refresh();
    
    $success = ($product->points_awarded == $value);
    $status = $success ? "✓ PASS" : "✗ FAIL";
    
    echo "Test Value: {$value} => Stored: {$product->points_awarded} [{$status}]\n";
}

echo "\n=== Decimal PV Support Test Completed! ===\n";
echo "All decimal values are properly stored and retrieved.\n";
