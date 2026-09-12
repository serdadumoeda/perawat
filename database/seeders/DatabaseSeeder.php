<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $adminEmail = env('ADMIN_EMAIL');
        $adminPassword = env('ADMIN_PASSWORD');

        if ($adminEmail && $adminPassword) {
            User::firstOrCreate(
                ['email' => $adminEmail],
                [
                    'name' => 'Admin',
                    'role' => User::ROLE_ADMIN,
                    'password' => Hash::make($adminPassword),
                ]
            );
        }

        if (filter_var(env('DEMO_SEED'), FILTER_VALIDATE_BOOL)) {
            $this->call([
                DemoSeeder::class,
                PerawatNikConstraintSeeder::class,
            ]);
        }

        if (filter_var(env('DEMO_SCENARIO_SEED'), FILTER_VALIDATE_BOOL)) {
            $this->call([
                DemoScenarioSeeder::class,
            ]);
        }
    }
}
