<?php
require '../config/database.php';
require '../includes/functions.php';
check_login('admin');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $stmt = $pdo->prepare("UPDATE complaints SET status = ? WHERE id = ?");
    $stmt->execute([$_POST['status'], $_POST['complaint_id']]);
    redirect('manage_complaints.php');
}

$stmt = $pdo->query("SELECT c.*, u.name, u.phone 
                     FROM complaints c 
                     JOIN users u ON c.user_id = u.id 
                     ORDER BY 
                       CASE c.status WHEN 'dilihat' THEN 1 WHEN 'diproses' THEN 2 ELSE 3 END,
                       c.created_at DESC");
$complaints = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Keluhan</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <img src="../assets/images/logo.png" alt="Logo Bawaslu"
             onerror="this.onerror=null; this.src='https://upload.wikimedia.org/wikipedia/commons/thumb/0/07/Logo_Bawaslu_RI.svg/200px-Logo_Bawaslu_RI.svg.png';">
        <h1>Kelola Keluhan User</h1>
        <a href="dashboard.php" class="btn-back">Kembali</a>
    </header>
    <main class="container">
        <table class="data-table">
            <thead>
                <tr><th>Tanggal</th><th>Pengirim</th><th>No. Telp</th><th>Pesan</th><th>Status</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                <?php foreach ($complaints as $c): ?>
                    <tr>
                        <td><?= date('d/m/Y H:i', strtotime($c['created_at'])) ?></td>
                        <td><?= e($c['name']) ?></td>
                        <td><?= e($c['phone']) ?></td>
                        <td><?= e($c['message']) ?></td>
                        <td><span class="badge badge-<?= $c['status'] ?>"><?= ucfirst($c['status']) ?></span></td>
                        <td>
                            <form method="POST" class="inline-form">
                                <input type="hidden" name="complaint_id" value="<?= $c['id'] ?>">
                                <select name="status">
                                    <option value="dilihat" <?= $c['status']=='dilihat'?'selected':'' ?>>Dilihat</option>
                                    <option value="diproses" <?= $c['status']=='diproses'?'selected':'' ?>>Diproses</option>
                                    <option value="selesai" <?= $c['status']=='selesai'?'selected':'' ?>>Selesai</option>
                                </select>
                                <button type="submit" name="update_status" class="btn-primary btn-sm">Update</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>
</body>
</html>