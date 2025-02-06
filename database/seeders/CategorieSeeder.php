<?php

namespace Database\Seeders;

use App\Models\User;
use DB;
use Illuminate\Database\Seeder;

class CategorieSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['nom' => 'Sportif', 'couleur' => 'vert'],
            ['nom' => 'Administratif', 'couleur' => 'bleu'],
            ['nom' => 'Formation', 'couleur' => 'rouge'],
        ];

        foreach ($categories as $categorie) {
            DB::table('categories')->insert([
                'nom' => $categorie['nom'],
                'couleur' => $categorie['couleur'],
                'created_at' => now(),
                'user_id_creation' => User::find(1)->id,
            ]);
        }
    }
}
