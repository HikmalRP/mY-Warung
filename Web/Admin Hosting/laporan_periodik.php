<?php
session_start();
require_once 'db_connection.php';

// Cek apakah admin sudah login
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

// Instansiasi koneksi database
$db = new DBConnection();
$sales = [];

// Filter Laporan Berdasarkan Periode
if (isset($_POST['filter'])) {
    $start_date = $_POST['start_date'] . " 00:00:00"; // Waktu awal hari
    $end_date = $_POST['end_date'] . " 23:59:59"; // Waktu akhir hari

    $query = $db->conn->prepare("SELECT dj.*, du.username FROM db_jual dj JOIN db_user du ON dj.user_id = du.id WHERE dj.created_at BETWEEN ? AND ? ORDER BY dj.created_at DESC");
    $query->bind_param("ss", $start_date, $end_date);
    $query->execute();
    $result = $query->get_result();
    $sales = $result->fetch_all(MYSQLI_ASSOC);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Periodik</title>
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
            <a class="navbar-brand" href="index.php">Admin Panel</a>
            <div class="d-flex">
                <span class="navbar-text text-white me-3">Welcome, <?= $_SESSION['admin_username'] ?></span>
                <a href="admin_login.php" class="btn btn-danger btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Laporan Periodik Content -->
    <main class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="text-center">Laporan Penjualan Periodik</h1>
            <a href="index.php" class="btn btn-secondary">Kembali ke Dashboard</a>
        </div>

        <!-- Filter Form -->
        <form method="POST" class="row g-3 mb-4">
            <div class="col-md-5">
                <label for="start_date" class="form-label">Dari Tanggal</label>
                <input type="date" id="start_date" name="start_date" class="form-control" required>
            </div>
            <div class="col-md-5">
                <label for="end_date" class="form-label">Sampai Tanggal</label>
                <input type="date" id="end_date" name="end_date" class="form-control" required>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" name="filter" class="btn btn-primary w-100">Filter</button>
            </div>
        </form>

        <!-- Tabel Penjualan -->
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title mb-4">Daftar Penjualan</h5>
                <div class="mb-3">
                    <?php if (!empty($sales)): ?>
                        <a href="export_excel_periodik.php?start_date=<?= $_POST['start_date'] ?>&end_date=<?= $_POST['end_date'] ?>" class="btn btn-success">Export Excel</a>
                        <a href="export_pdf_periodik.php?start_date=<?= $_POST['start_date'] ?>&end_date=<?= $_POST['end_date'] ?>" class="btn btn-danger">Export PDF</a>
                    <?php endif; ?>
                </div>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Username</th>
                            <th>Asal</th>
                            <th>Tujuan</th>
                            <th>Kurir</th>
                            <th>Total Transaksi</th>
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
                                    <td>Rp <?= number_format($sale['total_with_shipping'], 0, ',', '.') ?></td>
                                    <td><?= htmlspecialchars($sale['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">Belum ada data penjualan untuk periode ini.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

</body>
</html>
