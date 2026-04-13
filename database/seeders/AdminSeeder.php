<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name'              => env('ADMIN_NAME', 'Administrator'),
            'email'             => env('ADMIN_EMAIL'),
            'password'          => Hash::make(env('ADMIN_PASSWORD')),
            'role'              => 'admin', // Ton champ ajouté précédemment
            'email_verified_at' => now(),
        ]);
    }
}
