<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class RoleUserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'email' => 'admin@example.com',
                'nama_lengkap' => 'Administrator',
                'password_hash' => Hash::make('password'),
                'role' => 'admin',
                'no_telepon' => '081234567890',
            ]
        );

        // Guru
        User::updateOrCreate(
            ['username' => 'guru'],
            [
                'email' => 'guru@example.com',
                'nama_lengkap' => 'Guru',
                'password_hash' => Hash::make('password'),
                'role' => 'guru',
                'no_telepon' => '081234567891',
            ]
        );

        // Kepala Sekolah
        User::updateOrCreate(
            ['username' => 'kepala'],
            [
                'email' => 'kepala@example.com',
                'nama_lengkap' => 'Kepala Sekolah',
                'password_hash' => Hash::make('password'),
                'role' => 'kepala_sekolah',
                'no_telepon' => '081234567892',
            ]
        );
    }
}