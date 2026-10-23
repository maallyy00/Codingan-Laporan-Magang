<?php
require '../config/database.php';
require '../includes/functions.php';
check_login('admin');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'];
    $action = $_POST['action'];
    $status = $action == 'approve' ? 'approved' : 'rejected';
    
    $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
    $stmt->execute([$status, $user_id]);
    redirect('approve_petugas.php');
}

$pending = $pdo->query("SELECT * FROM users WHERE role='user' AND status='pending' ORDER BY created_at DESC")->fetchAll();
$all = $pdo->query("SELECT * FROM users WHERE role='user' ORDER BY status, created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Approval Petugas TPS</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <img src="../assets/images/logo.png" alt="Logo Bawaslu"
             onerror="this.onerror=null; this.src='https://upload.wikimedia.org/wikipedia/commons/thumb/0/07/Logo_Bawaslu_RI.svg/200px-Logo_Bawaslu_RI.svg.png';">
        <h1>Approval Petugas TPS</h1>
        <a href="dashboard.php" class="btn-back">Kembali</a>
    </header>

    <main class="container">
        <?php if (!empty($pending)): ?>
        <h2 class="section-title">⏳ Menunggu Approval (<?= count($pending) ?>)</h2>
        <table class="data-table">
            <thead>
                <tr><th>Tanggal</th><th>Nama</th><th>No. Telp</th><th>TPS</th><th>Desa</th><th>Kecamatan</th><th>KTP</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                <?php foreach ($pending as $u): ?>
                    <tr>
                        <td><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
                        <td><?= e($u['name']) ?></td>
                        <td><?= e($u['phone']) ?></td>
                        <td><?= e($u['nomor_tps']) ?></td>
                        <td><?= e($u['desa']) ?></td>
                        <td><?= e($u['kecamatan']) ?></td>
                        <td>
                            <?php 
                            if (!empty($u['ktp_photo'])): 
                                $ktp_full_path = '../uploads/' . $u['ktp_photo'];
                                if (file_exists($ktp_full_path)):
                            ?>
                                <a href="<?= $ktp_full_path ?>" target="_blank" style="color: #28a745;">📷 Lihat KTP</a>
                            <?php else: ?>
                                <span style="color: red;"> File tidak ada</span>
                                <br><small style="color: #666;">Path: <?= e($u['ktp_photo']) ?></small>
                            <?php 
                                endif; 
                            else: 
                            ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                <button type="submit" name="action" value="approve" class="btn-success btn-sm">✓ Setujui</button>
                                <button type="submit" name="action" value="reject" class="btn-danger btn-sm">✗ Tolak</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
            <div class="form-card"><p style="text-align:center;">Tidak ada petugas menunggu approval.</p></div>
        <?php endif; ?>

        <h2 class="section-title">📋 Semua Petugas TPS</h2>
        <table class="data-table">
            <thead>
                <tr><th>Nama</th><th>No. Telp</th><th>TPS</th><th>Desa</th><th>Kecamatan</th><th>Status</th></tr>
            </thead>
            <tbody>
                <?php foreach ($all as $u): ?>
                    <tr>
                        <td><?= e($u['name']) ?></td>
                        <td><?= e($u['phone']) ?></td>
                        <td><?= e($u['nomor_tps']) ?></td>
                        <td><?= e($u['desa']) ?></td>
                        <td><?= e($u['kecamatan']) ?></td>
                        <td><span class="badge badge-<?= $u['status'] ?>"><?= ucfirst($u['status']) ?></span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>

    <footer>
        <p class="footer-brand">Pusat Data Hitung Cepat - Kabupaten Kendal</p>
        <p>&copy; 2026 Bawaslu Kabupaten Kendal</p>
    </footer>
</body>
</html>