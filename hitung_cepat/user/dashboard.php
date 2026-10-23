<?php
require '../config/database.php';
require '../includes/functions.php';
check_login('user');
$uid = $_SESSION['user_id'];
// Cek apakah akun sudah approved
$stmt = $pdo->prepare("SELECT status FROM users WHERE id = ?");
$stmt->execute([$uid]);
$user_status = $stmt->fetchColumn();
if ($user_status !== 'approved') {
die('<div style="text-align:center; padding: 50px; font-family: Arial; background: #FFF8DC; min-height: 100vh;">
<h2 style="color: #8B0000;"> Akun Masih Dalam Verifikasi</h2>
<p>Akun Anda sedang diverifikasi oleh admin. Silakan tunggu.</p>
<a href="../logout.php" style="color: #d4af37;">Logout</a>
</div>');
}
// Riwayat submission user
$stmt = $pdo->prepare("SELECT v.id, ec.name as category, v.total_suara_sah, v.suara_tidak_sah,
v.total_seluruh, v.status, v.created_at
FROM votes v
JOIN election_categories ec ON v.category_id = ec.id
WHERE v.user_id = ? ORDER BY v.created_at DESC");
$stmt->execute([$uid]);
$my_votes = $stmt->fetchAll();
// Keluhan user
$stmt = $pdo->prepare("SELECT * FROM complaints WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$uid]);
$my_complaints = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard Petugas TPS</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<header>
<img src="../assets/images/logo.png" alt="Logo"
onerror="this.onerror=null; this.src='https://upload.wikimedia.org/wikipedia/commons/thumb/0/07/Logo_Bawaslu_RI.svg/200px-Logo_Bawaslu_RI.svg.png';">
<h1>Dashboard Petugas TPS</h1>
<span class="user-info"><?= e($_SESSION['name']) ?> - <?= e($_SESSION['nomor_tps'] ?? '') ?></span>
<a href="../logout.php" class="btn-logout">Logout</a>
</header>

<!-- CAROUSEL BANNER SECTION - HANYA GAMBAR -->
<div class="carousel-wrapper">
<div class="carousel-container" id="bawasluCarousel">
<div class="carousel-track">
<!-- Banner 1 -->
<div class="carousel-slide active">
<img src="../assets/images/banners/banner1.jpg" alt="Ayo Awasi Pemilu Bersama Bawaslu"
onerror="this.parentElement.style.background='linear-gradient(135deg, #8B0000, #DC143C)'; this.style.display='none';">
</div>
<!-- Banner 2 -->
<div class="carousel-slide">
<img src="../assets/images/banners/banner2.jpg" alt="Cegah Pelanggaran"
onerror="this.parentElement.style.background='linear-gradient(135deg, #DC143C, #8B0000)'; this.style.display='none';">
</div>
<!-- Banner 3 -->
<div class="carousel-slide">
<img src="../assets/images/banners/banner3.jpg" alt="Laporkan Pelanggaran"
onerror="this.parentElement.style.background='linear-gradient(135deg, #8B0000, #FFD700)'; this.style.display='none';">
</div>
<!-- Banner 4 -->
<div class="carousel-slide">
<img src="../assets/images/banners/banner4.jpg" alt="Tolak Politik Uang"
onerror="this.parentElement.style.background='linear-gradient(135deg, #FFD700, #8B0000)'; this.style.display='none';">
</div>
<!-- Banner 5 -->
<div class="carousel-slide">
<img src="../assets/images/banners/banner5.jpg" alt="Mata dan Telinga Demokrasi"
onerror="this.parentElement.style.background='linear-gradient(135deg, #8B0000, #DC143C)'; this.style.display='none';">
</div>
</div>
<!-- Tombol Navigasi Kiri/Kanan -->
<button class="carousel-btn prev-btn" onclick="moveSlide(-1)">&#10094;</button>
<button class="carousel-btn next-btn" onclick="moveSlide(1)">&#10095;</button>
<!-- Indikator Dots -->
<div class="carousel-dots" id="carouselDots"></div>
</div>
</div>
<!-- END CAROUSEL BANNER -->

<main class="container">
<!-- Info TPS -->
<div class="form-card" style="background: linear-gradient(135deg, #FFF8DC, white);">
<h3>📍 Identitas TPS Anda</h3>
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
<div><strong>Nomor TPS:</strong><br><?= e($_SESSION['nomor_tps'] ?? '-') ?></div>
<div><strong>Desa/Kelurahan:</strong><br><?= e($_SESSION['desa'] ?? '-') ?></div>
<div><strong>Kecamatan:</strong><br><?= e($_SESSION['kecamatan'] ?? '-') ?></div>
<div><strong>Kabupaten:</strong><br><?= e($_SESSION['kabupaten'] ?? 'Kendal') ?></div>
<div><strong>Nama Petugas:</strong><br><?= e($_SESSION['name']) ?></div>
<div><strong>No. Telepon:</strong><br><?= e($_SESSION['phone'] ?? '-') ?></div>
</div>
</div>

<h2 class="section-title">Menu Utama</h2>
<div class="menu-grid">
<a href="submit_vote.php" class="menu-card">
<span class="icon">📤</span>
<span>Kirim Hasil Perhitungan</span>
</a>
<a href="submit_complaint.php" class="menu-card">
<span class="icon">🗣️</span>
<span>Kirim Keluhan</span>
</a>
<a href="../results.php" class="menu-card">
<span class="icon">📊</span>
<span>Lihat Hasil Daerah Saya</span>
</a>
<a href="../info.php" class="menu-card">
<span class="icon">ℹ️</span>
<span>Tata Cara</span>
</a>
</div>

<h2 class="section-title">Riwayat Pengiriman Hasil</h2>
<?php if (empty($my_votes)): ?>
<div class="form-card">
<p style="text-align: center; color: #666;">Anda belum mengirim hasil perhitungan.</p>
</div>
<?php else: ?>
<table class="data-table">
<thead>
<tr>
<th>Tanggal</th>
<th>Jenis Pemilihan</th>
<th>Total Sah</th>
<th>Tidak Sah</th>
<th>Total</th>
<th>Status</th>
</tr>
</thead>
<tbody>
<?php foreach ($my_votes as $v): ?>
<tr>
<td><?= date('d/m/Y H:i', strtotime($v['created_at'])) ?></td>
<td><?= e($v['category']) ?></td>
<td><?= $v['total_suara_sah'] ?></td>
<td><?= $v['suara_tidak_sah'] ?></td>
<td><?= $v['total_seluruh'] ?></td>
<td><span class="badge badge-<?= $v['status'] ?>"><?= ucfirst(str_replace('_', ' ', $v['status'])) ?></span></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php endif; ?>
</main>

<footer>
<p class="footer-brand">Pusat Data Hitung Cepat - Kabupaten Kendal</p>
<p>&copy; 2026 Bawaslu Kabupaten Kendal. All rights reserved.</p>
</footer>

<!-- JAVASCRIPT CAROUSEL AUTO-SLIDE -->
<script>
// === LOGIC CAROUSEL BANNER ===
const slides = document.querySelectorAll('.carousel-slide');
const dotsContainer = document.getElementById('carouselDots');
let currentSlide = 0;
let slideInterval;

// Buat dots secara dinamis
slides.forEach((_, index) => {
    const dot = document.createElement('span');
    dot.classList.add('dot');
    if (index === 0) dot.classList.add('active');
    dot.onclick = () => goToSlide(index);
    dotsContainer.appendChild(dot);
});

const dots = document.querySelectorAll('.dot');

// Fungsi untuk update tampilan slide
function updateSlide() {
    slides.forEach(slide => slide.classList.remove('active'));
    dots.forEach(dot => dot.classList.remove('active'));
    
    slides[currentSlide].classList.add('active');
    dots[currentSlide].classList.add('active');
}

// Fungsi untuk geser slide (kiri/kanan)
function moveSlide(direction) {
    currentSlide = (currentSlide + direction + slides.length) % slides.length;
    updateSlide();
    resetInterval();
}

// Fungsi untuk lompat ke slide tertentu (klik dot)
function goToSlide(index) {
    currentSlide = index;
    updateSlide();
    resetInterval();
}

// Fungsi untuk mulai auto-slide setiap 5 detik
function startInterval() {
    slideInterval = setInterval(() => {
        moveSlide(1);
    }, 5000); // 5000ms = 5 detik
}

// Fungsi untuk reset timer (saat user klik manual)
function resetInterval() {
    clearInterval(slideInterval);
    startInterval();
}

// Pause auto-slide saat mouse hover di banner
const carousel = document.getElementById('bawasluCarousel');
carousel.addEventListener('mouseenter', () => clearInterval(slideInterval));
carousel.addEventListener('mouseleave', startInterval);

// Mulai auto-slide saat halaman dimuat
startInterval();
</script>
</body>
</html>