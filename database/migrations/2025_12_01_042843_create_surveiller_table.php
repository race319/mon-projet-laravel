<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surveiller', function (Blueprint $table) {
            $table->id();
            
            // Clés étrangères
            $table->unsignedBigInteger('code_enseignant');
            $table->unsignedBigInteger('code_creneau');
            
            // Qualité : S = Surveillant, C = Commission
            $table->enum('qualite', ['S', 'C'])->default('S');
            
            $table->timestamps();

            // Index pour optimiser les requêtes
            $table->index('code_enseignant');
            $table->index('code_creneau');
        });

        // Ajouter les contraintes de clés étrangères APRÈS la création
        Schema::table('surveiller', function (Blueprint $table) {
            // Clé étrangère vers users
            $table->foreign('code_enseignant')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');

            // ✅ Clé étrangère vers creneau (singulier selon votre modèle)
            $table->foreign('code_creneau')
                  ->references('code_creneau')
                  ->on('creneau')  // ← Changé de 'creneaux' à 'creneau'
                  ->onDelete('cascade');

            // Contrainte d'unicité
            $table->unique(['code_enseignant', 'code_creneau'], 'unique_enseignant_creneau');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surveiller');
    }
};