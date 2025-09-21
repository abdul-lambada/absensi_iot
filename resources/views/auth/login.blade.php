<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Masuk | Absensi IoT</title>
    <link href="/sb_admin/css/sb-admin-2.min.css" rel="stylesheet">
    <link href="/sb_admin/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <style>
        .brand-title {
            font-weight: 800;
            letter-spacing: .3px;
        }

        .login-subtitle {
            color: #6c757d;
        }

        .bg-login-side {
            background: linear-gradient(180deg, #4e73df 10%, #224abe 100%);
        }

        .logo-circle {
            width: 64px;
            height: 64px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: #ebf1ff;
            color: #4e73df;
        }
    </style>
</head>

<body class="bg-light">

    <div class="container">
        <!-- Outer Row -->
        <div class="row justify-content-center">

            <div class="col-xl-10 col-lg-12 col-md-9">

                <div class="card o-hidden border-0 shadow-lg my-5">
                    <div class="card-body p-0">
                        <!-- Nested Row within Card Body -->
                        <div class="row">
                            <div
                                class="col-lg-6 d-none d-lg-flex align-items-center justify-content-center bg-login-side">
                                <div class="text-center text-white p-5">
                                    <div class="logo-circle mb-3 mx-auto"><i class="fas fa-clipboard-check fa-lg"></i>
                                    </div>
                                    <h1 class="h4 mb-2">Absensi IoT</h1>
                                    <p class="mb-0">Sistem manajemen absensi sekolah berbasis IoT</p>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="p-5">
                                    <div class="text-center mb-4">
                                        <h1 class="h4 text-gray-900 mb-1 brand-title">Masuk ke Akun</h1>
                                        <p class="login-subtitle mb-0">Silakan masuk untuk melanjutkan</p>
                                    </div>

                                    @if (session('status'))
                                        <div class="alert alert-info alert-dismissible fade show" role="alert">
                                            {{ session('status') }}
                                            <button type="button" class="close" data-dismiss="alert"
                                                aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                    @endif

                                    @if ($errors->any())
                                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                            <strong>Login gagal.</strong> Periksa kembali data Anda.
                                            <ul class="mb-0 mt-2">
                                                @foreach ($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                            <button type="button" class="close" data-dismiss="alert"
                                                aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                    @endif

                                    <form class="user" method="POST" action="{{ route('login.attempt') }}">
                                        @csrf
                                        <div class="form-group">
                                            <label for="email" class="small text-gray-600">Email</label>
                                            <input type="email" id="email" class="form-control form-control-user"
                                                name="email" value="{{ old('email') }}"
                                                placeholder="nama@sekolah.sch.id" required autofocus>
                                        </div>
                                        <div class="form-group">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <label for="password" class="small text-gray-600 mb-0">Password</label>
                                            </div>
                                            <input type="password" id="password" class="form-control form-control-user"
                                                name="password" placeholder="••••••••" required>
                                        </div>
                                        <div class="form-group">
                                            <div class="custom-control custom-checkbox small">
                                                <input type="checkbox" class="custom-control-input" id="remember"
                                                    name="remember" value="1">
                                                <label class="custom-control-label" for="remember">Ingat saya</label>
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-user btn-block">
                                            <i class="fas fa-sign-in-alt mr-1"></i> Masuk
                                        </button>
                                    </form>
                                    <hr>
                                    <div class="text-center small text-muted">
                                        &copy; {{ date('Y') }} Absensi IoT
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </div>

    <script src="/sb_admin/vendor/jquery/jquery.min.js"></script>
    <script src="/sb_admin/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="/sb_admin/vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="/sb_admin/js/sb-admin-2.min.js"></script>
</body>

</html>
