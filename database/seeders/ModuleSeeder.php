<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Module;

class ModuleSeeder extends Seeder
{
    public function run(): void
    {
        Module::create([
            'nom' => 'Laravel',
            'coefficient' => 2.5,
            'volume_horaire' => 40,
            'filiere_id' => 1,
            'formateur_id' => 2,
        ]);

        Module::create([
            'nom' => 'JavaScript',
            'coefficient' => 2.0,
            'volume_horaire' => 30,
            'filiere_id' => 1,
            'formateur_id' => 2,
        ]);
    }
}