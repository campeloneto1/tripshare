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
}
