<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absence', function (Blueprint $table) {
            $table->id();

            
            $table->string('code_etudiant', 100);   
            $table->string('code_groupe', 50);     // Ajouté pour filtrage par groupe
            $table->string('code_matiere', 50);     
            $table->string('code_enseignant', 100);  

            $table->integer('seance')->default(1);
            $table->enum('statut', ['Absent','Present'])->default('Absent');
            $table->boolean('justifie')->default(false);
            $table->boolean('elimination')->default(false); // nouvelle colonne

            $table->timestamps();

           
            $table->foreign('code_matiere')
                  ->references('code_matiere')
                  ->on('matieres')
                  ->onDelete('cascade');

            $table->foreign('code_groupe')
                  ->references('code_groupe')
                  ->on('groupes')
                  ->onDelete('cascade');

            // code_enseignant reste relation logique vers users.code_enseignant
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absence');
    }
};