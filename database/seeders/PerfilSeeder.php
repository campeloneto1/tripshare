<?php

namespace Database\Seeders;

use App\Models\Perfil;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PerfilSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Perfil::factory()->createMany([
            [
                'nome' => 'admin',
                'descricao' => 'Administrador do sistema com todas as permissões.',
            ],
            [
                'nome' => 'user',
                'descricao' => 'Usuário padrão com permissões limitadas.',
            ],
            [
                'nome' => 'guest',
                'descricao' => 'Usuário convidado com acesso muito restrito.',
            ],
        ]);
    }
}
