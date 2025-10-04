<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\Permissao;
use App\Repositories\PermissaoRepository;

class PermissaoService
{
    public function __construct(private PermissaoRepository $repository) {}

    public function list()
    {
        return $this->repository->all()->paginate(10);
    }

    public function find(int $id): ?Permissao
    {
        return $this->repository->find($id);
    }

    public function store(array $data): Permissao
    {
        return DB::transaction(function () use ($data) {
            return $this->repository->create($data);
        });
    }

    public function update(Permissao $permissao, array $data): Permissao
    {
        return DB::transaction(function () use ($permissao, $data) {
            return $this->repository->update($permissao, $data);
        });
    }

    public function delete(Permissao $permissao): bool
    {
        return DB::transaction(fn() => $this->repository->delete($permissao));
    }
}
