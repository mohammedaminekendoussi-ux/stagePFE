<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Filiere;

class FiliereSeeder extends Seeder
{
    public function run(): void
    {
        Filiere::create(['nom' => 'Développement Web', 'description' => 'Formation en développement web']);
        Filiere::create(['nom' => 'Réseaux Informatiques', 'description' => 'Formation en réseaux']);
        Filiere::create(['nom' => 'Intelligence Artificielle', 'description' => 'Formation en IA']);
    }
}