<?php

namespace Database\Seeders;

use App\Models\PesoCabal;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PesoCabalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener el usuario con rol de peso cabal
        $usuario = User::where('email', 'pesocabal@test.com')->first();

        // Crear el peso cabal asociado al usuario
        PesoCabal::create([
            'nombre' => 'Carlos Peso',
            'codigo_empleado' => 'PC001',
            'area' => 'Báscula Central',
            'telefono' => '87654321',
            'user_id' => $usuario->id
        ]);
    }
}