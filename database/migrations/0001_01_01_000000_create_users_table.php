<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('prenom');
            $table->string('email')->unique();
            $table->string('mot_de_passe');
            $table->enum('role', ['administrateur', 'formateur', 'etudiant']);
            $table->boolean('actif')->default(true);
            $table->timestamp('bloque_time')->nullable();
            $table->integer('tentative_echec')->default(0);
            $table->unsignedBigInteger('groupe_id')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};