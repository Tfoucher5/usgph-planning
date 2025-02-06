<?php

namespace Database\Seeders;

use App\Models\Admin\Archive;
use App\Models\Planning\Planning;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ArchiveSeeder extends Seeder
{
    public function run()
    {
        $validatedPlannings = Planning::with('tache.categorie','lieu')->where('is_validated', 1)
            ->whereNotIn('id', Archive::pluck('planning_id'))
            ->get();

            foreach ($validatedPlannings as $planning) {
                if (!$planning->tache || !$planning->tache->categorie || !$planning->lieu) {
                    \Log::warning("Planning {$planning->id} has missing relationships and was skipped during archiving");
                    continue;
                }


            try {
                Archive::create([
                    'planning_id' => $planning->id,
                    'user_id' => $planning->user_id,
                    'lieu' => $planning->lieu->nom,
                    'nom' => $planning->nom,
                    'categorie_nom' => $planning->tache->categorie->nom,
                    'categorie_couleur' => $planning->tache->categorie->couleur,
                    'plannifier_le' => $planning->plannifier_le,
                    'heure_debut' => Carbon::parse($planning->heure_debut)->format('H:i'),
                    'heure_fin' => Carbon::parse($planning->heure_fin)->format('H:i'),
                    'duree_tache' => Carbon::parse($planning->heure_debut)
                        ->diffInMinutes(Carbon::parse($planning->heure_fin)),
                    'user_id_creation' => $planning->user_id_creation,
                ]);
            } catch (\Exception $e) {
                \Log::error("Failed to archive planning {$planning->id}: " . $e->getMessage());
                continue;
            }
        }
    }
}
