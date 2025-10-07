<?php

namespace Tests\Unit;

use App\Models\EventReview;
use App\Models\Place;
use App\Models\TripDayCity;
use App\Models\TripDayEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TripDayEventTest extends TestCase
{
    use RefreshDatabase;

    public function test_trip_day_event_can_be_created(): void
    {
        $place = Place::factory()->create();
        $tripDayCity = TripDayCity::factory()->create();

        $event = TripDayEvent::create([
            'trip_day_city_id' => $tripDayCity->id,
            'place_id' => $place->id,
            'start_time' => '10:00',
            'end_time' => '12:00',
            'order' => 1,
            'notes' => 'Visitar torre',
            'price' => 25.50,
            'currency' => 'EUR',
        ]);

        $this->assertDatabaseHas('trips_days_events', [
            'trip_day_city_id' => $tripDayCity->id,
            'place_id' => $place->id,
            'start_time' => '10:00',
        ]);
    }

    public function test_trip_day_event_belongs_to_place(): void
    {
        $event = TripDayEvent::factory()->create();

        $this->assertInstanceOf(Place::class, $event->place);
        $this->assertEquals($event->place_id, $event->place->id);
    }

    public function test_trip_day_event_belongs_to_city(): void
    {
        $event = TripDayEvent::factory()->create();

        $this->assertInstanceOf(TripDayCity::class, $event->city);
        $this->assertEquals($event->trip_day_city_id, $event->city->id);
    }

    public function test_trip_day_event_has_many_reviews(): void
    {
        $event = TripDayEvent::factory()->create();

        EventReview::factory()->count(3)->create([
            'trip_day_event_id' => $event->id,
            'place_id' => $event->place_id,
        ]);

        $this->assertEquals(3, $event->reviews()->count());
    }

    public function test_trip_day_event_calculates_average_rating(): void
    {
        $event = TripDayEvent::factory()->create();

        EventReview::factory()->create([
            'trip_day_event_id' => $event->id,
            'place_id' => $event->place_id,
            'rating' => 3,
        ]);

        EventReview::factory()->create([
            'trip_day_event_id' => $event->id,
            'place_id' => $event->place_id,
            'rating' => 5,
        ]);

        $this->assertEquals(4, $event->averageRating());
    }

    public function test_trip_day_event_only_stores_visit_data(): void
    {
        $place = Place::factory()->create([
            'name' => 'Torre Eiffel',
            'type' => 'attraction',
        ]);

        $event = TripDayEvent::factory()->create([
            'place_id' => $place->id,
            'notes' => 'Levar guarda-chuva',
            'price' => 30.00,
        ]);

        // Verifica que event não tem campos do place
        $this->assertArrayNotHasKey('name', $event->getAttributes());
        $this->assertArrayNotHasKey('type', $event->getAttributes());

        // Mas pode acessar via relacionamento
        $this->assertEquals('Torre Eiffel', $event->place->name);
        $this->assertEquals('attraction', $event->place->type);
    }

    public function test_trip_day_event_has_creator(): void
    {
        $user = User::factory()->create();
        $event = TripDayEvent::factory()->create([
            'created_by' => $user->id,
        ]);

        $this->assertInstanceOf(User::class, $event->creator);
        $this->assertEquals($user->id, $event->creator->id);
    }

    public function test_trip_day_event_has_updater(): void
    {
        $user = User::factory()->create();
        $event = TripDayEvent::factory()->create([
            'updated_by' => $user->id,
        ]);

        $this->assertInstanceOf(User::class, $event->updater);
        $this->assertEquals($user->id, $event->updater->id);
    }

    public function test_trip_day_event_time_fields_are_optional(): void
    {
        $event = TripDayEvent::factory()->create([
            'start_time' => null,
            'end_time' => null,
        ]);

        $this->assertNull($event->start_time);
        $this->assertNull($event->end_time);
    }

    public function test_trip_day_event_price_and_currency_are_optional(): void
    {
        $event = TripDayEvent::factory()->create([
            'price' => null,
            'currency' => null,
        ]);

        $this->assertNull($event->price);
        $this->assertNull($event->currency);
    }

    public function test_trip_day_event_can_be_updated(): void
    {
        $event = TripDayEvent::factory()->create([
            'start_time' => '10:00',
            'end_time' => '12:00',
            'notes' => 'Original notes',
            'price' => 20.00,
        ]);

        $event->update([
            'start_time' => '14:00',
            'end_time' => '16:00',
            'notes' => 'Updated notes',
            'price' => 25.00,
        ]);

        $this->assertEquals('14:00', $event->fresh()->start_time);
        $this->assertEquals('16:00', $event->fresh()->end_time);
        $this->assertEquals('Updated notes', $event->fresh()->notes);
        $this->assertEquals(25.00, $event->fresh()->price);
    }

    public function test_trip_day_event_times_can_be_updated(): void
    {
        $event = TripDayEvent::factory()->create([
            'start_time' => '09:00',
            'end_time' => '11:00',
        ]);

        $event->update([
            'start_time' => '10:00',
            'end_time' => '12:00',
        ]);

        $this->assertDatabaseHas('trips_days_events', [
            'id' => $event->id,
            'start_time' => '10:00',
            'end_time' => '12:00',
        ]);
    }

    public function test_trip_day_event_notes_can_be_updated(): void
    {
        $event = TripDayEvent::factory()->create([
            'notes' => 'Bring camera',
        ]);

        $event->update(['notes' => 'Bring camera and umbrella']);

        $this->assertEquals('Bring camera and umbrella', $event->fresh()->notes);
    }

    public function test_trip_day_event_price_can_be_updated(): void
    {
        $event = TripDayEvent::factory()->create([
            'price' => 15.00,
            'currency' => 'USD',
        ]);

        $event->update([
            'price' => 20.00,
            'currency' => 'EUR',
        ]);

        $this->assertEquals(20.00, $event->fresh()->price);
        $this->assertEquals('EUR', $event->fresh()->currency);
    }

    public function test_trip_day_event_order_can_be_updated(): void
    {
        $event = TripDayEvent::factory()->create(['order' => 1]);

        $event->update(['order' => 3]);

        $this->assertEquals(3, $event->fresh()->order);
    }

    public function test_trip_day_event_can_be_deleted(): void
    {
        $event = TripDayEvent::factory()->create();
        $eventId = $event->id;

        $event->delete();

        $this->assertDatabaseMissing('trips_days_events', ['id' => $eventId]);
    }

    public function test_deleting_trip_day_event_deletes_reviews(): void
    {
        $event = TripDayEvent::factory()->create();

        $review1 = EventReview::factory()->create([
            'trip_day_event_id' => $event->id,
            'place_id' => $event->place_id,
        ]);
        $review2 = EventReview::factory()->create([
            'trip_day_event_id' => $event->id,
            'place_id' => $event->place_id,
        ]);

        $event->delete();

        $this->assertEquals(0, EventReview::where('trip_day_event_id', $event->id)->count());
    }

    public function test_deleting_trip_day_city_deletes_all_events(): void
    {
        $city = TripDayCity::factory()->create();

        $event1 = TripDayEvent::factory()->create(['trip_day_city_id' => $city->id]);
        $event2 = TripDayEvent::factory()->create(['trip_day_city_id' => $city->id]);

        $city->delete();

        $this->assertDatabaseMissing('trips_days_events', ['id' => $event1->id]);
        $this->assertDatabaseMissing('trips_days_events', ['id' => $event2->id]);
    }

    public function test_deleting_place_does_not_delete_events(): void
    {
        $place = Place::factory()->create();
        $event = TripDayEvent::factory()->create(['place_id' => $place->id]);

        $eventId = $event->id;

        $place->delete();

        // Event should still exist but place_id might be null depending on migration
        $existingEvent = TripDayEvent::withoutGlobalScopes()->find($eventId);
        $this->assertNotNull($existingEvent);
    }
}
