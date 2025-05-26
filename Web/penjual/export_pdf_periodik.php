<?php
require_once '../db_connection.php';
require_once 'auth_penjual.php';
require_once 'vendor/autoload.php';

$db = new DBConnection();
$penjual_id = $_SESSION['penjual_id'];
$start_date = $_GET['start_date'] . " 00:00:00";
$end_date = $_GET['end_date'] . " 23:59:59";

// Ambil nama produk milik penjual
$produkQuery = $db->conn->prepare("SELECT nama FROM db_produk WHERE id_penjual = ?");
$produkQuery->bind_param("i", $penjual_id);
$produkQuery->execute();
$produkResult = $produkQuery->get_result();
$produkNama = [];
while ($row = $produkResult->fetch_assoc()) {
    $produkNama[] = $row['nama'];
}

// Ambil penjualan dalam rentang waktu
$query = $db->conn->prepare("SELECT dj.*, du.username FROM db_jual dj JOIN db_user du ON dj.user_id = du.id WHERE dj.created_at BETWEEN ? AND ? ORDER BY dj.created_at DESC");
$query->bind_param("ss", $start_date, $end_date);
$query->execute();
$result = $query->get_result();
$allSales = $result->fetch_all(MYSQLI_ASSOC);

// Filter hanya penjualan yang mengandung produk milik penjual
$sales = [];
foreach ($allSales as $sale) {
    $items = json_decode($sale['items'], true);
    if (!is_array($items)) continue;

    foreach ($items as $item) {
        if (in_array($item['nama'], $produkNama)) {
            $sales[] = $sale;
            break;
        }
    }
}

// Buat PDF
$pdf = new TCPDF();
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Admin');
$pdf->SetTitle('Laporan Penjualan Periodik');
$pdf->SetHeaderData('', 0, 'Laporan Periodik', '');
$pdf->setHeaderFont(['helvetica', '', 10]);
$pdf->setFooterFont(['helvetica', '', 8]);
$pdf->AddPage();

// Tabel Header
$html = '<h1>Laporan Penjualan Periodik</h1>
<table border="1" cellspacing="3" cellpadding="4">
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
    <tbody>';

// Isi Data
foreach ($sales as $index => $sale) {
    $html .= '<tr>
        <td>' . ($index + 1) . '</td>
        <td>' . htmlspecialchars($sale['username']) . '</td>
        <td>' . htmlspecialchars($sale['origin']) . '</td>
        <td>' . htmlspecialchars($sale['destination']) . '</td>
        <td>' . htmlspecialchars($sale['courier']) . ' - ' . htmlspecialchars($sale['service']) . '</td>
        <td>' . number_format($sale['total_with_shipping'], 0, ',', '.') . '</td>
        <td>' . htmlspecialchars($sale['created_at']) . '</td>
    </tr>';
}

$html .= '</tbody></table>';

// Tampilkan PDF
$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output('Laporan_Periodik.pdf', 'I');
exit;
