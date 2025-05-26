<?php
session_start();
require_once '../db_connection.php';

// Cek apakah admin sudah login
require_once 'auth_admin.php';
// Jika belum login, redirect ke halaman login

// Buat koneksi database
$db = new DBConnection();

// Total Produk
$totalProduk = $db->conn->query("SELECT COUNT(*) AS total FROM db_produk")->fetch_assoc()['total'];

// Total Pembeli
$totalPembeli = $db->conn->query("SELECT COUNT(*) AS total FROM db_user")->fetch_assoc()['total'];

// Total Penjual
$totalPenjual = $db->conn->query("SELECT COUNT(*) AS total FROM db_penjual")->fetch_assoc()['total'];

// Total Transaksi Penjualan
$totalPenjualan = $db->conn->query("SELECT COUNT(*) AS total FROM db_jual")->fetch_assoc()['total'];

// Total Pendapatan
$totalPendapatan = $db->conn->query("SELECT SUM(total_with_shipping) AS total FROM db_jual")->fetch_assoc()['total'];
$totalPendapatan = $totalPendapatan ?? 0; // Null jika tidak ada data
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
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

    <!-- Dashboard Content -->
    <div class="content">
        <div class="container mt-4">
            <h1 class="text-center mb-4">Admin Dashboard</h1>
            <div class="row g-4 justify-content-center">
                <!-- Kelola Produk -->
                <div class="col-12 col-md-6 col-lg-2">
                    <div class="card text-center shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">Kelola Produk</h5>
                            <p class="card-text">Total Produk: <strong><?= $totalProduk ?></strong></p>
                            <a href="kelola_produk.php" class="btn btn-primary w-100">Lihat Produk</a>
                        </div>
                    </div>
                </div>

                <!-- Kelola Pembeli -->
                <div class="col-12 col-md-6 col-lg-2">
                    <div class="card text-center shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">Kelola Pembeli</h5>
                            <p class="card-text">Total Pembeli: <strong><?= $totalPembeli ?></strong></p>
                            <a href="kelola_pembeli.php" class="btn btn-primary w-100">Lihat Pembeli</a>
                        </div>
                    </div>
                </div>

                <!-- Kelola Penjual -->
                <div class="col-12 col-md-6 col-lg-2">
                    <div class="card text-center shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">Kelola Penjual</h5>
                            <p class="card-text">Total Penjual: <strong><?= $totalPenjual ?></strong></p>
                            <a href="kelola_penjual.php" class="btn btn-primary w-100">Lihat Penjual</a>
                        </div>
                    </div>
                </div>

                <!-- Laporan Penjualan Global -->
                <div class="col-12 col-md-6 col-lg-2">
                    <div class="card text-center shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">Laporan Penjualan Global</h5>
                            <p class="card-text">Total Transaksi: <strong><?= $totalPenjualan ?></strong></p>
                            <p class="card-text">Pendapatan: <strong>Rp <?= number_format($totalPendapatan, 0, ',', '.') ?></strong></p>
                            <a href="laporan_penjualan.php" class="btn btn-primary w-100">Lihat Laporan</a>
                        </div>
                    </div>
                </div>

                <!-- Laporan Periodik -->
                <div class="col-12 col-md-6 col-lg-2">
                    <div class="card text-center shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">Laporan Periodik Global</h5>
                            <p class="card-text">Lihat data berdasarkan periode</p>
                            <a href="laporan_periodik.php" class="btn btn-primary w-100">Lihat Periodik</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer mt-4">
        <p class="mb-0">&copy; <?= date('Y') ?> mY Warung</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>