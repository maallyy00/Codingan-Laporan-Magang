# Pusat Data Hitung Cepat - Kabupaten Kendal

Sistem rekap suara sementara untuk berbagai tingkat pemilihan (Eksekutif & Legislatif) fokus wilayah Kabupaten Kendal.

## Teknologi
- Frontend: HTML, CSS, Vanilla JS, Chart.js
- Backend: PHP Native (PDO)
- Database: MySQL
- Server: XAMPP

## Cara Instalasi & Menjalankan

1. **Clone / Copy Project**
   Salin folder project ini ke dalam direktori `htdocs` XAMPP Anda.
   Contoh: `C:\xampp\htdocs\hitung_cepat`

2. **Setup Database**
   - Jalankan XAMPP Control Panel, start **Apache** dan **MySQL**.
   - Buka phpMyAdmin: `http://localhost/phpmyadmin`
   - Buat database baru atau langsung import file `database.sql` yang ada di root folder project.
   - File `database.sql` sudah berisi struktur tabel, data kecamatan Kendal, data tingkat pemilihan, dan 1 akun admin default.

3. **Konfigurasi Koneksi (Opsional)**
   Jika kredensial MySQL Anda berbeda dari default XAMPP, edit file `config/database.php`:
   ```php
   $user = 'root'; // Ganti dengan username MySQL Anda
   $pass = '';     // Ganti dengan password MySQL Anda