<?php
require '../config/database.php';
require '../includes/functions.php';
check_login('admin');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete') {
    $vote_id = $_POST['vote_id'];
    $check = $pdo->prepare("SELECT status FROM votes WHERE id = ?");
    $check->execute([$vote_id]);
    $vote = $check->fetch();
    
    if ($vote && $vote['status'] == 'ditolak') {
        $stmt = $pdo->prepare("SELECT photo_path FROM vote_evidence WHERE vote_id = ?");
        $stmt->execute([$vote_id]);
        $evidence = $stmt->fetch();
        if ($evidence && !empty($evidence['photo_path'])) {
            $file_path = '../' . $evidence['photo_path'];
            if (file_exists($file_path)) unlink($file_path);
        }
        $stmt = $pdo->prepare("DELETE FROM votes WHERE id = ?");
        $stmt->execute([$vote_id]);
        $success = "Data berhasil dihapus!";
    } else {
        $error = "Hanya data dengan status 'Ditolak' yang bisa dihapus!";
    }
}

$filter_kec = $_GET['kecamatan'] ?? '';
$filter_cat = $_GET['category_id'] ?? '';
$filter_status = $_GET['status'] ?? '';

$sql = "SELECT u.id as user_id, u.name as nama_petugas, u.phone as no_telp, u.nomor_tps, u.desa, u.kecamatan, u.kabupaten,
    ec.id as category_id, ec.name as jenis_pemilihan, v.id as vote_id, v.total_suara_sah, v.suara_tidak_sah, 
    v.total_seluruh, v.status, v.created_at, ve.photo_path as bukti_foto
FROM users u
LEFT JOIN votes v ON u.id = v.user_id
LEFT JOIN election_categories ec ON v.category_id = ec.id
LEFT JOIN vote_evidence ve ON v.id = ve.vote_id
WHERE u.role = 'user'";
$params = [];

if ($filter_kec) { $sql .= " AND u.kecamatan = ?"; $params[] = $filter_kec; }
if ($filter_cat) { $sql .= " AND v.category_id = ?"; $params[] = $filter_cat; }
if ($filter_status) { $sql .= " AND v.status = ?"; $params[] = $filter_status; }
$sql .= " ORDER BY u.kecamatan, u.nomor_tps, v.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rekap = $stmt->fetchAll();

