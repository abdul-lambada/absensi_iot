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

## Pengujian (Tests)
Panduan menjalankan test otomatis (Feature + Unit).

1) Siapkan environment khusus testing (.env.testing) agar database dev/prod tidak tersentuh saat test:
   - Opsi A (Direkomendasikan – SQLite in-memory, cepat dan aman):
     ```env
     APP_ENV=testing
     APP_DEBUG=true
     DB_CONNECTION=sqlite
     DB_DATABASE=:memory:
     DB_FOREIGN_KEYS=true
     SESSION_DRIVER=array
     CACHE_STORE=array
     QUEUE_CONNECTION=sync
     ```
   - Opsi B (MySQL terpisah): pastikan menggunakan database yang berbeda dari pengembangan, mis. `absensi_iot_test`.
     ```env
     APP_ENV=testing
     APP_DEBUG=true
     DB_CONNECTION=mysql
     DB_HOST=127.0.0.1
     DB_PORT=3306
     DB_DATABASE=absensi_iot_test
     DB_USERNAME=root
     DB_PASSWORD=

     # Untuk menghindari kebutuhan tabel session saat test
     SESSION_DRIVER=array
     CACHE_STORE=array
     QUEUE_CONNECTION=sync
     ```
   Catatan penting:
   - Test menggunakan trait RefreshDatabase yang akan melakukan migrate:fresh pada DB testing. Jangan arahkan ke DB pengembangan/produksi karena seluruh tabel dapat dijatuhkan (drop) saat test berjalan.

2) Menjalankan seluruh test suite (Feature + Unit):
   - `php artisan test`

3) Menjalankan hanya Feature tests:
   - `php artisan test --testsuite=Feature`

4) Menjalankan hanya Unit tests:
   - `php artisan test --testsuite=Unit`

Tips:
- Test tidak memerlukan seeding manual; masing-masing skenario membuat datanya sendiri via factory.
- Pada skenario login di test, middleware CSRF dinonaktifkan khusus untuk pengujian. Di aplikasi nyata, CSRF tetap aktif.

## Integrasi IoT (Arduino/ESP32)
API untuk perangkat IoT tersedia tanpa sesi login dan dilindungi oleh api_key per perangkat serta rate limit.

- Endpoint: `POST /api/v1/absensi`
- Header: `Content-Type: application/json`
- Rate limit: 60 request/menit per IP (middleware throttle)
- CSRF: tidak berlaku pada endpoint ini

Request JSON:
```json
{
  "device_uid": "ESP32-ABC123",   // UID unik perangkat (harus sama seperti yang terdaftar)
  "api_key": "RAHASIA_API_KEY",   // API key milik perangkat (rahasia)
  "finger_id": 11,                  // ID sidik jari siswa yang terbaca di perangkat
  "event": "masuk"                 // nilai: "masuk" atau "pulang"
}
```

Contoh respons sukses:
```json
{
  "status": "ok",
  "event": "masuk",
  "waktu": "2025-09-21 06:30:00",
  "siswa": { "id": 123, "nama": "Nama Siswa" },
  "perangkat": { "id": 5, "uid": "ESP32-ABC123" },
  "status_kehadiran": "hadir" // atau "terlambat" bila > 07:00 untuk event masuk
}
```

Kemungkinan respons error:
- 401: `{ "status": "error", "message": "Perangkat tidak dikenal atau tidak aktif" }`
- 404: `{ "status": "error", "message": "Siswa tidak ditemukan untuk finger_id=..." }`
- 422: `{ "status": "error", "message": "<pesan validasi>" }`
- 500: `{ "status": "error", "message": "Terjadi kesalahan server" }`

Langkah pairing perangkat:
1) Buat data Perangkat di menu Admin dengan kolom berikut:
   - `device_uid` (mis. "ESP32-ABC123")
   - `api_key` (string rahasia)
   - `status_perangkat` harus "aktif"
2) Pastikan setiap Siswa memiliki `finger_id` unik yang sama dengan ID di sensor sidik jari pada perangkat.
3) Programkan perangkat agar mengirim request ke endpoint di atas saat jari dikenali (event masuk/pulang).

