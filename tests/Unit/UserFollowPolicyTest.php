<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\UserFollow;
use App\Policies\UserFollowPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserFollowPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected UserFollowPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new UserFollowPolicy();
    }

    public function test_any_user_can_view_any_follows(): void
    {
        $user = User::factory()->create();

        $this->assertTrue($this->policy->viewAny($user));
    }

    public function test_any_user_can_create_follow(): void
    {
        $user = User::factory()->create();

        $this->assertTrue($this->policy->create($user));
    }

    public function test_follower_can_view_follow(): void
    {
        $follower = User::factory()->create();
        $following = User::factory()->create();

        $follow = UserFollow::create([
            'follower_id' => $follower->id,
            'following_id' => $following->id,
            'status' => 'pending',
        ]);

        $this->assertTrue($this->policy->view($follower, $follow));
    }

    public function test_following_can_view_follow(): void
    {
        $follower = User::factory()->create();
        $following = User::factory()->create();

        $follow = UserFollow::create([
            'follower_id' => $follower->id,
            'following_id' => $following->id,
            'status' => 'pending',
        ]);

        $this->assertTrue($this->policy->view($following, $follow));
    }

    public function test_outsider_cannot_view_follow(): void
    {
        $follower = User::factory()->create();
        $following = User::factory()->create();
        $outsider = User::factory()->create();

        $follow = UserFollow::create([
            'follower_id' => $follower->id,
            'following_id' => $following->id,
            'status' => 'pending',
        ]);

        $this->assertFalse($this->policy->view($outsider, $follow));
    }

    public function test_following_user_can_update_follow_status(): void
    {
        $follower = User::factory()->create();
        $following = User::factory()->create();

        $follow = UserFollow::create([
            'follower_id' => $follower->id,
            'following_id' => $following->id,
            'status' => 'pending',
        ]);

        // O usuário que está sendo seguido pode aceitar/rejeitar
        $this->assertTrue($this->policy->update($following, $follow));
    }

    public function test_follower_cannot_update_follow_status(): void
    {
        $follower = User::factory()->create();
        $following = User::factory()->create();

        $follow = UserFollow::create([
            'follower_id' => $follower->id,
            'following_id' => $following->id,
            'status' => 'pending',
        ]);

        // O seguidor não pode forçar aceitação
        $this->assertFalse($this->policy->update($follower, $follow));
    }

    public function test_follower_can_delete_follow(): void
    {
        $follower = User::factory()->create();
        $following = User::factory()->create();

        $follow = UserFollow::create([
            'follower_id' => $follower->id,
            'following_id' => $following->id,
            'status' => 'accepted',
        ]);

        // Seguidor pode unfollow
        $this->assertTrue($this->policy->delete($follower, $follow));
    }

    public function test_following_can_delete_follow(): void
    {
        $follower = User::factory()->create();
        $following = User::factory()->create();

        $follow = UserFollow::create([
            'follower_id' => $follower->id,
            'following_id' => $following->id,
            'status' => 'accepted',
        ]);

        // O usuário sendo seguido pode remover o seguidor
        $this->assertTrue($this->policy->delete($following, $follow));
    }

    public function test_outsider_cannot_delete_follow(): void
    {
        $follower = User::factory()->create();
        $following = User::factory()->create();
        $outsider = User::factory()->create();

        $follow = UserFollow::create([
            'follower_id' => $follower->id,
            'following_id' => $following->id,
            'status' => 'accepted',
        ]);

        $this->assertFalse($this->policy->delete($outsider, $follow));
    }
}
