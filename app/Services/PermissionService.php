<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\Permission;
use App\Repositories\PermissionRepository;

class PermissionService
{
    public function __construct(private PermissionRepository $repository) {}

    public function list(array $filters)
    {
        return $this->repository->all($filters);
    }

    public function find(int $id): ?Permission
    {
        return $this->repository->find($id);
    }

    public function store(array $data): Permission
    {
        return DB::transaction(function () use ($data) {
            return $this->repository->create($data);
        });
    }

    public function update(Permission $permission, array $data): Permission
    {
        return DB::transaction(function () use ($permission, $data) {
            return $this->repository->update($permission, $data);
        });
    }

    public function delete(Permission $permission): bool
    {
        return DB::transaction(fn() => $this->repository->delete($permission));
    }
}
