<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Role;
use App\Models\Trip;
use App\Models\User;
use App\Models\UserFollow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostControllerTest extends TestCase
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

    public function test_unauthenticated_user_cannot_create_post(): void
    {
        $data = ['content' => 'Test post'];

        $response = $this->postJson('/api/v1/posts', $data);

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_create_post(): void
    {
        $data = ['content' => 'Meu primeiro post!'];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/posts', $data);

        $response->assertStatus(201)
            ->assertJsonFragment(['content' => 'Meu primeiro post!']);

        $this->assertDatabaseHas('posts', [
            'user_id' => $this->user->id,
            'content' => 'Meu primeiro post!',
        ]);
    }

    public function test_user_can_create_post_with_trip(): void
    {
        $trip = Trip::factory()->create(['user_id' => $this->user->id]);

        $data = [
            'content' => 'Post sobre minha viagem',
            'trip_id' => $trip->id,
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/posts', $data);

        $response->assertStatus(201);

        $this->assertDatabaseHas('posts', [
            'user_id' => $this->user->id,
            'trip_id' => $trip->id,
        ]);
    }

    public function test_anyone_can_view_post_from_public_user(): void
    {
        $post = Post::factory()->create([
            'user_id' => $this->publicUser->id,
            'trip_id' => null,
            'content' => 'Public post',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/posts/{$post->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $post->id]);
    }

    public function test_non_follower_cannot_view_post_from_private_user(): void
    {
        $post = Post::factory()->create([
            'user_id' => $this->privateUser->id,
            'trip_id' => null,
            'content' => 'Private post',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/posts/{$post->id}");

        $response->assertStatus(403);
    }

    public function test_follower_can_view_post_from_private_user(): void
    {
        // User follows privateUser and is accepted
        UserFollow::create([
            'follower_id' => $this->user->id,
            'following_id' => $this->privateUser->id,
            'status' => 'accepted',
            'accepted_at' => now(),
        ]);

        $post = Post::factory()->create([
            'user_id' => $this->privateUser->id,
            'trip_id' => null,
            'content' => 'Private post',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/posts/{$post->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $post->id]);
    }

    public function test_pending_follower_cannot_view_post_from_private_user(): void
    {
        // User follows privateUser but still pending
        UserFollow::create([
            'follower_id' => $this->user->id,
            'following_id' => $this->privateUser->id,
            'status' => 'pending',
        ]);

        $post = Post::factory()->create([
            'user_id' => $this->privateUser->id,
            'trip_id' => null,
            'content' => 'Private post',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/posts/{$post->id}");

        $response->assertStatus(403);
    }

    public function test_author_can_view_own_post(): void
    {
        $post = Post::factory()->create([
            'user_id' => $this->user->id,
            'content' => 'My post',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/posts/{$post->id}");

        $response->assertStatus(200);
    }

    public function test_trip_member_can_view_post_from_private_trip(): void
    {
        $trip = Trip::factory()->create([
            'user_id' => $this->publicUser->id,
            'is_public' => false,
        ]);

        $trip->users()->attach($this->user->id, ['role' => 'participant']);

        $post = Post::factory()->create([
            'user_id' => $this->publicUser->id,
            'trip_id' => $trip->id,
            'content' => 'Private trip post',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/posts/{$post->id}");

        $response->assertStatus(200);
    }

    public function test_non_member_cannot_view_post_from_private_trip(): void
    {
        $trip = Trip::factory()->create([
            'user_id' => $this->publicUser->id,
            'is_public' => false,
        ]);

        $post = Post::factory()->create([
            'user_id' => $this->publicUser->id,
            'trip_id' => $trip->id,
            'content' => 'Private trip post',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/posts/{$post->id}");

        $response->assertStatus(403);
    }

    public function test_author_can_update_own_post(): void
    {
        $post = Post::factory()->create([
            'user_id' => $this->user->id,
            'content' => 'Original content',
        ]);

        $updateData = ['content' => 'Updated content'];

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/v1/posts/{$post->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonFragment(['content' => 'Updated content']);
    }

    public function test_user_cannot_update_others_post(): void
    {
        $post = Post::factory()->create([
            'user_id' => $this->publicUser->id,
            'content' => 'Original content',
        ]);

        $updateData = ['content' => 'Hacked content'];

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/v1/posts/{$post->id}", $updateData);

        $response->assertStatus(403);
    }

    public function test_author_can_delete_own_post(): void
    {
        $post = Post::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/posts/{$post->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    public function test_user_cannot_delete_others_post(): void
    {
        $post = Post::factory()->create(['user_id' => $this->publicUser->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/posts/{$post->id}");

        $response->assertStatus(403);
    }

    public function test_authenticated_user_can_list_public_posts(): void
    {
        Post::factory()->count(3)->create([
            'user_id' => $this->publicUser->id,
            'trip_id' => null,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/posts');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'content', 'user_id']
                ]
            ]);
    }

    public function test_validation_error_when_creating_post_without_content(): void
    {
        $data = []; // No content or photos

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/posts', $data);

        $response->assertStatus(422);
    }
}
