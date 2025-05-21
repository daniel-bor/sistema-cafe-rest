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
       Schema::create('pesajes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agricultor_id')->constrained('agricultores');
            $table->foreignId('medida_peso_id')->constrained('medidas_peso');
            $table->decimal('peso_total', 12, 2)->default(0);
            $table->decimal('cantidad_total', 12, 2);
            $table->decimal('tolerancia', 5, 2);
            $table->decimal('precio_unitario', 10, 2);
            $table->integer('cantidad_parcialidades');
            $table->foreignId('estado_id')->constrained('estados');
            $table->foreignId('cuenta_id')->nullable()->constrained('cuentas');
            $table->timestamp('fecha_creacion')->default(now());
            $table->dateTime('fecha_inicio')->nullable();
            $table->dateTime('fecha_cierre')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pesajes');
    }
};