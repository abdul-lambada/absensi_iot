<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $role = $user->role;

        $common = [
            'title' => 'Profil Saya',
            'user' => $user,
        ];

        try {
            if ($role === 'guru') {
                $kelasIds = DB::table('kelas')->where('guru', $user->id)->pluck('id');
                $roleData = [
                    'roleTitle' => 'Guru',
                    'stats' => [
                        'totalSiswaWali' => DB::table('siswa')->whereIn('kelas_id', $kelasIds)->count(),
                        'totalKelasDiwalikan' => count($kelasIds),
                        'totalAbsensiTodayWali' => DB::table('absensi_harian')
                            ->whereDate('tanggal', Carbon::today())
                            ->whereIn('siswa_id', function ($q) use ($kelasIds) {
                                $q->select('id')->from('siswa')->whereIn('kelas_id', $kelasIds);
                            })->count(),
                    ],
                ];
            } elseif ($role === 'kepala_sekolah') {
                $roleData = [
                    'roleTitle' => 'Kepala Sekolah',
                    'stats' => [
                        'totalSiswa' => DB::table('siswa')->count(),
                        'totalKelas' => DB::table('kelas')->count(),
                        'totalPerangkat' => DB::table('perangkat')->count(),
                        'totalAbsensiToday' => DB::table('absensi_harian')->whereDate('tanggal', Carbon::today())->count(),
                    ],
                ];
            } else { // admin
                $roleData = [
                    'roleTitle' => 'Admin',
                    'stats' => [
                        'totalSiswa' => DB::table('siswa')->count(),
                        'totalKelas' => DB::table('kelas')->count(),
                        'totalPerangkat' => DB::table('perangkat')->count(),
                        'totalAbsensiToday' => DB::table('absensi_harian')->whereDate('tanggal', Carbon::today())->count(),
                    ],
                ];
            }
        } catch (QueryException $e) {
            // fallback bila tabel belum ada
            if ($role === 'guru') {
                $roleData = [
                    'roleTitle' => 'Guru',
                    'stats' => [
                        'totalSiswaWali' => 0,
                        'totalKelasDiwalikan' => 0,
                        'totalAbsensiTodayWali' => 0,
                    ],
                ];
            } else {
                $roleData = [
                    'roleTitle' => $role === 'kepala_sekolah' ? 'Kepala Sekolah' : 'Admin',
                    'stats' => [
                        'totalSiswa' => 0,
                        'totalKelas' => 0,
                        'totalPerangkat' => 0,
                        'totalAbsensiToday' => 0,
                    ],
                ];
            }
        }

        // Ambil 10 aktivitas sesi terakhir user dari tabel sessions
        $activities = [];
        try {
            $rows = DB::table('sessions')
                ->where('user_id', $user->id)
                ->orderByDesc('last_activity')
                ->limit(10)
                ->get(['ip_address', 'user_agent', 'last_activity']);
            foreach ($rows as $r) {
                $activities[] = [
                    'ip' => $r->ip_address,
                    'user_agent' => $r->user_agent,
                    'time' => Carbon::createFromTimestamp((int) $r->last_activity)->toDateTimeString(),
                ];
            }
        } catch (\Throwable $e) {
            $activities = [];
        }

        // Shortcut sesuai role
        $shortcuts = [];
        if ($role === 'admin') {
            $shortcuts = [
                ['label' => 'Data Pengguna', 'icon' => 'fa-users-cog', 'route' => 'users.index'],
                ['label' => 'Data Siswa', 'icon' => 'fa-user-graduate', 'route' => 'siswa.index'],
                ['label' => 'Data Kelas', 'icon' => 'fa-school', 'route' => 'kelas.index'],
                ['label' => 'Perangkat', 'icon' => 'fa-microchip', 'route' => 'perangkat.index'],
                ['label' => 'Rekap Absensi', 'icon' => 'fa-chart-line', 'route' => 'rekap.index'],
            ];
        } elseif ($role === 'guru') {
            $shortcuts = [
                ['label' => 'Kelas Saya', 'icon' => 'fa-chalkboard-teacher', 'route' => 'kelas-saya.index'],
                ['label' => 'Absensi Harian', 'icon' => 'fa-clipboard-check', 'route' => 'absensi-harian.index'],
                ['label' => 'Rekap Absensi', 'icon' => 'fa-chart-line', 'route' => 'rekap.index'],
            ];
        } else { // kepala sekolah
            $shortcuts = [
                ['label' => 'Rekap Kelas', 'icon' => 'fa-chalkboard', 'route' => 'rekap.kelas'],
                ['label' => 'Rekap Absensi', 'icon' => 'fa-chart-line', 'route' => 'rekap.index'],
                ['label' => 'Data Kelas', 'icon' => 'fa-school', 'route' => 'kelas.index'],
            ];
        }

        return view('profile.index', array_merge($common, $roleData, [
            'activities' => $activities,
            'shortcuts' => $shortcuts,
        ]));
    }

    public function edit()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }
        return view('profile.edit', [
            'title' => 'Edit Profil',
            'page_title' => 'Edit Profil',
            'user' => $user,
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        // Update basic fields
        $user->nama_lengkap = $validated['nama_lengkap'];
        $user->email = $validated['email'] ?? null;

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            if (!empty($user->avatar_path)) {
                try {
                    Storage::disk('public')->delete($user->avatar_path);
                } catch (\Throwable $e) {
                }
            }
            $user->avatar_path = $path;
        }

        DB::table('users')->where('id', $user->id)->update([
            'nama_lengkap' => $user->nama_lengkap,
            'email' => $user->email,
            'avatar_path' => $user->avatar_path
        ]);

        return redirect()->route('profile.index')->with('success', 'Profil berhasil diperbarui.');
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (!Hash::check($validated['current_password'], $user->password_hash)) {
            return back()->withErrors(['current_password' => 'Password saat ini tidak sesuai.']);
        }

        $user->password_hash = Hash::make($validated['new_password']);
        DB::table('users')->where('id', $user->id)->update([
            'password_hash' => $user->password_hash
        ]);

        return redirect()->route('profile.index')->with('success', 'Password berhasil diperbarui.');
    }
}
