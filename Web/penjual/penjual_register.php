<?php
require_once '../db_connection.php';
session_start();

$db = new DBConnection();
$error = null;
$success = null;

if (isset($_POST['register'])) {
    $nama_warung = htmlspecialchars($_POST['nama_warung']);
    $username = htmlspecialchars($_POST['username']);
    $password = $_POST['password']; // tanpa hash

    // Cek apakah username sudah dipakai
    $cek = $db->conn->prepare("SELECT id FROM db_penjual WHERE username = ?");
    $cek->bind_param("s", $username);
    $cek->execute();
    $result = $cek->get_result();

    if ($result->num_rows > 0) {
        $error = "Username sudah digunakan. Silakan pilih username lain.";
    } else {
        // Simpan ke database tanpa hashing
        $stmt = $db->conn->prepare("INSERT INTO db_penjual (nama_warung, username, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $nama_warung, $username, $password);
        if ($stmt->execute()) {
            $success = "Pendaftaran berhasil! Silakan login.";
            header("Location: penjual_login.php");
            exit();
        } else {
            $error = "Terjadi kesalahan saat mendaftar.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Register Penjual</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light d-flex justify-content-center align-items-center vh-100">
    <div class="card shadow p-4" style="width: 420px;">
        <h3 class="text-center mb-4">Register Penjual</h3>
        <?php if ($error): ?>
            <div class="alert alert-danger text-center"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success text-center"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Nama Warung</label>
                <input type="text" name="nama_warung" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" name="register" class="btn btn-success w-100">Daftar</button>
            <div class="text-center mt-3">
                Sudah punya akun? <a href="penjual_login.php">Login di sini</a>
            </div>
        </form>
    </div>
</body>

</html>