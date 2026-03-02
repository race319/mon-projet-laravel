<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enseignants', function (Blueprint $table) {
            $table->string('code_enseignant', 100)->primary(); 
            $table->integer('charge_enseignement')->default(0);
            $table->integer('charge_surveillance')->default(0);
            $table->timestamps(); 
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enseignants');
    }
};