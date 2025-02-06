<?php

namespace Database\Factories\Conge;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Conge\Motif>
 */
class MotifFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
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

        return [
            'nom' => $this->faker->randomElement($motifs),
            'created_at' => now(),
            'user_id_creation' => User::factory()->create()->id,

        ];
    }
}
