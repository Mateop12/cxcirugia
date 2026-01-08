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
        Schema::table('pacientes', function (Blueprint $table) {
            $table->integer('numero_turno')->nullable()->after('observacion');
            $table->timestamp('turno_llamado_at')->nullable()->after('numero_turno');
            $table->unsignedBigInteger('destino_id')->nullable()->after('turno_llamado_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pacientes', function (Blueprint $table) {
            $table->dropColumn(['numero_turno', 'turno_llamado_at', 'destino_id']);
        });
    }
};
