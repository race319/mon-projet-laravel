<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inscrit', function (Blueprint $table) {
            $table->id(); 
            $table->string('code_etudiant', 100);
            $table->string('code_groupe', 50);
            $table->timestamps();

            
            $table->foreign('code_groupe')
                  ->references('code_groupe')
                  ->on('groupes')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inscrit');
    }
};