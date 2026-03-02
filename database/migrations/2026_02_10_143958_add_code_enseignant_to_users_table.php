<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('code_enseignant', 100)->nullable()->after('id'); 
            // ->after('id') : place la colonne après l'id, tu peux changer
            // ->nullable() : permet de ne pas obliger la valeur
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('code_enseignant');
        });
    }
};