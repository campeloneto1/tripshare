<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Defina as permissões que você deseja criar
        $permissions = [
            ['name' => 'administrator', 'description' => 'Administrador do sistema'],
            ['name' => 'create_users', 'description' => 'Permite criar usuários'],
            ['name' => 'update_users', 'description' => 'Permite editar usuários'],
            ['name' => 'delete_users', 'description' => 'Permite deletar usuários'],
            ['name' => 'list_users', 'description' => 'Permite visualizar usuários'],
            ['name' => 'restore_users', 'description' => 'Permite restaurar usuários'],
            ['name' => 'force_delete_users', 'description' => 'Permite excluir permanentemente usuários'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name']],
                ['description' => $permission['description']]
            );
        }
    }
}
