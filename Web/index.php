<?php session_start(); ?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Selamat Datang</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light d-flex justify-content-center align-items-center vh-100">
    <div class="card shadow p-4" style="width: 400px;">
        <h3 class="text-center mb-4">Selamat Datang di mY Warung</h3>
        <p class="text-center mb-4">Silakan login sebagai:</p>
        <div class="d-grid gap-3"> <a href="admin/admin_login.php" class="btn btn-primary btn-lg">Login sebagai Admin</a> <a href="penjual/penjual_login.php" class="btn btn-success btn-lg">Login sebagai Penjual</a> </div>
    </div>
</body>

</html>