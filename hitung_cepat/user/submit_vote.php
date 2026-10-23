<?php
require '../config/database.php';
require '../includes/functions.php';
check_login('user');

// Cek status approved
$stmt = $pdo->prepare("SELECT status FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
if ($stmt->fetchColumn() !== 'approved') {
    redirect('dashboard.php');
}

$uid = $_SESSION['user_id'];
$error = null;
$success = null;

// Ambil kategori yang BELUM pernah disubmit user ini
$submitted_cats = $pdo->prepare("SELECT category_id FROM votes WHERE user_id = ?");
$submitted_cats->execute([$uid]);
$submitted_ids = array_column($submitted_cats->fetchAll(), 'category_id');

$categories = $pdo->query("SELECT ec.*, el.name as level_name 
                           FROM election_categories ec 
                           JOIN election_levels el ON ec.level_id = el.id 
                           ORDER BY el.name, ec.name")->fetchAll();

// Filter hanya yang belum disubmit
$available_cats = array_filter($categories, fn($c) => !in_array($c['id'], $submitted_ids));

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // PERBAIKAN: Gunakan isset() dan null coalescing operator
    $captcha_input = $_POST['captcha'] ?? '';
    $category_id = $_POST['category_id'] ?? '';
    $suara_tidak_sah = (int)($_POST['suara_tidak_sah'] ?? 0);
    
    if ($captcha_input != $_SESSION['captcha']) {
        $error = "CAPTCHA salah!";
    } elseif (empty($category_id)) {
        $error = "Silakan pilih jenis pemilihan!";
    } else {
        // Cek lagi apakah sudah pernah submit
        $check = $pdo->prepare("SELECT id FROM votes WHERE user_id = ? AND category_id = ?");
        $check->execute([$uid, $category_id]);
        if ($check->fetch()) {
            $error = "Anda sudah mengirim hasil untuk pemilihan ini!";
        } else {
            // Hitung total
            $total_sah = 0;
            $jumlah_per_kandidat = [];
            
            if (isset($_POST['suara']) && is_array($_POST['suara'])) {
                foreach ($_POST['suara'] as $cand_id => $jumlah) {
                    $jumlah = (int)$jumlah;
                    $jumlah_per_kandidat[$cand_id] = $jumlah;
                    $total_sah += $jumlah;
                }
            }
            
            $total_seluruh = $total_sah + $suara_tidak_sah;

            // Insert vote
            $stmt = $pdo->prepare("INSERT INTO votes (user_id, category_id, total_suara_sah, suara_tidak_sah, total_seluruh) 
                                   VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$uid, $category_id, $total_sah, $suara_tidak_sah, $total_seluruh]);
            $vote_id = $pdo->lastInsertId();

            // Insert vote results per kandidat
            if (!empty($jumlah_per_kandidat)) {
                $stmt = $pdo->prepare("INSERT INTO vote_results (vote_id, candidate_id, jumlah_suara) VALUES (?, ?, ?)");
                foreach ($jumlah_per_kandidat as $cand_id => $jumlah) {
                    $stmt->execute([$vote_id, $cand_id, $jumlah]);
                }
            }

            // Upload foto bukti - PERBAIKAN PATH
            if (!empty($_FILES['bukti']['name'])) {
                $upload_dir = "../uploads/evidence/";
                
                // Buat folder jika belum ada
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $ext = pathinfo($_FILES['bukti']['name'], PATHINFO_EXTENSION);
                $filename = $vote_id . '_' . time() . '.' . $ext;
                $foto_path = 'evidence/' . $filename;
                
                // PERBAIKAN: Gunakan $upload_dir yang sudah benar
                if (move_uploaded_file($_FILES['bukti']['tmp_name'], $upload_dir . $filename)) {
                    $stmt = $pdo->prepare("INSERT INTO vote_evidence (vote_id, photo_path) VALUES (?, ?)");
                    $stmt->execute([$vote_id, $foto_path]);
                } else {
                    $error = "Gagal upload foto bukti! Pastikan folder uploads/evidence/ ada dan writable.";
                }
            }

            $success = "Hasil perhitungan berhasil dikirim! Menunggu verifikasi admin.";
            $_SESSION['captcha'] = null; // reset captcha
        }
    }
}

