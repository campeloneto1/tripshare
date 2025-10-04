<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Assumindo que você já tenha roles e permissões criados
        $adminRole = Role::where('name', 'admin')->first();
        $userRole = Role::where('name', 'user')->first();

        $permissions = Permission::all();

        if ($adminRole) {
            // Atribui todas as permissões ao role admin
            $adminRole->permissions()->sync($permissions->pluck('id')->toArray());
        }

        if ($userRole) {
            // Atribui permissões limitadas ao role user
            $userPermissions = $permissions->whereIn('name', ['list_users']);
            $userRole->permissions()->sync($userPermissions->pluck('id')->toArray());
        }
    }
}
