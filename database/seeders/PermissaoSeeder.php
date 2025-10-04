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
            ['nome' => 'create_user', 'descricao' => 'Permite criar users'],
            ['nome' => 'edit_user', 'descricao' => 'Permite editar users'],
            ['nome' => 'delete_user', 'descricao' => 'Permite deletar users'],
            ['nome' => 'view_user', 'descricao' => 'Permite visualizar users'],
        ];

        foreach ($permissoes as $permissao) {
            Permissao::firstOrCreate(
                ['nome' => $permissao['nome']],
                ['descricao' => $permissao['descricao']]
            );
        }
    }
}
