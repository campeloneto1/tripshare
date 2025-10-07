<?php

namespace Tests\Feature;

use App\Models\Place;
use App\Models\Post;
use App\Models\Role;
use App\Models\Trip;
use App\Models\TripDay;
use App\Models\TripDayCity;
use App\Models\TripDayEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SoftDeleteRestoreTest extends TestCase
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

    // ==================== TRIP SOFT DELETE TESTS ====================

    public function test_trip_can_be_soft_deleted(): void
    {
        $tripId = $this->trip->id;

        $this->trip->delete();

        $this->assertSoftDeleted('trips', ['id' => $tripId]);
        $this->assertNotNull($this->trip->fresh()->deleted_at);
    }

    public function test_soft_deleted_trip_is_excluded_from_normal_queries(): void
    {
        $this->trip->delete();

        $trips = Trip::all();

        $this->assertFalse($trips->contains($this->trip));
        $this->assertEquals(0, Trip::count());
    }

    public function test_soft_deleted_trip_can_be_retrieved_with_trashed_scope(): void
    {
        $this->trip->delete();

        $trashedTrips = Trip::onlyTrashed()->get();

        $this->assertTrue($trashedTrips->contains('id', $this->trip->id));
        $this->assertEquals(1, Trip::onlyTrashed()->count());
    }

    public function test_soft_deleted_trip_can_be_restored(): void
    {
        $this->trip->delete();
        $this->assertSoftDeleted('trips', ['id' => $this->trip->id]);

        $this->trip->restore();

        $this->assertDatabaseHas('trips', [
            'id' => $this->trip->id,
            'deleted_at' => null,
        ]);

        $this->assertNull($this->trip->fresh()->deleted_at);
    }

    public function test_restored_trip_appears_in_normal_queries(): void
    {
        $this->trip->delete();
        $this->assertEquals(0, Trip::count());

        $this->trip->restore();

        $trips = Trip::all();
        $this->assertTrue($trips->contains('id', $this->trip->id));
        $this->assertEquals(1, Trip::count());
    }

    public function test_trip_can_be_force_deleted(): void
    {
        $tripId = $this->trip->id;

        $this->trip->forceDelete();

        $this->assertDatabaseMissing('trips', ['id' => $tripId]);
        $this->assertEquals(0, Trip::withTrashed()->where('id', $tripId)->count());
    }

    public function test_soft_deleted_trip_preserves_relationships(): void
    {
        $day = TripDay::factory()->create(['trip_id' => $this->trip->id]);
        $city = TripDayCity::factory()->create(['trip_day_id' => $day->id]);
        $place = Place::factory()->create();
        $event = TripDayEvent::factory()->create([
            'trip_day_city_id' => $city->id,
            'place_id' => $place->id,
        ]);

        $this->trip->delete();

        // Related records should still exist
        $this->assertDatabaseHas('trips_days', ['id' => $day->id]);
        $this->assertDatabaseHas('trips_days_cities', ['id' => $city->id]);
        $this->assertDatabaseHas('trips_days_events', ['id' => $event->id]);
    }

    public function test_restored_trip_has_working_relationships(): void
    {
        $day = TripDay::factory()->create(['trip_id' => $this->trip->id]);
        $city = TripDayCity::factory()->create(['trip_day_id' => $day->id]);

        $this->trip->delete();
        $this->trip->restore();

        $restoredTrip = Trip::find($this->trip->id);

        $this->assertEquals(1, $restoredTrip->days->count());
        $this->assertEquals($day->id, $restoredTrip->days->first()->id);
    }

    public function test_with_trashed_scope_includes_both_active_and_deleted(): void
    {
        $trip2 = Trip::factory()->create(['user_id' => $this->user->id]);
        $trip2->delete();

        $allTrips = Trip::withTrashed()->get();

        $this->assertEquals(2, $allTrips->count());
        $this->assertTrue($allTrips->contains('id', $this->trip->id));
        $this->assertTrue($allTrips->contains('id', $trip2->id));
    }

    // ==================== USER SOFT DELETE TESTS ====================

    public function test_user_can_be_soft_deleted(): void
    {
        $userId = $this->user->id;

        $this->user->delete();

        $this->assertSoftDeleted('users', ['id' => $userId]);
        $this->assertNotNull($this->user->fresh()->deleted_at);
    }

    public function test_soft_deleted_user_is_excluded_from_normal_queries(): void
    {
        $this->user->delete();

        $users = User::all();

        $this->assertFalse($users->contains('id', $this->user->id));
    }

    public function test_soft_deleted_user_can_be_restored(): void
    {
        $this->user->delete();
        $this->assertSoftDeleted('users', ['id' => $this->user->id]);

        $this->user->restore();

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'deleted_at' => null,
        ]);
    }

    public function test_soft_deleted_user_preserves_trips(): void
    {
        $trip = Trip::factory()->create(['user_id' => $this->user->id]);

        $this->user->delete();

        // Trip should still exist
        $this->assertDatabaseHas('trips', ['id' => $trip->id]);
    }

    public function test_soft_deleted_user_preserves_posts(): void
    {
        $post = Post::factory()->create(['user_id' => $this->user->id]);

        $this->user->delete();

        // Post should still exist
        $this->assertDatabaseHas('posts', ['id' => $post->id]);
    }

    public function test_restored_user_has_access_to_trips(): void
    {
        $trip = Trip::factory()->create(['user_id' => $this->user->id]);

        $this->user->delete();
        $this->user->restore();

        $restoredUser = User::find($this->user->id);

        $this->assertEquals(1, $restoredUser->trips->count());
        $this->assertEquals($trip->id, $restoredUser->trips->first()->id);
    }

    public function test_user_can_be_force_deleted(): void
    {
        $userId = $this->user->id;

        $this->user->forceDelete();

        $this->assertDatabaseMissing('users', ['id' => $userId]);
        $this->assertEquals(0, User::withTrashed()->where('id', $userId)->count());
    }

    // ==================== INTEGRATION TESTS ====================

    public function test_deleting_user_then_trip_both_can_be_restored(): void
    {
        $this->user->delete();
        $this->trip->delete();

        $this->assertSoftDeleted('users', ['id' => $this->user->id]);
        $this->assertSoftDeleted('trips', ['id' => $this->trip->id]);

        $this->user->restore();
        $this->trip->restore();

        $this->assertEquals(1, User::count());
        $this->assertEquals(1, Trip::count());
    }

    public function test_soft_deleted_trip_does_not_appear_in_user_relationship(): void
    {
        $trip1 = Trip::factory()->create(['user_id' => $this->user->id]);
        $trip2 = Trip::factory()->create(['user_id' => $this->user->id]);

        $trip1->delete();

        // Refresh user to reload relationships
        $this->user->refresh();

        // Only non-deleted trip should appear
        $this->assertEquals(1, $this->user->trips->count());
        $this->assertEquals($trip2->id, $this->user->trips->first()->id);
    }

    public function test_soft_deleted_trip_appears_in_user_relationship_with_trashed(): void
    {
        $trip1 = Trip::factory()->create(['user_id' => $this->user->id]);
        $trip2 = Trip::factory()->create(['user_id' => $this->user->id]);

        $trip1->delete();

        // Access trips including trashed
        $allTrips = $this->user->trips()->withTrashed()->get();

        $this->assertEquals(2, $allTrips->count());
    }

    public function test_multiple_trips_can_be_soft_deleted_and_restored_independently(): void
    {
        $trip1 = Trip::factory()->create(['user_id' => $this->user->id]);
        $trip2 = Trip::factory()->create(['user_id' => $this->user->id]);
        $trip3 = Trip::factory()->create(['user_id' => $this->user->id]);

        $trip1->delete();
        $trip3->delete();

        $this->assertEquals(1, Trip::count());
        $this->assertEquals(2, Trip::onlyTrashed()->count());

        $trip1->restore();

        $this->assertEquals(2, Trip::count());
        $this->assertEquals(1, Trip::onlyTrashed()->count());
    }

    public function test_trashed_method_returns_true_for_deleted_models(): void
    {
        $this->assertFalse($this->trip->trashed());

        $this->trip->delete();

        $this->assertTrue($this->trip->trashed());

        $this->trip->restore();

        $this->assertFalse($this->trip->trashed());
    }

    public function test_soft_delete_sets_deleted_at_timestamp(): void
    {
        $this->assertNull($this->trip->deleted_at);

        $beforeDelete = now();
        $this->trip->delete();
        $afterDelete = now();

        $this->assertNotNull($this->trip->deleted_at);
        $this->assertTrue($this->trip->deleted_at->between($beforeDelete, $afterDelete));
    }

    public function test_restore_clears_deleted_at_timestamp(): void
    {
        $this->trip->delete();
        $this->assertNotNull($this->trip->deleted_at);

        $this->trip->restore();

        $this->assertNull($this->trip->fresh()->deleted_at);
    }

    public function test_can_query_trips_deleted_after_specific_date(): void
    {
        $trip1 = Trip::factory()->create(['user_id' => $this->user->id]);
        $trip2 = Trip::factory()->create(['user_id' => $this->user->id]);

        $trip1->delete();

        $this->travel(2)->days();

        $trip2->delete();

        $recentlyDeleted = Trip::onlyTrashed()
            ->where('deleted_at', '>', now()->subDay())
            ->get();

        $this->assertEquals(1, $recentlyDeleted->count());
        $this->assertEquals($trip2->id, $recentlyDeleted->first()->id);
    }

    public function test_force_delete_removes_trip_and_cascades_to_days(): void
    {
        $day = TripDay::factory()->create(['trip_id' => $this->trip->id]);
        $city = TripDayCity::factory()->create(['trip_day_id' => $day->id]);

        $this->trip->forceDelete();

        $this->assertDatabaseMissing('trips', ['id' => $this->trip->id]);
        $this->assertDatabaseMissing('trips_days', ['id' => $day->id]);
        $this->assertDatabaseMissing('trips_days_cities', ['id' => $city->id]);
    }

    public function test_restoring_user_allows_authentication(): void
    {
        $this->user->delete();

        // Try to authenticate - should fail
        $authUser = User::where('email', $this->user->email)->first();
        $this->assertNull($authUser);

        $this->user->restore();

        // Try to authenticate - should succeed
        $authUser = User::where('email', $this->user->email)->first();
        $this->assertNotNull($authUser);
        $this->assertEquals($this->user->id, $authUser->id);
    }

    public function test_soft_deleted_trip_cache_is_cleared(): void
    {
        $day = TripDay::factory()->create(['trip_id' => $this->trip->id]);

        // Access summary to cache it
        $this->trip->summary;

        $cacheKey = "trip_summary_{$this->trip->id}";
        $this->assertTrue(\Cache::has($cacheKey));

        // Soft delete should clear cache
        $this->trip->delete();

        $this->assertFalse(\Cache::has($cacheKey));
    }
}
