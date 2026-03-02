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

            $table->string('code_enseignant', 100);
            $table->foreign('code_enseignant')
                  ->references('code_enseignant')
                  ->on('enseignants')
                  ->onDelete('cascade');

            $table->string('code_creneau'); // ✅ string au lieu de unsignedBigInteger
            $table->foreign('code_creneau')
                  ->references('code_creneau')
                  ->on('creneau')
                  ->onDelete('cascade');

            $table->timestamps();
            $table->unique(['code_enseignant', 'code_creneau']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voeux_examens');
    }
};