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
        Schema::table('voeux_enseignement', function (Blueprint $table) {
            $table->date('semaine_cible')->nullable()->after('code_seance');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
   public function down(): void
    {
        Schema::table('voeux_enseignement', function (Blueprint $table) {
            $table->dropColumn('semaine_cible');
        });
    }
};
