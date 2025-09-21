<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Perangkat;

class DeviceDemoSeeder extends Seeder
{
    public function run(): void
    {
        Perangkat::firstOrCreate(
            ['device_uid' => 'ESP32-ABCD1234'],
            [
                'nama_perangkat' => 'ESP32 Pintu Utama',
                'lokasi_perangkat' => 'Gerbang Depan',
                'status_perangkat' => 'aktif',
                'api_key' => 'RAHASIA_PERANGKAT',
            ]
        );
    }
}