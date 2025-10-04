<?php

namespace Database\Seeders;

use App\Models\TripDay;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TripDaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TripDay::factory()->count(100)->create();
    }
}
