<?php
require 'config/database.php';

// Generate password hash untuk 'admin123'
$password = password_hash('admin123', PASSWORD_DEFAULT);

// Update password admin
$stmt = $pdo->prepare("UPDATE users SET password = ? WHERE phone = '081234567890'");
$stmt->execute([$password]);

echo "Password admin berhasil diupdate!<br>";
echo "Login dengan:<br>";
echo "No. Telepon: 081234567890<br>";
echo "Password: admin123";
?>