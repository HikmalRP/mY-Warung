<?php
require_once '../db_connection.php';
require_once 'auth_penjual.php';
require 'vendor/autoload.php'; // Pastikan Anda sudah menginstal PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$db = new DBConnection();
$penjual_id = $_SESSION['penjual_id'];

// Ambil nama-nama produk milik penjual ini
$produkQuery = $db->conn->prepare("SELECT nama FROM db_produk WHERE id_penjual = ?");
$produkQuery->bind_param("i", $penjual_id);
$produkQuery->execute();
$produkResult = $produkQuery->get_result();
$produkNama = [];
while ($row = $produkResult->fetch_assoc()) {
    $produkNama[] = $row['nama'];
}

// Ambil semua transaksi
$query = $db->conn->query("SELECT dj.*, du.username FROM db_jual dj JOIN db_user du ON dj.user_id = du.id ORDER BY dj.created_at DESC");
$allSales = $query->fetch_all(MYSQLI_ASSOC);

// Filter transaksi yang mengandung produk milik penjual
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
$sheet->setCellValue('F1', 'Total Transaksi');
$sheet->setCellValue('G1', 'Tanggal');

// Isi Data
$row = 2;
foreach ($sales as $index => $sale) {
    $sheet->setCellValue('A' . $row, $index + 1);
    $sheet->setCellValue('B' . $row, $sale['username']);
    $sheet->setCellValue('C' . $row, $sale['origin']);
    $sheet->setCellValue('D' . $row, $sale['destination']);
    $sheet->setCellValue('E' . $row, $sale['courier'] . ' - ' . $sale['service']);
    $sheet->setCellValue('F' . $row, $sale['total_with_shipping']);
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
