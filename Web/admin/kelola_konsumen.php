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

// Tambah Konsumen
if (isset($_POST['add_user'])) {
    $username = $_POST['username'];
    $password = $_POST['password']; // Password tanpa hashing (sesuai permintaan)

    // Query untuk menambahkan konsumen
    $query = $db->conn->prepare("INSERT INTO db_user (username, password) VALUES (?, ?)");
    $query->bind_param("ss", $username, $password); // Parameter: string, string
    $query->execute();
    header("Location: kelola_konsumen.php");
    exit();
}

// Edit Konsumen
if (isset($_POST['edit_user'])) {
    $id = $_POST['id'];
    $username = $_POST['username'];
    $password = $_POST['password']; // Password tanpa hashing

    // Query untuk mengupdate konsumen
    $query = $db->conn->prepare("UPDATE db_user SET username = ?, password = ? WHERE id = ?");
    $query->bind_param("ssi", $username, $password, $id); // Parameter: string, string, integer
    $query->execute();
    header("Location: kelola_konsumen.php");
    exit();
}

// Hapus Konsumen
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];

    // Query untuk menghapus konsumen
    $query = $db->conn->prepare("DELETE FROM db_user WHERE id = ?");
    $query->bind_param("i", $id); // Parameter: integer
    $query->execute();
    header("Location: kelola_konsumen.php");
    exit();
}

// Ambil semua data konsumen
$usersQuery = $db->conn->query("SELECT * FROM db_user");
$users = $usersQuery->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Konsumen</title>
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
        // Reset nilai modal edit saat modal dibuka
        function resetEditModal(userId, username, password) {
            document.getElementById('edit-username-' + userId).value = username;
            document.getElementById('edit-password-' + userId).value = password;
        }
    </script>
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

    <!-- Kelola Konsumen Content -->
    <div class="container mt-4">
        <h1 class="text-center mb-4">Kelola Konsumen</h1>

        <!-- Tambah Konsumen -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title">Tambah Konsumen</h5>
                <form method="POST">
                    <div class="row g-2">
                        <div class="col-md-4">
                            <input type="text" name="username" class="form-control" placeholder="Username" required>
                        </div>
                        <div class="col-md-4">
                            <input type="password" name="password" class="form-control" placeholder="Password" required>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" name="add_user" class="btn btn-success w-100">Tambah</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabel Konsumen -->
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Daftar Konsumen</h5>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Username</th>
                            <th>Password</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $index => $user): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($user['password'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <!-- Edit Button -->
                                    <button
                                        class="btn btn-warning btn-sm"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editModal<?= $user['id'] ?>"
                                        onclick="resetEditModal(<?= $user['id'] ?>, '<?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?>', '<?= htmlspecialchars($user['password'], ENT_QUOTES, 'UTF-8') ?>')"
                                    >
                                        Edit
                                    </button>

                                    <!-- Hapus Button -->
                                    <a href="kelola_konsumen.php?delete_id=<?= $user['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus konsumen ini?')">Hapus</a>

                                    <!-- Modal Edit -->
                                    <div class="modal fade" id="editModal<?= $user['id'] ?>" tabindex="-1" aria-labelledby="editModalLabel<?= $user['id'] ?>" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form method="POST">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="editModalLabel<?= $user['id'] ?>">Edit Konsumen</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <input type="hidden" name="id" value="<?= $user['id'] ?>">
                                                        <div class="mb-3">
                                                            <label for="username" class="form-label">Username</label>
                                                            <input type="text" name="username" id="edit-username-<?= $user['id'] ?>" class="form-control" value="<?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?>" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label for="password" class="form-label">Password</label>
                                                            <input type="password" name="password" id="edit-password-<?= $user['id'] ?>" class="form-control" value="<?= htmlspecialchars($user['password'], ENT_QUOTES, 'UTF-8') ?>" required>
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

