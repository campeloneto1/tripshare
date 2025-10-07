<?php

namespace App\Repositories;

use App\Models\VoteQuestion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class VoteQuestionRepository
{
    public function baseQuery(): Builder
    {
        return VoteQuestion::query()->with('options.votes');
    }

    public function all(array $filters = [])
    {
        $query = $this->baseQuery()->latest();

        if (!empty($filters['votable_type'])) {
            $query->where('votable_type', $filters['votable_type']);
        }

        if (!empty($filters['votable_id'])) {
            $query->where('votable_id', $filters['votable_id']);
        }

        if (!empty($filters['is_closed'])) {
            $query->where('is_closed', (bool) $filters['is_closed']);
        }

        return $query->get();
    }

    public function find(int $id): ?VoteQuestion
    {
        return $this->baseQuery()->find($id);
    }

    public function create(array $data): VoteQuestion
    {
        return VoteQuestion::create($data);
    }

    public function update(VoteQuestion $voteQuestion, array $data): VoteQuestion
    {
        $voteQuestion->update($data);
        return $voteQuestion;
    }

    public function delete(VoteQuestion $voteQuestion): bool
    {
        return $voteQuestion->delete();
    }
}
