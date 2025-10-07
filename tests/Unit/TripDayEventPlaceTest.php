<?php

namespace Tests\Unit;

use App\Models\Place;
use App\Models\TripDayCity;
use App\Models\TripDayEvent;
use App\Repositories\TripDayEventRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TripDayEventPlaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_trip_day_event_belongs_to_place(): void
    {
        $place = Place::factory()->create();
        $event = TripDayEvent::factory()->create([
            'place_id' => $place->id,
        ]);

        $this->assertInstanceOf(Place::class, $event->place);
        $this->assertEquals($place->id, $event->place->id);
    }

    public function test_repository_creates_place_automatically_with_xid(): void
    {
        $tripDayCity = TripDayCity::factory()->create();
        $repository = app(TripDayEventRepository::class);

        $eventData = [
            'trip_day_city_id' => $tripDayCity->id,
            'xid' => 'auto_place_xid',
            'name' => 'Auto Created Place',
            'type' => 'attraction',
            'lat' => 48.858370,
            'lon' => 2.294481,
            'start_time' => '10:00',
            'notes' => 'Test notes',
        ];

        $event = $repository->create($eventData);

        // Verifica que place foi criado
        $this->assertDatabaseHas('places', [
            'xid' => 'auto_place_xid',
            'name' => 'Auto Created Place',
        ]);

        // Verifica que event tem place_id
        $this->assertNotNull($event->place_id);
        $this->assertEquals('Auto Created Place', $event->place->name);

        // Verifica que campos do place não estão no evento
        $this->assertDatabaseMissing('trips_days_events', [
            'name' => 'Auto Created Place',
        ]);
    }

    public function test_repository_reuses_existing_place(): void
    {
        $tripDayCity = TripDayCity::factory()->create();
        $repository = app(TripDayEventRepository::class);

        // Cria place existente
        $existingPlace = Place::create([
            'xid' => 'reuse_xid',
            'name' => 'Existing Place',
            'type' => 'restaurant',
        ]);

        $eventData = [
            'trip_day_city_id' => $tripDayCity->id,
            'xid' => 'reuse_xid',
            'name' => 'Different Name',
            'type' => 'attraction',
            'start_time' => '14:00',
        ];

        $event = $repository->create($eventData);

        // Verifica que usou o place existente
        $this->assertEquals($existingPlace->id, $event->place_id);

        // Verifica que não duplicou place
        $this->assertEquals(1, Place::where('xid', 'reuse_xid')->count());
    }

    public function test_repository_updates_place_when_updating_event(): void
    {
        $place = Place::factory()->create(['xid' => 'update_xid']);
        $event = TripDayEvent::factory()->create([
            'place_id' => $place->id,
        ]);

        $repository = app(TripDayEventRepository::class);

        // Atualiza com novo place
        $updatedEvent = $repository->update($event, [
            'xid' => 'new_place_xid',
            'name' => 'New Place',
            'type' => 'hotel',
            'notes' => 'Updated notes',
        ]);

        // Verifica que novo place foi criado
        $this->assertDatabaseHas('places', [
            'xid' => 'new_place_xid',
            'name' => 'New Place',
        ]);

        // Verifica que event aponta pro novo place
        $newPlace = Place::where('xid', 'new_place_xid')->first();
        $this->assertEquals($newPlace->id, $updatedEvent->place_id);
    }

    public function test_event_can_access_place_data_via_relationship(): void
    {
        $place = Place::factory()->create([
            'name' => 'Torre Eiffel',
            'type' => 'attraction',
            'city' => 'Paris',
        ]);

        $event = TripDayEvent::factory()->create([
            'place_id' => $place->id,
            'start_time' => '09:00',
            'notes' => 'Visit in the morning',
        ]);

        $event->load('place');

        $this->assertEquals('Torre Eiffel', $event->place->name);
        $this->assertEquals('attraction', $event->place->type);
        $this->assertEquals('Paris', $event->place->city);
        $this->assertEquals('09:00', $event->start_time);
    }
}
