<?php
require '../config/database.php';
require '../includes/functions.php';
check_login('admin');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add'])) {
        $stmt = $pdo->prepare("INSERT INTO election_categories (level_id, name) VALUES (?, ?)");
        $stmt->execute([$_POST['level_id'], $_POST['name']]);
    } elseif (isset($_POST['edit'])) {
        $stmt = $pdo->prepare("UPDATE election_categories SET level_id = ?, name = ? WHERE id = ?");
        $stmt->execute([$_POST['level_id'], $_POST['name'], $_POST['id']]);
    } elseif (isset($_POST['delete'])) {
        $stmt = $pdo->prepare("DELETE FROM election_categories WHERE id = ?");
        $stmt->execute([$_POST['id']]);
    }
    redirect('manage_categories.php');
}

$levels = $pdo->query("SELECT * FROM election_levels ORDER BY name")->fetchAll();
$categories = $pdo->query("SELECT ec.*, el.name as level_name FROM election_categories ec JOIN election_levels el ON ec.level_id = el.id ORDER BY el.name, ec.name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Jenis Pemilihan</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <img src="../assets/images/logo.png" alt="Logo Bawaslu"
             onerror="this.onerror=null; this.src='https://upload.wikimedia.org/wikipedia/commons/thumb/0/07/Logo_Bawaslu_RI.svg/200px-Logo_Bawaslu_RI.svg.png';">
        <h1>Kelola Jenis Pemilihan</h1>
        <a href="dashboard.php" class="btn-back">Kembali</a>
    </header>
    <main class="container">
        <div class="form-card">
            <h3>Tambah Jenis Pemilihan</h3>
            <form method="POST" class="inline-form">
                <select name="level_id" required>
                    <option value="">Pilih Tingkat</option>
                    <?php foreach ($levels as $l): ?>
                        <option value="<?= $l['id'] ?>"><?= e($l['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="text" name="name" placeholder="Presiden" required>
                <button type="submit" name="add" class="btn-success">Tambah</button>
            </form>
        </div>

        <table class="data-table">
            <thead><tr><th>Tingkat</th><th>Jenis Pemilihan</th><th>Aksi</th></tr></thead>
            <tbody>
                <?php foreach ($categories as $c): ?>
                    <tr>
                        <td><?= e($c['level_name']) ?></td>
                        <td><?= e($c['name']) ?></td>
                        <td>
                            <form method="POST" class="inline-form">
                                <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                <select name="level_id" required>
                                    <?php foreach ($levels as $l): ?>
                                        <option value="<?= $l['id'] ?>" <?= $c['level_id']==$l['id']?'selected':'' ?>><?= e($l['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="text" name="name" value="<?= e($c['name']) ?>" required>
                                <button type="submit" name="edit" class="btn-primary btn-sm">Edit</button>
                            </form>
                            <form method="POST" class="inline-form" onsubmit="return confirm('Yakin hapus?');">
                                <input type="hidden" name="id" value="<?= $c['id'] ?>">
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