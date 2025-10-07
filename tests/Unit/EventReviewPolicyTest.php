<?php

namespace Tests\Unit;

use App\Models\EventReview;
use App\Models\Place;
use App\Models\Trip;
use App\Models\TripDay;
use App\Models\TripDayCity;
use App\Models\TripDayEvent;
use App\Models\User;
use App\Policies\EventReviewPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventReviewPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected EventReviewPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new EventReviewPolicy();
    }

    public function test_any_user_can_view_any_reviews(): void
    {
        $user = User::factory()->create();

        $this->assertTrue($this->policy->viewAny($user));
    }

    public function test_any_user_can_view_specific_review(): void
    {
        $user = User::factory()->create();
        $review = EventReview::factory()->create();

        $this->assertTrue($this->policy->view($user, $review));
    }

    public function test_trip_owner_can_create_review(): void
    {
        $owner = User::factory()->create();
        $trip = Trip::factory()->create(['user_id' => $owner->id]);
        $tripDay = TripDay::factory()->create(['trip_id' => $trip->id]);
        $tripDayCity = TripDayCity::factory()->create(['trip_day_id' => $tripDay->id]);
        $place = Place::factory()->create();
        $event = TripDayEvent::factory()->create([
            'trip_day_city_id' => $tripDayCity->id,
            'place_id' => $place->id,
        ]);

        $canCreate = $this->policy->create($owner, $event->id);

        $this->assertTrue($canCreate);
    }

    public function test_trip_participant_can_create_review(): void
    {
        $owner = User::factory()->create();
        $participant = User::factory()->create();
        $trip = Trip::factory()->create(['user_id' => $owner->id]);
        $tripDay = TripDay::factory()->create(['trip_id' => $trip->id]);
        $tripDayCity = TripDayCity::factory()->create(['trip_day_id' => $tripDay->id]);
        $place = Place::factory()->create();
        $event = TripDayEvent::factory()->create([
            'trip_day_city_id' => $tripDayCity->id,
            'place_id' => $place->id,
        ]);

        $trip->users()->attach($participant->id, ['role' => 'participant']);

        $canCreate = $this->policy->create($participant, $event->id);

        $this->assertTrue($canCreate);
    }

    public function test_trip_admin_can_create_review(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->create();
        $trip = Trip::factory()->create(['user_id' => $owner->id]);
        $tripDay = TripDay::factory()->create(['trip_id' => $trip->id]);
        $tripDayCity = TripDayCity::factory()->create(['trip_day_id' => $tripDay->id]);
        $place = Place::factory()->create();
        $event = TripDayEvent::factory()->create([
            'trip_day_city_id' => $tripDayCity->id,
            'place_id' => $place->id,
        ]);

        $trip->users()->attach($admin->id, ['role' => 'admin']);

        $canCreate = $this->policy->create($admin, $event->id);

        $this->assertTrue($canCreate);
    }

    public function test_outsider_cannot_create_review(): void
    {
        $owner = User::factory()->create();
        $outsider = User::factory()->create();
        $trip = Trip::factory()->create(['user_id' => $owner->id]);
        $tripDay = TripDay::factory()->create(['trip_id' => $trip->id]);
        $tripDayCity = TripDayCity::factory()->create(['trip_day_id' => $tripDay->id]);
        $place = Place::factory()->create();
        $event = TripDayEvent::factory()->create([
            'trip_day_city_id' => $tripDayCity->id,
            'place_id' => $place->id,
        ]);

        $canCreate = $this->policy->create($outsider, $event->id);

        $this->assertFalse($canCreate);
    }

    public function test_cannot_create_review_without_event_id(): void
    {
        $user = User::factory()->create();

        $canCreate = $this->policy->create($user, null);

        $this->assertFalse($canCreate);
    }

    public function test_review_creator_can_update_own_review(): void
    {
        $user = User::factory()->create();
        $review = EventReview::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($this->policy->update($user, $review));
    }

    public function test_user_cannot_update_others_review(): void
    {
        $creator = User::factory()->create();
        $otherUser = User::factory()->create();
        $review = EventReview::factory()->create(['user_id' => $creator->id]);

        $this->assertFalse($this->policy->update($otherUser, $review));
    }

    public function test_review_creator_can_delete_own_review(): void
    {
        $user = User::factory()->create();
        $review = EventReview::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($this->policy->delete($user, $review));
    }

    public function test_user_cannot_delete_others_review(): void
    {
        $creator = User::factory()->create();
        $otherUser = User::factory()->create();
        $review = EventReview::factory()->create(['user_id' => $creator->id]);

        $this->assertFalse($this->policy->delete($otherUser, $review));
    }

    public function test_trip_owner_cannot_delete_others_review(): void
    {
        $owner = User::factory()->create();
        $participant = User::factory()->create();
        $trip = Trip::factory()->create(['user_id' => $owner->id]);

        $trip->users()->attach($participant->id, ['role' => 'participant']);

        $review = EventReview::factory()->create(['user_id' => $participant->id]);

        $this->assertFalse($this->policy->delete($owner, $review));
    }
}
