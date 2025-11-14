<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

class ProcessUnilevelBonusesJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public $order;
    public $tries = 3;
    public $timeout = 120;
    public $backoff = [10, 30, 60];

    /**
     * Create a new job instance.
     *
     * @param Order $order
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Execute the job.
     *
     * @param \App\Services\UnilevelBonusService $unilevelBonusService
     * @return void
     */
    public function handle(\App\Services\UnilevelBonusService $unilevelBonusService): void
    {
        Log::info('ProcessUnilevelBonusesJob Started', [
            'order_id' => $this->order->id,
            'attempt' => $this->attempts()
        ]);

        $unilevelBonusService->processBonuses($this->order);
    }

    /**
     * Handle a job failure.
     *
     * @param \Throwable $exception
     * @return void
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('ProcessUnilevelBonusesJob Failed', [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number ?? 'N/A',
            'error' => $exception->getMessage(),
        ]);
    }
}
