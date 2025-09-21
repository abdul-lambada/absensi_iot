<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
// Tambahan import
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFour();
        // Bagikan jumlah kelas dengan tingkat hadir rendah (hari ini)
        View::composer('*', function ($view) {
            try {
                $user = Auth::user();
                $today = Carbon::today()->toDateString();
                $query = DB::table('kelas as k')
                    ->leftJoin('siswa as s', 's.kelas_id', '=', 'k.id')
                    ->leftJoin('absensi_harian as a', function($join) use ($today) {
                        $join->on('a.siswa_id', '=', 's.id')
                             ->whereDate('a.tanggal', '=', $today);
                    })
                    ->when(($user && $user->role === 'guru'), function($q) use ($user){
                        $q->where('k.guru', $user->id);
                    })
                    ->groupBy('k.id')
                    ->select(
                        'k.id',
                        DB::raw("SUM(CASE WHEN a.status_kehadiran = 'hadir' THEN 1 ELSE 0 END) as hadir"),
                        DB::raw('COUNT(a.id) as total')
                    )
                    ->get();

                $lowCount = $query->filter(function($r){
                    $total = (int) ($r->total ?? 0);
                    if ($total <= 0) return false; // abaikan kelas tanpa log hari ini
                    $hadir = (int) ($r->hadir ?? 0);
                    $pct = $total > 0 ? ($hadir / $total) : 0;
                    return $pct < 0.75; // < 75% hadir
                })->count();
            } catch (\Throwable $e) {
                $lowCount = 0;
            }
            $view->with('lowAttendanceKelasCount', $lowCount);
        });
    }
}
