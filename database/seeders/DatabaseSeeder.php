<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            FiliereSeeder::class,
            GroupeSeeder::class,
            UserSeeder::class,
            ModuleSeeder::class,
        ]);
    }
}