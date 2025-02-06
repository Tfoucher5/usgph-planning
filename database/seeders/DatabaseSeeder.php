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
            'first_name' => 'Erwan',
            'last_name' => 'Baron',
            'email' => 'erwan.baron@usgph.com',
            'password' => Hash::make('password'),
            'remember_token' => Str::random(10),
        ]);
        Bouncer::assign('salarie')->to($user);

        $user2 = User::firstOrCreate([
            'first_name' => 'Jimmy',
            'last_name' => 'Dorbais',
            'email' => 'jimmy.dorbais@usgph.com',
            'password' => Hash::make('password'),
            'remember_token' => Str::random(10),
        ]);
        Bouncer::assign('salarie')->to($user2);

        $user3 = User::firstOrCreate([
            'first_name' => 'Nathan',
            'last_name' => 'Richard',
            'email' => 'nathan.richard@usgph.com',
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
