<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // database/migrations/xxxx_add_fichier_to_support_cours_table.php
public function up()
{
    Schema::table('support_cours', function (Blueprint $table) {
        $table->string('fichier')->after('titre'); // chemin du fichier stocké
    });
}

public function down()
{
    Schema::table('support_cours', function (Blueprint $table) {
        $table->dropColumn('fichier');
    });
}
};
