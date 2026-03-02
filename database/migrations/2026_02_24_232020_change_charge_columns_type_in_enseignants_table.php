<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enseignants', function (Blueprint $table) {
            $table->float('charge_enseignement')->default(0)->change(); // ✅ float = nombre avec virgule
            $table->float('charge_surveillance')->default(0)->change();  // ✅ float = nombre avec virgule
        });
    }

    public function down(): void
    {
        Schema::table('enseignants', function (Blueprint $table) {
            $table->integer('charge_enseignement')->default(0)->change();
            $table->integer('charge_surveillance')->default(0)->change();
        });
    }
};