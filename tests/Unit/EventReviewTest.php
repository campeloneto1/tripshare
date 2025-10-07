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

    public function test_event_review_can_be_updated(): void
    {
        $review = EventReview::factory()->create([
            'rating' => 3,
            'comment' => 'Original comment',
        ]);

        $review->update([
            'rating' => 5,
            'comment' => 'Updated comment',
        ]);

        $this->assertDatabaseHas('events_reviews', [
            'id' => $review->id,
            'rating' => 5,
            'comment' => 'Updated comment',
        ]);

        $this->assertEquals(5, $review->fresh()->rating);
        $this->assertEquals('Updated comment', $review->fresh()->comment);
    }

    public function test_event_review_rating_can_be_updated_independently(): void
    {
        $review = EventReview::factory()->create([
            'rating' => 3,
            'comment' => 'Great place',
        ]);

        $review->update(['rating' => 4]);

        $this->assertEquals(4, $review->fresh()->rating);
        $this->assertEquals('Great place', $review->fresh()->comment);
    }

    public function test_event_review_comment_can_be_updated_independently(): void
    {
        $review = EventReview::factory()->create([
            'rating' => 5,
            'comment' => 'Original comment',
        ]);

        $review->update(['comment' => 'Updated comment']);

        $this->assertEquals(5, $review->fresh()->rating);
        $this->assertEquals('Updated comment', $review->fresh()->comment);
    }

    public function test_updating_review_affects_place_average_rating(): void
    {
        $place = Place::factory()->create();
        $event = TripDayEvent::factory()->create(['place_id' => $place->id]);

        $review1 = EventReview::factory()->create([
            'place_id' => $place->id,
            'trip_day_event_id' => $event->id,
            'rating' => 3,
        ]);

        $review2 = EventReview::factory()->create([
            'place_id' => $place->id,
            'trip_day_event_id' => $event->id,
            'rating' => 5,
        ]);

        $this->assertEquals(4, $place->fresh()->averageRating());

        $review1->update(['rating' => 5]);

        $this->assertEquals(5, $place->fresh()->averageRating());
    }

    public function test_event_review_can_be_deleted(): void
    {
        $review = EventReview::factory()->create();
        $reviewId = $review->id;

        $review->delete();

        $this->assertDatabaseMissing('events_reviews', [
            'id' => $reviewId,
        ]);
    }

    public function test_deleting_review_decreases_place_review_count(): void
    {
        $place = Place::factory()->create();
        $event = TripDayEvent::factory()->create(['place_id' => $place->id]);

        EventReview::factory()->count(3)->create([
            'place_id' => $place->id,
            'trip_day_event_id' => $event->id,
        ]);

        $this->assertEquals(3, $place->fresh()->reviewsCount());

        $firstReview = $place->reviews()->first();
        $firstReview->delete();

        $this->assertEquals(2, $place->fresh()->reviewsCount());
    }

    public function test_deleting_review_updates_place_average_rating(): void
    {
        $place = Place::factory()->create();
        $event = TripDayEvent::factory()->create(['place_id' => $place->id]);

        $review1 = EventReview::factory()->create([
            'place_id' => $place->id,
            'trip_day_event_id' => $event->id,
            'rating' => 2,
        ]);

        $review2 = EventReview::factory()->create([
            'place_id' => $place->id,
            'trip_day_event_id' => $event->id,
            'rating' => 4,
        ]);

        $review3 = EventReview::factory()->create([
            'place_id' => $place->id,
            'trip_day_event_id' => $event->id,
            'rating' => 3,
        ]);

        $this->assertEquals(3, $place->fresh()->averageRating());

        $review1->delete();

        $this->assertEquals(3.5, $place->fresh()->averageRating());
    }

    public function test_deleting_event_does_not_automatically_delete_reviews(): void
    {
        $event = TripDayEvent::factory()->create();

        $review = EventReview::factory()->create([
            'trip_day_event_id' => $event->id,
            'place_id' => $event->place_id,
        ]);

        $reviewId = $review->id;
        $eventId = $event->id;

        $event->delete();

        $this->assertDatabaseMissing('trips_days_events', ['id' => $eventId]);
        $this->assertDatabaseMissing('events_reviews', ['id' => $reviewId]);
    }
}
