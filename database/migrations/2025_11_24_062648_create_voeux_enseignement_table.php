<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
{
    Schema::create('voeux_enseignement', function (Blueprint $table) {
        $table->id();

        
        $table->foreignId('code_enseignant')->constrained('users')->onDelete('cascade');

        $table->integer('code_jour');      
        $table->integer('code_seance');    

        $table->timestamps();
    });
}
   

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('voeux_enseignement');
    }
};
