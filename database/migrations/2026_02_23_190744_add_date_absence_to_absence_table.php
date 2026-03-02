<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('absence', function (Blueprint $table) {
            $table->date('date_absence')->nullable()->after('seance');
        });
    }

    public function down(): void
    {
        Schema::table('absence', function (Blueprint $table) {
            $table->dropColumn('date_absence');
        });
    }
};