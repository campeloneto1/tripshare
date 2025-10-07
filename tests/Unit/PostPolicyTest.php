<?php

namespace Tests\Unit;

use App\Models\Post;
use App\Models\Trip;
use App\Models\User;
use App\Policies\PostPolicy;
use App\Services\UserFollowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected PostPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new PostPolicy();
    }

    public function test_any_user_can_view_any_posts(): void
    {
        $user = User::factory()->create();

        $this->assertTrue($this->policy->viewAny($user));
    }

    public function test_any_user_can_create_post(): void
    {
        $user = User::factory()->create();

        $this->assertTrue($this->policy->create($user));
    }

    public function test_author_can_view_own_post(): void
    {
        $author = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $author->id]);

        $this->assertTrue($this->policy->view($author, $post));
    }

    public function test_anyone_can_view_post_from_public_user_without_trip(): void
    {
        $author = User::factory()->create(['is_public' => true]);
        $viewer = User::factory()->create();
        $post = Post::factory()->create([
            'user_id' => $author->id,
            'trip_id' => null,
        ]);

        $this->assertTrue($this->policy->view($viewer, $post));
    }

    public function test_cannot_view_post_from_private_user_without_following(): void
    {
        $author = User::factory()->create(['is_public' => false]);
        $viewer = User::factory()->create();
        $post = Post::factory()->create([
            'user_id' => $author->id,
            'trip_id' => null,
        ]);

        $this->assertFalse($this->policy->view($viewer, $post));
    }

    public function test_can_view_post_from_private_user_when_following(): void
    {
        $author = User::factory()->create(['is_public' => false]);
        $viewer = User::factory()->create();
        $post = Post::factory()->create([
            'user_id' => $author->id,
            'trip_id' => null,
        ]);

        // Simula follow aceito
        $author->followers()->attach($viewer->id, [
            'status' => 'accepted',
            'accepted_at' => now(),
        ]);

        $this->assertTrue($this->policy->view($viewer, $post));
    }

    public function test_anyone_can_view_post_from_public_trip(): void
    {
        $author = User::factory()->create();
        $viewer = User::factory()->create();
        $trip = Trip::factory()->create(['is_public' => true]);
        $post = Post::factory()->create([
            'user_id' => $author->id,
            'trip_id' => $trip->id,
        ]);

        $this->assertTrue($this->policy->view($viewer, $post));
    }

    public function test_trip_member_can_view_post_from_private_trip(): void
    {
        $owner = User::factory()->create();
        $author = User::factory()->create();
        $viewer = User::factory()->create();
        $trip = Trip::factory()->create(['user_id' => $owner->id, 'is_public' => false]);

        $trip->users()->attach($viewer->id, ['role' => 'participant']);

        $post = Post::factory()->create([
            'user_id' => $author->id,
            'trip_id' => $trip->id,
        ]);

        $this->assertTrue($this->policy->view($viewer, $post));
    }

    public function test_non_member_cannot_view_post_from_private_trip(): void
    {
        $owner = User::factory()->create();
        $author = User::factory()->create();
        $outsider = User::factory()->create(['is_public' => true]);
        $trip = Trip::factory()->create(['user_id' => $owner->id, 'is_public' => false]);

        $post = Post::factory()->create([
            'user_id' => $author->id,
            'trip_id' => $trip->id,
        ]);

        $this->assertFalse($this->policy->view($outsider, $post));
    }

    public function test_author_can_update_own_post(): void
    {
        $author = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $author->id]);

        $this->assertTrue($this->policy->update($author, $post));
    }

    public function test_user_cannot_update_others_post(): void
    {
        $author = User::factory()->create();
        $otherUser = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $author->id]);

        $this->assertFalse($this->policy->update($otherUser, $post));
    }

    public function test_author_can_delete_own_post(): void
    {
        $author = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $author->id]);

        $this->assertTrue($this->policy->delete($author, $post));
    }

    public function test_user_cannot_delete_others_post(): void
    {
        $author = User::factory()->create();
        $otherUser = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $author->id]);

        $this->assertFalse($this->policy->delete($otherUser, $post));
    }
}
