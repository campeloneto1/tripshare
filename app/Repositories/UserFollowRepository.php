<?php

namespace App\Repositories;

use App\Models\UserFollow;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class UserFollowRepository
{
    public function baseQuery()
    {
        $query = UserFollow::query();
        return $query;
    }

    public function all(array $filters)
    {
        $query = $this->baseQuery();

        // Aplica filtro de follower_id
        if (isset($filters['follower_id'])) {
            $query->where('follower_id', $filters['follower_id']);
        }

        // Aplica filtro de following_id
        if (isset($filters['following_id'])) {
            $query->where('following_id', $filters['following_id']);
        }

        // Aplica filtro de status
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Carrega relationships
        $query->with(['follower', 'following']);

        return $query->get();
    }
    public function find(int $id): ?UserFollow
    {
        return $this->baseQuery()->find($id);
    }

    public function create(array $data): UserFollow
    {
        return UserFollow::create($data);
    }

    public function update(UserFollow $userFollow, array $data): UserFollow
    {
        $userFollow->update($data);
        return $userFollow;
    }

    public function delete(UserFollow $userFollow): bool
    {
        return $userFollow->delete();
    }
}
