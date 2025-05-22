<?php
require_once 'db_connection.php';

header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_username = $_POST['current_username'];
    $new_username = $_POST['new_username'];
    $password = $_POST['password'];

    $db = new DBConnection();
    $query = "UPDATE db_user SET username = '$new_username', password = '$password' WHERE username = '$current_username'";

    if ($db->conn->query($query)) {
        echo json_encode(['status' => 'success', 'message' => 'Profil berhasil diperbarui']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal memperbarui profil']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Metode request tidak valid']);
}
?>
