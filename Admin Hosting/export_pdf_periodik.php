<?php
require_once 'db_connection.php';
require_once 'vendor/autoload.php';

$db = new DBConnection();
$start_date = $_GET['start_date'] . " 00:00:00";
$end_date = $_GET['end_date'] . " 23:59:59";

$query = $db->conn->prepare("SELECT dj.*, du.username FROM db_jual dj JOIN db_user du ON dj.user_id = du.id WHERE dj.created_at BETWEEN ? AND ? ORDER BY dj.created_at DESC");
$query->bind_param("ss", $start_date, $end_date);
$query->execute();
$result = $query->get_result();
$sales = $result->fetch_all(MYSQLI_ASSOC);


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
