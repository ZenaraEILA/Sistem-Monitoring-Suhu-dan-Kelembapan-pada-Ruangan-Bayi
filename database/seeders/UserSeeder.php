<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        User::updateOrCreate(
            ['email' => 'admin@monitoring.local'],
            [
                'name' => 'Admin',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
            ]
        );

        // Create petugas user
        User::updateOrCreate(
            ['email' => 'petugas@monitoring.local'],
            [
                'name' => 'Petugas',
                'password' => Hash::make('petugas123'),
                'role' => 'petugas',
            ]
        );
    }
}
