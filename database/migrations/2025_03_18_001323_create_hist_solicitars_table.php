<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHistSolicitarsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hist_solicitars', function (Blueprint $table) {
            $table->id();
            $table->foreignid('solicitacao_id')->constrained('solicitars')->onDelete('cascade')->unique();
            $table->time('hora_aceito');
            $table->date('data_aceito');
            $table->foreignId('adm_id')->constrained('users');
            $table->time('hora_inicio')->default('00:00:00');
            $table->date('data_inicio')->nullable();
            $table->time('hora_final')->default('00:00:00');
            $table->date('data_final')->nullable();
            $table->text('obs_users')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hist_solicitars');
    }
}
