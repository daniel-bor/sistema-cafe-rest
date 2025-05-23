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
        Schema::create('peso_cabals', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('codigo_empleado', 20)->nullable();
            $table->string('area', 50)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peso_cabals');
    }
};