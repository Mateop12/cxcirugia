<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('turnero_config', function (Blueprint $table) {
            $table->id();
            $table->string('clave', 191)->unique();
            $table->string('valor');
            $table->string('descripcion')->nullable();
            $table->timestamps();
        });

        // Insertar configuraciones por defecto
        DB::table('turnero_config')->insert([
            [
                'clave' => 'turnos_visibles',
                'valor' => '5',
                'descripcion' => 'Número de turnos a mostrar simultáneamente en TV (3-6)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'clave' => 'refresh_interval',
                'valor' => '3000',
                'descripcion' => 'Intervalo de actualización en milisegundos',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'clave' => 'sonido_activo',
                'valor' => 'true',
                'descripcion' => 'Habilitar sonido al llamar turno',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'clave' => 'tiempo_parpadeo',
                'valor' => '5000',
                'descripcion' => 'Duración del parpadeo al llamar turno (ms)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('turnero_config');
    }
};
