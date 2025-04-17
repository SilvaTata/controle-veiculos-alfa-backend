<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up()
    {
        Schema::create('cargos', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->check("nome IN ('Adm', 'User')");
            $table->timestamps();
        });

        // Adicionando os cargos iniciais
        DB::table('cargos')->insert([
            ['nome' => 'Adm'],
            ['nome' => 'User'],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('cargos');
    }
};
