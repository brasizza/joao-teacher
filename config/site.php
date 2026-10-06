<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Administrator Account
    |--------------------------------------------------------------------------
    |
    | Credentials used by the AdminUserSeeder to create the teacher's account
    | for the Filament panel. The seeder is skipped when the e-mail or the
    | password is empty, so production secrets never live in the code.
    |
    */

    'admin' => [
        'name' => env('ADMIN_NAME', 'Teacher João'),
        'email' => env('ADMIN_EMAIL'),
        'password' => env('ADMIN_PASSWORD'),
    ],

];
