<?php
require_once 'db_connection.php';

header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $db = new DBConnection();
    $query = "SELECT * FROM db_user WHERE username = '$username' AND password = '$password'";
    $result = $db->conn->query($query);

    if ($result->num_rows > 0) {
        echo json_encode(['status' => 'success', 'username' => $username]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Username atau password salah']);
    }
}
?>
