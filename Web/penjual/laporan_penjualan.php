<?php
session_start();
require_once '../db_connection.php';
require_once 'auth_penjual.php';

$db = new DBConnection();
$id_penjual = $_SESSION['penjual_id'];

// Ambil produk milik penjual
$produkQuery = $db->conn->prepare("SELECT nama, harga FROM db_produk WHERE id_penjual = ?");
$produkQuery->bind_param("i", $id_penjual);
$produkQuery->execute();
$produkResult = $produkQuery->get_result();

$produkMap = []; // key: nama_produk, value: harga
while ($row = $produkResult->fetch_assoc()) {
    $produkMap[$row['nama']] = $row['harga'];
}

// Ambil seluruh transaksi dan filter yang terkait dengan penjual
$rawQuery = $db->conn->query("SELECT dj.*, du.username FROM db_jual dj JOIN db_user du ON dj.user_id = du.id ORDER BY dj.created_at DESC");
$sales = [];

while ($jual = $rawQuery->fetch_assoc()) {
    $items = json_decode($jual['items'], true);
    if (!is_array($items)) continue;

    $subtotal = 0;
    $terlibat = false;

    foreach ($items as $item) {
        $nama = $item['nama'] ?? '';
        $jumlah = (int) ($item['jumlah'] ?? 0);
        if (isset($produkMap[$nama])) {
            $terlibat = true;
            $subtotal += $produkMap[$nama] * $jumlah;
        }
    }

    if ($terlibat) {
        $jual['subtotal_penjual'] = $subtotal;
        $sales[] = $jual;
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Penjualan (Penjual)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
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
            <a class="navbar-brand" href="index.php">Panel Penjual</a>
            <div class="d-flex">
                <span class="navbar-text text-white me-3">Welcome, <?= htmlspecialchars($_SESSION['penjual_username']) ?></span>
                <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Laporan Penjualan Content -->
    <main class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="text-center">Laporan Penjualan</h1>
            <a href="index.php" class="btn btn-secondary">Kembali ke Dashboard</a>
        </div>

        <!-- Tombol Ekspor -->
        <div class="mb-3">
            <a href="export_excel.php" class="btn btn-success">Export Excel</a>
            <a href="export_pdf.php" class="btn btn-danger">Export PDF</a>
        </div>

        <!-- Tabel Penjualan -->
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title mb-4">Daftar Penjualan</h5>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Username</th>
                            <th>Asal</th>
                            <th>Tujuan</th>
                            <th>Kurir</th>
                            <th>Total Pendapatan</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($sales) > 0): ?>
                            <?php foreach ($sales as $index => $sale): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td><?= htmlspecialchars($sale['username'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($sale['origin'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($sale['destination'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($sale['courier'], ENT_QUOTES, 'UTF-8') ?> - <?= htmlspecialchars($sale['service'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td>Rp <?= number_format($sale['subtotal_penjual'], 0, ',', '.') ?></td>
                                    <td><?= htmlspecialchars($sale['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">Belum ada data penjualan.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>

</html>