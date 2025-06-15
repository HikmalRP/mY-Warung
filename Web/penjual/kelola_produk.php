<?php
require_once '../db_connection.php';
session_start();

// Cek apakah penjual sudah login
require_once 'auth_penjual.php';

$db = new DBConnection();
$penjual_id = $_SESSION['penjual_id'];
$error = null;

// Ambil nama warung penjual
$penjualQuery = $db->conn->prepare("SELECT nama_warung FROM db_penjual WHERE id = ?");
$penjualQuery->bind_param("i", $penjual_id);
$penjualQuery->execute();
$penjual = $penjualQuery->get_result()->fetch_assoc();
$nama_warung = $penjual['nama_warung'] ?? '';

// Tambah Produk
if (isset($_POST['add_product'])) {
    $nama = $_POST['nama'];
    $deskripsi = $_POST['deskripsi'];
    $harga = $_POST['harga'];

    // Cek duplikat nama produk milik penjual
    $cek = $db->conn->prepare("SELECT id FROM db_produk WHERE nama = ? AND id_penjual = ?");
    $cek->bind_param("si", $nama, $penjual_id);
    $cek->execute();
    if ($cek->get_result()->num_rows > 0) {
        $error = "Nama produk \"$nama\" sudah digunakan.";
    } else {
        $gambar = null;
        if (!empty($_FILES['gambar']['name'])) {
            $gambar = '/uploads/' . basename($_FILES['gambar']['name']);
            move_uploaded_file($_FILES['gambar']['tmp_name'], $gambar);
        }

        $query = $db->conn->prepare("INSERT INTO db_produk (nama, deskripsi, linkGambar, harga, id_penjual) VALUES (?, ?, ?, ?, ?)");
        $query->bind_param("sssii", $nama, $deskripsi, $gambar, $harga, $penjual_id);
        $query->execute();
        header("Location: kelola_produk.php");
        exit();
    }
}

// Edit Produk
if (isset($_POST['edit_product'])) {
    $id = $_POST['id'];
    $nama = $_POST['nama'];
    $deskripsi = $_POST['deskripsi'];
    $harga = $_POST['harga'];
    $gambar = $_POST['existing_image'];

    // Cek duplikat nama kecuali dirinya sendiri
    $cek = $db->conn->prepare("SELECT id FROM db_produk WHERE nama = ? AND id_penjual = ? AND id != ?");
    $cek->bind_param("sii", $nama, $penjual_id, $id);
    $cek->execute();
    if ($cek->get_result()->num_rows > 0) {
        $error = "Nama produk \"$nama\" sudah digunakan produk lain.";
    } else {
        if (!empty($_FILES['gambar']['name'])) {
            $gambar = '/uploads/' . basename($_FILES['gambar']['name']);
            move_uploaded_file($_FILES['gambar']['tmp_name'], $gambar);
        }

        $query = $db->conn->prepare("UPDATE db_produk SET nama = ?, deskripsi = ?, linkGambar = ?, harga = ? WHERE id = ? AND id_penjual = ?");
        $query->bind_param("sssiii", $nama, $deskripsi, $gambar, $harga, $id, $penjual_id);
        $query->execute();
        header("Location: kelola_produk.php");
        exit();
    }
}

// Hapus Produk
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    $query = $db->conn->prepare("DELETE FROM db_produk WHERE id = ? AND id_penjual = ?");
    $query->bind_param("ii", $id, $penjual_id);
    $query->execute();
    header("Location: kelola_produk.php");
    exit();
}

