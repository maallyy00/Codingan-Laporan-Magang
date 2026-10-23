<?php
function generate_captcha() {
    $_SESSION['captcha'] = rand(1000, 9999);
    return $_SESSION['captcha'];
}

function check_login($role = null) {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ../login.php');
        exit;
    }
    if ($role && $_SESSION['role'] !== $role) {
        die('<div style="text-align:center; padding: 50px; font-family: Arial;"><h2 style="color: #c0392b;">Akses Ditolak</h2><p>Anda tidak memiliki izin untuk mengakses halaman ini.</p><a href="../login.php" style="color: #d4af37;">Kembali ke Login</a></div>');
    }
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

// Fungsi untuk mendapatkan logo path yang benar
function get_logo_path($relative = false) {
    $path = 'assets/images/logo.png';
    if ($relative) {
        return $path;
    }
    // Jika dipanggil dari subfolder
    $base = '';
    if (strpos($_SERVER['SCRIPT_NAME'], '/admin/') !== false || 
        strpos($_SERVER['SCRIPT_NAME'], '/user/') !== false) {
        $base = '../';
    }
    return $base . $path;
}