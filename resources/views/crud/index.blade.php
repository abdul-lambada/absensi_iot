@extends('dashboard.layout')
@section('page_title', $page_title ?? $title ?? 'Data')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">{{ $page_title ?? $title ?? 'Data' }}</h1>
    <a href="{{ route($routePrefix.'.create') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
        <i class="fas fa-plus fa-sm text-white-50"></i> Tambah Data
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Daftar {{ $title ?? '' }}</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0">
                <thead class="thead-light">
                    <tr>
                        @foreach(($headers ?? []) as $head)
                            <th>{{ $head }}</th>
                        @endforeach
                        <th style="width:160px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @php($paramKey = $paramKey ?? str_replace('-', '_', $routePrefix))
                    @forelse(($rows ?? []) as $row)
                        <tr>
                            @foreach(($row['cols'] ?? []) as $col)
                                <td>{{ $col }}</td>
                            @endforeach
                            <td>
                                <a href="{{ route($routePrefix.'.show', [$paramKey => $row['id']]) }}" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                                <a href="{{ route($routePrefix.'.edit', [$paramKey => $row['id']]) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                <form action="{{ route($routePrefix.'.destroy', [$paramKey => $row['id']]) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($headers ?? []) + 1 }}" class="text-center text-muted">Belum ada data.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-between align-items-center">
            <div class="text-muted small">
                @if(method_exists($items, 'total'))
                    Menampilkan {{ $items->firstItem() ?? 0 }} - {{ $items->lastItem() ?? 0 }} dari {{ $items->total() }} data
                @endif
            </div>
            <div>
                {{ $items->links() }}
            </div>
        </div>
    </div>
</div>
@endsection