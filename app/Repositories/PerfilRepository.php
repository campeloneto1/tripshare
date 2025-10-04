<?php

namespace App\Repositories;

use App\Models\Perfil;

class PerfilRepository
{
    public function all()
    {
        return Perfil::query(); 
    }

    public function find(int $id): ?Perfil
    {
        return Perfil::find($id);
    }

    public function create(array $data): Perfil
    {
        return Perfil::create($data);
    }

    public function update(Perfil $perfil, array $data): Perfil
    {
        $perfil->update($data);
        return $perfil;
    }

    public function delete(Perfil $perfil): bool
    {
        return $perfil->delete();
    }
}
