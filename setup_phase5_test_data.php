<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Product;
use App\Models\Package;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\MonthlyQuotaService;

echo "=== Setting Up Phase 5 Test Data ===\n\n";

// 1. Setup products with PV
$products = [
    ['name' => 'Test Product A', 'points_awarded' => 10.00, 'price' => 500],
    ['name' => 'Test Product B', 'points_awarded' => 25.00, 'price' => 1000],
    ['name' => 'Test Product C', 'points_awarded' => 50.00, 'price' => 2000],
];

foreach ($products as $productData) {
    $product = Product::where('name', $productData['name'])->first();
    if (!$product) {
        $product = Product::create([
            'name' => $productData['name'],
            'slug' => \Illuminate\Support\Str::slug($productData['name']),
            'description' => 'Test product for Phase 5 quota testing',
            'price' => $productData['price'],
            'stock_quantity' => 100,
            'points_awarded' => $productData['points_awarded'],
        ]);
    } else {
        $product->points_awarded = $productData['points_awarded'];
        $product->price = $productData['price'];
        $product->save();
    }
    echo "✓ Product: {$product->name} - {$product->points_awarded} PV - ₱{$product->price}\n";
}

// 2. Setup test package with quota
$package = Package::where('name', 'Test Starter Package')->first();
if (!$package) {
    $package = Package::create([
        'name' => 'Test Starter Package',
        'slug' => 'test-starter-package',
        'short_description' => 'Test package for Phase 5',
        'description' => 'Test package for Phase 5 quota testing',
        'long_description' => 'This is a test starter package created for Phase 5 monthly quota system testing. It has a monthly quota requirement of 100 PV.',
        'price' => 5000,
        'is_mlm_package' => true,
        'max_mlm_levels' => 5,
        'monthly_quota_points' => 100.00,
        'enforce_monthly_quota' => true,
    ]);
} else {
    $package->monthly_quota_points = 100.00;
    $package->enforce_monthly_quota = true;
    $package->save();
}
echo "✓ Package: {$package->name} - Quota: {$package->monthly_quota_points} PV (Enforced)\n\n";

// 3. Get or create admin sponsor
$admin = User::role('admin')->first();
if (!$admin) {
    echo "❌ No admin user found. Please create admin user first.\n";
    exit(1);
}
echo "✓ Using Admin as sponsor: {$admin->username}\n\n";

// 4. Create test users with different quota statuses
$testUsers = [
    ['username' => 'quota_met_user', 'email' => 'quota_met@test.com', 'pv' => 120, 'status' => 'MET'],
    ['username' => 'quota_half_user', 'email' => 'quota_half@test.com', 'pv' => 50, 'status' => 'HALF'],
    ['username' => 'quota_zero_user', 'email' => 'quota_zero@test.com', 'pv' => 0, 'status' => 'ZERO'],
];

$quotaService = new MonthlyQuotaService();

echo "Creating test users...\n";
foreach ($testUsers as $userData) {
    $user = User::where('username', $userData['username'])->first();
    
    if (!$user) {
        $fullname = 'Test ' . ucfirst(str_replace('_', ' ', $userData['status']));
        $user = User::create([
            'username' => $userData['username'],
            'email' => $userData['email'],
            'password' => bcrypt('password123'),
            'fullname' => $fullname,
            'first_name' => 'Test',
            'last_name' => ucfirst(str_replace('_', ' ', $userData['status'])),
            'sponsor_id' => $admin->id,
            'network_status' => 'active',
            'email_verified_at' => now(),
        ]);
        $user->assignRole('member');
        
        // Create package purchase order
        $packageOrder = Order::create([
            'user_id' => $user->id,
            'order_number' => 'PKG-' . time() . '-' . $user->id,
            'payment_status' => 'paid',
            'payment_method' => 'gcash',
            'subtotal' => $package->price,
            'total_amount' => $package->price,
            'status' => 'completed',
        ]);
        
        OrderItem::create([
            'order_id' => $packageOrder->id,
            'package_id' => $package->id,
            'quantity' => 1,
            'unit_price' => $package->price,
            'price' => $package->price,
            'total_price' => $package->price,
            'subtotal' => $package->price,
        ]);
        
        echo "  ✓ Created user: {$user->username}\n";
    } else {
        echo "  ℹ User exists: {$user->username}\n";
        // Ensure user is active
        $user->network_status = 'active';
        $user->save();
    }
    
    // Add PV for current month
    if ($userData['pv'] > 0) {
        // Delete existing PV orders for current month to avoid duplicates
        $existingOrders = Order::where('user_id', $user->id)
            ->where('payment_status', 'paid')
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->whereHas('orderItems', function($q) {
                $q->whereNotNull('product_id');
            })
            ->get();
        
        foreach ($existingOrders as $order) {
            $order->delete();
        }
        
        // Create fresh order with exact PV
        $productA = Product::where('name', 'Test Product A')->first();
        $productB = Product::where('name', 'Test Product B')->first();
        $productC = Product::where('name', 'Test Product C')->first();
        
        // Calculate quantities to reach target PV
        $targetPV = $userData['pv'];
        $quantities = [];
        
        if ($targetPV >= 50) {
            $quantities[$productC->id] = floor($targetPV / 50);
            $targetPV -= ($quantities[$productC->id] * 50);
        }
        if ($targetPV >= 25) {
            $quantities[$productB->id] = floor($targetPV / 25);
            $targetPV -= ($quantities[$productB->id] * 25);
        }
        if ($targetPV > 0) {
            $quantities[$productA->id] = ceil($targetPV / 10);
        }
        
        // Create order
        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => 'TEST-' . time() . '-' . $user->id,
            'payment_status' => 'paid',
            'payment_method' => 'gcash',
            'subtotal' => 0,
            'total_amount' => 0,
            'status' => 'completed',
        ]);
        
        $total = 0;
        foreach ($quantities as $productId => $quantity) {
            if ($quantity > 0) {
                $product = Product::find($productId);
                $itemTotal = $product->price * $quantity;
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'unit_price' => $product->price,
                    'price' => $product->price,
                    'total_price' => $itemTotal,
                    'subtotal' => $itemTotal,
                ]);
                $total += $itemTotal;
            }
        }
        
        $order->total_amount = $total;
        $order->subtotal = $total;
        $order->save();
        
        // Process quota points
        $quotaService->processOrderPoints($order);
        echo "  ✓ Created PV order: {$order->order_number}\n";
    }
    
    $status = $quotaService->getUserMonthlyStatus($user);
    echo "  → User: {$user->username} - PV: {$status['total_pv']}/{$status['required_quota']} - " . 
         ($status['quota_met'] ? '✓ MET' : '✗ NOT MET') . "\n\n";
}

echo "=== Setup Complete ===\n\n";
echo "Test User Credentials:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Username: quota_met_user  | Password: password123 | Status: 120 PV - Quota Met ✓\n";
echo "Username: quota_half_user | Password: password123 | Status: 50 PV - Half Way\n";
echo "Username: quota_zero_user | Password: password123 | Status: 0 PV - Not Started\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "Ready for Phase 5 Testing!\n";
echo "Access the quota page at: " . url('/my-quota') . "\n";
