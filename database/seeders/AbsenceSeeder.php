<?php

namespace Database\Seeders;

use DB;
use Illuminate\Database\Seeder;

class AbsenceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $motifs = [
            'Maladie',
            'Congé payé',
            'Congé sans solde',
            'Maternité',
            'Paternité',
            'Congé de récupération',
            'Accident de travail',
            'Raisons personnelles',
            'Vacances',
            'Formation',
            'Jury',
        ];

        // Insérer les motifs dans la table absence_motifs (ou le nom de la table que tu utilises)
        foreach ($motifs as $motif) {
            DB::table('motifs')->insert([
                'nom' => $motif,
                'created_at' => now(),
            ]);
        }
    }
}
