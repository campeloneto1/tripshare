<?php

namespace Tests\Unit;

use App\Models\Trip;
use App\Models\TripDay;
use App\Models\User;
use App\Services\TripService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TripBusinessLogicTest extends TestCase
{
    use RefreshDatabase;

    protected TripService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(TripService::class);
    }

    public function test_trip_auto_creates_trip_days_on_creation(): void
    {
        $user = User::factory()->create();

        $trip = Trip::create([
            'user_id' => $user->id,
            'name' => 'Viagem Europa',
            'description' => 'Minha viagem dos sonhos',
            'start_date' => '2025-06-01',
            'end_date' => '2025-06-05',
            'is_public' => true,
        ]);

        // Chama o serviço para criar os TripDays
        $this->service->createTripDays($trip);

        // Deve criar 5 dias (01, 02, 03, 04, 05)
        $this->assertEquals(5, $trip->days()->count());

        // Verifica se as datas estão corretas
        $days = $trip->days()->orderBy('date')->get();
        $this->assertEquals('2025-06-01', $days[0]->date->format('Y-m-d'));
        $this->assertEquals('2025-06-02', $days[1]->date->format('Y-m-d'));
        $this->assertEquals('2025-06-03', $days[2]->date->format('Y-m-d'));
        $this->assertEquals('2025-06-04', $days[3]->date->format('Y-m-d'));
        $this->assertEquals('2025-06-05', $days[4]->date->format('Y-m-d'));
    }

    public function test_trip_auto_creates_trip_days_for_single_day(): void
    {
        $user = User::factory()->create();

        $trip = Trip::create([
            'user_id' => $user->id,
            'name' => 'Viagem de 1 dia',
            'start_date' => '2025-06-01',
            'end_date' => '2025-06-01',
            'is_public' => true,
        ]);

        $this->service->createTripDays($trip);

        // Deve criar 1 dia
        $this->assertEquals(1, $trip->days()->count());
    }

    public function test_trip_auto_creates_trip_days_for_long_period(): void
    {
        $user = User::factory()->create();

        $trip = Trip::create([
            'user_id' => $user->id,
            'name' => 'Viagem longa',
            'start_date' => '2025-06-01',
            'end_date' => '2025-06-30',
            'is_public' => true,
        ]);

        $this->service->createTripDays($trip);

        // Deve criar 30 dias (junho tem 30 dias)
        $this->assertEquals(30, $trip->days()->count());
    }

    public function test_trip_summary_calculates_correctly(): void
    {
        $user = User::factory()->create();
        $trip = Trip::factory()->create(['user_id' => $user->id]);

        $tripDay = TripDay::factory()->create(['trip_id' => $trip->id]);

        $tripDayCity = \App\Models\TripDayCity::factory()->create([
            'trip_day_id' => $tripDay->id,
        ]);

        // Cria places de diferentes tipos
        $hotel = \App\Models\Place::factory()->create(['type' => 'hotel']);
        $restaurant = \App\Models\Place::factory()->create(['type' => 'restaurant']);
        $attraction = \App\Models\Place::factory()->create(['type' => 'attraction']);

        // Cria eventos com esses places
        \App\Models\TripDayEvent::factory()->create([
            'trip_day_city_id' => $tripDayCity->id,
            'place_id' => $hotel->id,
        ]);
        \App\Models\TripDayEvent::factory()->create([
            'trip_day_city_id' => $tripDayCity->id,
            'place_id' => $restaurant->id,
        ]);
        \App\Models\TripDayEvent::factory()->create([
            'trip_day_city_id' => $tripDayCity->id,
            'place_id' => $attraction->id,
        ]);

        $summary = $trip->summary;

        $this->assertEquals(1, $summary['total_events_by_type']['hotel']);
        $this->assertEquals(1, $summary['total_events_by_type']['restaurant']);
        $this->assertEquals(1, $summary['total_events_by_type']['attraction']);
    }

    public function test_trip_cache_clears_on_update(): void
    {
        $trip = Trip::factory()->create();

        // Acessa o summary para popular o cache
        $summary1 = $trip->summary;

        // Atualiza o trip
        $trip->update(['name' => 'Nome Atualizado']);

        // Acessa novamente - deve recalcular
        $summary2 = $trip->summary;

        $this->assertIsArray($summary1);
        $this->assertIsArray($summary2);
    }

    public function test_trip_soft_deletes(): void
    {
        $trip = Trip::factory()->create();
        $tripId = $trip->id;

        $trip->delete();

        $this->assertSoftDeleted('trips', ['id' => $tripId]);
    }

    public function test_trip_can_be_restored(): void
    {
        $trip = Trip::factory()->create();
        $trip->delete();

        $trip->restore();

        $this->assertDatabaseHas('trips', [
            'id' => $trip->id,
            'deleted_at' => null,
        ]);
    }

    public function test_public_trip_scope_filters_correctly(): void
    {
        Trip::factory()->create(['is_public' => true]);
        Trip::factory()->create(['is_public' => true]);
        Trip::factory()->create(['is_public' => false]);

        $publicTrips = Trip::public()->get();

        $this->assertEquals(2, $publicTrips->count());
    }

    public function test_for_user_scope_filters_correctly(): void
    {
        $user = User::factory()->create();
        Trip::factory()->count(3)->create(['user_id' => $user->id]);
        Trip::factory()->count(2)->create();

        $userTrips = Trip::forUser($user->id)->get();

        $this->assertEquals(3, $userTrips->count());
    }

    public function test_active_trip_scope_filters_correctly(): void
    {
        // Trip ativa (não deletada)
        Trip::factory()->create();

        // Trip deletada
        $deletedTrip = Trip::factory()->create();
        $deletedTrip->delete();

        $activeTrips = Trip::active()->get();

        $this->assertEquals(1, $activeTrips->count());
    }
}
