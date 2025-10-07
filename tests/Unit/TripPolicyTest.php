<?php

namespace Tests\Unit;

use App\Models\Trip;
use App\Models\User;
use App\Policies\TripPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TripPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected TripPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new TripPolicy();
    }

    public function test_any_user_can_view_any_trips(): void
    {
        $user = User::factory()->create();

        $this->assertTrue($this->policy->viewAny($user));
    }

    public function test_any_user_can_create_trip(): void
    {
        $user = User::factory()->create();

        $this->assertTrue($this->policy->create($user));
    }

    public function test_owner_can_view_own_trip(): void
    {
        $owner = User::factory()->create();
        $trip = Trip::factory()->create(['user_id' => $owner->id, 'is_public' => false]);

        $this->assertTrue($this->policy->view($owner, $trip));
    }

    public function test_anyone_can_view_public_trip(): void
    {
        $user = User::factory()->create();
        $trip = Trip::factory()->create(['is_public' => true]);

        $this->assertTrue($this->policy->view($user, $trip));
    }

    public function test_participant_can_view_private_trip(): void
    {
        $owner = User::factory()->create();
        $participant = User::factory()->create();
        $trip = Trip::factory()->create(['user_id' => $owner->id, 'is_public' => false]);

        $trip->users()->attach($participant->id, ['role' => 'participant']);

        $this->assertTrue($this->policy->view($participant, $trip));
    }

    public function test_non_participant_cannot_view_private_trip(): void
    {
        $owner = User::factory()->create();
        $outsider = User::factory()->create();
        $trip = Trip::factory()->create(['user_id' => $owner->id, 'is_public' => false]);

        $this->assertFalse($this->policy->view($outsider, $trip));
    }

    public function test_owner_can_update_trip(): void
    {
        $owner = User::factory()->create();
        $trip = Trip::factory()->create(['user_id' => $owner->id]);

        $this->assertTrue($this->policy->update($owner, $trip));
    }

    public function test_admin_can_update_trip(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->create();
        $trip = Trip::factory()->create(['user_id' => $owner->id]);

        $trip->users()->attach($admin->id, ['role' => 'admin']);

        $this->assertTrue($this->policy->update($admin, $trip));
    }

    public function test_participant_cannot_update_trip(): void
    {
        $owner = User::factory()->create();
        $participant = User::factory()->create();
        $trip = Trip::factory()->create(['user_id' => $owner->id]);

        $trip->users()->attach($participant->id, ['role' => 'participant']);

        $this->assertFalse($this->policy->update($participant, $trip));
    }

    public function test_only_owner_can_delete_trip(): void
    {
        $owner = User::factory()->create();
        $trip = Trip::factory()->create(['user_id' => $owner->id]);

        $this->assertTrue($this->policy->delete($owner, $trip));
    }

    public function test_admin_cannot_delete_trip(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->create();
        $trip = Trip::factory()->create(['user_id' => $owner->id]);

        $trip->users()->attach($admin->id, ['role' => 'admin']);

        $this->assertFalse($this->policy->delete($admin, $trip));
    }

    public function test_participant_cannot_delete_trip(): void
    {
        $owner = User::factory()->create();
        $participant = User::factory()->create();
        $trip = Trip::factory()->create(['user_id' => $owner->id]);

        $trip->users()->attach($participant->id, ['role' => 'participant']);

        $this->assertFalse($this->policy->delete($participant, $trip));
    }

    public function test_outsider_cannot_update_trip(): void
    {
        $owner = User::factory()->create();
        $outsider = User::factory()->create();
        $trip = Trip::factory()->create(['user_id' => $owner->id]);

        $this->assertFalse($this->policy->update($outsider, $trip));
    }

    public function test_outsider_cannot_delete_trip(): void
    {
        $owner = User::factory()->create();
        $outsider = User::factory()->create();
        $trip = Trip::factory()->create(['user_id' => $owner->id]);

        $this->assertFalse($this->policy->delete($outsider, $trip));
    }
}
