<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Groupe;

class GroupeSeeder extends Seeder
{
    public function run(): void
    {
        Groupe::create(['nom' => 'G1', 'annee' => 2024, 'filiere_id' => 1]);
        Groupe::create(['nom' => 'G2', 'annee' => 2024, 'filiere_id' => 1]);
        Groupe::create(['nom' => 'G3', 'annee' => 2024, 'filiere_id' => 2]);
    }
}