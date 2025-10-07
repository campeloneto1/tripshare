<?php

namespace App\Repositories;

use App\Models\VoteOption;
use Illuminate\Database\Eloquent\Builder;

class VoteOptionRepository
{
    public function baseQuery(): Builder
    {
        return VoteOption::query()->with('votes');
    }

    public function all(array $filters = [])
    {
        $query = $this->baseQuery()->latest();

        if (!empty($filters['vote_question_id'])) {
            $query->where('vote_question_id', $filters['vote_question_id']);
        }

        return $query->get();
    }

    public function find(int $id): ?VoteOption
    {
        return $this->baseQuery()->find($id);
    }

    public function create(array $data): VoteOption
    {
        return VoteOption::create($data);
    }

    public function update(VoteOption $voteOption, array $data): VoteOption
    {
        $voteOption->update($data);
        return $voteOption;
    }

    public function delete(VoteOption $voteOption): bool
    {
        return $voteOption->delete();
    }
}
