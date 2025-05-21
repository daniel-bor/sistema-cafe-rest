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
            BeneficioSeeder::class,  // Crear beneficio para el usuario con rol de beneficio
        ]);

       DB::table('estados')->insert([
            // Estados para transportes y transportistas
            ['nombre' => 'Activo', 'contexto' => 'transporte', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Inactivo', 'contexto' => 'transporte', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Activo', 'contexto' => 'transportista', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Inactivo', 'contexto' => 'transportista', 'created_at' => now(), 'updated_at' => now()],

            // Estados para cuentas
            ['nombre' => 'Creada', 'contexto' => 'cuenta', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Abierta', 'contexto' => 'cuenta', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Cerrada', 'contexto' => 'cuenta', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Confirmada', 'contexto' => 'cuenta', 'created_at' => now(), 'updated_at' => now()],

            // Estados para pesajes
            ['nombre' => 'Pendiente', 'contexto' => 'pesaje', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Aprobado', 'contexto' => 'pesaje', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Iniciado', 'contexto' => 'pesaje', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Finalizado', 'contexto' => 'pesaje', 'created_at' => now(), 'updated_at' => now()],

            // Estados para parcialidades
            ['nombre' => 'Pendiente', 'contexto' => 'parcialidad', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Aprobada', 'contexto' => 'parcialidad', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Rechazada', 'contexto' => 'parcialidad', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Verificada', 'contexto' => 'parcialidad', 'created_at' => now(), 'updated_at' => now()],
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
