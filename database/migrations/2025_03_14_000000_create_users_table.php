<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('cpf', 11)->unique();
            $table->string('name'); 
            $table->string('password'); 
            $table->string('email')->unique();
            $table->string('telefone', 20)->default('Não informado');
            $table->enum('status', ['ativo', 'inativo'])->default('ativo');
            $table->foreignId('cargo_id')->constrained('cargos')->onDelete('cascade');
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
