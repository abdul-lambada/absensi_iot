<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('absensi_harian', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->time('waktu_masuk')->nullable();
            $table->time('waktu_pulang')->nullable();
            $table->enum('status_kehadiran', ['hadir', 'izin', 'sakit', 'alpa', 'terlambat'])->nullable();
            $table->text('keterangan')->nullable();
            $table->foreignId('siswa_id')
                ->constrained('siswa')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->foreignId('perangkat_masuk_id')
                ->nullable()
                ->constrained('perangkat')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->foreignId('perangkat_pulang_id')
                ->nullable()
                ->constrained('perangkat')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absensi_harian');
    }
};