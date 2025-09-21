<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\{User, Kelas, Siswa, AbsensiHarian};
use Carbon\Carbon;

class RekapAbsensiPageTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function makeKelasWithSiswa(): array
    {
        $guru = User::factory()->create(['role' => 'guru']);
        $kelas = Kelas::create([
            'nama_kelas' => '2B',
            'tahun_ajaran' => '2024/2025',
            'guru' => $guru->id,
        ]);
        $siswa = Siswa::create([
            'nama_siswa' => 'Budi Santoso',
            'jenis_kelamin' => 'L',
            'kelas_id' => $kelas->id,
            'finger_id' => 21,
        ]);
        return [$kelas, $siswa];
    }

    public function test_rekap_page_shows_records_for_admin(): void
    {
        $admin = $this->makeAdmin();
        [$kelas, $siswa] = $this->makeKelasWithSiswa();

        AbsensiHarian::create([
            'tanggal' => Carbon::create(2025, 9, 21)->toDateString(),
            'waktu_masuk' => '07:05:00',
            'waktu_pulang' => '16:00:00',
            'status_kehadiran' => 'terlambat',
            'siswa_id' => $siswa->id,
        ]);

        $this->actingAs($admin);

        $response = $this->get('/rekap-absensi?start_date=2025-09-21&end_date=2025-09-21');
        $response->assertStatus(200);
        $response->assertSee('Rekap Absensi');
        $response->assertSee('Budi Santoso');
    }

    public function test_rekap_export_returns_csv(): void
    {
        $admin = $this->makeAdmin();
        [$kelas, $siswa] = $this->makeKelasWithSiswa();

        AbsensiHarian::create([
            'tanggal' => Carbon::create(2025, 9, 21)->toDateString(),
            'waktu_masuk' => '07:05:00',
            'status_kehadiran' => 'terlambat',
            'siswa_id' => $siswa->id,
        ]);

        $this->actingAs($admin);

        $response = $this->get('/rekap-absensi/export?start_date=2025-09-21&end_date=2025-09-21');
        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));
        // Konten CSV bersifat streamed; kita cukup memastikan header benar.
    }
}