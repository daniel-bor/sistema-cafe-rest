co<?php

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
        Schema::create('boletas', function (Blueprint $table) {
            $table->id();
            $table->string('no_boleta', 20)->unique();
            $table->dateTime('fecha_boleta');
            $table->foreignId('parcialidad_id')->constrained('parcialidades');
            $table->unsignedBigInteger('generada_por');
            $table->foreign('generada_por')->references('id')->on('peso_cabals');
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('boletas');
    }
};