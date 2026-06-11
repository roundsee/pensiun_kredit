# File Siap Pakai Untuk Shared Hosting

Folder ini berisi template deploy untuk shared hosting tanpa SSH.

## Isi Folder

- `public_html/index.php`: contoh front controller jika root aplikasi Laravel diletakkan di luar `public_html`

## Cara Pakai

1. upload seluruh project Laravel ke folder di luar `public_html`
2. copy isi folder `public` ke `public_html`
3. timpa `public_html/index.php` dengan file contoh dari folder ini
4. sesuaikan nama folder `system_kredit_pensiun` di dalam `index.php` bila nama folder aplikasi di hosting berbeda
5. copy `.env.shared-hosting.example` menjadi `.env`, lalu isi kredensial database dan mail

## Catatan

File `.htaccess` tetap ambil dari folder `public` project utama.