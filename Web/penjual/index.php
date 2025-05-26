<?php
session_start();
require_once '../db_connection.php';

// Cek apakah penjual sudah login
require_once 'auth_penjual.php';

$db = new DBConnection();
$penjual_id = $_SESSION['penjual_id'];

// Total Produk Penjual
$totalProduk = $db->conn->query("SELECT COUNT(*) AS total FROM db_produk WHERE id_penjual = $penjual_id")->fetch_assoc()['total'];

// Ambil semua nama produk milik penjual
$produkQuery = $db->conn->prepare("SELECT nama FROM db_produk WHERE id_penjual = ?");
$produkQuery->bind_param("i", $penjual_id);
$produkQuery->execute();
$produkResult = $produkQuery->get_result();
$namaProduks = [];
while ($row = $produkResult->fetch_assoc()) {
    $namaProduks[] = $row['nama'];
}

// Cek semua transaksi dan cocokkan produk
$penjualanQuery = $db->conn->query("SELECT * FROM db_jual ORDER BY created_at DESC");
$totalPenjualan = 0;
$totalPendapatan = 0;

while ($jual = $penjualanQuery->fetch_assoc()) {
    $items = json_decode($jual['items'], true);
    if (!$items) continue;

    foreach ($items as $item) {
        if (in_array($item['nama'], $namaProduks)) {
            $totalPenjualan++;
            $totalPendapatan += $jual['total_with_shipping'];
            break; // hanya hitung 1x per transaksi
        }
    }
}

$totalPendapatan = $totalPendapatan ?? 0;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Penjual</title>
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
            <a class="navbar-brand" href="index.php">Panel Penjual</a>
            <div class="d-flex">
                <span class="navbar-text text-white me-3">Welcome, <?= htmlspecialchars($_SESSION['penjual_username']) ?></span>
                <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Dashboard Content -->
    <div class="content">
        <div class="container mt-4">
            <h1 class="text-center mb-4">Dashboard Penjual</h1>
            <div class="row g-4 justify-content-center">
                <!-- Kelola Produk -->
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="card text-center shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">Kelola Produk</h5>
                            <p class="card-text">Total Produk: <strong><?= $totalProduk ?></strong></p>
                            <a href="kelola_produk.php" class="btn btn-primary w-100">Lihat Produk</a>
                        </div>
                    </div>
                </div>

                <!-- Laporan Penjualan -->
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="card text-center shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">Laporan Penjualan</h5>
                            <p class="card-text">Total Transaksi: <strong><?= $totalPenjualan ?></strong></p>
                            <p class="card-text">Pendapatan: <strong>Rp <?= number_format($totalPendapatan, 0, ',', '.') ?></strong></p>
                            <a href="laporan_penjualan.php" class="btn btn-primary w-100">Lihat Laporan</a>
                        </div>
                    </div>
                </div>

                <!-- Laporan Periodik -->
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="card text-center shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">Laporan Periodik</h5>
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