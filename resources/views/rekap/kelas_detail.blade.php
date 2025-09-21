@extends('dashboard.layout')
@section('page_title', $page_title ?? ('Detail Kelas: ' . ($kelas->nama_kelas ?? '')))

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Detail Kelas: {{ $kelas->nama_kelas }}</h1>
    <a href="{{ route('rekap.kelas', ['start_date' => $filters['start_date'] ?? null, 'end_date' => $filters['end_date'] ?? null, 'kelas_id' => $kelas->id]) }}" class="btn btn-sm btn-outline-secondary">
        <i class="fas fa-arrow-left mr-1"></i> Kembali ke Ringkasan
    </a>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Breakdown Siswa ({{ $kelas->nama_kelas }})</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0">
                <thead class="thead-light">
                    <tr>
                        <th>Nama Siswa</th>
                        <th class="text-center">Hadir</th>
                        <th class="text-center">Izin</th>
                        <th class="text-center">Sakit</th>
                        <th class="text-center">Alpa</th>
                        <th class="text-center">Total</th>
                        <th class="text-center">% Hadir</th>
                    </tr>
                </thead>
                <tbody>
                    @php $items = collect($rekapSiswa ?? [])->values(); @endphp
                    @forelse($items as $row)
                        @php
                            $total = (int)($row['total'] ?? 0);
                            $hadir = (int)($row['hadir'] ?? 0);
                            $izin = (int)($row['izin'] ?? 0);
                            $sakit = (int)($row['sakit'] ?? 0);
                            $alpa = (int)($row['alpa'] ?? 0);
                            $percent = $total > 0 ? round(($hadir / $total) * 100) : 0;
                        @endphp
                        <tr>
                            <td>{{ $row['nama_siswa'] }}</td>
                            <td class="text-center text-success font-weight-bold">{{ $hadir }}</td>
                            <td class="text-center text-warning font-weight-bold">{{ $izin }}</td>
                            <td class="text-center text-info font-weight-bold">{{ $sakit }}</td>
                            <td class="text-center text-danger font-weight-bold">{{ $alpa }}</td>
                            <td class="text-center">{{ $total }}</td>
                            <td class="text-center">{{ $percent }}%</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">Tidak ada data untuk filter ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection