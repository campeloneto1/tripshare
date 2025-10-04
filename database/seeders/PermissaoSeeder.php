<?php

namespace Database\Seeders;

use App\Models\Permissao;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissaoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Defina as permissões que você deseja criar
        $permissoes = [
            ['nome' => 'administrador', 'descricao' => 'Administrador do sistema'],
            ['nome' => 'create_usuarios', 'descricao' => 'Permite criar usuários'],
            ['nome' => 'update_usuarios', 'descricao' => 'Permite editar usuários'],
            ['nome' => 'delete_usuarios', 'descricao' => 'Permite deletar usuários'],
            ['nome' => 'list_usuarios', 'descricao' => 'Permite visualizar usuários'],
            ['nome' => 'restore_usuarios', 'descricao' => 'Permite restaurar usuários'],
            ['nome' => 'force_delete_usuarios', 'descricao' => 'Permite excluir permanentemente usuários'],
        ];

        foreach ($permissoes as $permissao) {
            Permissao::firstOrCreate(
                ['nome' => $permissao['nome']],
                ['descricao' => $permissao['descricao']]
            );
        }
    }
}