Quick test via curl:
```bash
curl -X POST "http://127.0.0.1:8001/api/v1/absensi" \
  -H "Content-Type: application/json" \
  -d '{
    "device_uid": "ESP32-ABC123",
    "api_key": "RAHASIA_API_KEY",
    "finger_id": 11,
    "event": "masuk"
  }'
```

Contoh kode Arduino (ESP32 + WiFi + HTTPClient):
```cpp
#include <WiFi.h>
#include <HTTPClient.h>

const char* ssid     = "WIFI_SSID";
const char* password = "WIFI_PASSWORD";

const char* url = "http://127.0.0.1:8001/api/v1/absensi"; // ganti dengan URL server Anda
String deviceUid = "ESP32-ABC123";
String apiKey    = "RAHASIA_API_KEY";

void setup() {
  Serial.begin(115200);
  WiFi.begin(ssid, password);
  while (WiFi.status() != WL_CONNECTED) { delay(500); Serial.print("."); }
  Serial.println("\nWiFi connected");
}

void sendAbsensi(int fingerId, const char* event) {
  if (WiFi.status() == WL_CONNECTED) {
    HTTPClient http;
    http.begin(url);
    http.addHeader("Content-Type", "application/json");
    String payload = String("{\"device_uid\":\"") + deviceUid + "\"," \
                    + "\"api_key\":\"" + apiKey + "\"," \
                    + "\"finger_id\":" + fingerId + "," \
                    + "\"event\":\"" + event + "\"}";
    int code = http.POST(payload);
    String resp = http.getString();
    Serial.printf("HTTP %d: %s\n", code, resp.c_str());
    http.end();
  }
}

void loop() {
  // Contoh simulasi: kirim event masuk untuk finger_id 11
  sendAbsensi(11, "masuk");
  delay(10000);
}
```

### Diagram urutan (sequence)
```
Perangkat (ESP32)           Server Laravel
      |                           |
      |  Scan sidik jari         |
      |-------------------------->|
      |  Cari finger_id lokal     |
      |                           |
      |  POST /api/v1/absensi     |
      |  {device_uid, api_key,    |
      |   finger_id, event}       |
      |-------------------------->|
      |                           | Validasi payload
      |                           | Cek Perangkat (uid+api_key)
      |                           | Cek Siswa by finger_id
      |                           | Tentukan status kehadiran
      |                           | Simpan AbsensiHarian
      |                           | Bangun respons JSON
      |        200 OK + JSON      |
      |<--------------------------|
      |  Tampilkan hasil di Serial|
      |                           |
```

### Contoh pembacaan fingerprint (R307/AS608) – pseudo-code
Contoh alur untuk sensor TTL (mis. R307/AS608) menggunakan library pembaca sidik jari yang umum di Arduino (pseudo-code, ringkas agar mudah diadaptasi):
```cpp
#include <WiFi.h>
#include <HTTPClient.h>
// Termasuk library sensor sidik jari sesuai modul Anda, mis. Adafruit_Fingerprint
// #include <Adafruit_Fingerprint.h>

// HardwareSerial & Sensor setup (contoh pada ESP32)
// HardwareSerial mySerial(2); // RX2/TX2
// Adafruit_Fingerprint finger = Adafruit_Fingerprint(&mySerial);

const char* ssid = "WIFI_SSID";
const char* pass = "WIFI_PASSWORD";
const char* url  = "http://server-anda/api/v1/absensi"; // ganti sesuai server

String deviceUid = "ESP32-ABC123";
String apiKey    = "RAHASIA_API_KEY";

void setup() {
  Serial.begin(115200);
  // mySerial.begin(57600, SERIAL_8N1, RX_PIN, TX_PIN);
  // finger.begin(57600);
  // if (finger.verifyPassword()) { Serial.println("Sensor OK"); } else { Serial.println("Sensor gagal"); }

  WiFi.begin(ssid, pass);
  while (WiFi.status() != WL_CONNECTED) { delay(500); Serial.print("."); }
}

int readFingerId() {
  // if (finger.getImage() != FINGERPRINT_OK) return -1;
  // if (finger.image2Tz(1) != FINGERPRINT_OK) return -1;
  // if (finger.fingerFastSearch() != FINGERPRINT_OK) return -1;
  // return finger.fingerID;  // ID hasil pencarian di database sensor
  return -1; // placeholder bila belum dihubungkan ke sensor nyata
}

void postAbsensi(int fingerId, const char* event) {
  if (WiFi.status() != WL_CONNECTED) return;
  HTTPClient http;
  http.begin(url);
  http.addHeader("Content-Type", "application/json");
  String payload = String("{\"device_uid\":\"") + deviceUid + "\"," \
                   + "\"api_key\":\"" + apiKey + "\"," \
                   + "\"finger_id\":" + fingerId + "," \
                   + "\"event\":\"" + event + "\"}";
  int code = http.POST(payload);
  Serial.printf("HTTP %d\n", code);
  Serial.println(http.getString());
  http.end();
}

void loop() {
  int fid = readFingerId();
  if (fid >= 0) {
    postAbsensi(fid, "masuk");
  }
  delay(500);
}
```
Tips:
- Pastikan template sidik jari sudah terdaftar di sensor dan memiliki ID (finger_id) yang sama dengan data Siswa di aplikasi.
- Sesuaikan baud rate, pin RX/TX, dan library sesuai modul sensor Anda.

