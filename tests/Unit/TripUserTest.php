<?php

namespace Tests\Unit;

use App\Models\Trip;
use App\Models\TripUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TripUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_trip_user_can_be_created(): void
    {
        $trip = Trip::factory()->create();
        $user = User::factory()->create();

        $tripUser = TripUser::create([
            'trip_id' => $trip->id,
            'user_id' => $user->id,
            'role' => 'participant',
        ]);

        $this->assertDatabaseHas('trips_users', [
            'trip_id' => $trip->id,
            'user_id' => $user->id,
            'role' => 'participant',
        ]);
    }

    public function test_trip_user_can_be_created_as_admin(): void
    {
        $trip = Trip::factory()->create();
        $user = User::factory()->create();

        $tripUser = TripUser::create([
            'trip_id' => $trip->id,
            'user_id' => $user->id,
            'role' => 'admin',
        ]);

        $this->assertEquals('admin', $tripUser->role);
    }

    public function test_trip_user_can_be_created_as_participant(): void
    {
        $trip = Trip::factory()->create();
        $user = User::factory()->create();

        $tripUser = TripUser::create([
            'trip_id' => $trip->id,
            'user_id' => $user->id,
            'role' => 'participant',
        ]);

        $this->assertEquals('participant', $tripUser->role);
    }

    public function test_trip_user_can_be_updated(): void
    {
        $trip = Trip::factory()->create();
        $user = User::factory()->create();

        $tripUser = TripUser::create([
            'trip_id' => $trip->id,
            'user_id' => $user->id,
            'role' => 'participant',
        ]);

        // Promove de participant para admin
        $tripUser->update(['role' => 'admin']);

        $this->assertEquals('admin', $tripUser->fresh()->role);
    }

    public function test_trip_user_can_be_deleted(): void
    {
        $trip = Trip::factory()->create();
        $user = User::factory()->create();

        $tripUser = TripUser::create([
            'trip_id' => $trip->id,
            'user_id' => $user->id,
            'role' => 'participant',
        ]);

        $tripUserId = $tripUser->id;
        $tripUser->delete();

        $this->assertDatabaseMissing('trips_users', ['id' => $tripUserId]);
    }

    public function test_trip_user_belongs_to_trip(): void
    {
        $trip = Trip::factory()->create();
        $user = User::factory()->create();

        $tripUser = TripUser::create([
            'trip_id' => $trip->id,
            'user_id' => $user->id,
            'role' => 'participant',
        ]);

        $this->assertInstanceOf(Trip::class, $tripUser->trip);
        $this->assertEquals($trip->id, $tripUser->trip->id);
    }

    public function test_trip_user_belongs_to_user(): void
    {
        $trip = Trip::factory()->create();
        $user = User::factory()->create();

        $tripUser = TripUser::create([
            'trip_id' => $trip->id,
            'user_id' => $user->id,
            'role' => 'participant',
        ]);

        $this->assertInstanceOf(User::class, $tripUser->user);
        $this->assertEquals($user->id, $tripUser->user->id);
    }

    public function test_trip_can_have_multiple_users_with_different_roles(): void
    {
        $trip = Trip::factory()->create();
        $admin = User::factory()->create();
        $participant1 = User::factory()->create();
        $participant2 = User::factory()->create();

        TripUser::create([
            'trip_id' => $trip->id,
            'user_id' => $admin->id,
            'role' => 'admin',
        ]);

        TripUser::create([
            'trip_id' => $trip->id,
            'user_id' => $participant1->id,
            'role' => 'participant',
        ]);

        TripUser::create([
            'trip_id' => $trip->id,
            'user_id' => $participant2->id,
            'role' => 'participant',
        ]);

        $this->assertEquals(3, $trip->users()->count());
        $this->assertEquals(1, $trip->users()->where('role', 'admin')->count());
        $this->assertEquals(2, $trip->users()->where('role', 'participant')->count());
    }

    public function test_user_can_participate_in_multiple_trips(): void
    {
        $user = User::factory()->create();
        $trip1 = Trip::factory()->create();
        $trip2 = Trip::factory()->create();

        TripUser::create([
            'trip_id' => $trip1->id,
            'user_id' => $user->id,
            'role' => 'participant',
        ]);

        TripUser::create([
            'trip_id' => $trip2->id,
            'user_id' => $user->id,
            'role' => 'admin',
        ]);

        $this->assertEquals(2, $user->tripsParticipating()->count());
    }

    public function test_trip_user_has_different_roles_in_different_trips(): void
    {
        $user = User::factory()->create();
        $trip1 = Trip::factory()->create();
        $trip2 = Trip::factory()->create();

        TripUser::create([
            'trip_id' => $trip1->id,
            'user_id' => $user->id,
            'role' => 'participant',
        ]);

        TripUser::create([
            'trip_id' => $trip2->id,
            'user_id' => $user->id,
            'role' => 'admin',
        ]);

        $tripUsers = TripUser::where('user_id', $user->id)->get();

        $this->assertEquals('participant', $tripUsers->firstWhere('trip_id', $trip1->id)->role);
        $this->assertEquals('admin', $tripUsers->firstWhere('trip_id', $trip2->id)->role);
    }
}
