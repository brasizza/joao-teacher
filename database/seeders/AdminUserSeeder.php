<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Create the teacher's admin account from the ADMIN_* environment values.
     */
    public function run(): void
    {
        $email = config('site.admin.email');
        $password = config('site.admin.password');

        if (blank($email) || blank($password)) {
            $this->command?->warn('ADMIN_EMAIL/ADMIN_PASSWORD vazios: usuário admin não foi criado.');

            return;
        }

        User::query()->firstOrCreate(
            ['email' => $email],
            ['name' => config('site.admin.name'), 'password' => $password],
        );
    }
}
