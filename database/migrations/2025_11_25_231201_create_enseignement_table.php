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

            // Clés étrangères vers les tables existantes
            $table->unsignedBigInteger('code_enseignant'); // users.id
            $table->unsignedBigInteger('code_groupe');     // groupes.code_groupe
            $table->unsignedBigInteger('code_matiere');    // matieres.code_matiere

            $table->enum('nature_enseignement', ['Cours','TD','TP'])->default('Cours');
            $table->timestamps();

            // Définition des clés étrangères
            $table->foreign('code_enseignant')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');

            $table->foreign('code_groupe')
                  ->references('code_groupe')
                  ->on('groupes')
                  ->onDelete('cascade');

            $table->foreign('code_matiere')
                  ->references('code_matiere')
                  ->on('matieres')
                  ->onDelete('cascade');

            // Unique pour éviter les doublons enseignant/groupe/matiere/nature
            $table->unique(['code_enseignant','code_groupe','code_matiere','nature_enseignement'], 'ens_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enseignement');
    }
};
