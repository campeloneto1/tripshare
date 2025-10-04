<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::factory()->createMany([
            [
                'name' => 'admin',
                'description' => 'Administrador do sistema com todas as permissões.',
            ],
            [
                'name' => 'user',
                'description' => 'Usuário padrão com permissões limitadas.',
            ],
            [
                'name' => 'guest',
                'description' => 'Usuário convidado com acesso muito restrito.',
            ],
        ]);
    }
}
