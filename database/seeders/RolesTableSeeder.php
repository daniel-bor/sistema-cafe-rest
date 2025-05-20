<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Insertar roles en la tabla 'roles' para la relación con usuarios
        DB::table('roles')->insert([
            ['name' => 'Agricultor', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Beneficio', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'PesoCabal', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Administrador', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()]
        ]);
    }
}
