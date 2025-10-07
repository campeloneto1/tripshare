<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\UserFollow;
use App\Services\UserFollowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserFollowTest extends TestCase
{
    use RefreshDatabase;

    protected UserFollowService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(UserFollowService::class);
    }

    public function test_user_follow_can_be_created(): void
    {
        $follower = User::factory()->create();
        $following = User::factory()->create(['is_public' => false]);

        $this->actingAs($follower);

        $userFollow = $this->service->store([
            'following_id' => $following->id,
        ]);

        $this->assertDatabaseHas('users_follows', [
            'follower_id' => $follower->id,
            'following_id' => $following->id,
        ]);
    }

    public function test_follow_auto_accepts_if_user_is_public(): void
    {
        $follower = User::factory()->create();
        $following = User::factory()->create(['is_public' => true]);

        $this->actingAs($follower);

        $userFollow = $this->service->store([
            'following_id' => $following->id,
        ]);

        $this->assertEquals('accepted', $userFollow->status);
        $this->assertNotNull($userFollow->accepted_at);
    }

    public function test_follow_stays_pending_if_user_is_private(): void
    {
        $follower = User::factory()->create();
        $following = User::factory()->create(['is_public' => false]);

        $this->actingAs($follower);

        $userFollow = $this->service->store([
            'following_id' => $following->id,
        ]);

        $this->assertEquals('pending', $userFollow->status);
        $this->assertNull($userFollow->accepted_at);
    }

    public function test_cannot_follow_same_user_twice(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Você já está seguindo este usuário.');

        $follower = User::factory()->create();
        $following = User::factory()->create();

        $this->actingAs($follower);

        // Primeiro follow
        $this->service->store(['following_id' => $following->id]);

        // Segundo follow (deve falhar)
        $this->service->store(['following_id' => $following->id]);
    }

    public function test_user_follow_can_be_updated_to_accepted(): void
    {
        $follower = User::factory()->create();
        $following = User::factory()->create(['is_public' => false]);

        $this->actingAs($follower);

        $userFollow = $this->service->store([
            'following_id' => $following->id,
        ]);

        $this->assertEquals('pending', $userFollow->status);

        // Aceita o follow
        $updated = $this->service->update($userFollow, [
            'status' => 'accepted',
            'accepted_at' => now(),
        ]);

        $this->assertEquals('accepted', $updated->status);
        $this->assertNotNull($updated->accepted_at);
    }

    public function test_user_follow_can_be_deleted(): void
    {
        $follower = User::factory()->create();
        $following = User::factory()->create();

        $this->actingAs($follower);

        $userFollow = $this->service->store([
            'following_id' => $following->id,
        ]);

        $result = $this->service->delete($userFollow);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('users_follows', [
            'id' => $userFollow->id,
        ]);
    }

    public function test_follower_relationship_works(): void
    {
        $follower = User::factory()->create();
        $following = User::factory()->create(['is_public' => true]);

        $this->actingAs($follower);

        $this->service->store([
            'following_id' => $following->id,
        ]);

        $followers = $following->followers;

        $this->assertEquals(1, $followers->count());
        $this->assertEquals($follower->id, $followers->first()->id);
    }

    public function test_following_relationship_works(): void
    {
        $follower = User::factory()->create();
        $following = User::factory()->create(['is_public' => true]);

        $this->actingAs($follower);

        $this->service->store([
            'following_id' => $following->id,
        ]);

        $followingUsers = $follower->following;

        $this->assertEquals(1, $followingUsers->count());
        $this->assertEquals($following->id, $followingUsers->first()->id);
    }

    public function test_pending_follow_requests_relationship_works(): void
    {
        $follower = User::factory()->create();
        $following = User::factory()->create(['is_public' => false]);

        $this->actingAs($follower);

        $this->service->store([
            'following_id' => $following->id,
        ]);

        $pendingRequests = $following->pendingFollowRequests;

        $this->assertEquals(1, $pendingRequests->count());
        $this->assertEquals('pending', $pendingRequests->first()->status);
    }

    public function test_sent_follow_requests_relationship_works(): void
    {
        $follower = User::factory()->create();
        $following = User::factory()->create(['is_public' => false]);

        $this->actingAs($follower);

        $this->service->store([
            'following_id' => $following->id,
        ]);

        $sentRequests = $follower->sentFollowRequests;

        $this->assertEquals(1, $sentRequests->count());
        $this->assertEquals('pending', $sentRequests->first()->status);
    }
}
