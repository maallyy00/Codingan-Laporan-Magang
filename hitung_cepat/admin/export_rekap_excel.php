<?php
require '../config/database.php';
require '../includes/functions.php';
require '../includes/export_excel.php';
check_login('admin');

$filter_kec = $_GET['kecamatan'] ?? '';
$filter_cat = $_GET['category_id'] ?? '';
$filter_status = $_GET['status'] ?? '';

$sql = "SELECT u.name as nama_petugas, u.phone as no_telp, u.nomor_tps, u.desa, u.kecamatan, u.kabupaten,
    ec.name as jenis_pemilihan, v.total_suara_sah, v.suara_tidak_sah, v.total_seluruh, v.status, v.created_at
FROM users u
LEFT JOIN votes v ON u.id = v.user_id
LEFT JOIN election_categories ec ON v.category_id = ec.id
WHERE u.role = 'user' AND v.id IS NOT NULL";
$params = [];

if ($filter_kec) { $sql .= " AND u.kecamatan = ?"; $params[] = $filter_kec; }
if ($filter_cat) { $sql .= " AND v.category_id = ?"; $params[] = $filter_cat; }
if ($filter_status) { $sql .= " AND v.status = ?"; $params[] = $filter_status; }
$sql .= " ORDER BY u.kecamatan, u.nomor_tps, v.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$data = $stmt->fetchAll();

$rows = [];
$no = 1;
foreach ($data as $row) {
    $rows[] = [
        $no++, $row['nama_petugas'], $row['no_telp'], $row['nomor_tps'] ?? '-',
        $row['desa'], $row['kecamatan'], $row['jenis_pemilihan'] ?? '-',
        $row['total_suara_sah'] ?? 0, $row['suara_tidak_sah'] ?? 0,
        $row['total_seluruh'] ?? 0, ucfirst(str_replace('_', ' ', $row['status'])),
        date('d/m/Y H:i', strtotime($row['created_at']))
    ];
}

$headers = ['No', 'Petugas', 'No. Telp', 'TPS', 'Desa', 'Kecamatan', 'Jenis Pemilihan', 'Suara Sah', 'Tidak Sah', 'Total', 'Status', 'Tanggal'];
export_to_excel("rekap_tps_" . date('Y-m-d_H-i-s'), $headers, $rows, "REKAP HASIL PER TPS - Kabupaten Kendal");
?>