<?php

namespace App\Jobs;

use App\Models\TripDayCity;
use App\Models\TripDayEvent;
use App\Models\VoteQuestion;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ComputeVoteWinner implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected VoteQuestion $question;

    /**
     * Create a new job instance.
     */
    public function __construct(VoteQuestion $question)
    {
        $this->question = $question;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Se a votação já estiver fechada, nada a fazer
        if ($this->question->is_closed) {
            return;
        }

        // Determina a opção vencedora
        $winningOption = $this->question->options()
            ->withCount('votes')
            ->orderByDesc('votes_count')
            ->first();

        if (!$winningOption) {
            // Nenhum voto foi registrado
            $this->question->update(['is_closed' => true]);
            return;
        }

        $data = $winningOption->json_data ?? [];

        // Cria registro no modelo correto
        switch ($this->question->type) {
            case 'city':
                // votable é TripDay
                TripDayCity::create(array_merge([
                    'trip_day_id' => $this->question->votable_id,
                    'name' => $winningOption->title,
                ], $data));
                break;

            case 'event':
                // votable é TripDayCity
                TripDayEvent::create(array_merge([
                    'trip_day_city_id' => $this->question->votable_id,
                    'name' => $winningOption->title,
                ], $data));
                break;
        }

        // Fecha a votação
        $this->question->update(['is_closed' => true]);
    }
}
