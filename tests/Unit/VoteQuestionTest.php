<?php

namespace Tests\Unit;

use App\Models\TripDay;
use App\Models\TripDayCity;
use App\Models\VoteOption;
use App\Models\VoteQuestion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VoteQuestionTest extends TestCase
{
    use RefreshDatabase;

    public function test_vote_question_can_be_created(): void
    {
        $tripDay = TripDay::factory()->create();

        $question = VoteQuestion::create([
            'votable_id' => $tripDay->id,
            'votable_type' => TripDay::class,
            'title' => 'Qual cidade visitar?',
            'type' => 'city',
            'start_date' => now(),
            'end_date' => now()->addDay(),
        ]);

        $this->assertDatabaseHas('votes_questions', [
            'votable_id' => $tripDay->id,
            'votable_type' => TripDay::class,
            'title' => 'Qual cidade visitar?',
            'type' => 'city',
        ]);
    }

    public function test_vote_question_belongs_to_votable_trip_day(): void
    {
        $tripDay = TripDay::factory()->create();
        $question = VoteQuestion::factory()->create([
            'votable_id' => $tripDay->id,
            'votable_type' => TripDay::class,
        ]);

        $this->assertInstanceOf(TripDay::class, $question->votable);
        $this->assertEquals($tripDay->id, $question->votable->id);
    }

    public function test_vote_question_belongs_to_votable_trip_day_city(): void
    {
        $tripDayCity = TripDayCity::factory()->create();
        $question = VoteQuestion::factory()->create([
            'votable_id' => $tripDayCity->id,
            'votable_type' => TripDayCity::class,
        ]);

        $this->assertInstanceOf(TripDayCity::class, $question->votable);
        $this->assertEquals($tripDayCity->id, $question->votable->id);
    }

    public function test_vote_question_has_many_options(): void
    {
        $question = VoteQuestion::factory()->create();

        VoteOption::factory()->count(3)->create([
            'vote_question_id' => $question->id,
        ]);

        $this->assertEquals(3, $question->options()->count());
    }

    public function test_vote_question_type_can_be_city(): void
    {
        $question = VoteQuestion::factory()->create([
            'type' => 'city',
        ]);

        $this->assertEquals('city', $question->type);
    }

    public function test_vote_question_type_can_be_event(): void
    {
        $question = VoteQuestion::factory()->create([
            'type' => 'event',
        ]);

        $this->assertEquals('event', $question->type);
    }

    public function test_vote_question_dates_are_cast_to_datetime(): void
    {
        $question = VoteQuestion::factory()->create([
            'start_date' => '2025-01-01 10:00:00',
            'end_date' => '2025-01-02 10:00:00',
        ]);

        $this->assertInstanceOf(\DateTime::class, $question->start_date);
        $this->assertInstanceOf(\DateTime::class, $question->end_date);
    }

    public function test_vote_question_is_closed_defaults_to_false(): void
    {
        $question = VoteQuestion::factory()->create();

        $this->assertFalse($question->is_closed);
    }

    public function test_vote_question_can_be_closed(): void
    {
        $question = VoteQuestion::factory()->create([
            'is_closed' => true,
            'closed_at' => now(),
        ]);

        $this->assertTrue($question->is_closed);
        $this->assertNotNull($question->closed_at);
    }

    public function test_closed_at_is_nullable(): void
    {
        $question = VoteQuestion::factory()->create([
            'is_closed' => false,
            'closed_at' => null,
        ]);

        $this->assertNull($question->closed_at);
    }

    public function test_vote_question_can_be_updated(): void
    {
        $question = VoteQuestion::factory()->create([
            'title' => 'Original Title',
            'is_closed' => false,
        ]);

        $question->update([
            'title' => 'Updated Title',
        ]);

        $this->assertEquals('Updated Title', $question->fresh()->title);
    }

    public function test_vote_question_can_be_closed_via_update(): void
    {
        $question = VoteQuestion::factory()->create([
            'is_closed' => false,
            'closed_at' => null,
        ]);

        $question->update([
            'is_closed' => true,
            'closed_at' => now(),
        ]);

        $this->assertTrue($question->fresh()->is_closed);
        $this->assertNotNull($question->fresh()->closed_at);
    }

    public function test_vote_question_can_be_deleted(): void
    {
        $question = VoteQuestion::factory()->create();
        $questionId = $question->id;

        $question->delete();

        $this->assertDatabaseMissing('votes_questions', ['id' => $questionId]);
    }

    public function test_deleting_vote_question_deletes_options(): void
    {
        $question = VoteQuestion::factory()->create();

        $option1 = VoteOption::factory()->create(['vote_question_id' => $question->id]);
        $option2 = VoteOption::factory()->create(['vote_question_id' => $question->id]);

        $question->delete();

        // Verifica se as opções foram deletadas (se houver cascade)
        $this->assertEquals(0, VoteOption::where('vote_question_id', $question->id)->count());
    }
}
