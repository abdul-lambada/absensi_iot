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
        User::firstOrCreate(
            ['username' => 'admin'],
            [
                'nama_lengkap' => 'Administrator',
                'password_hash' => Hash::make('password'),
                'role' => 'admin',
                'no_telepon' => '081234567890',
            ]
        );

        // Guru
        User::firstOrCreate(
            ['username' => 'guru'],
            [
                'nama_lengkap' => 'Guru',
                'password_hash' => Hash::make('password'),
                'role' => 'guru',
                'no_telepon' => '081234567891',
            ]
        );

        // Kepala Sekolah
        User::firstOrCreate(
            ['username' => 'kepala'],
            [
                'nama_lengkap' => 'Kepala Sekolah',
                'password_hash' => Hash::make('password'),
                'role' => 'kepala_sekolah',
                'no_telepon' => '081234567892',
            ]
        );
    }
}