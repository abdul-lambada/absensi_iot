@extends('dashboard.layout')
@section('page_title', 'Rekap Absensi')

@section('content')
{{-- Heading duplikat dihapus --}}
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Filter</h6>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('rekap.index') }}" class="row">
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
                <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-filter mr-1"></i> Terapkan</button>
            </div>
        </form>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Hasil Rekap</h6>
        <div>
            <a class="btn btn-success btn-sm" href="{{ route('rekap.export', request()->query()) }}">
                <i class="fas fa-file-csv mr-1"></i> Export CSV
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0">
                <thead class="thead-light">
                    <tr>
                        <th>Tanggal</th>
                        <th>Nama Siswa</th>
                        <th>Kelas</th>
                        <th>Waktu Masuk</th>
                        <th>Waktu Pulang</th>
                        <th>Status</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse(($items ?? []) as $row)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($row->tanggal)->format('d-m-Y') }}</td>
                            <td>{{ optional($row->siswa)->nama_siswa }}</td>
                            <td>{{ optional(optional($row->siswa)->kelas)->nama_kelas }}</td>
                            <td>{{ $row->waktu_masuk ? \Carbon\Carbon::parse($row->waktu_masuk)->format('H:i') : '-' }}</td>
                            <td>{{ $row->waktu_pulang ? \Carbon\Carbon::parse($row->waktu_pulang)->format('H:i') : '-' }}</td>
                            <td>{{ $row->status_kehadiran ?? '-' }}</td>
                            <td>{{ $row->keterangan ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">Tidak ada data untuk rentang/filter dipilih.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($items ?? null, 'links'))
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-muted small">
                    Menampilkan {{ $items->firstItem() ?? 0 }} - {{ $items->lastItem() ?? 0 }} dari {{ $items->total() }} data
                </div>
                <div>{{ $items->links() }}</div>
            </div>
        @endif
    </div>
</div>
@endsection