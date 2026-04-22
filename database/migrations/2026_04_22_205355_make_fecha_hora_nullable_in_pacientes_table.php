<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeFechaHoraNullableInPacientesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Using raw SQL to avoid needing doctrine/dbal dependency
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE pacientes MODIFY fecha_cita DATE NULL");
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE pacientes MODIFY hora_cita TIME NULL");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE pacientes MODIFY fecha_cita DATE NOT NULL");
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE pacientes MODIFY hora_cita TIME NOT NULL");
    }
}
