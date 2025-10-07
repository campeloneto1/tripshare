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

    public function test_place_can_be_updated(): void
    {
        $place = Place::factory()->create([
            'name' => 'Original Name',
            'type' => 'attraction',
            'address' => 'Original Address',
        ]);

        $place->update([
            'name' => 'Updated Name',
            'type' => 'restaurant',
            'address' => 'Updated Address',
        ]);

        $this->assertEquals('Updated Name', $place->fresh()->name);
        $this->assertEquals('restaurant', $place->fresh()->type);
        $this->assertEquals('Updated Address', $place->fresh()->address);
    }

    public function test_place_name_can_be_updated(): void
    {
        $place = Place::factory()->create([
            'name' => 'Torre Eiffel',
        ]);

        $place->update(['name' => 'Eiffel Tower']);

        $this->assertDatabaseHas('places', [
            'id' => $place->id,
            'name' => 'Eiffel Tower',
        ]);
    }

    public function test_place_type_can_be_changed(): void
    {
        $place = Place::factory()->create([
            'type' => 'attraction',
        ]);

        $place->update(['type' => 'museum']);

        $this->assertEquals('museum', $place->fresh()->type);
    }

    public function test_place_coordinates_can_be_updated(): void
    {
        $place = Place::factory()->create([
            'lat' => 48.858370,
            'lon' => 2.294481,
        ]);

        $place->update([
            'lat' => 40.748817,
            'lon' => -73.985428,
        ]);

        $this->assertEquals(40.748817, $place->fresh()->lat);
        $this->assertEquals(-73.985428, $place->fresh()->lon);
    }

    public function test_place_address_details_can_be_updated(): void
    {
        $place = Place::factory()->create([
            'address' => 'Old Street',
            'city' => 'Old City',
            'country' => 'Old Country',
        ]);

        $place->update([
            'address' => 'New Street',
            'city' => 'New City',
            'country' => 'New Country',
        ]);

        $this->assertEquals('New Street', $place->fresh()->address);
        $this->assertEquals('New City', $place->fresh()->city);
        $this->assertEquals('New Country', $place->fresh()->country);
    }

    public function test_place_can_be_deleted(): void
    {
        $place = Place::factory()->create();
        $placeId = $place->id;

        $place->delete();

        $this->assertDatabaseMissing('places', ['id' => $placeId]);
    }

    public function test_deleting_place_with_events_preserves_events(): void
    {
        $place = Place::factory()->create();

        $event1 = TripDayEvent::factory()->create(['place_id' => $place->id]);
        $event2 = TripDayEvent::factory()->create(['place_id' => $place->id]);

        $event1Id = $event1->id;
        $event2Id = $event2->id;

        $place->delete();

        // Events should still exist (depending on foreign key cascade rules)
        // This test assumes SET NULL or similar behavior
        $remainingEvent1 = TripDayEvent::withoutGlobalScopes()->find($event1Id);
        $remainingEvent2 = TripDayEvent::withoutGlobalScopes()->find($event2Id);

        $this->assertNotNull($remainingEvent1);
        $this->assertNotNull($remainingEvent2);
    }

    public function test_deleting_place_with_reviews_cascades_to_reviews(): void
    {
        $place = Place::factory()->create();
        $event = TripDayEvent::factory()->create(['place_id' => $place->id]);

        $review1 = EventReview::factory()->create([
            'place_id' => $place->id,
            'trip_day_event_id' => $event->id,
        ]);
        $review2 = EventReview::factory()->create([
            'place_id' => $place->id,
            'trip_day_event_id' => $event->id,
        ]);

        $place->delete();

        // Reviews should be deleted (depending on cascade rules)
        $this->assertEquals(0, EventReview::where('place_id', $place->id)->count());
    }

    public function test_place_xid_cannot_be_changed_to_duplicate(): void
    {
        $place1 = Place::factory()->create(['xid' => 'unique_xid_1']);
        $place2 = Place::factory()->create(['xid' => 'unique_xid_2']);

        $this->expectException(\Illuminate\Database\QueryException::class);

        $place2->update(['xid' => 'unique_xid_1']);
    }
}
