<?php

namespace App\Repositories;

use App\Models\Permissao;

class PermissaoRepository
{
    public function all()
    {
        return Permissao::query(); 
    }

    public function find(int $id): ?Permissao
    {
        return Permissao::find($id);
    }

    public function create(array $data): Permissao
    {
        return Permissao::create($data);
    }

    public function update(Permissao $permissao, array $data): Permissao
    {
        $permissao->update($data);
        return $permissao;
    }

    public function delete(Permissao $permissao): bool
    {
        return $permissao->delete();
    }
}
