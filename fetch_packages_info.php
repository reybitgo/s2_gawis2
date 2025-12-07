<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Package;
use App\Models\MlmSetting;

echo "=== Fetching All Rank Packages ===\n\n";

$packages = Package::where('is_rankable', true)
    ->orderBy('rank_order', 'asc')
    ->get();

echo "Total Packages Found: " . $packages->count() . "\n\n";

foreach ($packages as $package) {
    echo "========================================\n";
    echo "PACKAGE #{$package->id}: {$package->name}\n";
    echo "========================================\n";
    echo "Slug: {$package->slug}\n";
    echo "Price: ₱" . number_format($package->price, 2) . "\n";
    echo "Points Awarded: {$package->points_awarded}\n";
    echo "Is Active: " . ($package->is_active ? 'Yes' : 'No') . "\n";
    echo "Is MLM Package: " . ($package->is_mlm_package ? 'Yes' : 'No') . "\n";
    echo "Is Rankable: " . ($package->is_rankable ? 'Yes' : 'No') . "\n";
    echo "Rank Name: " . ($package->rank_name ?? 'None') . "\n";
    echo "Rank Order: " . ($package->rank_order ?? 'None') . "\n";
    echo "Required Direct Sponsors: " . ($package->required_direct_sponsors ?? 'None') . "\n";
    echo "Next Rank Package ID: " . ($package->next_rank_package_id ?? 'None') . "\n";
    
    if ($package->next_rank_package_id) {
        $nextPackage = Package::find($package->next_rank_package_id);
        echo "Next Rank Package: " . ($nextPackage ? $nextPackage->name . " ({$nextPackage->rank_name})" : 'None') . "\n";
    }
    
    echo "\nCurrent Descriptions:\n";
    echo "---------------------\n";
    echo "Short Description:\n{$package->short_description}\n\n";
    echo "Long Description:\n{$package->long_description}\n\n";
    
    // Get current features
    if ($package->meta_data && isset($package->meta_data['features'])) {
        echo "Current Features:\n";
        foreach ($package->meta_data['features'] as $index => $feature) {
            echo "  " . ($index + 1) . ". {$feature}\n";
        }
        echo "\n";
    } else {
        echo "Current Features: None\n\n";
    }
    
    // Get MLM Settings
    echo "MLM Commission Structure:\n";
    echo "-------------------------\n";
    $mlmSettings = $package->mlmSettings()->where('is_active', true)->orderBy('level')->get();
    
    if ($mlmSettings->isEmpty()) {
        echo "No MLM settings configured.\n\n";
    } else {
        $totalCommission = 0;
        foreach ($mlmSettings as $setting) {
            echo "  Level {$setting->level}: ₱" . number_format($setting->commission_amount, 2) . "\n";
            $totalCommission += $setting->commission_amount;
        }
        echo "  Total Commission Potential: ₱" . number_format($totalCommission, 2) . "\n\n";
    }
    
    // Get category and duration
    if ($package->meta_data) {
        echo "Category: " . ($package->meta_data['category'] ?? 'None') . "\n";
        echo "Duration: " . ($package->meta_data['duration'] ?? 'None') . "\n";
    }
    
    echo "\n";
}

echo "=== End of Package Information ===\n";
