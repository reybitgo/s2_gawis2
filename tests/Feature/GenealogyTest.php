<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenealogyTest extends TestCase
{
    use RefreshDatabase;

    private User $mainUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mainUser = User::factory()->create();
    }

    /** @test */
    public function a_member_can_view_their_unilevel_genealogy()
    {
        $this->actingAs($this->mainUser);

        $response = $this->get(route('member.unilevel.genealogy'));

        $response->assertStatus(200);
    }

    /** @test */
    public function a_member_can_view_their_mlm_genealogy()
    {
        $this->actingAs($this->mainUser);

        $response = $this->get(route('member.mlm.genealogy'));

        $response->assertStatus(200);
    }

    /** @test */
    public function genealogy_tree_data_is_correctly_structured_and_has_correct_earnings()
    {
        $this->actingAs($this->mainUser);

        // Create a downline structure and transactions
        $l1User = User::factory()->create(['sponsor_id' => $this->mainUser->id]);
        $l2User = User::factory()->create(['sponsor_id' => $l1User->id]);

        // Create sample earnings transactions
        Transaction::factory()->create([
            'user_id' => $this->mainUser->id,
            'type' => 'unilevel_bonus',
            'amount' => 100,
            'metadata' => ['buyer_id' => $l1User->id]
        ]);
        Transaction::factory()->create([
            'user_id' => $this->mainUser->id,
            'type' => 'unilevel_bonus',
            'amount' => 50,
            'metadata' => ['buyer_id' => $l2User->id]
        ]);
        Transaction::factory()->create([
            'user_id' => $this->mainUser->id,
            'type' => 'mlm_commission',
            'amount' => 200,
            'metadata' => ['buyer_id' => $l1User->id]
        ]);

        // Test Unilevel Endpoint
        $unilevelResponse = $this->get(route('member.unilevel.genealogy'));
        $unilevelResponse->assertStatus(200);
        $unilevelData = $unilevelResponse->json();

        $this->assertEquals($l1User->id, $unilevelData[0]['id']);
        $this->assertEquals(100, $unilevelData[0]['earnings']);
        $this->assertEquals($l2User->id, $unilevelData[0]['children'][0]['id']);
        $this->assertEquals(50, $unilevelData[0]['children'][0]['earnings']);

        // Test MLM Endpoint
        $mlmResponse = $this->get(route('member.mlm.genealogy'));
        $mlmResponse->assertStatus(200);
        $mlmData = $mlmResponse->json();

        $this->assertEquals($l1User->id, $mlmData[0]['id']);
        $this->assertEquals(200, $mlmData[0]['earnings']);
        $this->assertEquals($l2User->id, $mlmData[0]['children'][0]['id']);
        $this->assertEquals(0, $mlmData[0]['children'][0]['earnings']); // No MLM commission from L2 user
    }

    /** @test */
    public function users_cannot_view_the_genealogy_of_other_members()
    {
        $otherUser = User::factory()->create();
        $this->actingAs($otherUser);

        // Attempt to access mainUser's genealogy (which shouldn't be possible directly)
        // The route implicitly uses Auth::user(), so this tests that one user can't see another's data.
        $response = $this->get(route('member.unilevel.genealogy'));

        $response->assertStatus(200);
        $this->assertEmpty($response->json()); // Should be empty as otherUser has no downline
    }
}
