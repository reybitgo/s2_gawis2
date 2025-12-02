<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

echo "=== Verifying Test User Roles and Permissions ===\n\n";

$testUser = User::where('username', 'test_starter_60')->first();

if (!$testUser) {
    echo "❌ Test user 'test_starter_60' not found!\n";
    exit(1);
}

echo "✓ Found user: {$testUser->username}\n";
echo "  ID: {$testUser->id}\n";
echo "  Email: {$testUser->email}\n\n";

echo "Roles:\n";
$roles = $testUser->getRoleNames();
if ($roles->isEmpty()) {
    echo "  ❌ No roles assigned\n";
} else {
    foreach ($roles as $role) {
        echo "  ✓ {$role}\n";
    }
}

echo "\nPermissions:\n";
$permissions = $testUser->getAllPermissions()->pluck('name');
if ($permissions->isEmpty()) {
    echo "  ❌ No permissions assigned\n";
} else {
    foreach ($permissions as $permission) {
        echo "  ✓ {$permission}\n";
    }
}

echo "\n=== Wallet Operations Access Check ===\n";
echo "Can deposit funds: " . ($testUser->can('deposit_funds') ? '✓ Yes' : '✗ No') . "\n";
echo "Can transfer funds: " . ($testUser->can('transfer_funds') ? '✓ Yes' : '✗ No') . "\n";
echo "Can withdraw funds: " . ($testUser->can('withdraw_funds') ? '✓ Yes' : '✗ No') . "\n";
echo "Can view transactions: " . ($testUser->can('view_transactions') ? '✓ Yes' : '✗ No') . "\n";

echo "\n✨ Verification complete!\n";
