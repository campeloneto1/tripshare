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

    public function test_vote_answer_can_be_updated(): void
    {
        $question = VoteQuestion::factory()->create();
        $option1 = VoteOption::factory()->create(['vote_question_id' => $question->id]);
        $option2 = VoteOption::factory()->create(['vote_question_id' => $question->id]);

        $answer = VoteAnswer::factory()->create([
            'vote_question_id' => $question->id,
            'vote_option_id' => $option1->id,
        ]);

        $answer->update([
            'vote_option_id' => $option2->id,
        ]);

        $this->assertDatabaseHas('votes_answers', [
            'id' => $answer->id,
            'vote_option_id' => $option2->id,
        ]);

        $this->assertEquals($option2->id, $answer->fresh()->vote_option_id);
    }

    public function test_vote_answer_relationships_remain_valid_after_update(): void
    {
        $question = VoteQuestion::factory()->create();
        $option1 = VoteOption::factory()->create(['vote_question_id' => $question->id]);
        $option2 = VoteOption::factory()->create(['vote_question_id' => $question->id]);

        $answer = VoteAnswer::factory()->create([
            'vote_question_id' => $question->id,
            'vote_option_id' => $option1->id,
        ]);

        $this->assertEquals($option1->id, $answer->option->id);

        $answer->update(['vote_option_id' => $option2->id]);

        $this->assertEquals($option2->id, $answer->fresh()->option->id);
        $this->assertEquals($question->id, $answer->fresh()->question->id);
    }

    public function test_vote_answer_can_be_deleted(): void
    {
        $answer = VoteAnswer::factory()->create();
        $answerId = $answer->id;

        $answer->delete();

        $this->assertDatabaseMissing('votes_answers', [
            'id' => $answerId,
        ]);
    }

    public function test_deleting_vote_answer_decreases_option_vote_count(): void
    {
        $option = VoteOption::factory()->create();

        VoteAnswer::factory()->count(5)->create([
            'vote_option_id' => $option->id,
            'vote_question_id' => $option->vote_question_id,
        ]);

        $this->assertEquals(5, $option->votes()->count());

        $firstVote = $option->votes()->first();
        $firstVote->delete();

        $this->assertEquals(4, $option->votes()->count());
    }

    public function test_user_can_change_vote_by_deleting_and_creating_new(): void
    {
        $question = VoteQuestion::factory()->create();
        $option1 = VoteOption::factory()->create(['vote_question_id' => $question->id]);
        $option2 = VoteOption::factory()->create(['vote_question_id' => $question->id]);
        $user = User::factory()->create();

        $oldAnswer = VoteAnswer::create([
            'vote_question_id' => $question->id,
            'vote_option_id' => $option1->id,
            'user_id' => $user->id,
        ]);

        $oldAnswer->delete();

        $newAnswer = VoteAnswer::create([
            'vote_question_id' => $question->id,
            'vote_option_id' => $option2->id,
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseMissing('votes_answers', ['id' => $oldAnswer->id]);
        $this->assertDatabaseHas('votes_answers', [
            'id' => $newAnswer->id,
            'vote_option_id' => $option2->id,
            'user_id' => $user->id,
        ]);
    }
}
