<?php
$folders = [
    'uploads',
    'uploads/evidence',
    'uploads/ktp',
    'uploads/candidates',
    'uploads/temp',
    'assets/images/banners'
];

echo "<h2>🔧 Membuat Folder yang Diperlukan</h2>";

foreach ($folders as $folder) {
    if (!is_dir($folder)) {
        if (mkdir($folder, 0777, true)) {
            echo "<p style='color:green;'>✅ Folder <code>$folder</code> berhasil dibuat</p>";
        } else {
            echo "<p style='color:red;'> Gagal membuat folder <code>$folder</code></p>";
        }
    } else {
        echo "<p style='color:blue;'>️ Folder <code>$folder</code> sudah ada</p>";
    }
}

echo "<p><a href='user/submit_vote.php'>Test Upload Lagi</a></p>";
echo "<p style='color:red;'><strong>⚠️ HAPUS file ini setelah selesai!</strong></p>";
?>