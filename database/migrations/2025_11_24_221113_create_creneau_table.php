<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('creneau', function (Blueprint $table) {
            $table->id('code_creneau'); // BIGINT UNSIGNED PRIMARY KEY
            $table->date('date');
            $table->time('heure_debut');
            $table->time('heure_fin');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('creneau');
    }
};
