<?php

namespace Database\Factories\Admin;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Admin\Lieu>
 */
class LieuFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nom' => collect($this->getALieu())->random()['nom'],
            'user_id_creation' => User::factory()->create()->id,
        ];
    }

    public function getALieu()
    {
        return [
            ['nom' => 'Salle de sport A'],
            ['nom' => 'Salle de sport B'],
            ['nom' => 'Stade extérieur'],
            ['nom' => 'Complexe sportif principal'],
            ['nom' => 'Maison'],
            ['nom' => 'Bureaux'],
        ];
    }
}
