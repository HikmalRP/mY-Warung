<?php
require_once '../db_connection.php';
require_once 'auth_penjual.php';
require 'vendor/autoload.php'; // Pastikan Anda sudah menginstal PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$db = new DBConnection();
$penjual_id = $_SESSION['penjual_id'];

// Ambil nama dan harga produk milik penjual ini
$produkQuery = $db->conn->prepare("SELECT nama, harga FROM db_produk WHERE id_penjual = ?");
$produkQuery->bind_param("i", $penjual_id);
$produkQuery->execute();
$produkResult = $produkQuery->get_result();
$produkMap = []; // nama => harga
while ($row = $produkResult->fetch_assoc()) {
    $produkMap[$row['nama']] = $row['harga'];
}

// Ambil semua transaksi
$query = $db->conn->query("SELECT dj.*, du.username FROM db_jual dj JOIN db_user du ON dj.user_id = du.id ORDER BY dj.created_at DESC");
$sales = [];

while ($jual = $query->fetch_assoc()) {
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

// Buat Spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Laporan Penjualan');

// Header Kolom
$sheet->setCellValue('A1', 'No');
$sheet->setCellValue('B1', 'Username');
$sheet->setCellValue('C1', 'Asal');
$sheet->setCellValue('D1', 'Tujuan');
$sheet->setCellValue('E1', 'Kurir');
$sheet->setCellValue('F1', 'Total Pendapatan');
$sheet->setCellValue('G1', 'Tanggal');

// Isi Data
$row = 2;
foreach ($sales as $index => $sale) {
    $sheet->setCellValue('A' . $row, $index + 1);
    $sheet->setCellValue('B' . $row, $sale['username']);
    $sheet->setCellValue('C' . $row, $sale['origin']);
    $sheet->setCellValue('D' . $row, $sale['destination']);
    $sheet->setCellValue('E' . $row, $sale['courier'] . ' - ' . $sale['service']);
    $sheet->setCellValue('F' . $row, $sale['subtotal_penjual']);
    $sheet->setCellValue('G' . $row, $sale['created_at']);
    $row++;
}

// Ekspor ke Excel
$writer = new Xlsx($spreadsheet);
$fileName = 'Laporan_Penjualan.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $fileName . '"');
header('Cache-Control: max-age=0');

$writer->save('php://output');
exit;
