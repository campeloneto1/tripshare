<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Place>
 */
class PlaceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'xid' => fake()->unique()->regexify('[A-Z0-9]{10}'),
            'name' => fake()->randomElement([
                'Torre Eiffel',
                'Museu do Louvre',
                'Cristo Redentor',
                'Big Ben',
                'Coliseu',
                'Estátua da Liberdade',
            ]) . ' - ' . fake()->city(),
            'type' => fake()->randomElement(['attraction', 'restaurant', 'hotel', 'transport', 'other']),
            'lat' => fake()->latitude(),
            'lon' => fake()->longitude(),
            'source_api' => fake()->randomElement(['opentripmap', 'google', 'nominatim']),
            'address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'state' => fake()->state(),
            'zip_code' => fake()->postcode(),
            'country' => fake()->country(),
        ];
    }
}
