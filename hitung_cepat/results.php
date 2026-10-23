<?php
require 'config/database.php';
require 'includes/functions.php';
check_login();

$role = $_SESSION['role'];
$category_id = $_GET['category_id'] ?? null;
$kecamatan_filter = $_GET['kecamatan'] ?? null;
$desa_filter = $_GET['desa'] ?? null;

if ($role === 'user') {
    $kecamatan_filter = $_SESSION['kecamatan'] ?? null;
    $desa_filter = $_SESSION['desa'] ?? null;
}

$sql = "SELECT c.id as cid, c.name as candidate, c.photo, COALESCE(SUM(vr.jumlah_suara), 0) as total 
        FROM candidates c
        LEFT JOIN vote_results vr ON c.id = vr.candidate_id
        LEFT JOIN votes v ON vr.vote_id = v.id AND v.status = 'disetujui'
        LEFT JOIN users u ON v.user_id = u.id
        WHERE 1=1";
$params = [];

if ($category_id) { $sql .= " AND c.category_id = ?"; $params[] = $category_id; }
if ($kecamatan_filter) { $sql .= " AND u.kecamatan = ?"; $params[] = $kecamatan_filter; }
if ($desa_filter) { $sql .= " AND u.desa = ?"; $params[] = $desa_filter; }
$sql .= " GROUP BY c.id ORDER BY total DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$results = $stmt->fetchAll();

$labels = json_encode(array_column($results, 'candidate'));
$data = json_encode(array_column($results, 'total'));
$total_suara = array_sum(array_column($results, 'total'));

$categories = $pdo->query("SELECT * FROM election_categories")->fetchAll();
$kecamatan_list = $pdo->query("SELECT DISTINCT kecamatan FROM regions ORDER BY kecamatan")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Hasil Hitung Cepat</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
</head>
<body>
    <header>
        <img src="assets/images/logo.png" alt="Logo" onerror="this.onerror=null; this.src='https://upload.wikimedia.org/wikipedia/commons/thumb/0/07/Logo_Bawaslu_RI.svg/200px-Logo_Bawaslu_RI.svg.png';">
        <h1>Hasil Perhitungan Suara</h1>
        <a href="<?= $role=='admin'?'admin/dashboard.php':'user/dashboard.php' ?>" class="btn-back">Dashboard</a>
    </header>
    <main class="container">
        <?php if ($role === 'user'): ?>
            <div class="alert alert-success" style="background: #FFF8DC; color: #8B0000; border-color: #FFD700;">
                📍 Anda hanya dapat melihat hasil dari daerah: <strong><?= e($_SESSION['desa']) ?>, <?= e($_SESSION['kecamatan']) ?></strong>
            </div>
        <?php endif; ?>

        <form method="GET" class="filter-form">
            <select name="category_id">
                <option value="">Semua Jenis Pemilihan</option>
                <?php foreach ($categories as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= $category_id==$c['id']?'selected':'' ?>><?= e($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <?php if ($role === 'admin'): ?>
                <select name="kecamatan">
                    <option value="">Semua Kecamatan</option>
                    <?php foreach ($kecamatan_list as $k): ?>
                        <option value="<?= e($k['kecamatan']) ?>" <?= $kecamatan_filter==$k['kecamatan']?'selected':'' ?>><?= e($k['kecamatan']) ?></option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>
            <button type="submit" class="btn-primary" style="width:auto;">Filter</button>
            <button type="button" onclick="exportImage('png')" class="btn-secondary">📷 PNG</button>
            <button type="button" onclick="exportImage('jpeg')" class="btn-secondary">️ 📷JPG</button>
            <a href="export_results_excel.php?category_id=<?= $category_id ?? '' ?>&kecamatan=<?= $kecamatan_filter ?? '' ?>" class="btn-success" style="text-decoration:none; padding: 10px 20px; border-radius: 6px; display: inline-block;">📊 Excel</a>
        </form>

        <div id="result-area" style="background: white; padding: 30px; border-radius: 10px; border: 2px solid #FFD700;">
            <h2 style="text-align: center; color: #8B0000;">Rekapitulasi Suara (Total: <?= $total_suara ?>)</h2>
            <div class="charts-grid">
                <div class="chart-box"><h3>Persentase Perolehan</h3><canvas id="pieChart"></canvas></div>
                <div class="chart-box"><h3>Perbandingan Jumlah Suara</h3><canvas id="barChart"></canvas></div>
            </div>
            <table class="data-table">
                <thead><tr><th>Foto</th><th>Kandidat</th><th>Jumlah Suara</th><th>Persentase</th></tr></thead>
                <tbody>
                    <?php foreach ($results as $r): ?>
                        <tr>
                            <td><img src="uploads/<?= e($r['photo'] ?? 'placeholder.png') ?>" class="thumb" onerror="this.src='https://via.placeholder.com/60x60.png?text=Foto'"></td>
                            <td><?= e($r['candidate']) ?></td>
                            <td><?= $r['total'] ?></td>
                            <td><?= $total_suara > 0 ? number_format(($r['total'] / $total_suara) * 100, 2) : 0 ?>%</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
    <footer>
        <p class="footer-brand">Pusat Data Hitung Cepat - Kabupaten Kendal</p>
        <p>&copy; 2026 Bawaslu Kabupaten Kendal</p>
    </footer>
    <script>
        const labels = <?= $labels ?>;
        const data = <?= $data ?>;
        const colors = ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40', '#8A4FFF'];
        new Chart(document.getElementById('pieChart'), { type: 'pie', data: { labels: labels, datasets: [{ data: data, backgroundColor: colors }] } });
        new Chart(document.getElementById('barChart'), { type: 'bar', data: { labels: labels, datasets: [{ label: 'Jumlah Suara', data: data, backgroundColor: colors }] }, options: { scales: { y: { beginAtZero: true } } } });
        function exportImage(format) {
            const area = document.querySelector("#result-area");
            html2canvas(area, { backgroundColor: '#ffffff', scale: 2 }).then(canvas => {
                let link = document.createElement('a');
                link.download = `hasil-hitung-cepat.${format}`;
                link.href = canvas.toDataURL(format === 'jpeg' ? "image/jpeg" : "image/png", 1.0);
                link.click();
            });
        }
    </script>
</body>
</html>