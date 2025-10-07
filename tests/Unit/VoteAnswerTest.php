<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\VoteAnswer;
use App\Models\VoteOption;
use App\Models\VoteQuestion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VoteAnswerTest extends TestCase
{
    use RefreshDatabase;

    public function test_vote_answer_can_be_created(): void
    {
        $question = VoteQuestion::factory()->create();
        $option = VoteOption::factory()->create(['vote_question_id' => $question->id]);
        $user = User::factory()->create();

        $answer = VoteAnswer::create([
            'vote_question_id' => $question->id,
            'vote_option_id' => $option->id,
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseHas('votes_answers', [
            'vote_question_id' => $question->id,
            'vote_option_id' => $option->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_vote_answer_belongs_to_question(): void
    {
        $answer = VoteAnswer::factory()->create();

        $this->assertInstanceOf(VoteQuestion::class, $answer->question);
        $this->assertEquals($answer->vote_question_id, $answer->question->id);
    }

    public function test_vote_answer_belongs_to_option(): void
    {
        $answer = VoteAnswer::factory()->create();

        $this->assertInstanceOf(VoteOption::class, $answer->option);
        $this->assertEquals($answer->vote_option_id, $answer->option->id);
    }

    public function test_vote_answer_belongs_to_user(): void
    {
        $answer = VoteAnswer::factory()->create();

        $this->assertInstanceOf(User::class, $answer->user);
        $this->assertEquals($answer->user_id, $answer->user->id);
    }

    public function test_user_can_vote_on_multiple_questions(): void
    {
        $user = User::factory()->create();

        VoteAnswer::factory()->count(3)->create([
            'user_id' => $user->id,
        ]);

        $this->assertEquals(3, VoteAnswer::where('user_id', $user->id)->count());
    }

    public function test_option_can_have_multiple_votes(): void
    {
        $option = VoteOption::factory()->create();

        VoteAnswer::factory()->count(5)->create([
            'vote_option_id' => $option->id,
            'vote_question_id' => $option->vote_question_id,
        ]);

        $this->assertEquals(5, $option->votes()->count());
    }
}
