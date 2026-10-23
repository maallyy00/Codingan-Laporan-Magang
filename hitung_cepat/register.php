<?php
require 'config/database.php';
require 'includes/functions.php';

$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['captcha']) || $_POST['captcha'] != $_SESSION['captcha']) {
        $error = "Kode CAPTCHA salah!";
    } elseif ($_POST['password'] !== $_POST['password_confirm']) {
        $error = "Konfirmasi password tidak cocok!";
    } elseif (strlen($_POST['password']) < 6) {
        $error = "Password minimal 6 karakter!";
    } else {
        try {
            $ktp_photo = null;
            if (!empty($_FILES['ktp_photo']['name'])) {
                $upload_dir = 'uploads/ktp/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                $ext = pathinfo($_FILES['ktp_photo']['name'], PATHINFO_EXTENSION);
                $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9]/', '_', $_POST['name']) . '.' . $ext;
                $ktp_photo = 'ktp/' . $filename;
                move_uploaded_file($_FILES['ktp_photo']['tmp_name'], $upload_dir . $filename);
            }

            $stmt = $pdo->prepare("INSERT INTO users 
                (name, phone, email, password, ktp_photo, nomor_tps, desa, kecamatan, kabupaten, role, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'user', 'pending')");
            $stmt->execute([
                trim($_POST['name']),
                trim($_POST['phone']),
                trim($_POST['email'] ?? ''),
                password_hash($_POST['password'], PASSWORD_DEFAULT),
                $ktp_photo,
                trim($_POST['nomor_tps']),
                trim($_POST['desa']),
                trim($_POST['kecamatan']),
                'Kendal'
            ]);
            
            header('Location: register.php?success=1');
            exit;
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate') !== false) {
                $error = "Nomor telepon sudah terdaftar!";
            } else {
                $error = "Terjadi kesalahan: " . $e->getMessage();
            }
        }
    }
}

if (isset($_GET['success'])) {
    $success = "Registrasi berhasil! Akun Anda sedang diverifikasi admin.";
}

