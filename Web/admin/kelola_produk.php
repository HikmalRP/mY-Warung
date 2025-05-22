<?php
require_once 'db_connection.php';
session_start();

// Cek apakah admin sudah login
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

// Instansiasi objek DBConnection
$db = new DBConnection();

// Tambah Produk
if (isset($_POST['add_product'])) {
    $nama = $_POST['nama'];
    $deskripsi = $_POST['deskripsi'];
    $harga = $_POST['harga'];

    // Upload gambar
    $gambar = null;
    if (!empty($_FILES['gambar']['name'])) {
        $gambar = 'uploads/' . basename($_FILES['gambar']['name']);
        move_uploaded_file($_FILES['gambar']['tmp_name'], $gambar);
    }

    // Query untuk menambahkan produk
    $query = $db->conn->prepare("INSERT INTO db_produk (nama, deskripsi, linkGambar, harga) VALUES (?, ?, ?, ?)");
    $query->bind_param("sssi", $nama, $deskripsi, $gambar, $harga);
    $query->execute();
    header("Location: kelola_produk.php");
    exit();
}

// Edit Produk
if (isset($_POST['edit_product'])) {
    $id = $_POST['id'];
    $nama = $_POST['nama'];
    $deskripsi = $_POST['deskripsi'];
    $harga = $_POST['harga'];

    // Upload gambar jika diupdate
    $gambar = $_POST['existing_image'];
    if (!empty($_FILES['gambar']['name'])) {
        $gambar = 'uploads/' . basename($_FILES['gambar']['name']);
        move_uploaded_file($_FILES['gambar']['tmp_name'], $gambar);
    }

    // Query untuk mengupdate produk
    $query = $db->conn->prepare("UPDATE db_produk SET nama = ?, deskripsi = ?, linkGambar = ?, harga = ? WHERE id = ?");
    $query->bind_param("sssii", $nama, $deskripsi, $gambar, $harga, $id);
    $query->execute();
    header("Location: kelola_produk.php");
    exit();
}

// Hapus Produk
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];

    // Query untuk menghapus produk
    $query = $db->conn->prepare("DELETE FROM db_produk WHERE id = ?");
    $query->bind_param("i", $id);
    $query->execute();
    header("Location: kelola_produk.php");
    exit();
}

// Ambil semua data produk
$productsQuery = $db->conn->query("SELECT * FROM db_produk");
$products = $productsQuery->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Produk</title>
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
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">Admin Panel</a>
            <div class="d-flex">
                <span class="navbar-text text-white me-3">Welcome, <?= $_SESSION['admin_username'] ?></span>
                <a href="admin_login.php" class="btn btn-danger btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Kelola Produk Content -->
    <div class="container mt-4">
        <h1 class="text-center mb-4">Kelola Produk</h1>

        <!-- Tambah Produk -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title">Tambah Produk</h5>
                <form method="POST" enctype="multipart/form-data">
                    <div class="row g-2">
                        <div class="col-md-3">
                            <input type="text" name="nama" class="form-control" placeholder="Nama Produk" required>
                        </div>
                        <div class="col-md-3">
                            <input type="text" name="deskripsi" class="form-control" placeholder="Deskripsi Produk">
                        </div>
                        <div class="col-md-3">
                            <input type="file" name="gambar" class="form-control" accept="image/*" onchange="previewImage(event, 'add-image-preview')">
                            <img id="add-image-preview" class="image-preview" />
                        </div>
                        <div class="col-md-2">
                            <input type="number" name="harga" class="form-control" placeholder="Harga Produk" required>
                        </div>
                        <div class="col-md-1">
                            <button type="submit" name="add_product" class="btn btn-success w-100">Tambah</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabel Produk -->
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Daftar Produk</h5>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Produk</th>
                            <th>Deskripsi</th>
                            <th>Gambar</th>
                            <th>Harga</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $index => $product): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= htmlspecialchars($product['nama'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($product['deskripsi'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <?php if (!empty($product['linkGambar'])): ?>
                                        <img src="<?= htmlspecialchars($product['linkGambar'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($product['nama'], ENT_QUOTES, 'UTF-8') ?>" style="max-height: 100px;">
                                    <?php else: ?>
                                        Tidak ada gambar
                                    <?php endif; ?>
                                </td>
                                <td>Rp <?= number_format($product['harga'], 0, ',', '.') ?></td>
                                <td>
                                    <!-- Edit Button -->
                                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?= $product['id'] ?>">Edit</button>

                                    <!-- Hapus Button -->
                                    <a href="kelola_produk.php?delete_id=<?= $product['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus produk ini?')">Hapus</a>

                                    <!-- Modal Edit -->
                                    <div class="modal fade" id="editModal<?= $product['id'] ?>" tabindex="-1" aria-labelledby="editModalLabel<?= $product['id'] ?>" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form method="POST" enctype="multipart/form-data">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="editModalLabel<?= $product['id'] ?>">Edit Produk</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <input type="hidden" name="id" value="<?= $product['id'] ?>">
                                                        <input type="hidden" name="existing_image" value="<?= htmlspecialchars($product['linkGambar'], ENT_QUOTES, 'UTF-8') ?>">
                                                        <div class="mb-3">
                                                            <label for="nama" class="form-label">Nama Produk</label>
                                                            <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($product['nama'], ENT_QUOTES, 'UTF-8') ?>" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label for="deskripsi" class="form-label">Deskripsi Produk</label>
                                                            <textarea name="deskripsi" class="form-control"><?= htmlspecialchars($product['deskripsi'], ENT_QUOTES, 'UTF-8') ?></textarea>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label for="gambar" class="form-label">Gambar</label>
                                                            <input type="file" name="gambar" class="form-control" accept="image/*" onchange="previewImage(event, 'edit-image-preview-<?= $product['id'] ?>')">
                                                            <img id="edit-image-preview-<?= $product['id'] ?>" class="image-preview" src="<?= htmlspecialchars($product['linkGambar'], ENT_QUOTES, 'UTF-8') ?>" />
                                                        </div>
                                                        <div class="mb-3">
                                                            <label for="harga" class="form-label">Harga Produk</label>
                                                            <input type="number" name="harga" class="form-control" value="<?= $product['harga'] ?>" required>
                                                        </div>
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
        function previewImage(event, previewId) {
            const reader = new FileReader();
            reader.onload = function () {
                const output = document.getElementById(previewId);
                output.src = reader.result;
            };
            reader.readAsDataURL(event.target.files[0]);
        }
    </script>
</body>
</html>
