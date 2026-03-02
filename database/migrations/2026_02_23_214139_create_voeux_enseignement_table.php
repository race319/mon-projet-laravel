<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('voeux_enseignement', function (Blueprint $table) {
            $table->id();

            $table->string('code_enseignant', 100);
            $table->foreign('code_enseignant')
                  ->references('code_enseignant')
                  ->on('enseignants') 
                  ->onDelete('cascade');

            $table->integer('code_jour');
            $table->integer('code_seance');

            $table->timestamps();

            $table->unique(['code_enseignant', 'code_jour', 'code_seance']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('voeux_enseignement');
    }
};