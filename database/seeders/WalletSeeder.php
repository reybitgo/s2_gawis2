<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WalletSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create wallets for all users who don't have one
        User::whereDoesntHave('wallet')->each(function ($user) {
            Wallet::create([
                'user_id' => $user->id,
                'mlm_balance' => 0.00,
                'purchase_balance' => 0.00,
                'is_active' => true,
            ]);
        });

        // Give the test member some initial purchase balance
        $testMember = User::where('email', 'member@example.com')->first();
        if ($testMember && $testMember->wallet) {
            $testMember->wallet->update(['purchase_balance' => 1000.00]);
        }
    }
}
