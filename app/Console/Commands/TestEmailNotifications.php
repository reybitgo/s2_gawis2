<?php

namespace App\Console\Commands;

use App\Mail\OrderStatusChanged;
use App\Mail\OrderPaymentConfirmed;
use App\Mail\OrderCancelled;
use App\Models\Order;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestEmailNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:email-notifications {--user-id=1} {--order-id=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test email notification system for orders';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->option('user-id');
        $orderId = $this->option('order-id');

        $user = User::find($userId);
        if (!$user) {
            $this->error("User with ID {$userId} not found.");
            return 1;
        }

        // Find an order or use the specified one
        if ($orderId) {
            $order = Order::with('user', 'orderItems')->find($orderId);
            if (!$order) {
                $this->error("Order with ID {$orderId} not found.");
                return 1;
            }
        } else {
            $order = Order::with('user', 'orderItems')->where('user_id', $userId)->first();
            if (!$order) {
                $this->error("No orders found for user ID {$userId}.");
                return 1;
            }
        }

        $this->info("Testing email notifications with:");
        $this->info("User: {$user->name} ({$user->email})");
        $this->info("Order: {$order->order_number}");
        $this->info("Mail driver: " . config('mail.default'));
        $this->newLine();

        try {
            // Test 1: Status Change Notification
            $this->info("1. Testing Order Status Change notification...");
            Mail::to($user->email)->send(new OrderStatusChanged(
                $order,
                'paid',
                'processing',
                'Order is being prepared for shipment',
                'admin'
            ));
            $this->info("✓ Status change email sent");

            // Test 2: Payment Confirmation
            $this->info("2. Testing Payment Confirmation notification...");
            Mail::to($user->email)->send(new OrderPaymentConfirmed($order, 'wallet'));
            $this->info("✓ Payment confirmation email sent");

            // Test 3: Order Cancellation
            $this->info("3. Testing Order Cancellation notification...");
            Mail::to($user->email)->send(new OrderCancelled(
                $order,
                'Customer requested cancellation',
                true
            ));
            $this->info("✓ Order cancellation email sent");

            $this->newLine();
            $this->info("All email notifications sent successfully!");
            $this->info("Check your log files at storage/logs/laravel.log to see the email content.");

        } catch (\Exception $e) {
            $this->error("Error sending emails: " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
