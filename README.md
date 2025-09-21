# Absensi IoT – Laravel 12

Aplikasi Absensi berbasis web (role: Admin, Guru, Kepala Sekolah) dengan pengelolaan Kelas, Siswa, Perangkat (IoT), serta Rekap Absensi Harian.

## Fitur Utama
- Autentikasi (login via email + password)
- Manajemen Kelas, Siswa, dan Perangkat
- Pencatatan Absensi Harian
- Rekap Absensi (ringkasan per kelas dan ekspor)
- Halaman Dashboard berdasarkan peran (Admin/Guru/Kepala Sekolah)
- Halaman Profil Pengguna

## Teknologi
- PHP ^8.2, Laravel ^12
- Database: MySQL (digunakan pada instalasi ini)

## Prasyarat
- PHP 8.2+
- Composer 2+
- Server MySQL berjalan dan akses kredensial tersedia

## Instalasi (MySQL)
1) Masuk ke folder proyek dan install dependency PHP:
   - `composer install`
2) Salin environment dan generate key aplikasi:
   - Salin `.env.example` menjadi `.env`
   - Jalankan `php artisan key:generate`
3) Buat database MySQL baru (mis. `absensi_iot`).
4) Atur koneksi MySQL di file `.env` (contoh):

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=absensi_iot
DB_USERNAME=root
DB_PASSWORD=

# Disarankan untuk setup sesuai konfigurasi proyek ini
SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database
```

Catatan:
- Lingkungan Laragon default biasanya menggunakan DB_USERNAME `root` dan DB_PASSWORD kosong.
- Anda dapat menyesuaikan `APP_URL` di `.env` (mis. `APP_URL=http://127.0.0.1:8001`).

5) (Wajib, karena session memakai database) Generate tabel session dan migrasi + seeding:
   - `php artisan session:table`
   - `php artisan migrate --seed`
   Seeder akan memuat: `RoleUserSeeder`, `DemoSDSeeder`, `DemoSDN03Seeder`.
6) Jalankan server pengembangan:
   - `php artisan serve` (contoh: `php artisan serve --host=127.0.0.1 --port=8001`)

Catatan: Tidak perlu menjalankan `npm install` atau `npm run dev` karena aset front-end tidak digunakan dalam setup ini.

Akses aplikasi:
- Halaman Welcome: `/welcome`
- Login: `/login`
- Setelah login akan diarahkan ke `/dashboard` sesuai peran.

## Akun Demo (hasil seeder)
- Admin: `admin@example.com` / `password`
- Guru: `guru@example.com` / `password`
- Kepala Sekolah: `kepala@example.com` / `password`

Catatan: Autentikasi menggunakan email + password. Model `User` menyimpan password pada kolom `password_hash` dan telah di-mapping untuk proses login.

## Alur Singkat Penggunaan
- Admin/Kepala Sekolah: kelola Kelas, Siswa, Perangkat, dan Users; lihat rekap & ekspor.
- Guru: melihat Dashboard Guru, Kelas Saya, melakukan/meninjau Absensi Harian.
- Rekap tersedia di `/rekap-absensi` dan rekap per kelas di `/rekap-kelas`.

## Perintah Berguna
- Reset database dan seed ulang: `php artisan migrate:fresh --seed`

## Lisensi
Proyek ini berlisensi MIT.
