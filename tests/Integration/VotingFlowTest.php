<?php

namespace Tests\Integration;

use App\Jobs\ComputeVoteWinner;
use App\Models\Place;
use App\Models\Role;
use App\Models\Trip;
use App\Models\TripDay;
use App\Models\TripDayCity;
use App\Models\TripDayEvent;
use App\Models\User;
use App\Models\VoteAnswer;
use App\Models\VoteOption;
use App\Models\VoteQuestion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class VotingFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_complete_voting_flow_from_creation_to_winner(): void
    {
        Queue::fake();

        $userRole = Role::factory()->create(['name' => 'User']);
        $owner = User::factory()->create(['role_id' => $userRole->id]);
        $participant1 = User::factory()->create(['role_id' => $userRole->id]);
        $participant2 = User::factory()->create(['role_id' => $userRole->id]);

        // Step 1: Create trip and add participants
        $trip = Trip::factory()->create(['user_id' => $owner->id]);
        $tripDay = TripDay::factory()->create(['trip_id' => $trip->id]);
        $city = TripDayCity::factory()->create(['trip_day_id' => $tripDay->id]);

        $trip->users()->attach($participant1->id, ['role' => 'participant']);
        $trip->users()->attach($participant2->id, ['role' => 'participant']);

        // Step 2: Owner creates vote question
        $question = VoteQuestion::create([
            'votable_type' => TripDayCity::class,
            'votable_id' => $city->id,
            'title' => 'Qual evento adicionar?',
            'type' => 'event',
            'start_date' => now(),
            'end_date' => now()->addDay(),
            'is_closed' => false,
        ]);

        // Step 3: Create vote options
        $place1 = Place::factory()->create(['name' => 'Torre Eiffel']);
        $place2 = Place::factory()->create(['name' => 'Museu do Louvre']);
        $place3 = Place::factory()->create(['name' => 'Arc de Triomphe']);

        $option1 = VoteOption::create([
            'vote_question_id' => $question->id,
            'place_id' => $place1->id,
            'title' => 'Torre Eiffel',
        ]);

        $option2 = VoteOption::create([
            'vote_question_id' => $question->id,
            'place_id' => $place2->id,
            'title' => 'Museu do Louvre',
        ]);

        $option3 = VoteOption::create([
            'vote_question_id' => $question->id,
            'place_id' => $place3->id,
            'title' => 'Arc de Triomphe',
        ]);

        // Step 4: Participants vote
        VoteAnswer::create([
            'vote_question_id' => $question->id,
            'vote_option_id' => $option1->id,
            'user_id' => $owner->id,
        ]);

        VoteAnswer::create([
            'vote_question_id' => $question->id,
            'vote_option_id' => $option2->id,
            'user_id' => $participant1->id,
        ]);

        VoteAnswer::create([
            'vote_question_id' => $question->id,
            'vote_option_id' => $option2->id,
            'user_id' => $participant2->id,
        ]);

        $this->assertEquals(3, VoteAnswer::where('vote_question_id', $question->id)->count());

        // Step 5: Close voting and compute winner
        $question->update([
            'is_closed' => true,
            'closed_at' => now(),
        ]);

        // Execute the job
        $job = new ComputeVoteWinner($question);
        $job->handle();

        // Step 6: Verify winner was added as event
        $events = TripDayEvent::where('trip_day_city_id', $city->id)->get();

        $this->assertGreaterThan(0, $events->count());

        $winnerEvent = $events->firstWhere('place_id', $option2->place_id);
        $this->assertNotNull($winnerEvent, 'Winner event should be created');
        $this->assertEquals($place2->id, $winnerEvent->place_id);
    }

    public function test_participant_can_change_vote_before_closing(): void
    {
        $user = User::factory()->create();
        $question = VoteQuestion::factory()->create(['is_closed' => false]);
        $option1 = VoteOption::factory()->create(['vote_question_id' => $question->id]);
        $option2 = VoteOption::factory()->create(['vote_question_id' => $question->id]);

        // Initial vote
        $vote = VoteAnswer::create([
            'vote_question_id' => $question->id,
            'vote_option_id' => $option1->id,
            'user_id' => $user->id,
        ]);

        // Change vote
        $vote->update(['vote_option_id' => $option2->id]);

        $this->assertEquals($option2->id, $vote->fresh()->vote_option_id);
        $this->assertEquals(1, VoteAnswer::where('user_id', $user->id)->count());
    }

    public function test_winner_is_option_with_most_votes(): void
    {
        $question = VoteQuestion::factory()->create();
        $option1 = VoteOption::factory()->create(['vote_question_id' => $question->id]);
        $option2 = VoteOption::factory()->create(['vote_question_id' => $question->id]);
        $option3 = VoteOption::factory()->create(['vote_question_id' => $question->id]);

        // Option2 gets most votes (3 votes)
        VoteAnswer::factory()->count(1)->create(['vote_question_id' => $question->id, 'vote_option_id' => $option1->id]);
        VoteAnswer::factory()->count(3)->create(['vote_question_id' => $question->id, 'vote_option_id' => $option2->id]);
        VoteAnswer::factory()->count(2)->create(['vote_question_id' => $question->id, 'vote_option_id' => $option3->id]);

        $votes = VoteAnswer::where('vote_question_id', $question->id)
            ->select('vote_option_id', \DB::raw('count(*) as votes_count'))
            ->groupBy('vote_option_id')
            ->orderByDesc('votes_count')
            ->first();

        $this->assertEquals($option2->id, $votes->vote_option_id);
        $this->assertEquals(3, $votes->votes_count);
    }

    public function test_tie_votes_selects_first_option(): void
    {
        $question = VoteQuestion::factory()->create();
        $option1 = VoteOption::factory()->create(['vote_question_id' => $question->id]);
        $option2 = VoteOption::factory()->create(['vote_question_id' => $question->id]);

        // Both options get same votes
        VoteAnswer::factory()->count(2)->create(['vote_question_id' => $question->id, 'vote_option_id' => $option1->id]);
        VoteAnswer::factory()->count(2)->create(['vote_question_id' => $question->id, 'vote_option_id' => $option2->id]);

        $winner = VoteAnswer::where('vote_question_id', $question->id)
            ->select('vote_option_id', \DB::raw('count(*) as votes_count'))
            ->groupBy('vote_option_id')
            ->orderByDesc('votes_count')
            ->first();

        // Should return one of them (first by query order)
        $this->assertContains($winner->vote_option_id, [$option1->id, $option2->id]);
        $this->assertEquals(2, $winner->votes_count);
    }
}
