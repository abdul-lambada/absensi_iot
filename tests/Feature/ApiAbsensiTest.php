<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;
use App\Models\{Perangkat, Siswa, Kelas, User, AbsensiHarian};

class ApiAbsensiTest extends TestCase
{
    use RefreshDatabase;

    private function makeDevice(string $uid = null, string $apiKey = 'RAHASIA') : Perangkat
    {
        return Perangkat::create([
            'nama_perangkat' => 'Perangkat Uji',
            'lokasi_perangkat' => 'Lab',
            'status_perangkat' => 'aktif',
            'device_uid' => $uid ?: ('ESP32-'.uniqid()),
            'api_key' => $apiKey,
        ]);
    }

    private function makeSiswa(int $fingerId = 1) : Siswa
    {
        $guru = User::factory()->create(['role' => 'guru']);
        $kelas = Kelas::create([
            'nama_kelas' => '1A',
            'tahun_ajaran' => '2024/2025',
            'guru' => $guru->id,
        ]);
        return Siswa::create([
            'nama_siswa' => 'Siswa Finger '.$fingerId,
            'jenis_kelamin' => 'L',
            'kelas_id' => $kelas->id,
            'finger_id' => $fingerId,
        ]);
    }

    public function test_masuk_then_pulang_flow_success(): void
    {
        Carbon::setTestNow(Carbon::create(2025, 9, 21, 6, 30, 0));

        $device = $this->makeDevice('ESP32-TEST-1', 'SECRET1');
        $siswa = $this->makeSiswa(11);

        // Masuk
        $resMasuk = $this->postJson(route('api.absensi.store'), [
            'device_uid' => $device->device_uid,
            'api_key' => 'SECRET1',
            'finger_id' => 11,
            'event' => 'masuk',
        ]);

        $resMasuk->assertStatus(200)
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('event', 'masuk')
            ->assertJsonPath('siswa.id', $siswa->id);

        $this->assertDatabaseHas('absensi_harian', [
            'siswa_id' => $siswa->id,
            'tanggal' => Carbon::now()->toDateString(),
            'waktu_masuk' => '06:30:00',
            'perangkat_masuk_id' => $device->id,
            'status_kehadiran' => 'hadir',
        ]);

        // Pulang (hari yang sama)
        Carbon::setTestNow(Carbon::create(2025, 9, 21, 16, 5, 0));

        $resPulang = $this->postJson(route('api.absensi.store'), [
            'device_uid' => $device->device_uid,
            'api_key' => 'SECRET1',
            'finger_id' => 11,
            'event' => 'pulang',
        ]);

        $resPulang->assertStatus(200)
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('event', 'pulang')
            ->assertJsonPath('siswa.id', $siswa->id);

        $this->assertDatabaseHas('absensi_harian', [
            'siswa_id' => $siswa->id,
            'tanggal' => Carbon::now()->toDateString(),
            'waktu_pulang' => '16:05:00',
            'perangkat_pulang_id' => $device->id,
        ]);

        Carbon::setTestNow();
    }

    public function test_invalid_api_key_returns_unauthorized(): void
    {
        $device = $this->makeDevice('ESP32-TEST-2', 'SECRET2');
        $this->makeSiswa(12);

        $res = $this->postJson(route('api.absensi.store'), [
            'device_uid' => $device->device_uid,
            'api_key' => 'WRONG',
            'finger_id' => 12,
            'event' => 'masuk',
        ]);

        $res->assertStatus(401)
            ->assertJsonPath('status', 'error');
    }

    public function test_unknown_finger_id_returns_not_found(): void
    {
        $device = $this->makeDevice('ESP32-TEST-3', 'SECRET3');

        $res = $this->postJson(route('api.absensi.store'), [
            'device_uid' => $device->device_uid,
            'api_key' => 'SECRET3',
            'finger_id' => 9999,
            'event' => 'masuk',
        ]);

        $res->assertStatus(404)
            ->assertJsonPath('status', 'error');
    }

    public function test_validation_error_returns_422(): void
    {
        $res = $this->postJson(route('api.absensi.store'), [
            // missing required fields
        ]);
        $res->assertStatus(422)
            ->assertJsonPath('status', 'error');
    }
}