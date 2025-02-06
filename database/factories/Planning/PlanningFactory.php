<?php

namespace Database\Factories\Planning;

use App\Models\Planning\Tache;
use App\Models\User;
use Arr;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Planning\Planning>
 */
class PlanningFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tache = Tache::factory()->create();

        return [
            'nom' => $tache->nom ?? $this->faker->word,
            'tache_id' => $tache->id ?? null,
            'user_id' => User::factory()->create()->id,
            'lieu_id' => $tache->lieu_id,
            'plannifier_le' => $this->faker->dateTimeBetween('2025-01-01', '2026-12-31')->format('Y-m-d'),            'heure_debut' => function () {
                $startTime = Carbon::createFromTime(rand(8, 19), Arr::random([0, 15, 30, 45]));

                return $startTime->format('H:i');
            },
            'heure_fin' => function (array $attributes) {
                $startTime = Carbon::createFromFormat('H:i', $attributes['heure_debut']);
                $endTime = $startTime->addHours(rand(1, 4))->addMinutes(Arr::random([0, 15, 30, 45]));

                return $endTime->format('H:i');
            },
            'is_validated' => $this->faker->boolean(0),
            'user_id_creation' => User::factory()->create()->id,
        ];
    }
}
