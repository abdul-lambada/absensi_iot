@extends('dashboard.layout')
@section('page_title', $page_title ?? $title ?? 'Detail')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">{{ $page_title ?? $title ?? 'Detail' }}</h1>
    <div>
        @php($paramKey = $paramKey ?? str_replace('-', '_', $routePrefix))
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
@endsection