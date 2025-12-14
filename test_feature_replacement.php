<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$packages = App\Models\Package::with('mlmSettings')->where('is_mlm_package', true)->get();

function currency($amount) {
    return '₱' . number_format($amount, 2, '.', ',');
}

foreach($packages as $package) {
    echo "\n=== {$package->name} (Rank: {$package->rank_name}) ===\n";
    
    if(isset($package->meta_data['features'])) {
        echo "\nFeatures will display as:\n";
        foreach($package->meta_data['features'] as $feature) {
            $displayFeature = $feature;
            
            if ($package->is_mlm_package && $package->mlmSettings->isNotEmpty()) {
                // Match pattern like "₱1,200 Level 1 Commission" or "₱800 Level 2 SUPREMACY"
                if (preg_match('/₱[\d,.]+ Level (\d+)/', $feature, $matches)) {
                    $level = (int)$matches[1];
                    $setting = $package->mlmSettings->firstWhere('level', $level);
                    if ($setting && $setting->is_active) {
                        // Replace the amount but keep all the wording
                        $displayFeature = preg_replace(
                            '/₱[\d,.]+/',
                            currency($setting->commission_amount),
                            $feature,
                            1
                        );
                    }
                }
            }
            
            echo "  ✓ {$displayFeature}\n";
        }
    }
}

echo "\n✅ All features updated with database values while retaining custom wordings!\n";
