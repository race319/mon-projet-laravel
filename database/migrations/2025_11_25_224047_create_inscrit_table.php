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
            $table->unsignedBigInteger('code_etudiant'); // référence à users.id
            $table->unsignedBigInteger('code_groupe');   // référence à groupes.code_groupe
            $table->date('date_inscription')->default(DB::raw('CURRENT_DATE'));
            $table->timestamps();

            $table->foreign('code_etudiant')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');

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
