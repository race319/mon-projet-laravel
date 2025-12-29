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
            $table->unsignedBigInteger('code_etudiant');   
            $table->unsignedBigInteger('code_matiere');     
            $table->unsignedBigInteger('code_enseignant');  
            $table->integer('seance')->default(1);
            $table->enum('statut', ['Absent','Present'])->default('Absent');
            $table->boolean('justifie')->default(false);
            $table->timestamps();

            
            $table->foreign('code_etudiant')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('code_matiere')->references('code_matiere')->on('matieres')->onDelete('cascade');
            $table->foreign('code_enseignant')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absence');
    }
};
