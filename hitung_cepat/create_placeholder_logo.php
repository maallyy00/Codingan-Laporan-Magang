<?php
// Buat logo placeholder sederhana
$logo_dir = 'assets/images/';
if (!file_exists($logo_dir)) {
    mkdir($logo_dir, 0777, true);
}

// Buat gambar placeholder dengan GD library
if (function_exists('imagecreatetruecolor')) {
    $width = 100;
    $height = 100;
    $image = imagecreatetruecolor($width, $height);
    
    // Background putih
    $white = imagecolorallocate($image, 255, 255, 255);
    imagefill($image, 0, 0, $white);
    
    // Border merah
    $red = imagecolorallocate($image, 139, 0, 0);
    imagerectangle($image, 0, 0, $width-1, $height-1, $red);
    
    // Teks "BAWASLU"
    $text_color = imagecolorallocate($image, 139, 0, 0);
    $font_size = 5;
    $text = "BAWASLU";
    $text_width = imagefontwidth($font_size) * strlen($text);
    $text_height = imagefontheight($font_size);
    $x = ($width - $text_width) / 2;
    $y = ($height - $text_height) / 2;
    imagestring($image, $font_size, $x, $y, $text, $text_color);
    
    // Simpan sebagai PNG
    imagepng($image, $logo_dir . 'logo.png');
    imagedestroy($image);
    
    echo "✅ Logo placeholder berhasil dibuat<br>";
} else {
    echo "❌ GD Library tidak tersedia. Silakan install atau gunakan logo manual.<br>";
}

echo "<br><a href='index.php'>Kembali ke Home</a>";
?>