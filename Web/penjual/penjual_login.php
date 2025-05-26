<?php
session_start();
require_once '../db_connection.php';

$db = new DBConnection();
$error = null;

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $query = $db->conn->prepare("SELECT * FROM db_penjual WHERE username = ?");
    $query->bind_param("s", $username);
    $query->execute();
    $result = $query->get_result();
    $penjual = $result->fetch_assoc();

    if ($penjual && $password === $penjual['password']) {
        session_regenerate_id(true); // Cegah session hijacking
        $_SESSION['penjual_logged_in'] = true;
        $_SESSION['penjual_id'] = $penjual['id'];
        $_SESSION['penjual_username'] = $penjual['username'];
        header("Location: index.php");
        exit();
    } else {
        $error = "Username atau password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Login Penjual</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light d-flex justify-content-center align-items-center vh-100">
    <div class="card shadow p-4" style="width: 400px;">
        <h3 class="text-center mb-4">Login Penjual</h3>
        <?php if ($error): ?>
            <div class="alert alert-danger text-center"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" name="login" class="btn btn-primary w-100">Login</button>
            <div class="text-center mt-3">
                Belum punya akun? <a href="penjual_register.php">Register di sini</a>
            </div>
            <div class="text-center mt-3">
                <a href="../index.php">Kembali</a>
            </div>
        </form>
    </div>
</body>

</html>