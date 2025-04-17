<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVeiculosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('veiculos', function (Blueprint $table) {
            $table->id();
            $table->string('placa', 10)->unique();
            $table->string('chassi', 17)->unique();
            $table->enum('status_veiculo', ['disponível', 'em uso', 'manutenção'])->default('disponível');
            $table->string('qr_code', 100)->unique()->nullable();
            $table->integer('ano');
            $table->string('cor', 30);
            $table->integer('capacidade');
            $table->text('obs_veiculo')->nullable();
            $table->integer('km_revisao')->default(10000);
            $table->foreignId('marca_id')->constrained('marcas');
            $table->foreignId('modelo_id')->constrained('modelos');
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
        Schema::dropIfExists('veiculos');
    }
}
