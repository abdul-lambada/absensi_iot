<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Models\Perangkat;
use App\Models\Siswa;
use App\Models\AbsensiHarian;

class IoTAbsensiController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'device_uid' => 'required|string',
            'api_key' => 'required|string',
            'finger_id' => 'required|integer',
            'event' => 'required|in:masuk,pulang',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $device = Perangkat::where('device_uid', $request->device_uid)
            ->where('api_key', $request->api_key)
            ->where('status_perangkat', 'aktif')
            ->first();

        if (!$device) {
            return response()->json([
                'status' => 'error',
                'message' => 'Perangkat tidak dikenal atau tidak aktif',
            ], 401);
        }

        $siswa = Siswa::where('finger_id', $request->finger_id)
            ->orWhere('template_sidik_jari', (string)$request->finger_id)
            ->first();

        if (!$siswa) {
            return response()->json([
                'status' => 'error',
                'message' => 'Siswa tidak ditemukan untuk finger_id='.$request->finger_id,
            ], 404);
        }

        $now = Carbon::now();
        $today = $now->toDateString();

        try {
            $absen = AbsensiHarian::firstOrCreate(
                ['siswa_id' => $siswa->id, 'tanggal' => $today],
                [
                    'waktu_masuk' => null,
                    'waktu_pulang' => null,
                    'status_kehadiran' => null,
                    'keterangan' => null,
                ]
            );

            if ($request->event === 'masuk') {
                if (!$absen->waktu_masuk) {
                    $absen->waktu_masuk = $now->format('H:i:s');
                    $absen->perangkat_masuk_id = $device->id;
                    // aturan terlambat sederhana: setelah 07:00
                    $absen->status_kehadiran = $now->greaterThan(Carbon::createFromTime(7,0,0)) ? 'terlambat' : 'hadir';
                    $absen->save();
                }
            } else { // pulang
                $absen->waktu_pulang = $now->format('H:i:s');
                $absen->perangkat_pulang_id = $device->id;
                $absen->save();
            }

            return response()->json([
                'status' => 'ok',
                'event' => $request->event,
                'waktu' => $now->toDateTimeString(),
                'siswa' => [
                    'id' => $siswa->id,
                    'nama' => $siswa->nama_siswa,
                ],
                'perangkat' => [
                    'id' => $device->id,
                    'uid' => $device->device_uid,
                ],
                'status_kehadiran' => $absen->status_kehadiran,
            ]);
        } catch (\Throwable $e) {
            Log::error('IoTAbsensi error', ['msg' => $e->getMessage()]);
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan server',
            ], 500);
        }
    }
}