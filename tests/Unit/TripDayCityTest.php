<?php

namespace Tests\Unit;

use App\Models\TripDay;
use App\Models\TripDayCity;
use App\Models\TripDayEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TripDayCityTest extends TestCase
{
    use RefreshDatabase;

    public function test_trip_day_city_can_be_created(): void
    {
        $tripDay = TripDay::factory()->create();

        $city = TripDayCity::create([
            'trip_day_id' => $tripDay->id,
            'name' => 'Paris',
            'country' => 'France',
        ]);

        $this->assertDatabaseHas('trips_days_cities', [
            'trip_day_id' => $tripDay->id,
            'name' => 'Paris',
            'country' => 'France',
        ]);
    }

    public function test_trip_day_city_belongs_to_trip_day(): void
    {
        $city = TripDayCity::factory()->create();

        $this->assertInstanceOf(TripDay::class, $city->day);
        $this->assertEquals($city->trip_day_id, $city->day->id);
    }

    public function test_trip_day_city_has_many_events(): void
    {
        $city = TripDayCity::factory()->create();

        TripDayEvent::factory()->count(5)->create([
            'trip_day_city_id' => $city->id,
        ]);

        $this->assertEquals(5, $city->events()->count());
    }

    public function test_multiple_cities_for_same_day(): void
    {
        $tripDay = TripDay::factory()->create();

        TripDayCity::factory()->count(3)->create([
            'trip_day_id' => $tripDay->id,
        ]);

        $this->assertEquals(3, $tripDay->cities()->count());
    }

    public function test_trip_day_city_country_is_optional(): void
    {
        $city = TripDayCity::factory()->create([
            'country' => null,
        ]);

        $this->assertNull($city->country);
        $this->assertNotNull($city->name);
    }
}
