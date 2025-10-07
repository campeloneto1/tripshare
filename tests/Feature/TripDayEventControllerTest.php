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
use Tests\TestCase;

class TripDayEventControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;
    protected User $admin;
    protected User $participant;
    protected User $outsider;
    protected Trip $trip;
    protected TripDayCity $city;
    protected Role $userRole;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRole = Role::factory()->create(['name' => 'User']);

        $this->owner = User::factory()->create(['role_id' => $this->userRole->id]);
        $this->admin = User::factory()->create(['role_id' => $this->userRole->id]);
        $this->participant = User::factory()->create(['role_id' => $this->userRole->id]);
        $this->outsider = User::factory()->create(['role_id' => $this->userRole->id]);

        $this->trip = Trip::factory()->create(['user_id' => $this->owner->id]);
        $tripDay = TripDay::factory()->create(['trip_id' => $this->trip->id]);
        $this->city = TripDayCity::factory()->create(['trip_day_id' => $tripDay->id]);

        $this->trip->users()->attach($this->admin->id, ['role' => 'admin']);
        $this->trip->users()->attach($this->participant->id, ['role' => 'participant']);
    }

    public function test_unauthenticated_user_cannot_create_event(): void
    {
        $place = Place::factory()->create();

        $data = [
            'trip_day_city_id' => $this->city->id,
            'place_id' => $place->id,
            'start_time' => '10:00',
            'end_time' => '12:00',
        ];

        $response = $this->postJson('/api/v1/trips/days/events', $data);

        $response->assertStatus(401);
    }

    public function test_owner_can_create_event(): void
    {
        $place = Place::factory()->create();

        $data = [
            'trip_day_city_id' => $this->city->id,
            'place_id' => $place->id,
            'start_time' => '10:00',
            'end_time' => '12:00',
            'notes' => 'Visitar torre',
        ];

        $response = $this->actingAs($this->owner, 'sanctum')
            ->postJson('/api/v1/trips/days/events', $data);

        $response->assertStatus(201);

        $this->assertDatabaseHas('trips_days_events', [
            'trip_day_city_id' => $this->city->id,
            'place_id' => $place->id,
        ]);
    }

    public function test_admin_can_create_event(): void
    {
        $place = Place::factory()->create();

        $data = [
            'trip_day_city_id' => $this->city->id,
            'place_id' => $place->id,
            'start_time' => '14:00',
            'end_time' => '16:00',
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/trips/days/events', $data);

        $response->assertStatus(201);
    }

    public function test_participant_cannot_create_event(): void
    {
        $place = Place::factory()->create();

        $data = [
            'trip_day_city_id' => $this->city->id,
            'place_id' => $place->id,
            'start_time' => '10:00',
        ];

        $response = $this->actingAs($this->participant, 'sanctum')
            ->postJson('/api/v1/trips/days/events', $data);

        $response->assertStatus(403);
    }

    public function test_outsider_cannot_create_event(): void
    {
        $place = Place::factory()->create();

        $data = [
            'trip_day_city_id' => $this->city->id,
            'place_id' => $place->id,
        ];

        $response = $this->actingAs($this->outsider, 'sanctum')
            ->postJson('/api/v1/trips/days/events', $data);

        $response->assertStatus(403);
    }

    public function test_create_event_with_xid_auto_creates_place(): void
    {
        $data = [
            'trip_day_city_id' => $this->city->id,
            'xid' => 'new_place_xid_123',
            'name' => 'Torre Eiffel',
            'type' => 'attraction',
            'lat' => 48.858370,
            'lon' => 2.294481,
            'start_time' => '10:00',
        ];

        $response = $this->actingAs($this->owner, 'sanctum')
            ->postJson('/api/v1/trips/days/events', $data);

        $response->assertStatus(201);

        // Verify place was created
        $this->assertDatabaseHas('places', [
            'xid' => 'new_place_xid_123',
            'name' => 'Torre Eiffel',
        ]);

        // Verify event references the place
        $place = Place::where('xid', 'new_place_xid_123')->first();
        $this->assertDatabaseHas('trips_days_events', [
            'trip_day_city_id' => $this->city->id,
            'place_id' => $place->id,
        ]);
    }

    public function test_create_event_with_existing_xid_reuses_place(): void
    {
        $existingPlace = Place::factory()->create(['xid' => 'existing_xid']);

        $data = [
            'trip_day_city_id' => $this->city->id,
            'xid' => 'existing_xid',
            'name' => 'Different Name', // Should be ignored
            'type' => 'attraction',
        ];

        $response = $this->actingAs($this->owner, 'sanctum')
            ->postJson('/api/v1/trips/days/events', $data);

        $response->assertStatus(201);

        // Should only have one place with this xid
        $this->assertEquals(1, Place::where('xid', 'existing_xid')->count());

        // Event should reference existing place
        $this->assertDatabaseHas('trips_days_events', [
            'trip_day_city_id' => $this->city->id,
            'place_id' => $existingPlace->id,
        ]);
    }

    public function test_owner_can_update_event(): void
    {
        $place = Place::factory()->create();
        $event = TripDayEvent::factory()->create([
            'trip_day_city_id' => $this->city->id,
            'place_id' => $place->id,
            'notes' => 'Original notes',
        ]);

        $updateData = [
            'notes' => 'Updated notes',
            'start_time' => '11:00',
        ];

        $response = $this->actingAs($this->owner, 'sanctum')
            ->putJson("/api/v1/trips/days/events/{$event->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonFragment(['notes' => 'Updated notes']);
    }

    public function test_admin_can_update_event(): void
    {
        $place = Place::factory()->create();
        $event = TripDayEvent::factory()->create([
            'trip_day_city_id' => $this->city->id,
            'place_id' => $place->id,
        ]);

        $updateData = ['notes' => 'Admin updated'];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/v1/trips/days/events/{$event->id}", $updateData);

        $response->assertStatus(200);
    }

    public function test_participant_cannot_update_event(): void
    {
        $place = Place::factory()->create();
        $event = TripDayEvent::factory()->create([
            'trip_day_city_id' => $this->city->id,
            'place_id' => $place->id,
        ]);

        $updateData = ['notes' => 'Participant trying to update'];

        $response = $this->actingAs($this->participant, 'sanctum')
            ->putJson("/api/v1/trips/days/events/{$event->id}", $updateData);

        $response->assertStatus(403);
    }

    public function test_owner_can_delete_event(): void
    {
        $place = Place::factory()->create();
        $event = TripDayEvent::factory()->create([
            'trip_day_city_id' => $this->city->id,
            'place_id' => $place->id,
        ]);

        $response = $this->actingAs($this->owner, 'sanctum')
            ->deleteJson("/api/v1/trips/days/events/{$event->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('trips_days_events', ['id' => $event->id]);
    }

    public function test_admin_can_delete_event(): void
    {
        $place = Place::factory()->create();
        $event = TripDayEvent::factory()->create([
            'trip_day_city_id' => $this->city->id,
            'place_id' => $place->id,
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->deleteJson("/api/v1/trips/days/events/{$event->id}");

        $response->assertStatus(204);
    }

    public function test_participant_cannot_delete_event(): void
    {
        $place = Place::factory()->create();
        $event = TripDayEvent::factory()->create([
            'trip_day_city_id' => $this->city->id,
            'place_id' => $place->id,
        ]);

        $response = $this->actingAs($this->participant, 'sanctum')
            ->deleteJson("/api/v1/trips/days/events/{$event->id}");

        $response->assertStatus(403);
    }

    public function test_anyone_can_view_event_from_public_trip(): void
    {
        $this->trip->update(['is_public' => true]);

        $place = Place::factory()->create();
        $event = TripDayEvent::factory()->create([
            'trip_day_city_id' => $this->city->id,
            'place_id' => $place->id,
        ]);

        $response = $this->actingAs($this->outsider, 'sanctum')
            ->getJson("/api/v1/trips/days/events/{$event->id}");

        $response->assertStatus(200);
    }

    public function test_outsider_cannot_view_event_from_private_trip(): void
    {
        $this->trip->update(['is_public' => false]);

        $place = Place::factory()->create();
        $event = TripDayEvent::factory()->create([
            'trip_day_city_id' => $this->city->id,
            'place_id' => $place->id,
        ]);

        $response = $this->actingAs($this->outsider, 'sanctum')
            ->getJson("/api/v1/trips/days/events/{$event->id}");

        $response->assertStatus(403);
    }

    public function test_can_list_events_for_city(): void
    {
        $place = Place::factory()->create();
        TripDayEvent::factory()->count(3)->create([
            'trip_day_city_id' => $this->city->id,
            'place_id' => $place->id,
        ]);

        $response = $this->actingAs($this->owner, 'sanctum')
            ->getJson("/api/v1/trips/days/events?trip_day_city_id={$this->city->id}");

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_validation_error_when_creating_without_place(): void
    {
        $data = [
            'trip_day_city_id' => $this->city->id,
            // Missing place_id or xid
        ];

        $response = $this->actingAs($this->owner, 'sanctum')
            ->postJson('/api/v1/trips/days/events', $data);

        $response->assertStatus(422);
    }
}
