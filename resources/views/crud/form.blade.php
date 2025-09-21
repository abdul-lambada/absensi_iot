@extends('dashboard.layout')
@section('page_title', $page_title ?? $title ?? 'Form')

@section('content')
<div class="d-sm-flex align-items-center justify-content-end mb-4">
    <a href="{{ route($routePrefix.'.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
        <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali
    </a>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">{{ $title ?? '' }}</h6>
    </div>
    <div class="card-body">
        <form action="{{ $action }}" method="POST">
            @csrf
            @if(($method ?? 'POST') !== 'POST')
                @method($method)
            @endif

            <div class="row">
                @foreach(($fields ?? []) as $field)
                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold">{{ $field['label'] ?? ucfirst($field['name']) }}</label>
                        @php($type = $field['type'] ?? 'text')
                        @if($type === 'textarea')
                            <textarea name="{{ $field['name'] }}" class="form-control @error($field['name']) is-invalid @enderror" rows="3">{{ old($field['name'], $field['value'] ?? '') }}</textarea>
                        @elseif($type === 'select')
                            <select name="{{ $field['name'] }}" class="form-control @error($field['name']) is-invalid @enderror">
                                <option value="">-- Pilih {{ $field['label'] ?? ucfirst($field['name']) }} --</option>
                                @foreach(($field['options'] ?? []) as $opt)
                                    <option value="{{ $opt['value'] }}" {{ (string)old($field['name'], $field['value'] ?? '') === (string)$opt['value'] ? 'selected' : '' }}>{{ $opt['label'] }}</option>
                                @endforeach
                            </select>
                        @else
                            <input type="{{ $type }}" name="{{ $field['name'] }}" value="{{ old($field['name'], $field['value'] ?? '') }}" class="form-control @error($field['name']) is-invalid @enderror" />
                        @endif
                        @error($field['name'])
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                @endforeach
            </div>

            <div class="mt-3">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Simpan</button>
                <a href="{{ route($routePrefix.'.index') }}" class="btn btn-light">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection