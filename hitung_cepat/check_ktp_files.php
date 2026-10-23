<?php
require 'config/database.php';

echo "<h2>Cek File KTP</h2>";

// Cek folder uploads
$upload_dir = 'uploads/ktp/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
    echo "✅ Folder uploads/ktp dibuat<br>";
} else {
    echo "✅ Folder uploads/ktp sudah ada<br>";
}

// List semua file di folder
echo "<h3>File di folder uploads/ktp/:</h3>";
$files = scandir($upload_dir);
foreach ($files as $file) {
    if ($file !== '.' && $file !== '..') {
        echo " $file<br>";
    }
}

// Cek data di database
echo "<h3>Data KTP di Database:</h3>";
$users = $pdo->query("SELECT id, name, ktp_photo FROM users WHERE role='user'")->fetchAll();
foreach ($users as $u) {
    echo "<strong>{$u['name']}</strong> (ID: {$u['id']}): ";
    if (empty($u['ktp_photo'])) {
        echo "<span style='color: orange;'>Tidak ada foto KTP</span><br>";
    } else {
        $full_path = 'uploads/' . $u['ktp_photo'];
        if (file_exists($full_path)) {
            echo "<span style='color: green;'>✅ File ada</span> - <a href='$full_path' target='_blank'>Lihat</a><br>";
        } else {
            echo "<span style='color: red;'>❌ File tidak ada di server</span><br>";
            echo "<small>Path di DB: {$u['ktp_photo']}</small><br>";
            echo "<small>Path yang dicek: $full_path</small><br>";
        }
    }
}

echo "<br><a href='admin/approve_petugas.php'>Kembali ke Approval</a>";
?>