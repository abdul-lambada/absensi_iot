@extends('dashboard.layout')
@section('page_title', $page_title ?? ($title ?? 'Data'))

@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ $page_title ?? ($title ?? 'Data') }}</h1>
        <a href="{{ route($routePrefix . '.create') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Tambah Data
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
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
                            @foreach ($headers ?? [] as $head)
                                <th>{{ $head }}</th>
                            @endforeach
                            <th style="width:160px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(($rows ?? []) as $row)
                            <tr>
                                @foreach ($row['cols'] ?? [] as $col)
                                    <td>{{ $col }}</td>
                                @endforeach
                                <td>
                                    <a href="{{ route($routePrefix . '.show', $row['id']) }}" class="btn btn-sm btn-info"><i
                                            class="fas fa-eye"></i></a>
                                    <a href="{{ route($routePrefix . '.edit', $row['id']) }}"
                                        class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                    <button type="button" class="btn btn-sm btn-danger btn-delete"
                                        data-action="{{ route($routePrefix . '.destroy', $row['id']) }}"
                                        data-name="{{ $row['name'] ?? ($row['cols'][0] ?? 'ID ' . $row['id']) }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($headers ?? []) + 1 }}" class="text-center text-muted">Belum ada
                                    data.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-muted small">
                    @if (method_exists($items, 'total'))
                        Menampilkan {{ $items->firstItem() ?? 0 }} - {{ $items->lastItem() ?? 0 }} dari
                        {{ $items->total() }} data
                    @endif
                </div>
                <div>
                    {{ $items->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Konfirmasi Hapus (SB Admin 2 / Bootstrap 4) -->
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" role="dialog" aria-labelledby="confirmDeleteLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmDeleteLabel">Konfirmasi Hapus</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">Apakah Anda yakin ingin menghapus data berikut?</div>
                    <div class="font-weight-bold text-danger" id="itemNameToDelete">Item</div>
                    <div class="small text-muted mt-2">Tindakan ini tidak dapat dibatalkan.</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <form id="deleteForm" action="#" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" id="btnConfirmDelete">Hapus</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var modal = $('#confirmDeleteModal');
                var form = document.getElementById('deleteForm');
                var nameTarget = document.getElementById('itemNameToDelete');
                var btnSubmit = document.getElementById('btnConfirmDelete');

                $(document).on('click', '.btn-delete', function() {
                    var action = $(this).data('action');
                    var name = $(this).data('name');
                    if (form && action) {
                        form.setAttribute('action', action);
                    }
                    if (nameTarget && typeof name !== 'undefined') {
                        nameTarget.textContent = name;
                    }
                    modal.modal('show');
                });

                if (form) {
                    form.addEventListener('submit', function() {
                        if (btnSubmit) {
                            btnSubmit.disabled = true;
                            btnSubmit.innerHTML =
                                '<span class="spinner-border spinner-border-sm mr-1" role="status" aria-hidden="true"></span> Menghapus...';
                        }
                    });
                }
            });
        </script>
    @endpush
@endsection
