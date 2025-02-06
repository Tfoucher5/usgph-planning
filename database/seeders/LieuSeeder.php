<?php

namespace Database\Seeders;

use App\Models\Admin\Lieu;
use Illuminate\Database\Seeder;

class LieuSeeder extends Seeder
{
    public function run()
    {
        $lieux = [
            ['nom' => 'Salle de sport A'],
            ['nom' => 'Salle de sport B'],
            ['nom' => 'Stade extérieur'],
            ['nom' => 'Complexe sportif principal'],
            ['nom' => 'Maison'],
            ['nom' => 'Bureaux'],
        ];

        foreach ($lieux as $lieu) {
            Lieu::create([
                'nom' => $lieu['nom'],
                'user_id_creation' => 5,
            ]);
        }
    }
}
