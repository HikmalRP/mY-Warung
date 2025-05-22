<?php
require_once 'db_connection.php';

header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $db = new DBConnection();
    $query = "INSERT INTO db_user (username, password) VALUES ('$username', '$password')";

    if ($db->conn->query($query)) {
        echo json_encode(['status' => 'success', 'message' => 'Registrasi berhasil']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal melakukan registrasi']);
    }
}
?>
