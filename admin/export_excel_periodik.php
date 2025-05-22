<?php
require_once 'db_connection.php';
require 'vendor/autoload.php'; // Pastikan library PhpSpreadsheet terinstal

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$db = new DBConnection();
$start_date = $_GET['start_date'] . " 00:00:00";
$end_date = $_GET['end_date'] . " 23:59:59";

$query = $db->conn->prepare("SELECT dj.*, du.username FROM db_jual dj JOIN db_user du ON dj.user_id = du.id WHERE dj.created_at BETWEEN ? AND ? ORDER BY dj.created_at DESC");
$query->bind_param("ss", $start_date, $end_date);
$query->execute();
$result = $query->get_result();
$sales = $result->fetch_all(MYSQLI_ASSOC);


// Buat Spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Laporan Periodik');

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
$fileName = 'Laporan_Periodik.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $fileName . '"');
header('Cache-Control: max-age=0');

$writer->save('php://output');
exit;
