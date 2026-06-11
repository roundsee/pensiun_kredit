# Deploy Ke Shared Hosting Tanpa SSH

Panduan ini untuk project Laravel 11 di shared hosting yang tidak menyediakan akses SSH.

## Ringkasannya

Karena tidak ada SSH, semua proses berikut harus dilakukan di komputer lokal lalu hasilnya di-upload ke hosting:

- install dependency Composer
- build asset Vite
- optimasi autoload
- siapkan file `.env`
- siapkan database

## 1. Persiapan Lokal

Jalankan dari folder project:

```powershell
composer install --no-dev --optimize-autoloader
npm install
npm run build
```

Hasil penting yang harus ikut ter-upload:

- folder `vendor`
- folder `public/build`
- seluruh source project Laravel

## 2. Siapkan `.env` Untuk Hosting

Buat file `.env` dari `.env.example`, lalu sesuaikan minimal seperti ini:

```env
APP_NAME="System Kredit Pensiun"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domain-anda.com

WEB_DB_MAINTENANCE_ENABLED=false
WEB_DB_MAINTENANCE_TOKEN=isi_token_acak_panjang

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=nama_database
DB_USERNAME=user_database
DB_PASSWORD=password_database

SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync

FILESYSTEM_DISK=public
LOG_CHANNEL=stack
LOG_LEVEL=error
```

Catatan:

- `SESSION_DRIVER=file` menghindari kebutuhan tabel session.
- `CACHE_STORE=file` menghindari kebutuhan tabel cache.
- `QUEUE_CONNECTION=sync` cocok untuk shared hosting tanpa queue worker.
- `FILESYSTEM_DISK=public` dipakai jika file upload ingin diakses publik.

## 3. Database Tanpa SSH

Ada 2 cara yang umum:

1. Jika hosting memberi akses MySQL dari luar, jalankan migrasi dari komputer lokal ke database hosting dengan `.env` yang sudah mengarah ke database hosting.
2. Jika hosting hanya menyediakan phpMyAdmin, buat struktur database melalui phpMyAdmin dengan import SQL.

Cara paling praktis adalah opsi 1.

Langkah opsi 1:

```powershell
php artisan migrate --force
```

Jika Anda memang butuh data awal dari seeder:

```powershell
php artisan db:seed --force
```

Jika database hosting tidak bisa diakses dari luar, gunakan phpMyAdmin untuk membuat database dan import file SQL hasil export dari database lokal.

## 4. Struktur Upload Yang Disarankan

Struktur paling aman di shared hosting:

- letakkan isi project Laravel di folder di luar `public_html`
- letakkan isi folder `public` ke dalam `public_html`

Contoh:

- application root: `/home/username/system_kredit_pensiun`
- web root: `/home/username/public_html`

Dalam skenario ini:

- semua file Laravel selain isi `public` disimpan di `/home/username/system_kredit_pensiun`
- file dari folder `public` disimpan di `/home/username/public_html`

## 5. Penyesuaian `index.php` Jika Memakai `public_html`

Jika domain Anda tidak bisa diarahkan langsung ke folder `public`, buka file `public_html/index.php` lalu sesuaikan path berikut:

```php
require __DIR__.'/../system_kredit_pensiun/vendor/autoload.php';

(require_once __DIR__.'/../system_kredit_pensiun/bootstrap/app.php')
    ->handleRequest(Request::capture());
```

Nama folder `system_kredit_pensiun` harus disesuaikan dengan nama folder aplikasi Anda di hosting.

## 6. File `.htaccess`

Pastikan file `.htaccess` dari folder `public` ikut dipindahkan ke `public_html`.

Tanpa file ini, route Laravel tidak akan berjalan normal.

## 7. Folder Yang Wajib Bisa Ditulis

Pastikan folder berikut writable di hosting:

- `storage`
- `bootstrap/cache`

Jika panel hosting punya menu `Permissions`, biasanya gunakan:

- folder: `755` atau `775`
- file: `644`

Jika aplikasi gagal membuat cache atau log, cek permission dua folder tersebut lebih dulu.

## 8. Upload File

Upload melalui File Manager hosting atau FTP:

1. upload folder aplikasi Laravel ke luar `public_html`
2. upload isi folder `public` ke `public_html`
3. pastikan `.env` ada di root aplikasi Laravel
4. pastikan folder `vendor` dan `public/build` sudah ikut ter-upload

## 9. Storage Link Tanpa SSH

Normalnya Laravel memakai `php artisan storage:link`, tetapi itu tidak bisa dijalankan di hosting tanpa SSH.

Solusi manual:

1. buat folder `public_html/storage`
2. copy isi `storage/app/public` ke folder tersebut saat deploy

Jika file upload berubah-ubah setelah aplikasi berjalan, Anda perlu membuat symlink dari panel hosting bila fitur itu tersedia. Jika tidak ada, gunakan strategi copy file ke folder publik.

## 10. Checklist Error Yang Paling Umum

Jika muncul `500 Internal Server Error`, cek urutan ini:

1. versi PHP hosting harus `8.2` atau lebih tinggi
2. folder `vendor` sudah ter-upload
3. file `.env` sudah benar
4. `APP_KEY` sudah terisi
5. permission `storage` dan `bootstrap/cache` sudah benar
6. file `.htaccess` ada di `public_html`
7. `public/build` sudah ter-upload

Untuk membuat `APP_KEY` dari lokal:

```powershell
php artisan key:generate
```

## 11. Rekomendasi Deploy Pertama

Urutan deploy pertama yang paling aman:

1. set `.env` production
2. jalankan `composer install --no-dev --optimize-autoloader`
3. jalankan `npm install` dan `npm run build`
4. jalankan `php artisan key:generate`
5. jalankan `php artisan migrate --force` jika database hosting bisa diakses dari lokal
6. upload file aplikasi
7. pindahkan isi folder `public` ke `public_html`
8. sesuaikan `public_html/index.php` bila app root ada di luar `public_html`

## 12. Catatan Khusus Project Ini

Project ini memakai Vite, jadi folder `public/build` wajib ada di hosting.

Project ini juga memakai paket Composer yang tidak boleh diinstall di server bila tidak ada SSH, jadi folder `vendor` hasil install lokal wajib ikut di-upload.

## 13. Menjalankan Migration dan Seeder dari Web (Tanpa CLI)

Jika hosting benar-benar tidak menyediakan SSH/CLI, project ini menyediakan halaman web untuk menjalankan migration dan seeder.

1. Set di `.env`:

```env
WEB_DB_MAINTENANCE_ENABLED=true
WEB_DB_MAINTENANCE_TOKEN=isi_token_acak_panjang
```

2. Login ke aplikasi.
3. Buka menu `DB Tools` di navbar.
4. Masukkan token, lalu pilih:
    - `Jalankan Migrate`
    - `Jalankan Seeder`
    - `Migrate + Seeder`

Keamanan:

- Halaman ini hanya bisa diakses user login.
- Eksekusi command wajib token yang cocok dengan `WEB_DB_MAINTENANCE_TOKEN`.
- Setelah selesai, sangat disarankan set kembali:

```env
WEB_DB_MAINTENANCE_ENABLED=false
```