### Keamanan lanjutan (opsional): Signature HMAC
Untuk mengeraskan keamanan selain api_key, Anda bisa menambahkan signature HMAC agar payload tidak mudah dipalsukan.

Skema yang disarankan:
- Header tambahan:
  - X-Timestamp: epoch detik (mis. 1737510000)
  - X-Signature: HMAC-SHA256 dari string: `${timestamp}.${device_uid}.${finger_id}.${event}` menggunakan kunci api_key perangkat
- Server menolak request bila timestamp > skew (mis. 5 menit) atau signature tidak cocok.

Contoh perhitungan di perangkat (pseudo-code):
```cpp
#include <mbedtls/md.h>
String makeSignature(String ts, String deviceUid, int fingerId, String event, String apiKey) {
  String msg = ts + "." + deviceUid + "." + String(fingerId) + "." + event;
  // Hitung HMAC-SHA256(msg, apiKey) -> hex
  // Kembalikan string hex lowercase
  return "...hmac_hex..."; // implementasi sesuai library HMAC yang Anda pakai
}
```
Contoh verifikasi di server (Laravel, konsep):
- Ambil `X-Timestamp` dan `X-Signature` dari header
- Cari perangkat by `device_uid`, ambil `api_key`
- Bangun string `${timestamp}.${device_uid}.${finger_id}.${event}` lalu hitung `hash_hmac('sha256', $msg, $apiKey)` dan bandingkan dengan signature header (case-insensitive)
- Tolak jika selisih waktu > 300 detik atau signature mismatch

Catatan:
- Simpan api_key aman di perangkat (hindari print ke Serial dalam produksi)
- Tetap gunakan throttle rate limit di server
- Pertimbangkan IP allowlist atau VPN jika lingkungan memungkinkan

### Deploy endpoint ke Internet (HTTPS)
Agar perangkat di luar jaringan lokal dapat mengakses API dengan aman:
1) Siapkan domain dan DNS yang mengarah ke server Anda
2) Pasang sertifikat TLS (Let's Encrypt) dan jalankan server di HTTPS (port 443)
3) Jalankan Laravel di balik reverse proxy (Nginx/Apache):
   - Proxy pass ke PHP-FPM/Artisan serve atau gunakan setup production (Nginx+PHP-FPM)
   - Set `APP_URL=https://domain-anda` dan konfigurasi `TrustedProxy` bila di belakang proxy
4) Buka firewall hanya port yang diperlukan (443/HTTPS)
5) Ubah URL pada firmware perangkat ke `https://domain-anda/api/v1/absensi`
6) Jika memakai NAT rumahan: lakukan port forwarding dan gunakan DDNS
7) Monitor log dan atur rate limit yang sesuai beban perangkat

## Lisensi
Proyek ini berlisensi MIT.
