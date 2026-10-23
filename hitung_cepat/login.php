<?php
require 'config/database.php';
require 'includes/functions.php';

// Jika sudah login, redirect ke dashboard
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: user/dashboard.php');
    }
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $captcha_input = $_POST['captcha'] ?? '';
    
    if (!isset($_SESSION['captcha']) || $captcha_input != $_SESSION['captcha']) {
        $error = "Kode CAPTCHA salah!";
    } elseif (empty($phone) || empty($password)) {
        $error = "Nomor telepon dan password harus diisi!";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE phone = ?");
        $stmt->execute([$phone]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['phone'] = $user['phone'];
            $_SESSION['nomor_tps'] = $user['nomor_tps'] ?? '';
            $_SESSION['desa'] = $user['desa'] ?? '';
            $_SESSION['kecamatan'] = $user['kecamatan'] ?? '';
            $_SESSION['kabupaten'] = $user['kabupaten'] ?? 'Kendal';
            
            if ($user['role'] === 'admin') {
                header('Location: admin/dashboard.php');
            } else {
                if ($user['status'] !== 'approved') {
                    $error = "Akun Anda belum disetujui admin. Silakan tunggu verifikasi.";
                } else {
                    header('Location: user/dashboard.php');
                }
            }
            exit;
        } else {
            $error = "Nomor telepon atau password salah!";
        }
    }
}

$captcha = generate_captcha();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Pusat Data Hitung Cepat</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* === BACKGROUND MERAH DOVE DI LUAR KOTAK LOGIN === */
        body {
            background: linear-gradient(135deg, #B22234 0%, #8B2635 50%, #6B1D2A 100%) !important;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow-x: hidden;
        }
        
        body::before {
            content: '';
            position: fixed;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,215,0,0.08) 0%, transparent 70%);
            animation: rotate 30s linear infinite;
            pointer-events: none;
        }
        
        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        .login-box {
            background: white;
            border-radius: 15px;
            border: 3px solid #FFD700;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.4);
            padding: 40px;
            max-width: 450px;
            width: 100%;
            position: relative;
            z-index: 1;
            animation: slideIn 0.5s ease-out;
        }
        
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .login-logo {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-logo img {
            max-width: 220px;
            height: auto;
            margin-bottom: 20px;
        }
        
        .login-logo h1 {
            color: #8B0000;
            font-size: 1.8rem;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .login-logo .subtitle {
            color: #d4af37;
            font-size: 1.1rem;
            font-weight: 600;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #8B0000;
            font-weight: 700;
            font-size: 0.95rem;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s;
            box-sizing: border-box;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #8B0000;
            box-shadow: 0 0 0 3px rgba(139, 0, 0, 0.1);
        }
        
        .captcha-box {
            background: linear-gradient(135deg, #FFF8DC, #FFEDB0);
            padding: 20px;
            border-radius: 10px;
            border: 2px solid #FFD700;
            margin-bottom: 20px;
        }
        
        .captcha-display {
            background: linear-gradient(45deg, #8B0000, #DC143C);
            color: #FFD700;
            padding: 15px;
            border-radius: 8px;
            font-size: 2rem;
            font-weight: bold;
            letter-spacing: 8px;
            text-align: center;
            margin-bottom: 15px;
            font-family: 'Courier New', monospace;
            border: 2px solid #FFD700;
        }
        
        .captcha-input {
            width: 100%;
            padding: 10px;
            border: 2px solid #8B0000;
            border-radius: 6px;
            font-size: 1.2rem;
            text-align: center;
            letter-spacing: 5px;
            font-weight: bold;
            box-sizing: border-box;
        }
        
        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #8B0000, #DC143C);
            color: #FFD700;
            border: 2px solid #FFD700;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(212, 175, 55, 0.4);
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .alert-error {
            background: #FFF0F0;
            color: #8B0000;
            border: 2px solid #DC143C;
        }
        
        .alert-success {
            background: #F0FFF0;
            color: #006400;
            border: 2px solid #FFD700;
        }
        
        .login-footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #FFD700;
            color: #666;
        }
        
        .login-footer a {
            color: #8B0000;
            text-decoration: none;
            font-weight: 600;
        }
        
        .login-footer a:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 480px) {
            .login-box {
                padding: 30px 20px;
            }
            
            .login-logo img {
                max-width: 180px;
            }
            
            .login-logo h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-box">
        <div class="login-logo">
            <img src="assets/images/logo.png" alt="Logo Bawaslu" 
                 onerror="this.onerror=null; this.src='https://upload.wikimedia.org/wikipedia/commons/thumb/0/07/Logo_Bawaslu_RI.svg/400px-Logo_Bawaslu_RI.svg.png';">
            <h1>Pusat Data Hitung Cepat</h1>
            <p class="subtitle">Kabupaten Kendal</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error">
                 <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['msg']) && $_GET['msg'] == 'registered'): ?>
            <div class="alert alert-success">
                 Registrasi berhasil! Silakan login dengan akun Anda.
            </div>
        <?php endif; ?>

        <form method="POST" action="" autocomplete="off">
            <div class="form-group">
                <label for="phone"> Nomor Telepon</label>
                <input type="text" 
                       id="phone" 
                       name="phone" 
                       placeholder="081234567890" 
                       required 
                       autofocus
                       autocomplete="off"
                       value="">
            </div>

            <div class="form-group">
                <label for="password"> Password</label>
                <input type="password" 
                       id="password" 
                       name="password" 
                       placeholder="Masukkan password Anda" 
                       required
                       autocomplete="new-password">
            </div>

            <div class="captcha-box">
                <label style="display: block; margin-bottom: 10px; color: #8B0000; font-weight: 700;">
                     Kode CAPTCHA
                </label>
                <div class="captcha-display"><?= $captcha ?></div>
                <input type="text" 
                       name="captcha" 
                       class="captcha-input" 
                       placeholder="Masukkan kode" 
                       required 
                       maxlength="4"
                       autocomplete="off">
            </div>

            <button type="submit" class="btn-login">
                 Login
            </button>
        </form>

        <div class="login-footer">
            <p>Belum punya akun? <a href="register.php">Daftar di sini</a></p>
            <p style="margin-top: 10px; font-size: 0.85rem;">
                 2026 Bawaslu Kabupaten Kendal
            </p>
        </div>
    </div>
</body>
</html>