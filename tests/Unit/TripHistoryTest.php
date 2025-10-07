<?php

namespace Tests\Unit;

use App\Models\Trip;
use App\Models\TripHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TripHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_trip_history_logs_are_created_automatically(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $trip = Trip::factory()->create(['user_id' => $user->id]);

        // Atualiza trip para gerar histórico
        $trip->update(['name' => 'Nome Atualizado']);

        $this->assertDatabaseHas('trips_history', [
            'historyable_type' => Trip::class,
            'historyable_id' => $trip->id,
        ]);
    }
}
