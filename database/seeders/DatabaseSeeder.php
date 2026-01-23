<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat Akun ADMIN
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@iot.com',
            'password' => Hash::make('admin123'), // Password Admin
            'role' => 'admin', // Role Admin
        ]);

        // 2. Buat Akun USER BIASA
        User::create([
            'name' => 'Tamu',
            'email' => 'user@iot.com',
            'password' => Hash::make('user123'), // Password User
            'role' => 'user', // Role User
        ]);
    }
}