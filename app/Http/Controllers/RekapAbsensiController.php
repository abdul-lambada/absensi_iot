<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AbsensiHarian;
use App\Models\Kelas;

class RekapAbsensiController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $start = $request->query('start_date', now()->toDateString());
        $end = $request->query('end_date', now()->toDateString());
        $kelasId = $request->query('kelas_id');

        $query = AbsensiHarian::with(['siswa.kelas'])
            ->when($start, fn($q) => $q->whereDate('tanggal', '>=', $start))
            ->when($end, fn($q) => $q->whereDate('tanggal', '<=', $end));

        if ($user->role === 'guru') {
            $allowed = Kelas::where('guru', $user->id)->pluck('id');
            $query->whereHas('siswa', fn($q) => $q->whereIn('kelas_id', $allowed));
        }
        if ($kelasId) {
            $query->whereHas('siswa', fn($q) => $q->where('kelas_id', $kelasId));
        }

        $items = $query->orderBy('tanggal', 'desc')->paginate(20)->withQueryString();

        $kelasOptions = Kelas::when($user->role === 'guru', function($q) use ($user) {
                $q->where('guru', $user->id);
            })
            ->orderBy('nama_kelas')
            ->get(['id','nama_kelas']);

        return view('rekap.index', [
            'title' => 'Rekap Absensi',
            'page_title' => 'Rekap Absensi',
            'items' => $items,
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
        $start = $request->query('start_date', now()->toDateString());
        $end = $request->query('end_date', now()->toDateString());
        $kelasId = $request->query('kelas_id');

        $query = AbsensiHarian::with(['siswa.kelas'])
            ->when($start, fn($q) => $q->whereDate('tanggal', '>=', $start))
            ->when($end, fn($q) => $q->whereDate('tanggal', '<=', $end));

        if ($user->role === 'guru') {
            $allowed = Kelas::where('guru', $user->id)->pluck('id');
            $query->whereHas('siswa', fn($q) => $q->whereIn('kelas_id', $allowed));
        }
        if ($kelasId) {
            $query->whereHas('siswa', fn($q) => $q->where('kelas_id', $kelasId));
        }

        $rows = $query->orderBy('tanggal')->get();

        $filename = 'rekap_absensi_' . ($start ?: 'all') . '_sd_' . ($end ?: 'all') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($rows) {
            $handle = fopen('php://output', 'w');
            // Header
            fputcsv($handle, ['Tanggal', 'Nama Siswa', 'Kelas', 'Waktu Masuk', 'Waktu Pulang', 'Status', 'Keterangan']);
            foreach ($rows as $row) {
                fputcsv($handle, [
                    optional($row->tanggal)->format('Y-m-d') ?? (string) $row->tanggal,
                    optional($row->siswa)->nama_siswa,
                    optional(optional($row->siswa)->kelas)->nama_kelas,
                    $row->waktu_masuk ? \Carbon\Carbon::parse($row->waktu_masuk)->format('H:i:s') : '',
                    $row->waktu_pulang ? \Carbon\Carbon::parse($row->waktu_pulang)->format('H:i:s') : '',
                    $row->status_kehadiran,
                    $row->keterangan,
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}