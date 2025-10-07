<?php

namespace Tests\Feature;

use App\Models\EventReview;
use App\Models\Place;
use App\Models\Role;
use App\Models\Trip;
use App\Models\TripDay;
use App\Models\TripDayCity;
use App\Models\TripDayEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventReviewControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;
    protected User $participant;
    protected User $outsider;
    protected Trip $trip;
    protected TripDayEvent $event;
    protected Role $userRole;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRole = Role::factory()->create(['name' => 'User']);

        $this->owner = User::factory()->create(['role_id' => $this->userRole->id]);
        $this->participant = User::factory()->create(['role_id' => $this->userRole->id]);
        $this->outsider = User::factory()->create(['role_id' => $this->userRole->id]);

        $this->trip = Trip::factory()->create(['user_id' => $this->owner->id]);
        $tripDay = TripDay::factory()->create(['trip_id' => $this->trip->id]);
        $city = TripDayCity::factory()->create(['trip_day_id' => $tripDay->id]);
        $place = Place::factory()->create();
        $this->event = TripDayEvent::factory()->create([
            'trip_day_city_id' => $city->id,
            'place_id' => $place->id,
        ]);

        $this->trip->users()->attach($this->participant->id, ['role' => 'participant']);
    }

    public function test_unauthenticated_user_cannot_create_review(): void
    {
        $data = [
            'trip_day_event_id' => $this->event->id,
            'place_id' => $this->event->place_id,
            'rating' => 5,
            'comment' => 'Excelente!',
        ];

        $response = $this->postJson('/api/v1/events/reviews', $data);

        $response->assertStatus(401);
    }

    public function test_trip_owner_can_create_review(): void
    {
        $data = [
            'trip_day_event_id' => $this->event->id,
            'place_id' => $this->event->place_id,
            'user_id' => $this->owner->id,
            'rating' => 5,
            'comment' => 'Lugar incrível!',
        ];

        $response = $this->actingAs($this->owner, 'sanctum')
            ->postJson('/api/v1/events/reviews', $data);

        $response->assertStatus(201)
            ->assertJsonFragment(['rating' => 5]);

        $this->assertDatabaseHas('events_reviews', [
            'trip_day_event_id' => $this->event->id,
            'user_id' => $this->owner->id,
            'rating' => 5,
        ]);
    }

    public function test_trip_participant_can_create_review(): void
    {
        $data = [
            'trip_day_event_id' => $this->event->id,
            'place_id' => $this->event->place_id,
            'user_id' => $this->participant->id,
            'rating' => 4,
            'comment' => 'Muito bom!',
        ];

        $response = $this->actingAs($this->participant, 'sanctum')
            ->postJson('/api/v1/events/reviews', $data);

        $response->assertStatus(201);
    }

    public function test_outsider_cannot_create_review(): void
    {
        $data = [
            'trip_day_event_id' => $this->event->id,
            'place_id' => $this->event->place_id,
            'user_id' => $this->outsider->id,
            'rating' => 5,
            'comment' => 'Tentando avaliar',
        ];

        $response = $this->actingAs($this->outsider, 'sanctum')
            ->postJson('/api/v1/events/reviews', $data);

        $response->assertStatus(403);
    }

    public function test_validation_error_when_rating_is_invalid(): void
    {
        $data = [
            'trip_day_event_id' => $this->event->id,
            'place_id' => $this->event->place_id,
            'user_id' => $this->owner->id,
            'rating' => 6, // Invalid: should be 1-5
            'comment' => 'Test',
        ];

        $response = $this->actingAs($this->owner, 'sanctum')
            ->postJson('/api/v1/events/reviews', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['rating']);
    }

    public function test_validation_error_when_rating_is_missing(): void
    {
        $data = [
            'trip_day_event_id' => $this->event->id,
            'place_id' => $this->event->place_id,
            'user_id' => $this->owner->id,
            'comment' => 'Test without rating',
        ];

        $response = $this->actingAs($this->owner, 'sanctum')
            ->postJson('/api/v1/events/reviews', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['rating']);
    }

    public function test_review_creator_can_update_own_review(): void
    {
        $review = EventReview::factory()->create([
            'trip_day_event_id' => $this->event->id,
            'place_id' => $this->event->place_id,
            'user_id' => $this->owner->id,
            'rating' => 3,
            'comment' => 'Original comment',
        ]);

        $updateData = [
            'rating' => 5,
            'comment' => 'Updated comment',
        ];

        $response = $this->actingAs($this->owner, 'sanctum')
            ->putJson("/api/v1/events/reviews/{$review->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonFragment(['rating' => 5]);

        $this->assertDatabaseHas('events_reviews', [
            'id' => $review->id,
            'rating' => 5,
            'comment' => 'Updated comment',
        ]);
    }

    public function test_user_cannot_update_others_review(): void
    {
        $review = EventReview::factory()->create([
            'trip_day_event_id' => $this->event->id,
            'place_id' => $this->event->place_id,
            'user_id' => $this->owner->id,
            'rating' => 3,
        ]);

        $updateData = ['rating' => 5];

        $response = $this->actingAs($this->participant, 'sanctum')
            ->putJson("/api/v1/events/reviews/{$review->id}", $updateData);

        $response->assertStatus(403);
    }

    public function test_review_creator_can_delete_own_review(): void
    {
        $review = EventReview::factory()->create([
            'trip_day_event_id' => $this->event->id,
            'place_id' => $this->event->place_id,
            'user_id' => $this->owner->id,
        ]);

        $response = $this->actingAs($this->owner, 'sanctum')
            ->deleteJson("/api/v1/events/reviews/{$review->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('events_reviews', ['id' => $review->id]);
    }

    public function test_user_cannot_delete_others_review(): void
    {
        $review = EventReview::factory()->create([
            'trip_day_event_id' => $this->event->id,
            'place_id' => $this->event->place_id,
            'user_id' => $this->owner->id,
        ]);

        $response = $this->actingAs($this->participant, 'sanctum')
            ->deleteJson("/api/v1/events/reviews/{$review->id}");

        $response->assertStatus(403);
    }

    public function test_authenticated_user_can_list_reviews(): void
    {
        EventReview::factory()->count(3)->create([
            'trip_day_event_id' => $this->event->id,
            'place_id' => $this->event->place_id,
        ]);

        $response = $this->actingAs($this->owner, 'sanctum')
            ->getJson('/api/v1/events/reviews');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_can_filter_reviews_by_place(): void
    {
        $place1 = Place::factory()->create();
        $place2 = Place::factory()->create();

        EventReview::factory()->count(2)->create(['place_id' => $place1->id]);
        EventReview::factory()->count(3)->create(['place_id' => $place2->id]);

        $response = $this->actingAs($this->owner, 'sanctum')
            ->getJson("/api/v1/events/reviews?place_id={$place1->id}");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }
}
