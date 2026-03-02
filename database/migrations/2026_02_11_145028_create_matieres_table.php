<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matieres', function (Blueprint $table) {
            $table->string('code_matiere')->primary(); // id devient string
            $table->string('nom_matiere');
            $table->string('created_at')->nullable();
            $table->string('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matieres');
    }
};