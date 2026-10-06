<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with the starter content only.
     *
     * The demo seeders (fictional testimonials, a sample schedule block and
     * sample booking requests) are disabled so the site starts clean. Run
     * them individually with `php artisan db:seed --class=...` if needed.
     */
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            SiteSettingSeeder::class,
            MethodologyStepSeeder::class,
            LessonExampleSeeder::class,
            AvailabilityWindowSeeder::class,
        ]);

        // $this->call([
        //     TestimonialSeeder::class,
        //     ScheduleBlockSeeder::class,
        //     BookingRequestSeeder::class,
        // ]);
    }
}
