<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Package;

echo "=== Rank Package Configuration ===\n\n";

$packages = Package::whereNotNull('rank_name')->orderBy('rank_order')->get();

foreach ($packages as $package) {
    echo "Package: {$package->name}\n";
    echo "  Rank Name: {$package->rank_name}\n";
    echo "  Rank Order: {$package->rank_order}\n";
    echo "  Price: ₱" . number_format($package->price, 2) . "\n";
    echo "  Required Sponsors: {$package->required_direct_sponsors}\n";
    echo "  Next Rank Package ID: " . ($package->next_rank_package_id ?? 'NULL') . "\n";
    
    if ($package->next_rank_package_id) {
        $nextPackage = Package::find($package->next_rank_package_id);
        echo "  Next Rank: " . ($nextPackage ? $nextPackage->rank_name : 'NOT FOUND') . "\n";
    } else {
        echo "  Next Rank: None (Top Rank)\n";
    }
    
    echo "  Can Advance: " . ($package->canAdvanceToNextRank() ? 'Yes' : 'No') . "\n";
    echo "\n";
}
