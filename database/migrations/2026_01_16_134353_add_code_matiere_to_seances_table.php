<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('seances', function (Blueprint $table) {
            $table->unsignedBigInteger('code_matiere')->nullable()->after('code_groupe');

            // Clé étrangère (recommandé)
            $table->foreign('code_matiere')
                  ->references('code_matiere')
                  ->on('matieres')
                  ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('seances', function (Blueprint $table) {
            $table->dropForeign(['code_matiere']);
            $table->dropColumn('code_matiere');
        });
    }
};
