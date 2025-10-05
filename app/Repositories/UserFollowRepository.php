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
        $user = Auth::user();
        $query = $this->baseQuery();

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