$kecamatan_list = $pdo->query("SELECT DISTINCT kecamatan FROM regions ORDER BY kecamatan")->fetchAll();
$categories = $pdo->query("SELECT * FROM election_categories ORDER BY name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Hasil Per TPS</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <style>
        .rekap-wrapper { max-width: 1400px; margin: 30px auto; padding: 0 20px; }
        .rekap-area { background: white; padding: 30px; border-radius: 10px; border: 2px solid #FFD700; margin-top: 20px; }
        .rekap-header { text-align: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 3px solid #FFD700; }
        .rekap-header h2 { color: #8B0000; margin: 0 0 10px 0; font-size: 1.8rem; }
        .data-info { background: #FFF8DC; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #FFD700; }
        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 12px; font-size: 0.85rem; font-weight: bold; }
        .status-menunggu_verifikasi { background: #FFD700; color: #8B0000; }
        .status_disetujui { background: #28a745; color: white; }
        .status_ditolak { background: #DC143C; color: white; }
        .btn-delete { background: #DC143C; color: white; padding: 6px 12px; border: none; border-radius: 6px; cursor: pointer; font-size: 0.85rem; font-weight: 600; }
        .btn-delete:hover { background: #c82333; }
        .btn-success { background: #28a745; color: white; padding: 10px 20px; border-radius: 6px; font-weight: 600; border: none; cursor: pointer; display: inline-block; text-decoration: none; }
        .btn-success:hover { background: #218838; }
        .btn-secondary { background: #6c757d; color: white; padding: 10px 20px; border-radius: 6px; font-weight: 600; border: none; cursor: pointer; margin-right: 10px; }
        .no-data { text-align: center; padding: 40px; color: #666; font-size: 1.1rem; }
        .filter-section { background: white; padding: 20px; border-radius: 10px; border: 2px solid #FFD700; margin-bottom: 20px; }
        .data-table { width: 100%; border-collapse: collapse; background: white; }
        .data-table th { background: linear-gradient(135deg, #8B0000, #DC143C); color: #FFD700; padding: 12px; text-align: left; }
        .data-table td { padding: 12px; border-bottom: 1px solid #eee; }
        .data-table tr:hover { background: #FFF8DC; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <header>
        <img src="../assets/images/logo.png" alt="Logo Bawaslu" onerror="this.onerror=null; this.src='https://upload.wikimedia.org/wikipedia/commons/thumb/0/07/Logo_Bawaslu_RI.svg/200px-Logo_Bawaslu_RI.svg.png';">
        <h1>Rekap Hasil Per TPS</h1>
        <a href="dashboard.php" class="btn-back">Kembali</a>
    </header>

    <div class="rekap-wrapper">
        <?php if (isset($success)): ?>
            <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px;"><?= $success ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 20px;"><?= $error ?></div>
        <?php endif; ?>

        <form method="GET" class="filter-section no-print">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; align-items: end;">
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: bold; color: #8B0000;">Kecamatan</label>
                    <select name="kecamatan" style="width: 100%; padding: 10px; border: 2px solid #e0e0e0; border-radius: 6px;">
                        <option value="">Semua Kecamatan</option>
                        <?php foreach ($kecamatan_list as $k): ?>
                            <option value="<?= e($k['kecamatan']) ?>" <?= $filter_kec==$k['kecamatan']?'selected':'' ?>><?= e($k['kecamatan']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: bold; color: #8B0000;">Jenis Pemilihan</label>
                    <select name="category_id" style="width: 100%; padding: 10px; border: 2px solid #e0e0e0; border-radius: 6px;">
                        <option value="">Semua Jenis</option>
                        <?php foreach ($categories as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= $filter_cat==$c['id']?'selected':'' ?>><?= e($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: bold; color: #8B0000;">Status</label>
                    <select name="status" style="width: 100%; padding: 10px; border: 2px solid #e0e0e0; border-radius: 6px;">
                        <option value="">Semua Status</option>
                        <option value="menunggu_verifikasi" <?= $filter_status=='menunggu_verifikasi'?'selected':'' ?>>Menunggu Verifikasi</option>
                        <option value="disetujui" <?= $filter_status=='disetujui'?'selected':'' ?>>Disetujui</option>
                        <option value="ditolak" <?= $filter_status=='ditolak'?'selected':'' ?>>Ditolak</option>
                    </select>
                </div>
                <div>
                    <button type="submit" class="btn-primary" style="width: 100%;">FILTER</button>
                </div>
            </div>
        </form>

        <div class="no-print" style="margin-bottom: 20px;">
            <button onclick="exportRekap('png')" class="btn-secondary">📷 Export PNG</button>
            <button onclick="exportRekap('jpeg')" class="btn-secondary">🖼️ Export JPG</button>
            <a href="export_rekap_excel.php?kecamatan=<?= e($filter_kec) ?>&category_id=<?= e($filter_cat) ?>&status=<?= e($filter_status) ?>" class="btn-success">📊 Export Excel</a>
        </div>

        <div id="rekap-area" class="rekap-area">
            <div class="rekap-header">
                <h2>REKAP HASIL PER TPS</h2>
                <p>Pusat Data Hitung Cepat - Kabupaten Kendal</p>
                <p>Filter: <?= $filter_kec ? e($filter_kec) : 'Semua Kecamatan' ?> | 
                   <?= $filter_cat ? ($categories[array_search($filter_cat, array_column($categories, 'id'))]['name'] ?? 'Semua') : 'Semua Jenis' ?> |
                   <?= $filter_status ? ucfirst(str_replace('_', ' ', $filter_status)) : 'Semua Status' ?>
                </p>
            </div>

            <?php 
            $valid_rekap = array_filter($rekap, fn($r) => !empty($r['vote_id']));
            $total_data = count($valid_rekap);
            ?>

            <?php if ($total_data == 0): ?>
                <div class="no-data"><p><strong>Tidak ada data.</strong></p></div>
            <?php else: ?>
                <div class="data-info">
                    <strong>Total Data:</strong> <?= $total_data ?> submission | 
                    <strong>Dicetak:</strong> <?= date('d/m/Y H:i') ?>
                </div>

                <table class="data-table">
                    <thead>
                        <tr>
                            <th>No</th><th>Petugas</th><th>No. Telp</th><th>TPS</th><th>Desa</th><th>Kecamatan</th>
                            <th>Jenis Pemilihan</th><th>Suara Sah</th><th>Tidak Sah</th><th>Total</th>
                            <th>Status</th><th>Tanggal</th><th>Bukti</th><th class="no-print">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach ($rekap as $r): if (empty($r['vote_id'])) continue; ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= e($r['nama_petugas']) ?></td>
                                <td><?= e($r['no_telp']) ?></td>
                                <td><?= e($r['nomor_tps'] ?? '-') ?></td>
                                <td><?= e($r['desa']) ?></td>
                                <td><?= e($r['kecamatan']) ?></td>
                                <td><?= e($r['jenis_pemilihan'] ?? '-') ?></td>
                                <td><?= $r['total_suara_sah'] ?? 0 ?></td>
                                <td><?= $r['suara_tidak_sah'] ?? 0 ?></td>
                                <td><strong><?= $r['total_seluruh'] ?? 0 ?></strong></td>
                                <td><span class="status-badge status_<?= e($r['status']) ?>"><?= ucfirst(str_replace('_', ' ', $r['status'])) ?></span></td>
                                <td><?= date('d/m/Y H:i', strtotime($r['created_at'])) ?></td>
                                <td><?php if ($r['bukti_foto']): ?><a href="../uploads/<?= e($r['bukti_foto']) ?>" target="_blank">📷</a><?php else: ?>-<?php endif; ?></td>
                                <td class="no-print">
                                    <?php if ($r['status'] == 'ditolak'): ?>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('⚠️ Yakin ingin menghapus data ini?')">
                                            <input type="hidden" name="vote_id" value="<?= $r['vote_id'] ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <button type="submit" class="btn-delete">🗑️ Hapus</button>
                                        </form>
                                    <?php else: ?>
                                        <span style="color: #999; font-size: 0.85rem;">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <footer>
        <p class="footer-brand">Pusat Data Hitung Cepat - Kabupaten Kendal</p>
        <p>&copy; 2026 Bawaslu Kabupaten Kendal</p>
    </footer>

    <script>
        function exportRekap(format) {
            const area = document.querySelector("#rekap-area");
            html2canvas(area, { backgroundColor: '#ffffff', scale: 2 }).then(canvas => {
                let link = document.createElement('a');
                link.download = `rekap-tps-<?= date('Y-m-d') ?>.${format}`;
                link.href = canvas.toDataURL(format === 'jpeg' ? "image/jpeg" : "image/png", 1.0);
                link.click();
            });
        }
    </script>
</body>
</html>