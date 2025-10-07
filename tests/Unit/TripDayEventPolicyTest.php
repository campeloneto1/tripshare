<?php

namespace Tests\Unit;

use App\Models\Place;
use App\Models\Trip;
use App\Models\TripDay;
use App\Models\TripDayCity;
use App\Models\TripDayEvent;
use App\Models\User;
use App\Policies\TripDayEventPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TripDayEventPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected TripDayEventPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new TripDayEventPolicy();
    }

    public function test_any_user_can_view_any_events(): void
    {
        $user = User::factory()->create();

        $this->assertTrue($this->policy->viewAny($user));
    }

    public function test_anyone_can_view_event_from_public_trip(): void
    {
        $owner = User::factory()->create();
        $viewer = User::factory()->create();
        $trip = Trip::factory()->create(['user_id' => $owner->id, 'is_public' => true]);
        $tripDay = TripDay::factory()->create(['trip_id' => $trip->id]);
        $city = TripDayCity::factory()->create(['trip_day_id' => $tripDay->id]);
        $place = Place::factory()->create();
        $event = TripDayEvent::factory()->create([
            'trip_day_city_id' => $city->id,
            'place_id' => $place->id,
        ]);

        $this->assertTrue($this->policy->view($viewer, $event));
    }

    public function test_trip_member_can_view_event_from_private_trip(): void
    {
        $owner = User::factory()->create();
        $participant = User::factory()->create();
        $trip = Trip::factory()->create(['user_id' => $owner->id, 'is_public' => false]);
        $tripDay = TripDay::factory()->create(['trip_id' => $trip->id]);
        $city = TripDayCity::factory()->create(['trip_day_id' => $tripDay->id]);
        $place = Place::factory()->create();
        $event = TripDayEvent::factory()->create([
            'trip_day_city_id' => $city->id,
            'place_id' => $place->id,
        ]);

        $trip->users()->attach($participant->id, ['role' => 'participant']);

        $this->assertTrue($this->policy->view($participant, $event));
    }

    public function test_outsider_cannot_view_event_from_private_trip(): void
    {
        $owner = User::factory()->create();
        $outsider = User::factory()->create();
        $trip = Trip::factory()->create(['user_id' => $owner->id, 'is_public' => false]);
        $tripDay = TripDay::factory()->create(['trip_id' => $trip->id]);
        $city = TripDayCity::factory()->create(['trip_day_id' => $tripDay->id]);
        $place = Place::factory()->create();
        $event = TripDayEvent::factory()->create([
            'trip_day_city_id' => $city->id,
            'place_id' => $place->id,
        ]);

        $this->assertFalse($this->policy->view($outsider, $event));
    }

    public function test_owner_can_create_event(): void
    {
        $owner = User::factory()->create();
        $trip = Trip::factory()->create(['user_id' => $owner->id]);
        $tripDay = TripDay::factory()->create(['trip_id' => $trip->id]);
        $city = TripDayCity::factory()->create(['trip_day_id' => $tripDay->id]);

        $this->assertTrue($this->policy->create($owner, $city));
    }

    public function test_admin_can_create_event(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->create();
        $trip = Trip::factory()->create(['user_id' => $owner->id]);
        $tripDay = TripDay::factory()->create(['trip_id' => $trip->id]);
        $city = TripDayCity::factory()->create(['trip_day_id' => $tripDay->id]);

        $trip->users()->attach($admin->id, ['role' => 'admin']);

        $this->assertTrue($this->policy->create($admin, $city));
    }

    public function test_participant_cannot_create_event(): void
    {
        $owner = User::factory()->create();
        $participant = User::factory()->create();
        $trip = Trip::factory()->create(['user_id' => $owner->id]);
        $tripDay = TripDay::factory()->create(['trip_id' => $trip->id]);
        $city = TripDayCity::factory()->create(['trip_day_id' => $tripDay->id]);

        $trip->users()->attach($participant->id, ['role' => 'participant']);

        $this->assertFalse($this->policy->create($participant, $city));
    }

    public function test_owner_can_update_event(): void
    {
        $owner = User::factory()->create();
        $trip = Trip::factory()->create(['user_id' => $owner->id]);
        $tripDay = TripDay::factory()->create(['trip_id' => $trip->id]);
        $city = TripDayCity::factory()->create(['trip_day_id' => $tripDay->id]);
        $place = Place::factory()->create();
        $event = TripDayEvent::factory()->create([
            'trip_day_city_id' => $city->id,
            'place_id' => $place->id,
        ]);

        $this->assertTrue($this->policy->update($owner, $event, $city));
    }

    public function test_admin_can_update_event(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->create();
        $trip = Trip::factory()->create(['user_id' => $owner->id]);
        $tripDay = TripDay::factory()->create(['trip_id' => $trip->id]);
        $city = TripDayCity::factory()->create(['trip_day_id' => $tripDay->id]);
        $place = Place::factory()->create();
        $event = TripDayEvent::factory()->create([
            'trip_day_city_id' => $city->id,
            'place_id' => $place->id,
        ]);

        $trip->users()->attach($admin->id, ['role' => 'admin']);

        $this->assertTrue($this->policy->update($admin, $event, $city));
    }

    public function test_participant_cannot_update_event(): void
    {
        $owner = User::factory()->create();
        $participant = User::factory()->create();
        $trip = Trip::factory()->create(['user_id' => $owner->id]);
        $tripDay = TripDay::factory()->create(['trip_id' => $trip->id]);
        $city = TripDayCity::factory()->create(['trip_day_id' => $tripDay->id]);
        $place = Place::factory()->create();
        $event = TripDayEvent::factory()->create([
            'trip_day_city_id' => $city->id,
            'place_id' => $place->id,
        ]);

        $trip->users()->attach($participant->id, ['role' => 'participant']);

        $this->assertFalse($this->policy->update($participant, $event, $city));
    }

    public function test_owner_can_delete_event(): void
    {
        $owner = User::factory()->create();
        $trip = Trip::factory()->create(['user_id' => $owner->id]);
        $tripDay = TripDay::factory()->create(['trip_id' => $trip->id]);
        $city = TripDayCity::factory()->create(['trip_day_id' => $tripDay->id]);
        $place = Place::factory()->create();
        $event = TripDayEvent::factory()->create([
            'trip_day_city_id' => $city->id,
            'place_id' => $place->id,
        ]);

        $this->assertTrue($this->policy->delete($owner, $event, $city));
    }

    public function test_admin_can_delete_event(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->create();
        $trip = Trip::factory()->create(['user_id' => $owner->id]);
        $tripDay = TripDay::factory()->create(['trip_id' => $trip->id]);
        $city = TripDayCity::factory()->create(['trip_day_id' => $tripDay->id]);
        $place = Place::factory()->create();
        $event = TripDayEvent::factory()->create([
            'trip_day_city_id' => $city->id,
            'place_id' => $place->id,
        ]);

        $trip->users()->attach($admin->id, ['role' => 'admin']);

        $this->assertTrue($this->policy->delete($admin, $event, $city));
    }

    public function test_participant_cannot_delete_event(): void
    {
        $owner = User::factory()->create();
        $participant = User::factory()->create();
        $trip = Trip::factory()->create(['user_id' => $owner->id]);
        $tripDay = TripDay::factory()->create(['trip_id' => $trip->id]);
        $city = TripDayCity::factory()->create(['trip_day_id' => $tripDay->id]);
        $place = Place::factory()->create();
        $event = TripDayEvent::factory()->create([
            'trip_day_city_id' => $city->id,
            'place_id' => $place->id,
        ]);

        $trip->users()->attach($participant->id, ['role' => 'participant']);

        $this->assertFalse($this->policy->delete($participant, $event, $city));
    }
}
