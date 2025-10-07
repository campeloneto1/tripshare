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

    public function test_trip_day_can_be_updated(): void
    {
        $day = TripDay::factory()->create([
            'date' => '2025-06-01',
        ]);

        $day->update([
            'date' => '2025-06-02',
        ]);

        $this->assertEquals('2025-06-02', $day->fresh()->date->format('Y-m-d'));
    }

    public function test_trip_day_date_can_be_changed(): void
    {
        $day = TripDay::factory()->create([
            'date' => '2025-01-15',
        ]);

        $day->update(['date' => '2025-01-20']);

        $this->assertDatabaseHas('trips_days', [
            'id' => $day->id,
            'date' => '2025-01-20',
        ]);
    }

    public function test_trip_day_can_be_deleted(): void
    {
        $day = TripDay::factory()->create();
        $dayId = $day->id;

        $day->delete();

        $this->assertDatabaseMissing('trips_days', ['id' => $dayId]);
    }

    public function test_deleting_trip_day_deletes_cities(): void
    {
        $day = TripDay::factory()->create();

        $city1 = TripDayCity::factory()->create(['trip_day_id' => $day->id]);
        $city2 = TripDayCity::factory()->create(['trip_day_id' => $day->id]);

        $day->delete();

        $this->assertEquals(0, TripDayCity::where('trip_day_id', $day->id)->count());
    }

    public function test_deleting_trip_deletes_all_trip_days(): void
    {
        $trip = Trip::factory()->create();

        $day1 = TripDay::factory()->create(['trip_id' => $trip->id]);
        $day2 = TripDay::factory()->create(['trip_id' => $trip->id]);

        $trip->delete();

        $this->assertDatabaseMissing('trips_days', ['id' => $day1->id]);
        $this->assertDatabaseMissing('trips_days', ['id' => $day2->id]);
    }
}
