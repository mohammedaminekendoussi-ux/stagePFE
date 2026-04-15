<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_cours', function (Blueprint $table) {
            $table->id();
            $table->string('titre');
            $table->integer('taille');
            $table->timestamp('date_upload');
            $table->foreignId('module_id')->constrained()->onDelete('cascade');
            $table->foreignId('formateur_id')->constrained('users')->onDelete('restrict');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_cours');
    }
};