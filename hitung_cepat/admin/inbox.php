<?php
require '../config/database.php';
require '../includes/functions.php';
check_login('admin');

// Proses approval/rejection
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $vote_id = $_POST['vote_id'];
    $action = $_POST['action'];
    
    $status = $action === 'approve' ? 'disetujui' : 'ditolak';
    
    $stmt = $pdo->prepare("UPDATE votes SET status = ? WHERE id = ?");
    $stmt->execute([$status, $vote_id]);
    
    header("Location: inbox.php?msg=" . $action);
    exit;
}

// Ambil semua data yang menunggu verifikasi
$stmt = $pdo->query("
    SELECT 
        v.id as vote_id,
        u.name as nama_petugas,
        u.phone as no_telp,
        u.nomor_tps,
        u.desa,
        u.kecamatan,
        ec.name as jenis_pemilihan,
        v.total_suara_sah,
        v.suara_tidak_sah,
        v.total_seluruh,
        v.status,
        v.created_at,
        ve.photo_path as bukti_foto
    FROM votes v
    JOIN users u ON v.user_id = u.id
    JOIN election_categories ec ON v.category_id = ec.id
    LEFT JOIN vote_evidence ve ON v.id = ve.vote_id
    WHERE v.status = 'menunggu_verifikasi'
    ORDER BY v.created_at DESC
");
$pending_votes = $stmt->fetchAll();

// Statistik
$total_pending = count($pending_votes);
$total_approved = $pdo->query("SELECT COUNT(*) FROM votes WHERE status='disetujui'")->fetchColumn();
$total_rejected = $pdo->query("SELECT COUNT(*) FROM votes WHERE status='ditolak'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kotak Masuk Suara - Verifikasi</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .inbox-wrapper { max-width: 1400px; margin: 30px auto; padding: 0 20px; }
        .stats-box {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        .stat-item {
            background: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            border: 2px solid #FFD700;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        .stat-item h3 { margin: 0 0 5px 0; font-size: 2rem; color: #8B0000; }
        .stat-item p { margin: 0; color: #666; font-size: 0.9rem; }
        .stat-item.pending { border-left: 5px solid #FFD700; }
        .stat-item.approved { border-left: 5px solid #28a745; }
        .stat-item.rejected { border-left: 5px solid #DC143C; }
        
        .vote-card {
            background: white;
            border-radius: 10px;
            border: 2px solid #FFD700;
            margin-bottom: 20px;
            overflow: hidden;
            box-shadow: 0 3px 15px rgba(139, 0, 0, 0.1);
        }
        .vote-header {
            background: linear-gradient(135deg, #8B0000, #DC143C);
            color: #FFD700;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .vote-header h3 { margin: 0; font-size: 1.2rem; }
        .vote-badge {
            background: #FFD700;
            color: #8B0000;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 0.9rem;
        }
        .vote-body { padding: 20px; }
        .vote-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        .info-item {
            background: #FFF8DC;
            padding: 12px;
            border-radius: 8px;
            border-left: 3px solid #FFD700;
        }
        .info-item label {
            font-weight: bold;
            color: #8B0000;
            font-size: 0.85rem;
            display: block;
            margin-bottom: 5px;
        }
        .info-item span { color: #333; font-size: 1rem; }
        .vote-results {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .vote-results h4 { margin: 0 0 10px 0; color: #8B0000; font-size: 1rem; }
        .results-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 10px;
        }
        .result-item {
            background: white;
            padding: 10px;
            border-radius: 6px;
            text-align: center;
        }
        .result-item .label { font-size: 0.85rem; color: #666; }
        .result-item .value { font-size: 1.3rem; font-weight: bold; color: #8B0000; }
        .bukti-foto {
            text-align: center;
            margin: 20px 0;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        .bukti-foto h4 { color: #8B0000; margin-bottom: 15px; }
        .bukti-foto img {
            max-width: 100%;
            max-height: 400px;
            border: 2px solid #FFD700;
            border-radius: 8px;
            cursor: pointer;
        }
        .bukti-foto img:hover { transform: scale(1.02); transition: transform 0.3s; }
        .bukti-error {
            background: #FFF0F0;
            border: 2px solid #DC143C;
            padding: 15px;
            border-radius: 8px;
            color: #8B0000;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            padding: 15px 20px;
            background: #f8f9fa;
            border-top: 2px solid #FFD700;
        }
        .btn-approve {
            background: #28a745;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            font-size: 1rem;
        }
        .btn-approve:hover { background: #218838; }
        .btn-reject {
            background: #DC143C;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            font-size: 1rem;
        }
        .btn-reject:hover { background: #c82333; }
        .no-data {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 10px;
            border: 2px solid #FFD700;
        }
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
    </style>
</head>
<body>
    <header>
        <img src="../assets/images/logo.png" alt="Logo Bawaslu"
             onerror="this.onerror=null; this.src='https://upload.wikimedia.org/wikipedia/commons/thumb/0/07/Logo_Bawaslu_RI.svg/200px-Logo_Bawaslu_RI.svg.png';">
        <h1>Kotak Masuk Suara - Verifikasi</h1>
        <a href="dashboard.php" class="btn-back">Kembali</a>
    </header>

    <div class="inbox-wrapper">
        <?php if (isset($_GET['msg'])): ?>
            <div class="alert alert-success">
                <?= $_GET['msg'] == 'approve' ? '✅ Data berhasil disetujui!' : '❌ Data ditolak.' ?>
            </div>
        <?php endif; ?>

        <div class="stats-box">
            <div class="stat-item pending">
                <h3><?= $total_pending ?></h3>
                <p>Menunggu Verifikasi</p>
            </div>
            <div class="stat-item approved">
                <h3><?= $total_approved ?></h3>
                <p>Disetujui</p>
            </div>
            <div class="stat-item rejected">
                <h3><?= $total_rejected ?></h3>
                <p>Ditolak</p>
            </div>
        </div>

        <?php if (empty($pending_votes)): ?>
            <div class="no-data">
                <h3>📭 Tidak Ada Data Menunggu Verifikasi</h3>
                <p>Semua data sudah diproses. Bagus!</p>
            </div>
        <?php else: ?>
            <h2 style="color: #8B0000; margin-bottom: 20px;">
                 Data Menunggu Verifikasi (<?= $total_pending ?>)
            </h2>

            <?php foreach ($pending_votes as $vote): ?>
                <div class="vote-card">
                    <div class="vote-header">
                        <h3><?= e($vote['nama_petugas']) ?> - <?= e($vote['nomor_tps']) ?></h3>
                        <span class="vote-badge">Menunggu Verifikasi</span>
                    </div>
                    
                    <div class="vote-body">
                        <div class="vote-info">
                            <div class="info-item">
                                <label>Nama Petugas</label>
                                <span><?= e($vote['nama_petugas']) ?></span>
                            </div>
                            <div class="info-item">
                                <label>No. Telepon</label>
                                <span><?= e($vote['no_telp']) ?></span>
                            </div>
                            <div class="info-item">
                                <label>Nomor TPS</label>
                                <span><?= e($vote['nomor_tps']) ?></span>
                            </div>
                            <div class="info-item">
                                <label>Desa/Kelurahan</label>
                                <span><?= e($vote['desa']) ?></span>
                            </div>
                            <div class="info-item">
                                <label>Kecamatan</label>
                                <span><?= e($vote['kecamatan']) ?></span>
                            </div>
                            <div class="info-item">
                                <label>Jenis Pemilihan</label>
                                <span><?= e($vote['jenis_pemilihan']) ?></span>
                            </div>
                            <div class="info-item">
                                <label>Tanggal Submit</label>
                                <span><?= date('d/m/Y H:i', strtotime($vote['created_at'])) ?></span>
                            </div>
                        </div>

                        <div class="vote-results">
                            <h4>📊 Hasil Perhitungan Suara</h4>
                            <div class="results-grid">
                                <div class="result-item">
                                    <div class="label">Suara Sah</div>
                                    <div class="value"><?= number_format($vote['total_suara_sah']) ?></div>
                                </div>
                                <div class="result-item">
                                    <div class="label">Suara Tidak Sah</div>
                                    <div class="value"><?= number_format($vote['suara_tidak_sah']) ?></div>
                                </div>
                                <div class="result-item">
                                    <div class="label">Total Seluruh</div>
                                    <div class="value" style="color: #DC143C;"><?= number_format($vote['total_seluruh']) ?></div>
                                </div>
                            </div>
                        </div>

                        <!-- Bukti Foto dengan pengecekan file -->
                        <?php 
                        $foto_found = false;
                        $foto_url = '';
                        
                        if (!empty($vote['bukti_foto'])) {
                            // Cek beberapa kemungkinan path
                            $possible_paths = [
                                '../' . $vote['bukti_foto'],
                                '../uploads/' . basename($vote['bukti_foto']),
                                '../uploads/evidence/' . basename($vote['bukti_foto']),
                            ];
                            
                            foreach ($possible_paths as $path) {
                                if (file_exists($path)) {
                                    $foto_found = true;
                                    $foto_url = $path;
                                    break;
                                }
                            }
                        }
                        ?>
                        
                        <div class="bukti-foto">
                            <h4>📸 Bukti Foto</h4>
                            <?php if ($foto_found): ?>
                                <a href="<?= $foto_url ?>" target="_blank">
                                    <img src="<?= $foto_url ?>" alt="Bukti Foto">
                                </a>
                                <p style="margin-top: 10px; font-size: 0.85rem; color: #666;">
                                    Klik gambar untuk perbesar | Path: <?= e($vote['bukti_foto']) ?>
                                </p>
                            <?php else: ?>
                                <div class="bukti-error">
                                    <strong>⚠️ Foto bukti tidak ditemukan</strong>
                                    <p style="margin: 10px 0 0 0; font-size: 0.9rem;">
                                        Path di database: <code><?= e($vote['bukti_foto'] ?? 'Tidak ada') ?></code>
                                    </p>
                                    <p style="margin: 5px 0 0 0; font-size: 0.85rem;">
                                        Silakan cek folder uploads/ atau minta user upload ulang.
                                    </p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="action-buttons">
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="vote_id" value="<?= $vote['vote_id'] ?>">
                            <button type="submit" name="action" value="reject" class="btn-reject" 
                                    onclick="return confirm('Yakin ingin menolak data ini?')">
                                ❌ Tolak Data
                            </button>
                        </form>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="vote_id" value="<?= $vote['vote_id'] ?>">
                            <button type="submit" name="action" value="approve" class="btn-approve"
                                    onclick="return confirm('Yakin ingin menyetujui data ini?')">
                                ✅ Setujui Data
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <footer>
        <p class="footer-brand">Pusat Data Hitung Cepat - Kabupaten Kendal</p>
        <p>&copy; 2026 Bawaslu Kabupaten Kendal</p>
    </footer>
</body>
</html>