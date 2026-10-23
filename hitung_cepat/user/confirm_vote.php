<?php
require '../config/database.php';
require '../includes/functions.php';
check_login('user');

if (!isset($_SESSION['pending_vote'])) redirect('submit_vote.php');
$data = $_SESSION['pending_vote'];

// Ambil detail untuk ditampilkan
$stmt = $pdo->prepare("SELECT name FROM election_categories WHERE id = ?");
$stmt->execute([$data['category_id']]);
$cat_name = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT name FROM candidates WHERE id = ?");
$stmt->execute([$data['candidate_id']]);
$cand_name = $stmt->fetchColumn();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['confirm'])) {
        // Cek apakah user sudah pernah vote di kategori ini (Validasi Backend)
        $check = $pdo->prepare("SELECT id FROM votes WHERE user_id = ? AND category_id = ?");
        $check->execute([$_SESSION['user_id'], $data['category_id']]);
        
        if ($check->fetch()) {
            $error = "Anda sudah mengirim suara untuk pemilihan ini!";
        } else {
            // Insert/Update Desa jika belum ada
            $stmt = $pdo->prepare("SELECT id FROM regions WHERE kecamatan = ? AND desa = ?");
            $stmt->execute([$data['kecamatan'], $data['desa']]);
            $region = $stmt->fetch();
            
            if (!$region) {
                $stmt = $pdo->prepare("INSERT INTO regions (kecamatan, desa) VALUES (?, ?)");
                $stmt->execute([$data['kecamatan'], $data['desa']]);
                $region_id = $pdo->lastInsertId();
            } else {
                $region_id = $region['id'];
            }

            // Insert Vote
            $stmt = $pdo->prepare("INSERT INTO votes (user_id, region_id, category_id, candidate_id) VALUES (?, ?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $region_id, $data['category_id'], $data['candidate_id']]);
            $vote_id = $pdo->lastInsertId();

            // Pindah foto dari temp ke permanent
            $perm_dir = "../uploads/evidence/";
            if (!file_exists($perm_dir)) mkdir($perm_dir, 0777, true);
            $new_path = $perm_dir . $vote_id . "_" . basename($data['bukti_path']);
            rename($data['bukti_path'], $new_path);

            // Insert Evidence
            $stmt = $pdo->prepare("INSERT INTO vote_evidence (vote_id, photo_path) VALUES (?, ?)");
            $stmt->execute([$vote_id, $new_path]);

            unset($_SESSION['pending_vote']);
            redirect('dashboard.php?msg=vote_success');
        }
    } else {
        // Batal / Kembali
        if (file_exists($data['bukti_path'])) unlink($data['bukti_path']);
        unset($_SESSION['pending_vote']);
        redirect('submit_vote.php');
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Konfirmasi Suara</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header><img src="https://via.placeholder.com/40x40.png?text=Bawaslu" alt="Logo"> <h1>Konfirmasi Data Suara</h1></header>
    <main class="container">
        <?php if (isset($error)): ?><div class="alert error"><?= $error ?></div><?php endif; ?>
        
        <div class="confirm-box">
            <h3>Ringkasan Data</h3>
            <p><strong>Nama:</strong> <?= e($_SESSION['name']) ?></p>
            <p><strong>Kecamatan:</strong> <?= e($data['kecamatan']) ?></p>
            <p><strong>Desa:</strong> <?= e($data['desa']) ?></p>
            <p><strong>Jenis Pemilihan:</strong> <?= e($cat_name) ?></p>
            <p><strong>Pilihan Kandidat:</strong> <?= e($cand_name) ?></p>
            <p><strong>Bukti:</strong> <a href="../<?= e($data['bukti_path']) ?>" target="_blank">Lihat Foto</a></p>
            
            <div class="alert warning">
                <strong>PERINGATAN:</strong> Pastikan seluruh data dan pilihan Anda sudah benar. Setelah dikirim, data suara tidak dapat ditarik kembali atau diubah.
            </div>
            
            <form method="POST" class="action-buttons">
                <button type="submit" name="back" class="btn-secondary">Kembali</button>
                <button type="submit" name="confirm" class="btn-primary">Kirim Suara</button>
            </form>
        </div>
    </main>
</body>
</html>