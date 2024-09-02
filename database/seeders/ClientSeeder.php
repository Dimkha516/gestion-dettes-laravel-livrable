<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Client;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Générer 3 clients sans compte utilisateur
        Client::factory(3)->create();

        // Générer 3 clients avec compte utilisateur
        // Client::factory(3)->withUser()->create();

    }
}
