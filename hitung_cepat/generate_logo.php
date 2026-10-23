<?php
echo "<h1>🔧 Auto Generate Logo Bawaslu</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; padding: 30px; background: #f5f5f5; }
    .box { background: white; padding: 20px; border-radius: 10px; margin: 15px 0; border-left: 5px solid #8B0000; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    .success { color: #28a745; font-weight: bold; }
    .error { color: #dc3545; font-weight: bold; }
    .info { color: #0066cc; }
    code { background: #f0f0f0; padding: 2px 6px; border-radius: 3px; font-family: monospace; }
    img { border: 2px solid #FFD700; border-radius: 8px; padding: 5px; background: white; margin: 10px 0; }
</style>";

// Path logo
$logo_dir = 'assets/images/';
$logo_path = $logo_dir . 'logo.png';

// 1. Pastikan folder ada
if (!is_dir($logo_dir)) {
    mkdir($logo_dir, 0777, true);
    echo "<div class='box'>✅ Folder <code>$logo_dir</code> berhasil dibuat.</div>";
} else {
    echo "<div class='box'>✅ Folder <code>$logo_dir</code> sudah ada.</div>";
}

// 2. Coba download logo dari internet
echo "<div class='box'>";
echo "<h3>📥 Mencoba Download Logo Bawaslu...</h3>";

$urls = [
    'https://upload.wikimedia.org/wikipedia/commons/thumb/0/07/Logo_Bawaslu_RI.svg/200px-Logo_Bawaslu_RI.svg.png',
    'https://upload.wikimedia.org/wikipedia/commons/0/07/Logo_Bawaslu_RI.svg',
];

$downloaded = false;
foreach ($urls as $url) {
    echo "<p>Mencoba: <code>$url</code></p>";
    
    $context = stream_context_create([
        'http' => [
            'timeout' => 20,
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            'follow_location' => true,
            'header' => "Accept: image/png,image/svg+xml,*/*\r\n"
        ]
    ]);
    
    $data = @file_get_contents($url, false, $context);
    
    if ($data !== false && strlen($data) > 500) {
        file_put_contents($logo_path, $data);
        echo "<p class='success'>✅ Berhasil download dari URL ini!</p>";
        $downloaded = true;
        break;
    } else {
        echo "<p class='error'>❌ Gagal dari URL ini.</p>";
    }
}

// 3. Jika download gagal, buat logo placeholder
if (!$downloaded) {
    echo "<h3>🎨 Membuat Logo Placeholder...</h3>";
    
    if (function_exists('imagecreatetruecolor')) {
        // Buat gambar 200x200
        $img = imagecreatetruecolor(200, 200);
        
        // Warna
        $white = imagecolorallocate($img, 255, 255, 255);
        $red = imagecolorallocate($img, 139, 0, 0);
        $gold = imagecolorallocate($img, 255, 215, 0);
        $dark_red = imagecolorallocate($img, 100, 0, 0);
        
        // Background putih
        imagefill($img, 0, 0, $white);
        
        // Border luar merah
        imagerectangle($img, 0, 0, 199, 199, $red);
        
        // Border dalam emas
        imagerectangle($img, 5, 5, 194, 194, $gold);
        
        // Background merah di tengah (logo box)
        imagefilledrectangle($img, 20, 20, 180, 100, $red);
        
        // Teks BAWASLU
        $font = 5;
        $text = "BAWASLU";
        $tw = imagefontwidth($font) * strlen($text);
        $th = imagefontheight($font);
        imagestring($img, $font, (200-$tw)/2, 50, $text, $gold);
        
        // Teks Kabupaten Kendal
        $text2 = "Kab. Kendal";
        $tw2 = imagefontwidth(3) * strlen($text2);
        imagestring($img, 3, (200-$tw2)/2, 130, $text2, $red);
        
        // Simpan
        imagepng($img, $logo_path);
        imagedestroy($img);
        
        echo "<p class='success'>✅ Logo placeholder berhasil dibuat!</p>";
    } else {
        echo "<p class='error'>❌ GD Library tidak tersedia. Membuat file SVG...</p>";
        
        // Buat SVG sebagai fallback
        $svg = '<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="200" height="200" viewBox="0 0 200 200">
  <rect width="200" height="200" fill="white" stroke="#8B0000" stroke-width="4"/>
  <rect x="10" y="10" width="180" height="180" fill="none" stroke="#FFD700" stroke-width="3"/>
  <rect x="20" y="20" width="160" height="80" fill="#8B0000"/>
  <text x="100" y="70" font-family="Arial" font-size="24" font-weight="bold" fill="#FFD700" text-anchor="middle">BAWASLU</text>
  <text x="100" y="140" font-family="Arial" font-size="16" fill="#8B0000" text-anchor="middle">Kab. Kendal</text>
</svg>';
        
        // Simpan sebagai PNG (rename extension)
        file_put_contents($logo_path, $svg);
        echo "<p class='info'>⚠️ File SVG dibuat (mungkin tidak kompatibel dengan semua browser).</p>";
    }
}

// 4. Verifikasi
echo "<div class='box'>";
echo "<h3>✅ Verifikasi Logo</h3>";
if (file_exists($logo_path)) {
    $size = filesize($logo_path);
    echo "<p class='success'>Logo berhasil dibuat di: <code>$logo_path</code></p>";
    echo "<p>Ukuran file: " . number_format($size) . " bytes</p>";
    echo "<img src='$logo_path' alt='Logo Preview'>";
} else {
    echo "<p class='error'>❌ Logo GAGAL dibuat!</p>";
}
echo "</div>";

// 5. Test di semua halaman
echo "<div class='box'>";
echo "<h3>🔗 Test Logo di Halaman</h3>";
echo "<ul>";
echo "<li><a href='login.php' target='_blank'>Test Login Page</a></li>";
echo "<li><a href='register.php' target='_blank'>Test Register Page</a></li>";
echo "<li><a href='info.php' target='_blank'>Test Info Page</a></li>";
echo "<li><a href='user/submit_complaint.php' target='_blank'>Test Form Keluhan</a></li>";
echo "</ul>";
echo "</div>";

// 6. Instruksi
echo "<div class='box' style='background: #FFF8DC; border-left-color: #FFD700;'>";
echo "<h3>️ PENTING</h3>";
echo "<p>Setelah logo berhasil dibuat, <strong>HAPUS file ini</strong> (<code>generate_logo.php</code>) untuk keamanan.</p>";
echo "<p>Cara hapus: Delete file <code>C:\\xampp\\htdocs\\hitung_cepat\\generate_logo.php</code></p>";
echo "</div>";
?>