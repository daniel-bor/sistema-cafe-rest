<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('rols', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 20);
            $table->timestamps();
            $table->softDeletes();
        });
        
        // Insertar roles básicos
        DB::table('rols')->insert([
            ['nombre' => 'Agricultor', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Beneficio', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Pesaje', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Administrador', 'created_at' => now(), 'updated_at' => now()]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rols');
    }
};