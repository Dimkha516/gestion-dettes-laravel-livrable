<?php

namespace Database\Seeders;

use DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['libelle' => 'Bronze'],
            ['libelle' => 'Silver'],
            ['libelle' => 'Gold'],
        ];

        DB::table('categories')->insert($categories);
    }
}
