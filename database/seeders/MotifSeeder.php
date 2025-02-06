<?php

namespace Database\Seeders;

use App\Models\Conge\Motif;
use Auth;
use Illuminate\Database\Seeder;

class MotifSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userId = Auth::check() ? Auth::id() : 1;
        $motifs = [
            ['nom' => 'Congé Annuel'],
            ['nom' => 'Repos Compensateur'],
            ['nom' => 'Congé Exceptionnel'],
            ['nom' => 'Congé Maladie'],
        ];
        foreach ($motifs as $motif) {
            Motif::create(array_merge($motif, [
                'user_id_creation' => $userId,
            ]));
        }
    }
}
