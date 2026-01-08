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
        Schema::create('destinos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre'); // Ej: "Consultorio 1", "Quirófano A", "Laboratorio"
            $table->string('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->integer('orden')->default(0); // Para ordenar en selectores
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('destinos');
    }
};
