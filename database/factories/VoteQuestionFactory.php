<?php

namespace Database\Factories;

use App\Models\TripDay;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\VoteQuestion>
 */
class VoteQuestionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('now', '+30 days');
        $endDate = fake()->dateTimeBetween($startDate, '+60 days');

        return [
            'votable_type' => TripDay::class,
            'votable_id' => TripDay::factory(),
            'title' => fake()->sentence(),
            'type' => fake()->randomElement(['city', 'event']),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'is_closed' => false,
        ];
    }
}
