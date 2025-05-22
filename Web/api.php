<?php
require_once 'db_connection.php';

header("Content-Type: application/json");

$db = new DBConnection();
$baseUrl = 'https://honeydew-panther-755692.hostingersite.com/';

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        if (isset($_GET['id'])) {
            $id = intval($_GET['id']);
            $result = $db->conn->query("SELECT * FROM db_produk WHERE id = $id");
        } else {
            $result = $db->conn->query("SELECT * FROM db_produk");
        }

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $row['linkGambar'] = $row['linkGambar'] 
                ? $baseUrl . $row['linkGambar'] 
                : $baseUrl . 'uploads/default_image.jpg'; // Gambar default jika kosong
            $data[] = $row;
        }

        echo json_encode($data);
        break;

    default:
        echo json_encode(["status" => "error", "message" => "Metode tidak didukung"]);
        break;
}
?>
