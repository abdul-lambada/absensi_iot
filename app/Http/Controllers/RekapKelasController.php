<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Kelas;

class RekapKelasController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $start = $request->query('start_date', now()->toDateString());
        $end = $request->query('end_date', now()->toDateString());
        $kelasId = $request->query('kelas_id');

        $allowedKelas = null;
        if ($user && $user->role === 'guru') {
            $allowedKelas = Kelas::where('guru', $user->id)->pluck('id');
        }

        $rows = DB::table('absensi_harian as a')
            ->join('siswa as s', 's.id', '=', 'a.siswa_id')
            ->join('kelas as k', 'k.id', '=', 's.kelas_id')
            ->select(
                'k.id as kelas_id',
                'k.nama_kelas',
                DB::raw("COALESCE(a.status_kehadiran, 'unknown') as status_kehadiran"),
                DB::raw('COUNT(*) as jumlah')
            )
            ->when($start, fn($q) => $q->whereDate('a.tanggal', '>=', $start))
            ->when($end, fn($q) => $q->whereDate('a.tanggal', '<=', $end))
            ->when($allowedKelas, fn($q) => $q->whereIn('k.id', $allowedKelas))
            ->when($kelasId, fn($q) => $q->where('k.id', $kelasId))
            ->groupBy('k.id', 'k.nama_kelas', 'a.status_kehadiran')
            ->orderBy('k.nama_kelas')
            ->get();

        // Pivot ke struktur per kelas
        $rekap = [];
        foreach ($rows as $r) {
            $id = (string) $r->kelas_id;
            if (!isset($rekap[$id])) {
                $rekap[$id] = [
                    'kelas_id' => $r->kelas_id,
                    'nama_kelas' => $r->nama_kelas,
                    'hadir' => 0,
                    'izin' => 0,
                    'sakit' => 0,
                    'alpa' => 0,
                ];
            }
            $status = strtolower((string) $r->status_kehadiran);
            if (isset($rekap[$id][$status])) {
                $rekap[$id][$status] += (int) $r->jumlah;
            }
        }

        // Hitung total per kelas
        foreach ($rekap as $id => $data) {
            $rekap[$id]['total'] = ($data['hadir'] ?? 0) + ($data['izin'] ?? 0) + ($data['sakit'] ?? 0) + ($data['alpa'] ?? 0);
        }

        // Opsi kelas untuk filter
        $kelasOptions = Kelas::when($user && $user->role === 'guru', function($q) use ($user) {
                $q->where('guru', $user->id);
            })
            ->orderBy('nama_kelas')
            ->get(['id','nama_kelas']);

        return view('rekap.kelas', [
            'title' => 'Ringkasan Per Kelas',
            'page_title' => 'Ringkasan Per Kelas',
            'rekap' => $rekap, // array keyed by kelas_id
            'kelasOptions' => $kelasOptions,
            'filters' => [
                'start_date' => $start,
                'end_date' => $end,
                'kelas_id' => $kelasId,
            ],
        ]);
    }
}