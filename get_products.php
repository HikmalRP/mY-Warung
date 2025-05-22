<?php
require_once 'db_connection.php';

header('Content-Type: application/json');

// Koneksi database
$db = new DBConnection();

// Query untuk mengambil data produk
$query = $db->conn->query("SELECT id, nama, deskripsi, harga, linkGambar FROM db_produk");

// Fetch semua data produk
$products = [];
while ($row = $query->fetch_assoc()) {
    $products[] = [
        'id' => $row['id'],
        'name' => $row['nama'] ?? 'Tidak ada nama', // Default to 'Tidak ada nama'
        'description' => $row['deskripsi'] ?? 'Tidak ada deskripsi',
        'price' => $row['harga'] ?? 0, // Default to 0
        'image' => $row['linkGambar'] ? 'https://honeydew-panther-755692.hostingersite.com/' . $row['linkGambar'] : 'https://via.placeholder.com/150', // Default image
    ];
}

// Return data produk dalam format JSON
echo json_encode($products);
?>
