<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * @return void
     */
    public function up(): void
    {
        Schema::create('seances', function (Blueprint $table) {
            $table->id();
            $table->string('date_seance'); // date en format texte
            $table->string('code_jour');
            $table->string('numero_seance');
            $table->string('code_salle');
            $table->string('code_matiere');
            $table->string('code_typeseance');
            $table->string('code_enseignant');
            $table->string('code_groupe');
            $table->string('code_effectue')->default('P'); // valeur par défaut
            $table->unsignedBigInteger('code_surveillance')->nullable(); // clé étrangère vers users
            $table->string('locked_at')->nullable(); // nullable
            $table->timestamps();

            $table->foreign('code_surveillance')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('seances');
    }
};
