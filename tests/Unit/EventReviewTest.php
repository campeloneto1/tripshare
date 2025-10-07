<?php

namespace Tests\Unit;

use App\Models\EventReview;
use App\Models\Place;
use App\Models\TripDayEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_event_review_can_be_created(): void
    {
        $place = Place::factory()->create();
        $event = TripDayEvent::factory()->create(['place_id' => $place->id]);
        $user = User::factory()->create();

        $review = EventReview::create([
            'trip_day_event_id' => $event->id,
            'place_id' => $place->id,
            'user_id' => $user->id,
            'rating' => 5,
            'comment' => 'Excelente lugar!',
        ]);

        $this->assertDatabaseHas('events_reviews', [
            'trip_day_event_id' => $event->id,
            'place_id' => $place->id,
            'user_id' => $user->id,
            'rating' => 5,
        ]);
    }

    public function test_event_review_belongs_to_event(): void
    {
        $review = EventReview::factory()->create();

        $this->assertInstanceOf(TripDayEvent::class, $review->event);
        $this->assertEquals($review->trip_day_event_id, $review->event->id);
    }

    public function test_event_review_belongs_to_user(): void
    {
        $review = EventReview::factory()->create();

        $this->assertInstanceOf(User::class, $review->user);
        $this->assertEquals($review->user_id, $review->user->id);
    }

    public function test_event_review_belongs_to_place(): void
    {
        $place = Place::factory()->create();
        $event = TripDayEvent::factory()->create(['place_id' => $place->id]);
        $review = EventReview::factory()->create([
            'place_id' => $place->id,
            'trip_day_event_id' => $event->id,
        ]);

        $this->assertInstanceOf(Place::class, $review->place);
        $this->assertEquals($place->id, $review->place->id);
    }

    public function test_rating_is_cast_to_integer(): void
    {
        $review = EventReview::factory()->create([
            'rating' => '4',
        ]);

        $this->assertIsInt($review->rating);
        $this->assertEquals(4, $review->rating);
    }

    public function test_multiple_reviews_for_same_place(): void
    {
        $place = Place::factory()->create();
        $event1 = TripDayEvent::factory()->create(['place_id' => $place->id]);
        $event2 = TripDayEvent::factory()->create(['place_id' => $place->id]);

        EventReview::factory()->create([
            'place_id' => $place->id,
            'trip_day_event_id' => $event1->id,
            'rating' => 5,
        ]);

        EventReview::factory()->create([
            'place_id' => $place->id,
            'trip_day_event_id' => $event2->id,
            'rating' => 4,
        ]);

        $this->assertEquals(2, $place->reviews()->count());
        $this->assertEquals(4.5, $place->averageRating());
    }

    public function test_user_can_have_multiple_reviews(): void
    {
        $user = User::factory()->create();

        EventReview::factory()->count(3)->create([
            'user_id' => $user->id,
        ]);

        $this->assertEquals(3, EventReview::where('user_id', $user->id)->count());
    }

    public function test_review_comment_is_optional(): void
    {
        $review = EventReview::factory()->create([
            'comment' => null,
        ]);

        $this->assertNull($review->comment);
        $this->assertNotNull($review->rating);
    }
}
