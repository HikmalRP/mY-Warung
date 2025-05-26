<?php require_once '../db_connection.php';
header('Content-Type: application/json'); // Koneksi database 
$db = new DBConnection(); // Query untuk mengambil data produk beserta nama warung 
$query = $db->conn->query(" SELECT p.id, p.nama, p.deskripsi, p.harga, p.linkGambar, pen.nama_warung FROM db_produk p LEFT JOIN db_penjual pen ON p.id_penjual = pen.id "); // Fetch semua data produk 
$products = [];
while ($row = $query->fetch_assoc()) {
    $products[] = ['id' => $row['id'], 'nama' => $row['nama'] ?? 'Tidak ada nama', 'deskripsi' => $row['deskripsi'] ?? 'Tidak ada deskripsi', 'harga' => $row['harga'] ?? 0, 'linkGambar' => $row['linkGambar'] ? 'http://10.0.2.2/mY_Warung/Web/' . $row['linkGambar'] : 'https://via.placeholder.com/150', 'nama_warung' => $row['nama_warung'] ?? 'Tidak diketahui',];
} // Return data produk dalam format JSON 
echo json_encode($products);
