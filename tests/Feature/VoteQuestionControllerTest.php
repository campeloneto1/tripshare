<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Trip;
use App\Models\TripDay;
use App\Models\User;
use App\Models\VoteQuestion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VoteQuestionControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;
    protected User $admin;
    protected User $participant;
    protected User $outsider;
    protected Trip $trip;
    protected TripDay $tripDay;
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
        $this->tripDay = TripDay::factory()->create(['trip_id' => $this->trip->id]);

        $this->trip->users()->attach($this->admin->id, ['role' => 'admin']);
        $this->trip->users()->attach($this->participant->id, ['role' => 'participant']);
    }

    public function test_unauthenticated_user_cannot_create_vote_question(): void
    {
        $data = [
            'votable_type' => TripDay::class,
            'votable_id' => $this->tripDay->id,
            'title' => 'Qual evento adicionar?',
            'type' => 'event',
            'start_date' => now()->format('Y-m-d H:i:s'),
            'end_date' => now()->addDay()->format('Y-m-d H:i:s'),
        ];

        $response = $this->postJson('/api/v1/votes/questions', $data);

        $response->assertStatus(401);
    }

    public function test_owner_can_create_vote_question(): void
    {
        $data = [
            'votable_type' => TripDay::class,
            'votable_id' => $this->tripDay->id,
            'title' => 'Qual evento adicionar?',
            'type' => 'event',
            'start_date' => now()->format('Y-m-d H:i:s'),
            'end_date' => now()->addDay()->format('Y-m-d H:i:s'),
        ];

        $response = $this->actingAs($this->owner, 'sanctum')
            ->postJson('/api/v1/votes/questions', $data);

        $response->assertStatus(201)
            ->assertJsonFragment(['title' => 'Qual evento adicionar?']);

        $this->assertDatabaseHas('votes_questions', [
            'votable_type' => TripDay::class,
            'votable_id' => $this->tripDay->id,
            'title' => 'Qual evento adicionar?',
        ]);
    }

    public function test_admin_can_create_vote_question(): void
    {
        $data = [
            'votable_type' => TripDay::class,
            'votable_id' => $this->tripDay->id,
            'title' => 'Qual cidade visitar?',
            'type' => 'city',
            'start_date' => now()->format('Y-m-d H:i:s'),
            'end_date' => now()->addDay()->format('Y-m-d H:i:s'),
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/votes/questions', $data);

        $response->assertStatus(201);
    }

    public function test_participant_cannot_create_vote_question(): void
    {
        $data = [
            'votable_type' => TripDay::class,
            'votable_id' => $this->tripDay->id,
            'title' => 'Qual evento adicionar?',
            'type' => 'event',
            'start_date' => now()->format('Y-m-d H:i:s'),
            'end_date' => now()->addDay()->format('Y-m-d H:i:s'),
        ];

        $response = $this->actingAs($this->participant, 'sanctum')
            ->postJson('/api/v1/votes/questions', $data);

        $response->assertStatus(403);
    }

    public function test_outsider_cannot_create_vote_question(): void
    {
        $data = [
            'votable_type' => TripDay::class,
            'votable_id' => $this->tripDay->id,
            'title' => 'Qual evento adicionar?',
            'type' => 'event',
            'start_date' => now()->format('Y-m-d H:i:s'),
            'end_date' => now()->addDay()->format('Y-m-d H:i:s'),
        ];

        $response = $this->actingAs($this->outsider, 'sanctum')
            ->postJson('/api/v1/votes/questions', $data);

        $response->assertStatus(403);
    }

    public function test_validation_error_when_creating_without_required_fields(): void
    {
        $data = [
            'title' => 'Qual evento?',
            // Missing votable_type, votable_id, type, dates
        ];

        $response = $this->actingAs($this->owner, 'sanctum')
            ->postJson('/api/v1/votes/questions', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['votable_type', 'votable_id', 'type']);
    }

    public function test_owner_can_update_open_vote_question(): void
    {
        $question = VoteQuestion::factory()->create([
            'votable_type' => TripDay::class,
            'votable_id' => $this->tripDay->id,
            'title' => 'Original Title',
            'is_closed' => false,
        ]);

        $updateData = ['title' => 'Updated Title'];

        $response = $this->actingAs($this->owner, 'sanctum')
            ->putJson("/api/v1/votes/questions/{$question->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonFragment(['title' => 'Updated Title']);
    }

    public function test_cannot_update_closed_vote_question(): void
    {
        $question = VoteQuestion::factory()->create([
            'votable_type' => TripDay::class,
            'votable_id' => $this->tripDay->id,
            'is_closed' => true,
        ]);

        $updateData = ['title' => 'Updated Title'];

        $response = $this->actingAs($this->owner, 'sanctum')
            ->putJson("/api/v1/votes/questions/{$question->id}", $updateData);

        $response->assertStatus(403);
    }

    public function test_participant_cannot_update_vote_question(): void
    {
        $question = VoteQuestion::factory()->create([
            'votable_type' => TripDay::class,
            'votable_id' => $this->tripDay->id,
            'is_closed' => false,
        ]);

        $updateData = ['title' => 'Updated Title'];

        $response = $this->actingAs($this->participant, 'sanctum')
            ->putJson("/api/v1/votes/questions/{$question->id}", $updateData);

        $response->assertStatus(403);
    }

    public function test_owner_can_delete_open_vote_question(): void
    {
        $question = VoteQuestion::factory()->create([
            'votable_type' => TripDay::class,
            'votable_id' => $this->tripDay->id,
            'is_closed' => false,
        ]);

        $response = $this->actingAs($this->owner, 'sanctum')
            ->deleteJson("/api/v1/votes/questions/{$question->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('votes_questions', ['id' => $question->id]);
    }

    public function test_cannot_delete_closed_vote_question(): void
    {
        $question = VoteQuestion::factory()->create([
            'votable_type' => TripDay::class,
            'votable_id' => $this->tripDay->id,
            'is_closed' => true,
        ]);

        $response = $this->actingAs($this->owner, 'sanctum')
            ->deleteJson("/api/v1/votes/questions/{$question->id}");

        $response->assertStatus(403);
    }

    public function test_authenticated_user_can_list_vote_questions(): void
    {
        VoteQuestion::factory()->count(3)->create([
            'votable_type' => TripDay::class,
            'votable_id' => $this->tripDay->id,
        ]);

        $response = $this->actingAs($this->owner, 'sanctum')
            ->getJson('/api/v1/votes/questions');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'title', 'type', 'votable_type', 'votable_id']
                ]
            ]);
    }

    public function test_returns_404_for_non_existent_vote_question(): void
    {
        $response = $this->actingAs($this->owner, 'sanctum')
            ->getJson('/api/v1/votes/questions/99999');

        $response->assertStatus(404);
    }
}
