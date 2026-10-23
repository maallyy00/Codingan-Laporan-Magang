<?php
require 'config/database.php';
require 'includes/functions.php';
check_login();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tata Cara Pemilihan</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <img src="assets/images/logo.png" alt="Logo Bawaslu" 
             onerror="this.onerror=null; this.src='https://upload.wikimedia.org/wikipedia/commons/thumb/0/07/Logo_Bawaslu_RI.svg/200px-Logo_Bawaslu_RI.svg.png';">
        <h1>Tata Cara Pemilihan</h1>
        <a href="<?= $_SESSION['role']=='admin'?'admin/dashboard.php':'user/dashboard.php' ?>" class="btn-back">Kembali</a>
    </header>
    <main class="container">
        <div class="info-card fade-in">
            <h2>📋 Panduan Pengiriman Suara</h2>
            <ol class="step-list">
                <li><strong>Registrasi & Login</strong><br>Buat akun dengan data diri yang valid. Gunakan nomor telepon aktif.</li>
                <li><strong>Pilih Jenis Pemilihan</strong><br>Pilih tingkat (Eksekutif/Legislatif) dan jenis pemilihan yang diinginkan.</li>
                <li><strong>Isi Data Wilayah</strong><br>Pilih Kecamatan dan masukkan nama Desa sesuai domisili Anda.</li>
                <li><strong>Pilih Kandidat</strong><br>Pilih kandidat/personil yang menjadi pilihan Anda.</li>
                <li><strong>Unggah Bukti</strong><br>Upload foto bukti pilihan Anda (Coblosan surat suara, form C1, dsb).</li>
                <li><strong>Konfirmasi</strong><br>Periksa kembali seluruh data. Pastikan tidak ada kesalahan sebelum mengirim.</li>
                <li><strong>Tunggu Verifikasi</strong><br>Admin akan memverifikasi bukti Anda. Status akan berubah menjadi "Disetujui" atau "Ditolak".</li>
            </ol>
        </div>
        <div class="info-card fade-in">
            <h2>⚠️ Ketentuan Penting</h2>
            <ul>
                <li>Satu akun hanya dapat mengirim <strong>satu suara</strong> per jenis pemilihan.</li>
                <li>Data yang sudah dikirim <strong>tidak dapat diubah</strong> atau ditarik kembali.</li>
                <li>Pastikan foto bukti <strong>jelas dan terbaca</strong>.</li>
                <li>Data pribadi Anda bersifat <strong>rahasia</strong> dan hanya dapat dilihat oleh admin.</li>
                <li>Jika mengalami kendala, gunakan menu <strong>"Kirim Keluhan"</strong>.</li>
            </ul>
        </div>
        <div class="info-card fade-in">
            <h2> Kontak Pengaduan</h2>
            <p>Untuk pertanyaan lebih lanjut, silakan kirim keluhan melalui dashboard Anda atau hubungi:</p>
            <p><strong>Pusat Data Hitung Cepat - Kabupaten Kendal</strong><br>
            Email: pusatdata@bawaslu-kendal.go.id<br>
            Hotline: (0294) 123-4567</p>
        </div>
    </main>
</body>
</html>