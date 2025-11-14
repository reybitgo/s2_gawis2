<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Package;
use App\Models\Order;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class DebugMLMCommission extends Command
{
    protected $signature = 'debug:mlm-commission';
    protected $description = 'Debug MLM commission setup and distribution';

    public function handle()
    {
        $this->info('=== MLM Commission Debug Report ===');
        $this->newLine();

        // Check users
        $this->info('1. Checking Users:');
        $member = User::where('username', 'member')->first();
        $member2 = User::where('username', 'member2')->first();

        if ($member) {
            $this->line("   Member: ID={$member->id}, Username={$member->username}, Sponsor={$member->sponsor_id}, Active=" . ($member->isActive() ? 'Yes' : 'No'));
        } else {
            $this->error('   Member user not found!');
        }

        if ($member2) {
            $this->line("   Member2: ID={$member2->id}, Username={$member2->username}, Sponsor={$member2->sponsor_id}, Active=" . ($member2->isActive() ? 'Yes' : 'No'));
        } else {
            $this->error('   Member2 user not found!');
        }

        if ($member && $member2) {
            if ($member2->sponsor_id == $member->id) {
                $this->info('   ✓ Sponsor relationship verified: member2 is sponsored by member');
            } else {
                $this->error('   ✗ Sponsor relationship INCORRECT: member2.sponsor_id=' . $member2->sponsor_id . ', expected=' . $member->id);
            }
        }

        $this->newLine();

        // Check packages
        $this->info('2. Checking Packages:');
        $packages = Package::all();
        foreach ($packages as $package) {
            $mlmStatus = $package->is_mlm_package ? '✓ MLM Enabled' : '✗ MLM Disabled';
            $this->line("   Package #{$package->id}: {$package->name} - {$mlmStatus}");
        }

        $this->newLine();

        // Check recent orders
        $this->info('3. Recent Orders (last 5):');
        $orders = Order::with(['user', 'orderItems.package'])
            ->orderBy('id', 'desc')
            ->limit(5)
            ->get();

        foreach ($orders as $order) {
            $username = $order->user ? $order->user->username : 'N/A';
            $packageNames = $order->orderItems->pluck('package.name')->filter()->join(', ') ?: 'N/A';
            $hasMlmPackage = $order->orderItems->contains(function($item) {
                return $item->package && $item->package->is_mlm_package;
            });
            $isMlm = $hasMlmPackage ? '✓ Has MLM Package' : '✗ No MLM Package';
            $this->line("   Order #{$order->id}: User={$username}, Packages={$packageNames}, Status={$order->payment_status}, Amount={$order->total_amount}, {$isMlm}");
        }

        $this->newLine();

        // Check MLM commission transactions
        $this->info('4. MLM Commission Transactions (last 10):');
        $transactions = Transaction::where('type', 'mlm_commission')
            ->with('user')
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get();

        if ($transactions->count() > 0) {
            foreach ($transactions as $transaction) {
                $username = $transaction->user ? $transaction->user->username : 'N/A';
                $this->line("   Transaction #{$transaction->id}: User={$username}, Amount={$transaction->amount}, Desc={$transaction->description}, Date={$transaction->created_at}");
            }
        } else {
            $this->warn('   No MLM commission transactions found');
        }

        $this->newLine();

        // Specific check for member2's orders
        if ($member2) {
            $this->info("5. Checking member2's orders:");
            $member2Orders = Order::where('user_id', $member2->id)
                ->with('orderItems.package')
                ->orderBy('id', 'desc')
                ->get();

            if ($member2Orders->count() > 0) {
                foreach ($member2Orders as $order) {
                    $packageNames = $order->orderItems->pluck('package.name')->filter()->join(', ') ?: 'N/A';
                    $hasMlmPackage = $order->orderItems->contains(function($item) {
                        return $item->package && $item->package->is_mlm_package;
                    });
                    $isMlm = $hasMlmPackage ? '✓ Has MLM Package' : '✗ No MLM Package';
                    $this->line("   Order #{$order->id}: Packages={$packageNames}, Status={$order->payment_status}, Amount={$order->total_amount}, {$isMlm}");
                }
            } else {
                $this->warn('   No orders found for member2');
            }
        }

        $this->newLine();

        // Check if member received any commissions
        if ($member) {
            $this->info("6. Checking member's commission earnings:");
            $memberCommissions = Transaction::where('user_id', $member->id)
                ->where('type', 'mlm_commission')
                ->orderBy('id', 'desc')
                ->get();

            if ($memberCommissions->count() > 0) {
                $total = $memberCommissions->sum('amount');
                $this->line("   Total commissions received: {$total}");
                foreach ($memberCommissions as $commission) {
                    $this->line("   - Amount: {$commission->amount}, Desc: {$commission->description}, Date: {$commission->created_at}");
                }
            } else {
                $this->warn('   No commissions received by member');
            }
        }

        $this->newLine();
        $this->info('=== Debug Report Complete ===');
    }
}
