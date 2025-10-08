<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class UserRepository
{
    public function baseQuery()
    {
        return User::query();
    }

    public function all(array $filters)
    {
        $query = $this->baseQuery();

        if(!empty($filters['role_id'])){
            $query->where('role_id', $filters['role_id']);
        }

        if(!empty($filters['search'])){
            $this->filterSearch($query, $filters['search']);
        }

        if(!empty($filters['limit']) && is_numeric($filters['limit'])){
            return $query->paginate((int)$filters['limit']);
        }

        return $query->get();
    }

    public function allWithTrashed(array $filters)
    {
         $query = $this->baseQuery()->withTrashed();

        if(!empty($filters['search'])){
            $this->filterSearch($query, $filters['search']);
        }

        if(!empty($filters['limit']) && is_numeric($filters['limit'])){
            return $query->paginate((int)$filters['limit']);
        }

        return $query->get();
    }

    public function find(int $id): ?User
    {
        return $this->baseQuery()->find($id);
    }

    public function findWithTrashed(int $id): ?User
    {
        return $this->baseQuery()->withTrashed()->find($id);
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
        return $user->delete(); 
    }

    public function restore(User $user): bool
    {
        return $user->restore();
    }

    public function forceDelete(User $user): bool
    {
        return $user->forceDelete();
    }

    public function resetPassword(User $user, string $newPassword): User
    {
        $user->password = $newPassword;
        $user->save();
        return $user;
    }


    public function filterSearch(Builder $query, string $search)
    {
        $query->where(function($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('cpf', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%")
              ->orWhere('username', 'like', "%{$search}%");
        });
    }
}