$captcha = generate_captcha();

// Ambil kandidat berdasarkan kategori yang dipilih (via AJAX atau default)
$category_id = $_GET['category_id'] ?? ($_POST['category_id'] ?? null);
$kandidat_list = [];
if ($category_id) {
    $stmt = $pdo->prepare("SELECT * FROM candidates WHERE category_id = ? ORDER BY name");
    $stmt->execute([$category_id]);
    $kandidat_list = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Input Hasil Perhitungan</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .submit-page {
            background: #f5f5f5;
            min-height: calc(100vh - 200px);
        }
        .submit-container {
            max-width: 1000px;
            margin: 30px auto;
            padding: 0 20px;
        }
        .form-card {
            background: white;
            border-radius: 15px;
            border: 2px solid #FFD700;
            box-shadow: 0 5px 20px rgba(139, 0, 0, 0.1);
            padding: 30px;
            margin-bottom: 25px;
        }
        .form-card h3 {
            color: #8B0000;
            margin: 0 0 20px 0;
            font-size: 1.3rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .step-number {
            background: linear-gradient(135deg, #8B0000, #DC143C);
            color: #FFD700;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 1.1rem;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #8B0000;
            font-weight: 700;
            font-size: 1rem;
        }
        .form-group select,
        .form-group input[type="number"],
        .form-group input[type="file"] {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s;
        }
        .form-group select:focus,
        .form-group input:focus {
            outline: none;
            border-color: #8B0000;
            box-shadow: 0 0 0 3px rgba(139, 0, 0, 0.1);
        }
        .kandidat-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            background: #FFF8DC;
            border-radius: 10px;
            margin-bottom: 12px;
            border: 2px solid #FFD700;
        }
        .kandidat-item img {
            width: 70px;
            height: 70px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #FFD700;
        }
        .kandidat-item .kandidat-info {
            flex: 1;
        }
        .kandidat-item .kandidat-name {
            font-weight: 700;
            color: #333;
            font-size: 1.05rem;
        }
        .kandidat-item input[type="number"] {
            width: 150px;
            padding: 10px;
            border: 2px solid #8B0000;
            border-radius: 8px;
            font-size: 1.1rem;
            text-align: center;
            font-weight: bold;
            color: #8B0000;
        }
        .total-box {
            background: linear-gradient(135deg, #FFF8DC, #FFEDB0);
            padding: 20px;
            border-radius: 10px;
            border: 2px solid #FFD700;
            margin-top: 20px;
        }
        .total-box .total-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 1.1rem;
        }
        .total-box .total-row.grand-total {
            border-top: 2px solid #8B0000;
            margin-top: 10px;
            padding-top: 15px;
            font-weight: 800;
            font-size: 1.3rem;
            color: #8B0000;
        }
        .captcha-wrapper {
            background: linear-gradient(135deg, #FFF8DC, #FFEDB0);
            padding: 20px;
            border-radius: 10px;
            border: 2px solid #FFD700;
            display: flex;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
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
        .btn-submit {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #8B0000, #DC143C);
            color: #FFD700;
            border: 2px solid #FFD700;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(212, 175, 55, 0.4);
        }
        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
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
        .tps-info {
            background: linear-gradient(135deg, #FFF8DC, white);
            padding: 20px;
            border-radius: 10px;
            border: 2px solid #FFD700;
            margin-bottom: 25px;
        }
        .tps-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        .tps-info-grid div {
            padding: 10px;
        }
        .tps-info-grid strong {
            color: #8B0000;
        }
        .no-kandidat {
            text-align: center;
            padding: 30px;
            color: #666;
            background: #f8f9fa;
            border-radius: 10px;
        }
    </style>
</head>
<body class="submit-page">
    <header>
        <img src="../assets/images/logo.png" alt="Logo"
             onerror="this.onerror=null; this.src='https://upload.wikimedia.org/wikipedia/commons/thumb/0/07/Logo_Bawaslu_RI.svg/200px-Logo_Bawaslu_RI.svg.png';">
        <h1>Form Input Hasil Perhitungan</h1>
        <a href="dashboard.php" class="btn-back">Kembali</a>
    </header>

    <div class="submit-container">
        <?php if ($error): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?= e($success) ?></div>
        <?php endif; ?>

        <?php if (empty($available_cats)): ?>
            <div class="form-card">
                <h3>✅ Semua Jenis Pemilihan Sudah Dikirim</h3>
                <p>Anda sudah mengirim hasil untuk semua jenis pemilihan yang tersedia.</p>
                <a href="dashboard.php" class="btn-submit" style="display:inline-block; margin-top:15px; width:auto; padding: 12px 25px; text-decoration:none;">Kembali ke Dashboard</a>
            </div>
        <?php else: ?>
            <!-- Info TPS -->
            <div class="tps-info">
                <h3 style="color: #8B0000; margin: 0 0 15px 0;"> Identitas TPS (Otomatis)</h3>
                <div class="tps-info-grid">
                    <div><strong>TPS:</strong> <?= e($_SESSION['nomor_tps']) ?></div>
                    <div><strong>Desa:</strong> <?= e($_SESSION['desa']) ?></div>
                    <div><strong>Kecamatan:</strong> <?= e($_SESSION['kecamatan']) ?></div>
                    <div><strong>Petugas:</strong> <?= e($_SESSION['name']) ?></div>
                </div>
            </div>

            <form method="POST" enctype="multipart/form-data" id="voteForm">
                <!-- Step 1: Pilih Jenis Pemilihan -->
                <div class="form-card">
                    <h3><span class="step-number">1</span> Pilih Jenis Pemilihan</h3>
                    <div class="form-group">
                        <select name="category_id" id="category_id" required onchange="loadKandidat(this.value)">
                            <option value="">-- Pilih Jenis Pemilihan --</option>
                            <?php foreach ($available_cats as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= $category_id == $c['id'] ? 'selected' : '' ?>>
                                    <?= e($c['level_name']) ?> - <?= e($c['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Step 2: Hasil Perolehan Suara -->
                <div class="form-card" id="kandidatSection" style="<?= empty($kandidat_list) ? 'display:none;' : '' ?>">
                    <h3><span class="step-number">2</span> Hasil Perolehan Suara</h3>
                    
                    <div id="kandidatList">
                        <?php if (empty($kandidat_list)): ?>
                            <div class="no-kandidat">
                                <p>⚠️ Belum ada kandidat untuk jenis pemilihan ini.</p>
                                <p style="font-size: 0.9rem;">Silakan hubungi admin untuk menambahkan kandidat.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($kandidat_list as $k): ?>
                                <div class="kandidat-item">
                                    <img src="../uploads/<?= e($k['photo'] ?? 'placeholder.png') ?>" 
                                         onerror="this.src='https://via.placeholder.com/70x70.png?text=Foto'">
                                    <div class="kandidat-info">
                                        <div class="kandidat-name"><?= e($k['name']) ?></div>
                                    </div>
                                    <input type="number" name="suara[<?= $k['id'] ?>]" 
                                           placeholder="Jumlah suara" min="0" value="0"
                                           class="suara-input" required>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Suara Tidak Sah -->
                    <div class="form-group" style="margin-top: 25px;">
                        <label for="suara_tidak_sah">Suara Tidak Sah</label>
                        <input type="number" name="suara_tidak_sah" id="suara_tidak_sah" 
                               placeholder="Jumlah suara tidak sah" min="0" value="0" required>
                    </div>

                    <!-- Total -->
                    <div class="total-box">
                        <div class="total-row">
                            <span>Total Suara Sah:</span>
                            <span id="totalSah">0</span>
                        </div>
                        <div class="total-row">
                            <span>Suara Tidak Sah:</span>
                            <span id="totalTidakSah">0</span>
                        </div>
                        <div class="total-row grand-total">
                            <span>Total Seluruh Suara:</span>
                            <span id="totalSeluruh">0</span>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Upload Foto Bukti -->
                <div class="form-card">
                    <h3><span class="step-number">3</span> Upload Foto Bukti (Form C1)</h3>
                    <div class="form-group">
                        <label for="bukti">Foto Bukti Hasil Perhitungan</label>
                        <input type="file" name="bukti" id="bukti" accept="image/jpeg,image/jpg,image/png" required>
                        <small style="color: #666; display: block; margin-top: 5px;">
                            Format: JPG/JPEG/PNG. Foto harus jelas dan dapat dibaca.
                        </small>
                    </div>
                </div>

                <!-- Step 4: CAPTCHA -->
                <div class="form-card">
                    <h3><span class="step-number">4</span> Verifikasi CAPTCHA</h3>
                    <div class="captcha-wrapper">
                        <span style="font-weight: 700; color: #8B0000;">Kode CAPTCHA:</span>
                        <div class="captcha-code-display"><?= $captcha ?></div>
                        <input type="text" name="captcha" class="captcha-input" 
                               placeholder="Masukkan kode" required maxlength="4" autocomplete="off">
                    </div>
                </div>

                <button type="submit" class="btn-submit">
                    📤 Kirim Hasil Perhitungan
                </button>
            </form>
        <?php endif; ?>
    </div>

    <footer>
        <p class="footer-brand">Pusat Data Hitung Cepat - Kabupaten Kendal</p>
        <p>&copy; 2026 Bawaslu Kabupaten Kendal</p>
    </footer>

    <script>
        function loadKandidat(catId) {
            if (!catId) {
                document.getElementById('kandidatSection').style.display = 'none';
                return;
            }
            
            const list = document.getElementById('kandidatList');
            list.innerHTML = '<p style="text-align:center; padding:20px;">Memuat kandidat...</p>';
            document.getElementById('kandidatSection').style.display = 'block';
            
            fetch(`../api/get_candidates.php?category_id=${catId}`)
                .then(r => r.json())
                .then(data => {
                    if (data.length === 0) {
                        list.innerHTML = '<div class="no-kandidat"><p>⚠️ Belum ada kandidat untuk jenis pemilihan ini.</p></div>';
                        return;
                    }
                    
                    list.innerHTML = '';
                    data.forEach(k => {
                        list.innerHTML += `
                            <div class="kandidat-item">
                                <img src="../uploads/${k.photo || 'placeholder.png'}" 
                                     onerror="this.src='https://via.placeholder.com/70x70.png?text=Foto'">
                                <div class="kandidat-info">
                                    <div class="kandidat-name">${k.name}</div>
                                </div>
                                <input type="number" name="suara[${k.id}]" 
                                       placeholder="Jumlah suara" min="0" value="0"
                                       class="suara-input" required>
                            </div>`;
                    });
                    attachListeners();
                })
                .catch(err => {
                    list.innerHTML = '<div class="no-kandidat"><p>❌ Gagal memuat kandidat.</p></div>';
                });
        }

        function attachListeners() {
            document.querySelectorAll('.suara-input, #suara_tidak_sah').forEach(input => {
                input.addEventListener('input', hitungTotal);
            });
        }

        function hitungTotal() {
            let totalSah = 0;
            document.querySelectorAll('.suara-input').forEach(input => {
                totalSah += parseInt(input.value || 0);
            });
            const tidakSah = parseInt(document.getElementById('suara_tidak_sah').value || 0);
            document.getElementById('totalSah').textContent = totalSah.toLocaleString();
            document.getElementById('totalTidakSah').textContent = tidakSah.toLocaleString();
            document.getElementById('totalSeluruh').textContent = (totalSah + tidakSah).toLocaleString();
        }

        attachListeners();
    </script>
</body>
</html>