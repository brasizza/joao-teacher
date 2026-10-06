<?php

namespace Database\Seeders;

use App\Models\BookingRequest;
use Illuminate\Database\Seeder;

class BookingRequestSeeder extends Seeder
{
    /**
     * Seed sample requests so the admin workflow can be explored (local only).
     */
    public function run(): void
    {
        if (BookingRequest::query()->exists()) {
            return;
        }

        $nextTuesday = today()->next('Tuesday')->toDateString();
        $nextWednesday = today()->next('Wednesday')->toDateString();

        BookingRequest::factory()->pending()->startingAt("{$nextTuesday} 09:00")->create(['name' => 'Carla Mendes']);
        BookingRequest::factory()->confirmed()->startingAt("{$nextWednesday} 15:00")->create(['name' => 'Pedro Henrique']);
    }
}
