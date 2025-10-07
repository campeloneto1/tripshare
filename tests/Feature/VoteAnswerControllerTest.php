<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Trip;
use App\Models\TripDay;
use App\Models\User;
use App\Models\VoteAnswer;
use App\Models\VoteOption;
use App\Models\VoteQuestion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VoteAnswerControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;
    protected User $participant;
    protected User $outsider;
    protected Trip $trip;
    protected VoteQuestion $question;
    protected VoteOption $option1;
    protected VoteOption $option2;
    protected Role $userRole;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRole = Role::factory()->create(['name' => 'User']);

        $this->owner = User::factory()->create(['role_id' => $this->userRole->id]);
        $this->participant = User::factory()->create(['role_id' => $this->userRole->id]);
        $this->outsider = User::factory()->create(['role_id' => $this->userRole->id]);

        $this->trip = Trip::factory()->create(['user_id' => $this->owner->id]);
        $tripDay = TripDay::factory()->create(['trip_id' => $this->trip->id]);

        $this->trip->users()->attach($this->participant->id, ['role' => 'participant']);

        $this->question = VoteQuestion::factory()->create([
            'votable_type' => TripDay::class,
            'votable_id' => $tripDay->id,
            'is_closed' => false,
        ]);

        $this->option1 = VoteOption::factory()->create(['vote_question_id' => $this->question->id]);
        $this->option2 = VoteOption::factory()->create(['vote_question_id' => $this->question->id]);
    }

    public function test_unauthenticated_user_cannot_vote(): void
    {
        $data = [
            'vote_question_id' => $this->question->id,
            'vote_option_id' => $this->option1->id,
        ];

        $response = $this->postJson('/api/v1/votes/answers', $data);

        $response->assertStatus(401);
    }

    public function test_trip_participant_can_vote(): void
    {
        $data = [
            'vote_question_id' => $this->question->id,
            'vote_option_id' => $this->option1->id,
            'user_id' => $this->participant->id,
        ];

        $response = $this->actingAs($this->participant, 'sanctum')
            ->postJson('/api/v1/votes/answers', $data);

        $response->assertStatus(201);

        $this->assertDatabaseHas('votes_answers', [
            'vote_question_id' => $this->question->id,
            'vote_option_id' => $this->option1->id,
            'user_id' => $this->participant->id,
        ]);
    }

    public function test_trip_owner_can_vote(): void
    {
        $data = [
            'vote_question_id' => $this->question->id,
            'vote_option_id' => $this->option1->id,
            'user_id' => $this->owner->id,
        ];

        $response = $this->actingAs($this->owner, 'sanctum')
            ->postJson('/api/v1/votes/answers', $data);

        $response->assertStatus(201);
    }

    public function test_outsider_cannot_vote(): void
    {
        $data = [
            'vote_question_id' => $this->question->id,
            'vote_option_id' => $this->option1->id,
            'user_id' => $this->outsider->id,
        ];

        $response = $this->actingAs($this->outsider, 'sanctum')
            ->postJson('/api/v1/votes/answers', $data);

        $response->assertStatus(403);
    }

    public function test_cannot_vote_on_closed_question(): void
    {
        $this->question->update(['is_closed' => true]);

        $data = [
            'vote_question_id' => $this->question->id,
            'vote_option_id' => $this->option1->id,
            'user_id' => $this->participant->id,
        ];

        $response = $this->actingAs($this->participant, 'sanctum')
            ->postJson('/api/v1/votes/answers', $data);

        $response->assertStatus(403);
    }

    public function test_user_cannot_vote_twice_on_same_question(): void
    {
        $data = [
            'vote_question_id' => $this->question->id,
            'vote_option_id' => $this->option1->id,
            'user_id' => $this->participant->id,
        ];

        // First vote
        $this->actingAs($this->participant, 'sanctum')
            ->postJson('/api/v1/votes/answers', $data);

        // Try to vote again
        $data['vote_option_id'] = $this->option2->id;
        $response = $this->actingAs($this->participant, 'sanctum')
            ->postJson('/api/v1/votes/answers', $data);

        $response->assertStatus(422);
    }

    public function test_user_can_change_vote_by_updating(): void
    {
        $vote = VoteAnswer::create([
            'vote_question_id' => $this->question->id,
            'vote_option_id' => $this->option1->id,
            'user_id' => $this->participant->id,
        ]);

        $updateData = ['vote_option_id' => $this->option2->id];

        $response = $this->actingAs($this->participant, 'sanctum')
            ->putJson("/api/v1/votes/answers/{$vote->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonFragment(['vote_option_id' => $this->option2->id]);

        $this->assertDatabaseHas('votes_answers', [
            'id' => $vote->id,
            'vote_option_id' => $this->option2->id,
        ]);
    }

    public function test_cannot_change_vote_on_closed_question(): void
    {
        $vote = VoteAnswer::create([
            'vote_question_id' => $this->question->id,
            'vote_option_id' => $this->option1->id,
            'user_id' => $this->participant->id,
        ]);

        $this->question->update(['is_closed' => true]);

        $updateData = ['vote_option_id' => $this->option2->id];

        $response = $this->actingAs($this->participant, 'sanctum')
            ->putJson("/api/v1/votes/answers/{$vote->id}", $updateData);

        $response->assertStatus(403);
    }

    public function test_user_cannot_update_others_vote(): void
    {
        $vote = VoteAnswer::create([
            'vote_question_id' => $this->question->id,
            'vote_option_id' => $this->option1->id,
            'user_id' => $this->owner->id,
        ]);

        $updateData = ['vote_option_id' => $this->option2->id];

        $response = $this->actingAs($this->participant, 'sanctum')
            ->putJson("/api/v1/votes/answers/{$vote->id}", $updateData);

        $response->assertStatus(403);
    }

    public function test_user_can_delete_own_vote(): void
    {
        $vote = VoteAnswer::create([
            'vote_question_id' => $this->question->id,
            'vote_option_id' => $this->option1->id,
            'user_id' => $this->participant->id,
        ]);

        $response = $this->actingAs($this->participant, 'sanctum')
            ->deleteJson("/api/v1/votes/answers/{$vote->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('votes_answers', ['id' => $vote->id]);
    }

    public function test_cannot_delete_vote_on_closed_question(): void
    {
        $vote = VoteAnswer::create([
            'vote_question_id' => $this->question->id,
            'vote_option_id' => $this->option1->id,
            'user_id' => $this->participant->id,
        ]);

        $this->question->update(['is_closed' => true]);

        $response = $this->actingAs($this->participant, 'sanctum')
            ->deleteJson("/api/v1/votes/answers/{$vote->id}");

        $response->assertStatus(403);
    }

    public function test_user_cannot_delete_others_vote(): void
    {
        $vote = VoteAnswer::create([
            'vote_question_id' => $this->question->id,
            'vote_option_id' => $this->option1->id,
            'user_id' => $this->owner->id,
        ]);

        $response = $this->actingAs($this->participant, 'sanctum')
            ->deleteJson("/api/v1/votes/answers/{$vote->id}");

        $response->assertStatus(403);
    }

    public function test_can_list_votes_for_question(): void
    {
        VoteAnswer::factory()->count(3)->create([
            'vote_question_id' => $this->question->id,
        ]);

        $response = $this->actingAs($this->participant, 'sanctum')
            ->getJson("/api/v1/votes/answers?vote_question_id={$this->question->id}");

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_can_see_vote_count_per_option(): void
    {
        // 2 votes for option1
        VoteAnswer::factory()->count(2)->create([
            'vote_question_id' => $this->question->id,
            'vote_option_id' => $this->option1->id,
        ]);

        // 3 votes for option2
        VoteAnswer::factory()->count(3)->create([
            'vote_question_id' => $this->question->id,
            'vote_option_id' => $this->option2->id,
        ]);

        $response = $this->actingAs($this->participant, 'sanctum')
            ->getJson("/api/v1/votes/answers?vote_question_id={$this->question->id}");

        $response->assertStatus(200);

        $votes = VoteAnswer::where('vote_question_id', $this->question->id)->get();
        $this->assertEquals(5, $votes->count());
    }

    public function test_validation_error_when_voting_without_option(): void
    {
        $data = [
            'vote_question_id' => $this->question->id,
            'user_id' => $this->participant->id,
        ];

        $response = $this->actingAs($this->participant, 'sanctum')
            ->postJson('/api/v1/votes/answers', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['vote_option_id']);
    }
}
