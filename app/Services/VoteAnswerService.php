<?php

namespace App\Services;

use App\Models\VoteAnswer;
use App\Repositories\VoteAnswerRepository;
use Illuminate\Support\Facades\DB;

class VoteAnswerService
{
    public function __construct(private VoteAnswerRepository $repository) {}

    public function list(array $filters = [])
    {
        return $this->repository->all($filters);
    }

    public function find(int $id): ?VoteAnswer
    {
        return $this->repository->find($id);
    }

    public function store(array $data): VoteAnswer
    {
        return DB::transaction(function () use ($data) {
            // Pode incluir validações, por exemplo: usuário não vota duas vezes na mesma opção
            return $this->repository->create($data);
        });
    }

    public function update(VoteAnswer $voteAnswer, array $data): VoteAnswer
    {
        return DB::transaction(function () use ($voteAnswer, $data) {
            return $this->repository->update($voteAnswer, $data);
        });
    }

    public function delete(VoteAnswer $voteAnswer): bool
    {
        return DB::transaction(function () use ($voteAnswer) {
            return $this->repository->delete($voteAnswer);
        });
    }
}
