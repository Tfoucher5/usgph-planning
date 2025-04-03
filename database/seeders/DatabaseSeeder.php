<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Silber\Bouncer\BouncerFacade as Bouncer;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::firstOrCreate([
            'first_name' => 'Theo',
            'last_name' => 'Foucher',
            'email' => 'theo.foucher@usgph.com',
            'password' => Hash::make('password'),
            'remember_token' => Str::random(10),
        ]);
        Bouncer::assign('salarie')->to($user);

        $user2 = User::firstOrCreate([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@usgph.com',
            'password' => Hash::make('password'),
            'remember_token' => Str::random(10),
        ]);
        Bouncer::assign('salarie')->to($user2);

        $user3 = User::firstOrCreate([
            'first_name' => ' Mike',
            'last_name' => 'Grand',
            'email' => 'mike.grand@usgph.com',
            'password' => Hash::make('password'),
            'remember_token' => Str::random(10),
        ]);
        Bouncer::assign('salarie')->to($user3);

        $admin = User::firstOrCreate([
            'first_name' => 'test',
            'last_name' => 'admin',
            'email' => 'testadmin@usgph.com',
            'password' => Hash::make('password'),
            'remember_token' => Str::random(10),
        ]);
        Bouncer::assign('admin')->to($admin);

        $this->call([
            LieuSeeder::class,
            CategorieSeeder::class,
            TacheSeeder::class,
            PlanningSeeder::class,
            ArchiveSeeder::class,
            MotifSeeder::class,
        ]);
    }
}
