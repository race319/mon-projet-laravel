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
    Schema::table('seances', function (Blueprint $table) {
        $table->unsignedBigInteger('code_suveillance')->nullable()->after('etat');

        $table->foreign('code_suveillance')
              ->references('id')
              ->on('users')
              ->onDelete('set null');
    });
}

public function down()
{
    Schema::table('seances', function (Blueprint $table) {
        $table->dropForeign(['code_suveillance']);
        $table->dropColumn('code_suveillance');
    });
}

};
