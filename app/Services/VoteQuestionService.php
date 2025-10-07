<?php

namespace App\Services;

use App\Jobs\ComputeVoteWinner;
use App\Models\User;
use App\Models\VoteQuestion;
use App\Notifications\VoteQuestionCreatedNotification;
use App\Repositories\VoteQuestionRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class VoteQuestionService
{
    public function __construct(private VoteQuestionRepository $repository) {}

    public function list(array $filters = [])
    {
        return $this->repository->all($filters);
    }

    public function find(int $id): ?VoteQuestion
    {
        return $this->repository->find($id);
    }

    public function store(array $data): VoteQuestion
    {
        return DB::transaction(function () use ($data) {
            // Cria a pergunta de votação
            $voteQuestion = $this->repository->create($data);

             ComputeVoteWinner::dispatch($voteQuestion)->delay($voteQuestion->end_date);

            // Carrega relação votable
            $voteQuestion->load('votable');

            // Notifica participantes da trip
            $this->notifyTripParticipants($voteQuestion);

            return $voteQuestion;
        });
    }

    public function update(VoteQuestion $voteQuestion, array $data): VoteQuestion
    {
        return DB::transaction(function () use ($voteQuestion, $data) {
            return $this->repository->update($voteQuestion, $data);
        });
    }

    public function delete(VoteQuestion $voteQuestion): bool
    {
        return DB::transaction(function () use ($voteQuestion) {
            return $this->repository->delete($voteQuestion);
        });
    }

    private function notifyTripParticipants(VoteQuestion $voteQuestion): void
    {
        $votable = $voteQuestion->votable;

        if (!$votable) {
            return;
        }

        // Busca a trip relacionada
        $trip = null;
        if ($votable instanceof \App\Models\TripDay) {
            $trip = $votable->trip;
        } elseif ($votable instanceof \App\Models\TripDayCity) {
            $trip = $votable->tripDay?->trip;
        }

        if (!$trip) {
            return;
        }

        // Busca criador da enquete
        $creator = auth()->user();
        if (!$creator) {
            return;
        }

        // Busca participantes da trip (exceto quem criou)
        $participants = User::whereHas('trips', function ($query) use ($trip) {
            $query->where('trips.id', $trip->id);
        })->where('id', '!=', $creator->id)->get();

        // Se não tiver participantes, notifica só o dono da trip (se não for o criador)
        if ($participants->isEmpty() && $trip->user_id != $creator->id) {
            $tripOwner = User::find($trip->user_id);
            if ($tripOwner) {
                $participants->push($tripOwner);
            }
        }

        // Envia notificação
        Notification::send(
            $participants,
            new VoteQuestionCreatedNotification($voteQuestion, $creator)
        );
    }
}
