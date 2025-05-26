<?php require_once '../db_connection.php';
header("Content-Type: application/json");
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'] ?? '';
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    if (empty($nama) || empty($username) || empty($password)) {
        echo json_encode(['status' => 'error', 'message' => 'Semua field wajib diisi']);
        exit;
    }
    $db = new DBConnection(); // Cek apakah username sudah ada 
    $cek = $db->conn->prepare("SELECT id FROM db_user WHERE username = ?");
    $cek->bind_param("s", $username);
    $cek->execute();
    $result = $cek->get_result();
    if ($result->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Username sudah digunakan']);
        exit;
    } // Simpan user
    $query = $db->conn->prepare("INSERT INTO db_user (nama, username, password) VALUES (?, ?, ?)");
    $query->bind_param("sss", $nama, $username, $password);
    if ($query->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Registrasi berhasil']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal melakukan registrasi']);
    }
}
