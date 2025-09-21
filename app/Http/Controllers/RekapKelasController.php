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

    public function export(Request $request)
    {
        $user = Auth::user();
        $start = $request->query('start_date');
        $end = $request->query('end_date');
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

        $rekap = [];
        foreach ($rows as $r) {
            $id = (string) $r->kelas_id;
            if (!isset($rekap[$id])) {
                $rekap[$id] = [
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
        foreach ($rekap as $id => $d) {
            $total = ($d['hadir'] ?? 0) + ($d['izin'] ?? 0) + ($d['sakit'] ?? 0) + ($d['alpa'] ?? 0);
            $rekap[$id]['total'] = $total;
            $rekap[$id]['percent_hadir'] = $total > 0 ? round(($d['hadir'] / $total) * 100) : 0;
        }

        $filename = 'rekap_kelas_' . now()->format('Ymd_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($rekap) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Kelas', 'Hadir', 'Izin', 'Sakit', 'Alpa', 'Total', 'Persen_Hadir']);
            foreach ($rekap as $data) {
                fputcsv($out, [
                    $data['nama_kelas'] ?? '-',
                    (int)($data['hadir'] ?? 0),
                    (int)($data['izin'] ?? 0),
                    (int)($data['sakit'] ?? 0),
                    (int)($data['alpa'] ?? 0),
                    (int)($data['total'] ?? 0),
                    (int)($data['percent_hadir'] ?? 0),
                ]);
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function detail(Request $request, $kelas)
    {
        $user = Auth::user();
        $start = $request->query('start_date');
        $end = $request->query('end_date');

        $kelasModel = Kelas::findOrFail($kelas);
        if ($user && $user->role === 'guru' && $kelasModel->guru != $user->id) {
            abort(403);
        }

        $rows = DB::table('siswa as s')
            ->leftJoin('absensi_harian as a', function($join) use ($start, $end) {
                $join->on('a.siswa_id', '=', 's.id');
                if ($start) $join->whereDate('a.tanggal', '>=', $start);
                if ($end) $join->whereDate('a.tanggal', '<=', $end);
            })
            ->where('s.kelas_id', $kelasModel->id)
            ->select(
                's.id as siswa_id',
                's.nama_siswa',
                DB::raw("COALESCE(a.status_kehadiran, 'unknown') as status_kehadiran"),
                DB::raw('COUNT(a.id) as jumlah')
            )
            ->groupBy('s.id', 's.nama_siswa', 'a.status_kehadiran')
            ->orderBy('s.nama_siswa')
            ->get();

        $rekapSiswa = [];
        foreach ($rows as $r) {
            $id = (string) $r->siswa_id;
            if (!isset($rekapSiswa[$id])) {
                $rekapSiswa[$id] = [
                    'siswa_id' => $r->siswa_id,
                    'nama_siswa' => $r->nama_siswa,
                    'hadir' => 0,
                    'izin' => 0,
                    'sakit' => 0,
                    'alpa' => 0,
                ];
            }
            $status = strtolower((string) $r->status_kehadiran);
            if (isset($rekapSiswa[$id][$status])) {
                $rekapSiswa[$id][$status] += (int) $r->jumlah;
            }
        }
        foreach ($rekapSiswa as $id => $d) {
            $total = ($d['hadir'] ?? 0) + ($d['izin'] ?? 0) + ($d['sakit'] ?? 0) + ($d['alpa'] ?? 0);
            $rekapSiswa[$id]['total'] = $total;
            $rekapSiswa[$id]['percent_hadir'] = $total > 0 ? round(($d['hadir'] / $total) * 100) : 0;
        }

        return view('rekap.kelas_detail', [
            'title' => 'Detail Kelas: ' . $kelasModel->nama_kelas,
            'page_title' => 'Detail Kelas: ' . $kelasModel->nama_kelas,
            'kelas' => $kelasModel,
            'rekapSiswa' => $rekapSiswa,
            'filters' => [
                'start_date' => $start,
                'end_date' => $end,
            ],
        ]);
    }
}