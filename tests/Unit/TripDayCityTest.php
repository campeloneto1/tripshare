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

    public function test_trip_day_city_can_be_updated(): void
    {
        $city = TripDayCity::factory()->create([
            'name' => 'Paris',
            'country' => 'France',
        ]);

        $city->update([
            'name' => 'Lyon',
            'country' => 'France',
        ]);

        $this->assertEquals('Lyon', $city->fresh()->name);
    }

    public function test_trip_day_city_name_can_be_changed(): void
    {
        $city = TripDayCity::factory()->create([
            'name' => 'Original City',
        ]);

        $city->update(['name' => 'Updated City']);

        $this->assertDatabaseHas('trips_days_cities', [
            'id' => $city->id,
            'name' => 'Updated City',
        ]);
    }

    public function test_trip_day_city_country_can_be_updated(): void
    {
        $city = TripDayCity::factory()->create([
            'name' => 'Barcelona',
            'country' => 'Spain',
        ]);

        $city->update(['country' => 'España']);

        $this->assertEquals('España', $city->fresh()->country);
    }

    public function test_trip_day_city_can_be_deleted(): void
    {
        $city = TripDayCity::factory()->create();
        $cityId = $city->id;

        $city->delete();

        $this->assertDatabaseMissing('trips_days_cities', ['id' => $cityId]);
    }

    public function test_deleting_trip_day_city_deletes_events(): void
    {
        $city = TripDayCity::factory()->create();

        $event1 = TripDayEvent::factory()->create(['trip_day_city_id' => $city->id]);
        $event2 = TripDayEvent::factory()->create(['trip_day_city_id' => $city->id]);

        $city->delete();

        $this->assertEquals(0, TripDayEvent::where('trip_day_city_id', $city->id)->count());
    }

    public function test_deleting_trip_day_deletes_all_cities(): void
    {
        $tripDay = TripDay::factory()->create();

        $city1 = TripDayCity::factory()->create(['trip_day_id' => $tripDay->id]);
        $city2 = TripDayCity::factory()->create(['trip_day_id' => $tripDay->id]);

        $tripDay->delete();

        $this->assertDatabaseMissing('trips_days_cities', ['id' => $city1->id]);
        $this->assertDatabaseMissing('trips_days_cities', ['id' => $city2->id]);
    }
}
