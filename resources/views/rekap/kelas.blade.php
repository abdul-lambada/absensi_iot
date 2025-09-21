@extends('dashboard.layout')
@section('page_title', 'Ringkasan Per Kelas')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Ringkasan Per Kelas</h1>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Filter</h6>
        <a href="{{ route('rekap.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-list mr-1"></i> Rekap Detail</a>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('rekap.kelas') }}" class="row">
            <div class="form-group col-md-3">
                <label>Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ $filters['start_date'] ?? '' }}" class="form-control">
            </div>
            <div class="form-group col-md-3">
                <label>Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ $filters['end_date'] ?? '' }}" class="form-control">
            </div>
            <div class="form-group col-md-4">
                <label>Kelas</label>
                <select name="kelas_id" class="form-control">
                    <option value="">-- Semua Kelas --</option>
                    @foreach(($kelasOptions ?? []) as $k)
                        <option value="{{ $k->id }}" {{ (string)($filters['kelas_id'] ?? '') === (string)$k->id ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-filter mr-1"></i> Terapkan
                </button>
            </div>
        </form>
    </div>
</div>

<div class="row">
    @php
        // Siapkan data untuk sparkline chart dan badge total
        $cards = collect($rekap ?? [])->values();
    @endphp

    @forelse($cards as $data)
        @php
            $total = ($data['total'] ?? 0) ?: 1;
            $series = [
                (int)($data['hadir'] ?? 0),
                (int)($data['izin'] ?? 0),
                (int)($data['sakit'] ?? 0),
                (int)($data['alpa'] ?? 0),
            ];
            $percentHadir = round(($series[0] / $total) * 100);
            $chartId = 'spark_' . $data['kelas_id'];
        @endphp
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase">{{ $data['nama_kelas'] }}</div>
                        <span class="badge badge-pill badge-primary">{{ $data['total'] ?? 0 }} log</span>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <div class="h5 mb-0 font-weight-bold text-gray-800 mr-2">{{ $percentHadir }}%</div>
                        <div class="text-xs text-muted">Hadir</div>
                    </div>
                    <canvas id="{{ $chartId }}" height="60"></canvas>
                    <div class="mt-2 d-flex justify-content-between text-xs">
                        <span class="text-success">H {{ $series[0] }}</span>
                        <span class="text-warning">I {{ $series[1] }}</span>
                        <span class="text-info">S {{ $series[2] }}</span>
                        <span class="text-danger">A {{ $series[3] }}</span>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="alert alert-info">Belum ada data untuk rentang/filter yang dipilih.</div>
        </div>
    @endforelse
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function(){
        // Warna sesuai status
        const colors = {
            hadir: '#1cc88a',   // success
            izin: '#f6c23e',    // warning
            sakit: '#36b9cc',   // info
            alpa: '#e74a3b'     // danger
        };
        @foreach(($rekap ?? []) as $data)
            (function(){
                var ctx = document.getElementById('spark_{{ $data['kelas_id'] }}');
                if (!ctx) return;
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: ['H', 'I', 'S', 'A'],
                        datasets: [{
                            data: [{{ (int)($data['hadir'] ?? 0) }}, {{ (int)($data['izin'] ?? 0) }}, {{ (int)($data['sakit'] ?? 0) }}, {{ (int)($data['alpa'] ?? 0) }}],
                            backgroundColor: [colors.hadir, colors.izin, colors.sakit, colors.alpa],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false }, tooltip: { enabled: true } },
                        scales: {
                            x: { display: false },
                            y: { display: false, beginAtZero: true }
                        }
                    }
                });
            })();
        @endforeach
    });
</script>
@endpush