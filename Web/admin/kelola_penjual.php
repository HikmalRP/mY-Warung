<?php
require_once '../db_connection.php';
session_start();

// Cek apakah admin sudah login
require_once 'auth_admin.php';

$db = new DBConnection();
$error = null;

// Tambah Penjual
if (isset($_POST['add_penjual'])) {
    $nama_warung = $_POST['nama_warung'];
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Cek username duplikat
    $cek = $db->conn->prepare("SELECT id FROM db_penjual WHERE username = ?");
    $cek->bind_param("s", $username);
    $cek->execute();
    $cek_result = $cek->get_result();
    if ($cek_result->num_rows > 0) {
        $error = "Username \"$username\" sudah digunakan.";
    } else {
        $query = $db->conn->prepare("INSERT INTO db_penjual (nama_warung, username, password) VALUES (?, ?, ?)");
        $query->bind_param("sss", $nama_warung, $username, $password);
        $query->execute();
        header("Location: kelola_penjual.php");
        exit();
    }
}

// Edit Penjual
if (isset($_POST['edit_penjual'])) {
    $id = $_POST['id'];
    $nama_warung = $_POST['nama_warung'];
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Cek username duplikat (kecuali dirinya sendiri)
    $cek = $db->conn->prepare("SELECT id FROM db_penjual WHERE username = ? AND id != ?");
    $cek->bind_param("si", $username, $id);
    $cek->execute();
    $cek_result = $cek->get_result();
    if ($cek_result->num_rows > 0) {
        $error = "Username \"$username\" sudah digunakan oleh penjual lain.";
    } else {
        $query = $db->conn->prepare("UPDATE db_penjual SET nama_warung = ?, username = ?, password = ? WHERE id = ?");
        $query->bind_param("sssi", $nama_warung, $username, $password, $id);
        $query->execute();
        header("Location: kelola_penjual.php");
        exit();
    }
}

// Hapus Penjual
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    $query = $db->conn->prepare("DELETE FROM db_penjual WHERE id = ?");
    $query->bind_param("i", $id);
    $query->execute();
    header("Location: kelola_penjual.php");
    exit();
}

// Ambil semua data penjual
$penjualQuery = $db->conn->query("SELECT * FROM db_penjual");
$penjualList = $penjualQuery->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Kelola Penjual (Admin)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .content {
            flex: 1;
        }

        .footer {
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
                <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Kelola Penjual Content -->
    <div class="container mt-4">
        <h1 class="text-center mb-4">Kelola Penjual</h1>

        <?php if ($error): ?>
            <div class="alert alert-danger text-center"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- Tambah Penjual -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title">Tambah Penjual</h5>
                <form method="POST">
                    <div class="row g-2">
                        <div class="col-md-3">
                            <input type="text" name="nama_warung" class="form-control" placeholder="Nama Warung" required>
                        </div>
                        <div class="col-md-3">
                            <input type="text" name="username" class="form-control" placeholder="Username" required>
                        </div>
                        <div class="col-md-3">
                            <input type="password" name="password" class="form-control" placeholder="Password" required>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" name="add_penjual" class="btn btn-success w-100">Tambah</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabel Penjual -->
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Daftar Penjual</h5>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Warung</th>
                            <th>Username</th>
                            <th>Password</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($penjualList as $index => $penjual): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= htmlspecialchars($penjual['nama_warung'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($penjual['username'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($penjual['password'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?= $penjual['id'] ?>">Edit</button>
                                    <a href="kelola_penjual.php?delete_id=<?= $penjual['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus penjual ini?')">Hapus</a>

                                    <!-- Modal Edit -->
                                    <div class="modal fade" id="editModal<?= $penjual['id'] ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form method="POST">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Edit Penjual</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <input type="hidden" name="id" value="<?= $penjual['id'] ?>">
                                                        <div class="mb-3">
                                                            <label class="form-label">Nama Warung</label>
                                                            <input type="text" name="nama_warung" class="form-control" value="<?= htmlspecialchars($penjual['nama_warung'], ENT_QUOTES, 'UTF-8') ?>" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Username</label>
                                                            <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($penjual['username'], ENT_QUOTES, 'UTF-8') ?>" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Password</label>
                                                            <input type="password" name="password" class="form-control" value="<?= htmlspecialchars($penjual['password'], ENT_QUOTES, 'UTF-8') ?>" required>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" name="edit_penjual" class="btn btn-primary">Simpan</button>
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
</body>

</html>