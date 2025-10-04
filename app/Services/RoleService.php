<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\Role;
use App\Repositories\RoleRepository;

class RoleService
{
    public function __construct(private RoleRepository $repository) {}

    public function list(array $filters)
    {
        return $this->repository->all($filters);
    }

    public function find(int $id): ?Role
    {
        return $this->repository->find($id);
    }

    public function store(array $data): Role
    {
        return DB::transaction(function () use ($data) {
            return $this->repository->create($data);
        });
    }

    public function update(Role $role, array $data): Role
    {
        return DB::transaction(function () use ($role, $data) {
            return $this->repository->update($role, $data);
        });
    }

    public function delete(Role $role): bool
    {
        return DB::transaction(fn() => $this->repository->delete($role));
    }

    public function getPermissions(Role $role)
    {
        return $role->permissions()->get();
    }

    public function syncPermissions(Role $role, array $permissionIds): void
    {
        DB::transaction(function () use ($role, $permissionIds) {
            $role->permissions()->sync($permissionIds);
        });
    }
}
