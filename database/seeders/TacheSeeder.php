<?php

namespace Database\Seeders;

use App\Models\Planning\Tache;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TacheSeeder extends Seeder
{
    public function run()
    {
        $users = [2, 3, 4];

        $taches = [
            [
                'nom' => 'Entraînement Complet',
                'horaires' => [
                    '14:00-18:00',
                    '16:00-20:00',
                    '17:00-21:00',
                ],
                'categorie' => '1',
                'priorite' => 'haute',
            ],
            [
                'nom' => 'Stage journée',
                'horaires' => [
                    '09:00-12:00,14:00-17:00',
                ],
                'categorie' => '3',
                'priorite' => 'haute',
            ],
            [
                'nom' => 'Préparation et entraînement',
                'horaires' => [
                    '13:00-17:00',
                    '15:00-19:00',
                ],
                'categorie' => '1',
                'priorite' => 'haute',
            ],
            [
                'nom' => 'Entraînement Seniors',
                'horaires' => [
                    '18:00-20:30',
                    '19:00-21:30',
                ],
                'categorie' => '1',
                'priorite' => 'moyenne',
            ],
            [
                'nom' => 'Entraînement Jeunes',
                'horaires' => [
                    '14:00-16:30',
                    '15:30-18:00',
                ],
                'categorie' => '1',
                'priorite' => 'moyenne',
            ],
            [
                'nom' => 'Administrative complète',
                'horaires' => [
                    '09:00-12:00',
                    '14:00-17:00',
                ],
                'categorie' => '2',
                'priorite' => 'moyenne',
            ],
            [
                'nom' => 'Réunion staff',
                'horaires' => [
                    '10:00-12:00',
                    '14:00-16:00',
                ],
                'categorie' => '2',
                'priorite' => 'basse',
            ],
            [
                'nom' => 'Séance vidéo',
                'horaires' => [
                    '10:00-12:00',
                    '14:00-16:00',
                ],
                'categorie' => '1',
                'priorite' => 'basse',
            ],
            [
                'nom' => 'Préparation physique',
                'horaires' => [
                    '09:00-11:00',
                    '16:00-18:00',
                ],
                'categorie' => '1',
                'priorite' => 'basse',
            ],
        ];

        foreach ($users as $user_id) {
            for ($jour = 1; $jour <= 5; $jour++) {
                $this->createTasksOfPriority($taches, 'haute', 1, 1, $user_id, $jour);
                $this->createTasksOfPriority($taches, 'moyenne', 1, 2, $user_id, $jour);
                $this->createTasksOfPriority($taches, 'basse', 1, 2, $user_id, $jour);
            }

            if (rand(0, 1)) {
                $this->createTasksOfPriority($taches, 'haute', 1, 1, $user_id, 6);
            } else {
                $this->createTasksOfPriority($taches, 'moyenne', 2, 2, $user_id, 6);
            }
        }
    }

    private function createTasksOfPriority($taches, $priority, $min, $max, $user_id, $jour)
    {
        $priorityTasks = array_values(array_filter($taches, fn ($t) => $t['priorite'] === $priority));
        $numTasks = min($max, count($priorityTasks));
        $numTasks = max($min, min($numTasks, $max));

        $indices = range(0, count($priorityTasks) - 1);
        shuffle($indices);
        $selectedIndices = array_slice($indices, 0, $numTasks);

        foreach ($selectedIndices as $index) {
            $tache = $priorityTasks[$index];
            $horaire = $tache['horaires'][array_rand($tache['horaires'])];

            if (strpos($horaire, ',') !== false) {
                foreach (explode(',', $horaire) as $subHoraire) {
                    $this->createSingleTask($tache, $subHoraire, $user_id, $jour);
                }
            } else {
                $this->createSingleTask($tache, $horaire, $user_id, $jour);
            }
        }
    }

    private function createSingleTask($tache, $horaire, $user_id, $jour)
    {
        [$debut, $fin] = explode('-', $horaire);

        // Convertir en Carbon pour manipulation facile
        $debut = Carbon::createFromFormat('H:i', $debut);
        $fin = Carbon::createFromFormat('H:i', $fin);

        // Arrondir au quart d'heure le plus proche
        $debut = $this->roundToNearestQuarter($debut);
        $fin = $this->roundToNearestQuarter($fin);

        Tache::create([
            'nom' => $tache['nom'],
            'lieu_id' => rand(1, 6),
            'user_id' => $user_id,
            'categorie_id' => $tache['categorie'],
            'jour' => $jour,
            'heure_debut' => $debut->format('H:i:00'),
            'heure_fin' => $fin->format('H:i:00'),
            'user_id_creation' => 5,
        ]);
    }

    private function roundToNearestQuarter(Carbon $time)
    {
        $minutes = $time->minute;
        $roundedMinutes = round($minutes / 15) * 15;

        return $time->setMinute($roundedMinutes)->setSecond(0);
    }
}
