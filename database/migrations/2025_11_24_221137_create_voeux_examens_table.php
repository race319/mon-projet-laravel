<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('voeux_examens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('code_enseignant');
            $table->unsignedBigInteger('code_creneau');
            $table->timestamps();

            
            $table->foreign('code_enseignant')
                  ->references('id')->on('users')
                  ->onDelete('cascade');


            $table->foreign('code_creneau')
                  ->references('code_creneau')->on('creneau')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voeux_examens');
    }
};
