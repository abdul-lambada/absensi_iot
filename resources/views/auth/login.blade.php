<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Login - Absensi IoT</title>
    <link href="/sb_admin/css/sb-admin-2.min.css" rel="stylesheet">
    <link href="/sb_admin/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
</head>

<body class="bg-gradient-primary">

    <div class="container">
        <!-- Outer Row -->
        <div class="row justify-content-center">

            <div class="col-xl-10 col-lg-12 col-md-9">

                <div class="card o-hidden border-0 shadow-lg my-5">
                    <div class="card-body p-0">
                        <!-- Nested Row within Card Body -->
                        <div class="row">
                            <div class="col-lg-6 d-none d-lg-block bg-login-image"
                                style="background-image:url('/sb_admin/img/undraw_posting_photo.svg');background-size:cover;background-position:center;">
                            </div>
                            <div class="col-lg-6">
                                <div class="p-5">
                                    <div class="text-center">
                                        <h1 class="h4 text-gray-900 mb-4">Selamat Datang!</h1>
                                    </div>

                                    <!-- Alert Errors -->
                                    <?php if ($errors->any()): ?>
                                    <div class="alert alert-danger">
                                        <ul class="mb-0">
                                            <?php foreach ($errors->all() as $error): ?>
                                            <li><?= e($error) ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                    <?php endif; ?>

                                    <form class="user" method="POST" action="<?= e(route('login.attempt')) ?>">
                                        <?= csrf_field() ?>
                                        <div class="form-group">
                                            <input type="text" class="form-control form-control-user" name="username"
                                                value="<?= e(old('username')) ?>" placeholder="Username" required
                                                autofocus>
                                        </div>
                                        <div class="form-group">
                                            <input type="password" class="form-control form-control-user"
                                                name="password" placeholder="Password" required>
                                        </div>
                                        <div class="form-group">
                                            <div class="custom-control custom-checkbox small">
                                                <input type="checkbox" class="custom-control-input" id="remember"
                                                    name="remember" value="1">
                                                <label class="custom-control-label" for="remember">Ingat saya</label>
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-user btn-block">
                                            Login
                                        </button>
                                    </form>
                                    <hr>
                                    <div class="text-center">
                                        <a class="small" href="#">Lupa Password?</a>
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
