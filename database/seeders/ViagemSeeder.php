<?php

namespace Database\Seeders;

use App\Models\Viagem;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ViagemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Viagem::factory()->count(30)->create();
    }
}
