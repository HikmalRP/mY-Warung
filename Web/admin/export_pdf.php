<?php
require_once 'db_connection.php';
require_once 'vendor/autoload.php';

$db = new DBConnection();
$query = $db->conn->query("SELECT dj.*, du.username FROM db_jual dj JOIN db_user du ON dj.user_id = du.id ORDER BY dj.created_at DESC");
$sales = $query->fetch_all(MYSQLI_ASSOC);

// Buat PDF
$pdf = new TCPDF();
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Admin');
$pdf->SetTitle('Laporan Penjualan');
$pdf->SetHeaderData('', 0, 'Laporan Penjualan', '');
$pdf->setHeaderFont(['helvetica', '', 10]);
$pdf->setFooterFont(['helvetica', '', 8]);
$pdf->AddPage();

// Tabel Header
$html = '<h1>Laporan Penjualan</h1>
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
$pdf->Output('Laporan_Penjualan.pdf', 'I');
exit;
