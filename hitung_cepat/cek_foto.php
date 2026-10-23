<?php
require 'config/database.php';

echo "<h2> Diagnostic Foto Bukti</h2>";

// Cek folder uploads
$upload_dirs = [
    'uploads/',
    'uploads/evidence/',
    'uploads/temp/',
];

foreach ($upload_dirs as $dir) {
    if (is_dir($dir)) {
        echo "<p style='color:green;'>✅ Folder <code>$dir</code> ADA</p>";
        
        // List file di folder
        $files = scandir($dir);
        $file_count = count($files) - 2; // Kurangi . dan ..
        echo "<p>Jumlah file: $file_count</p>";
        
        if ($file_count > 0) {
            echo "<ul>";
            foreach ($files as $f) {
                if ($f !== '.' && $f !== '..') {
                    echo "<li>$f</li>";
                }
            }
            echo "</ul>";
        }
    } else {
        echo "<p style='color:red;'>❌ Folder <code>$dir</code> TIDAK ADA</p>";
    }
}

// Cek data di database
echo "<h2>📊 Data Foto di Database</h2>";
$stmt = $pdo->query("
    SELECT v.id, v.status, u.name, ve.photo_path 
    FROM votes v
    JOIN users u ON v.user_id = u.id
    LEFT JOIN vote_evidence ve ON v.id = ve.vote_id
    ORDER BY v.id DESC
");
$votes = $stmt->fetchAll();

echo "<table border='1' cellpadding='10'>";
echo "<tr><th>ID Vote</th><th>Petugas</th><th>Status</th><th>Path di DB</th><th>File Ada?</th></tr>";

foreach ($votes as $v) {
    $path = $v['photo_path'] ?? 'Tidak ada';
    $file_exists = false;
    
    if ($path && $path !== 'Tidak ada') {
        // Cek beberapa kemungkinan path
        $possible_paths = [
            $path,
            '../' . $path,
            'uploads/' . basename($path),
            'uploads/evidence/' . basename($path),
        ];
        
        foreach ($possible_paths as $check_path) {
            if (file_exists($check_path)) {
                $file_exists = true;
                break;
            }
        }
    }
    
    $status_color = $file_exists ? 'green' : 'red';
    $status_text = $file_exists ? '✅ ADA' : ' TIDAK ADA';
    
    echo "<tr>";
    echo "<td>{$v['id']}</td>";
    echo "<td>{$v['name']}</td>";
    echo "<td>{$v['status']}</td>";
    echo "<td><code>$path</code></td>";
    echo "<td style='color:$status_color;'>$status_text</td>";
    echo "</tr>";
}
echo "</table>";

echo "<p><a href='admin/inbox.php'>Kembali ke Inbox</a></p>";
?>