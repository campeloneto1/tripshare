<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Models\UserFollow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserFollowControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $publicUser;
    protected User $privateUser;
    protected Role $userRole;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRole = Role::factory()->create(['name' => 'User']);

        $this->user = User::factory()->create(['role_id' => $this->userRole->id]);
        $this->publicUser = User::factory()->create([
            'role_id' => $this->userRole->id,
            'is_public' => true,
        ]);
        $this->privateUser = User::factory()->create([
            'role_id' => $this->userRole->id,
            'is_public' => false,
        ]);
    }

    public function test_unauthenticated_user_cannot_follow(): void
    {
        $data = ['following_id' => $this->publicUser->id];

        $response = $this->postJson('/api/v1/users/follows', $data);

        $response->assertStatus(401);
    }

    public function test_user_can_follow_public_user_and_auto_accept(): void
    {
        $data = ['following_id' => $this->publicUser->id];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/users/follows', $data);

        $response->assertStatus(201);

        $this->assertDatabaseHas('users_follows', [
            'follower_id' => $this->user->id,
            'following_id' => $this->publicUser->id,
            'status' => 'accepted',
        ]);

        $follow = UserFollow::where('follower_id', $this->user->id)
            ->where('following_id', $this->publicUser->id)
            ->first();

        $this->assertNotNull($follow->accepted_at);
    }

    public function test_user_can_follow_private_user_and_stays_pending(): void
    {
        $data = ['following_id' => $this->privateUser->id];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/users/follows', $data);

        $response->assertStatus(201);

        $this->assertDatabaseHas('users_follows', [
            'follower_id' => $this->user->id,
            'following_id' => $this->privateUser->id,
            'status' => 'pending',
        ]);

        $follow = UserFollow::where('follower_id', $this->user->id)
            ->where('following_id', $this->privateUser->id)
            ->first();

        $this->assertNull($follow->accepted_at);
    }

    public function test_cannot_follow_same_user_twice(): void
    {
        $data = ['following_id' => $this->publicUser->id];

        // First follow
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/users/follows', $data);

        // Second follow attempt
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/users/follows', $data);

        $response->assertStatus(400);
    }

    public function test_private_user_can_accept_follow_request(): void
    {
        // Create pending follow
        $follow = UserFollow::create([
            'follower_id' => $this->user->id,
            'following_id' => $this->privateUser->id,
            'status' => 'pending',
        ]);

        $updateData = [
            'status' => 'accepted',
            'accepted_at' => now(),
        ];

        $response = $this->actingAs($this->privateUser, 'sanctum')
            ->putJson("/api/v1/users/follows/{$follow->id}", $updateData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('users_follows', [
            'id' => $follow->id,
            'status' => 'accepted',
        ]);
    }

    public function test_follower_cannot_force_accept(): void
    {
        $follow = UserFollow::create([
            'follower_id' => $this->user->id,
            'following_id' => $this->privateUser->id,
            'status' => 'pending',
        ]);

        $updateData = ['status' => 'accepted'];

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/v1/users/follows/{$follow->id}", $updateData);

        $response->assertStatus(403);
    }

    public function test_user_can_unfollow(): void
    {
        $follow = UserFollow::create([
            'follower_id' => $this->user->id,
            'following_id' => $this->publicUser->id,
            'status' => 'accepted',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/users/follows/{$follow->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('users_follows', ['id' => $follow->id]);
    }

    public function test_following_user_can_remove_follower(): void
    {
        $follow = UserFollow::create([
            'follower_id' => $this->user->id,
            'following_id' => $this->publicUser->id,
            'status' => 'accepted',
        ]);

        $response = $this->actingAs($this->publicUser, 'sanctum')
            ->deleteJson("/api/v1/users/follows/{$follow->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('users_follows', ['id' => $follow->id]);
    }

    public function test_outsider_cannot_delete_follow(): void
    {
        $otherUser = User::factory()->create(['role_id' => $this->userRole->id]);

        $follow = UserFollow::create([
            'follower_id' => $this->user->id,
            'following_id' => $this->publicUser->id,
            'status' => 'accepted',
        ]);

        $response = $this->actingAs($otherUser, 'sanctum')
            ->deleteJson("/api/v1/users/follows/{$follow->id}");

        $response->assertStatus(403);
    }

    public function test_user_can_list_own_follows(): void
    {
        UserFollow::create([
            'follower_id' => $this->user->id,
            'following_id' => $this->publicUser->id,
            'status' => 'accepted',
        ]);

        UserFollow::create([
            'follower_id' => $this->user->id,
            'following_id' => $this->privateUser->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/users/follows');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_user_can_see_pending_follow_requests(): void
    {
        $requester = User::factory()->create(['role_id' => $this->userRole->id]);

        UserFollow::create([
            'follower_id' => $requester->id,
            'following_id' => $this->privateUser->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->privateUser, 'sanctum')
            ->getJson('/api/v1/users/follows?status=pending');

        $response->assertStatus(200);
    }
}
