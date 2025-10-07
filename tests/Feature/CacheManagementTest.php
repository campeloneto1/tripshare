<?php

namespace Tests\Feature;

use App\Models\Place;
use App\Models\Role;
use App\Models\Trip;
use App\Models\TripDay;
use App\Models\TripDayCity;
use App\Models\TripDayEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CacheManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Trip $trip;
    protected Role $userRole;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRole = Role::factory()->create(['name' => 'User']);
        $this->user = User::factory()->create(['role_id' => $this->userRole->id]);
        $this->trip = Trip::factory()->create(['user_id' => $this->user->id]);
    }

    public function test_trip_summary_is_cached(): void
    {
        $day = TripDay::factory()->create(['trip_id' => $this->trip->id]);
        $city = TripDayCity::factory()->create(['trip_day_id' => $day->id]);
        $place = Place::factory()->create();
        TripDayEvent::factory()->create([
            'trip_day_city_id' => $city->id,
            'place_id' => $place->id,
        ]);

        // First call - should cache
        $summary1 = $this->trip->summary;

        // Verify cache key exists
        $cacheKey = "trip_summary_{$this->trip->id}";
        $this->assertTrue(Cache::has($cacheKey));

        // Second call - should use cache
        $summary2 = $this->trip->summary;

        $this->assertEquals($summary1, $summary2);
    }

    public function test_trip_summary_cache_contains_correct_data(): void
    {
        $day = TripDay::factory()->create(['trip_id' => $this->trip->id]);
        $city = TripDayCity::factory()->create(['trip_day_id' => $day->id]);
        $place = Place::factory()->create(['type' => 'restaurant']);
        TripDayEvent::factory()->create([
            'trip_day_city_id' => $city->id,
            'place_id' => $place->id,
            'price' => 50.00,
        ]);

        $summary = $this->trip->summary;

        $this->assertArrayHasKey('total_days', $summary);
        $this->assertArrayHasKey('total_cities', $summary);
        $this->assertArrayHasKey('total_events', $summary);
        $this->assertArrayHasKey('total_value', $summary);
        $this->assertArrayHasKey('total_events_by_type', $summary);
        $this->assertArrayHasKey('total_value_by_type', $summary);

        $this->assertEquals(1, $summary['total_days']);
        $this->assertEquals(1, $summary['total_cities']);
        $this->assertEquals(1, $summary['total_events']);
        $this->assertEquals(50.00, $summary['total_value']);
        $this->assertEquals(1, $summary['total_events_by_type']['restaurant']);
        $this->assertEquals(50.00, $summary['total_value_by_type']['restaurant']);
    }

    public function test_trip_summary_cache_is_cleared_on_update(): void
    {
        $day = TripDay::factory()->create(['trip_id' => $this->trip->id]);

        // Access summary to cache it
        $this->trip->summary;

        $cacheKey = "trip_summary_{$this->trip->id}";
        $this->assertTrue(Cache::has($cacheKey));

        // Update trip
        $this->trip->update(['name' => 'Updated Trip Name']);

        // Cache should be cleared
        $this->assertFalse(Cache::has($cacheKey));
    }

    public function test_trip_summary_cache_is_cleared_on_delete(): void
    {
        $day = TripDay::factory()->create(['trip_id' => $this->trip->id]);

        // Access summary to cache it
        $this->trip->summary;

        $cacheKey = "trip_summary_{$this->trip->id}";
        $this->assertTrue(Cache::has($cacheKey));

        // Delete trip
        $this->trip->delete();

        // Cache should be cleared
        $this->assertFalse(Cache::has($cacheKey));
    }

    public function test_trip_summary_cache_recalculates_after_clear(): void
    {
        $day = TripDay::factory()->create(['trip_id' => $this->trip->id]);
        $city = TripDayCity::factory()->create(['trip_day_id' => $day->id]);
        $place = Place::factory()->create();
        TripDayEvent::factory()->create([
            'trip_day_city_id' => $city->id,
            'place_id' => $place->id,
        ]);

        // Get initial summary
        $summary1 = $this->trip->summary;
        $this->assertEquals(1, $summary1['total_events']);

        // Clear cache manually
        $this->trip->clearSummaryCache();

        // Add more events
        TripDayEvent::factory()->create([
            'trip_day_city_id' => $city->id,
            'place_id' => $place->id,
        ]);

        // Refresh model to clear in-memory attributes
        $this->trip = $this->trip->fresh();

        // Get summary again - should recalculate
        $summary2 = $this->trip->summary;
        $this->assertEquals(2, $summary2['total_events']);
    }

    public function test_trip_summary_calculates_events_by_type(): void
    {
        $day = TripDay::factory()->create(['trip_id' => $this->trip->id]);
        $city = TripDayCity::factory()->create(['trip_day_id' => $day->id]);

        $hotel = Place::factory()->create(['type' => 'hotel']);
        $restaurant = Place::factory()->create(['type' => 'restaurant']);
        $attraction = Place::factory()->create(['type' => 'attraction']);

        TripDayEvent::factory()->create(['trip_day_city_id' => $city->id, 'place_id' => $hotel->id]);
        TripDayEvent::factory()->create(['trip_day_city_id' => $city->id, 'place_id' => $restaurant->id]);
        TripDayEvent::factory()->create(['trip_day_city_id' => $city->id, 'place_id' => $restaurant->id]);
        TripDayEvent::factory()->create(['trip_day_city_id' => $city->id, 'place_id' => $attraction->id]);

        $summary = $this->trip->summary;

        $this->assertEquals(1, $summary['total_events_by_type']['hotel']);
        $this->assertEquals(2, $summary['total_events_by_type']['restaurant']);
        $this->assertEquals(1, $summary['total_events_by_type']['attraction']);
        $this->assertEquals(0, $summary['total_events_by_type']['transport']);
    }

    public function test_trip_summary_calculates_value_by_type(): void
    {
        $day = TripDay::factory()->create(['trip_id' => $this->trip->id]);
        $city = TripDayCity::factory()->create(['trip_day_id' => $day->id]);

        $hotel = Place::factory()->create(['type' => 'hotel']);
        $restaurant = Place::factory()->create(['type' => 'restaurant']);

        TripDayEvent::factory()->create([
            'trip_day_city_id' => $city->id,
            'place_id' => $hotel->id,
            'price' => 100.00,
        ]);

        TripDayEvent::factory()->create([
            'trip_day_city_id' => $city->id,
            'place_id' => $restaurant->id,
            'price' => 50.00,
        ]);

        TripDayEvent::factory()->create([
            'trip_day_city_id' => $city->id,
            'place_id' => $restaurant->id,
            'price' => 30.00,
        ]);

        $summary = $this->trip->summary;

        $this->assertEquals(100.00, $summary['total_value_by_type']['hotel']);
        $this->assertEquals(80.00, $summary['total_value_by_type']['restaurant']);
        $this->assertEquals(180.00, $summary['total_value']);
    }

    public function test_trip_summary_handles_null_prices(): void
    {
        $day = TripDay::factory()->create(['trip_id' => $this->trip->id]);
        $city = TripDayCity::factory()->create(['trip_day_id' => $day->id]);
        $place = Place::factory()->create();

        TripDayEvent::factory()->create([
            'trip_day_city_id' => $city->id,
            'place_id' => $place->id,
            'price' => null,
        ]);

        $summary = $this->trip->summary;

        $this->assertEquals(0.00, $summary['total_value']);
    }

    public function test_trip_summary_counts_multiple_days_and_cities(): void
    {
        $day1 = TripDay::factory()->create(['trip_id' => $this->trip->id]);
        $day2 = TripDay::factory()->create(['trip_id' => $this->trip->id]);

        $city1 = TripDayCity::factory()->create(['trip_day_id' => $day1->id]);
        $city2 = TripDayCity::factory()->create(['trip_day_id' => $day1->id]);
        $city3 = TripDayCity::factory()->create(['trip_day_id' => $day2->id]);

        $summary = $this->trip->summary;

        $this->assertEquals(2, $summary['total_days']);
        $this->assertEquals(3, $summary['total_cities']);
    }

    public function test_cache_key_is_unique_per_trip(): void
    {
        $trip2 = Trip::factory()->create(['user_id' => $this->user->id]);

        $day1 = TripDay::factory()->create(['trip_id' => $this->trip->id]);
        $day2 = TripDay::factory()->create(['trip_id' => $trip2->id]);

        // Access both summaries
        $this->trip->summary;
        $trip2->summary;

        // Both should have separate cache keys
        $this->assertTrue(Cache::has("trip_summary_{$this->trip->id}"));
        $this->assertTrue(Cache::has("trip_summary_{$trip2->id}"));
    }

    public function test_clear_summary_cache_method_works(): void
    {
        $day = TripDay::factory()->create(['trip_id' => $this->trip->id]);

        // Access summary to cache it
        $this->trip->summary;

        $cacheKey = "trip_summary_{$this->trip->id}";
        $this->assertTrue(Cache::has($cacheKey));

        // Clear cache manually
        $this->trip->clearSummaryCache();

        // Cache should be cleared
        $this->assertFalse(Cache::has($cacheKey));
    }

    public function test_trip_summary_cache_expires_after_one_hour(): void
    {
        $day = TripDay::factory()->create(['trip_id' => $this->trip->id]);

        // Access summary to cache it
        $this->trip->summary;

        $cacheKey = "trip_summary_{$this->trip->id}";

        // Cache should exist now
        $this->assertTrue(Cache::has($cacheKey));

        // Travel 2 hours into the future
        $this->travel(2)->hours();

        // Cache should be expired
        $this->assertFalse(Cache::has($cacheKey));
    }

    public function test_trip_summary_handles_events_without_place(): void
    {
        $day = TripDay::factory()->create(['trip_id' => $this->trip->id]);
        $city = TripDayCity::factory()->create(['trip_day_id' => $day->id]);

        // Create event without place (edge case)
        TripDayEvent::factory()->create([
            'trip_day_city_id' => $city->id,
            'place_id' => null,
            'price' => 25.00,
        ]);

        $summary = $this->trip->summary;

        // Should count the event
        $this->assertEquals(1, $summary['total_events']);
        $this->assertEquals(25.00, $summary['total_value']);

        // Should categorize as 'other' since place is null
        $this->assertEquals(1, $summary['total_events_by_type']['other']);
    }

    public function test_multiple_trips_have_independent_caches(): void
    {
        $trip2 = Trip::factory()->create(['user_id' => $this->user->id]);

        $day1 = TripDay::factory()->create(['trip_id' => $this->trip->id]);
        $city1 = TripDayCity::factory()->create(['trip_day_id' => $day1->id]);
        $place1 = Place::factory()->create();
        TripDayEvent::factory()->create([
            'trip_day_city_id' => $city1->id,
            'place_id' => $place1->id,
        ]);

        $day2 = TripDay::factory()->create(['trip_id' => $trip2->id]);
        $city2 = TripDayCity::factory()->create(['trip_day_id' => $day2->id]);
        $place2 = Place::factory()->create();
        TripDayEvent::factory()->count(3)->create([
            'trip_day_city_id' => $city2->id,
            'place_id' => $place2->id,
        ]);

        $summary1 = $this->trip->summary;
        $summary2 = $trip2->summary;

        $this->assertEquals(1, $summary1['total_events']);
        $this->assertEquals(3, $summary2['total_events']);

        // Updating trip1 should not affect trip2's cache
        $this->trip->update(['name' => 'Updated']);

        $this->assertFalse(Cache::has("trip_summary_{$this->trip->id}"));
        $this->assertTrue(Cache::has("trip_summary_{$trip2->id}"));
    }

    public function test_trip_summary_cache_miss_then_hit_scenario(): void
    {
        $day = TripDay::factory()->create(['trip_id' => $this->trip->id]);
        $city = TripDayCity::factory()->create(['trip_day_id' => $day->id]);
        $place = Place::factory()->create();
        TripDayEvent::factory()->create([
            'trip_day_city_id' => $city->id,
            'place_id' => $place->id,
        ]);

        $cacheKey = "trip_summary_{$this->trip->id}";

        // Initial state - cache should not exist
        $this->assertFalse(Cache::has($cacheKey));

        // First access - cache miss, should calculate and cache
        $summary1 = $this->trip->summary;
        $this->assertTrue(Cache::has($cacheKey));

        // Second access - cache hit, should return cached value
        $summary2 = $this->trip->summary;
        $this->assertEquals($summary1, $summary2);
    }
}
