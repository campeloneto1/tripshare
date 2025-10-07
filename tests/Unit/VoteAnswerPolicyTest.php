<?php

namespace Tests\Unit;

use App\Models\Trip;
use App\Models\TripDay;
use App\Models\User;
use App\Models\VoteAnswer;
use App\Models\VoteOption;
use App\Models\VoteQuestion;
use App\Policies\VoteAnswerPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VoteAnswerPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected VoteAnswerPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new VoteAnswerPolicy();
    }

    public function test_any_user_can_view_any_vote_answers(): void
    {
        $user = User::factory()->create();

        $this->assertTrue($this->policy->viewAny($user));
    }

    public function test_participant_can_create_vote_answer(): void
    {
        $owner = User::factory()->create();
        $participant = User::factory()->create();
        $trip = Trip::factory()->create(['user_id' => $owner->id]);
        $tripDay = TripDay::factory()->create(['trip_id' => $trip->id]);

        $trip->users()->attach($participant->id, ['role' => 'participant']);

        $question = VoteQuestion::factory()->create([
            'votable_type' => TripDay::class,
            'votable_id' => $tripDay->id,
            'is_closed' => false,
        ]);

        $this->assertTrue($this->policy->create($participant, $question));
    }

    public function test_owner_can_create_vote_answer(): void
    {
        $owner = User::factory()->create();
        $trip = Trip::factory()->create(['user_id' => $owner->id]);
        $tripDay = TripDay::factory()->create(['trip_id' => $trip->id]);

        $question = VoteQuestion::factory()->create([
            'votable_type' => TripDay::class,
            'votable_id' => $tripDay->id,
            'is_closed' => false,
        ]);

        $this->assertTrue($this->policy->create($owner, $question));
    }

    public function test_outsider_cannot_create_vote_answer(): void
    {
        $owner = User::factory()->create();
        $outsider = User::factory()->create();
        $trip = Trip::factory()->create(['user_id' => $owner->id]);
        $tripDay = TripDay::factory()->create(['trip_id' => $trip->id]);

        $question = VoteQuestion::factory()->create([
            'votable_type' => TripDay::class,
            'votable_id' => $tripDay->id,
            'is_closed' => false,
        ]);

        $this->assertFalse($this->policy->create($outsider, $question));
    }

    public function test_cannot_vote_on_closed_question(): void
    {
        $owner = User::factory()->create();
        $trip = Trip::factory()->create(['user_id' => $owner->id]);
        $tripDay = TripDay::factory()->create(['trip_id' => $trip->id]);

        $question = VoteQuestion::factory()->create([
            'votable_type' => TripDay::class,
            'votable_id' => $tripDay->id,
            'is_closed' => true,
        ]);

        $this->assertFalse($this->policy->create($owner, $question));
    }

    public function test_user_can_update_own_vote_if_question_is_open(): void
    {
        $user = User::factory()->create();
        $question = VoteQuestion::factory()->create(['is_closed' => false]);
        $answer = VoteAnswer::factory()->create([
            'user_id' => $user->id,
            'vote_question_id' => $question->id,
        ]);

        $this->assertTrue($this->policy->update($user, $answer));
    }

    public function test_user_cannot_update_own_vote_if_question_is_closed(): void
    {
        $user = User::factory()->create();
        $question = VoteQuestion::factory()->create(['is_closed' => true]);
        $answer = VoteAnswer::factory()->create([
            'user_id' => $user->id,
            'vote_question_id' => $question->id,
        ]);

        $this->assertFalse($this->policy->update($user, $answer));
    }

    public function test_user_cannot_update_others_vote(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $question = VoteQuestion::factory()->create(['is_closed' => false]);
        $answer = VoteAnswer::factory()->create([
            'user_id' => $user1->id,
            'vote_question_id' => $question->id,
        ]);

        $this->assertFalse($this->policy->update($user2, $answer));
    }

    public function test_user_can_delete_own_vote_if_question_is_open(): void
    {
        $user = User::factory()->create();
        $question = VoteQuestion::factory()->create(['is_closed' => false]);
        $answer = VoteAnswer::factory()->create([
            'user_id' => $user->id,
            'vote_question_id' => $question->id,
        ]);

        $this->assertTrue($this->policy->delete($user, $answer));
    }

    public function test_user_cannot_delete_own_vote_if_question_is_closed(): void
    {
        $user = User::factory()->create();
        $question = VoteQuestion::factory()->create(['is_closed' => true]);
        $answer = VoteAnswer::factory()->create([
            'user_id' => $user->id,
            'vote_question_id' => $question->id,
        ]);

        $this->assertFalse($this->policy->delete($user, $answer));
    }

    public function test_user_cannot_delete_others_vote(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $question = VoteQuestion::factory()->create(['is_closed' => false]);
        $answer = VoteAnswer::factory()->create([
            'user_id' => $user1->id,
            'vote_question_id' => $question->id,
        ]);

        $this->assertFalse($this->policy->delete($user2, $answer));
    }
}
