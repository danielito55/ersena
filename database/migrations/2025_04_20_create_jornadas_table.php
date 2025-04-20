<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('jornadas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre'); // mañana, tarde, noche
            $table->time('hora_entrada');
            $table->time('tolerancia')->default('00:05:00'); // 5 minutos de tolerancia por defecto
            $table->timestamps();
        });

        // Insertar jornadas predefinidas
        DB::table('jornadas')->insert([
            ['nombre' => 'mañana', 'hora_entrada' => '06:00:00', 'tolerancia' => '00:05:00', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'tarde', 'hora_entrada' => '12:00:00', 'tolerancia' => '00:05:00', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'noche', 'hora_entrada' => '18:00:00', 'tolerancia' => '00:05:00', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('jornadas');
    }
};