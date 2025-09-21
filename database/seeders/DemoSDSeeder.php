<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

use App\Models\User;
use App\Models\Kelas;
use App\Models\Siswa;

class DemoSDSeeder extends Seeder
{
    public function run(): void
    {
        // Pastikan user guru tersedia
        $guru = User::firstOrCreate(
            ['username' => 'guru'],
            [
                'nama_lengkap' => 'Guru SDN 01 Menteng',
                'password_hash' => Hash::make('password'),
                'role' => 'guru',
                'no_telepon' => '081234567891',
            ]
        );

        // Buat 1 kelas contoh di SDN 01 Menteng
        $kelas = Kelas::firstOrCreate(
            [
                'nama_kelas' => '4A',
                'tahun_ajaran' => '2024/2025',
                'guru' => $guru->id,
            ],
            [
                'nama_kelas' => '4A',
                'tahun_ajaran' => '2024/2025',
                'guru' => $guru->id,
            ]
        );

        // Dua data siswa dummy di kelas tersebut
        Siswa::firstOrCreate(
            [
                'nama_siswa' => 'Ahmad Fajar',
                'kelas_id' => $kelas->id,
            ],
            [
                'nama_siswa' => 'Ahmad Fajar',
                'jenis_kelamin' => 'L',
                'template_sidik_jari' => null,
                'nama_orang_tua' => 'Budi Santoso',
                'no_telepon_orang_tua' => '081234111111',
                'kelas_id' => $kelas->id,
            ]
        );

        Siswa::firstOrCreate(
            [
                'nama_siswa' => 'Siti Aisyah',
                'kelas_id' => $kelas->id,
            ],
            [
                'nama_siswa' => 'Siti Aisyah',
                'jenis_kelamin' => 'P',
                'template_sidik_jari' => null,
                'nama_orang_tua' => 'Sulastri',
                'no_telepon_orang_tua' => '081234222222',
                'kelas_id' => $kelas->id,
            ]
        );
    }
}