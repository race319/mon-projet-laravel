<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('creneau', function (Blueprint $table) {
            $table->id();                             // ✅ auto increment
            $table->string('code_creneau')->unique(); // ✅ string unique
            $table->string('date');                   // ✅ date en string
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