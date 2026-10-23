<?php
// File sementara untuk generate password hash
$password = 'admin123';
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "<h2>Password Hash Generator</h2>";
echo "<p>Password: <strong>admin123</strong></p>";
echo "<p>Hash: <code>$hash</code></p>";
echo "<p><strong>Copy hash di atas dan jalankan query ini:</strong></p>";
echo "<pre>";
echo "UPDATE users SET password = '$hash' WHERE phone = '081234567890' AND role = 'admin';";
echo "</pre>";

// Auto update jika admin sudah ada
$pdo = new PDO('mysql:host=localhost;dbname=hitung_cepat_kendal', 'root', '');
$stmt = $pdo->prepare("UPDATE users SET password = ? WHERE phone = ? AND role = 'admin'");
$stmt->execute([$hash, '081234567890']);

echo "<p style='color:green;'>✅ Password admin sudah diupdate otomatis!</p>";
echo "<p><a href='login.php'>Login Sekarang</a></p>";
?>