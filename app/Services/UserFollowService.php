<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\UserFollow;
use App\Repositories\UserFollowRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class UserFollowService
{
    public function __construct(
        private UserFollowRepository $repository) {}

    public function list(array $filters)
    {
        return $this->repository->all($filters);
    }

    public function find(int $id): ?UserFollow
    {
        $return = $this->repository->find($id);
        return $return;
    }

    public function store(array $data): UserFollow
    {
        return DB::transaction(function () use ($data) {
            $data['follower_id'] = Auth::id();
            $return = $this->repository->create($data);
            return $return;
        });
    }

    public function update(UserFollow $userFollow, array $data): UserFollow
    {
        return DB::transaction(function () use ($userFollow, $data) {
            return $this->repository->update($userFollow, $data);
        });
    }

    public function delete(UserFollow $userFollow): bool
    {
        return DB::transaction(fn() => $this->repository->delete($userFollow));
    }

    public function listByUser(int $userId)
    {
        $filters = ['follower_id' => $userId];
        return $this->repository->all($filters);
    }
}
