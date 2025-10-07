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
}
