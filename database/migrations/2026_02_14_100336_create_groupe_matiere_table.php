<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('groupe_matiere', function (Blueprint $table) {
           

            $table->string('code_groupe', 50);
            $table->string('code_matiere', 50);

            $table->timestamps();

            
            $table->foreign('code_groupe')
                  ->references('code_groupe')
                  ->on('groupes')
                  ->onDelete('cascade');

            $table->foreign('code_matiere')
                  ->references('code_matiere')
                  ->on('matieres')
                  ->onDelete('cascade');

            
            $table->unique(['code_groupe', 'code_matiere']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('groupe_matiere');
    }
};