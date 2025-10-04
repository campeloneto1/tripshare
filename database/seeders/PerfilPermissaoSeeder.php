<?php

namespace Database\Seeders;

use App\Models\Perfil;
use App\Models\Permissao;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PerfilPermissaoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Assumindo que você já tenha perfis e permissões criados
        $adminPerfil = Perfil::where('nome', 'admin')->first();
        $userPerfil = Perfil::where('nome', 'user')->first();

        $permissoes = Permissao::all();

        if ($adminPerfil) {
            // Atribui todas as permissões ao perfil admin
            $adminPerfil->permissoes()->sync($permissoes->pluck('id')->toArray());
        }

        if ($userPerfil) {
            // Atribui permissões limitadas ao perfil user
            $userPermissoes = $permissoes->whereIn('nome', ['view_user']);
            $userPerfil->permissoes()->sync($userPermissoes->pluck('id')->toArray());
        }
    }
}
