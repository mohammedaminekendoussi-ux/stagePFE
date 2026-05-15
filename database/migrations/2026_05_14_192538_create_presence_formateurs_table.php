<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('presence_formateurs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seance_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->timestamps();
            $table->unique(['seance_id', 'date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('presence_formateurs');
    }
};