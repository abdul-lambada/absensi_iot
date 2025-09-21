<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }
        $role = $user->role;

        if ($role === 'guru') {
            return $this->guru();
        }
        if ($role === 'kepala_sekolah') {
            return $this->kepalaSekolah();
        }
        return $this->admin();
    }

    public function admin()
    {
        try {
            $data = [
                'title' => 'Dashboard Admin',
                'totalSiswa' => DB::table('siswa')->count(),
                'totalKelas' => DB::table('kelas')->count(),
                'totalPerangkat' => DB::table('perangkat')->count(),
                'totalAbsensiToday' => DB::table('absensi_harian')->whereDate('tanggal', Carbon::today())->count(),
            ];
        } catch (QueryException $e) {
            $data = [
                'title' => 'Dashboard Admin',
                'totalSiswa' => 0,
                'totalKelas' => 0,
                'totalPerangkat' => 0,
                'totalAbsensiToday' => 0,
            ];
        }
        return view('dashboard.admin', $data);
    }

    public function guru()
    {
        $user = Auth::user();
        $waliId = $user?->id ?? 0;
        try {
            // NOTE: sesuaikan kolom foreign key di tabel kelas jika berbeda
            $kelasIds = DB::table('kelas')->where('guru', $waliId)->pluck('id');
            $siswaCount = DB::table('siswa')->whereIn('kelas_id', $kelasIds)->count();
            $absensiToday = DB::table('absensi_harian')
                ->whereDate('tanggal', Carbon::today())
                ->whereIn('siswa_id', function ($q) use ($kelasIds) {
                    $q->select('id')->from('siswa')->whereIn('kelas_id', $kelasIds);
                })->count();

            $data = [
                'title' => 'Dashboard Guru',
                'totalSiswaWali' => $siswaCount,
                'totalKelasDiwalikan' => count($kelasIds),
                'totalAbsensiTodayWali' => $absensiToday,
            ];
        } catch (QueryException $e) {
            $data = [
                'title' => 'Dashboard Guru',
                'totalSiswaWali' => 0,
                'totalKelasDiwalikan' => 0,
                'totalAbsensiTodayWali' => 0,
            ];
        }
        return view('dashboard.guru', $data);
    }

    public function kepalaSekolah()
    {
        try {
            $data = [
                'title' => 'Dashboard Kepala Sekolah',
                'totalSiswa' => DB::table('siswa')->count(),
                'totalKelas' => DB::table('kelas')->count(),
                'totalPerangkat' => DB::table('perangkat')->count(),
                'totalAbsensiToday' => DB::table('absensi_harian')->whereDate('tanggal', Carbon::today())->count(),
            ];
        } catch (QueryException $e) {
            $data = [
                'title' => 'Dashboard Kepala Sekolah',
                'totalSiswa' => 0,
                'totalKelas' => 0,
                'totalPerangkat' => 0,
                'totalAbsensiToday' => 0,
            ];
        }
        return view('dashboard.kepala_sekolah', $data);
    }
}