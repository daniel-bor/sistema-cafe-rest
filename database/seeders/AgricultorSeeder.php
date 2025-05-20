<?php

namespace Database\Seeders;

use App\Models\Agricultor;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AgricultorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener el usuario con rol de agricultor
        $usuario = User::where('email', 'agricultor@test.com')->first();

        // Crear el agricultor asociado al usuario
        Agricultor::create([
            'nit' => '12345678',
            'nombre' => 'Juan',
            'apellido' => 'Pérez',
            'telefono' => '12345678',
            'direccion' => 'Ciudad de Guatemala',
            'observaciones' => 'Agricultor de prueba',
            'user_id' => $usuario->id
        ]);
    }
}
