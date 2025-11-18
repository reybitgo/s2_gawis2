<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

echo "=== User Hierarchy Checker ===\n\n";

// Get all users with their sponsor relationships
$users = User::with(['sponsor.sponsor.sponsor'])
    ->orderBy('id')
    ->get();

if ($users->isEmpty()) {
    die("No users found in database.\n");
}

echo "Total Users: " . $users->count() . "\n";
echo str_repeat('=', 80) . "\n\n";

foreach ($users as $user) {
    echo "User ID: {$user->id}\n";
    echo "Username: {$user->username}\n";
    echo "Email: {$user->email}\n";
    echo "Network Status: {$user->network_status}\n";
    echo "Sponsor ID: " . ($user->sponsor_id ?? 'NULL') . "\n";
    
    // Show hierarchy
    $hierarchy = [];
    $current = $user;
    $level = 0;
    
    while ($current && $level < 5) {
        if ($level > 0) {
            $hierarchy[] = "  Level $level: {$current->username} (ID: {$current->id})";
        }
        $current = $current->sponsor;
        $level++;
    }
    
    if (!empty($hierarchy)) {
        echo "Upline Hierarchy:\n";
        foreach ($hierarchy as $line) {
            echo "$line\n";
        }
    } else {
        echo "Upline Hierarchy: (No sponsor)\n";
    }
    
    // Count downline
    $directReferrals = User::where('sponsor_id', $user->id)->count();
    echo "Direct Referrals: $directReferrals\n";
    
    echo str_repeat('-', 80) . "\n\n";
}

// Find users with 3+ level hierarchy
echo str_repeat('=', 80) . "\n";
echo "Users with 3+ Level Hierarchy (Good for testing):\n";
echo str_repeat('=', 80) . "\n\n";

$goodForTesting = [];

foreach ($users as $user) {
    if ($user->sponsor && $user->sponsor->sponsor && $user->sponsor->sponsor->sponsor) {
        $goodForTesting[] = $user;
        echo "✓ {$user->username} (ID: {$user->id})\n";
        echo "  Level 1: {$user->sponsor->username} (ID: {$user->sponsor->id})\n";
        echo "  Level 2: {$user->sponsor->sponsor->username} (ID: {$user->sponsor->sponsor->id})\n";
        echo "  Level 3: {$user->sponsor->sponsor->sponsor->username} (ID: {$user->sponsor->sponsor->sponsor->id})\n\n";
    }
}

if (empty($goodForTesting)) {
    echo "❌ No users with 3+ level hierarchy found.\n\n";
    echo "To set up test data, you need to:\n";
    echo "1. Create or update users with sponsor_id relationships\n";
    echo "2. Create at least 3 levels: User A -> User B -> User C -> User D\n";
    echo "3. Ensure they have network_status = 'active'\n\n";
    
    echo "Quick fix using tinker:\n";
    echo "  php artisan tinker\n";
    echo "  \$user1 = User::find(1);\n";
    echo "  \$user2 = User::find(2);\n";
    echo "  \$user3 = User::find(3);\n";
    echo "  \$user4 = User::find(4);\n";
    echo "  \$user2->sponsor_id = \$user1->id; \$user2->save();\n";
    echo "  \$user3->sponsor_id = \$user2->id; \$user3->save();\n";
    echo "  \$user4->sponsor_id = \$user3->id; \$user4->save();\n";
    echo "  // Now user4 has 3 levels of sponsors\n\n";
}

echo "Done!\n";
