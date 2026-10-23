<?php
// Script untuk membuat folder upload yang diperlukan
$folders = [
    'uploads/ktp',
    'uploads/evidence',
    'uploads/candidates',
    'uploads/temp',
    'assets/images'
];

foreach ($folders as $folder) {
    if (!file_exists($folder)) {
        if (mkdir($folder, 0777, true)) {
            echo "✅ Folder '$folder' berhasil dibuat<br>";
        } else {
            echo "❌ Gagal membuat folder '$folder'<br>";
        }
    } else {
        echo "ℹ️ Folder '$folder' sudah ada<br>";
    }
}

// Buat file placeholder untuk logo jika belum ada
if (!file_exists('assets/images/logo.png')) {
    // Copy dari URL atau buat placeholder
    $logo_url = 'https://upload.wikimedia.org/wikipedia/commons/thumb/0/07/Logo_Bawaslu_RI.svg/200px-Logo_Bawaslu_RI.svg.png';
    $logo_data = file_get_contents($logo_url);
    if ($logo_data) {
        file_put_contents('assets/images/logo.png', $logo_data);
        echo "✅ Logo berhasil di-download<br>";
    } else {
        echo "️ Gagal download logo. Silakan manual copy logo ke assets/images/logo.png<br>";
    }
}

echo "<br><a href='index.php'>Kembali ke Home</a>";
?>