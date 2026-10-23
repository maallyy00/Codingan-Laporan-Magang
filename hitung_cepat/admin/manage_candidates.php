<?php
require '../config/database.php';
require '../includes/functions.php';
check_login('admin');

$upload_dir = "../uploads/candidates/";
if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add'])) {
        $photo = null;
        if (!empty($_FILES['photo']['name'])) {
            $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
            $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9]/', '_', $_POST['name']) . '.' . $ext;
            $photo = 'candidates/' . $filename;
            move_uploaded_file($_FILES['photo']['tmp_name'], $upload_dir . $filename);
        }
        $stmt = $pdo->prepare("INSERT INTO candidates (category_id, name, photo) VALUES (?, ?, ?)");
        $stmt->execute([$_POST['category_id'], $_POST['name'], $photo]);
    } elseif (isset($_POST['edit'])) {
        $photo = $_POST['old_photo'];
        if (!empty($_FILES['photo']['name'])) {
            $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
            $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9]/', '_', $_POST['name']) . '.' . $ext;
            $photo = 'candidates/' . $filename;
            move_uploaded_file($_FILES['photo']['tmp_name'], $upload_dir . $filename);
        }
        $stmt = $pdo->prepare("UPDATE candidates SET category_id = ?, name = ?, photo = ? WHERE id = ?");
        $stmt->execute([$_POST['category_id'], $_POST['name'], $photo, $_POST['id']]);
    } elseif (isset($_POST['delete'])) {
        $stmt = $pdo->prepare("DELETE FROM candidates WHERE id = ?");
        $stmt->execute([$_POST['id']]);
    }
    redirect('manage_candidates.php');
}

$categories = $pdo->query("SELECT ec.*, el.name as level_name FROM election_categories ec JOIN election_levels el ON ec.level_id = el.id ORDER BY el.name, ec.name")->fetchAll();
$candidates = $pdo->query("SELECT c.*, ec.name as category_name FROM candidates c JOIN election_categories ec ON c.category_id = ec.id ORDER BY ec.name, c.name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Kandidat</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <img src="../assets/images/logo.png" alt="Logo Bawaslu"
             onerror="this.onerror=null; this.src='https://upload.wikimedia.org/wikipedia/commons/thumb/0/07/Logo_Bawaslu_RI.svg/200px-Logo_Bawaslu_RI.svg.png';">
        <h1>Kelola Kandidat / Personil</h1>
        <a href="dashboard.php" class="btn-back">Kembali</a>
    </header>
    <main class="container">
        <div class="form-card">
            <h3>Tambah Kandidat</h3>
            <form method="POST" enctype="multipart/form-data" class="inline-form">
                <select name="category_id" required>
                    <option value="">Pilih Jenis Pemilihan</option>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= e($c['level_name']) ?> - <?= e($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="text" name="name" placeholder="Nama Kandidat" required>
                <input type="file" name="photo" accept="image/*">
                <button type="submit" name="add" class="btn-success">Tambah</button>
            </form>
        </div>

        <table class="data-table">
            <thead><tr><th>Foto</th><th>Nama</th><th>Jenis Pemilihan</th><th>Aksi</th></tr></thead>
            <tbody>
                <?php foreach ($candidates as $c): ?>
                    <tr>
                        <td>
                            <?php if (!empty($c['photo'])): 
                                $foto_path = '../uploads/' . $c['photo'];
                                if (file_exists($foto_path)):
                            ?>
                                <img src="<?= $foto_path ?>" class="thumb">
                            <?php else: ?>
                                <img src="https://via.placeholder.com/60x60.png?text=Foto" class="thumb">
                            <?php endif; else: ?>
                                <img src="https://via.placeholder.com/60x60.png?text=Foto" class="thumb">
                            <?php endif; ?>
                        </td>
                        <td><?= e($c['name']) ?></td>
                        <td><?= e($c['category_name']) ?></td>
                        <td>
                            <form method="POST" enctype="multipart/form-data" class="inline-form">
                                <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                <input type="hidden" name="old_photo" value="<?= e($c['photo']) ?>">
                                <select name="category_id" required>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= $cat['id'] ?>" <?= $c['category_id']==$cat['id']?'selected':'' ?>><?= e($cat['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="text" name="name" value="<?= e($c['name']) ?>" required>
                                <input type="file" name="photo" accept="image/*">
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