<?php
require '../config/database.php';
require '../includes/functions.php';
check_login('admin');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add'])) {
        $stmt = $pdo->prepare("INSERT INTO regions (kabupaten, kecamatan, desa) VALUES ('Kendal', ?, ?)");
        $stmt->execute([$_POST['kecamatan'], $_POST['desa']]);
    } elseif (isset($_POST['delete'])) {
        $stmt = $pdo->prepare("DELETE FROM regions WHERE id = ?");
        $stmt->execute([$_POST['id']]);
    }
    redirect('manage_regions.php');
}

$regions = $pdo->query("SELECT * FROM regions ORDER BY kecamatan, desa")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Wilayah</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <img src="../assets/images/logo.png" alt="Logo Bawaslu"
             onerror="this.onerror=null; this.src='https://upload.wikimedia.org/wikipedia/commons/thumb/0/07/Logo_Bawaslu_RI.svg/200px-Logo_Bawaslu_RI.svg.png';">
        <h1>Kelola Wilayah (Kecamatan & Desa)</h1>
        <a href="dashboard.php" class="btn-back">Kembali</a>
    </header>
    <main class="container">
        <div class="form-card">
            <h3>Tambah Desa</h3>
            <form method="POST" class="inline-form">
                <input type="text" name="kecamatan" placeholder="Nama Kecamatan" required>
                <input type="text" name="desa" placeholder="Nama Desa" required>
                <button type="submit" name="add" class="btn-success">Tambah</button>
            </form>
        </div>

        <table class="data-table">
            <thead><tr><th>Kabupaten</th><th>Kecamatan</th><th>Desa</th><th>Aksi</th></tr></thead>
            <tbody>
                <?php foreach ($regions as $r): ?>
                    <tr>
                        <td><?= e($r['kabupaten']) ?></td>
                        <td><?= e($r['kecamatan']) ?></td>
                        <td><?= e($r['desa']) ?></td>
                        <td>
                            <form method="POST" class="inline-form" onsubmit="return confirm('Yakin hapus?');">
                                <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                <button type="submit" name="delete" class="btn-danger btn-sm">Hapus</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>
</body>
</html>