<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$packages = App\Models\Package::with('mlmSettings')->where('is_mlm_package', true)->get();

foreach($packages as $pkg) {
    echo "\n--- {$pkg->name} (Rank: {$pkg->rank_name}) ---\n";
    
    if(isset($pkg->meta_data['features'])) {
        echo "Current Features:\n";
        foreach($pkg->meta_data['features'] as $f) {
            echo "  - {$f}\n";
        }
    }
    
    echo "\nDatabase MLM Settings:\n";
    foreach($pkg->mlmSettings as $s) {
        echo "  Level {$s->level}: ₱" . number_format($s->commission_amount, 2) . "\n";
    }
    echo "\n";
}
