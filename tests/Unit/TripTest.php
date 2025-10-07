<?php

namespace Tests\Unit;

use App\Models\Trip;
use App\Models\TripDay;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TripTest extends TestCase
{
    use RefreshDatabase;

    public function test_trip_can_be_created(): void
    {
        $user = User::factory()->create();

        $trip = Trip::create([
            'user_id' => $user->id,
            'name' => 'Viagem Europa',
            'description' => 'Minha viagem dos sonhos',
            'start_date' => '2025-06-01',
            'end_date' => '2025-06-15',
            'is_public' => true,
        ]);

        $this->assertDatabaseHas('trips', [
            'user_id' => $user->id,
            'name' => 'Viagem Europa',
        ]);
    }

    public function test_trip_belongs_to_user(): void
    {
        $trip = Trip::factory()->create();

        $this->assertInstanceOf(User::class, $trip->user);
        $this->assertEquals($trip->user_id, $trip->user->id);
    }

    public function test_trip_has_many_days(): void
    {
        $trip = Trip::factory()->create();

        TripDay::factory()->count(5)->create([
            'trip_id' => $trip->id,
        ]);

        $this->assertEquals(5, $trip->days()->count());
    }

    public function test_trip_belongs_to_many_users(): void
    {
        $trip = Trip::factory()->create();
        $users = User::factory()->count(3)->create();

        $trip->users()->attach($users->pluck('id'), ['role' => 'participant']);

        $this->assertEquals(3, $trip->users()->count());
    }

    public function test_trip_dates_are_cast_to_date(): void
    {
        $trip = Trip::factory()->create([
            'start_date' => '2025-06-01',
            'end_date' => '2025-06-15',
        ]);

        $this->assertInstanceOf(\DateTime::class, $trip->start_date);
        $this->assertInstanceOf(\DateTime::class, $trip->end_date);
    }

    public function test_trip_is_public_is_cast_to_boolean(): void
    {
        $trip = Trip::factory()->create([
            'is_public' => 1,
        ]);

        $this->assertIsBool($trip->is_public);
        $this->assertTrue($trip->is_public);
    }

    public function test_trip_soft_deletes(): void
    {
        $trip = Trip::factory()->create();
        $tripId = $trip->id;

        $trip->delete();

        $this->assertSoftDeleted('trips', ['id' => $tripId]);
    }

    public function test_trip_scope_public(): void
    {
        Trip::factory()->count(3)->create(['is_public' => true]);
        Trip::factory()->count(2)->create(['is_public' => false]);

        $publicTrips = Trip::public()->get();

        $this->assertEquals(3, $publicTrips->count());
    }

    public function test_trip_scope_for_user(): void
    {
        $user = User::factory()->create();
        Trip::factory()->count(3)->create(['user_id' => $user->id]);
        Trip::factory()->count(2)->create();

        $userTrips = Trip::forUser($user->id)->get();

        $this->assertEquals(3, $userTrips->count());
    }

    public function test_trip_scope_active(): void
    {
        Trip::factory()->create(['end_date' => now()->addDays(5)]);
        Trip::factory()->create(['end_date' => now()->subDays(5)]);

        $activeTrips = Trip::active()->get();

        $this->assertEquals(1, $activeTrips->count());
    }

    public function test_trip_has_creator(): void
    {
        $creator = User::factory()->create();
        $trip = Trip::factory()->create([
            'created_by' => $creator->id,
        ]);

        $this->assertInstanceOf(User::class, $trip->creator);
        $this->assertEquals($creator->id, $trip->creator->id);
    }

    public function test_trip_has_updater(): void
    {
        $updater = User::factory()->create();
        $trip = Trip::factory()->create([
            'updated_by' => $updater->id,
        ]);

        $this->assertInstanceOf(User::class, $trip->updater);
        $this->assertEquals($updater->id, $trip->updater->id);
    }

    public function test_trip_clears_cache_on_update(): void
    {
        $trip = Trip::factory()->create();

        // Acessa summary para criar cache
        $summary = $trip->summary;

        // Atualiza trip
        $trip->update(['name' => 'Novo Nome']);

        // Cache deve ter sido limpo
        $this->assertNotEquals($summary, $trip->fresh()->summary);
    }

    public function test_trip_description_is_optional(): void
    {
        $trip = Trip::factory()->create([
            'description' => null,
        ]);

        $this->assertNull($trip->description);
    }
}
