<?php
session_start();
require_once 'db_connection.php';

// Membuat instance DBConnection
$db = new DBConnection();

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Menggunakan prepared statement untuk mencegah SQL injection
    $query = $db->conn->prepare("SELECT * FROM db_admin WHERE username = ?");
    $query->bind_param("s", $username);
    $query->execute();
    $result = $query->get_result();
    $admin = $result->fetch_assoc();

    if ($admin && $password === $admin['password']) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $admin['username'];
        header("Location: index.php");
        exit();
    } else {
        $error = "Username atau password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex justify-content-center align-items-center vh-100">
    <div class="card shadow p-4" style="width: 400px;">
        <h3 class="text-center mb-4">Admin Login</h3>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger text-center"><?= $error ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" name="login" class="btn btn-primary w-100">Login</button>
        </form>
    </div>
</body>
</html>
