<?php

/**
 * Setup rank configuration for existing packages
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Package;

echo "Setting up rank configuration for existing packages...\n\n";

// Update Starter package
$starter = Package::find(1);
if ($starter) {
    $starter->update([
        'rank_name' => 'Starter',
        'rank_order' => 1,
        'required_direct_sponsors' => 5,
        'is_rankable' => true,
    ]);
    echo "✓ Updated '{$starter->name}' as Starter rank (Order: 1)\n";
}

// Update Professional Package as Newbie
$pro = Package::find(2);
if ($pro) {
    $pro->update([
        'rank_name' => 'Newbie',
        'rank_order' => 2,
        'required_direct_sponsors' => 8,
        'is_rankable' => true,
    ]);
    echo "✓ Updated '{$pro->name}' as Newbie rank (Order: 2)\n";
}

// Update Premium Package as Bronze
$premium = Package::find(3);
if ($premium) {
    $premium->update([
        'rank_name' => 'Bronze',
        'rank_order' => 3,
        'required_direct_sponsors' => 10,
        'is_rankable' => true,
    ]);
    echo "✓ Updated '{$premium->name}' as Bronze rank (Order: 3)\n";
}

// Set rank progression chain
if ($starter && $pro) {
    $starter->update(['next_rank_package_id' => $pro->id]);
    echo "✓ Set Starter → Newbie progression\n";
}

if ($pro && $premium) {
    $pro->update(['next_rank_package_id' => $premium->id]);
    echo "✓ Set Newbie → Bronze progression\n";
}

echo "\n✓ Rank configuration complete!\n";
echo "\nRank Hierarchy:\n";
echo "1. Starter (requires 5 same-rank sponsors to advance)\n";
echo "2. Newbie (requires 8 same-rank sponsors to advance)\n";
echo "3. Bronze (top rank)\n";
