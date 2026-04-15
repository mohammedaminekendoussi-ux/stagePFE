<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seances', function (Blueprint $table) {
            $table->id();
            $table->date('jour');
            $table->time('h_debut');
            $table->time('h_fin');
            $table->string('salle');
            $table->enum('statut', ['prevue', 'annulee', 'reportee'])->default('prevue');
            $table->foreignId('module_id')->constrained()->onDelete('cascade');
            $table->foreignId('groupe_id')->constrained()->onDelete('cascade');
            $table->foreignId('formateur_id')->constrained('users')->onDelete('restrict');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seances');
    }
};