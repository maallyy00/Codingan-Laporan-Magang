<?php
// Script sederhana untuk membuat logo
$folder = 'assets/images/';
if (!is_dir($folder)) mkdir($folder, 0777, true);

$img = imagecreatetruecolor(100, 100);
$putih = imagecolorallocate($img, 255, 255, 255);
$merah = imagecolorallocate($img, 139, 0, 0);
$emas = imagecolorallocate($img, 255, 215, 0);

imagefill($img, 0, 0, $putih);
imagerectangle($img, 0, 0, 99, 99, $merah);
imagerectangle($img, 4, 4, 95, 95, $emas);

imagefilledrectangle($img, 15, 20, 85, 55, $merah);
imagestring($img, 5, 22, 30, "BAWASLU", $emas);
imagestring($img, 3, 20, 70, "Kab.Kendal", $merah);

imagepng($img, $folder . 'logo.png');
imagedestroy($img);

echo "<h2 style='color:green;'>✅ Logo berhasil dibuat!</h2>";
echo "<img src='assets/images/logo.png' style='border:2px solid gold; border-radius:8px;'>";
echo "<p><a href='info.php'>Test di halaman Tata Cara</a></p>";
echo "<p><a href='user/submit_complaint.php'>Test di halaman Form Keluhan</a></p>";
echo "<p style='color:red;'><strong>HAPUS file buat_logo.php setelah ini!</strong></p>";
?>