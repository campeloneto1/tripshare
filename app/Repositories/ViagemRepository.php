<?php

namespace App\Repositories;

use App\Models\Viagem;

class ViagemRepository
{
    public function all()
    {
        return Viagem::query(); 
    }

    public function find(int $id): ?Viagem
    {
        return Viagem::find($id);
    }

    public function create(array $data): Viagem
    {
        return Viagem::create($data);
    }

    public function update(Viagem $viagem, array $data): Viagem
    {
        $viagem->update($data);
        return $viagem;
    }

    public function delete(Viagem $viagem): bool
    {
        return $viagem->delete();
    }
}
