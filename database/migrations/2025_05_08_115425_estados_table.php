<?php


use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('estados', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 30);
            $table->string('contexto', 20);
            $table->timestamps();
            $table->softDeletes();
        });
        
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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estados');
    }
};