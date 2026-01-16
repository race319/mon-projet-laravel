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
   public function up(): void
    {
        Schema::create('enseignants', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->unique(); // lien avec la table users
            $table->integer('charge_enseignement')->default(0); // heures d'enseignement
            $table->integer('charge_surveillance')->default(0); // heures de surveillance
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('enseignants');
    }
};
