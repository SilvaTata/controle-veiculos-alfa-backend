<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateModelosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('modelos', function (Blueprint $table) {
            $table->id();
            $table->string('modelo', 50)->unique();
            $table->timestamps();
        });

        DB::table('modelos')->insert([
            ['modelo' => 'Corolla'],
            ['modelo' => 'Civic'],
            ['modelo' => 'Mustang'],
            ['modelo' => 'Onix'],
            ['modelo' => 'Golf'],
            ['modelo' => 'Cronos'],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('modelos');
    }
}
