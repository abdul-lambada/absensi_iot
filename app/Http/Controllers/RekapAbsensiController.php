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
}