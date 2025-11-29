<?php

/**
 * Create test users with package purchases for testing rank assignment
 * This will create:
 * - 50 users with Starter package
 * - 20 users with Newbie package
 * - 10 users with Bronze package
 * - All with paid orders
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Package;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

echo "===========================================\n";
echo "Creating Test Users with Package Purchases\n";
echo "===========================================\n\n";

// Get the rank packages
$starterPackage = Package::where('rank_name', 'Starter')->first();
$newbiePackage = Package::where('rank_name', 'Newbie')->first();
$bronzePackage = Package::where('rank_name', 'Bronze')->first();

if (!$starterPackage || !$newbiePackage || !$bronzePackage) {
    echo "❌ ERROR: Rank packages not found!\n";
    echo "Please run: php artisan db:seed --class=PackageSeeder\n";
    exit(1);
}

echo "✓ Found rank packages:\n";
echo "  - Starter (ID: {$starterPackage->id}): ₱" . number_format($starterPackage->price, 2) . "\n";
echo "  - Newbie (ID: {$newbiePackage->id}): ₱" . number_format($newbiePackage->price, 2) . "\n";
echo "  - Bronze (ID: {$bronzePackage->id}): ₱" . number_format($bronzePackage->price, 2) . "\n\n";

$createdUsers = 0;
$createdOrders = 0;

// Configuration
$testData = [
    ['package' => $starterPackage, 'count' => 50, 'prefix' => 'starter_user'],
    ['package' => $newbiePackage, 'count' => 20, 'prefix' => 'newbie_user'],
    ['package' => $bronzePackage, 'count' => 10, 'prefix' => 'bronze_user'],
];

try {
    foreach ($testData as $config) {
        $package = $config['package'];
        $count = $config['count'];
        $prefix = $config['prefix'];
        
        echo "Creating {$count} users with {$package->rank_name} package...\n";
        
        for ($i = 1; $i <= $count; $i++) {
            // Create user
            $username = "{$prefix}_{$i}";
            $email = "{$prefix}_{$i}@test.com";
            
            // Check if user already exists
            $existingUser = User::where('username', $username)->first();
            if ($existingUser) {
                echo "  ⊙ User {$username} already exists, skipping...\n";
                continue;
            }
            
            $user = User::create([
                'username' => $username,
                'email' => $email,
                'password' => Hash::make('password123'),
                'fullname' => ucwords(str_replace('_', ' ', $username)),
                'sponsor_id' => 1, // Assuming user ID 1 exists
                'email_verified_at' => now(),
            ]);
            
            $createdUsers++;
            
            // Create order
            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => 'TEST-' . strtoupper(Str::random(10)),
                'subtotal' => $package->price,
                'tax_amount' => 0,
                'total_amount' => $package->price,
                'payment_status' => 'paid',
                'status' => 'completed',
                'paid_at' => now(),
                'completed_at' => now(),
            ]);
            
            // Create order item
            OrderItem::create([
                'order_id' => $order->id,
                'package_id' => $package->id,
                'product_id' => null,
                'quantity' => 1,
                'unit_price' => $package->price,
                'total_price' => $package->price,
            ]);
            
            $createdOrders++;
            
            if ($i % 10 === 0) {
                echo "  ✓ Created {$i}/{$count} users...\n";
            }
        }
        
        echo "  ✓ Completed: {$count} users with {$package->rank_name} package\n\n";
    }
    
    echo "===========================================\n";
    echo "Test Data Creation Complete!\n";
    echo "===========================================\n";
    echo "Total users created: {$createdUsers}\n";
    echo "Total orders created: {$createdOrders}\n";
    echo "===========================================\n\n";
    
    // Verify the data
    echo "VERIFICATION:\n";
    echo "-------------\n";
    $starterUsers = User::whereHas('orders', function($q) use ($starterPackage) {
        $q->where('payment_status', 'paid')
          ->whereHas('orderItems', function($oq) use ($starterPackage) {
              $oq->where('package_id', $starterPackage->id);
          });
    })->count();
    
    $newbieUsers = User::whereHas('orders', function($q) use ($newbiePackage) {
        $q->where('payment_status', 'paid')
          ->whereHas('orderItems', function($oq) use ($newbiePackage) {
              $oq->where('package_id', $newbiePackage->id);
          });
    })->count();
    
    $bronzeUsers = User::whereHas('orders', function($q) use ($bronzePackage) {
        $q->where('payment_status', 'paid')
          ->whereHas('orderItems', function($oq) use ($bronzePackage) {
              $oq->where('package_id', $bronzePackage->id);
          });
    })->count();
    
    echo "Users with Starter package: {$starterUsers}\n";
    echo "Users with Newbie package: {$newbieUsers}\n";
    echo "Users with Bronze package: {$bronzeUsers}\n";
    echo "Total: " . ($starterUsers + $newbieUsers + $bronzeUsers) . "\n\n";
    
    echo "===========================================\n";
    echo "✓ Ready for rank assignment!\n";
    echo "===========================================\n";
    echo "Run: php assign_ranks_to_users.php\n";
    echo "===========================================\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
