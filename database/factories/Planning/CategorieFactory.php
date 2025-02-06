<?php

namespace Database\Factories\Planning;

use App\Models\User;
use Auth;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Planning\Categorie>
 */
class CategorieFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {


        return [
            'nom' => collect($this->getCategoriename())->random(),
            'couleur' => $this->faker->colorName(),
            'created_at' => now(),
            'user_id_creation' => User::factory()->create()->id,
        ];
    }

    public function getCategoriename()
    {
        return [
            'Sportif',
            'administratif',
            'formation'
        ];
    }
}
