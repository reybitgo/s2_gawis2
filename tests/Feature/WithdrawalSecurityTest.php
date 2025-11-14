<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WithdrawalSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $wallet;

    public function setUp(): void
    {
        parent::setUp();

        // Create a test user with a wallet
        $this->user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->wallet = Wallet::create([
            'user_id' => $this->user->id,
            'balance' => 100.00, // $100 initial balance
            'is_active' => true,
        ]);

        // Assign necessary permissions
        $this->user->givePermissionTo('withdraw_funds');
    }

    /** @test */
    public function user_can_withdraw_within_available_balance()
    {
        $this->actingAs($this->user);

        $response = $this->post(route('wallet.withdraw.process'), [
            'amount' => 50.00,
            'payment_method' => 'Gcash',
            'gcash_number' => '09123456789',
            'agree_terms' => true,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Check that transaction was created
        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->user->id,
            'type' => 'withdrawal',
            'amount' => 50.00,
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function user_cannot_withdraw_more_than_wallet_balance()
    {
        $this->actingAs($this->user);

        $response = $this->post(route('wallet.withdraw.process'), [
            'amount' => 150.00, // More than $100 balance
            'payment_method' => 'Gcash',
            'gcash_number' => '09123456789',
            'agree_terms' => true,
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['amount']);

        // Check that no transaction was created
        $this->assertDatabaseMissing('transactions', [
            'user_id' => $this->user->id,
            'type' => 'withdrawal',
            'amount' => 150.00,
        ]);
    }

    /** @test */
    public function user_cannot_make_multiple_withdrawals_exceeding_balance()
    {
        $this->actingAs($this->user);

        // First withdrawal of $60 - should succeed
        $response1 = $this->post(route('wallet.withdraw.process'), [
            'amount' => 60.00,
            'payment_method' => 'Gcash',
            'gcash_number' => '09123456789',
            'agree_terms' => true,
        ]);

        $response1->assertRedirect();
        $response1->assertSessionHas('success');

        // Second withdrawal of $50 - should fail because total would be $110 > $100 balance
        $response2 = $this->post(route('wallet.withdraw.process'), [
            'amount' => 50.00,
            'payment_method' => 'Maya',
            'maya_number' => '09987654321',
            'agree_terms' => true,
        ]);

        $response2->assertRedirect();
        $response2->assertSessionHasErrors(['amount']);

        // Check that only the first transaction was created
        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->user->id,
            'type' => 'withdrawal',
            'amount' => 60.00,
            'status' => 'pending',
        ]);

        $this->assertDatabaseMissing('transactions', [
            'user_id' => $this->user->id,
            'type' => 'withdrawal',
            'amount' => 50.00,
        ]);

        // Verify error message mentions pending withdrawals
        $errors = session('errors');
        if ($errors) {
            $this->assertStringContainsString('pending withdrawals', $errors->first('amount'));
            $this->assertStringContainsString('$60.00', $errors->first('amount'));
        }
    }

    /** @test */
    public function user_can_withdraw_remaining_balance_after_pending_withdrawal()
    {
        $this->actingAs($this->user);

        // First withdrawal of $60
        $this->post(route('wallet.withdraw.process'), [
            'amount' => 60.00,
            'payment_method' => 'Gcash',
            'gcash_number' => '09123456789',
            'agree_terms' => true,
        ]);

        // Second withdrawal of $40 (exactly the remaining available balance) - should succeed
        $response = $this->post(route('wallet.withdraw.process'), [
            'amount' => 40.00,
            'payment_method' => 'Maya',
            'maya_number' => '09987654321',
            'agree_terms' => true,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Check that both transactions exist
        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->user->id,
            'type' => 'withdrawal',
            'amount' => 60.00,
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->user->id,
            'type' => 'withdrawal',
            'amount' => 40.00,
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function approved_withdrawals_do_not_affect_available_balance_calculation()
    {
        $this->actingAs($this->user);

        // Create an already approved withdrawal
        Transaction::create([
            'user_id' => $this->user->id,
            'type' => 'withdrawal',
            'amount' => 30.00,
            'status' => 'approved', // Already processed
            'payment_method' => 'Gcash',
            'description' => 'Previous withdrawal',
        ]);

        // Update wallet balance to reflect the approved withdrawal
        $this->wallet->update(['balance' => 70.00]);

        // New withdrawal of $70 should succeed (full current balance)
        $response = $this->post(route('wallet.withdraw.process'), [
            'amount' => 70.00,
            'payment_method' => 'Maya',
            'maya_number' => '09987654321',
            'agree_terms' => true,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->user->id,
            'type' => 'withdrawal',
            'amount' => 70.00,
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function rejected_withdrawals_do_not_affect_available_balance_calculation()
    {
        $this->actingAs($this->user);

        // Create a rejected withdrawal
        Transaction::create([
            'user_id' => $this->user->id,
            'type' => 'withdrawal',
            'amount' => 50.00,
            'status' => 'rejected', // Rejected, funds should be available again
            'payment_method' => 'Gcash',
            'description' => 'Rejected withdrawal',
        ]);

        // New withdrawal of $100 should succeed (full balance available since previous was rejected)
        $response = $this->post(route('wallet.withdraw.process'), [
            'amount' => 100.00,
            'payment_method' => 'Maya',
            'maya_number' => '09987654321',
            'agree_terms' => true,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->user->id,
            'type' => 'withdrawal',
            'amount' => 100.00,
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function frozen_wallet_cannot_make_withdrawals()
    {
        $this->actingAs($this->user);

        // Freeze the wallet
        $this->wallet->update(['is_active' => false]);

        $response = $this->post(route('wallet.withdraw.process'), [
            'amount' => 50.00,
            'payment_method' => 'Gcash',
            'gcash_number' => '09123456789',
            'agree_terms' => true,
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['general']);

        $errors = session('errors');
        $this->assertStringContainsString('frozen', $errors->first('general'));
    }
}