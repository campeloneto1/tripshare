<?php

namespace App\Jobs;

use App\Models\TripDay;
use App\Models\TripDayCity;
use App\Models\TripDayEvent;
use App\Models\User;
use App\Models\VoteAnswer;
use App\Models\VoteQuestion;
use App\Notifications\VoteEndedNotification;
use App\Repositories\TripDayEventRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

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

        // Calcula total de votos
        $totalVotes = VoteAnswer::where('vote_question_id', $this->question->id)->count();

        if (!$winningOption) {
            // Nenhum voto foi registrado
            $this->question->update([
                'is_closed' => true,
                'closed_at' => now(),
            ]);

            // Notifica participantes
            $this->notifyTripParticipants($totalVotes, null);
            return;
        }

        // Cria registro no modelo correto
        switch ($this->question->type) {
            case 'city':
                // votable é TripDay
                $data = $winningOption->json_data ?? [];
                TripDayCity::create(array_merge([
                    'trip_day_id' => $this->question->votable_id,
                    'name' => $winningOption->title,
                ], $data));
                break;

            case 'event':
                // votable é TripDayCity
                // Se tem place_id, usa ele diretamente
                if ($winningOption->place_id) {
                    $eventRepository = app(TripDayEventRepository::class);
                    $eventRepository->create([
                        'trip_day_city_id' => $this->question->votable_id,
                        'place_id' => $winningOption->place_id,
                    ]);
                } else {
                    // Fallback: se não tem place_id mas tem json_data com xid
                    $data = $winningOption->json_data ?? [];
                    if (!empty($data['xid'])) {
                        $eventRepository = app(TripDayEventRepository::class);
                        $eventRepository->create(array_merge([
                            'trip_day_city_id' => $this->question->votable_id,
                        ], $data));
                    }
                }
                break;
        }

        // Fecha a votação
        $this->question->update([
            'is_closed' => true,
            'closed_at' => now(),
        ]);

        // Notifica participantes
        $this->notifyTripParticipants($totalVotes, $winningOption);
    }

    private function notifyTripParticipants(int $totalVotes, $winningOption): void
    {
        $votable = $this->question->votable;

        if (!$votable) {
            return;
        }

        // Busca a trip relacionada
        $trip = null;
        if ($votable instanceof TripDay) {
            $trip = $votable->trip;
        } elseif ($votable instanceof TripDayCity) {
            $trip = $votable->tripDay?->trip;
        }

        if (!$trip) {
            return;
        }

        // Busca todos participantes da trip
        $participants = User::whereHas('trips', function ($query) use ($trip) {
            $query->where('trips.id', $trip->id);
        })->get();

        // Adiciona criador da trip se não estiver nos participantes
        if (!$participants->contains('id', $trip->user_id)) {
            $tripOwner = User::find($trip->user_id);
            if ($tripOwner) {
                $participants->push($tripOwner);
            }
        }

        // Envia notificação
        Notification::send(
            $participants,
            new VoteEndedNotification($this->question, $winningOption, $totalVotes)
        );
    }
}
