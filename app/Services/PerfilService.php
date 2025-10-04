<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\Perfil;
use App\Repositories\PerfilRepository;

class PerfilService
{
    public function __construct(private PerfilRepository $repository) {}

    public function list()
    {
        return $this->repository->all()->paginate(10);
    }

    public function find(int $id): ?Perfil
    {
        return $this->repository->find($id);
    }

    public function store(array $data): Perfil
    {
        return DB::transaction(function () use ($data) {
            return $this->repository->create($data);
        });
    }

    public function update(Perfil $perfil, array $data): Perfil
    {
        return DB::transaction(function () use ($perfil, $data) {
            return $this->repository->update($perfil, $data);
        });
    }

    public function delete(Perfil $perfil): bool
    {
        return DB::transaction(fn() => $this->repository->delete($perfil));
    }

    public function getPermissoes(Perfil $perfil)
    {
        return $perfil->permissoes()->get();
    }

    public function syncPermissoes(Perfil $perfil, array $permissaoIds): void
    {
        DB::transaction(function () use ($perfil, $permissaoIds) {
            $perfil->permissoes()->sync($permissaoIds);
        });
    }
}
