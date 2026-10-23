<?php
require '../config/database.php';
require '../includes/functions.php';
check_login('admin');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add'])) {
        $stmt = $pdo->prepare("INSERT INTO election_levels (name) VALUES (?)");
        $stmt->execute([$_POST['name']]);
    } elseif (isset($_POST['edit'])) {
        $stmt = $pdo->prepare("UPDATE election_levels SET name = ? WHERE id = ?");
        $stmt->execute([$_POST['name'], $_POST['id']]);
    } elseif (isset($_POST['delete'])) {
        $stmt = $pdo->prepare("DELETE FROM election_levels WHERE id = ?");
        $stmt->execute([$_POST['id']]);
    }
    redirect('manage_levels.php');
}

$levels = $pdo->query("SELECT * FROM election_levels ORDER BY name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Tingkat Pemilihan</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <img src="../assets/images/logo.png" alt="Logo Bawaslu" 
             onerror="this.onerror=null; this.src='https://upload.wikimedia.org/wikipedia/commons/thumb/0/07/Logo_Bawaslu_RI.svg/200px-Logo_Bawaslu_RI.svg.png';">
        <h1>Kelola Tingkat Pemilihan</h1>
        <a href="dashboard.php" class="btn-back">Kembali</a>
    </header>
    <main class="container">
        <div class="form-card">
            <h3>Tambah Tingkat Pemilihan</h3>
            <form method="POST" class="inline-form">
                <input type="text" name="name" placeholder="Eksekutif" required>
                <button type="submit" name="add" class="btn-success">Tambah</button>
            </form>
        </div>

        <table class="data-table">
            <thead><tr><th>ID</th><th>Nama Tingkat</th><th>Aksi</th></tr></thead>
            <tbody>
                <?php foreach ($levels as $l): ?>
                    <tr>
                        <td><?= $l['id'] ?></td>
                        <td><?= e($l['name']) ?></td>
                        <td>
                            <form method="POST" class="inline-form">
                                <input type="hidden" name="id" value="<?= $l['id'] ?>">
                                <input type="text" name="name" value="<?= e($l['name']) ?>" required>
                                <button type="submit" name="edit" class="btn-primary btn-sm">Edit</button>
                            </form>
                            <form method="POST" class="inline-form" onsubmit="return confirm('Yakin hapus?');">
                                <input type="hidden" name="id" value="<?= $l['id'] ?>">
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