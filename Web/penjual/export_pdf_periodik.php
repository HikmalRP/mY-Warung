<?php
require_once '../db_connection.php';
require_once 'auth_penjual.php';
require_once 'vendor/autoload.php';

$db = new DBConnection();
$penjual_id = $_SESSION['penjual_id'];
$start_date = $_GET['start_date'] . " 00:00:00";
$end_date = $_GET['end_date'] . " 23:59:59";

// Ambil nama dan harga produk milik penjual
$produkQuery = $db->conn->prepare("SELECT nama, harga FROM db_produk WHERE id_penjual = ?");
$produkQuery->bind_param("i", $penjual_id);
$produkQuery->execute();
$produkResult = $produkQuery->get_result();
$produkMap = []; // nama => harga
while ($row = $produkResult->fetch_assoc()) {
    $produkMap[$row['nama']] = $row['harga'];
}

// Ambil penjualan dalam rentang waktu
$query = $db->conn->prepare("SELECT dj.*, du.username FROM db_jual dj JOIN db_user du ON dj.user_id = du.id WHERE dj.created_at BETWEEN ? AND ? ORDER BY dj.created_at DESC");
$query->bind_param("ss", $start_date, $end_date);
$query->execute();
$result = $query->get_result();
$sales = [];

while ($jual = $result->fetch_assoc()) {
    $items = json_decode($jual['items'], true);
    if (!is_array($items)) continue;

    $subtotal = 0;
    $terlibat = false;

    foreach ($items as $item) {
        $nama = $item['nama'] ?? '';
        $jumlah = (int)($item['jumlah'] ?? 0);
        if (isset($produkMap[$nama])) {
            $subtotal += $produkMap[$nama] * $jumlah;
            $terlibat = true;
        }
    }

    if ($terlibat) {
        $jual['subtotal_penjual'] = $subtotal;
        $sales[] = $jual;
    }
}

// Buat PDF
$pdf = new TCPDF();
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Penjual');
$pdf->SetTitle('Laporan Penjualan Periodik');
$pdf->SetHeaderData('', 0, 'Laporan Periodik', '');
$pdf->setHeaderFont(['helvetica', '', 10]);
$pdf->setFooterFont(['helvetica', '', 8]);
$pdf->AddPage();

// Header
$html = '<h1 style="text-align:center;">Laporan Penjualan Periodik</h1>
<table border="1" cellspacing="3" cellpadding="4">
    <thead>
        <tr style="font-weight: bold; background-color: #f2f2f2;">
            <th>No</th>
            <th>Username</th>
            <th>Asal</th>
            <th>Tujuan</th>
            <th>Kurir</th>
            <th>Total Pendapatan</th>
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
        <td>Rp ' . number_format($sale['subtotal_penjual'], 0, ',', '.') . '</td>
        <td>' . htmlspecialchars($sale['created_at']) . '</td>
    </tr>';
}

$html .= '</tbody></table>';

// Tampilkan PDF
$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output('Laporan_Periodik.pdf', 'I');
exit;
