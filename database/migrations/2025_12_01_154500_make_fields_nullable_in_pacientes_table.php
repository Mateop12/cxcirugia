<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class MakeFieldsNullableInPacientesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Using raw SQL to avoid needing doctrine/dbal dependency
        DB::statement("ALTER TABLE pacientes MODIFY apellido VARCHAR(255) NULL");
        DB::statement("ALTER TABLE pacientes MODIFY area_id BIGINT UNSIGNED NULL");
        DB::statement("ALTER TABLE pacientes MODIFY estado_id BIGINT UNSIGNED NULL");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE pacientes MODIFY apellido VARCHAR(255) NOT NULL");
        DB::statement("ALTER TABLE pacientes MODIFY area_id BIGINT UNSIGNED NOT NULL");
        DB::statement("ALTER TABLE pacientes MODIFY estado_id BIGINT UNSIGNED NOT NULL");
    }
}
