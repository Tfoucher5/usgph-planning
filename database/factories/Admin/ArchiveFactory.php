<?php

namespace Database\Factories\Admin;

use App\Models\Admin\Archive;
use App\Models\Planning\Planning;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Admin\Archive>
 */
class ArchiveFactory extends Factory
{
    /**
     * Summary of model
     *
     * @var Archive
     */
    protected $model = Archive::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $usedPlanningIds = Archive::pluck('planning_id')->toArray();

        $planningValidated = Planning::with('tache', 'categorie')->where('is_validated', 1)
            ->whereNotIn('id', $usedPlanningIds)
            ->lockForUpdate()
            ->inRandomOrder()
            ->first();

        if (!$planningValidated) {
            throw new Exception('No unused validated Planning records available.');
        }

        return [
            'planning_id' => $planningValidated->id,
            'user_id' => $planningValidated->user_id,
            'lieu' => $planningValidated->lieu->nom,
            'nom' => $planningValidated->nom,
            'categorie_nom' => $planningValidated->tache->categorie->nom,
            'categorie_couleur' => $planningValidated->tache->categorie->couleur,
            'plannifier_le' => $planningValidated->plannifier_le,
            'heure_debut' => Carbon::parse($planningValidated->heure_debut)->format('H:i'),
            'heure_fin' => Carbon::parse($planningValidated->heure_fin)->format('H:i'),
            'duree_tache' => Carbon::parse($planningValidated->heure_debut)
                ->diffInMinutes(Carbon::parse($planningValidated->heure_fin)),
            'created_at' => now(),
            'user_id_creation' => $planningValidated->user_id,
        ];
    }
}
