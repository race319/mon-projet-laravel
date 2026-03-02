<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enseignement', function (Blueprint $table) {

           
            $table->id();

            $table->string('code_enseignant', 100);
            $table->string('code_groupe', 50);
            $table->string('code_matiere', 200);
            $table->string('code_typeseance', 10);
            $table->string('date_seance', 20);
            $table->timestamps();

            // 🔗 Foreign keys (OK avec doublons)
            $table->foreign('code_groupe')
                  ->references('code_groupe')
                  ->on('groupes')
                  ->onDelete('cascade');

            $table->foreign('code_matiere')
                  ->references('code_matiere')
                  ->on('matieres')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enseignement');
    }
};