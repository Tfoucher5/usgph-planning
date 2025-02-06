<?php

namespace Database\Factories\Conge;

use App\Enums\ValidationStatus;
use App\Models\Conge\Motif;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Conge\Absence>
 */
class AbsenceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'motif_id' => Motif::factory(),
            'date_debut' => function () {
                $startDate = Carbon::now()->addDays(10);

                return $startDate->format('Y-m-d');
            },
            'date_fin' => function (array $attributes) {
                $startDate = Carbon::createFromFormat('Y-m-d', $attributes['date_debut']);
                $endDate = $startDate->addDays(rand(0, 14));

                return $endDate->format('Y-m-d');
            },
            'created_at' => now(),
            'user_id_creation' => User::factory(),
            'status' => ValidationStatus::WAITING,
            'nb_of_work_days' => function (array $attributes) {
                $startDate = Carbon::createFromFormat('Y-m-d', $attributes['date_debut']);
                $endDate = Carbon::createFromFormat('Y-m-d', $attributes['date_fin']);

                return $startDate->diffInDays($endDate);
            },
        ];
    }
}
