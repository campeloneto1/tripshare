<?php

namespace Tests\Unit;

use App\Models\Trip;
use App\Models\TripDay;
use App\Models\TripDayCity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TripDayTest extends TestCase
{
    use RefreshDatabase;

    public function test_trip_day_can_be_created(): void
    {
        $trip = Trip::factory()->create();

        $day = TripDay::create([
            'trip_id' => $trip->id,
            'date' => '2025-06-01',
        ]);

        $this->assertDatabaseHas('trips_days', [
            'trip_id' => $trip->id,
            'date' => '2025-06-01',
        ]);
    }

    public function test_trip_day_belongs_to_trip(): void
    {
        $day = TripDay::factory()->create();

        $this->assertInstanceOf(Trip::class, $day->trip);
        $this->assertEquals($day->trip_id, $day->trip->id);
    }

    public function test_trip_day_has_many_cities(): void
    {
        $day = TripDay::factory()->create();

        TripDayCity::factory()->count(3)->create([
            'trip_day_id' => $day->id,
        ]);

        $this->assertEquals(3, $day->cities()->count());
    }

    public function test_trip_day_date_is_cast_to_date(): void
    {
        $day = TripDay::factory()->create([
            'date' => '2025-06-01',
        ]);

        $this->assertInstanceOf(\DateTime::class, $day->date);
    }

    public function test_trip_day_has_creator(): void
    {
        $creator = User::factory()->create();
        $day = TripDay::factory()->create([
            'created_by' => $creator->id,
        ]);

        $this->assertInstanceOf(User::class, $day->creator);
        $this->assertEquals($creator->id, $day->creator->id);
    }

    public function test_trip_day_clears_trip_cache_on_save(): void
    {
        $day = TripDay::factory()->create();

        // Acessa summary para criar cache
        $summary = $day->trip->summary;

        // Atualiza day
        $day->update(['date' => now()->addDay()]);

        // Cache do trip deve ter sido limpo
        $this->assertNotEquals($summary, $day->trip->fresh()->summary);
    }
}
