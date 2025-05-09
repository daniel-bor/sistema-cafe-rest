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
        Schema::create('medidas_peso', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 20);
            $table->string('simbolo', 5);
            $table->timestamps();
            $table->softDeletes();
        });
        
        // Insertar medidas de peso básicas
        DB::table('medidas_peso')->insert([
            ['nombre' => 'Kilogramos', 'simbolo' => 'kg', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Quintales', 'simbolo' => 'qq', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Libras', 'simbolo' => 'lb', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Toneladas', 'simbolo' => 't', 'created_at' => now(), 'updated_at' => now()]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medidas_peso');
    }
};