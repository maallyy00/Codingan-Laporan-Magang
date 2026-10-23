<?php
require '../config/database.php';
require '../includes/functions.php';
check_login('admin');

$total_users = $pdo->query("SELECT COUNT(*) FROM users WHERE role='user'")->fetchColumn();
$pending_users = $pdo->query("SELECT COUNT(*) FROM users WHERE role='user' AND status='pending'")->fetchColumn();
$total_votes = $pdo->query("SELECT COUNT(*) FROM votes")->fetchColumn();
$pending = $pdo->query("SELECT COUNT(*) FROM votes WHERE status='menunggu_verifikasi'")->fetchColumn();
$approved = $pdo->query("SELECT COUNT(*) FROM votes WHERE status='disetujui'")->fetchColumn();
$rejected = $pdo->query("SELECT COUNT(*) FROM votes WHERE status='ditolak'")->fetchColumn();
$complaints = $pdo->query("SELECT COUNT(*) FROM complaints WHERE status != 'selesai'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
/* === STATISTIK SISTEM - MODERN & COMPACT === */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 15px;
    margin: 20px 0 30px 0;
}

.stat-card {
    background: white;
    border-radius: 10px;
    padding: 18px 15px;
    border: 2px solid #FFD700;
    box-shadow: 0 2px 10px rgba(139, 0, 0, 0.08);
    transition: all 0.3s;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: linear-gradient(135deg, #8B0000, #DC143C);
}

.stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(139, 0, 0, 0.15);
}

.stat-card .stat-icon {
    font-size: 1.8rem;
    margin-bottom: 8px;
    opacity: 0.9;
}

.stat-card .stat-value {
    font-size: 1.8rem;
    font-weight: 800;
    color: #8B0000;
    margin-bottom: 3px;
    line-height: 1.2;
}

.stat-card .stat-label {
    font-size: 0.8rem;
    color: #666;
    font-weight: 600;
    line-height: 1.3;
}

/* Color variants */
.stat-card.highlight {
    background: linear-gradient(135deg, #FFF8DC, #FFEDB0);
    border-color: #8B0000;
}

.stat-card.highlight .stat-value {
    color: #DC143C;
}

.stat-card.warning {
    background: linear-gradient(135deg, #FFF0F0, #FFE0E0);
    border-color: #DC143C;
}

.stat-card.warning .stat-value {
    color: #DC143C;
}

.stat-card.success {
    background: linear-gradient(135deg, #F0FFF0, #E0FFE0);
    border-color: #28a745;
}

.stat-card.success .stat-value {
    color: #28a745;
}

/* Responsive */
@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
    }
    
    .stat-card {
        padding: 15px 10px;
    }
    
    .stat-card .stat-value {
        font-size: 1.5rem;
    }
    
    .stat-card .stat-icon {
        font-size: 1.5rem;
    }
    
    .stat-card .stat-label {
        font-size: 0.75rem;
    }
}

@media (max-width: 480px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 8px;
    }
    
    .stat-card {
        padding: 12px 8px;
    }
    
    .stat-card .stat-value {
        font-size: 1.3rem;
    }
}
</style>

</head>
<body>
    <header>
        <img src="../assets/images/logo.png" alt="Logo Bawaslu" 
             onerror="this.onerror=null; this.src='https://upload.wikimedia.org/wikipedia/commons/thumb/0/07/Logo_Bawaslu_RI.svg/200px-Logo_Bawaslu_RI.svg.png';">
        <h1>Dashboard Admin</h1>
        <span class="user-info"><?= e($_SESSION['name']) ?></span>
        <a href="../logout.php" class="btn-logout">Logout</a>
    </header>

    <main class="container">
     <h2 class="section-title">📊 Statistik Sistem</h2>
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">👥</div>
        <div class="stat-value"><?= $total_users ?></div>
        <div class="stat-label">Total Petugas TPS</div>
    </div>
    
    <div class="stat-card warning">
        <div class="stat-icon">⏳</div>
        <div class="stat-value"><?= $pending_users ?></div>
        <div class="stat-label">Menunggu Approval</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">📝</div>
        <div class="stat-value"><?= $total_votes ?></div>
        <div class="stat-label">Total Submission</div>
    </div>
    
    <div class="stat-card highlight">
        <div class="stat-icon"></div>
        <div class="stat-value"><?= $pending ?></div>
        <div class="stat-label">Menunggu Verifikasi</div>
    </div>
    
    <div class="stat-card success">
        <div class="stat-icon">✅</div>
        <div class="stat-value"><?= $approved ?></div>
        <div class="stat-label">Disetujui</div>
    </div>
    
    <div class="stat-card warning">
        <div class="stat-icon">❌</div>
        <div class="stat-value"><?= $rejected ?></div>
        <div class="stat-label">Ditolak</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">📢</div>
        <div class="stat-value"><?= $complaints ?></div>
        <div class="stat-label">Keluhan Aktif</div>
    </div>
</div>
        </div>

        <h2 class="section-title">🎯 Menu Administrasi</h2>
        <div class="menu-grid">
            <a href="approve_petugas.php" class="menu-card">
                <span class="icon">👥</span>
                <span>Approval Petugas TPS</span>
            </a>
            <a href="inbox.php" class="menu-card">
                <span class="icon">📥</span>
                <span>Kotak Masuk Suara</span>
            </a>
            <a href="rekap_tps.php" class="menu-card">
                <span class="icon">📋</span>
                <span>Rekap Per TPS</span>
            </a>
            <a href="../results.php" class="menu-card">
                <span class="icon">📊</span>
                <span>Hasil Pemilihan</span>
            </a>
            <a href="manage_complaints.php" class="menu-card">
                <span class="icon">💬</span>
                <span>Kelola Keluhan</span>
            </a>
            <a href="manage_levels.php" class="menu-card">
                <span class="icon">🏛️</span>
                <span>Tingkat Pemilihan</span>
            </a>
            <a href="manage_categories.php" class="menu-card">
                <span class="icon">🗳️</span>
                <span>Jenis Pemilihan</span>
            </a>
            <a href="manage_candidates.php" class="menu-card">
                <span class="icon">👤</span>
                <span>Kelola Kandidat</span>
            </a>
            <a href="manage_regions.php" class="menu-card">
                <span class="icon">📍</span>
                <span>Kelola Wilayah</span>
            </a>
        </div>
    </main>

    <footer>
        <p class="footer-brand">Pusat Data Hitung Cepat - Kabupaten Kendal</p>
        <p>&copy; 2026 Bawaslu Kabupaten Kendal. All rights reserved.</p>
    </footer>
</body>
</html>