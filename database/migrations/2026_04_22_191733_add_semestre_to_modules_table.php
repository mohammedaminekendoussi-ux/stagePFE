<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::table('modules', function (Blueprint $table) {
        $table->integer('semestre')->nullable()->after('volume_horaire');
    });
}

public function down()
{
    Schema::table('modules', function (Blueprint $table) {
        $table->dropColumn('semestre');
    });
}
};
