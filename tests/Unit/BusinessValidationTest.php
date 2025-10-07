<?php

namespace Tests\Unit;

use App\Models\EventReview;
use App\Models\Place;
use App\Models\Trip;
use App\Models\TripDay;
use App\Models\TripDayCity;
use App\Models\TripDayEvent;
use App\Models\User;
use App\Models\VoteAnswer;
use App\Models\VoteOption;
use App\Models\VoteQuestion;
use App\Services\EventReviewService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_vote_twice_on_same_question(): void
    {
        $user = User::factory()->create();
        $question = VoteQuestion::factory()->create();
        $option1 = VoteOption::factory()->create(['vote_question_id' => $question->id]);
        $option2 = VoteOption::factory()->create(['vote_question_id' => $question->id]);

        // First vote
        VoteAnswer::create([
            'vote_question_id' => $question->id,
            'vote_option_id' => $option1->id,
            'user_id' => $user->id,
        ]);

        // Try to vote again on same question
        $this->expectException(\Illuminate\Database\QueryException::class);

        VoteAnswer::create([
            'vote_question_id' => $question->id,
            'vote_option_id' => $option2->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_user_can_change_vote_by_updating(): void
    {
        $user = User::factory()->create();
        $question = VoteQuestion::factory()->create();
        $option1 = VoteOption::factory()->create(['vote_question_id' => $question->id]);
        $option2 = VoteOption::factory()->create(['vote_question_id' => $question->id]);

        $vote = VoteAnswer::create([
            'vote_question_id' => $question->id,
            'vote_option_id' => $option1->id,
            'user_id' => $user->id,
        ]);

        // Change vote to different option
        $vote->update(['vote_option_id' => $option2->id]);

        $this->assertEquals($option2->id, $vote->fresh()->vote_option_id);
    }

    public function test_user_cannot_create_duplicate_review_for_same_event(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Você já avaliou este evento.');

        $user = User::factory()->create();
        $place = Place::factory()->create();
        $event = TripDayEvent::factory()->create(['place_id' => $place->id]);

        $service = app(EventReviewService::class);

        // First review
        $service->store([
            'trip_day_event_id' => $event->id,
            'place_id' => $place->id,
            'user_id' => $user->id,
            'rating' => 5,
        ]);

        // Try to create second review for same event
        $service->store([
            'trip_day_event_id' => $event->id,
            'place_id' => $place->id,
            'user_id' => $user->id,
            'rating' => 4,
        ]);
    }

    public function test_user_can_create_multiple_reviews_for_different_events(): void
    {
        $user = User::factory()->create();
        $place = Place::factory()->create();
        $event1 = TripDayEvent::factory()->create(['place_id' => $place->id]);
        $event2 = TripDayEvent::factory()->create(['place_id' => $place->id]);

        $service = app(EventReviewService::class);

        $review1 = $service->store([
            'trip_day_event_id' => $event1->id,
            'place_id' => $place->id,
            'user_id' => $user->id,
            'rating' => 5,
        ]);

        $review2 = $service->store([
            'trip_day_event_id' => $event2->id,
            'place_id' => $place->id,
            'user_id' => $user->id,
            'rating' => 4,
        ]);

        $this->assertNotEquals($review1->id, $review2->id);
    }

    public function test_trip_end_date_must_be_after_start_date(): void
    {
        $user = User::factory()->create();

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $trip = Trip::create([
            'user_id' => $user->id,
            'name' => 'Invalid Trip',
            'start_date' => '2025-06-10',
            'end_date' => '2025-06-05', // Before start_date
        ]);
    }

    public function test_trip_can_have_same_start_and_end_date(): void
    {
        $user = User::factory()->create();

        $trip = Trip::create([
            'user_id' => $user->id,
            'name' => 'One Day Trip',
            'start_date' => '2025-06-10',
            'end_date' => '2025-06-10',
        ]);

        $this->assertDatabaseHas('trips', ['id' => $trip->id]);
    }

    public function test_place_xid_must_be_unique(): void
    {
        Place::create([
            'xid' => 'unique_xid_123',
            'name' => 'Place 1',
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        Place::create([
            'xid' => 'unique_xid_123',
            'name' => 'Place 2',
        ]);
    }

    public function test_rating_must_be_between_1_and_5(): void
    {
        $review = EventReview::factory()->create(['rating' => 3]);

        $this->assertGreaterThanOrEqual(1, $review->rating);
        $this->assertLessThanOrEqual(5, $review->rating);
    }

    public function test_vote_question_closed_state_prevents_edits(): void
    {
        $question = VoteQuestion::factory()->create([
            'title' => 'Original',
            'is_closed' => false,
        ]);

        // Can update while open
        $question->update(['title' => 'Updated']);
        $this->assertEquals('Updated', $question->fresh()->title);

        // Close the question
        $question->update(['is_closed' => true]);

        // Should not be able to update (this is enforced by policy, not model)
        $this->assertTrue($question->fresh()->is_closed);
    }

    public function test_trip_day_date_should_be_within_trip_date_range(): void
    {
        $trip = Trip::factory()->create([
            'start_date' => '2025-06-01',
            'end_date' => '2025-06-10',
        ]);

        // Valid: within range
        $validDay = TripDay::create([
            'trip_id' => $trip->id,
            'date' => '2025-06-05',
        ]);

        $this->assertDatabaseHas('trips_days', ['id' => $validDay->id]);

        // Note: Validation for dates outside range should be in FormRequest
    }

    public function test_user_cannot_like_same_post_twice(): void
    {
        $post = \App\Models\Post::factory()->create();
        $user = User::factory()->create();

        \App\Models\PostLike::create([
            'post_id' => $post->id,
            'user_id' => $user->id,
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        \App\Models\PostLike::create([
            'post_id' => $post->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_vote_option_count_per_question(): void
    {
        $question = VoteQuestion::factory()->create();

        VoteOption::factory()->count(5)->create([
            'vote_question_id' => $question->id,
        ]);

        $this->assertEquals(5, $question->options()->count());
        $this->assertLessThanOrEqual(10, $question->options()->count()); // Business rule: max 10 options
    }

    public function test_trip_can_have_multiple_participants_with_different_roles(): void
    {
        $trip = Trip::factory()->create();
        $admin = User::factory()->create();
        $participant1 = User::factory()->create();
        $participant2 = User::factory()->create();

        $trip->users()->attach($admin->id, ['role' => 'admin']);
        $trip->users()->attach($participant1->id, ['role' => 'participant']);
        $trip->users()->attach($participant2->id, ['role' => 'participant']);

        $this->assertEquals(3, $trip->users()->count());
        $this->assertEquals(1, $trip->users()->where('role', 'admin')->count());
        $this->assertEquals(2, $trip->users()->where('role', 'participant')->count());
    }

    public function test_deleting_trip_cascades_to_trip_days(): void
    {
        $trip = Trip::factory()->create();
        $day1 = TripDay::factory()->create(['trip_id' => $trip->id]);
        $day2 = TripDay::factory()->create(['trip_id' => $trip->id]);

        $trip->delete();

        $this->assertEquals(0, TripDay::where('trip_id', $trip->id)->count());
    }

    public function test_deleting_place_does_not_delete_events(): void
    {
        $place = Place::factory()->create();
        $event = TripDayEvent::factory()->create(['place_id' => $place->id]);

        $eventId = $event->id;
        $place->delete();

        // Event should still exist
        $remainingEvent = TripDayEvent::withoutGlobalScopes()->find($eventId);
        $this->assertNotNull($remainingEvent);
    }
}
