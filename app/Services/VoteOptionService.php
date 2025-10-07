<?php

namespace App\Services;

use App\Models\VoteOption;
use App\Repositories\VoteOptionRepository;
use Illuminate\Support\Facades\DB;

class VoteOptionService
{
    public function __construct(private VoteOptionRepository $repository) {}

    public function list(array $filters = [])
    {
        return $this->repository->all($filters);
    }

    public function find(int $id): ?VoteOption
    {
        return $this->repository->find($id);
    }

    public function store(array $data): VoteOption
    {
        return DB::transaction(function () use ($data) {
            return $this->repository->create($data);
        });
    }

    public function update(VoteOption $voteOption, array $data): VoteOption
    {
        return DB::transaction(function () use ($voteOption, $data) {
            return $this->repository->update($voteOption, $data);
        });
    }

    public function delete(VoteOption $voteOption): bool
    {
        return DB::transaction(function () use ($voteOption) {
            return $this->repository->delete($voteOption);
        });
    }
}
