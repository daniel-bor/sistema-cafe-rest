<?php

namespace Database\Seeders;

use App\Models\Beneficio;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BeneficioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener el usuario con rol de beneficio
        $usuario = User::where('email', 'beneficio@test.com')->first();

        // Crear el beneficio asociado al usuario
        Beneficio::create([
            'nombre' => 'Beneficio Prueba',
            'direccion' => 'Ciudad de Guatemala',
            'telefono' => '87654321',
            'descripcion' => 'Beneficio de prueba',
            'user_id' => $usuario->id
        ]);
    }
}