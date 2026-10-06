<?php

namespace Database\Seeders;

use App\Models\ScheduleBlock;
use Illuminate\Database\Seeder;

class ScheduleBlockSeeder extends Seeder
{
    /**
     * Seed a sample blocked afternoon (local only).
     */
    public function run(): void
    {
        if (ScheduleBlock::query()->exists()) {
            return;
        }

        $nextFriday = today()->next('Friday');

        ScheduleBlock::query()->create([
            'starts_at' => $nextFriday->copy()->setTime(14, 0),
            'ends_at' => $nextFriday->copy()->setTime(18, 0),
            'reason' => 'Compromisso pessoal (exemplo)',
        ]);
    }
}
