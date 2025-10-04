<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\Viagem;
use App\Repositories\ViagemRepository;

class ViagemService
{
    public function __construct(private ViagemRepository $repository) {}

    public function list()
    {
        return $this->repository->all()->paginate(10);
    }

    public function find(int $id): ?Viagem
    {
        return $this->repository->find($id);
    }

    public function store(array $data): Viagem
    {
        return DB::transaction(function () use ($data) {
            return $this->repository->create($data);
        });
    }

    public function update(Viagem $viagem, array $data): Viagem
    {
        return DB::transaction(function () use ($viagem, $data) {
            return $this->repository->update($viagem, $data);
        });
    }

    public function delete(Viagem $viagem): bool
    {
        return DB::transaction(fn() => $this->repository->delete($viagem));
    }
}
