<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository
{
    public function all()
    {
        return User::query(); // só ativos
    }

    public function allWithTrashed()
    {
        return User::withTrashed()->query(); // ativos + soft deleted
    }

    public function find(int $id): ?User
    {
        return User::find($id);
    }

    public function findWithTrashed(int $id): ?User
    {
        return User::withTrashed()->find($id);
    }

    public function create(array $data): User
    {
        return User::create($data);
    }

    public function update(User $user, array $data): User
    {
        $user->update($data);
        return $user;
    }

    public function delete(User $user): bool
    {
        return $user->delete(); // soft delete
    }

    public function restore(User $user): bool
    {
        return $user->restore();
    }

    public function forceDelete(User $user): bool
    {
        return $user->forceDelete();
    }
}
