<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear roles
        $administrador = Role::create(['name' => 'Administrador', 'guard_name' => 'api']);
        $agricultor = Role::create(['name' => 'Agricultor', 'guard_name' => 'api']);
        $beneficio = Role::create(['name' => 'Beneficio', 'guard_name' => 'api']);
        $pesoCabal = Role::create(['name' => 'PesoCabal', 'guard_name' => 'api']);

        // Aquí se podrían asignar permisos específicos a cada rol
        // Por ejemplo:
        // $permiso = Permission::create(['name' => 'administrar usuarios', 'guard_name' => 'api']);
        // $administrador->givePermissionTo($permiso);

        // Para los propósitos de este seeder, solo estamos creando los roles base
    }
}
