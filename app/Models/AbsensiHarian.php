<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AbsensiHarian extends Model
{
    protected $table = 'absensi_harian';

    protected $fillable = [
        'tanggal',
        'waktu_masuk',
        'waktu_pulang',
        'status_kehadiran',
        'keterangan',
        'siswa_id',
        'perangkat_masuk_id',
        'perangkat_pulang_id',
    ];

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function perangkatMasuk(): BelongsTo
    {
        return $this->belongsTo(Perangkat::class, 'perangkat_masuk_id');
    }

    public function perangkatPulang(): BelongsTo
    {
        return $this->belongsTo(Perangkat::class, 'perangkat_pulang_id');
    }

    // Kompatibilitas: beberapa controller/view mengakses relasi 'perangkat'
    // Map ke perangkat_masuk_id sebagai perangkat utama yang digunakan saat check-in
    public function perangkat(): BelongsTo
    {
        return $this->belongsTo(Perangkat::class, 'perangkat_masuk_id');
    }
}
