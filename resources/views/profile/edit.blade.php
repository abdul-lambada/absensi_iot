@extends('dashboard.layout')
@section('page_title', $page_title ?? $title ?? 'Edit Profil')

@section('content')
<div class="d-sm-flex align-items-center justify-content-end mb-4">
    <a href="{{ route('profile.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm"><i class="fas fa-arrow-left mr-1"></i> Kembali</a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif
@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Periksa kembali input Anda.</strong>
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

<div class="row">
    <div class="col-lg-7">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Update Profil</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label for="nama_lengkap">Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" id="nama_lengkap" value="{{ old('nama_lengkap', $user->nama_lengkap) }}" class="form-control @error('nama_lengkap') is-invalid @enderror" required>
                        @error('nama_lengkap')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" class="form-control @error('email') is-invalid @enderror" placeholder="nama@sekolah.sch.id">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="avatar">Foto Profil (Avatar)</label>
                        <div class="custom-file">
                            <input type="file" name="avatar" id="avatar" class="custom-file-input @error('avatar') is-invalid @enderror" accept="image/*">
                            <label class="custom-file-label" for="avatar">Pilih berkas gambar...</label>
                            @error('avatar')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <small class="form-text text-muted">Format: jpg, jpeg, png, webp. Maksimal 2 MB.</small>
                    </div>

                    <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Simpan Perubahan</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Foto Saat Ini</h6>
            </div>
            <div class="card-body text-center">
                @php($avatar = !empty($user->avatar_path) ? asset('storage/'.$user->avatar_path) : asset('/sb_admin/img/undraw_profile.svg'))
                <img src="{{ $avatar }}" alt="Avatar" class="rounded-circle mb-3" width="120" height="120" style="object-fit:cover">
                <div class="text-muted small">Ukuran disarankan persegi (1:1) agar tidak terpotong.</div>
            </div>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-danger">Ubah Password</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('profile.password') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="current_password">Password Saat Ini</label>
                        <input type="password" name="current_password" id="current_password" class="form-control @error('current_password') is-invalid @enderror" required>
                        @error('current_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="new_password">Password Baru</label>
                        <input type="password" name="new_password" id="new_password" class="form-control @error('new_password') is-invalid @enderror" required minlength="8">
                        @error('new_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Minimal 8 karakter.</small>
                    </div>
                    <div class="form-group">
                        <label for="new_password_confirmation">Konfirmasi Password Baru</label>
                        <input type="password" name="new_password_confirmation" id="new_password_confirmation" class="form-control" required minlength="8">
                    </div>
                    <button type="submit" class="btn btn-danger"><i class="fas fa-key mr-1"></i> Ubah Password</button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Update label nama file pada input file bootstrap custom-file
    document.addEventListener('DOMContentLoaded', function () {
        var fileInput = document.getElementById('avatar');
        if (fileInput) {
            fileInput.addEventListener('change', function(e) {
                var label = this.nextElementSibling;
                if (label && this.files && this.files.length > 0) {
                    label.innerText = this.files[0].name;
                }
            });
        }
    });
</script>
@endpush
@endsection