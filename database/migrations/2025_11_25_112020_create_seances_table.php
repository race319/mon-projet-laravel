<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('seances', function (Blueprint $table) {
            $table->id();
            $table->date('date_seance');
            $table->enum('heure_seance', [
            '08:00', '09:00', '10:00', '11:00',
             '12:00', '14:00', '15:00', '16:00','13:00','17:00'
             ]);

            $table->integer('numero_seance');
            $table->unsignedBigInteger('code_salle'); 
           $table->enum('nature', ['Rattrapage', 'Normale', 'Absence', 'Justifiee']);
            $table->integer('nb_seances')->default(1);
            $table->unsignedBigInteger('code_enseignant');
            $table->unsignedBigInteger('code_groupe');
            $table->boolean('etat')->default(1); 
            $table->timestamps();

            
            $table->foreign('code_salle')->references('code_salle')->on('salles')->onDelete('cascade');
            $table->foreign('code_enseignant')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('code_groupe')->references('code_groupe')->on('groupes')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seances');
    }
};
