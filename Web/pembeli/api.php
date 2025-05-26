<?php require_once '../db_connection.php';
header("Content-Type: application/json");
$db = new DBConnection();
$baseUrl = 'http://10.0.2.2/mY_Warung/Web/';
switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        if (isset($_GET['id'])) {
            $id = intval($_GET['id']);
            $query = $db->conn->prepare(" SELECT p.*, pen.nama_warung FROM db_produk p LEFT JOIN db_penjual pen ON p.id_penjual = pen.id WHERE p.id = ? ");
            $query->bind_param("i", $id);
            $query->execute();
            $result = $query->get_result();
        } else {
            $result = $db->conn->query(" SELECT p.*, pen.nama_warung FROM db_produk p LEFT JOIN db_penjual pen ON p.id_penjual = pen.id ");
        }
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $row['linkGambar'] = $row['linkGambar'] ? $baseUrl . $row['linkGambar'] : $baseUrl . '../uploads/default_image.jpg';
            $data[] = $row;
        }
        echo json_encode($data);
        break;
    default:
        echo json_encode(["status" => "error", "message" => "Metode tidak didukung"]);
        break;
}
