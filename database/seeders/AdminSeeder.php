<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => env('ADMIN_EMAIL')],
            [
                'name'              => env('ADMIN_NAME', 'Admin'),
                'password'          => bcrypt(env('ADMIN_PASSWORD')),
                'role'              => 'admin',
                'email_verified_at' => now(),
            ]
        );
    }
}
