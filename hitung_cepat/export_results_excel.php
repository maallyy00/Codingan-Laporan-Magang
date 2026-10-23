<?php
require 'config/database.php';
require 'includes/functions.php';
require 'includes/export_excel.php';
check_login();

$role = $_SESSION['role'];
$category_id = $_GET['category_id'] ?? null;
$kecamatan_filter = $_GET['kecamatan'] ?? null;
$desa_filter = $_GET['desa'] ?? null;

if ($role === 'user') {
    $kecamatan_filter = $_SESSION['kecamatan'] ?? null;
    $desa_filter = $_SESSION['desa'] ?? null;
}

$sql = "SELECT c.name as candidate, COALESCE(SUM(vr.jumlah_suara), 0) as total 
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

$total_suara = array_sum(array_column($results, 'total'));

$rows = [];
$no = 1;
foreach ($results as $r) {
    $persentase = $total_suara > 0 ? number_format(($r['total'] / $total_suara) * 100, 2) . '%' : '0%';
    $rows[] = [$no++, $r['candidate'], number_format($r['total']), $persentase];
}
$rows[] = ['', 'TOTAL', number_format($total_suara), '100%'];

$headers = ['No', 'Kandidat', 'Jumlah Suara', 'Persentase'];
export_to_excel("hasil_perhitungan_" . date('Y-m-d_H-i-s'), $headers, $rows, "HASIL PERHITUNGAN SUARA - Kabupaten Kendal");
?>