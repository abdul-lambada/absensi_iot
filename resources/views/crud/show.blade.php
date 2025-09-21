@extends('dashboard.layout')
@section('page_title', $page_title ?? $title ?? 'Detail')

@section('content')
<div class="d-sm-flex align-items-center justify-content-end mb-4">
    <div>
        @php($paramKey = $paramKey ?? str_replace('-', '_', $routePrefix))
        <button type="button" class="btn btn-sm btn-danger shadow-sm btn-delete" data-action="{{ route($routePrefix.'.destroy', [$paramKey => $item->id]) }}" data-name="{{ $title ?? 'Item' }}: {{ $fields[0]['value'] ?? ($item->name ?? $item->id) }}"><i class="fas fa-trash fa-sm text-white-50"></i> Hapus</button>
        <a href="{{ route($routePrefix.'.edit', [$paramKey => $item->id]) }}" class="btn btn-sm btn-warning shadow-sm"><i class="fas fa-edit fa-sm text-white-50"></i> Ubah</a>
        <a href="{{ route($routePrefix.'.index') }}" class="btn btn-sm btn-secondary shadow-sm"><i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali</a>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">{{ $title ?? '' }}</h6>
    </div>
    <div class="card-body">
        <div class="row">
            @foreach(($fields ?? []) as $field)
                <div class="col-md-6 mb-3">
                    <div class="border rounded p-3 h-100">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">{{ $field['label'] ?? ucfirst($field['name']) }}</div>
                        @php($value = $field['value'] ?? null)
                        @if(($field['type'] ?? '') === 'select')
                            @php($opts = collect($field['options'] ?? []))
                            @php($found = $opts->firstWhere('value', $value))
                            @php($label = is_array($found) ? ($found['label'] ?? '-') : (is_object($found) ? ($found->label ?? '-') : '-'))
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $label }}</div>
                        @elseif(($field['type'] ?? '') === 'textarea')
                            <div class="text-gray-800">{!! nl2br(e($value ?? '-')) !!}</div>
                        @elseif(($field['type'] ?? '') === 'datetime-local')
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $value ? \Carbon\Carbon::parse($value)->format('d-m-Y H:i') : '-' }}</div>
                        @else
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $value ?? '-' }}</div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Hapus (reused) -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" role="dialog" aria-labelledby="confirmDeleteLabel" aria-hidden="true">
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
    document.addEventListener('DOMContentLoaded', function () {
        var modal = $('#confirmDeleteModal');
        var form = document.getElementById('deleteForm');
        var nameTarget = document.getElementById('itemNameToDelete');
        var btnSubmit = document.getElementById('btnConfirmDelete');

        $(document).on('click', '.btn-delete', function () {
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
                    btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm mr-1" role="status" aria-hidden="true"></span> Menghapus...';
                }
            });
        }
    });
</script>
@endpush
@endsection