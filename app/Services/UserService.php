<?php

namespace App\Services;

use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class UserService
{
    public function __construct(private UserRepository $repository) {}

    public function list(array $filters)
    {
        return $this->repository->all($filters);
    }

    public function listAll(array $filters)
    {
        return $this->repository->allWithTrashed($filters);
    }

    public function find(int $id): ?User
    {
        return $this->repository->find($id);
    }

    public function findWithTrashed(int $id): ?User
    {
        return $this->repository->findWithTrashed($id);
    }

    public function store(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $data['password'] = Hash::make($data['password']);
            return $this->repository->create($data);
        });
    }

    public function update(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data) {
            if (!empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }
            return $this->repository->update($user, $data);
        });
    }

    public function delete(User $user): bool
    {
        return DB::transaction(fn() => $this->repository->delete($user));
    }

    public function restore(User $user): bool
    {
        return DB::transaction(fn() => $this->repository->restore($user));
    }

    public function forceDelete(User $user): bool
    {
        return DB::transaction(fn() => $this->repository->forceDelete($user));
    }
}
