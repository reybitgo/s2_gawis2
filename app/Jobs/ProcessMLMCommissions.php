<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Order;
use App\Services\MLMCommissionService;
use Illuminate\Support\Facades\Log;

class ProcessMLMCommissions implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public $order;
    public $tries = 3;
    public $timeout = 120;
    public $backoff = [10, 30, 60]; // Retry after 10s, 30s, 60s

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
     * @param MLMCommissionService $mlmCommissionService
     * @return void
     */
    public function handle(MLMCommissionService $mlmCommissionService): void
    {
        Log::info('MLM Commission Job Started', [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'package_id' => $this->order->package_id,
            'attempt' => $this->attempts()
        ]);

        $success = $mlmCommissionService->processCommissions($this->order);

        if ($success) {
            Log::info('MLM Commission Job Completed Successfully', [
                'order_id' => $this->order->id,
                'order_number' => $this->order->order_number
            ]);
        } else {
            Log::warning('MLM Commission Job Completed with Warnings', [
                'order_id' => $this->order->id,
                'order_number' => $this->order->order_number,
                'message' => 'Some commissions may not have been distributed'
            ]);
        }
    }

    /**
     * Handle a job failure.
     *
     * @param \Throwable $exception
     * @return void
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('MLM Commission Job Failed', [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number ?? 'N/A',
            'package_id' => $this->order->package_id ?? 'N/A',
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
            'attempts' => $this->attempts()
        ]);

        // Optionally notify admin about the failure
        // Admin::notify(new MLMCommissionJobFailed($this->order, $exception));
    }
}
