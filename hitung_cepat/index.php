<?php
session_start();

// Jika sudah login, redirect ke dashboard sesuai role
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: user/dashboard.php');
    }
    exit;
}

// Jika belum login, tampilkan landing page
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pusat Data Hitung Cepat - Kabupaten Kendal</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* Landing Page Styles */
        .landing-body {
            background: linear-gradient(135deg, #8B0000 0%, #DC143C 50%, #8B0000 100%);
            min-height: 100vh;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .landing-header {
            background: rgba(139, 0, 0, 0.95);
            padding: 20px 40px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 4px solid #FFD700;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        }

        .landing-header .logo-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .landing-header .logo-section img {
            width: 60px;
            height: 60px;
            border: 2px solid #FFD700;
            border-radius: 8px;
            padding: 3px;
            background: white;
        }

        .landing-header .logo-section h1 {
            color: #FFD700;
            font-size: 1.5rem;
            margin: 0;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }

        .landing-header .logo-section p {
            color: white;
            font-size: 0.9rem;
            margin: 0;
        }

        .landing-header .auth-buttons {
            display: flex;
            gap: 10px;
        }

        .btn-landing {
            padding: 12px 30px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 700;
            font-size: 1rem;
            transition: all 0.3s;
            border: 2px solid #FFD700;
        }

        .btn-landing-primary {
            background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
            color: #8B0000;
        }

        .btn-landing-primary:hover {
            background: linear-gradient(135deg, #FFA500 0%, #FFD700 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 215, 0, 0.4);
        }

        .btn-landing-secondary {
            background: transparent;
            color: #FFD700;
        }

        .btn-landing-secondary:hover {
            background: rgba(255, 215, 0, 0.2);
            transform: translateY(-2px);
        }

        .hero-section {
            text-align: center;
            padding: 80px 20px;
            color: white;
        }

        .hero-section h2 {
            font-size: 3rem;
            margin-bottom: 20px;
            text-shadow: 3px 3px 6px rgba(0,0,0,0.5);
            color: #FFD700;
        }

        .hero-section p {
            font-size: 1.3rem;
            max-width: 800px;
            margin: 0 auto 40px;
            line-height: 1.8;
        }

        .hero-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .hero-buttons .btn-landing {
            padding: 15px 40px;
            font-size: 1.1rem;
        }

        .features-section {
            background: white;
            padding: 60px 20px;
        }

        .features-section h2 {
            text-align: center;
            color: #8B0000;
            font-size: 2rem;
            margin-bottom: 40px;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .feature-card {
            background: linear-gradient(135deg, #FFF8DC 0%, white 100%);
            padding: 30px;
            border-radius: 15px;
            border: 2px solid #FFD700;
            text-align: center;
            transition: all 0.3s;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(139, 0, 0, 0.2);
        }

        .feature-card .icon {
            font-size: 3rem;
            margin-bottom: 15px;
        }

        .feature-card h3 {
            color: #8B0000;
            margin-bottom: 10px;
            font-size: 1.3rem;
        }

        .feature-card p {
            color: #666;
            line-height: 1.6;
        }

        .info-section {
            background: linear-gradient(135deg, #FFF8DC 0%, #FFD700 100%);
            padding: 50px 20px;
            text-align: center;
        }

        .info-section h2 {
            color: #8B0000;
            font-size: 1.8rem;
            margin-bottom: 20px;
        }

        .info-section p {
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            font-size: 1.1rem;
            line-height: 1.8;
        }

        .landing-footer {
            background: #8B0000;
            color: white;
            text-align: center;
            padding: 30px 20px;
            border-top: 4px solid #FFD700;
        }

        .landing-footer .footer-brand {
            color: #FFD700;
            font-weight: bold;
            font-size: 1.2rem;
            margin-bottom: 10px;
        }

        .landing-footer p {
            margin: 5px 0;
            font-size: 0.95rem;
        }

        @media (max-width: 768px) {
            .landing-header {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }

            .hero-section h2 {
                font-size: 2rem;
            }

            .hero-section p {
                font-size: 1rem;
            }

            .hero-buttons {
                flex-direction: column;
                align-items: center;
            }
        }
    </style>
</head>
<body class="landing-body">

    <!-- Header -->
    <header class="landing-header">
        <div class="logo-section">
            <img src="assets/images/logo.png" alt="Logo Bawaslu" onerror="this.src='https://via.placeholder.com/60x60.png?text=Bawaslu'">
            <div>
                <h1>Pusat Data Hitung Cepat</h1>
                <p>Kabupaten Kendal</p>
            </div>
        </div>
        <div class="auth-buttons">
            <a href="login.php" class="btn-landing btn-landing-secondary">Login</a>
            <a href="register.php" class="btn-landing btn-landing-primary">Daftar</a>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero-section">
        <h2>Sistem Rekapitulasi Suara Terpercaya</h2>
        <p>
            Platform resmi untuk pengumpulan, verifikasi, dan perhitungan suara pemilihan 
            di Kabupaten Kendal. Transparan, akurat, dan dapat dipertanggungjawabkan.
        </p>
        <div class="hero-buttons">
            <a href="register.php" class="btn-landing btn-landing-primary"> Daftar Sekarang</a>
            <a href="info.php" class="btn-landing btn-landing-secondary">ℹ️ Pelajari Lebih Lanjut</a>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section">
        <h2>Fitur Unggulan</h2>
        <div class="features-grid">
            <div class="feature-card">
                <div class="icon">🗳️</div>
                <h3>Pengiriman Suara Aman</h3>
                <p>Kirim suara Anda dengan aman melalui sistem terenkripsi dengan validasi CAPTCHA dan verifikasi admin.</p>
            </div>
            <div class="feature-card">
                <div class="icon">📊</div>
                <h3>Hasil Real-Time</h3>
                <p>Pantau perkembangan hasil perhitungan suara secara langsung dengan visualisasi diagram yang interaktif.</p>
            </div>
            <div class="feature-card">
                <div class="icon">🔒</div>
                <h3>Keamanan Terjamin</h3>
                <p>Satu akun satu suara, data pribadi terlindungi, dan semua transaksi tercatat dengan audit trail lengkap.</p>
            </div>
            <div class="feature-card">
                <div class="icon">📱</div>
                <h3>Akses Mudah</h3>
                <p>Akses dari perangkat apapun - desktop, tablet, atau smartphone. Tampilan responsif dan user-friendly.</p>
            </div>
            <div class="feature-card">
                <div class="icon">✅</div>
                <h3>Verifikasi Admin</h3>
                <p>Setiap suara diverifikasi oleh admin dengan memeriksa bukti foto sebelum masuk perhitungan resmi.</p>
            </div>
            <div class="feature-card">
                <div class="icon">💬</div>
                <h3>Layanan Keluhan</h3>
                <p>Sampaikan keluhan atau pertanyaan Anda melalui form khusus yang akan ditindaklanjuti oleh tim admin.</p>
            </div>
        </div>
    </section>

    <!-- Info Section -->
    <section class="info-section">
        <h2>Tentang Sistem</h2>
        <p>
            Pusat Data Hitung Cepat Kabupaten Kendal adalah platform digital yang dirancang untuk 
            mendukung transparansi dan akurasi dalam proses rekapitulasi suara pemilihan. 
            Sistem ini mencakup berbagai tingkat pemilihan mulai dari Eksekutif (Presiden, Gubernur, Bupati) 
            hingga Legislatif (DPR RI, DPD RI, DPRD Provinsi, DPRD Kabupaten).
        </p>
    </section>

    <!-- Footer -->
    <footer class="landing-footer">
        <p class="footer-brand">Pusat Data Hitung Cepat - Kabupaten Kendal</p>
        <p>&copy; 2026 Bawaslu Kabupaten Kendal. All rights reserved.</p>
        <p style="margin-top: 10px; font-size: 0.85rem; opacity: 0.8;">
            Dikembangkan untuk mendukung demokrasi yang transparan dan akuntabel
        </p>
    </footer>

</body>
</html>