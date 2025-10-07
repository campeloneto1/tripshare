<?php

namespace App\Services;

use App\Jobs\ComputeVoteWinner;
use App\Models\VoteQuestion;
use App\Repositories\VoteQuestionRepository;
use Illuminate\Support\Facades\DB;

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

             ComputeVoteWinner::dispatch($voteQuestion)->delay($voteQuestion->ends_at);

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
}