// Ambil semua produk penjual
$productsQuery = $db->conn->prepare("SELECT * FROM db_produk WHERE id_penjual = ?");
$productsQuery->bind_param("i", $penjual_id);
$productsQuery->execute();
$products = $productsQuery->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Kelola Produk (Penjual)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .image-preview {
            max-height: 100px;
            margin-top: 10px;
        }

        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        main {
            flex: 1;
        }

        footer {
            background-color: #343a40;
            color: white;
            text-align: center;
            padding: 10px 0;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">Panel Penjual</a>
            <div class="d-flex">
                <span class="navbar-text text-white me-3">Welcome, <?= htmlspecialchars($_SESSION['penjual_username']) ?></span>
                <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h1 class="text-center mb-4">Kelola Produk</h1>

        <?php if ($error): ?>
            <div class="alert alert-danger text-center"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- Tambah Produk -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title">Tambah Produk</h5>
                <form method="POST" enctype="multipart/form-data">
                    <div class="row g-2">
                        <div class="col-md-2"><input type="text" name="nama" class="form-control" placeholder="Nama Produk" required></div>
                        <div class="col-md-2"><input type="text" name="deskripsi" class="form-control" placeholder="Deskripsi"></div>
                        <div class="col-md-2">
                            <input type="file" name="gambar" class="form-control" accept="image/*" onchange="previewImage(event, 'add-preview')">
                            <img id="add-preview" class="image-preview" />
                        </div>
                        <div class="col-md-2"><input type="number" name="harga" class="form-control" placeholder="Harga" required></div>
                        <div class="col-md-3"><input type="text" class="form-control" value="<?= htmlspecialchars($nama_warung) ?>" readonly></div>
                        <div class="col-md-1"><button type="submit" name="add_product" class="btn btn-success w-100">Tambah</button></div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Daftar Produk -->
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Daftar Produk</h5>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Deskripsi</th>
                            <th>Gambar</th>
                            <th>Harga</th>
                            <th>Nama Warung</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $i => $p): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><?= htmlspecialchars($p['nama']) ?></td>
                                <td><?= htmlspecialchars($p['deskripsi']) ?></td>
                                <td>
                                    <?php if (!empty($p['linkGambar'])): ?>
                                        <img src="../<?= htmlspecialchars($p['linkGambar']) ?>" style="max-height: 100px;">
                                        <?php else: ?>Tidak ada gambar<?php endif; ?>
                                </td>
                                <td>Rp <?= number_format($p['harga'], 0, ',', '.') ?></td>
                                <td><?= htmlspecialchars($nama_warung) ?></td>
                                <td>
                                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?= $p['id'] ?>">Edit</button>
                                    <a href="kelola_produk.php?delete_id=<?= $p['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus produk ini?')">Hapus</a>

                                    <!-- Modal -->
                                    <div class="modal fade" id="editModal<?= $p['id'] ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form method="POST" enctype="multipart/form-data">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Edit Produk</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                                        <input type="hidden" name="existing_image" value="<?= htmlspecialchars($p['linkGambar']) ?>">
                                                        <div class="mb-3"><label>Nama Produk</label><input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($p['nama']) ?>" required></div>
                                                        <div class="mb-3"><label>Deskripsi</label><textarea name="deskripsi" class="form-control"><?= htmlspecialchars($p['deskripsi']) ?></textarea></div>
                                                        <div class="mb-3">
                                                            <label>Gambar</label>
                                                            <input type="file" name="gambar" class="form-control" accept="image/*" onchange="previewImage(event, 'edit-preview-<?= $p['id'] ?>')">
                                                            <img id="edit-preview-<?= $p['id'] ?>" class="image-preview" src="../<?= htmlspecialchars($p['linkGambar']) ?>">
                                                        </div>
                                                        <div class="mb-3"><label>Harga</label><input type="number" name="harga" class="form-control" value="<?= $p['harga'] ?>" required></div>
                                                        <div class="mb-3"><label>Nama Warung</label><input type="text" class="form-control" value="<?= htmlspecialchars($nama_warung) ?>" readonly></div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" name="edit_product" class="btn btn-primary">Simpan</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function previewImage(event, id) {
            const reader = new FileReader();
            reader.onload = () => document.getElementById(id).src = reader.result;
            reader.readAsDataURL(event.target.files[0]);
        }
    </script>
</body>

</html>