<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener IDs de los roles
        $rolAgricultor = DB::table('roles')->where('name', 'Agricultor')->first()->id;
        $rolBeneficio = DB::table('roles')->where('name', 'Beneficio')->first()->id;
        $rolPesoCabal = DB::table('roles')->where('name', 'PesoCabal')->first()->id;
        $rolAdmin = DB::table('roles')->where('name', 'Administrador')->first()->id;

        // Usuario Administrador
        $admin = User::create([
            'name' => 'Administrador Test',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'rol_id' => $rolAdmin,
            'activo' => true,
        ]);
        $admin->assignRole('Administrador');

        // Usuario Agricultor
        $agricultor = User::create([
            'name' => 'Agricultor Test',
            'email' => 'agricultor@test.com',
            'password' => Hash::make('password'),
            'rol_id' => $rolAgricultor,
            'activo' => true,
        ]);
        $agricultor->assignRole('Agricultor');

        // Usuario Beneficio
        $beneficio = User::create([
            'name' => 'Beneficio Test',
            'email' => 'beneficio@test.com',
            'password' => Hash::make('password'),
            'rol_id' => $rolBeneficio,
            'activo' => true,
        ]);
        $beneficio->assignRole('Beneficio');

        // Usuario PesoCabal
        $pesoCabal = User::create([
            'name' => 'PesoCabal Test',
            'email' => 'pesocabal@test.com',
            'password' => Hash::make('password'),
            'rol_id' => $rolPesoCabal,
            'activo' => true,
        ]);
        $pesoCabal->assignRole('PesoCabal');
    }
}
