<?php

namespace Tests\Feature;

use App\Models\Trip;
use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TripControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $otherUser;
    protected Role $userRole;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        $this->userRole = Role::factory()->create(['name' => 'User']);

        // Create users
        $this->user = User::factory()->create(['role_id' => $this->userRole->id]);
        $this->otherUser = User::factory()->create(['role_id' => $this->userRole->id]);
    }

    /**
     * Test: Unauthenticated user cannot access trip index
     */
    public function test_unauthenticated_user_cannot_access_trips_index(): void
    {
        $response = $this->getJson('/api/v1/trips');

        $response->assertStatus(401);
    }

    /**
     * Test: Authenticated user can list trips
     */
    public function test_authenticated_user_can_list_trips(): void
    {
        $trip = Trip::factory()->create([
            'user_id' => $this->user->id,
            'is_public' => true
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/trips');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'description',
                        'start_date',
                        'end_date',
                        'is_public',
                    ]
                ]
            ]);
    }

    /**
     * Test: Authenticated user can filter trips by search
     */
    public function test_authenticated_user_can_filter_trips_by_search(): void
    {
        Trip::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Trip to Paris',
            'is_public' => true
        ]);

        Trip::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Trip to Tokyo',
            'is_public' => true
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/trips?search=Paris');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['name' => 'Trip to Paris']);
    }

    /**
     * Test: Unauthenticated user cannot view specific trip
     */
    public function test_unauthenticated_user_cannot_view_trip(): void
    {
        $trip = Trip::factory()->create(['user_id' => $this->user->id]);

        $response = $this->getJson("/api/v1/trips/{$trip->id}");

        $response->assertStatus(401);
    }

    /**
     * Test: Authenticated user can view their own trip
     */
    public function test_authenticated_user_can_view_their_own_trip(): void
    {
        $trip = Trip::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/trips/{$trip->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'description',
                    'start_date',
                    'end_date',
                    'is_public',
                ]
            ])
            ->assertJsonFragment(['id' => $trip->id]);
    }

    /**
     * Test: Authenticated user can view public trip
     */
    public function test_authenticated_user_can_view_public_trip(): void
    {
        $trip = Trip::factory()->create([
            'user_id' => $this->otherUser->id,
            'is_public' => true
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/trips/{$trip->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $trip->id]);
    }

    /**
     * Test: Authenticated user cannot view private trip of another user
     */
    public function test_authenticated_user_cannot_view_private_trip_of_another_user(): void
    {
        $trip = Trip::factory()->create([
            'user_id' => $this->otherUser->id,
            'is_public' => false
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/trips/{$trip->id}");

        $response->assertStatus(403);
    }

    /**
     * Test: Returns 404 for non-existent trip
     */
    public function test_returns_404_for_non_existent_trip(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/trips/99999');

        $response->assertStatus(404);
    }

    /**
     * Test: Unauthenticated user cannot create trip
     */
    public function test_unauthenticated_user_cannot_create_trip(): void
    {
        $tripData = [
            'name' => 'New Trip',
            'description' => 'Trip description',
            'start_date' => now()->addDays(1)->format('Y-m-d'),
            'end_date' => now()->addDays(5)->format('Y-m-d'),
            'is_public' => true,
        ];

        $response = $this->postJson('/api/v1/trips', $tripData);

        $response->assertStatus(401);
    }

    /**
     * Test: Authenticated user can create trip
     */
    public function test_authenticated_user_can_create_trip(): void
    {
        $tripData = [
            'name' => 'New Trip',
            'description' => 'Trip description',
            'start_date' => now()->addDays(1)->format('Y-m-d'),
            'end_date' => now()->addDays(5)->format('Y-m-d'),
            'is_public' => true,
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/trips', $tripData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'name',
                    'description',
                    'start_date',
                    'end_date',
                    'is_public',
                ]
            ])
            ->assertJsonFragment([
                'message' => 'Viagem cadastrada com sucesso',
                'name' => 'New Trip'
            ]);

        $this->assertDatabaseHas('trips', [
            'name' => 'New Trip',
            'user_id' => $this->user->id,
        ]);
    }

    /**
     * Test: Validation error when creating trip with missing name
     */
    public function test_validation_error_when_creating_trip_without_name(): void
    {
        $tripData = [
            'description' => 'Trip description',
            'start_date' => now()->addDays(1)->format('Y-m-d'),
            'end_date' => now()->addDays(5)->format('Y-m-d'),
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/trips', $tripData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /**
     * Test: Validation error when creating trip with invalid dates
     */
    public function test_validation_error_when_creating_trip_with_invalid_dates(): void
    {
        $tripData = [
            'name' => 'New Trip',
            'description' => 'Trip description',
            'start_date' => now()->addDays(5)->format('Y-m-d'),
            'end_date' => now()->addDays(1)->format('Y-m-d'), // End date before start date
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/trips', $tripData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['end_date']);
    }

    /**
     * Test: Unauthenticated user cannot update trip
     */
    public function test_unauthenticated_user_cannot_update_trip(): void
    {
        $trip = Trip::factory()->create(['user_id' => $this->user->id]);

        $updateData = [
            'name' => 'Updated Trip Name',
        ];

        $response = $this->putJson("/api/v1/trips/{$trip->id}", $updateData);

        $response->assertStatus(401);
    }

    /**
     * Test: Authenticated user can update their own trip
     */
    public function test_authenticated_user_can_update_their_own_trip(): void
    {
        $trip = Trip::factory()->create(['user_id' => $this->user->id]);

        $updateData = [
            'name' => 'Updated Trip Name',
            'description' => 'Updated description',
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/v1/trips/{$trip->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'message' => 'Viagem atualizada com sucesso',
                'name' => 'Updated Trip Name',
            ]);

        $this->assertDatabaseHas('trips', [
            'id' => $trip->id,
            'name' => 'Updated Trip Name',
        ]);
    }

    /**
     * Test: Authenticated user cannot update another user's trip
     */
    public function test_authenticated_user_cannot_update_another_users_trip(): void
    {
        $trip = Trip::factory()->create(['user_id' => $this->otherUser->id]);

        $updateData = [
            'name' => 'Updated Trip Name',
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/v1/trips/{$trip->id}", $updateData);

        $response->assertStatus(403);
    }

    /**
     * Test: Validation error when updating trip with invalid data
     */
    public function test_validation_error_when_updating_trip_with_invalid_data(): void
    {
        $trip = Trip::factory()->create(['user_id' => $this->user->id]);

        $updateData = [
            'name' => '', // Empty name
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/v1/trips/{$trip->id}", $updateData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /**
     * Test: Unauthenticated user cannot delete trip
     */
    public function test_unauthenticated_user_cannot_delete_trip(): void
    {
        $trip = Trip::factory()->create(['user_id' => $this->user->id]);

        $response = $this->deleteJson("/api/v1/trips/{$trip->id}");

        $response->assertStatus(401);
    }

    /**
     * Test: Authenticated user can delete their own trip
     */
    public function test_authenticated_user_can_delete_their_own_trip(): void
    {
        $trip = Trip::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/trips/{$trip->id}");

        $response->assertStatus(204);

        $this->assertSoftDeleted('trips', [
            'id' => $trip->id,
        ]);
    }

    /**
     * Test: Authenticated user cannot delete another user's trip
     */
    public function test_authenticated_user_cannot_delete_another_users_trip(): void
    {
        $trip = Trip::factory()->create(['user_id' => $this->otherUser->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/trips/{$trip->id}");

        $response->assertStatus(403);
    }

    /**
     * Test: Trip member with admin role can update trip
     */
    public function test_trip_member_with_admin_role_can_update_trip(): void
    {
        $trip = Trip::factory()->create(['user_id' => $this->otherUser->id]);

        // Add user as admin to the trip
        $trip->users()->attach($this->user->id, ['role' => 'admin']);

        $updateData = [
            'name' => 'Updated by Admin',
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/v1/trips/{$trip->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Updated by Admin']);
    }

    /**
     * Test: Trip member with participant role cannot update trip
     */
    public function test_trip_member_with_participant_role_cannot_update_trip(): void
    {
        $trip = Trip::factory()->create(['user_id' => $this->otherUser->id]);

        // Add user as participant to the trip
        $trip->users()->attach($this->user->id, ['role' => 'participant']);

        $updateData = [
            'name' => 'Updated by Participant',
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/v1/trips/{$trip->id}", $updateData);

        $response->assertStatus(403);
    }
}
