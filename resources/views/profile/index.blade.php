@extends('dashboard.layout')
@section('page_title', 'Profil Saya')

@section('content')
<div class="row">
    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <div class="card-body text-center">
                @php($avatar = !empty($user->avatar_path) ? asset('storage/'.$user->avatar_path) : asset('/sb_admin/img/undraw_profile.svg'))
                <img src="{{ $avatar }}" class="img-profile rounded-circle mb-3" style="width:100px;height:100px;object-fit:cover;" alt="Avatar">
                <h5 class="mb-1">{{ $user->nama_lengkap ?? $user->username }}</h5>
                <span class="badge badge-primary text-uppercase">{{ str_replace('_',' ', $user->role) }}</span>
                <hr>
                <p class="mb-1"><i class="fas fa-user mr-2"></i> Username: <strong>{{ $user->username }}</strong></p>
                @if(!empty($user->email))
                <p class="mb-0"><i class="fas fa-envelope mr-2"></i> Email: <strong>{{ $user->email }}</strong></p>
                @endif
            </div>
        </div>
        <a href="{{ route('profile.edit') }}" class="btn btn-sm btn-primary mb-4"><i class="fas fa-user-edit mr-1"></i> Edit Profil</a>
    </div>
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Ringkasan Peran: {{ $roleTitle }}</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    @if(isset($stats['totalSiswa']))
                        <div class="col-md-3 mb-3">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Siswa</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['totalSiswa'] }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Kelas</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['totalKelas'] }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card border-left-warning shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Total Perangkat</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['totalPerangkat'] }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card border-left-info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Absensi Hari Ini</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['totalAbsensiToday'] }}</div>
                                </div>
                            </div>
                        </div>
                    @elseif(isset($stats['totalSiswaWali']))
                        <div class="col-md-4 mb-3">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Jumlah Siswa</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['totalSiswaWali'] }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Kelas Diwalikan</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['totalKelasDiwalikan'] }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card border-left-info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Absensi Hari Ini</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['totalAbsensiTodayWali'] }}</div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="col-12">
                            <div class="alert alert-info mb-0">Belum ada statistik yang tersedia untuk peran ini.</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Riwayat Aktivitas</h6>
                    </div>
                    <div class="card-body">
                        @if(!empty($activities))
                            <ul class="list-unstyled mb-0">
                                @foreach($activities as $a)
                                    <li class="mb-2">
                                        <i class="fas fa-clock text-gray-400 mr-1"></i>
                                        <strong>{{ $a['time'] }}</strong>
                                        <div class="small text-muted">IP: {{ $a['ip'] ?? '-' }} | {{ Str::limit($a['user_agent'] ?? '-', 60) }}</div>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <div class="text-muted">Belum ada data aktivitas.</div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Shortcut</h6>
                    </div>
                    <div class="card-body">
                        @if(!empty($shortcuts))
                            <div class="list-group">
                                @foreach($shortcuts as $s)
                                    <a href="{{ route($s['route']) }}" class="list-group-item list-group-item-action">
                                        <i class="fas {{ $s['icon'] }} mr-2"></i>{{ $s['label'] }}
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <div class="text-muted">Tidak ada shortcut untuk peran ini.</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection