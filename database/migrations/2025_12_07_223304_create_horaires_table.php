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
        Schema::create('horaires', function (Blueprint $table) {
            $table->id();
            $table->string('jour');      // Lundi, Mardi...
            $table->string('creneau');   // 08-10, 10-12...
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
   public function down(): void
    {
        Schema::dropIfExists('horaires');
    }
};
