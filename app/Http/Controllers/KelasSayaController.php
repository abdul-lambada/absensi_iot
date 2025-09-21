<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Kelas;

class KelasSayaController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $items = Kelas::withCount('siswa')
            ->where('guru', $user->id)
            ->orderBy('nama_kelas')
            ->paginate(10);

        return view('guru.kelas_saya', [
            'title' => 'Kelas Saya',
            'page_title' => 'Kelas Saya',
            'items' => $items,
        ]);
    }
}