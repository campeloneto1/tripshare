<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Perfil extends Model
{
    /** @use HasFactory<\Database\Factories\PerfilFactory> */
    use HasFactory;

    protected $table = 'perfis';

    protected $fillable = ['nome', 'descricao'];

    public function permissoes()
    {
        return $this->belongsToMany(Permissao::class, 'perfis_permissoes', 'perfil_id', 'permissao_id');

    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
