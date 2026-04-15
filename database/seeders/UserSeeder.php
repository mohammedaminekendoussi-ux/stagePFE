<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Administrateur
        User::create([
            'nom' => 'Admin',
            'prenom' => 'Super',
            'email' => 'admin@stage.com',
            'mot_de_passe' => Hash::make('password'),
            'role' => 'administrateur',
            'actif' => true,
            'groupe_id' => null,
        ]);

        // Formateur
        User::create([
            'nom' => 'Dupont',
            'prenom' => 'Jean',
            'email' => 'formateur@stage.com',
            'mot_de_passe' => Hash::make('password'),
            'role' => 'formateur',
            'actif' => true,
            'groupe_id' => null,
        ]);

        // Etudiants
        User::create([
            'nom' => 'Martin',
            'prenom' => 'Alice',
            'email' => 'etudiant1@stage.com',
            'mot_de_passe' => Hash::make('password'),
            'role' => 'etudiant',
            'actif' => true,
            'groupe_id' => 1,
        ]);

        User::create([
            'nom' => 'Bernard',
            'prenom' => 'Lucas',
            'email' => 'etudiant2@stage.com',
            'mot_de_passe' => Hash::make('password'),
            'role' => 'etudiant',
            'actif' => true,
            'groupe_id' => 1,
        ]);
    }
}