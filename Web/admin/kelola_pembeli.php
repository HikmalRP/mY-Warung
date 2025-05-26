<?php
require_once '../db_connection.php';
session_start();

// Cek apakah admin sudah login
require_once 'auth_admin.php';

$db = new DBConnection();
$error = null;

// Tambah Pembeli
if (isset($_POST['add_user'])) {
    $nama = $_POST['nama'];
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Cek duplikat username
    $cek = $db->conn->prepare("SELECT id FROM db_user WHERE username = ?");
    $cek->bind_param("s", $username);
    $cek->execute();
    $cek_result = $cek->get_result();
    if ($cek_result->num_rows > 0) {
        $error = "Username \"$username\" sudah digunakan.";
    } else {
        $query = $db->conn->prepare("INSERT INTO db_user (nama, username, password) VALUES (?, ?, ?)");
        $query->bind_param("sss", $nama, $username, $password);
        $query->execute();
        header("Location: kelola_pembeli.php");
        exit();
    }
}

// Edit Pembeli
if (isset($_POST['edit_user'])) {
    $id = $_POST['id'];
    $nama = $_POST['nama'];
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Cek duplikat username selain dirinya sendiri
    $cek = $db->conn->prepare("SELECT id FROM db_user WHERE username = ? AND id != ?");
    $cek->bind_param("si", $username, $id);
    $cek->execute();
    $cek_result = $cek->get_result();
    if ($cek_result->num_rows > 0) {
        $error = "Username \"$username\" sudah digunakan oleh pengguna lain.";
    } else {
        $query = $db->conn->prepare("UPDATE db_user SET nama = ?, username = ?, password = ? WHERE id = ?");
        $query->bind_param("sssi", $nama, $username, $password, $id);
        $query->execute();
        header("Location: kelola_pembeli.php");
        exit();
    }
}

// Hapus Pembeli
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    $query = $db->conn->prepare("DELETE FROM db_user WHERE id = ?");
    $query->bind_param("i", $id);
    $query->execute();
    header("Location: kelola_pembeli.php");
    exit();
}

// Ambil semua data Pembeli
$usersQuery = $db->conn->query("SELECT * FROM db_user");
$users = $usersQuery->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Kelola Pembeli (Admin)</title>
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
    <script>
        function resetEditModal(userId, nama, username, password) {
            document.getElementById('edit-nama-' + userId).value = nama;
            document.getElementById('edit-username-' + userId).value = username;
            document.getElementById('edit-password-' + userId).value = password;
        }
    </script>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">Admin Panel</a>
            <div class="d-flex">
                <span class="navbar-text text-white me-3">Welcome, <?= $_SESSION['admin_username'] ?></span>
                <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h1 class="text-center mb-4">Kelola Pembeli</h1>

        <?php if ($error): ?>
            <div class="alert alert-danger text-center"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- Tambah Pembeli -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title">Tambah Pembeli</h5>
                <form method="POST">
                    <div class="row g-2">
                        <div class="col-md-3">
                            <input type="text" name="nama" class="form-control" placeholder="Nama" required>
                        </div>
                        <div class="col-md-3">
                            <input type="text" name="username" class="form-control" placeholder="Username" required>
                        </div>
                        <div class="col-md-3">
                            <input type="password" name="password" class="form-control" placeholder="Password" required>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" name="add_user" class="btn btn-success w-100">Tambah</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabel Pembeli -->
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Daftar Pembeli</h5>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Username</th>
                            <th>Password</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $index => $user): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= htmlspecialchars($user['nama'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($user['password'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <button class="btn btn-warning btn-sm"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editModal<?= $user['id'] ?>"
                                        onclick="resetEditModal(<?= $user['id'] ?>, '<?= htmlspecialchars($user['nama'], ENT_QUOTES, 'UTF-8') ?>', '<?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?>', '<?= htmlspecialchars($user['password'], ENT_QUOTES, 'UTF-8') ?>')">
                                        Edit
                                    </button>
                                    <a href="kelola_pembeli.php?delete_id=<?= $user['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus Pembeli ini?')">Hapus</a>

                                    <!-- Modal Edit -->
                                    <div class="modal fade" id="editModal<?= $user['id'] ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form method="POST">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Edit Pembeli</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <input type="hidden" name="id" value="<?= $user['id'] ?>">
                                                        <div class="mb-3">
                                                            <label class="form-label">Nama</label>
                                                            <input type="text" name="nama" id="edit-nama-<?= $user['id'] ?>" class="form-control" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Username</label>
                                                            <input type="text" name="username" id="edit-username-<?= $user['id'] ?>" class="form-control" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Password</label>
                                                            <input type="password" name="password" id="edit-password-<?= $user['id'] ?>" class="form-control" required>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" name="edit_user" class="btn btn-primary">Simpan</button>
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