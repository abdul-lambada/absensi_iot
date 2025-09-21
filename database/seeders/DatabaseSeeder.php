<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // Opsional: contoh 1 user admin default dari factory
        // User::factory()->create([
        //     'nama_lengkap' => 'Administrator',
        //     'username' => 'admin',
        //     'password_hash' => Hash::make('password'),
        //     'role' => 'admin',
        //     'no_telepon' => '081234567890',
        // ]);

        // Panggil seeder khusus role
        $this->call([
            RoleUserSeeder::class,
        ]);
    }
}
