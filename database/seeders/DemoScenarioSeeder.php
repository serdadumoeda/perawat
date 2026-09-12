<?php

namespace Database\Seeders;

use App\Services\DemoScenarioService;
use Illuminate\Database\Seeder;

class DemoScenarioSeeder extends Seeder
{
    public function run(): void
    {
        $run = app(DemoScenarioService::class)->runSinglePerawatTimeline();

        if ($this->command) {
            $this->command->info('Demo scenario run: '.$run->scenario_key);
            $this->command->info('Perawat NIK: '.$run->perawat_nik);
            $this->command->info('Total event: '.$run->events()->count());
        }
    }
}

