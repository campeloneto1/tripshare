<?php

namespace Tests\Unit;

use App\Models\EventReview;
use App\Models\Place;
use App\Models\TripDayEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_place_can_be_created_with_valid_data(): void
    {
        $place = Place::create([
            'xid' => 'test_xid_123',
            'name' => 'Torre Eiffel',
            'type' => 'attraction',
            'lat' => 48.858370,
            'lon' => 2.294481,
            'source_api' => 'opentripmap',
            'address' => 'Champ de Mars',
            'city' => 'Paris',
            'country' => 'France',
        ]);

        $this->assertDatabaseHas('places', [
            'xid' => 'test_xid_123',
            'name' => 'Torre Eiffel',
        ]);

        $this->assertEquals('Torre Eiffel', $place->name);
        $this->assertEquals(48.858370, $place->lat);
    }

    public function test_place_xid_must_be_unique(): void
    {
        Place::create([
            'xid' => 'unique_xid',
            'name' => 'Place 1',
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        Place::create([
            'xid' => 'unique_xid',
            'name' => 'Place 2',
        ]);
    }

    public function test_place_has_events_relationship(): void
    {
        $place = Place::factory()->create();
        $event = TripDayEvent::factory()->create([
            'place_id' => $place->id,
        ]);

        $this->assertTrue($place->events->contains($event));
        $this->assertEquals($place->id, $event->place_id);
    }

    public function test_place_has_reviews_relationship(): void
    {
        $place = Place::factory()->create();
        $event = TripDayEvent::factory()->create([
            'place_id' => $place->id,
        ]);
        $review = EventReview::factory()->create([
            'place_id' => $place->id,
            'trip_day_event_id' => $event->id,
            'rating' => 5,
        ]);

        $this->assertTrue($place->reviews->contains($review));
    }

    public function test_place_calculates_average_rating(): void
    {
        $place = Place::factory()->create();
        $event = TripDayEvent::factory()->create([
            'place_id' => $place->id,
        ]);

        EventReview::factory()->create([
            'place_id' => $place->id,
            'trip_day_event_id' => $event->id,
            'rating' => 4,
        ]);

        EventReview::factory()->create([
            'place_id' => $place->id,
            'trip_day_event_id' => $event->id,
            'rating' => 5,
        ]);

        $avgRating = $place->averageRating();
        $this->assertEquals(4.5, $avgRating);
    }

    public function test_place_counts_reviews(): void
    {
        $place = Place::factory()->create();
        $event = TripDayEvent::factory()->create([
            'place_id' => $place->id,
        ]);

        EventReview::factory()->count(3)->create([
            'place_id' => $place->id,
            'trip_day_event_id' => $event->id,
        ]);

        $reviewsCount = $place->reviewsCount();
        $this->assertEquals(3, $reviewsCount);
    }

    public function test_place_casts_coordinates_to_float(): void
    {
        $place = Place::factory()->create([
            'lat' => '48.858370',
            'lon' => '2.294481',
        ]);

        $this->assertIsFloat($place->lat);
        $this->assertIsFloat($place->lon);
    }
}
