<?php

namespace Database\Factories\Planning;

use App\Models\Admin\Lieu;
use App\Models\Planning\Categorie;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Planning\Tache>
 */
class TacheFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nom' => $this->faker->word,
            'lieu_id' => Lieu::factory(),
            'user_id' => User::factory(),
            'categorie_id' => Categorie::factory(),
            'heure_debut' => $this->faker->dateTimeThisYear()->format('H:i'),
            'heure_fin' => $this->faker->dateTimeThisYear()->format('H:i'),
            'jour' => $this->faker->numberBetween(1, 7),
            'user_id_creation' => User::factory()->create()->id,
        ];
    }
}
