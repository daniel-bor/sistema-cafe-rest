<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        //     ''
        // ]);

        // Ejecutar seeders para roles y permisos de Spatie
        $this->call([
            RolesTableSeeder::class, // Crear roles en tabla roles
            RoleSeeder::class,       // Crear roles en tabla de permisos
            UserSeeder::class,       // Crear usuarios
            AgricultorSeeder::class, // Crear agricultor para el usuario con rol de agricultor
        ]);

        // Insertar estados iniciales
        DB::table('estados')->insert([
            // Estados para transportes y transportistas
            ['nombre' => 'Activo', 'contexto' => 'transporte', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Inactivo', 'contexto' => 'transporte', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Activo', 'contexto' => 'transportista', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Inactivo', 'contexto' => 'transportista', 'created_at' => now(), 'updated_at' => now()],

            // Estados para solicitudes de pesaje
            ['nombre' => 'Registrada', 'contexto' => 'solicitud', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'En Proceso', 'contexto' => 'solicitud', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Completada', 'contexto' => 'solicitud', 'created_at' => now(), 'updated_at' => now()],

            // Estados para cuentas
            ['nombre' => 'Registrada', 'contexto' => 'cuenta', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Cuenta Creada', 'contexto' => 'cuenta', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Cuenta Abierta', 'contexto' => 'cuenta', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Cuenta Cerrada', 'contexto' => 'cuenta', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Cuenta Confirmada', 'contexto' => 'cuenta', 'created_at' => now(), 'updated_at' => now()],

            // Estados para pesajes
            ['nombre' => 'Sin Peso', 'contexto' => 'pesaje', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Pendiente de Verificación', 'contexto' => 'pesaje', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Pesaje Iniciado', 'contexto' => 'pesaje', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Pesaje Finalizado', 'contexto' => 'pesaje', 'created_at' => now(), 'updated_at' => now()],

            // Estados para parcialidades
            ['nombre' => 'Pendiente', 'contexto' => 'parcialidad', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Aceptada', 'contexto' => 'parcialidad', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Rechazada', 'contexto' => 'parcialidad', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Insertar medidas de peso básicas
        DB::table('medidas_peso')->insert([
            ['nombre' => 'Kilogramos', 'simbolo' => 'kg', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Quintales', 'simbolo' => 'qq', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Libras', 'simbolo' => 'lb', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Toneladas', 'simbolo' => 't', 'created_at' => now(), 'updated_at' => now()]
        ]);
    }
}
