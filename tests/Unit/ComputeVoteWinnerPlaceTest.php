<?php

namespace Tests\Unit;

use App\Jobs\ComputeVoteWinner;
use App\Models\Place;
use App\Models\TripDayCity;
use App\Models\TripDayEvent;
use App\Models\VoteAnswer;
use App\Models\VoteOption;
use App\Models\VoteQuestion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComputeVoteWinnerPlaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_event_with_place_id_when_vote_option_has_place(): void
    {
        $tripDayCity = TripDayCity::factory()->create();
        $place = Place::factory()->create(['xid' => 'vote_place_xid']);

        $question = VoteQuestion::create([
            'votable_id' => $tripDayCity->id,
            'votable_type' => TripDayCity::class,
            'title' => 'Escolha um local',
            'type' => 'event',
            'start_date' => now()->subDay(),
            'end_date' => now()->subHour(),
        ]);

        $option = VoteOption::create([
            'vote_question_id' => $question->id,
            'place_id' => $place->id,
            'title' => 'Torre Eiffel',
        ]);

        VoteAnswer::create([
            'vote_question_id' => $question->id,
            'vote_option_id' => $option->id,
            'user_id' => 1,
        ]);

        $job = new ComputeVoteWinner($question);
        $job->handle();

        // Verifica que o evento foi criado com place_id
        $this->assertDatabaseHas('trips_days_events', [
            'trip_day_city_id' => $tripDayCity->id,
            'place_id' => $place->id,
        ]);

        // Verifica que a votação foi fechada
        $this->assertTrue($question->fresh()->is_closed);
    }

    public function test_creates_event_with_xid_fallback_when_no_place_id(): void
    {
        $tripDayCity = TripDayCity::factory()->create();

        $question = VoteQuestion::create([
            'votable_id' => $tripDayCity->id,
            'votable_type' => TripDayCity::class,
            'title' => 'Escolha um local',
            'type' => 'event',
            'start_date' => now()->subDay(),
            'end_date' => now()->subHour(),
        ]);

        $option = VoteOption::create([
            'vote_question_id' => $question->id,
            'place_id' => null,
            'title' => 'Local Sem Place',
            'json_data' => [
                'xid' => 'fallback_xid',
                'name' => 'Local Fallback',
                'type' => 'restaurant',
            ],
        ]);

        VoteAnswer::create([
            'vote_question_id' => $question->id,
            'vote_option_id' => $option->id,
            'user_id' => 1,
        ]);

        $job = new ComputeVoteWinner($question);
        $job->handle();

        // Verifica que place foi criado automaticamente
        $this->assertDatabaseHas('places', [
            'xid' => 'fallback_xid',
            'name' => 'Local Fallback',
        ]);

        // Verifica que evento foi criado
        $place = Place::where('xid', 'fallback_xid')->first();
        $this->assertDatabaseHas('trips_days_events', [
            'trip_day_city_id' => $tripDayCity->id,
            'place_id' => $place->id,
        ]);
    }

    public function test_does_not_create_event_if_vote_has_no_place_and_no_xid(): void
    {
        $tripDayCity = TripDayCity::factory()->create();

        $question = VoteQuestion::create([
            'votable_id' => $tripDayCity->id,
            'votable_type' => TripDayCity::class,
            'title' => 'Escolha um local',
            'type' => 'event',
            'start_date' => now()->subDay(),
            'end_date' => now()->subHour(),
        ]);

        $option = VoteOption::create([
            'vote_question_id' => $question->id,
            'place_id' => null,
            'title' => 'Local Incompleto',
            'json_data' => [],
        ]);

        VoteAnswer::create([
            'vote_question_id' => $question->id,
            'vote_option_id' => $option->id,
            'user_id' => 1,
        ]);

        $job = new ComputeVoteWinner($question);
        $job->handle();

        // Verifica que nenhum evento foi criado
        $this->assertDatabaseMissing('trips_days_events', [
            'trip_day_city_id' => $tripDayCity->id,
        ]);

        // Mas a votação ainda foi fechada
        $this->assertTrue($question->fresh()->is_closed);
    }

    public function test_closes_vote_even_if_no_votes_were_cast(): void
    {
        $tripDayCity = TripDayCity::factory()->create();

        $question = VoteQuestion::create([
            'votable_id' => $tripDayCity->id,
            'votable_type' => TripDayCity::class,
            'title' => 'Escolha um local',
            'type' => 'event',
            'start_date' => now()->subDay(),
            'end_date' => now()->subHour(),
        ]);

        VoteOption::create([
            'vote_question_id' => $question->id,
            'place_id' => Place::factory()->create()->id,
            'title' => 'Opção não votada',
        ]);

        $job = new ComputeVoteWinner($question);
        $job->handle();

        // Verifica que votação foi fechada
        $this->assertTrue($question->fresh()->is_closed);
        $this->assertNotNull($question->fresh()->closed_at);

        // Verifica que nenhum evento foi criado
        $this->assertEquals(0, TripDayEvent::count());
    }
}
