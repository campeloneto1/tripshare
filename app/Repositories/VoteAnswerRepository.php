<?php

namespace App\Repositories;

use App\Models\VoteAnswer;
use Illuminate\Database\Eloquent\Builder;

class VoteAnswerRepository
{
    public function baseQuery(): Builder
    {
        return VoteAnswer::query()->with('user');
    }

    public function all(array $filters = [])
    {
        $query = $this->baseQuery()->latest();

        if (!empty($filters['vote_option_id'])) {
            $query->where('vote_option_id', $filters['vote_option_id']);
        }

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        return $query->get();
    }

    public function find(int $id): ?VoteAnswer
    {
        return $this->baseQuery()->find($id);
    }

    public function create(array $data): VoteAnswer
    {
        return VoteAnswer::create($data);
    }

    public function update(VoteAnswer $voteAnswer, array $data): VoteAnswer
    {
        $voteAnswer->update($data);
        return $voteAnswer;
    }

    public function delete(VoteAnswer $voteAnswer): bool
    {
        return $voteAnswer->delete();
    }
}