$captcha = generate_captcha();
$kecamatan_list = $pdo->query("SELECT DISTINCT kecamatan FROM regions ORDER BY kecamatan")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Petugas TPS</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
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
        
        .register-box {
            background: white;
            border-radius: 15px;
            border: 3px solid #FFD700;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.4);
            padding: 40px;
            max-width: 550px;
            width: 100%;
            position: relative;
            z-index: 1;
            animation: slideIn 0.5s ease-out;
        }
        
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .register-logo {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .register-logo img {
            max-width: 200px;
            height: auto;
            margin-bottom: 20px;
        }
        
        .register-logo h1 {
            color: #8B0000;
            font-size: 1.6rem;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .register-logo .subtitle {
            color: #d4af37;
            font-size: 1rem;
            font-weight: 600;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #8B0000;
            font-weight: 700;
            font-size: 0.9rem;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px 12px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 0.95rem;
            transition: all 0.3s;
            box-sizing: border-box;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #8B0000;
            box-shadow: 0 0 0 3px rgba(139, 0, 0, 0.1);
        }
        
        .form-group small {
            display: block;
            margin-top: 3px;
            color: #666;
            font-size: 0.8rem;
        }
        
        .captcha-box {
            background: linear-gradient(135deg, #FFF8DC, #FFEDB0);
            padding: 15px;
            border-radius: 8px;
            border: 2px solid #FFD700;
            margin: 20px 0;
        }
        
        .captcha-display {
            background: linear-gradient(45deg, #8B0000, #DC143C);
            color: #FFD700;
            padding: 12px;
            border-radius: 6px;
            font-size: 1.8rem;
            font-weight: bold;
            letter-spacing: 6px;
            text-align: center;
            margin-bottom: 10px;
            font-family: 'Courier New', monospace;
            border: 2px solid #FFD700;
        }
        
        .captcha-input {
            width: 100%;
            padding: 8px;
            border: 2px solid #8B0000;
            border-radius: 6px;
            font-size: 1.1rem;
            text-align: center;
            letter-spacing: 4px;
            font-weight: bold;
            box-sizing: border-box;
        }
        
        .btn-register {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #8B0000, #DC143C);
            color: #FFD700;
            border: 2px solid #FFD700;
            border-radius: 8px;
            font-size: 1.05rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(212, 175, 55, 0.4);
        }
        
        .alert {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 15px;
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
        
        .register-footer {
            text-align: center;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 2px solid #FFD700;
            color: #666;
            font-size: 0.9rem;
        }
        
        .register-footer a {
            color: #8B0000;
            text-decoration: none;
            font-weight: 600;
        }
        
        .register-footer a:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 480px) {
            .register-box {
                padding: 30px 20px;
            }
            
            .register-logo img {
                max-width: 160px;
            }
            
            .register-logo h1 {
                font-size: 1.3rem;
            }
        }
    </style>
</head>
<body>
    <div class="register-box">
        <div class="register-logo">
            <img src="assets/images/logo.png" alt="Logo Bawaslu" 
                 onerror="this.onerror=null; this.src='https://upload.wikimedia.org/wikipedia/commons/thumb/0/07/Logo_Bawaslu_RI.svg/400px-Logo_Bawaslu_RI.svg.png';">
            <h1>Registrasi Petugas TPS</h1>
            <p class="subtitle">Pusat Data Hitung Cepat - Kabupaten Kendal</p>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-error">
                 <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success">
                 <?= $success ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" enctype="multipart/form-data" autocomplete="off">
            <div class="form-group">
                <label for="name"> Nama Lengkap Petugas</label>
                <input type="text" 
                       id="name" 
                       name="name" 
                       placeholder="Masukkan nama lengkap" 
                       required
                       autocomplete="off"
                       value="">
            </div>

            <div class="form-group">
                <label for="phone"> Nomor Telepon</label>
                <input type="tel" 
                       id="phone" 
                       name="phone" 
                       placeholder="Contoh: 081234567890" 
                       required
                       autocomplete="off"
                       value="">
            </div>

            <div class="form-group">
                <label for="email"> Email (Opsional)</label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       placeholder="email@contoh.com"
                       autocomplete="off"
                       value="">
            </div>

            <div class="form-group">
                <label for="ktp_photo"> Foto KTP (JPG/PNG)</label>
                <input type="file" 
                       id="ktp_photo" 
                       name="ktp_photo" 
                       accept="image/jpeg,image/jpg,image/png" 
                       required>
                <small style="color: #666;">Pastikan foto KTP jelas dan terbaca</small>
            </div>

            <div class="form-group">
                <label for="nomor_tps"> Nomor TPS</label>
                <input type="text" 
                       id="nomor_tps" 
                       name="nomor_tps" 
                       placeholder="Contoh: TPS 015" 
                       required
                       autocomplete="off"
                       value="">
            </div>

            <div class="form-group">
                <label for="kecamatan"> Kecamatan</label>
                <select id="kecamatan" name="kecamatan" required>
                    <option value="">Pilih Kecamatan</option>
                    <?php foreach ($kecamatan_list as $k): ?>
                        <option value="<?= e($k['kecamatan']) ?>">
                            <?= e($k['kecamatan']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="desa"> Desa/Kelurahan</label>
                <input type="text" 
                       id="desa" 
                       name="desa" 
                       placeholder="Nama Desa/Kelurahan" 
                       required
                       autocomplete="off"
                       value="">
            </div>

            <div class="form-group">
                <label for="password"> Password</label>
                <input type="password" 
                       id="password" 
                       name="password" 
                       placeholder="Minimal 6 karakter" 
                       required 
                       minlength="6"
                       autocomplete="new-password">
            </div>

            <div class="form-group">
                <label for="password_confirm"> Konfirmasi Password</label>
                <input type="password" 
                       id="password_confirm" 
                       name="password_confirm" 
                       placeholder="Ulangi password" 
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
                       placeholder="Masukkan kode di atas" 
                       required 
                       maxlength="4"
                       autocomplete="off">
            </div>

            <button type="submit" class="btn-register">
                 Daftar
            </button>
        </form>

        <div class="register-footer">
            <p>Sudah punya akun? <a href="login.php">Login di sini</a></p>
            <p style="margin-top: 8px; font-size: 0.85rem;">
                 2026 Bawaslu Kabupaten Kendal
            </p>
        </div>
    </div>
</body>
</html>