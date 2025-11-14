<?php

namespace App\Console\Commands;

use App\Services\InventoryManagementService;
use Illuminate\Console\Command;

class CleanupExpiredReservations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:cleanup-reservations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up expired package reservations and release inventory';

    protected InventoryManagementService $inventoryService;

    /**
     * Create a new command instance.
     */
    public function __construct(InventoryManagementService $inventoryService)
    {
        parent::__construct();
        $this->inventoryService = $inventoryService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Cleaning up expired reservations...');

        $releasedCount = $this->inventoryService->cleanupExpiredReservations();

        if ($releasedCount > 0) {
            $this->info("✓ Released {$releasedCount} expired reservations");
        } else {
            $this->info('✓ No expired reservations found');
        }

        return Command::SUCCESS;
    }
}