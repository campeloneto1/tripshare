<?php

namespace Tests\Unit;

use App\Models\Place;
use App\Models\VoteAnswer;
use App\Models\VoteOption;
use App\Models\VoteQuestion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VoteOptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_vote_option_can_be_created(): void
    {
        $question = VoteQuestion::factory()->create();

        $option = VoteOption::create([
            'vote_question_id' => $question->id,
            'title' => 'Torre Eiffel',
            'json_data' => ['lat' => 48.858, 'lon' => 2.294],
        ]);

        $this->assertDatabaseHas('votes_options', [
            'vote_question_id' => $question->id,
            'title' => 'Torre Eiffel',
        ]);
    }

    public function test_vote_option_belongs_to_question(): void
    {
        $option = VoteOption::factory()->create();

        $this->assertInstanceOf(VoteQuestion::class, $option->question);
        $this->assertEquals($option->vote_question_id, $option->question->id);
    }

    public function test_vote_option_belongs_to_place(): void
    {
        $place = Place::factory()->create();
        $option = VoteOption::factory()->create([
            'place_id' => $place->id,
        ]);

        $this->assertInstanceOf(Place::class, $option->place);
        $this->assertEquals($place->id, $option->place->id);
    }

    public function test_vote_option_can_have_null_place_id(): void
    {
        $option = VoteOption::factory()->create([
            'place_id' => null,
        ]);

        $this->assertNull($option->place_id);
    }

    public function test_json_data_is_cast_to_array(): void
    {
        $data = ['xid' => 'test123', 'type' => 'attraction', 'lat' => 48.858];

        $option = VoteOption::factory()->create([
            'json_data' => $data,
        ]);

        $this->assertIsArray($option->json_data);
        $this->assertEquals($data, $option->json_data);
    }

    public function test_vote_option_has_many_votes(): void
    {
        $option = VoteOption::factory()->create();

        VoteAnswer::factory()->count(3)->create([
            'vote_option_id' => $option->id,
            'vote_question_id' => $option->vote_question_id,
        ]);

        $this->assertEquals(3, $option->votes()->count());
    }

    public function test_vote_option_with_place_stores_place_data(): void
    {
        $place = Place::factory()->create([
            'xid' => 'eiffel_tower',
            'name' => 'Torre Eiffel',
            'type' => 'attraction',
        ]);

        $option = VoteOption::create([
            'vote_question_id' => VoteQuestion::factory()->create()->id,
            'place_id' => $place->id,
            'title' => 'Torre Eiffel',
            'json_data' => null,
        ]);

        $this->assertEquals($place->id, $option->place_id);
        $this->assertEquals('Torre Eiffel', $option->place->name);
    }

    public function test_vote_option_without_place_uses_json_data(): void
    {
        $jsonData = [
            'xid' => 'custom_place',
            'name' => 'Local Customizado',
            'type' => 'restaurant',
            'lat' => 40.7128,
            'lon' => -74.0060,
        ];

        $option = VoteOption::create([
            'vote_question_id' => VoteQuestion::factory()->create()->id,
            'place_id' => null,
            'title' => 'Local Customizado',
            'json_data' => $jsonData,
        ]);

        $this->assertNull($option->place_id);
        $this->assertEquals($jsonData, $option->json_data);
        $this->assertEquals('custom_place', $option->json_data['xid']);
    }

    public function test_multiple_options_for_same_question(): void
    {
        $question = VoteQuestion::factory()->create();

        VoteOption::factory()->count(4)->create([
            'vote_question_id' => $question->id,
        ]);

        $this->assertEquals(4, $question->options()->count());
    }

    public function test_vote_option_can_be_updated(): void
    {
        $option = VoteOption::factory()->create([
            'title' => 'Original Title',
        ]);

        $option->update([
            'title' => 'Updated Title',
        ]);

        $this->assertDatabaseHas('votes_options', [
            'id' => $option->id,
            'title' => 'Updated Title',
        ]);

        $this->assertEquals('Updated Title', $option->fresh()->title);
    }

    public function test_vote_option_json_data_can_be_updated(): void
    {
        $option = VoteOption::factory()->create([
            'json_data' => ['lat' => 48.858, 'lon' => 2.294],
        ]);

        $newData = ['lat' => 40.7128, 'lon' => -74.0060, 'type' => 'restaurant'];
        $option->update([
            'json_data' => $newData,
        ]);

        $this->assertEquals($newData, $option->fresh()->json_data);
    }

    public function test_vote_option_place_relationship_can_be_updated(): void
    {
        $place1 = Place::factory()->create();
        $place2 = Place::factory()->create();

        $option = VoteOption::factory()->create([
            'place_id' => $place1->id,
        ]);

        $option->update([
            'place_id' => $place2->id,
        ]);

        $this->assertEquals($place2->id, $option->fresh()->place_id);
        $this->assertEquals($place2->id, $option->fresh()->place->id);
    }

    public function test_vote_option_can_be_deleted(): void
    {
        $option = VoteOption::factory()->create();
        $optionId = $option->id;

        $option->delete();

        $this->assertDatabaseMissing('votes_options', [
            'id' => $optionId,
        ]);
    }

    public function test_deleting_vote_option_deletes_associated_votes(): void
    {
        $option = VoteOption::factory()->create();

        $vote = VoteAnswer::factory()->create([
            'vote_option_id' => $option->id,
            'vote_question_id' => $option->vote_question_id,
        ]);

        $voteId = $vote->id;
        $option->delete();

        $this->assertDatabaseMissing('votes_answers', [
            'id' => $voteId,
        ]);
    }

    public function test_multiple_vote_options_can_be_deleted_independently(): void
    {
        $question = VoteQuestion::factory()->create();

        $option1 = VoteOption::factory()->create(['vote_question_id' => $question->id]);
        $option2 = VoteOption::factory()->create(['vote_question_id' => $question->id]);
        $option3 = VoteOption::factory()->create(['vote_question_id' => $question->id]);

        $option2->delete();

        $this->assertDatabaseHas('votes_options', ['id' => $option1->id]);
        $this->assertDatabaseMissing('votes_options', ['id' => $option2->id]);
        $this->assertDatabaseHas('votes_options', ['id' => $option3->id]);

        $this->assertEquals(2, $question->options()->count());
    }
}
