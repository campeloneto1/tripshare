<?php

namespace Tests\Unit;

use App\Models\Trip;
use App\Models\TripDay;
use App\Models\TripDayCity;
use App\Models\User;
use App\Models\VoteQuestion;
use App\Policies\VoteQuestionPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VoteQuestionPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected VoteQuestionPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new VoteQuestionPolicy();
    }

    public function test_any_user_can_view_any_vote_questions(): void
    {
        $user = User::factory()->create();

        $this->assertTrue($this->policy->viewAny($user));
    }

    public function test_owner_can_create_vote_question_for_trip_day(): void
    {
        $owner = User::factory()->create();
        $trip = Trip::factory()->create(['user_id' => $owner->id]);
        $tripDay = TripDay::factory()->create(['trip_id' => $trip->id]);

        $canCreate = $this->policy->create($owner, TripDay::class, $tripDay->id);

        $this->assertTrue($canCreate);
    }

    public function test_admin_can_create_vote_question_for_trip_day(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->create();
        $trip = Trip::factory()->create(['user_id' => $owner->id]);
        $tripDay = TripDay::factory()->create(['trip_id' => $trip->id]);

        $trip->users()->attach($admin->id, ['role' => 'admin']);

        $canCreate = $this->policy->create($admin, TripDay::class, $tripDay->id);

        $this->assertTrue($canCreate);
    }

    public function test_participant_cannot_create_vote_question(): void
    {
        $owner = User::factory()->create();
        $participant = User::factory()->create();
        $trip = Trip::factory()->create(['user_id' => $owner->id]);
        $tripDay = TripDay::factory()->create(['trip_id' => $trip->id]);

        $trip->users()->attach($participant->id, ['role' => 'participant']);

        $canCreate = $this->policy->create($participant, TripDay::class, $tripDay->id);

        $this->assertFalse($canCreate);
    }

    public function test_outsider_cannot_create_vote_question(): void
    {
        $owner = User::factory()->create();
        $outsider = User::factory()->create();
        $trip = Trip::factory()->create(['user_id' => $owner->id]);
        $tripDay = TripDay::factory()->create(['trip_id' => $trip->id]);

        $canCreate = $this->policy->create($outsider, TripDay::class, $tripDay->id);

        $this->assertFalse($canCreate);
    }

    public function test_owner_can_create_vote_question_for_trip_day_city(): void
    {
        $owner = User::factory()->create();
        $trip = Trip::factory()->create(['user_id' => $owner->id]);
        $tripDay = TripDay::factory()->create(['trip_id' => $trip->id]);
        $tripDayCity = TripDayCity::factory()->create(['trip_day_id' => $tripDay->id]);

        $canCreate = $this->policy->create($owner, TripDayCity::class, $tripDayCity->id);

        $this->assertTrue($canCreate);
    }

    public function test_admin_can_create_vote_question_for_trip_day_city(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->create();
        $trip = Trip::factory()->create(['user_id' => $owner->id]);
        $tripDay = TripDay::factory()->create(['trip_id' => $trip->id]);
        $tripDayCity = TripDayCity::factory()->create(['trip_day_id' => $tripDay->id]);

        $trip->users()->attach($admin->id, ['role' => 'admin']);

        $canCreate = $this->policy->create($admin, TripDayCity::class, $tripDayCity->id);

        $this->assertTrue($canCreate);
    }

    public function test_cannot_create_without_votable_type(): void
    {
        $user = User::factory()->create();

        $canCreate = $this->policy->create($user, null, null);

        $this->assertFalse($canCreate);
    }

    public function test_owner_can_update_open_vote_question(): void
    {
        $owner = User::factory()->create();
        $trip = Trip::factory()->create(['user_id' => $owner->id]);
        $tripDay = TripDay::factory()->create(['trip_id' => $trip->id]);

        $voteQuestion = VoteQuestion::factory()->create([
            'votable_type' => TripDay::class,
            'votable_id' => $tripDay->id,
            'is_closed' => false,
        ]);

        $this->assertTrue($this->policy->update($owner, $voteQuestion));
    }

    public function test_cannot_update_closed_vote_question(): void
    {
        $owner = User::factory()->create();
        $trip = Trip::factory()->create(['user_id' => $owner->id]);
        $tripDay = TripDay::factory()->create(['trip_id' => $trip->id]);

        $voteQuestion = VoteQuestion::factory()->create([
            'votable_type' => TripDay::class,
            'votable_id' => $tripDay->id,
            'is_closed' => true,
        ]);

        $this->assertFalse($this->policy->update($owner, $voteQuestion));
    }

    public function test_participant_cannot_update_vote_question(): void
    {
        $owner = User::factory()->create();
        $participant = User::factory()->create();
        $trip = Trip::factory()->create(['user_id' => $owner->id]);
        $tripDay = TripDay::factory()->create(['trip_id' => $trip->id]);

        $trip->users()->attach($participant->id, ['role' => 'participant']);

        $voteQuestion = VoteQuestion::factory()->create([
            'votable_type' => TripDay::class,
            'votable_id' => $tripDay->id,
            'is_closed' => false,
        ]);

        $this->assertFalse($this->policy->update($participant, $voteQuestion));
    }

    public function test_owner_can_delete_open_vote_question(): void
    {
        $owner = User::factory()->create();
        $trip = Trip::factory()->create(['user_id' => $owner->id]);
        $tripDay = TripDay::factory()->create(['trip_id' => $trip->id]);

        $voteQuestion = VoteQuestion::factory()->create([
            'votable_type' => TripDay::class,
            'votable_id' => $tripDay->id,
            'is_closed' => false,
        ]);

        $this->assertTrue($this->policy->delete($owner, $voteQuestion));
    }

    public function test_cannot_delete_closed_vote_question(): void
    {
        $owner = User::factory()->create();
        $trip = Trip::factory()->create(['user_id' => $owner->id]);
        $tripDay = TripDay::factory()->create(['trip_id' => $trip->id]);

        $voteQuestion = VoteQuestion::factory()->create([
            'votable_type' => TripDay::class,
            'votable_id' => $tripDay->id,
            'is_closed' => true,
        ]);

        $this->assertFalse($this->policy->delete($owner, $voteQuestion));
    }
}
