<?php
require '../config/database.php';
require '../includes/functions.php';
check_login('user');

$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['captcha']) || $_POST['captcha'] != $_SESSION['captcha']) {
        $error = "Kode CAPTCHA yang Anda masukkan salah!";
    } elseif (empty(trim($_POST['message']))) {
        $error = "Pesan keluhan tidak boleh kosong!";
    } elseif (strlen(trim($_POST['message'])) < 10) {
        $error = "Pesan keluhan minimal 10 karakter!";
    } else {
        $stmt = $pdo->prepare("INSERT INTO complaints (user_id, message) VALUES (?, ?)");
        $stmt->execute([$_SESSION['user_id'], trim($_POST['message'])]);
        
        header('Location: submit_complaint.php?success=1');
        exit;
    }
}

if (isset($_GET['success'])) {
    $success = "Keluhan Anda berhasil dikirim! Admin akan segera menindaklanjuti.";
}

$captcha = generate_captcha();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Keluhan - Pusat Data Hitung Cepat</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* Khusus halaman form keluhan */
        .complaint-page {
            background: #f5f5f5;
            min-height: calc(100vh - 200px);
        }
        
        .complaint-container {
            max-width: 900px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .complaint-card {
            background: white;
            border-radius: 15px;
            border: 2px solid #FFD700;
            box-shadow: 0 5px 20px rgba(139, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .complaint-header {
            background: linear-gradient(135deg, #8B0000 0%, #DC143C 100%);
            color: #FFD700;
            padding: 25px 30px;
            border-bottom: 3px solid #FFD700;
        }
        
        .complaint-header h2 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 700;
        }
        
        .complaint-header p {
            margin: 8px 0 0;
            color: #FFF;
            font-size: 0.95rem;
        }
        
        .complaint-body {
            padding: 35px 30px;
        }
        
        .form-group-full {
            margin-bottom: 25px;
        }
        
        .form-group-full label {
            display: block;
            margin-bottom: 10px;
            color: #8B0000;
            font-weight: 700;
            font-size: 1rem;
        }
        
        .form-group-full textarea {
            width: 100%;
            min-height: 180px;
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1rem;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            resize: vertical;
            transition: all 0.3s;
            line-height: 1.6;
        }
        
        .form-group-full textarea:focus {
            outline: none;
            border-color: #8B0000;
            box-shadow: 0 0 0 3px rgba(139, 0, 0, 0.1);
        }
        
        .form-group-full textarea::placeholder {
            color: #999;
        }
        
        .char-counter {
            text-align: right;
            font-size: 0.85rem;
            color: #666;
            margin-top: 5px;
        }
        
        .captcha-wrapper {
            background: linear-gradient(135deg, #FFF8DC 0%, #FFEDB0 100%);
            padding: 20px;
            border-radius: 10px;
            border: 2px solid #FFD700;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
        }
        
        .captcha-label {
            font-weight: 700;
            color: #8B0000;
        }
        
        .captcha-code-display {
            background: linear-gradient(45deg, #8B0000, #DC143C);
            color: #FFD700;
            padding: 10px 25px;
            border-radius: 8px;
            font-size: 1.8rem;
            font-weight: bold;
            letter-spacing: 6px;
            font-family: 'Courier New', monospace;
            border: 2px solid #FFD700;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .captcha-input {
            flex: 1;
            min-width: 150px;
            padding: 12px 15px;
            border: 2px solid #8B0000;
            border-radius: 8px;
            font-size: 1.2rem;
            text-align: center;
            letter-spacing: 4px;
            font-weight: bold;
            color: #8B0000;
        }
        
        .captcha-input:focus {
            outline: none;
            border-color: #FFD700;
            box-shadow: 0 0 0 3px rgba(255, 215, 0, 0.3);
        }
        
        .btn-submit-complaint {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #8B0000 0%, #DC143C 100%);
            color: #FFD700;
            border: 2px solid #FFD700;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 5px 15px rgba(139, 0, 0, 0.3);
        }
        
        .btn-submit-complaint:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(212, 175, 55, 0.4);
            background: linear-gradient(135deg, #DC143C 0%, #8B0000 100%);
        }
        
        .alert-complaint {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 0.95rem;
            border-left: 4px solid;
        }
        
        .alert-complaint.error {
            background: #FFF0F0;
            color: #8B0000;
            border-left-color: #DC143C;
            border: 2px solid #DC143C;
        }
        
        .alert-complaint.success {
            background: #F0FFF0;
            color: #006400;
            border-left-color: #FFD700;
            border: 2px solid #FFD700;
        }
        
        .info-box {
            background: #FFF8DC;
            border-left: 4px solid #FFD700;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            color: #8B0000;
            font-size: 0.95rem;
        }
        
        .info-box strong {
            color: #8B0000;
        }
    </style>
</head>
<body class="complaint-page">
    <header>
        <img src="../assets/images/logo.png" alt="Logo Bawaslu" 
             onerror="this.onerror=null; this.src='https://upload.wikimedia.org/wikipedia/commons/thumb/0/07/Logo_Bawaslu_RI.svg/200px-Logo_Bawaslu_RI.svg.png'; this.style.width='50px'; this.style.height='50px'; this.style.border='2px solid #FFD700'; this.style.borderRadius='8px'; this.style.padding='3px'; this.style.background='white';">
        <h1>Form Keluhan</h1>
        <a href="dashboard.php" class="btn-back">Kembali</a>
    </header>

    <div class="complaint-container">
        <div class="complaint-card">
            <div class="complaint-header">
                <h2>📝 Sampaikan Keluhan Anda</h2>
                <p>Kami akan menindaklanjuti setiap keluhan yang masuk</p>
            </div>
            
            <div class="complaint-body">
                <?php if (isset($error)): ?>
                    <div class="alert-complaint error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                
                <?php if (isset($success)): ?>
                    <div class="alert-complaint success"><?= $success ?></div>
                <?php endif; ?>

                <div class="info-box">
                    <strong>ℹ️ Informasi:</strong> Keluhan Anda akan ditinjau oleh admin. 
                    Status dapat dilihat di Dashboard pada bagian "Riwayat Keluhan Saya".
                </div>

                <form method="POST" action="" autocomplete="off">
                    <div class="form-group-full">
                        <label for="message">Tuliskan Keluhan atau Pertanyaan Anda:</label>
                        <textarea 
                            id="message" 
                            name="message" 
                            placeholder="Contoh: Saya mengalami kesulitan dalam menginput hasil perhitungan suara untuk TPS saya..."
                            required
                            minlength="10"
                            maxlength="1000"
                            oninput="updateCharCount()"><?= isset($_POST['message']) ? e($_POST['message']) : '' ?></textarea>
                        <div class="char-counter">
                            <span id="charCount">0</span> / 1000 karakter
                        </div>
                    </div>

                    <div class="captcha-wrapper">
                        <span class="captcha-label">Kode CAPTCHA:</span>
                        <div class="captcha-code-display"><?= $captcha ?></div>
                        <input 
                            type="text" 
                            name="captcha" 
                            class="captcha-input" 
                            placeholder="Masukkan kode" 
                            required 
                            maxlength="4"
                            autocomplete="off">
                    </div>

                    <button type="submit" class="btn-submit-complaint">
                        📤 Kirim Keluhan
                    </button>
                </form>
            </div>
        </div>
    </div>

    <footer>
        <p class="footer-brand">Pusat Data Hitung Cepat - Kabupaten Kendal</p>
        <p>&copy; 2026 Bawaslu Kabupaten Kendal. All rights reserved.</p>
    </footer>

    <script>
        function updateCharCount() {
            const textarea = document.getElementById('message');
            const counter = document.getElementById('charCount');
            counter.textContent = textarea.value.length;
            
            if (textarea.value.length > 900) {
                counter.style.color = '#DC143C';
                counter.style.fontWeight = 'bold';
            } else if (textarea.value.length > 700) {
                counter.style.color = '#FFA500';
            } else {
                counter.style.color = '#666';
            }
        }
        
        // Inisialisasi counter saat halaman dimuat
        updateCharCount();
    </script>
</body>
</html>