<?php

namespace Database\Seeders;

use App\Enums\Weekday;
use App\Models\AvailabilityWindow;
use Illuminate\Database\Seeder;

class AvailabilityWindowSeeder extends Seeder
{
    /**
     * Seed a Monday-to-Friday morning and afternoon availability.
     */
    public function run(): void
    {
        if (AvailabilityWindow::query()->exists()) {
            return;
        }

        $weekdays = [Weekday::Monday, Weekday::Tuesday, Weekday::Wednesday, Weekday::Thursday, Weekday::Friday];

        foreach ($weekdays as $weekday) {
            AvailabilityWindow::query()->create(['weekday' => $weekday, 'start_time' => '08:00', 'end_time' => '12:00']);
            AvailabilityWindow::query()->create(['weekday' => $weekday, 'start_time' => '14:00', 'end_time' => '18:00']);
        }
    }
}
